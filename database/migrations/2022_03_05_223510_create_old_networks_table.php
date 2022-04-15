<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOldNetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('old_networks', function (Blueprint $table) {
            $table->id();
            $table->string('network_id')->nullable()->default(null);
            $table->string('customer_id')->nullable()->default(null);
            $table->string('name')->nullable()->default(null);
            $table->string('identifier')->nullable()->default(null);
            $table->string('account_status')->nullable()->default(null);
            $table->string('displayed_name')->nullable()->default(null);
            $table->string('is_show_name')->nullable()->default(null);
            $table->string('timezone_id')->nullable()->default(null);
            $table->string('language_id')->nullable()->default(null);
            $table->string('currency_id')->nullable()->default(null);
            $table->string('logo_image_url')->nullable()->default(null);
            $table->string('favicon_image_url')->nullable()->default(null);
            $table->string('support_email')->nullable()->default(null);
            $table->string('email_background_logo_color')->nullable()->default(null);
            $table->string('time_created')->nullable()->default(null);
            $table->string('time_saved')->nullable()->default(null);
            $table->json('relationship')->nullable()->default(null);
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
        Schema::dropIfExists('old_networks');
    }
}
