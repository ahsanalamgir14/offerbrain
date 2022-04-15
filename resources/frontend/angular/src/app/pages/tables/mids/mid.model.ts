import { DatePipe } from '@angular/common'
const datePipe: DatePipe = new DatePipe('en-US')
let nf = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 });

export class Mid {
    id: number;
    router_id: number;
    mid_group_name: string;
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
    gateway_alias: string;
    global_monthly_cap: string;
    current_monthly_amount: string;
    processing_percent: string;
    decline_per: number;
    decline_orders: [];
    checked: boolean;
    approved_per: number;
    decline_count: number;

    constructor(mid) {
        this.id = mid.id;
        this.router_id = mid.router_id;
        
        this.mid_group_name = mid.group_name;
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
        this.gateway_alias = mid.gateway_alias;
        this.global_monthly_cap = '$' + nf.format(mid.global_monthly_cap);
        this.current_monthly_amount = mid.current_monthly_amount;
        this.processing_percent = mid.processing_percent + '%';
        this.decline_count = mid.decline_per;
        this.decline_per = (mid.decline_per / mid.total_count)*100;
        this.approved_per = 100 - (mid.decline_per / mid.total_count)*100;
        this.decline_orders = mid.decline_orders;
        this.checked = false;
    }

    // numberWithCommas(x) {
    //     var parts = x.toString().split(".");
    //     parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    //     return parts.join(".");
    // }
}