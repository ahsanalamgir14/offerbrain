<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'invoice_number',
        'mid_group_id',
        'amount',
        'created_at',
        'updated_at',
    ];
}
