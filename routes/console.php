<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes — Millénaire Connect
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Scheduled tasks ──────────────────────────────────────────────────────
Schedule::call(function () {
    // Mark users offline if they haven't pinged in 5 minutes
    \App\Models\User::where('is_online', true)
        ->where('last_seen_at', '<', now()->subMinutes(5))
        ->update(['is_online' => false]);
})->everyFiveMinutes()->name('presence.cleanup');

Schedule::call(function () {
    // Send appointment reminders 24h before
    \App\Models\Appointment::where('status', 'confirmed')
        ->whereBetween('scheduled_at', [now()->addHours(23), now()->addHours(25)])
        ->each(function ($appt) {
            $guardian = $appt->student?->guardians()?->first();
            if ($guardian?->user) {
                $guardian->user->notify(
                    new \App\Notifications\AppointmentRequestedNotification($appt)
                );
            }
        });
})->hourly()->name('appointments.reminders');

Schedule::call(function () {
    // Remind teachers to submit grades before end of term
    \Illuminate\Support\Facades\Cache::forget('admin.kpis');
    \Illuminate\Support\Facades\Cache::forget('admin.finance.stats');
})->daily()->name('cache.flush.daily');
