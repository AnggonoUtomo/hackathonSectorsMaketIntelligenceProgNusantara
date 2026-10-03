<?php

use App\Modules\Intelligence\Application\PruneResearchEvidence;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('nusalens:prune-evidence', function (PruneResearchEvidence $prune) {
    $this->info('Bukti kedaluwarsa dibersihkan: '.$prune->execute(now()->toDateTimeImmutable()));
})->purpose('Hapus bukti bersama kedaluwarsa berusia lebih dari 30 hari; salinan riset privat tetap utuh');

Schedule::command('nusalens:prune-evidence')->dailyAt('01:00')->timezone('Asia/Jakarta')->withoutOverlapping();
