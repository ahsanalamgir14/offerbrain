import { DatePipe } from '@angular/common';
const datePipe: DatePipe = new DatePipe('en-US');

export class Campaign {

    // Affiliate -- // missing in campaign builder
    // Prepaid be
    // Prepaid % --fe
    // C1 Decline % fe
    // C2 Decline % fe
    // C3 Decline % fe
    // Fulfilment $
    // CPA $
    // Throttle -- 
    // Throttle % --
    // CPA %
    // Net $
    // CLV $

    campaign_id: string;
    name: string;
    tracking_networks: string;
    initials: any;
    rebills: any;
    cycle_1_per: any;
    c1: any;
    c1_decline_per: any;
    cycle_2_per: any;
    c2: any;
    c2_decline_per: any;
    cycle_3_per: any;
    c3: any;
    c3_decline_per: any;
    avg_ticket: any;
    revenue: any;
    refund: any;
    refund_rate: any;
    CBs: any;
    CB_per: any;
    CB_currency: any;
    fulfillment: any;
    // processing: any;
    cpa: any;
    cpa_avg: any;
    net: any;
    clv: any;
    created_at: any;
    updated_at: any;

    constructor(campaign) {
        this.campaign_id = campaign.campaign_id;
        // this.created_at = datePipe.transform(campaign.created_at, 'MM-dd-yyyy');
        this.created_at = campaign.created_at;
        this.name = campaign.name;
        this.tracking_networks = JSON.parse(campaign.tracking_networks);
        this.net = (campaign.revenue - campaign.refund - campaign.CB_currency).toFixed(2);
        this.initials = campaign.initials;
        this.rebills = campaign.rebills;
        if (campaign.initials && campaign.initials != 0) {
            this.cycle_1_per = (campaign.c1 / campaign.initials * 100).toFixed(2);
            this.cycle_2_per = (campaign.c2 / campaign.initials * 100).toFixed(2);
            this.cycle_3_per = (campaign.c3 / campaign.initials * 100).toFixed(2);
            this.avg_ticket = '$' + (campaign.revenue / campaign.initials).toFixed(2);
            this.fulfillment = -campaign.initials;
            this.clv = (this.net / campaign.initials).toFixed(2);
        }
        this.c1 = campaign.c1;
        if (campaign.c1 && campaign.c1 != 0) {
            this.c1_decline_per = ((campaign.c1_declines / campaign.total_c1) * 100).toFixed(2);
            console.log('this.c1_decline_per:', this.c1_decline_per);
        }
        this.c2 = campaign.c2;
        if (campaign.c2 && campaign.c2 != 0) {
            this.c2_decline_per = (( campaign.c2_declines / campaign.total_c2) * 100).toFixed(2);
        }
        // if (campaign.rebills && campaign.rebills != 0) {
        //     this.cycle_2_per = (campaign.c2 / campaign.rebills * 100).toFixed(2);
        // }
        // if (campaign.c2 && campaign.c2 != 0) {
        //     this.cycle_3_per = (campaign.c3 / campaign.c2 * 100).toFixed(2);
        // }
        this.c3 = campaign.c3;
        if (campaign.c3 && campaign.c3 != 0) {
            this.c3_decline_per = ((campaign.c3_declines / campaign.total_c3) * 100).toFixed(2);
        }
        // if (campaign.c1_declines && campaign.c1_declines != 0) {
        //     this.c3_decline_per = campaign.c3_declines;
        // }
        this.revenue = '$' + campaign.revenue;
        if (campaign.revenue && campaign.revenue != 0) {
            this.refund_rate = ((campaign.refund / campaign.revenue) * 100).toFixed(2) + '%';
            this.CB_per = ((campaign.CBs / campaign.initials) * 100).toFixed(1) + '%';
            // this.CB_per = ((campaign.CBs / campaign.revenue) * 100 ).toFixed(2);
            // this.processing = -0.2 * campaign.revenue;
        }
        this.refund = '$' + campaign.refund;
        this.CBs = campaign.CBs;
        this.CB_currency = '$' + (campaign.CB_currency).toFixed(2);
        this.cpa = campaign.cpa;
        this.cpa_avg = campaign.cpa_avg;
    }
}