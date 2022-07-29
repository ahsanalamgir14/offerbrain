<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id()->index();
            $table->text('order_id')->nullable()->default(null);
            // $table->timestamp('date_created_at')->nullable()->default(null);
            $table->text('created_by_employee_name')->nullable()->default(null);
            $table->text('billing_first_name')->nullable()->default(null);
            $table->text('billing_last_name')->nullable()->default(null);
            $table->text('billing_city')->nullable()->default(null);
            $table->text('billing_state')->nullable()->default(null);
            $table->text('billing_country')->nullable()->default(null);
            $table->text('billing_street_address')->nullable()->default(null);
            $table->timestamp('acquisition_date')->nullable()->default(null)->comment('created_at');
            $table->text('acquisition_month')->nullable()->default(null)->comment('derived-acquisition_date');
            $table->text('acquisition_year')->nullable()->default(null)->comment('derived-acquisition_date');
            $table->text('c1')->nullable()->default(null)->comment('PubID');
            $table->text('trx_month')->nullable()->default(null)->comment('derived-acquisition_date');
            $table->text('affid')->nullable()->default(null)->comment('network');
            $table->text('parent_affId')->nullable()->default(null)->comment('Parent Affiliate ID');
            // $table->text('billing_street_address2')->nullable()->default(null);
            // $table->text('billing_email')->nullable()->default(null);
            // $table->text('billing_telephone')->nullable()->default(null);
            // $table->text('billing_postcode')->nullable()->default(null);
            // $table->text('billing_state_id')->nullable()->default(null);
            $table->text('shipping_first_name')->nullable()->default(null);
            $table->text('shipping_last_name')->nullable()->default(null);
            $table->text('shipping_street_address')->nullable()->default(null);
            $table->text('shipping_street_address2')->nullable()->default(null);
            $table->text('shipping_city')->nullable()->default(null);
            $table->text('shipping_state')->nullable()->default(null);
            $table->text('shipping_postcode')->nullable()->default(null);
            $table->text('shipping_country')->nullable()->default(null);
            $table->text('shipping_telephone')->nullable()->default(null);
            $table->text('shipping_email')->nullable()->default(null);
            $table->text('shipping_method_name')->nullable()->default(null);
            $table->text('shippable')->nullable()->default(null);
            $table->text('shipping_amount')->nullable()->default(null);
            $table->text('order_sales_tax_amount')->nullable()->default(null);
            $table->text('order_total')->nullable()->default(null);
            $table->text('tracking_number')->nullable()->default(null);
            $table->text('cc_type')->nullable()->default(null);
            $table->text('campaign_id')->nullable()->default(null);
            $table->text('customer_id')->nullable()->default(null);
            $table->text('credit_card_number')->nullable()->default(null);
            $table->text('cc_expires')->nullable()->default(null);
            $table->text('prepaid_match')->nullable()->default(null);
            $table->text('gateway_id')->nullable()->default(null);
            $table->text('preserve_gateway')->nullable()->default(null);
            $table->text('gateway_descriptor')->nullable()->default(null)->index();
            $table->text('processor_id')->nullable()->default(null);
            $table->text('ip_address')->nullable()->default(null);
            $table->text('decline_reason')->nullable()->default(null);
            $table->text('is_cascaded')->nullable()->default(null);
            $table->text('utm_info')->nullable()->default(null);
            $table->text('decline_reason_details')->nullable()->default(null);
            $table->text('shipping_date')->nullable()->default(null);
            $table->text('is_fraud')->nullable()->default(null);
            $table->text('is_chargeback')->nullable()->default(null);
            $table->text('chargeback_date')->nullable()->default(null);
            $table->text('is_rma')->nullable()->default(null);
            $table->text('rma_number')->nullable()->default(null);
            $table->text('rma_reason')->nullable()->default(null);
            $table->text('is_recurring')->nullable()->default(null);
            $table->text('retry_date')->nullable()->default(null);
            $table->text('auth_id')->nullable()->default(null);
            $table->text('hold_date')->nullable()->default(null);
            $table->text('is_void')->nullable()->default(null);
            $table->text('void_amount')->nullable()->default(null);
            $table->text('void_date')->nullable()->default(null);
            $table->text('is_refund')->nullable()->default(null);
            $table->text('refund_amount')->nullable()->default(null);
            $table->text('refund_date')->nullable()->default(null);
            $table->text('afid')->nullable()->default(null);
            $table->text('sid')->nullable()->default(null);
            $table->text('c2')->nullable()->default(null);
            $table->text('c3')->nullable()->default(null);
            $table->text('aid')->nullable()->default(null);
            $table->text('opt')->nullable()->default(null);
            $table->text('rebill_discount_percent')->nullable()->default(null);
            $table->text('billing_cycle')->nullable()->default(null);
            $table->text('parent_id')->nullable()->default(null);
            $table->text('main_product_id')->nullable()->default(null);
            $table->text('main_product_quantity')->nullable()->default(null);
            $table->text('order_confirmed')->nullable()->default(null);
            $table->text('order_confirmed_date')->nullable()->default(null);
            $table->text('is_blacklisted')->nullable()->default(null);
            $table->text('ancestor_id')->nullable()->default(null);
            $table->text('decline_salvage_discount_percent')->nullable()->default(null);
            $table->text('is_test_cc')->nullable()->default(null);
            $table->text('current_rebill_discount_percent')->nullable()->default(null);
            $table->text('amount_refunded_to_date')->nullable()->default(null);
            $table->text('shipping_id')->nullable()->default(null);
            $table->text('shipping_state_id')->nullable()->default(null);
            $table->text('affiliate')->nullable()->default(null);
            $table->text('cc_first_6')->nullable()->default(null);
            $table->text('cc_last_4')->nullable()->default(null);
            $table->text('cc_number')->nullable()->default(null);
            $table->text('cc_orig_first_6')->nullable()->default(null);
            $table->text('cc_orig_last_4')->nullable()->default(null);
            $table->text('check_account_last_4')->nullable()->default(null);
            $table->text('check_routing_last_4')->nullable()->default(null);
            $table->text('check_ssn_last_4')->nullable()->default(null);
            $table->text('check_transitnum')->nullable()->default(null);
            $table->text('child_id')->nullable()->default(null);
            $table->text('click_id')->nullable()->default(null);
            $table->text('coupon_discount_amount')->nullable()->default(null);
            $table->text('coupon_id')->nullable()->default(null);
            $table->text('created_by_user_name')->nullable()->default(null);
            $table->text('credit_applied')->nullable()->default(null);
            $table->text('customers_telephone')->nullable()->default(null);
            $table->text('email_address')->nullable()->default(null);
            $table->text('employeeNotes')->nullable()->default(null);
            $table->text('first_name')->nullable()->default(null);
            $table->text('is_3d_protected')->nullable()->default(null);
            $table->text('is_any_product_recurring')->nullable()->default(null);
            $table->text('last_name')->nullable()->default(null);
            $table->text('next_subscription_product')->nullable()->default(null);
            $table->text('next_subscription_product_id')->nullable()->default(null);
            $table->text('on_hold')->nullable()->default(null);
            $table->text('on_hold_by')->nullable()->default(null);
            $table->text('order_sales_tax')->nullable()->default(null);
            $table->text('order_status')->nullable()->default(null);
            $table->text('products')->nullable()->default(null);
            $table->text('promo_code')->nullable()->default(null);
            $table->text('recurring_date')->nullable()->default(null);
            $table->text('response_code')->nullable()->default(null);
            $table->text('return_reason')->nullable()->default(null);
            $table->text('stop_after_next_rebill')->nullable()->default(null);
            $table->text('sub_affiliate')->nullable()->default(null);
            $table->text('systemNotes')->nullable()->default(null);
            $table->text('time_stamp')->nullable()->default(null);
            $table->text('totals_breakdown')->nullable()->default(null);
            $table->text('transaction_id')->nullable()->default(null);
            $table->text('upsell_product_id')->nullable()->default(null);
            $table->text('upsell_product_quantity')->nullable()->default(null);
            $table->text('website_received')->nullable()->default(null);
            $table->text('website_sent')->nullable()->default(null);
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
