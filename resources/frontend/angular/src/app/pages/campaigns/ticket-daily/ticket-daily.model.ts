import { DatePipe } from '@angular/common'
const datePipe: DatePipe = new DatePipe('en-US')

export class TicketDaily {
  date: string;
  initials: number;
  decline: number;
  decline_per: number;
  scrub_per: number;
  EOT_declines: number;
  EOT_approved: number;
  EOT_per: number;

  constructor(ticket) {
    // this.date = datePipe.transform(ticket.date, 'MM-dd-yyyy');
    this.date = ticket.date;
    this.initials = ticket.initials;
    this.decline = ticket.decline;
    this.decline_per = ticket.decline_per;
    this.scrub_per = ticket.scrub_per;
    this.EOT_declines = ticket.EOT_declines;
    this.EOT_approved = ticket.EOT_approved;
    this.EOT_per = ticket.EOT_per;

  }

  get decline_percentage() {
    let decline_percentage = '';

    if (this.decline_per) {
      decline_percentage = this.decline_per + '%';
    }

    return decline_percentage;
  }
}
