<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class Campaign extends Model
{
    // use HasApiTokens, HasFactory, Notifiable;

    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'campaigns';
    protected $casts = [
        'creator' => 'array',
        'updator' => 'array',
        'countries' => 'array',
        'offers' => 'array',
        'channel' => 'array',
        'payment_methods' => 'array',
        'gateway' => 'array',
        'alternative_payments' => 'array',
        'shipping_profiles' => 'array',
        'return_profiles' => 'array',
        'postback_profiles' => 'array',
        'coupon_profiles' => 'array',
        'fraud_providers' => 'array',
        'volume_discounts' => 'array',
        //added only for OfferBrain campaigns
        'tracking_campaigns' => 'array',
        'tracking_networks' => 'array',
        'upsell_products' => 'array',
        'downsell_products' => 'array',
        'cycle_products' => 'array',
        'cogs_track' => 'boolean',
        'cpa_track' => 'boolean',
        'third_party_track' => 'boolean',
    ];
    protected $fillable = [
        'id',
        'campaign_id',
        'gateway_id',
        'user_id',
        'is_active',
        'tax_provider_id',
        'data_verification_provider_id',
        'site_url',
        'is_archived',
        'is_archivedprepaid_blocked',
        'is_custom_price_allowed',
        'is_avs_enabled',
        'is_collections_enabled',
        'created_at',
        'updated_at',
        'archived_at',
        'name',
        'description',
        'pre_auth_amount',
        'creator',
        'updator',
        'countries',
        'fulfillment_id',
        'check_provider_id',
        'membership_provider_id',
        'call_confirm_provider_id',
        'chargeback_provider_id',
        'prospect_provider_id',
        'email_provider_id',
        'offers',
        'channel',
        'payment_methods',
        'gateway',
        'alternative_payments',
        'shipping_profiles',
        'return_profiles',
        'postback_profiles',
        'coupon_profiles',
        'fraud_providers',
        'volume_discounts',
        'campaign_type',
        'tracking_campaigns',
        'tracking_networks',
        'no_of_upsells',
        'no_of_downsells',
        'upsell_products',
        'downsell_products',
        'no_of_cycles',
        'cycle_products',
        'cogs_track',
        'cpa_track',
        'third_party_track',
    ];

    public function getCogsTrackAttribute($value)
    {
        return $value == 1 ? 1 : 0;
    }
    public function getCpaTrackAttribute($value)
    {
        return $value == 1 ? 1 : 0;
    }
    public function getThirdPartyTrackAttribute($value)
    {
        return $value == 1 ? 1 : 0;
    }
}
