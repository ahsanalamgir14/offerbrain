<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderProduct extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $dates = ['deleted_at'];
    protected $table = 'order_products';
    protected $fillable = [
        'order_id',
        'user_id',
        'product_id',
        'sku',
        'price',
        'product_qty',
        'name',
        'is_recurring',
        'is_terminal',
        'recurring_date',
        'subscription_id',
        'next_subscription_product',
        'next_subscription_product_id',
        'next_subscription_product_price',
        'next_subscription_qty',
        'billing_model_discount',
        'is_add_on',
        'is_in_trial',
        'step_number',
        'is_shippable',
        'is_full_refund',
        'refund_amount',
        'on_hold',
        'hold_date',
        'billing_model_id',
        'billing_model_name',
        'billing_model_description',
        'offer_id',
        'offer_name'
    ];
}
