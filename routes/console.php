<?php

use Illuminate\Support\Facades\Schedule;

// Process renewals and overdue invoices daily at midnight
Schedule::command('billing:process')->dailyAt('00:00');

// Sync server statuses every 5 minutes
Schedule::command('servers:sync')->everyFiveMinutes();
