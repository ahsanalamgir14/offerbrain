<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreateNetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('networks', function (Blueprint $table) {
            $table->id();
            $table->string('network_affiliate_id')->nullable()->default(null);
            $table->string('network_id')->nullable()->default(null);
            $table->string('name')->nullable()->default(null);
            $table->string('account_status')->nullable()->default(null);
            $table->string('network_employee_id')->nullable()->default(null);
            $table->string('internal_notes')->nullable()->default(null);
            $table->string('has_notifications')->nullable()->default(null);
            $table->string('network_traffic_source_id')->nullable()->default(null);
            $table->string('account_executive_id')->nullable()->default(null);
            $table->string('adress_id')->nullable()->default(null);
            $table->string('default_currency_id')->nullable()->default(null);
            $table->string('is_contact_address_enabled')->nullable()->default(null);
            $table->string('enable_media_cost_tracking_links')->nullable()->default(null);
            $table->string('time_created')->nullable()->default(null);
            $table->string('time_saved')->nullable()->default(null);
            $table->json('relationship')->nullable()->default(null);
            $table->string('referrer_id')->nullable()->default(null);
            $table->softDeletes();
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
        Schema::dropIfExists('networks');
    }
}
