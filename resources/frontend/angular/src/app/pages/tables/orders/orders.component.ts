import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Observable, of, ReplaySubject } from 'rxjs';
import { ActivatedRoute } from '@angular/router';
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
  affiliate = "";
  subAffiliate = "";
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
  status = '';
  is_chargeback = '';
  is_refund = '';
  is_void = '';
  end_date = '';
  field_value = '';
  field = '';
  gateway_id = '';
  is_filtered = false;

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
    { name: 'Network', property: 'affid', visible: true, isModelProperty: true },
    { name: 'Taxable Total', property: 'order_sales_tax_amount', visible: false, isModelProperty: true },
    { name: 'Sub Total', property: 'order_total', visible: true, isModelProperty: true },
    { name: 'Actions', property: 'actions', visible: true },

  ] as ListColumn[];
  dataSource: MatTableDataSource<Order> | null;

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;

  constructor(private dialog: MatDialog, private ordersService: OrdersService, private apiService: ApiService, private route: ActivatedRoute) {
    this.endPoint = environment.endpoint;
    this.route.queryParams.subscribe(params => {
      const mapped = Object.entries(params).map(([key, value]) => ({key, value}));

      if(mapped[0]){
        this.gateway_id = mapped[0].value;
        this.field = mapped[1].value;
        this.field_value = mapped[2].value;
        this.start_date = mapped[3].value;
        this.end_date = mapped[4].value;
        this.is_filtered = true;
        if(this.gateway_id != ''){
          this.range.get('start').setValue(new Date(mapped[3].value));
          this.range.get('end').setValue(new Date(mapped[4].value));
          if(this.field == 'is_refund'){
            this.is_refund = mapped[2].value;
          } else if(this.field == 'is_chargeback'){
            this.is_chargeback = mapped[2].value;
          } else if(this.field == 'order_status' && this.field_value == '2'){
            this.status = mapped[2].value;
          } else if(this.field == 'order_status' && this.field_value == '7'){
            this.status = mapped[2].value;
          }
        }
        this.commonFilter(this.field_value, this.field);
      } else {
        this.selectDate('today');
      }
  });
  }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  ngOnInit() {
    this.getCampaignsSubscription = this.ordersService.getCampaignsResponse$.subscribe(data => this.manageCampaignsResponse(data))
    this.getProductsSubscription = this.ordersService.getProductsResponse$.subscribe(data => this.manageProductsResponse(data))
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

    console.log(' affiliate:', this.affiliate );
    console.log(' sub-affiliate:', this.subAffiliate );
    // return;
    this.isLoading = true;
    if(!this.is_filtered){
      if(this.range.get('start').value != null){
        this.start_date = formatDate(this.range.get('start').value, 'yyyy/MM/dd', 'en')
      }
      if(this.range.get('end').value != null){
        this.end_date = formatDate(this.range.get('end').value, 'yyyy/MM/dd', 'en')
      }
    }
    this.filters = {
      "currentPage": this.currentPage,
      "pageSize": this.pageSize,
      "start": this.start_date,
      "end": this.end_date,
      'all_fields': this.all_fields,
      'all_values': this.all_values,
      'search': this.search,
      'gateway_id': this.gateway_id,
      'affiliate': this.affiliate,
      'sub_affiliate': this.subAffiliate
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