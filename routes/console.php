<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('trash:prune --days=2')->daily();

// Daily digest email — set MAIL_DAILY_DIGEST_TO in your .env
Schedule::command('mail:daily-digest')->daily();
