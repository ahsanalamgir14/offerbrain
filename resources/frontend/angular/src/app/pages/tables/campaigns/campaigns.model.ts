import { DatePipe } from '@angular/common';
const datePipe: DatePipe = new DatePipe('en-US');

export class Campaign {

    id: string;
    campaign_id: string;
    gateway_id: string;
    is_active: string;
    tax_provider_id: string;
    data_verification_provider_id: string;
    site_url: string;
    is_archived: string;
    prepaid_blocked: string;
    is_custom_price_allowed: string;
    is_avs_enabled: string;
    is_collections_enabled: string;
    created_at: string;
    updated_at: string;
    archived_at: string;
    name: string;
    description: string;
    pre_auth_amount: string;
    creator: string;
    updator: string;
    countries: string;
    fulfillment_id: string;
    check_provider_id: string;
    membership_provider_id: string;
    call_confirm_provider_id: string;
    chargeback_provider_id: string;
    prospect_provider_id: string;
    email_provider_id: string;
    offers: string;
    channel: string;
    payment_methods: string;
    gateway: string;
    alternative_payments: string;
    shipping_profiles: string;
    return_profiles: string;
    postback_profiles: string;
    coupon_profiles: string;
    fraud_providers: string;
    volume_discounts: string;

    constructor(campaign) {
        this.id = campaign.id;
        this.campaign_id = campaign.campaign_id;
        this.gateway_id = campaign.gateway_id;
        this.is_active = campaign.is_active;
        this.tax_provider_id = campaign.tax_provider_id;
        this.data_verification_provider_id = campaign.data_verification_provider_id;
        this.site_url = campaign.site_url;
        this.is_archived = campaign.is_archived;
        this.is_archived = campaign.is_archived;
        this.is_custom_price_allowed = campaign.is_custom_price_allowed;
        this.is_avs_enabled = campaign.is_avs_enabled;
        this.is_collections_enabled = campaign.is_collections_enabled;
        this.created_at = datePipe.transform(campaign.created_at, 'MM-dd-yyyy');
        this.updated_at = campaign.updated_at;
        this.archived_at = campaign.archived_at;
        this.name = campaign.name;
        if (campaign.description) {
            this.description = campaign.description;
        } else {
            this.description = '-';
        }
        this.pre_auth_amount = campaign.pre_auth_amount;
        this.creator = campaign.creator;
        this.updator = campaign.updator;
        this.countries = campaign.countries;
        this.fulfillment_id = campaign.fulfillment_id;
        this.check_provider_id = campaign.check_provider_id;
        this.membership_provider_id = campaign.membership_provider_id;
        this.call_confirm_provider_id = campaign.call_confirm_provider_id;
        this.chargeback_provider_id = campaign.chargeback_provider_id;
        this.prospect_provider_id = campaign.prospect_provider_id;
        this.email_provider_id = campaign.email_provider_id;
        this.offers = campaign.offers;
        this.channel = campaign.channel;
        this.payment_methods = campaign.payment_methods;
        this.gateway = campaign.gateway;
        this.alternative_payments = campaign.alternative_payments;
        this.shipping_profiles = campaign.shipping_profiles;
        this.return_profiles = campaign.return_profiles;
        this.postback_profiles = campaign.postback_profiles;
        this.coupon_profiles = campaign.coupon_profiles;
        this.fraud_providers = campaign.fraud_providers;
        this.volume_discounts = campaign.volume_discounts;

    }
}