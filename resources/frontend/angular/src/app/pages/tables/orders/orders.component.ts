import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Observable, of, ReplaySubject } from 'rxjs';
import { filter } from 'rxjs/operators';
import { ListColumn } from '../../../../@fury/shared/list/list-column.model';
import { Order } from './order.model';
import { fadeInRightAnimation } from '../../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../../@fury/animations/fade-in-up.animation';
//self imports
import { FormGroup, FormControl } from '@angular/forms';
import { OrdersService } from './orders.service';
import { Subscription } from 'rxjs';
import { formatDate } from '@angular/common';
import { environment } from '../../../../environments/environment';
import { ProductDetailComponent } from './product-detail/product-detail.component';
import { ApiService } from 'src/app/api.service';
import *  as  states from './states.json';

@Component({
  selector: 'fury-orders',
  templateUrl: './orders.component.html',
  styleUrls: ['./orders.component.scss'],
  animations: [fadeInRightAnimation, fadeInUpAnimation]
})
export class OrdersComponent implements OnInit, AfterViewInit, OnDestroy {


  subject$: ReplaySubject<Order[]> = new ReplaySubject<Order[]>(1);
  data$: Observable<Order[]> = this.subject$.asObservable();
  orders: Order[];

  //customer coding
  getSubscription: Subscription;
  getCampaignsSubscription: Subscription;
  getProductsSubscription: Subscription;
  isLoading = false;
  totalRows = 0;
  pageSize = 25;
  currentPage = 0;
  all_fields = [];
  all_values = [];
  search = '';
  filterData: any = [];
  filters = {};
  endPoint = '';
  skeletonloader = true;

  range = new FormGroup({
    start: new FormControl(),
    end: new FormControl()
  });

  //mat date range selector
  selected = "transactionCreatedDate";
  campaign = "allCampaigns";
  campaignCategory = "allCategories";
  product = "allProducts";
  productCategory = "allCategories";
  campaignProduct = "allCampaignProducts";
  affiliate = "allAffiliates";
  callCenter = "allCallCenters";
  billType = "allBillings";
  billingCycle = "all";
  recycleNo = "all";
  txnType = "all";
  billing_country = "allCurrencies";
  country = "allCountries";
  state = "allStates";
  gateway = "all";
  ccType = "all";
  is_3d_protected = "all";
  gatewayCategory = "allGatewayCategories";
  gatewayType = "all";
  creditOrDebit = "all";
  start_date = '';
  end_date = '';

  campaignOptions: [];
  productOptions: [];
  cardOptions: string[] = ['visa', 'master'];
  pageSizeOptions: number[] = [5, 10, 25, 100];
  stateOptions: any = (states as any).default;

  @Input()
  columns: ListColumn[] = [
    { name: 'Id', property: 'id', isModelProperty: true },
    { name: 'Order Id', property: 'order_id', visible: true, isModelProperty: true },
    { name: 'Created By', property: 'created_by_employee_name', visible: true, isModelProperty: true },
    { name: 'Bill First', property: 'billing_first_name', visible: true, isModelProperty: true },
    { name: 'Bill Last', property: 'billing_last_name', visible: true, isModelProperty: true },
    { name: 'Bill Address1', property: 'billing_street_address', visible: true, isModelProperty: true },
    { name: 'Acq Date', property: 'acquisition_date', visible: true, isModelProperty: true },
    { name: 'Acq Month', property: 'acquisition_month', visible: true, isModelProperty: true },
    { name: 'Pub ID', property: 'c1', visible: true, isModelProperty: true },
    // { name: 'Trx month', property: 'trx_month', visible: true, isModelProperty: true },
    { name: 'Network', property: 'affid', visible: true, isModelProperty: true },
    // { name: 'Bill Phone', property: 'billing_telephone', visible: true, isModelProperty: true },
    // { name: 'Bill Email', property: 'billing_email', visible: true, isModelProperty: true },
    // { name: 'Ship First', property: 'shipping_first_name', visible: true, isModelProperty: true },
    // { name: 'Ship Last', property: 'shipping_last_name', visible: true, isModelProperty: true },
    // { name: 'Ship Address1', property: 'shipping_street_address', visible: true, isModelProperty: true },
    // { name: 'Ship Address2', property: 'shipping_street_address2', visible: true, isModelProperty: true },
    // { name: 'Ship City', property: 'shipping_city', visible: true, isModelProperty: true },
    // { name: 'Ship State', property: 'shipping_state', visible: true, isModelProperty: true },
    // { name: 'Ship Zip', property: 'shipping_postcode', visible: true, isModelProperty: true },
    // { name: 'Ship Country', property: 'shipping_country', visible: true, isModelProperty: true },
    // { name: 'Ship Phone', property: 'shipping_telephone', visible: true, isModelProperty: true },
    // { name: 'Ship Method Name', property: 'shipping_method_name', visible: true, isModelProperty: true },
    // { name: 'shippable', property: 'shippable', visible: true, isModelProperty: true },
    { name: 'Taxable Total', property: 'order_sales_tax_amount', visible: false, isModelProperty: true },
    { name: 'Sub Total', property: 'order_total', visible: true, isModelProperty: true },
    // { name: 'Tracking Number', property: 'tracking_number', visible: true, isModelProperty: true },
    // { name: 'Payment', property: 'cc_type', visible: true, isModelProperty: true },
    // { name: 'Campaign ID', property: 'campaign_id', visible: true, isModelProperty: true },
    // { name: 'Customer Number', property: 'customer_id', visible: true, isModelProperty: true },
    // { name: 'credit_card_number', property: 'credit_card_number', visible: true, isModelProperty: true },
    // { name: 'cc_expires', property: 'cc_expires', visible: true, isModelProperty: true },
    // { name: 'prepaid_match', property: 'prepaid_match', visible: true, isModelProperty: true },
    // { name: 'gateway_id', property: 'gateway_id', visible: true, isModelProperty: true },
    // { name: 'preserve_gateway', property: 'preserve_gateway', visible: true, isModelProperty: true },
    // { name: 'gateway_descriptor', property: 'gateway_descriptor', visible: true, isModelProperty: true },
    // { name: 'processor_id', property: 'processor_id', visible: true, isModelProperty: true },
    // { name: 'ip_address', property: 'ip_address', visible: true, isModelProperty: true },
    // { name: 'decline_reason', property: 'decline_reason', visible: true, isModelProperty: true },
    // { name: 'is_cascaded', property: 'is_cascaded', visible: true, isModelProperty: true },
    // { name: 'decline_reason_details', property: 'decline_reason_details', visible: true, isModelProperty: true },    
    // { name: 'Shipped Date', property: 'shipping_date', visible: true, isModelProperty: true },
    // { name: 'is_fraud', property: 'is_fraud', visible: true, isModelProperty: true },
    // { name: 'is_chargeback', property: 'is_chargeback', visible: true, isModelProperty: true },
    // { name: 'chargeback_date', property: 'chargeback_date', visible: true, isModelProperty: true },
    // { name: 'is_rma', property: 'is_rma', visible: true, isModelProperty: true },
    // { name: 'rma_number', property: 'rma_number', visible: true, isModelProperty: true },
    // { name: 'rma_reason', property: 'rma_reason', visible: true, isModelProperty: true },
    // { name: 'is_recurring', property: 'is_recurring', visible: true, isModelProperty: true },
    // { name: 'retry_date', property: 'retry_date', visible: true, isModelProperty: true },
    // { name: 'auth_id', property: 'auth_id', visible: true, isModelProperty: true },
    // { name: 'hold_date', property: 'hold_date', visible: true, isModelProperty: true },
    // { name: 'is_void', property: 'is_void', visible: true, isModelProperty: true },
    // { name: 'void_amount', property: 'void_amount', visible: true, isModelProperty: true },
    // { name: 'void_date', property: 'void_date', visible: true, isModelProperty: true },
    // { name: 'is_refund', property: 'is_refund', visible: true, isModelProperty: true },
    // { name: 'refund_amount', property: 'refund_amount', visible: true, isModelProperty: true },
    // { name: 'refund_date', property: 'refund_date', visible: true, isModelProperty: true },
    // { name: 'afid', property: 'afid', visible: true, isModelProperty: true },
    // { name: 'sid', property: 'sid', visible: true, isModelProperty: true },
    // { name: 'c2', property: 'c2', visible: true, isModelProperty: true },
    // { name: 'c3', property: 'c3', visible: true, isModelProperty: true },
    // { name: 'aid', property: 'aid', visible: true, isModelProperty: true },
    // { name: 'opt', property: 'opt', visible: true, isModelProperty: true },
    // { name: 'rebill_discount_percent', property: 'rebill_discount_percent', visible: true, isModelProperty: true },
    // { name: 'Bill Cycle', property: 'billing_cycle', visible: true, isModelProperty: true },
    // { name: 'parent_id', property: 'parent_id', visible: true, isModelProperty: true },
    // { name: 'main_product_id', property: 'main_product_id', visible: true, isModelProperty: true },
    // { name: 'main_product_quantity', property: 'main_product_quantity', visible: true, isModelProperty: true },
    // { name: 'order_confirmed', property: 'order_confirmed', visible: true, isModelProperty: true },
    // { name: 'order_confirmed_date', property: 'order_confirmed_date', visible: true, isModelProperty: true },
    // { name: 'is_blacklisted', property: 'is_blacklisted', visible: true, isModelProperty: true },
    // { name: 'ancestor_id', property: '  ancestor_id', visible: true, isModelProperty: true },
    // { name: 'decline_salvage_discount_percent', property: 'decline_salvage_discount_percent', visible: true, isModelProperty: true },
    // { name: 'is_test_cc', property: 'is_test_cc', visible: true, isModelProperty: true },
    // { name: 'current_rebill_discount_percent', property: 'current_rebill_discount_percent', visible: true, isModelProperty: true },
    // { name: 'amount_refunded_to_date', property: 'amount_refunded_to_date', visible: true, isModelProperty: true },
    // { name: 'Ship_state_id', property: 'shipping_state_id', visible: true, isModelProperty: true },
    // { name: 'billing_state_id', property: 'billing_state_id', visible: true, isModelProperty: true },
    // { name: 'affiliate', property: 'affiliate', visible: true, isModelProperty: true },
    // { name: 'cc_first_6', property: 'cc_first_6', visible: true, isModelProperty: true },
    // { name: 'cc_last_4', property: 'cc_last_4', visible: true, isModelProperty: true },
    // { name: 'cc_number', property: 'cc_number', visible: true, isModelProperty: true },
    // { name: 'cc_orig_first_6', property: 'cc_orig_first_6', visible: true, isModelProperty: true },
    // { name: 'cc_orig_last_4', property: 'cc_orig_last_4', visible: true, isModelProperty: true },
    // { name: 'check_account_last_4', property: 'check_account_last_4', visible: true, isModelProperty: true },
    // { name: 'check_routing_last_4', property: 'check_routing_last_4', visible: true, isModelProperty: true },
    // { name: 'check_ssn_last_4', property: 'check_ssn_last_4', visible: true, isModelProperty: true },
    // { name: 'check_transitnum', property: 'check_transitnum', visible: true, isModelProperty: true },
    // { name: 'child_id', property: 'child_id', visible: true, isModelProperty: true },
    // { name: 'click_id', property: 'click_id', visible: true, isModelProperty: true },
    // { name: 'coupon_discount_amount', property: 'coupon_discount_amount', visible: true, isModelProperty: true },
    // { name: 'coupon_id', property: 'coupon_id', visible: true, isModelProperty: true },
    // { name: 'created_by_user_name', property: 'created_by_user_name', visible: true, isModelProperty: true },
    // { name: 'credit_applied', property: 'credit_applied', visible: true, isModelProperty: true },
    // { name: 'customers_telephone', property: 'customers_telephone', visible: true, isModelProperty: true },
    // { name: 'email_address', property: 'email_address', visible: true, isModelProperty: true },
    // { name: 'first_name', property: 'first_name', visible: true, isModelProperty: true },
    // { name: 'is_3d_protected', property: 'is_3d_protected', visible: true, isModelProperty: true },
    // { name: 'is_any_product_recurring', property: 'is_any_product_recurring', visible: true, isModelProperty: true },
    // { name: 'last_name', property: 'last_name', visible: true, isModelProperty: true },
    // { name: 'next_subscription_product', property: 'next_subscription_product', visible: true, isModelProperty: true },
    // { name: 'next_subscription_product_id', property: 'next_subscription_product_id', visible: true, isModelProperty: true },
    // { name: 'on_hold', property: 'on_hold', visible: true, isModelProperty: true },
    // { name: 'on_hold_by', property: 'on_hold_by', visible: true, isModelProperty: true },
    // { name: 'order_sales_tax', property: 'order_sales_tax', visible: true, isModelProperty: true },
    // { name: 'order_status', property: 'order_status', visible: true, isModelProperty: true },
    // { name: 'time_stamp', property: 'time_stamp', visible: true, isModelProperty: true },
    // { name: 'totals_breakdown', property: 'totals_breakdown', visible: true, isModelProperty: true },
    // { name: 'transaction_id', property: 'transaction_id', visible: true, isModelProperty: true },
    // { name: 'upsell_product_id', property: 'upsell_product_id', visible: true, isModelProperty: true },
    // { name: 'upsell_product_quantity', property: 'upsell_product_quantity', visible: true, isModelProperty: true },
    // { name: 'website_received', property: 'website_received', visible: true, isModelProperty: true },
    // { name: 'website_sent', property: 'website_sent', visible: true, isModelProperty: true },
    { name: 'Actions', property: 'actions', visible: true },

  ] as ListColumn[];
  // pageSize = 20000;
  dataSource: MatTableDataSource<Order> | null;

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;

  constructor(private dialog: MatDialog, private ordersService: OrdersService, private apiService: ApiService) {
    this.endPoint = environment.endpoint;
  }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  ngOnInit() {
    this.getCampaignsSubscription = this.ordersService.getCampaignsResponse$.subscribe(data => this.manageCampaignsResponse(data))
    this.getProductsSubscription = this.ordersService.getProductsResponse$.subscribe(data => this.manageProductsResponse(data))
    this.selectDate('today');
    this.ordersService.getCampaigns();
    this.ordersService.getProducts();
    this.getData();
    // this.getDropData();
    this.dataSource = new MatTableDataSource();
    this.data$.pipe(
      filter(data => !!data)
    ).subscribe((orders) => {
      this.orders = orders;
      this.dataSource.data = orders;
    });
  }
  mapData() {
    return of(this.orders.map(order => new Order(order)));
  }
  ngAfterViewInit() {
    this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }
  pageChanged(event: PageEvent) {
    this.pageSize = event.pageSize;
    this.currentPage = event.pageIndex;
    this.getData();
  }

  getData() {
    this.isLoading = true;
    if(this.range.get('start').value != null){
      this.start_date = formatDate(this.range.get('start').value, 'yyyy/MM/dd', 'en')
    }
    if(this.range.get('end').value != null){
      this.end_date = formatDate(this.range.get('end').value, 'yyyy/MM/dd', 'en')
    }
    this.filters = {
      "currentPage": this.currentPage,
      "pageSize": this.pageSize,
      "start": this.start_date,
      "end": this.end_date,
      'all_fields': this.all_fields,
      'all_values': this.all_values,
      'search': this.search
    }
    this.ordersService.getOrders(this.filters)
      .then(orders => {
        this.orders = orders.data.data;
        // this.dataSource.data = orders.data;
        setTimeout(() => {
          this.paginator.pageIndex = this.currentPage;
          this.paginator.length = orders.pag.count;
        });
        this.mapData().subscribe(orders => {
          this.subject$.next(orders);
        });
        this.skeletonloader = false;
        this.isLoading = false;
      }, error => {
        this.skeletonloader = false;
        this.isLoading = false;
      });
  }
  async getDropData() {
    const response = fetch(`${this.endPoint}/api/getDropDownContent`)
      .then(res => res.json()).then((data) => {
        this.filterData = data;
      });
  }
  commonFilter(value, field) {
    if (this.all_fields.indexOf(field) === -1) {
      this.all_fields.push(field);
      this.all_values.push(value);
    } else {
      let index = this.all_fields.indexOf(field);
      this.all_values[index] = value;
    }
    // this.getData();
  }

  manageGetResponse(orders) {
    if (orders.status) {
      this.orders = orders.data.data;
      this.dataSource.data = orders.data.data;
      setTimeout(() => {
        this.paginator.pageIndex = this.currentPage;
        this.paginator.length = orders.pag.count;
      });
      this.isLoading = false;
    } else {
      this.isLoading = false;
    }
  }

  manageCampaignsResponse(data) {
    if (data.status) {
      this.campaignOptions = data.data;
    }
  }
  manageProductsResponse(data) {
    if (data.status) {
      this.productOptions = data.data;
    }
  }

  createCustomer() {

  }

  updateCustomer(Order) {

  }

  deleteCustomer(Order) {

  }

  onFilterChange(value) {
    // value = value.toLowerCase()
    // this.search = value;
    // if(value == ''){
    //   this.getData();
    // }
    if (!this.dataSource) {
      return;
    }
    value = value.trim();
    value = value.toLowerCase();
    this.dataSource.filter = value;
  }

  selectDate(param) {
    var startDate = new Date();
    var endDate = new Date();
    if (param == 'today') {
      this.range.get('start').setValue(new Date());
      this.range.get('end').setValue(new Date());
    } else if (param == 'yesterday') {
      this.range.get('start').setValue(new Date(startDate.setDate(startDate.getDate() - 1)));
      this.range.get('end').setValue(new Date(endDate.setDate(endDate.getDate() - 1)));
    } else if (param == 'thisMonth') {
      this.range.get('start').setValue(new Date(startDate.getFullYear(), startDate.getMonth(), 1));
      this.range.get('end').setValue(new Date(endDate.getFullYear(), endDate.getMonth() + 1, 0));
    } else if (param == 'pastWeek') {
      this.range.get('start').setValue(new Date(startDate.setDate(startDate.getDate() - 7)));
      this.range.get('end').setValue(new Date());
    } else if (param == 'pastTwoWeek') {
      this.range.get('start').setValue(new Date(startDate.setDate(startDate.getDate() - 14)));
      this.range.get('end').setValue(new Date());
    } else if (param == 'lastMonth') {
      this.range.get('start').setValue(new Date(startDate.getFullYear(), startDate.getMonth() - 1, 1));
      this.range.get('end').setValue(new Date(endDate.getFullYear(), endDate.getMonth(), 0));
    } else if (param == 'lastThreeMonths') {
      this.range.get('start').setValue(new Date(startDate.getFullYear(), startDate.getMonth() - 3, 1));
      this.range.get('end').setValue(new Date(endDate.getFullYear(), endDate.getMonth(), 0));
    } else if (param == 'lastSixMonths') {
      this.range.get('start').setValue(new Date(startDate.getFullYear(), startDate.getMonth() - 6, 1));
      this.range.get('end').setValue(new Date(endDate.getFullYear(), endDate.getMonth(), 0));
    }
  }
  
  openDialog(id) {
    const dialogRef = this.dialog.open(ProductDetailComponent, {
      disableClose: true,
      data: { id: id }
    });
    dialogRef.updateSize('1000px');
    dialogRef.afterClosed().subscribe(result => {
    });
  }
  ngOnDestroy() {
  }
}