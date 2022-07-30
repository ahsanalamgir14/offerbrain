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
        'tracking_campaign_ids' => 'array',
        'tracking_networks' => 'array',
        'tracking_network_ids' => 'array',
        'upsell_products' => 'array',
        'upsell_product_ids' => 'array',
        'downsell_products' => 'array',
        'downsell_product_ids' => 'array',
        'cycle_products' => 'array',
        'cycle_product_ids' => 'object',
        'cogs_track' => 'boolean',
        'cpa_track' => 'boolean',
        'third_party_track' => 'boolean',
    ];
    protected $fillable = [
        // 'id',
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
        'tracking_campaign_ids',
        'tracking_networks',
        'tracking_network_ids',
        'no_of_upsells',
        'no_of_downsells',
        'upsell_products',
        'upsell_product_ids',
        'downsell_products',
        'downsell_product_ids',
        'no_of_cycles',
        'cycle_product_ids',
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

    public function setTrackingCampaignIdsAttribute($value)
    {
        $tracking_campaigns = json_decode($this->attributes['tracking_campaigns']);
        if (is_array($tracking_campaigns)) {
            $tracking_campaign_ids = array_column($tracking_campaigns, 'campaign_id');
            $tracking_campaign_ids = array_map('trim', $tracking_campaign_ids);
            $tracking_campaign_ids = implode(',', $tracking_campaign_ids);
            return $this->attributes['tracking_campaign_ids'] = $tracking_campaign_ids;
        }
    }

    public function setTrackingNetworkIdsAttribute($value)
    {
        $tracking_networks = json_decode($this->attributes['tracking_networks']);
        if (is_array($tracking_networks)) {
            $tracking_network_ids = array_column($tracking_networks, 'network_affiliate_id');
            $integer_tracking_network_ids = array_map('trim', $tracking_network_ids);
            $integer_tracking_network_ids = implode(',', $integer_tracking_network_ids);
            return $this->attributes['tracking_network_ids'] = $integer_tracking_network_ids;
        }
    }

    public function setUpsellProductIdsAttribute($value)
    {
        $upsell_products = json_decode($this->attributes['upsell_products']);
        if (is_array($upsell_products)) {
            $upsell_product_ids = array_column($upsell_products, 'product_id');
            return $this->attributes['upsell_product_ids'] = json_encode($upsell_product_ids);
        }
    }

    public function setDownsellProductIdsAttribute($value)
    {
        $downsell_products = json_decode($this->attributes['downsell_products']);
        if (is_array($downsell_products)) {
            $downsell_product_ids = array_column($downsell_products, 'product_id');
            return $this->attributes['downsell_product_ids'] = json_encode($downsell_product_ids);
        }
    }

    public function setCycleProductIdsAttribute($value)
    {
        $cycle_products = json_decode($this->attributes['cycle_products']);
        if (is_array($cycle_products)) {
            $cycle_product_ids = array_column($cycle_products, 'product_id');
            foreach ($cycle_product_ids as $i => $id) {
                $string_ids[$i] = (string)$id;
            }
            return $this->attributes['cycle_product_ids'] = json_encode($string_ids);
        }
    }
}
