import { DatePipe } from '@angular/common'
const datePipe: DatePipe = new DatePipe('en-US')
let nf = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 });

export class Mid {
    id: number;
    router_id: number;
    mid_group: string;
    mid_count: number;
    router_date_in: string;
    router_desc: string;
    mid_group_setting_id: number;
    mid_group_setting: number;
    is_three_d_routed: string;
    is_strict_preserve: string;
    created_on: string;
    campaign_id: string;
    gateway_id: string;
    initials: string;
    subscr: string;
    gateway_alias: string;
    global_monthly_cap: string;
    current_monthly_amount: string;
    processing_percent: string;
    decline_per: number;
    decline_orders: [];
    checked: boolean;
    approved_per: number;
    decline_count: number;
    refund_per: number;
    chargeback_per: number;
    chargeback_count: number;
    refund_count: number;
    void_count: number;
    void_per: number;
    // product_name: string;

    constructor(mid) {
        this.id = mid.id;
        this.router_id = mid.router_id;
        this.mid_group = mid.mid_group;
        this.mid_count = mid.mid_count;
        this.router_date_in = datePipe.transform(mid.router_date_in, 'MM-dd-yyyy');
        this.router_desc = mid.router_desc;
        this.mid_group_setting_id = mid.mid_group_setting_id;
        this.mid_group_setting = mid.mid_group_setting;
        this.is_three_d_routed = mid.is_three_d_routed;
        this.is_strict_preserve = mid.is_strict_preserve;
        this.created_on = datePipe.transform(mid.created_on, 'MM-dd-yyyy');
        this.campaign_id = mid.campaign_id;
        this.gateway_id = mid.gateway_id;
        this.initials = mid.initials;
        this.subscr = mid.subscr;
        this.gateway_alias = mid.gateway_alias;
        this.global_monthly_cap = '$' + nf.format(mid.global_monthly_cap);
        this.current_monthly_amount = mid.gross_revenue;
        this.processing_percent = mid.processing_percent + '%';
        this.decline_count = mid.decline_per;
        this.refund_count = mid.refund_per;
        this.refund_per = (mid.refund_per / mid.total_count)*100;
        this.void_count = mid.void_per;
        this.void_per = (mid.void_per / mid.total_count)*100;
        this.chargeback_count = mid.chargeback_per;
        this.chargeback_per = (mid.chargeback_per / mid.total_count)*100;
        this.decline_per = (mid.decline_per / mid.total_count)*100;
        this.approved_per = 100 - (mid.decline_per / mid.total_count)*100;
        this.decline_orders = mid.decline_orders;
        // this.product_name = mid.product_name;
        this.checked = false;
    }
}