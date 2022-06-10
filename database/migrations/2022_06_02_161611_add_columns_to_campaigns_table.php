<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // $table->string('name')->nullable()->default(null);
            $table->unsignedBigInteger('user_id')->nullable()->after('gateway_id')->default(null);
            $table->string('campaign_type')->nullable()->default(null);
            $table->json('tracking_campaigns')->nullable()->default(null);
            $table->json('tracking_networks')->nullable()->default(null);
            $table->integer('no_of_upsells')->nullable()->default(null);
            $table->integer('no_of_downsells')->nullable()->default(null);
            $table->json('upsell_products')->nullable()->default(null);
            $table->json('downsell_products')->nullable()->default(null);
            $table->integer('no_of_cycles')->nullable()->default(null);
            $table->json('cycle_products')->nullable()->default(null);
            $table->boolean('cogs_track')->nullable()->default(0);
            $table->boolean('cpa_track')->nullable()->default(0);
            $table->boolean('third_party_track')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('campaign_type')->nullable()->default(null);
            $table->json('tracking_campaigns')->nullable()->default(null);
            $table->json('tracking_networks')->nullable()->default(null);
            $table->integer('no_of_upsells')->nullable()->default(null);
            $table->integer('no_of_downsells')->nullable()->default(null);
            $table->json('upsell_products')->nullable()->default(null);
            $table->json('downsell_products')->nullable()->default(null);
            $table->integer('no_of_cycles')->nullable()->default(null);
            $table->boolean('cogs_track')->nullable()->default(0);
            $table->boolean('cpa_track')->nullable()->default(0);
            $table->boolean('third_party_track')->nullable()->default(0);
        });
    }
}
