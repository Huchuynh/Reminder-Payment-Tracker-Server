<?php

use App\Jobs\CheckSubscriptionRemindersJob;
use App\Jobs\HandleOverdueSubscriptionsJob;
use App\Jobs\UpdateSubscriptionStatusJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new CheckSubscriptionRemindersJob())->hourly();
Schedule::job(new HandleOverdueSubscriptionsJob())->hourly();
Schedule::job(new UpdateSubscriptionStatusJob())->daily();
