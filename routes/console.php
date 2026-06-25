<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// هيشتغل يوم 1 في الشهر الساعة 12:00 AM بتوقيت القاهرة
Schedule::command('app:monthly-reset-balances')
    ->monthlyOn(1, '00:00')
    ->timezone('Africa/Cairo');

Schedule::command('app:send-weekly-goal-reminder')->weekly();