<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BaseCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('base_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('month')->nullable()->default(null)->comment('Month');
            $table->string('year')->nullable()->default(null)->comment('Self-added');
            $table->integer('initials')->nullable()->default(null)->comment('Initials');
            $table->integer('rebills')->nullable()->default(null)->comment('Rebills');
            $table->decimal('cycle_1_per', 10, 2)->nullable()->default(null)->comment('Cycle 1 %');
            $table->integer('cycle_2')->nullable()->default(null)->comment('Cycle 2');
            $table->decimal('cycle_2_per', 10, 2)->nullable()->default(null)->comment('Cycle 2 %');
            $table->integer('cycle_3_plus')->nullable()->default(null)->comment('Cycle 3+');
            $table->decimal('cycle_3_plus_per', 10, 2)->nullable()->default(null)->comment('Cycle 3+ %');
            $table->decimal('avg_ticket', 10, 2)->nullable()->default(null)->comment('Avg Ticket');
            $table->decimal('revenue', 10, 2)->nullable()->default(null)->comment('Revenue');
            $table->decimal('refund', 10, 2)->nullable()->default(null)->comment('Refund');
            $table->decimal('refund_rate', 10, 2)->nullable()->default(null)->comment('Refund Rate');
            $table->integer('CBs')->nullable()->default(null)->comment('CBs');
            $table->decimal('CB_per', 10, 2)->nullable()->default(null)->comment('CB %');
            $table->decimal('CB_currency', 10, 2)->nullable()->default(null)->comment('CB $');
            $table->decimal('fulfillment', 10, 2)->nullable()->default(null)->comment('Fulfillment');
            $table->decimal('processing', 10, 2)->nullable()->default(null)->comment('Processing');
            $table->decimal('cpa', 10, 2)->nullable()->default(null)->comment('CPA');
            $table->decimal('cpa_avg', 10, 2)->nullable()->default(null)->comment('CPA AVG');
            $table->decimal('net', 10, 2)->nullable()->default(null)->comment('Net');
            $table->decimal('clv', 10, 2)->nullable()->default(null)->comment('CLV');
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
        //
    }
}
