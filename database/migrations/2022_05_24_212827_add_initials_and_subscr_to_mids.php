<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInitialsAndSubscrToMids extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mids', function (Blueprint $table) {
            $table->string('initials')->after('gateway_id')->nullable()->default(null)->comment('self-added');           
            $table->string('subscr')->after('initials')->nullable()->default(null)->comment('self-added');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mids', function (Blueprint $table) {
            //
        });
    }
}
