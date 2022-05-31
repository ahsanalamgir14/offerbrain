<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketMonthly extends Model
{
    use HasFactory;
    protected $table = "sticket_monthly";

    protected $fillable = [
        'month',
        'year',
        'initials',
        'rebills',
        'cycle_1_per',
        'cycle_2',
        'cycle_2_per',
        'cycle_3_plus',
        'cycle_3_plus_per',
        'avg_day',
        'filled_per',
        'avg_ticket',
        'revenue',
        'refund',
        'refund_rate',
        'CBs',
        'CB_per',
        'CB_currency',
        'fulfillment',
        'processing',
        'cpa',
        'cpa_avg',
        'net',
        'clv',
    ];
}
