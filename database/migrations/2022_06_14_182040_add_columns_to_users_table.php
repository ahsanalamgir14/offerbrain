<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('users', function (Blueprint $table) {
        //     $table->text('sticky_api_username')->after('remember_token')->nullable()->default(null);
        //     $table->text('sticky_api_key')->after('sticky_api_username')->nullable()->default(null);
        //     $table->text('sticky_url')->after('sticky_api_key')->nullable()->default(null);
        //     $table->text('everflow_api_key')->after('sticky_url')->nullable()->default(null);
        // });

        // Schema::table('orders', function (Blueprint $table) {
        //     $table->unsignedBigInteger('user_id')->after('order_id')->nullable()->default(null);
        // });

        // Schema::table('order_products', function (Blueprint $table) {
        //     $table->unsignedBigInteger('user_id')->after('order_id')->nullable()->default(null);
        // });

        // Schema::table('customers', function (Blueprint $table) {
        //     $table->unsignedBigInteger('user_id')->after('customer_id')->nullable()->default(null);
        // });

        // Schema::table('prospects', function (Blueprint $table) {
        //     $table->unsignedBigInteger('user_id')->after('prospect_id')->nullable()->default(null);
        // });

        // Schema::table('settings', function (Blueprint $table) {
        //     $table->unsignedBigInteger('user_id')->after('id')->nullable()->default(null);
        // });

        // Schema::table('mids', function (Blueprint $table) {
        //     $table->unsignedBigInteger('user_id')->after('router_id')->nullable()->default(null);
        // });

        // Schema::table('mid_groups', function (Blueprint $table) {
        //     $table->unsignedBigInteger('user_id')->after('id')->nullable()->default(null);
        // });

        // Schema::table('networks', function (Blueprint $table) {
        //     $table->unsignedBigInteger('user_id')->after('network_id')->nullable()->default(null);
        // });
        // Schema::table('orders', function (Blueprint $table) {
        //     $table->json('ip_details')->after('ip_address')->nullable()->default(null);
        // });

        // Schema::table('campaigns', function (Blueprint $table) {
        //     $table->json('tracking_campaign_ids')->after('tracking_campaigns')->nullable()->default(null);
        // });

        // Schema::table('campaigns', function (Blueprint $table) {
        //     $table->json('tracking_network_ids')->after('tracking_networks')->nullable()->default(null);
        // });

        // Schema::table('campaigns', function (Blueprint $table) {
        //     $table->json('upsell_product_ids')->after('upsell_products')->nullable()->default(null);
        // });
        
        // Schema::table('campaigns', function (Blueprint $table) {
        //     $table->json('downsell_product_ids')->after('downsell_products')->nullable()->default(null);
        // });

        // Schema::table('campaigns', function (Blueprint $table) {
        //     $table->json('cycle_product_ids')->after('cycle_products')->nullable()->default(null);
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
