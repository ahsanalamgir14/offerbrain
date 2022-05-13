export class SubAffiliate {
  sub1: string;
  sub2: string;
  sub3: string;
  sub4: string;
  sub5: string;
  gross_revenue: string;
  impressions: string;
  gross_clicks: string;
  total_clicks: string;
  unique_clicks: string;
  duplicate_clicks: string;
  invalid_clicks: string;
  total_conversions: string;
  CV: string;
  invalid_conversions_scrub: string;
  view_through_conversions: string;
  events: string;
  view_through_events: string;
  CVR: string;
  EVR: string;
  CTR: string;
  CPC: string;
  CPA: string;
  EPC: string;
  RPC: string;
  RPA: string;
  payout: string;
  revenue: string;
  margin: string;
  profit: string;
  gross_sales: string;
  ROAS: string;
  gross_sales_vt: string;
  RPM: string;
  CPM: string;
  avg_sale_value: string;

  constructor(subAff) {
    this.sub1 = subAff.sub1;
    this.sub2 = subAff.sub2;
    this.sub3 = subAff.sub3;
    this.sub4 = subAff.sub4;
    this.sub5 = subAff.sub5;
    this.impressions = subAff.impressions;
    if (subAff.gross_revenue && subAff.gross_revenue != null) {
      this.gross_revenue = subAff.gross_revenue;
    } else { this.gross_revenue = '-' }
    this.gross_clicks = subAff.gross_clicks;
    this.total_clicks = subAff.total_clicks;
    this.unique_clicks = subAff.unique_clicks;
    this.duplicate_clicks = subAff.duplicate_clicks;
    this.invalid_clicks = subAff.invalid_clicks;
    this.total_conversions = subAff.total_conversions;
    this.CV = subAff.CV;
    this.invalid_conversions_scrub = subAff.invalid_conversions_scrub;
    this.view_through_conversions = subAff.view_through_conversions;
    this.events = subAff.events;
    this.view_through_events = subAff.view_through_events;
    this.CVR = subAff.CVR;
    this.EVR = subAff.EVR;
    this.CTR = subAff.CTR;
    this.CPC = subAff.CPC;
    this.CPA = subAff.CPA;
    this.EPC = subAff.EPC;
    this.RPC = subAff.RPC;
    this.RPA = subAff.RPA;
    this.payout = subAff.payout;
    this.revenue = subAff.revenue;
    this.margin = subAff.margin;
    this.profit = subAff.profit;
    this.gross_sales = subAff.gross_sales;
    this.ROAS = subAff.ROAS;
    this.gross_sales_vt = subAff.gross_sales_vt;
    this.RPM = subAff.RPM;
    this.CPM = subAff.CPM;
    this.avg_sale_value = subAff.avg_sale_value;
  }

  set set_gross_revenue(revenue) {
    if (revenue && revenue != null) {
      this.gross_revenue = revenue;
    } else { this.gross_revenue = '-' }
  }
}
