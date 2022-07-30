<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MidGroup extends Model
{
    use HasFactory, softDeletes;
    protected $guarded = [];
    protected $table = 'mid_groups';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'group_name',
        'user_id',
        // 'assigned_mids',
        // 'gross_revenue',
        'bank_per',
        'status',
        // 'target_bank_balance',
    ];
}