<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Pengingat follow-up harian via WhatsApp
        $schedule->command('wa:send-reminders')->dailyAt('07:30');

        // Backup database otomatis setiap minggu ke Google Drive masing-masing kampus (UBSI Cikampek & UBSI Karawang)
        $schedule->command('db:backup --upload-gdrive --campus=ALL --type=scheduled')
            ->weekly()
            ->sundays()
            ->at('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
