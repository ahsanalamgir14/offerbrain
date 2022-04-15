<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    protected $table = 'profiles';
    protected $casts = [
        'global_fields' => 'object',
        'account_fields' => 'object',
        'fee_fields' => 'object',
    ];
    protected $fillable = [
        'profile_id',
        'account_id',
        'alias',
        'account_name',
        'currency_id',
        'currency_title',
        'currency_code',
        'currency_symbol_left',
        'global_fields',
        'account_fields',
        'fee_fields'
    ];

    function getGlobalFieldsAttribute($value){
        return json_decode($value);
    }
}
