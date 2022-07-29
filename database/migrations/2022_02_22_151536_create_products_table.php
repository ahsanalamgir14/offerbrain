<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->string('is_trial_product')->nullable()->default(null);
            $table->string('is_shippable')->nullable()->default(null);
            $table->string('tax_code')->nullable()->default(null);
            $table->string('is_licensed')->nullable()->default(null);
            $table->longText('name')->nullable()->default(null); 
            $table->longText('description')->nullable()->default(null);
            $table->string('sku')->nullable()->default(null);
            $table->string('price')->nullable()->default(null);
            $table->string('weight')->nullable()->default(null);
            $table->string('declared_value')->nullable()->default(null);
            $table->string('restocking_fee')->nullable()->default(null);
            $table->string('cost_of_goods')->nullable()->default(null);
            $table->string('max_quantity')->nullable()->default(null);
            $table->json('custom_fields')->nullable()->default(null);
            $table->json('vertical')->nullable()->default(null);
            $table->string('category_id')->nullable()->default(null);
            $table->json('category')->nullable()->default(null);
            $table->string('is_bundle')->nullable()->default(null);
            $table->string('is_custom_bundle')->nullable()->default(null);
            $table->string('is_variant_enabled')->nullable()->default(null);
            $table->json('legacy_subscription')->nullable()->default(null);
            $table->json('images')->nullable()->default(null);
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
        Schema::dropIfExists('products');
    }
}
