<?php

namespace App\Modules\Company\Infrastructure;

use App\Modules\Company\Application\Contracts\FactAttestation;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

final class EncryptedFactAttestation implements FactAttestation
{
    public function seal(array $facts, string $kind): string
    {
        return Crypt::encryptString(json_encode(['purpose' => 'nusalens-facts-v1', 'kind' => $kind, 'facts' => $facts], JSON_THROW_ON_ERROR));
    }

    public function open(string $receipt, string $kind, string $symbol): array
    {
        try {
            $data = json_decode(Crypt::decryptString($receipt), true, flags: JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException) {
            throw new InvalidArgumentException('Bukti data tidak valid. Muat kembali data perusahaan.');
        }
        if (($data['purpose'] ?? null) !== 'nusalens-facts-v1' || ($data['kind'] ?? null) !== $kind || ($data['facts']['symbol'] ?? null) !== $symbol) {
            throw new InvalidArgumentException('Bukti tidak sesuai perusahaan atau bagian data.');
        }

        return $data['facts'];
    }
}
