<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (config('benotes.use_filesystem') == false) {
            return;
        }
        if (config('benotes.run_backup')) {
            $schedule->command('backup:run')->cron(config('benotes.backup_interval'));
        }
        if (config('benotes.generate_missing_thumbnails')) {
            $schedule->command('queue:work --max-jobs=10 --stop-when-empty')->cron(config('thumbnail_filler_interval'));
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
