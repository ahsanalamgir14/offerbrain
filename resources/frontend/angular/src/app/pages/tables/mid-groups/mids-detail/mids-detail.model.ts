export class Mid {
    id: number;
    router_id: number;
    mid_group: string;
    router_date_in: Date;
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
    constructor(mid) {
        this.id = mid.id;
        this.router_id = mid.router_id;
        this.mid_group = mid.mid_group;
        this.router_date_in = mid.router_date_in;
        this.router_desc = mid.router_desc;
        this.mid_group_setting_id = mid.mid_group_setting_id;
        this.mid_group_setting = mid.mid_group_setting;
        this.is_three_d_routed = mid.is_three_d_routed;
        this.is_strict_preserve = mid.is_strict_preserve;
        this.created_on = mid.created_on;
        this.campaign_id = mid.campaign_id;
        this.gateway_id = mid.gateway_id;
        this.gateway_alias = mid.gateway_alias;
        this.global_monthly_cap = mid.global_monthly_cap;
        this.current_monthly_amount = mid.current_monthly_amount;
        this.processing_percent = mid.processing_percent;
    }
}