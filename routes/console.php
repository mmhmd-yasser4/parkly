<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('reservations:send-reminders')->everyMinute();