<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'settings';
    
    protected $fillable = [
        'id',
        'user_id',
        'key',
        'value',
        'compound',
    ];
    
}
