<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'customers';
    public $timestamps = false;
    public $incrementing = false;
    protected $dates = ['deleted_at'];
    protected $casts = [
        'custom_fields' => 'array',
        'addresses' => 'array',
        'notes' => 'array'
    ];
    protected $guarded = [];
    protected $fillable = [
        'id',
        'customer_id',
        'email',
        'origin_id',
        'is_member',
        'first_name',
        'last_name',
        'phone',
        'phone_key',
        'custom_fields',
        'addresses',
        'notes',
        'is_sms_communication_enabled',
        'created_at',
    ];
    public function customers(){
        return $this->hasMany(Order::class, 'customer_id');
    }

}