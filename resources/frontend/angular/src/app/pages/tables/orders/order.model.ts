import { DatePipe } from '@angular/common'
const datePipe: DatePipe = new DatePipe('en-US')

export class Order {
  order_id: number;
  id: number;
  created_by_employee_name: string;
  billing_first_name: string;
  billing_last_name: string;
  billing_city: string;
  billing_state: string;
  billing_country: string;
  billing_street_address: string;
  acquisition_month: string;
  // shipping_first_name: string;
  // shipping_last_name: string;
  // shipping_street_address: string;
  // shipping_street_address2: string;
  // shipping_city: string;
  // shipping_state: string;
  // shipping_postcode: number;
  // shipping_country: string;
  // shipping_telephone: string;
  // shipping_email: string;
  // shipping_method_name: string;
  // shippable: number;
  // shipping_amount: number;
  order_sales_tax_amount: number;
  order_total: number;
  // tracking_number:string;
  cc_type: string;
  // campaign_id: number;
  // customer_id: string;
  // credit_card_number: string;
  // cc_expires: number;
  // prepaid_match: string;
  // gateway_id: number;
  // preserve_gateway: number;
  // gateway_descriptor: string;
  // processor_id: string;
  // ip_address: string;
  decline_reason: string;
  is_cascaded: number;
  decline_reason_details: string;
  // shipping_date: string;
  is_fraud: number;
  is_chargeback: number;
  chargeback_date: string;
  is_rma: number;
  rma_number: string;
  rma_reason: string;
  is_recurring: number;
  // retry_date: string;
  // auth_id: number;
  // hold_date: string;
  is_void: string;
  void_amount: number;
  void_date: string;
  is_refund: string;
  refund_amount: number;
  refund_date: string;
  // afid: string;
  // sid: string;
  affid: string;
  c1: string;
  // c2: string;
  // c3: string;
  // aid: string;
  // opt:string;
  // rebill_discount_percent: number;
  // billing_cycle: number;
  // parent_id: number;
  // main_product_id: number;
  // main_product_quantity: number;
  order_confirmed: string;
  order_confirmed_date: string;
  acquisition_date: string;
  is_blacklisted: number;
  // ancestor_id: number;
  // decline_salvage_discount_percent: number;
  // is_test_cc: number;
  // current_rebill_discount_percent:string;
  // amount_refunded_to_date: number;
  // shipping_id: number;
  // shipping_state_id: string;
  // affiliate: string;
  // cc_first_6: number;
  // cc_last_4: number;
  // cc_number: number;
  // cc_orig_first_6: number;
  // cc_orig_last_4: number;
  // check_account_last_4: string;
  // check_routing_last_4: string;
  // check_ssn_last_4: string;
  // check_transitnum: string;
  // child_id: string;
  // click_id: string;
  // coupon_discount_amount: number;
  coupon_id: number;
  created_by_user_name: string;
  // credit_applied: number;
  // customers_telephone: string;
  // email_address: string;
  // employeeNotes: null;
  // first_name: string;
  // is_3d_protected: string;
  // is_any_product_recurring: number;
  // last_name: string;
  // next_subscription_product: string;
  // next_subscription_product_id:number;
  // on_hold: number;
  // on_hold_by: string;
  order_sales_tax: number;
  order_status: number;
  // products: null;
  promo_code:string;
  recurring_date: string;
  response_code: number;
  return_reason: string;
  // stop_after_next_rebill: number;
  // sub_affiliate: string;
  // systemNotes: null;
  // time_stamp: string;
  // totals_breakdown: null;
  transaction_id: string;
  // upsell_product_id: string;
  // upsell_product_quantity: string;
  // website_received:string;
  // website_sent:string;
  count:string;
  total_pages:string;
  pageno:string;
  rows_per_page:string;

  constructor(order) {
    this.id = order.id;
    this.order_id = order.order_id;
    this.created_by_employee_name = order.created_by_employee_name;
    this.billing_first_name = order.billing_first_name;
    this.billing_last_name = order.billing_last_name;
    this.billing_city = order.billing_city;
    this.billing_state = order.billing_state;
    this.billing_country = order.billing_country;
    this.billing_street_address = order.billing_street_address;
    this.acquisition_month = order.acquisition_month;
    // this.shipping_first_name = order.shipping_first_name;
    // this.shipping_last_name = order.shipping_last_name;
    // this.shipping_street_address = order.shipping_street_address;
    // this.shipping_street_address2 = order.shipping_street_address2;
    // this.shipping_city = order.shipping_city;
    // this.shipping_state = order.shipping_state;
    // this.shipping_postcode = order.shipping_postcode;
    // this.shipping_country = order.shipping_country;
    // this.shipping_telephone = order.customers_telephone;
    // this.shipping_email = order.email_address;
    // this.shipping_method_name = order.shipping_method_name;
    // this.shippable = order.shippable;
    this.order_sales_tax_amount = order.order_sales_tax_amount;
    this.order_total = order.order_total;
    // this.tracking_number = order.tracking_number;
    this.cc_type = order.cc_type;
    // this.campaign_id = order.campaign_id;
    // this.customer_id = order.customer_id;
    // this.credit_card_number = order.credit_card_number;
    // this.cc_expires = order.cc_expires;
    // this.prepaid_match = order.prepaid_match;
    // this.gateway_id = order.gateway_id;
    // this.preserve_gateway = order.preserve_gateway;
    // this.gateway_descriptor = order.gateway_descriptor;
    // this.processor_id = order.processor_id;
    // this.ip_address = order.ip_address;
    this.decline_reason = order.decline_reason;
    this.is_cascaded = order.is_cascaded;
    this.decline_reason_details = order.decline_reason_details;
    // this.shipping_date = order.shipping_date;
    this.is_fraud = order.is_fraud;
    this.is_chargeback = order.is_chargeback;
    this.chargeback_date = order.chargeback_date;
    this.is_rma = order.is_rma;
    this.rma_number = order.rma_number;
    this.rma_reason = order.rma_reason;
    this.is_recurring = order.is_recurring;
    // this.retry_date = order.retry_date;
    // this.auth_id = order.auth_id;
    // this.hold_date = order.hold_date;
    this.is_void = order.is_void;
    this.void_amount = order.void_amount;
    this.void_date = order.void_date;
    this.is_refund = order.is_refund;
    this.refund_amount = order.refund_amount;
    this.refund_date = order.refund_date;
    // this.afid = order.afid;
    // this.sid = order.sid;
    this.affid = order.affid;
    this.c1 = order.c1;
    // this.c2 = order.c2;
    // this.c3 = order.c3;
    // this.aid = order.aid;
    // this.opt = order.opt;
    // this.rebill_discount_percent = order.rebill_discount_percent;
    // this.billing_cycle = order.billing_cycle;
    // this.parent_id = order.parent_id;
    // this.main_product_id = order.main_product_id;
    // this.main_product_quantity = order.main_product_quantity;
    this.order_confirmed = order.order_confirmed;
    this.order_confirmed_date = order.order_confirmed_date;
    this.acquisition_date = datePipe.transform(order.acquisition_date, 'MM-dd-yyyy');;
    this.is_blacklisted = order.is_blacklisted;
    // this.ancestor_id = order.ancestor_id;
    // this.decline_salvage_discount_percent = order.decline_salvage_discount_percent;
    // this.is_test_cc = order.is_test_cc;
    // this.shipping_amount = order.shipping_amount;    
    // this.current_rebill_discount_percent = order.current_rebill_discount_percent;
    // this.amount_refunded_to_date = order.amount_refunded_to_date;
    // this.shipping_id = order.shipping_id;
    // this.shipping_state_id = order.shipping_state_id;
    // this.affiliate = order.affiliate;
    // this.cc_first_6 = order.cc_first_6;
    // this.cc_last_4 = order.cc_last_4;
    // this.cc_number = order.cc_number;
    // this.cc_orig_first_6 = order.cc_orig_first_6;
    // this.cc_orig_last_4 = order.cc_orig_last_4;
    // this.check_account_last_4 = order.check_account_last_4;
    // this.check_routing_last_4 = order.check_routing_last_4;
    // this.check_ssn_last_4 = order.check_ssn_last_4;
    // this.check_transitnum = order.check_transitnum;
    // this.child_id = order.child_id;    
    // this.click_id = order.click_id;    
    // this.coupon_discount_amount = order.coupon_discount_amount;
    this.coupon_id = order.coupon_id;
    this.created_by_user_name = order.created_by_user_name;
    // this.credit_applied = order.credit_applied;
    // this.customers_telephone = order.customers_telephone;
    // this.email_address = order.email_address;
    // this.employeeNotes = order.employeeNotes;
    // this.first_name = order.first_name;
    // this.is_3d_protected = order.is_3d_protected;
    // this.is_any_product_recurring = order.is_any_product_recurring;
    // this.last_name = order.last_name;
    // this.next_subscription_product = order.next_subscription_product;
    // this.next_subscription_product_id = order.next_subscription_product_id;
    // this.on_hold = order.on_hold;
    // this.on_hold_by = order.on_hold_by;
    this.order_sales_tax = order.order_sales_tax;
    this.order_status = order.order_status;
    // this.products = order.products;
    this.promo_code = order.promo_code;
    this.recurring_date = order.recurring_date;
    this.response_code = order.response_code;
    this.return_reason = order.return_reason;
    // this.stop_after_next_rebill = order.stop_after_next_rebill;
    // this.sub_affiliate = order.sub_affiliate;
    // this.systemNotes = order.systemNotes;
    // this.time_stamp = order.time_stamp;
    
  }

  // get name() {
  //   let name = '';

  //   if (this.billing_first_name && this.billing_last_name) {
  //     name = this.billing_first_name + ' ' + this.billing_last_name;
  //   } else if (this.billing_first_name) {
  //     name = this.billing_first_name;
  //   } else if (this.billing_last_name) {
  //     name = this.billing_last_name;
  //   }

  //   return name;
  // }

  set name(value) {
  }

  set address(value) {
  }
}
