<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldNetwork extends Model
{
    use HasFactory;
    protected $table = 'old_networks';
    protected $casts = [
        'relationship' => 'object',
    ];
    protected $guarded = [];
    protected $fillable = [
        'network_id',
        'customer_id',
        'name',
        'identifier',
        'account_status',
        'displayed_name',
        'is_show_name',
        'timezone_id',
        'language_id',
        'currency_id',
        'logo_image_url',
        'favicon_image_url',
        'support_email',
        'email_background_logo_color',
        'time_created',
        'time_saved',
        'relationship',
    ];
}
