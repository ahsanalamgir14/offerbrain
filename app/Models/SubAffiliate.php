<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubAffiliate extends Model
{
    use HasFactory;
    protected $table = 'sub_affiliates';
    protected $fillable = [
        'affid',
        'user_id',
        'sub1',
        'sub2',
        'sub3',
        'impressions',
        'gross_clicks',
        'total_clicks',
        'unique_clicks',
        'duplicate_clicks',
        'invalid_clicks',
        'total_conversions',
        'CV',
        'invalid_conversions_scrub',
        'view_through_conversions',
        'events',
        'view_through_events',
        'CVR',
        'EVR',
        'CTR',
        'CPC',
        'CPA',
        'EPC',
        'RPC',
        'RPA',
        'payout',
        'revenue',
        'margin',
        'profit',
        'gross_sales',
        'ROAS',
        'gross_sales_vt',
        'RPM',
        'CPM',
        'avg_sale_value',
        'date',
    ];
}
