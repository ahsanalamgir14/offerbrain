<?php

namespace App\Console;

use Carbon\Carbon;
use App\Models\Setting;
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
        $start_date = Carbon::now()->startOfDay()->format('m/d/Y');
        $end_date = Carbon::now()->endOfDay()->format('m/d/Y');
        $setting = Setting::where('key','_last_date_for_history_api')->first();
        $historyStartDate = date($setting->value);
        $historyEndDate = date('Y-m-d H:i:s');

        $prospectsStartDate = Carbon::now()->startOfDay()->format('Y-m-d');
        $prospectsEndDate = date('Y-m-d', strtotime($prospectsStartDate) + 86400);
        
        // echo OrdersController::curentTime();
        echo OrdersController::pull_cron_orders($start_date, $end_date);
        echo OrdersController::daily_order_history_cron($historyStartDate, $historyEndDate);
        echo NetworkController::pull_affiliates_for_cron();
        echo CampaignsController::refresh_campaigns_for_cron();
        echo CustomerController::refresh_customers();
        
        $now_date = Carbon::now()->startOfDay()->format('m/d/Y');
        if($now_date != $start_date){
            echo OrdersController::pull_cron_orders($start_date, $end_date);
        } else {
            echo 'Start date is equal to now date';
        }
        echo ProspectController::pull_prospects($prospectsStartDate, $prospectsEndDate);
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
