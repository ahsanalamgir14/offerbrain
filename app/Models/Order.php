<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'orders';
    protected $fillable = [
        'order_id',
        'user_id',
        // 'date_created_at',
        'created_by_employee_name',
        'billing_first_name',
        'billing_last_name',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_street_address',
        'acquisition_date',
        'acquisition_month',
        'acquisition_year',
        'c1',
        'trx_month',
        'affid',
        // 'billing_street_address2',
        // 'billing_email',
        // 'billing_telephone',
        // 'billing_postcode',
        // 'billing_state_id',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_street_address',
        'shipping_street_address2',
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
        'shipping_country',
        'shipping_telephone',
        'shipping_email',
        'shipping_method_name',
        'shippable',
        'shipping_amount',
        'order_sales_tax_amount',
        'order_total',
        'tracking_number',
        'cc_type',
        'campaign_id',
        'customer_id',
        'credit_card_number',
        'cc_expires',
        'prepaid_match',
        'gateway_id',
        'preserve_gateway',
        'gateway_descriptor',
        'processor_id',
        'ip_address',
        'decline_reason',
        'is_cascaded',
        'utm_info',
        'decline_reason_details',
        'shipping_date',
        'is_fraud',
        'is_chargeback',
        'chargeback_date',
        'is_rma',
        'rma_number',
        'rma_reason',
        'is_recurring',
        'retry_date',
        'auth_id',
        'hold_date',
        'is_void',
        'void_amount',
        'void_date',
        'is_refund',
        'refund_amount',
        'refund_date',
        'afid',
        'sid',
        'c2',
        'c3',
        'aid',
        'opt',
        'rebill_discount_percent',
        'billing_cycle',
        'parent_id',
        'main_product_id',
        'main_product_quantity',
        'order_confirmed',
        'order_confirmed_date',
        'is_blacklisted',
        'ancestor_id',
        'decline_salvage_discount_percent',
        'is_test_cc',
        'current_rebill_discount_percent',
        'amount_refunded_to_date',
        'shipping_id',
        'shipping_state_id',
        'affiliate',
        'cc_first_6',
        'cc_last_4',
        'cc_number',
        'cc_orig_first_6',
        'cc_orig_last_4',
        'check_account_last_4',
        'check_routing_last_4',
        'check_ssn_last_4',
        'check_transitnum',
        'child_id',
        'click_id',
        'coupon_discount_amount',
        'coupon_id',
        'created_by_user_name',
        'credit_applied',
        'customers_telephone',
        'email_address',
        'employeeNotes',
        'first_name',
        'is_3d_protected',
        'is_any_product_recurring',
        'last_name',
        'next_subscription_product',
        'next_subscription_product_id',
        'on_hold',
        'on_hold_by',
        'order_sales_tax',
        'order_status',
        'products',
        'promo_code',
        'recurring_date',
        'response_code',
        'return_reason',
        'stop_after_next_rebill',
        'sub_affiliate',
        'systemNotes',
        'time_stamp',
        'totals_breakdown',
        'transaction_id',
        'upsell_product_id',
        'upsell_product_quantity',
        'website_received',
        'website_sent',
        'ip_details',
        'parent_affId',
    ];
    protected $casts = [
        'ip_details' => 'object',
    ];

    public function get_order_ids($data)
    {
        foreach ($data as $order) {
            $order_ids[] = $order['order_id'];
        }
        return $order_ids;
    }
    public function getTableColumns()
    {
        return Schema::getColumnListing($this->table);
    }
    public function product()
    {
        return $this->hasOne(OrderProduct::class, 'order_id', 'order_id');
    }
    public function getProductsAttribute($value)
    {
        return unserialize($value);
    }
}
