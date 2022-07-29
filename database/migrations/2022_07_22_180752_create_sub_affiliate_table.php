<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubAffiliateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_affiliate', function (Blueprint $table) {
            $table->id();
            $table->integer('affid');
            $table->integer('user_id');
            $table->text('sub1');
            $table->text('sub2');
            $table->text('sub3');
            $table->text('impressions');
            $table->text('gross_clicks');
            $table->text('total_clicks');
            $table->text('unique_clicks');
            $table->text('duplicate_clicks');
            $table->text('invalid_clicks');
            $table->text('total_conversions');
            $table->text('CV');
            $table->text('invalid_conversions_scrub');
            $table->text('view_through_conversions');
            $table->text('events');
            $table->text('view_through_events');
            $table->text('CVR');
            $table->text('EVR');
            $table->text('CTR');
            $table->text('CPC');
            $table->text('CPA');
            $table->text('EPC');
            $table->text('RPC');
            $table->text('RPA');
            $table->text('payout');
            $table->text('revenue');
            $table->text('margin');
            $table->text('profit');
            $table->text('gross_sales');
            $table->text('ROAS');
            $table->text('gross_sales_vt');
            $table->text('RPM');
            $table->text('CPM');
            $table->text('avg_sale_value');
            $table->date('date');
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
        Schema::dropIfExists('sub_affiliate');
    }
}
