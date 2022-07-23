<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuickAccounts extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_id',
        'account_name',
    ];
}
