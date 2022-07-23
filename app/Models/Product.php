<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'products';
    protected $dates = ['deleted_at'];
    public $timestamps = false;
    protected $cast = [
        'custom_fields' => 'array',
        'vertical' => 'vertical',
        'category' => 'array',
        'legacy_subscription' => 'array',
        'images' => 'array'
    ];
    protected $fillable = [
        'product_id',
        'user_id',
        'is_trial_product',
        'is_shippable',
        'tax_code',
        'is_licensed',
        'name',
        'description',
        'sku',
        'price',
        'weight',
        'declared_value',
        'restocking_fee',
        'cost_of_goods',
        'max_quantity',
        'custom_fields',
        'vertical',
        'category_id',
        'category',
        'is_bundle',
        'is_custom_bundle',
        'is_variant_enabled',
        'legacy_subscription',
        'created_at',
        'updated_at',
        'images',
    ];

}
