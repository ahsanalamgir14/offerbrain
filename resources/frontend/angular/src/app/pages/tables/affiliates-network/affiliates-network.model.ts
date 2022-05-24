import { titleCase } from "title-case";
import { DatePipe } from '@angular/common'
const datePipe: DatePipe = new DatePipe('en-US')
let nf = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 });

export class Network {
  id: string;
  network_affiliate_id: string;
  network_id: string;
  name: string;
  gross_revenue: string;
  rebill_per: number;
  upsell_per: number;
  chargeback_per: number;
  refund_per: number;
  account_status: string;
  network_employee_id: string;
  internal_notes: string;
  has_notifications: string;
  network_traffic_source_id: string;
  account_executive_id: string;
  adress_id: string;
  default_currency_id: string;
  is_contact_address_enabled: string;
  enable_media_cost_tracking_links: string;
  time_created: string;
  time_saved: string;
  relationship: string;
  referrer_id: string;

  constructor(aff) {
    this.id = aff.id;
    this.network_affiliate_id = aff.network_affiliate_id;
    this.network_id = aff.network_id;
    this.name = aff.name;
    this.gross_revenue = '$' + nf.format(aff.gross_revenue);
    this.rebill_per = (aff.rebill_per / aff.total_count) * 100;
    this.upsell_per = (aff.upsell_per / aff.total_count) * 100;
    this.chargeback_per = (aff.chargeback_per / aff.total_count) * 100;
    this.refund_per = (aff.refund_per / aff.total_count) * 100;
    this.account_status = titleCase(aff.account_status);
    this.network_employee_id = aff.network_employee_id;
    this.internal_notes = aff.internal_notes;
    this.has_notifications = aff.has_notifications;
    this.network_traffic_source_id = aff.network_traffic_source_id;
    this.account_executive_id = aff.account_executive_id;
    this.adress_id = aff.adress_id;
    this.default_currency_id = aff.default_currency_id;
    this.is_contact_address_enabled = aff.is_contact_address_enabled;
    this.enable_media_cost_tracking_links = aff.enable_media_cost_tracking_links;
    this.time_created = aff.time_created;
    this.time_saved = aff.time_saved;
    this.relationship = aff.relationship;
    this.referrer_id = aff.referrer_id;
  }
}
