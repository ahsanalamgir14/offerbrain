import { DatePipe } from '@angular/common'
const datePipe: DatePipe = new DatePipe('en-US')
let nf = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 });

export class TicketWeekly {
  week: string;
  volume: string;
  rebills: string;
  rebill_per: string;
  avg_day: string;
  filled_per: string;
  avg_ticket: string;
  revenue: string;
  refund: string;
  refund_rate: string;
  CBs: string;
  CB_per: string;
  CB_currency: string;
  // fulfillment: string;
  // COGS: string;
  processing: string;
  cpa: string;
  cpa_avg: string;
  net: string;
  clv: string;

  constructor(ticket) {
    this.week = ticket.week;
    this.volume = ticket.volume;
    this.rebills = ticket.rebills;
    this.rebill_per = ticket.rebill_per + '%';
    this.avg_day = ticket.avg_day;
    this.filled_per = ticket.filled_per;
    this.avg_ticket = '$' + nf.format(ticket.avg_ticket);
    this.revenue = '$' + nf.format(ticket.revenue);
    this.refund = '$' + nf.format(ticket.refund);
    this.refund_rate = ticket.refund_rate + '%';
    this.CBs = ticket.CBs;
    this.CB_per = ticket.CB_per + '%';
    this.CB_currency = '$' + nf.format(ticket.CB_currency);
    // this.fulfillment = ticket.fulfillment;
    this.processing = '-$' + nf.format(ticket.processing);
    this.cpa = '$' + nf.format(ticket.cpa);
    // this.COGS = '$' + nf.format(ticket.COGS);
    this.cpa_avg = '$' + nf.format(ticket.cpa_avg);
    this.net = '$' + nf.format(ticket.net);
    this.clv = '$' + nf.format(ticket.clv);
  }
}
