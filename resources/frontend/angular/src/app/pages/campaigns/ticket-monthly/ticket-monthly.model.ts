import { DatePipe } from '@angular/common'
const datePipe: DatePipe = new DatePipe('en-US')

export class TicketMonthly {
  month: string;
  year: string;
  initials: string;
  rebills: string;
  cycle_1_per: string;
  avg_day: string;
  filled_per: string;
  avg_ticket: string;
  revenue: string;
  refund: string;
  refund_rate: string;
  CBs: string;
  CB_per: string;
  CB_currency: string;
  fulfillment: string;
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
    this.cycle_1_per = ticket.cycle_1_per;
    this.avg_day = ticket.avg_day;
    this.filled_per = ticket.filled_per;
    this.avg_ticket = ticket.avg_ticket;
    this.revenue = ticket.revenue;
    this.refund = ticket.refund;
    this.refund_rate = ticket.refund_rate;
    this.CBs = ticket.CBs;
    this.CB_per = ticket.CB_per;
    this.CB_currency = ticket.CB_currency;
    this.fulfillment = ticket.fulfillment;
    this.processing = ticket.processing;
    this.cpa = ticket.cpa;
    this.cpa_avg = ticket.cpa_avg;
    this.net = ticket.net;
    this.clv = ticket.clv;
  }
}
