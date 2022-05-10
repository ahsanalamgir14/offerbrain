import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Observable, of, ReplaySubject, observable } from 'rxjs';
import { filter } from 'rxjs/operators';
import { ListColumn } from 'src/@fury/shared/list/list-column.model';
import { fadeInRightAnimation } from 'src/@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from 'src/@fury/animations/fade-in-up.animation';
import { FormGroup, FormControl } from '@angular/forms';
import { SubAffiliate } from './sub-affiliates.model';
import { SubAffiliatesService } from './sub-affiliates.service';
import { Subscription } from 'rxjs';
import { SelectionModel } from '@angular/cdk/collections';
import { formatDate } from '@angular/common';
import { environment } from 'src/environments/environment';
import { ApiService } from 'src/app/api.service';
import { Pipe, PipeTransform } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Notyf } from 'notyf';
const ndjsonParser = require('ndjson-parse');

@Component({
  selector: 'fury-sub-affiliates',
  templateUrl: './sub-affiliates.component.html',
  styleUrls: ['./sub-affiliates.component.scss'],
  // animations: [fadeInRightAnimation, fadeInUpAnimation]

})
export class SubAffiliatesComponent implements OnInit {
  subject$: ReplaySubject<SubAffiliate[]> = new ReplaySubject<SubAffiliate[]>(1);
  data$: Observable<SubAffiliate[]> = this.subject$.asObservable();

  subAffiliates: SubAffiliate[];
  AffOptionsSubscription: Subscription;
  grossRevenueSubscription: Subscription;
  deleteSubscription: Subscription;
  isLoading = false;
  totalRows = 0;
  pageSize = 100;
  currentPage = 0;
  pageSizeOptions: number[] = [25, 50, 100, 500];
  filters = {};
  address = [];
  all_fields = [];
  all_values = [];
  search = '';
  filterData: any = [];
  notyf = new Notyf();
  name: string;
  id: number;
  idArray = [];
  timer: any;
  isChecked = false;
  endPoint = '';
  start_date = '';
  end_date = '';
  summary: any;
  sub1 = "";
  sub2 = "";
  sub3 = "";

  range = new FormGroup({
    start: new FormControl(),
    end: new FormControl()
  });

  affiliateOptions = [];
  affiliate: string;
  skeletonloader = true;

  @Input()
  columns: ListColumn[] = [
    { name: 'sub1', property: 'sub1', visible: true, isModelProperty: true },
    { name: 'sub2', property: 'sub2', visible: true, isModelProperty: true },
    { name: 'sub3', property: 'sub3', visible: true, isModelProperty: true },
    // { name: 'sub4', property: 'sub4', visible: true, isModelProperty: true },
    // { name: 'sub5', property: 'sub5', visible: true, isModelProperty: true },
    { name: 'gross_revenue', property: 'gross_revenue', visible: true, isModelProperty: true },
    { name: 'impressions', property: 'impressions', visible: true, isModelProperty: true },
    { name: 'gross_clicks', property: 'gross_clicks', visible: true, isModelProperty: true },
    { name: 'total_clicks', property: 'total_clicks', visible: true, isModelProperty: true },
    { name: 'unique_clicks', property: 'unique_clicks', visible: true, isModelProperty: true },
    { name: 'duplicate_clicks', property: 'duplicate_clicks', visible: true, isModelProperty: true },
    { name: 'invalid_clicks', property: 'invalid_clicks', visible: true, isModelProperty: true },
    { name: 'total_conversions', property: 'total_conversions', visible: true, isModelProperty: true },
    { name: 'CV', property: 'CV', visible: false, isModelProperty: false },
    { name: 'invalid_conversions_scrub', property: 'invalid_conversions_scrub', visible: false, isModelProperty: false },
    { name: 'view_through_conversions', property: 'view_through_conversions', visible: false, isModelProperty: false },
    { name: 'events', property: 'events', visible: false, isModelProperty: false },
    { name: 'view_through_events', property: 'view_through_events', visible: false, isModelProperty: false },
    { name: 'CVR', property: 'CVR', visible: false, isModelProperty: false },
    { name: 'EVR', property: 'EVR', visible: false, isModelProperty: false },
    { name: 'CTR', property: 'CTR', visible: false, isModelProperty: false },
    { name: 'CPC', property: 'CPC', visible: false, isModelProperty: false },
    { name: 'CPA', property: 'CPA', visible: false, isModelProperty: false },
    { name: 'EPC', property: 'EPC', visible: false, isModelProperty: false },
    { name: 'RPC', property: 'RPC', visible: false, isModelProperty: false },
    { name: 'RPA', property: 'RPA', visible: false, isModelProperty: false },
    { name: 'payout', property: 'payout', visible: false, isModelProperty: false },
    { name: 'revenue', property: 'revenue', visible: false, isModelProperty: false },
    { name: 'margin', property: 'margin', visible: false, isModelProperty: false },
    { name: 'profit', property: 'profit', visible: false, isModelProperty: false },
    { name: 'gross_sales', property: 'gross_sales', visible: false, isModelProperty: false },
    { name: 'ROAS', property: 'ROAS', visible: false, isModelProperty: false },
    { name: 'gross_sales_vt', property: 'gross_sales_vt', visible: false, isModelProperty: false },
    { name: 'RPM', property: 'RPM', visible: false, isModelProperty: false },
    { name: 'CPM', property: 'CPM', visible: false, isModelProperty: false },
    { name: 'avg_sale_value', property: 'avg_sale_value', visible: false, isModelProperty: false }
  ] as ListColumn[];

  dataSource: MatTableDataSource<SubAffiliate>;
  selection = new SelectionModel<SubAffiliate>(true, []);

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;

  constructor(private dialog: MatDialog, private subAffiliatesService: SubAffiliatesService, private http: HttpClient) {
    this.endPoint = environment.endpoint;
  }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  mapData() {
    return of(this.subAffiliates.map(subAff => new SubAffiliate(subAff)));
  }

  mapRevenue(data) {
    this.subAffiliates.map(function (subAff, i) {
      if (data[i][0] != null && data[i][0] != '') {
        subAff.gross_revenue = data[i][0];
      }
    })
  }

  ngOnInit(): void {
    this.AffOptionsSubscription = this.subAffiliatesService.affOptionsResponse$.subscribe(data => this.manageAffOptionsResponse(data))
    this.grossRevenueSubscription = this.subAffiliatesService.grossRevenueResponse$.subscribe(data => this.manageRevenueResponse(data))
    this.subAffiliatesService.getAffiliateOptions();
    this.selectDate('today');
    // this.getData();
    this.dataSource = new MatTableDataSource();
    this.data$.pipe(
      filter(data => !!data)
    ).subscribe((subAffiliates) => {
      // this.subAffiliates = subAffiliates;
      this.dataSource.data = subAffiliates;
    });
  }

  ngAfterViewInit(): void {
    this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }

  pageChanged(event: PageEvent) {
    this.pageSize = event.pageSize;
    this.currentPage = event.pageIndex;
    // this.getData();
  }

  manageAffOptionsResponse(data) {
    if (data.status) {
      this.affiliateOptions = data.data.affiliates;
    }
  }

  manageRevenueResponse(data) {
    if (data.status) {
      this.mapRevenue(data.data);
      this.subject$.next(this.subAffiliates);
    }
  }

  async getData() {
    if (!this.affiliate) {
      this.notyf.error('Please select network to get data');
      return;
    }
    let result = [];
    this.skeletonloader = true;
    this.isLoading = true;
    // this.isChecked = false;
    this.start_date = formatDate(this.range.get('start').value, 'yyyy-MM-dd', 'en');
    this.end_date = formatDate(this.range.get('end').value, 'yyyy-MM-dd', 'en');
    const headers = { "Content-type": "application/json; charset=UTF-8", 'X-Eflow-API-Key': 'nH43mlvTSCuYUOgOXrRA' };
    this.getSummary(headers);
    result = await this.getSubAffiliates(headers);
    console.log('result :', result);

    let affiliatesArray = result.map(a => [a.sub1, a.sub2, a.sub3]);
    let filter = {
      'data': affiliatesArray,
      'start_date': this.start_date,
      'end_date': this.end_date,
    };
    this.subAffiliatesService.getGrossRevenue(filter);
  }

  getSummary(headers) {
    const summaryURL = 'https://api.eflow.team/v1/networks/reporting/entity/summary';
    const summaryBody = {
      "from": this.start_date,
      "to": this.end_date,
      "timezone_id": 67,
      "currency_id": "USD",
      "columns": [
        {
          "column": "affiliate"
        }
      ],
      "query": {
        "filters": []
      }
    }

    const response = fetch(summaryURL, {
      method: 'POST',
      body: JSON.stringify(summaryBody),
      headers: headers,
      credentials: 'same-origin'
    }).then(res => res.json()).then((res: any) => {
      this.summary = res;
      // this.isLoading = false;
    });

  }

  async getSubAffiliates(headers) {
    const url = 'https://api.eflow.team/v1/networks/reporting/entity/table/export';
    const body =
    {
      "from": this.start_date,
      "to": this.end_date,
      "timezone_id": 67,
      "currency_id": "USD",
      "columns": [
        { "column": "sub1" },
        { "column": "sub2" },
        { "column": "sub3" },
        // { "column": "sub4" },
        // { "column": "sub5" }
      ],
      "query": {
        "filters": [
          {
            "filter_id_value": this.affiliate,
            "resource_type": "affiliate"
          }
        ]
      },
      "format": "json"
    };
    if (this.sub1) {
      body.query.filters.push(
        {
          "filter_id_value": this.sub1,
          "resource_type": "sub1"
        }
      )
    }
    if (this.sub2) {
      body.query.filters.push(
        {
          "filter_id_value": this.sub2,
          "resource_type": "sub2"
        }
      )
    }
    if (this.sub3) {
      body.query.filters.push(
        {
          "filter_id_value": this.sub3,
          "resource_type": "sub3"
        }
      )
    }
    let jsonData = [];
    const response2 = await fetch(url, {
      method: 'POST',
      body: JSON.stringify(body),
      headers: headers,
      credentials: 'same-origin'
    }).then(res => res.text()).then((res: any) => {
      jsonData = ndjsonParser(res);
      this.subAffiliates = jsonData;
      setTimeout(() => {
        this.paginator.length = this.subAffiliates.length;
        // this.paginator.length = jsonData.count;
      });
      this.mapData().subscribe(subAffiliates => {
        this.subject$.next(subAffiliates);
      });
      this.isLoading = false;
      this.skeletonloader = false;
    });

    return jsonData;
  }

  onFilterChange(value) {
    if (!this.dataSource) {
      return;
    }
    value = value.trim();
    value = value.toLowerCase();
    this.dataSource.filter = value;
  }

  viewDetails(id) {
    console.log(id);
  }

  handleDeleteAction(id) {
    console.log(id);
  }

  commonFilter(value, field) {
    console.log(value);
    console.log(this.affiliate);
    if (this.all_fields.indexOf(field) === -1) {
      this.all_fields.push(field);
      this.all_values.push(value);
    } else {
      let index = this.all_fields.indexOf(field);
      this.all_values[index] = value;
    }
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

  ngOnDestroy(): void {
    if (this.grossRevenueSubscription) {
      this.subAffiliatesService.affiliatesGetResponse.next([]);
      this.grossRevenueSubscription.unsubscribe();
    }
  }
}