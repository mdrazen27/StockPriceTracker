<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('update-stock-prices')->everyMinute();
