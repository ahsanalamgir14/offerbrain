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
import { AffiliatesService } from './sub-affiliates.service';
import { Subscription } from 'rxjs';
import { SelectionModel } from '@angular/cdk/collections';
import { formatDate } from '@angular/common';
import { environment } from 'src/environments/environment';
import { ApiService } from 'src/app/api.service';
import { Pipe, PipeTransform } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Notyf } from 'notyf';

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
  getSubscription: Subscription;
  deleteSubscription: Subscription;
  isLoading = false;
  totalRows = 0;
  pageSize = 25;
  currentPage = 1;
  pageSizeOptions: number[] = [5, 10, 25, 100];
  filters = {};
  address = [];
  search = '';
  notyf = new Notyf();
  name: string;
  id: number;
  idArray = [];
  allIdArray = [];
  timer: any;
  isChecked = false;
  endPoint = '';

  @Input()
  columns: ListColumn[] = [
    { name: 'sub1', property: 'sub1', visible: true, isModelProperty: true },
    { name: 'sub2', property: 'sub2', visible: true, isModelProperty: true },
    { name: 'sub3', property: 'sub3', visible: true, isModelProperty: true },
    { name: 'sub4', property: 'sub4', visible: true, isModelProperty: true },
    { name: 'sub5', property: 'sub5', visible: true, isModelProperty: true },
    { name: 'impressions', property: 'impressions', visible: true, isModelProperty: true },
    { name: 'gross_clicks', property: 'gross_clicks', visible: true, isModelProperty: true },
    { name: 'total_clicks', property: 'total_clicks', visible: true, isModelProperty: true },
    { name: 'unique_clicks', property: 'unique_clicks', visible: true, isModelProperty: true },
    { name: 'duplicate_clicks', property: 'duplicate_clicks', visible: true, isModelProperty: true },
    { name: 'invalid_clicks', property: 'invalid_clicks', visible: true, isModelProperty: true },
    { name: 'total_conversions', property: 'total_conversions', visible: true, isModelProperty: true },
    { name: 'CV', property: 'CV', visible: true, isModelProperty: true },
    { name: 'invalid_conversions_scrub', property: 'invalid_conversions_scrub', visible: true, isModelProperty: true },
    { name: 'view_through_conversions', property: 'view_through_conversions', visible: true, isModelProperty: true },
    { name: 'events', property: 'events', visible: true, isModelProperty: true },
    { name: 'view_through_events', property: 'view_through_events', visible: true, isModelProperty: true },
    { name: 'CVR', property: 'CVR', visible: true, isModelProperty: true },
    { name: 'EVR', property: 'EVR', visible: true, isModelProperty: true },
    { name: 'CTR', property: 'CTR', visible: true, isModelProperty: true },
    { name: 'CPC', property: 'CPC', visible: true, isModelProperty: true },
    { name: 'CPA', property: 'CPA', visible: true, isModelProperty: true },
    { name: 'EPC', property: 'EPC', visible: true, isModelProperty: true },
    { name: 'RPC', property: 'RPC', visible: true, isModelProperty: true },
    { name: 'RPA', property: 'RPA', visible: true, isModelProperty: true },
    { name: 'payout', property: 'payout', visible: true, isModelProperty: true },
    { name: 'revenue', property: 'revenue', visible: true, isModelProperty: true },
    { name: 'margin', property: 'margin', visible: true, isModelProperty: true },
    { name: 'profit', property: 'profit', visible: true, isModelProperty: true },
    { name: 'gross_sales', property: 'gross_sales', visible: true, isModelProperty: true },
    { name: 'ROAS', property: 'ROAS', visible: true, isModelProperty: true },
    { name: 'gross_sales_vt', property: 'gross_sales_vt', visible: true, isModelProperty: true },
    { name: 'RPM', property: 'RPM', visible: true, isModelProperty: true },
    { name: 'CPM', property: 'CPM', visible: true, isModelProperty: true },
    { name: 'avg_sale_value', property: 'avg_sale_value', visible: true, isModelProperty: true }
  ] as ListColumn[];

  dataSource: MatTableDataSource<SubAffiliate>;
  selection = new SelectionModel<SubAffiliate>(true, []);

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;

  constructor(private dialog: MatDialog, private affiliatesService: AffiliatesService, private http: HttpClient) {
    this.endPoint = environment.endpoint;
  }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  mapData() {
    return of(this.subAffiliates.map(midGroup => new SubAffiliate(midGroup)));
  }

  ngOnInit(): void {

    this.getData();
    this.dataSource = new MatTableDataSource();
    this.data$.pipe(
      filter(data => !!data)
    ).subscribe((subAffiliates) => {
      this.subAffiliates = subAffiliates;
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
    this.getData();
  }

  async getData() {
    this.isLoading = true;
    this.isChecked = false;
    // this.filters = {
    //   "currentPage": this.currentPage,
    //   "pageSize": this.pageSize,
    //   "search": this.search,
    // }

    const headers = { 'X-Eflow-API-Key': 'nH43mlvTSCuYUOgOXrRA' };
    // const options: {
    //   headers: { 'X-Eflow-API-Key': 'nH43mlvTSCuYUOgOXrRA', 'Content-Type': 'application/json' },
    //   // context?: HttpContext,
    //   observe: 'response',
    //   // params?: HttpParams|
    //   // {[param:/ string]: string | number | boolean | ReadonlyArray<string|number|boolean>},
    //   // reportProgress?: boolean,
    //   // responseType: 'json',
    //   // withCredentials?: true,
    // }
    const url = 'https://api.eflow.team/v1/networks/reporting/entity/table/export';
    const body =
    {
      "from": "2022-03-01",
      "to": "2022-03-03",
      "timezone_id": 67,
      "currency_id": "USD",
      "columns": [
        { "column": "sub1" },
        { "column": "sub2" },
        { "column": "sub3" },
        { "column": "sub4" },
        { "column": "sub5" }
      ],
      "query": {
        "filters": [
          {
            "filter_id_value": "1",
            "resource_type": "affiliate"
          }
        ]
      },
      "format": "json"
    };
    // this.http.post(url, body, { headers }).subscribe(res => {
    // console.log('res :', res);
      // console.log(JSON.stringify(res));
      // console.log(res.json());
      // this.isLoading = false;
      // this.subAffiliates = subAffiliates.data;
      // this.dataSource.data = subAffiliates.data;
      // this.mapData().subscribe(subAffiliates => {
      //   this.subject$.next(subAffiliates);
      // });
    // });

    // const response = fetch(url, {
    //   method: 'POST',
    //   body: JSON.stringify(body),
    //   headers: { 
    //     "Content-type": "application/json; charset=UTF-8",
    //     'X-Eflow-API-Key': 'nH43mlvTSCuYUOgOXrRA'
    //    },
    //   credentials: 'same-origin'
    // }).then(res => res.text()).then((data) => {
    //   var data2 = {data: data};
    //   console.log(data2);
      
    // });

    await this.affiliatesService.getSubAffiliates()
      .then(subAffiliates => {
        this.subAffiliates = subAffiliates.data;
        this.dataSource.data = subAffiliates.data;
        this.mapData().subscribe(subAffiliates => {
          this.subject$.next(subAffiliates);
        });
        setTimeout(() => {
          // this.paginator.pageIndex = this.currentPage;
          // this.paginator.length = subAffiliates.pag.count;
        });
        this.isLoading = false;
      }, error => {
        this.isLoading = false;
      });
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

  ngOnDestroy(): void {
    if (this.deleteSubscription) {
      // this.affiliateservice.deleteResponse.next([]);
      // this.deleteSubscription.unsubscribe();
    }
  }
}