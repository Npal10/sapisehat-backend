<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Models\Cow;

Schedule::call(function () {
    // Menambahkan umur sapi (age) sebanyak 1
    Cow::query()->increment('age', 1);
})->monthlyOn(1, '00:00')->name('increment_cow_age')->withoutOverlapping();

// Penjadwalan pengecekan kesehatan sapi setiap pagi jam 8
Schedule::command('app:check-cow-health')->dailyAt('08:00')->withoutOverlapping();
