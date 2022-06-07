<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\FormulasController;
use App\Http\Controllers\CampaignsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GoldenTicketController;
use App\Http\Controllers\TicketDailyController;
use App\Http\Controllers\TicketWeeklyController;
use App\Http\Controllers\TicketMonthlyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\OrderProductController;
use App\Http\Controllers\MidController;
use App\Http\Controllers\MidGroupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\SubAffiliateController;
use App\Http\Controllers\CampaignBuilderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['middleware' => ['web']], function () {
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
});
Route::resource('dashboard', DashboardController::class);
Route::resource('orders', OrdersController::class);
Route::resource('customers', CustomerController::class);
Route::resource('formulas', FormulasController::class);
Route::resource('campaigns', CampaignsController::class);
Route::resource('golden-ticket', GoldenTicketController::class);
Route::resource('ticket-daily', TicketDailyController::class);
Route::resource('ticket-weekly', TicketWeeklyController::class);
Route::resource('ticket-monthly', TicketMonthlyController::class);
Route::resource('products', ProductController::class);
Route::resource('order-products', OrderProductController::class); 
Route::resource('prospects', ProspectController::class);
Route::resource('mids', MidController::class);
Route::resource('mid-groups', MidGroupController::class);
Route::resource('profiles', ProfileController::class);
Route::resource('columns', ColumnController::class);
Route::resource('sub-affiliates', SubAffiliateController::class);
Route::resource('networks', NetworkController::class);
Route::resource('campaigns_builder', CampaignBuilderController::class);
Route::get('campaign-builder-options', [CampaignBuilderController::class, 'campaign_builder_options']);
Route::get('golden-ticket/create/{month}/{year}', [GoldenTicketController::class, 'create']);
Route::get('get_campaigns', [CampaignsController::class, 'get_campaigns']);
Route::get('get_campaign_columns/{campaign_name}', [CampaignsController::class, 'get_campaign_columns']);
Route::get('refresh_campaigns', [CampaignsController::class, 'refresh_campaigns']);
Route::get('refresh_database_dec', [OrdersController::class, 'refresh_database_dec']);
Route::get('refresh_database_jan', [OrdersController::class, 'refresh_database_jan']);
Route::get('test_dec', [OrdersController::class, 'test_dec']);
Route::get('test_jan', [OrdersController::class, 'test_jan']);
Route::get('pull_cron_orders', [OrdersController::class, 'pull_cron_orders']);
Route::get('pull_orders_dec', [OrdersController::class, 'pull_orders_dec']);
Route::get('pull_orders_jan', [OrdersController::class, 'pull_orders_jan']);
Route::get('pull_daily_orders', [OrdersController::class, 'pull_daily_orders']);
Route::get('pull_daily_order_find', [OrdersController::class, 'pull_daily_order_find']);
Route::get('pull_yesterday_orders', [OrdersController::class, 'pull_yesterday_orders']);
Route::get('pull_prospects', [ProspectController::class, 'pull_prospects']);
Route::get('pull_local_prospects', [ProspectController::class, 'pull_local_prospects']);
Route::get('daily_orders', [OrdersController::class, 'daily_orders']);
Route::get('get_customer_detail', [CustomerController::class, 'get_customer_detail']);
Route::get('get_product_detail', [OrdersController::class, 'get_product_detail']);
Route::get('getDropDownContent', [OrdersController::class, 'getDropDownContent']);
Route::get('refresh_customers', [CustomerController::class, 'refresh_customers']);
Route::get('filter-golden-ticket/{month}/{year}', [GoldenTicketController::class, 'index']);
Route::get('pull_all_products', [ProductController::class, 'pull_all_products']);
Route::get('get_states', [OrdersController::class, 'get_states']);
Route::get('refresh-golden-ticket', [GoldenTicketController::class, 'refresh_golden_ticket']);
Route::get('refresh-daily', [TicketDailyController::class, 'refresh_daily']);
Route::get('refresh-all-daily', [TicketDailyController::class, 'refresh_all_daily_tickets']);
Route::get('refresh-weekly', [TicketWeeklyController::class, 'refresh_weekly']);
Route::get('refresh-all-weekly', [TicketWeeklyController::class, 'refresh_all_weekly']);
Route::get('refresh-monthly', [TicketMonthlyController::class, 'refresh_monthly']);
Route::get('refresh-all-monthly', [TicketMonthlyController::class, 'refresh_all_monthly']);
Route::get('populate_products_from_orders', [OrderProductController::class, 'populate_products_from_orders']);
Route::get('pull_payment_router_view', [MidController::class, 'pull_payment_router_view']);
Route::get('pull_profiles', [ProfileController::class, 'pull_profiles']);
Route::get('get_gateway_ids', [MidController::class, 'get_gateway_ids']);
Route::get('refresh_mids_groups', [MidGroupController::class, 'refresh_mids_groups']);
Route::get('mid_group_names', [MidGroupController::class, 'mid_group_names']);
Route::get('getMidDetail', [MidGroupController::class, 'getMidDetail']);
Route::get('getProductDetail', [MidGroupController::class, 'getProductDetail']);
Route::get('assign_mid_group', [MidController::class, 'assign_mid_group']);
Route::get('get_first_mid', [MidController::class, 'get_first_mid']);
Route::get('refresh_mid_count', [MidController::class, 'refresh_mid_count']);
Route::get('get_mid_count_detail', [MidController::class, 'get_mid_count_detail']);
Route::get('get_assigned_mids', [MidGroupController::class, 'get_assigned_mids']);
Route::get('user-details', [DashboardController::class, 'user_data']);
Route::post('destroy_customers', [CustomerController::class, 'destroy_customers']);
Route::get('destroy_affiliates', [NetworkController::class, 'destroy_affiliates']);
Route::post('assign_bulk_group', [MidController::class, 'assign_bulk_group']);
Route::post('remove_groups', [MidController::class, 'remove_groups']);
Route::post('change_column', [ColumnController::class, 'change_column']);
Route::post('delete_prospects', [ProspectController::class, 'delete_prospects']);
Route::get('pull_affiliates', [NetworkController::class, 'pull_affiliates']);
Route::get('pull_networks', [NetworkController::class, 'pull_networks']);
Route::get('refresh_decline_percentage', [MidController::class, 'refresh_decline_percentage']);
Route::get('get_mids_decline_data', [MidController::class, 'get_mids_decline_data']);
Route::get('get_mids_count_data', [MidController::class, 'get_mids_count_data']);
Route::get('products', [MidController::class, 'products']);
Route::get('getProductForFilter', [MidController::class, 'getProductForFilter']);
Route::get('mids_order_total/{id}', [MidController::class, 'mids_order_total']);
Route::get('order_history', [OrdersController::class, 'order_history']);
Route::get('daily_order_history', [OrdersController::class, 'daily_order_history']);
Route::post('sub_affiliate_gross_revenue', [SubAffiliateController::class, 'sub_affiliate_gross_revenue']);
Route::get('get-active-mids', [MidController::class, 'get_active_mids']);
Route::get('refresh-initials', [MidController::class, 'refresh_initials']);
Route::get('reset-initials', [MidController::class, 'reset_initials']);
Route::get('date-range-products', [OrderProductController::class, 'date_range_products']);