<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Schedule the OpenRemote token refresh every minute
        $schedule->command('openremote:refresh-token')->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
    protected $commands = [
        // ...
        \App\Console\Commands\RunAllConsumers::class,
    ];
    
}
