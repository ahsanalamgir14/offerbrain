import { DatePipe } from '@angular/common'
const datePipe: DatePipe = new DatePipe('en-US')
let nf = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 });

export class TicketMonthly {
  month: string;
  year: string;
  initials: string;
  rebills: string;
  cycle_1_per: string;
  cycle_2: string;
  cycle_2_per: string;
  cycle_3_plus: string;
  cycle_3_plus_per: string;
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
  COGS: string;
  processing: string;
  cpa: string;
  cpa_avg: string;
  net: string;
  clv: string;

  constructor(ticket) {
    // this.date = datePipe.transform(ticket.date, 'MM-dd-yyyy');
    this.month = ticket.month;
    this.year = ticket.year;
    this.initials = ticket.initials;
    this.rebills = ticket.rebills;
    this.cycle_1_per = ticket.cycle_1_per + '%';
    this.cycle_2 = ticket.cycle_2;
    this.cycle_2_per = ticket.cycle_2_per + '%';
    this.cycle_3_plus = ticket.cycle_3_plus;
    this.cycle_3_plus_per = ticket.cycle_3_plus_per + '%';
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
    this.COGS = '$' + nf.format(ticket.COGS);
    this.cpa_avg = '$' + nf.format(ticket.cpa_avg);
    this.net = '$' + nf.format(ticket.net);
    this.clv = '$' + nf.format(ticket.clv);
  }
}
