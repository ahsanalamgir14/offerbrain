import { DatePipe, formatDate } from '@angular/common';
const datePipe = new DatePipe('en-US');

export class CampaignView {

    id: any;
    month: any;
    year: any;
    initials: any;
    rebills: any;
    cycle_1_per: any;
    cycle_2: any;
    cycle_2_per: any;
    cycle_3_plus: any;
    cycle_3_plus_per: any;
    avg_ticket: any;
    revenue: any;
    refund: any;
    refund_rate: any;
    CBs: any;
    CB_per: any;
    CB_currency: any;
    fulfillment: any;
    processing: any;
    cpa: any;
    cpa_avg: any;
    net: any;
    clv: any;
    created_at: any;
    updated_at: any;

    constructor(data) {
        this.net = data.revenue - data.refund - data.CBs // - data.processing + data.cpa; // - data.COGS 
        this.id = data.id;
        this.month = data.month;
        this.year = data.year;
        this.initials = data.initials;
        this.rebills = data.rebills;
        if (data.initials && data.initials != 0) {
            this.cycle_1_per = (data.rebills / data.initials * 100).toFixed(2);
            this.avg_ticket = '$' + (data.revenue / data.initials).toFixed(2);
            this.fulfillment = -data.initials;
            this.clv = (this.net / data.initials).toFixed(2);
        }
        this.cycle_2 = data.cycle_2;
        if (data.rebills && data.rebills != 0) {
            this.cycle_2_per = (data.cycle_2 / data.rebills * 100).toFixed(2);
        }
        if (data.cycle_2 && data.cycle_2 != 0) {
            this.cycle_3_plus_per = (data.cycle_3_plus / data.cycle_2 * 100).toFixed(2);
        }
        this.cycle_3_plus = data.cycle_3_plus;
        this.revenue = '$' + data.revenue;
        if (data.revenue && data.revenue != 0) {
            this.refund_rate = ((data.refund / data.revenue) * 100).toFixed(2) + '%';
            this.CB_per = data.CBs / data.revenue;
            this.processing = -0.2 * data.revenue;
        }
        this.refund = '$' + data.refund;
        this.CBs = data.CBs;
        this.CB_currency = data.CB_currency;
        this.cpa = data.cpa;
        this.cpa_avg = data.cpa_avg;
        this.net = data.net;
        this.created_at = datePipe.transform(data.created_at, 'Y-m-d');
        this.updated_at = data.updated_at;
    }
}