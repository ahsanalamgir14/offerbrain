<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\DailyOrders;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\CustomerController;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\DailyOrders::class,
    ];
     
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        echo OrdersController::pull_cron_orders();
        echo ProspectController::pull_prospects();
        echo CustomerController::refresh_customers();
        echo OrdersController::daily_order_history_cron();
        // $schedule->command('inspire')->hourly();
        // $schedule->command('daily:orders')->cron('*/1 * * * *');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
    */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
