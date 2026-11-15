<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
		\App\Console\Commands\CreateSerieStatistics::class,
		\App\Console\Commands\CreateSerieZip::class,
		\App\Console\Commands\ClearFileFolder::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('clear:file_folder')->daily();
		$schedule->command('backup:clean')->weekly()->at('02:00');
		$schedule->command('backup:run')->weekly()->at('03:00');
    }
}
