<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\EmployeeDocument;
use App\Services\EmailService;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    // Warn 30 days before + 7 days before + on day of expiry
    $targetDates = [
        now()->addDays(30)->toDateString(),
        now()->addDays(7)->toDateString(),
        now()->toDateString(),
    ];

    EmployeeDocument::with('employee')
        ->whereNotNull('expiry_date')
        ->whereIn(\DB::raw('DATE(expiry_date)'), $targetDates)
        ->get()
        ->each(fn($doc) => EmailService::documentExpiryReminder($doc));
})->dailyAt('08:00')->name('document-expiry-reminders')->withoutOverlapping();
