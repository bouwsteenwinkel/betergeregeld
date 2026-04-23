<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('bookkeeping:send-invoice-reminders')
    ->dailyAt('08:00')
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('bookkeeping:generate-recurring-invoices')
    ->dailyAt('06:00')
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('accessguard:scan-risks')
    ->dailyAt('03:00')
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('accessguard:build-reminders')
    ->dailyAt('03:15')
    ->onOneServer()
    ->withoutOverlapping();
