<?php

namespace App\Console;

use App\Console\Commands\Cron;
use App\Console\Commands\DailyOrders;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\NetworkController;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\CampaignsController;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        echo OrdersController::curentTime();
        echo OrdersController::pull_cron_orders();
        echo CustomerController::refresh_customers();
        echo ProspectController::pull_prospects();
        echo OrdersController::daily_order_history_cron();
        echo NetworkController::pull_affiliates_for_cron();
        echo CampaignsController::refresh_campaigns_for_cron();
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
