<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreateOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->text('order_id')->nullable()->default(null)->index();
            $table->string('product_id')->nullable()->default(null)->index();
            $table->string('sku')->nullable()->default(null);
            $table->string('price')->nullable()->default(null);
            $table->string('product_qty')->nullable()->default(null);
            $table->string('name')->nullable()->default(null);
            $table->string('is_recurring')->nullable()->default(null);
            $table->string('is_terminal')->nullable()->default(null);
            $table->string('recurring_date')->nullable()->default(null);
            $table->string('subscription_id')->nullable()->default(null);
            $table->string('next_subscription_product')->nullable()->default(null);
            $table->string('next_subscription_product_id')->nullable()->default(null);
            $table->string('next_subscription_product_price')->nullable()->default(null);
            $table->string('next_subscription_qty')->nullable()->default(null);
            $table->string('billing_model_discount')->nullable()->default(null);
            $table->string('is_add_on')->nullable()->default(null);
            $table->string('is_in_trial')->nullable()->default(null);
            $table->string('step_number')->nullable()->default(null);
            $table->string('is_shippable')->nullable()->default(null);
            $table->string('is_full_refund')->nullable()->default(null);
            $table->string('refund_amount')->nullable()->default(null);
            $table->string('on_hold')->nullable()->default(null);
            $table->string('hold_date')->nullable()->default(null);
            $table->string('billing_model_id')->nullable()->default(null);
            $table->string('billing_model_name')->nullable()->default(null);
            $table->string('billing_model_description')->nullable()->default(null);
            $table->string('offer_id')->nullable()->default(null);
            $table->string('offer_name')->nullable()->default(null);
            // $table->foreign('order_id')->references('order_id')->on('orders');
            // $table->foreign('order_id')->nullable()->references('order_id')->on('orders')
            // $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['order_id', 'product_id']);
            $table->text('time_stamp')->nullable()->default(null);
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
        Schema::dropIfExists('order_products');
    }
}
