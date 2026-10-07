<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Predis\Client;

class ProductionPreflight extends Command
{
    protected $signature = 'nusalens:preflight {--check-services : Periksa MySQL, migration, Redis dan cache lock}';

    protected $description = 'Validasi kesiapan konfigurasi produksi tanpa API Sectors, pengiriman email, atau menampilkan secret';

    public function handle(): int
    {
        $key = (string) config('app.key');
        $decoded = str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7), true) : $key;
        $url = (string) config('app.url');
        $host = parse_url($url, PHP_URL_HOST);
        $mailer = (string) config('mail.default');
        $checks = [
            'APP_ENV production' => config('app.env') === 'production',
            'Debug nonaktif' => config('app.debug') === false,
            'APP_URL HTTPS domain publik (bukan contoh)' => filter_var($url, FILTER_VALIDATE_URL) && parse_url($url, PHP_URL_SCHEME) === 'https'
                && is_string($host) && str_contains($host, '.') && ! preg_match('/(?:localhost|example\.(?:com|org|net)|\.(?:test|local|invalid|example))$/i', $host),
            'APP_KEY valid' => is_string($decoded) && Encrypter::supported($decoded, (string) config('app.cipher')),
            'PHP 8.4+ dan ekstensi runtime' => PHP_VERSION_ID >= 80400 && extension_loaded('pdo_mysql') && extension_loaded('mbstring')
                && extension_loaded('openssl') && extension_loaded('curl') && extension_loaded('dom'),
            'MySQL, akun aplikasi non-root, credential tersedia' => config('database.default') === 'mysql'
                && ! empty(config('database.connections.mysql.database')) && ! empty(config('database.connections.mysql.password'))
                && ! in_array(config('database.connections.mysql.username'), [null, '', 'root'], true),
            'Redis cache/queue dan namespace' => config('cache.default') === 'redis' && config('queue.default') === 'redis'
                && ! empty(config('cache.prefix')),
            'Driver Redis terpasang' => match (config('database.redis.client')) {
                'phpredis' => extension_loaded('redis'),
                'predis' => class_exists(Client::class),
                default => false,
            },
            'Session server-side dan cookie aman' => in_array(config('session.driver'), ['database', 'redis'], true)
                && config('session.secure') === true && config('session.http_only') === true && config('session.same_site') === 'lax',
            'Sectors real, key tersedia, endpoint resmi HTTPS' => config('marketdata.provider_mode') === 'real'
                && ! empty(config('services.sectors.api_key')) && rtrim((string) config('services.sectors.base_url'), '/') === 'https://api.sectors.app/v2',
            'Budget 1000, kuota <=20 dan maksimum dua attempt' => (int) config('marketdata.credits.global_budget') === 1000
                && (int) config('marketdata.credits.daily_user_quota') > 0 && (int) config('marketdata.credits.daily_user_quota') <= 20
                && in_array((int) config('marketdata.credits.max_attempts'), [1, 2], true) && config('marketdata.credits.timezone') === 'Asia/Jakarta',
            'SMTP nyata dengan TLS wajib, timeout dan pengirim' => $mailer === 'smtp'
                && ! in_array(config('mail.mailers.smtp.host'), [null, '', 'localhost', '127.0.0.1'], true)
                && config('mail.mailers.smtp.require_tls') === true && (int) config('mail.mailers.smtp.timeout') > 0
                && filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL),
            'Log produksi bukan debug' => in_array(config('logging.default'), ['daily', 'stderr'], true)
                && in_array(config('logging.channels.'.config('logging.default').'.level'), ['info', 'notice', 'warning', 'error'], true),
            'Artifact Vite lengkap tanpa public/hot' => ! is_file(public_path('hot')) && $this->validBuild(),
            'Direktori runtime writable' => is_writable(storage_path()) && is_writable(base_path('bootstrap/cache')),
        ];
        if ($this->option('check-services')) {
            if (in_array(false, array_map(fn ($value) => (bool) $value, $checks), true)) {
                $checks['Layanan tidak dihubungi sebelum konfigurasi valid'] = false;
            } else {
                $checks += $this->services();
            }
        }
        $this->table(['Pemeriksaan', 'Status'], array_map(fn ($label, $pass) => [$label, $pass ? 'PASS' : 'FAIL'], array_keys($checks), $checks));
        $this->line('Tidak memanggil Sectors atau mengirim email. DNS/TLS publik, SMTP inbox, backup/restore dan worker tetap perlu smoke di server.');

        return in_array(false, array_map(fn ($value) => (bool) $value, $checks), true) ? self::FAILURE : self::SUCCESS;
    }

    private function validBuild(): bool
    {
        try {
            $path = public_path('build/manifest.json');
            if (! is_file($path)) {
                return false;
            }
            $manifest = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($manifest) || ! isset($manifest['resources/js/app.tsx']['file'])) {
                return false;
            }
            foreach ($manifest as $entry) {
                if (! is_array($entry) || ! is_string($entry['file'] ?? null)) {
                    return false;
                }
                foreach (array_merge([$entry['file']], $entry['css'] ?? [], $entry['assets'] ?? []) as $asset) {
                    if (! is_string($asset) || str_contains($asset, '..') || ! is_file(public_path('build/'.$asset))) {
                        return false;
                    }
                }
                foreach (array_merge($entry['imports'] ?? [], $entry['dynamicImports'] ?? []) as $import) {
                    if (! isset($manifest[$import])) {
                        return false;
                    }
                }
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function services(): array
    {
        // Never render connection exceptions: driver messages may contain credentials.
        try {
            DB::select('SELECT 1');
            $migrator = app('migrator');
            $pending = array_diff(array_keys($migrator->getMigrationFiles(database_path('migrations'))), $migrator->getRepository()->getRan());
            $checks = ['MySQL terhubung dan seluruh migration terpasang' => $pending === []];
        } catch (\Throwable) {
            return ['MySQL/migration gagal; periksa layanan dan credential secara privat' => false];
        }
        try {
            Redis::connection(config('queue.connections.redis.connection', 'default'))->ping();
            $lock = Cache::store('redis')->lock('preflight:'.Str::uuid(), 10);
            $acquired = $lock->get();
            if ($acquired) {
                $lock->release();
            }
            $checks['Redis queue dan cache lock berfungsi'] = $acquired;
        } catch (\Throwable) {
            $checks['Redis queue/cache gagal; periksa layanan secara privat'] = false;
        }

        return $checks;
    }
}
