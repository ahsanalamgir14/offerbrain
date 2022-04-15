<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Network extends Model
{
    use HasFactory, SoftDeletes;
    protected $softDelete = true;
    protected $dates = ['deleted_at'];

    protected $table = 'networks';
    protected $casts = [
        'relationship' => 'object',
    ];
    protected $guarded = [];
    protected $fillable = [
        'network_affiliate_id',
        'network_id',
        'name',
        'account_status',
        'network_employee_id',
        'internal_notes',
        'has_notifications',
        'network_traffic_source_id',
        'account_executive_id',
        'adress_id',
        'default_currency_id',
        'is_contact_address_enabled',
        'enable_media_cost_tracking_links',
        'time_created',
        'time_saved',
        'relationship',
        'referrer_id',
    ];
}
