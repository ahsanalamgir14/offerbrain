<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospect extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $table = 'prospects';
    protected $dates = ['deleted_at'];
    protected $casts = [
        'notes' => 'array'
    ];
    protected $fillable = [
        'prospect_id',
        'user_id',
        'campaign_id',
        'first_name',
        'last_name',
        'address',
        'address2',
        'city',
        'state',
        'state_id',
        'zip',
        'country',
        'phone',
        'email',
        'ip_address',
        'month_created',
        'year_created',
        'date_created',
        'risk_flag',
        'affiliate',
        'sub_affiliate',
        'notes'
    ];
    
}
