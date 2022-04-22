<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Mid extends Model
{
    public $from;
    public $to;
    public $order_status;
    use HasFactory, SearchableTrait;
    // public $timestamps = false;
    protected $guarded = [];
    protected $table = 'mids';
    // protected $casts = [
    //     'decline_orders' => 'array'

    protected $searchable = [
        'columns' => [
            'mids.gateway_alias' => 10,
            // 'mids.mid_group' => 10,
            'mids.global_monthly_cap' => 2,
            'mids.current_monthly_amount' => 2,
            'mids.processing_percent' => 1,
        ],
    ];
    // ];
    protected $fillable = [
        'router_id',
        'router_name',
        // 'mid_count',
        'mid_group',
        'router_date_in',
        'router_desc',
        'mid_group_setting_id',
        'mid_group_setting',
        'is_three_d_routed',
        'is_strict_preserve',
        'created_on',
        'campaign_id',
        'current_monthly_amount',
        'processing_percent',
        'gateway_id',
        'gateway_type',
        'gateway_provider',
        'gateway_alias',
        'gateway_created',
        'gateway_active',
        'global_monthly_cap',
        'monthly_sales',
        'processing_percent',
        'reserve_percent',
        'transaction_fee',
        'chargeback_fee',
        'gateway_descriptor',
        'customer_service_number',
        'gateway_currency',
        // 'decline_per',
        // 'decline_id',
        // 'decline_orders',
        // 'mid_group',
    ];

    function orders()
    {
        return $this->hasMany(Order::class, 'gateway_id', 'gateway_id');
    }
    function approved_orders()
    {
        return $this->hasMany(Order::class, 'gateway_id', 'gateway_id');
    }
    function declined_orders()
    {
        return $this->hasMany(Order::class, 'gateway_id', 'gateway_id');

    }
}
