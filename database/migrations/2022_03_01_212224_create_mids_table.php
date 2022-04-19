<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema; 

class CreateMidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('router_id')->nullable()->default(null);
            $table->string('router_name')->nullable()->default(null);
            $table->Integer('mid_count')->nullable()->default(0);
            $table->timestamp('router_date_in')->nullable()->default(null);
            $table->string('router_desc')->nullable()->default(null);
            $table->string('mid_group_setting_id')->nullable()->default(null);
            $table->string('mid_group_setting')->nullable()->default(null);
            $table->string('is_three_d_routed')->nullable()->default(null);
            $table->string('is_strict_preserve')->nullable()->default(null);
            $table->timestamp('created_on')->nullable()->default(null);
            $table->unsignedBigInteger('campaign_id')->nullable()->default(null);
            $table->unsignedBigInteger('gateway_id')->index();
            $table->string('gateway_alias')->nullable()->default(null);
            $table->string('global_monthly_cap')->nullable()->default(null);
            $table->string('current_monthly_amount')->nullable()->default(null);
            $table->string('processing_percent')->nullable()->default(null);
            $table->string('decline_per')->nullable()->default(0);
            $table->unique(['gateway_id','gateway_alias']);
            // $table->unsignedBigInteger('decline_id')->nullable()->default(null);
            // $table->unsignedBigInteger('mid_count_id')->nullable()->default(null);
            // $table->json('decline_orders')->nullable()->default(null);
            // $table->foreign('decline_id')->references('id')->on('declines');
            // $table->foreign('mid_count_id')->references('id')->on('declines');
            // $table->foreign('campaign_id')->references('campaign_id')->on('campaigns');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mids');
    }
}
