export class Network {

  network_id: string;
  customer_id: string;
  name: string;
  identifier: string;
  account_status: string;
  displayed_name: string;
  is_show_name: string;
  timezone_id: string;
  language_id: string;
  currency_id: string;
  logo_image_url: string;
  favicon_image_url: string;
  support_email: string;
  email_background_logo_color: string;
  time_created: string;
  time_saved: string;
  relationship: string;

  constructor(network) {
    this.network_id = network.network_id;
    this.customer_id = network.customer_id;
    this.name = network.name;
    this.identifier = network.identifier;
    this.account_status = network.account_status;
    this.displayed_name = network.displayed_name;
    this.is_show_name = network.is_show_name;
    this.timezone_id = network.timezone_id;
    this.language_id = network.language_id;
    this.currency_id = network.currency_id;
    this.logo_image_url = network.logo_image_url;
    this.favicon_image_url = network.favicon_image_url;
    this.support_email = network.support_email;
    this.email_background_logo_color = network.email_background_logo_color;
    this.time_created = network.time_created;
    this.time_saved = network.time_saved;
    this.relationship = network.relationship;
  }
}
