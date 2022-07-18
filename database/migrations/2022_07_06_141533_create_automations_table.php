<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('automation_name')->nullable();
            $table->string('automation_type')->nullable();
            $table->string('throttle_resource')->nullable();
            $table->text('affiliate')->nullable();
            $table->text('affiliate_id')->nullable();
            $table->string('sub_affiliate')->nullable();
            $table->string('cpa')->nullable();
            $table->string('cap')->nullable();
            $table->string('trigger')->nullable();
            $table->string('operator')->nullable();
            $table->string('lookback')->nullable();
            $table->string('action')->nullable();
            $table->integer('is_prefire_reach_target')->default(0);
            $table->string('prefire_resource');
            $table->string('timeframe')->nullable();
            $table->integer('is_per_day')->default(0);
            $table->string('time_from')->nullable();
            $table->string('time_to')->nullable();
            $table->timestamps();
            $table->foreign("user_id")->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('automations');
    }
}
