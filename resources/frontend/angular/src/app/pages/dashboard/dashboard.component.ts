import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { ChartData } from 'chart.js';
import * as moment from 'moment';
import { Observable, ReplaySubject } from 'rxjs';
import { AdvancedPieChartWidgetOptions } from './widgets/advanced-pie-chart-widget/advanced-pie-chart-widget-options.interface';
import { AudienceOverviewWidgetOptions } from './widgets/audience-overview-widget/audience-overview-widget-options.interface';
import { BarChartWidgetOptions } from './widgets/bar-chart-widget/bar-chart-widget-options.interface';
import { DonutChartWidgetOptions } from './widgets/donut-chart-widget/donut-chart-widget-options.interface';
import { RealtimeUsersWidgetData, RealtimeUsersWidgetPages } from './widgets/realtime-users-widget/realtime-users-widget.interface';
import { RecentSalesWidgetOptions } from './widgets/recent-sales-widget/recent-sales-widget-options.interface';
import { SalesSummaryWidgetOptions } from './widgets/sales-summary-widget/sales-summary-widget-options.interface';
import { DashboardService } from './dashboard.service';
import { ChartWidgetOptions } from '../../../@fury/shared/chart-widget/chart-widget-options.interface';
import { Subscription } from 'rxjs';
import { Location, formatDate } from '@angular/common';
import { ChartOptions, ChartType, ChartDataSets, Chart } from 'chart.js';
import { environment } from '../../../environments/environment';
import { Label } from 'ng2-charts';
import { ApiService } from 'src/app/api.service';
import { FormControl, FormGroup } from '@angular/forms';

@Component({
  selector: 'fury-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss']
})
export class DashboardComponent implements OnInit {


  dashboardSubscription: Subscription;
  chart: any;
  public lineChart: any;
  public ctx: any;
  isDisabled = false;
  ngAfterViewInit() {
    this.ctx = document.getElementById('lineChart') as HTMLElement;
    this.lineChart = {
      labels: [],
      datasets: [
        {
          label: 'Decline',
          data: [],
          backgroundColor: 'blue',
          borderColor: 'lightblue',
          fill: false,
          lineTension: 0,
          radius: 5,
        },
        {
          label: 'ChargeBack',
          data: [],
          backgroundColor: 'green',
          borderColor: 'lightgreen',
          fill: false,
          lineTension: 0,
          radius: 5,
        },
        {
          label: 'Refund',
          data: [],
          backgroundColor: 'red',
          borderColor: '#FF7F7F',
          fill: false,
          lineTension: 0,
          radius: 5,
        },
      ],
    };
  }
  
  url = environment.endpoint;
  public barChartOptions: ChartOptions = {
    responsive: true,
  };
  public barChartLabels: Label[] = [];
  public barChartType: ChartType = 'bar';
  public barChartLegend = true;
  public barChartPlugins = [];
  customerArr = [];
  isLoading = true;
  spinning = false;
  getCustomerOrderData(){
    this.isDisabled = true;
    const startDate = formatDate(this.range.get('start').value, 'yyyy/MM/dd', 'en');
    const endDate = formatDate(this.range.get('end').value, 'yyyy/MM/dd', 'en');
    this.apiService.getData(`getCustomersForGraph?start_date=${startDate}&end_date=${endDate}`).then(res => res.json()).then((data) => {
      this.barChartData[0].data = data.customer;
      this.barChartData[1].data = data.order;
      this.barChartLabels = data.label;
      this.isDisabled = false;
      this.spinning = false;
    });
  }
  getMidGraphData(){
    // this.isLoading = true;
    const startDate = formatDate(this.range.get('start').value, 'yyyy/MM/dd', 'en');
    const endDate = formatDate(this.range.get('end').value, 'yyyy/MM/dd', 'en');
    this.apiService.getData(`getOrdersForGraph?start_date=${startDate}&end_date=${endDate}`).then(res => res.json()).then((data) => {
    // this.isLoading = false;
      this.lineChart.labels = data.label;
      this.lineChart.datasets[0].data = data.declineArr;
      this.lineChart.datasets[1].data = data.chargebackArr;
      this.lineChart.datasets[2].data = data.refundArr;
      new Chart(this.ctx, {
        type: 'line',
        data: this.lineChart,
      });
      this.isDisabled = false;
      this.spinning = false;
    });
  }
  
  public barChartData: ChartDataSets[] = [
    { data: [], label: 'Total Customers' },
    { data: [], label: 'Total Orders' }
  ];
  range = new FormGroup({
    start: new FormControl(),
    end: new FormControl()
  });
  private static isInitialLoad = true;
  salesData$: Observable<ChartData>;
  totalSalesOptions: BarChartWidgetOptions = {
    title: 'Total Sales',
    gain: 16.3,
    subTitle: 'compared to last month',
    background: '#3F51B5',
    color: '#FFFFFF'
  };
  visitsData$: Observable<ChartData>;
  totalVisitsOptions: ChartWidgetOptions = {
    title: 'Visits',
    gain: 42.5,
    subTitle: 'compared to last month',
    background: '#03A9F4',
    color: '#FFFFFF'
  };
  clicksData$: Observable<ChartData>;
  totalClicksOptions: ChartWidgetOptions = {
    title: 'Total Clicks',
    gain: -6.1,
    subTitle: 'compared to last month',
    background: '#4CAF50',
    color: '#FFFFFF'
  };
  conversionsData$: Observable<ChartData>;
  conversionsOptions: ChartWidgetOptions = {
    title: 'Conversions',
    gain: 10.4,
    subTitle: 'compared to last month',
    background: '#009688',
    color: '#FFFFFF'
  };
  salesSummaryData$: Observable<ChartData>;
  salesSummaryOptions: SalesSummaryWidgetOptions = {
    title: 'Sales Summary',
    subTitle: 'Compare Sales by Time',
    gain: 37.2
  };
  top5CategoriesData$: Observable<ChartData>;
  top5CategoriesOptions: DonutChartWidgetOptions = {
    title: 'Top Categories',
    subTitle: 'Compare Sales by Category'
  };
  audienceOverviewOptions: AudienceOverviewWidgetOptions[] = [];
  recentSalesData$: Observable<ChartData>;
  recentSalesOptions: RecentSalesWidgetOptions = {
    title: 'Recent Sales',
    subTitle: 'See who bought what in realtime'
  };
  recentSalesTableOptions = {
    pageSize: 5,
    columns: [
      { name: 'Product', property: 'name', visible: true, isModelProperty: true },
      { name: '$ Price', property: 'price', visible: true, isModelProperty: true },
      { name: 'Time ago', property: 'timestamp', visible: true, isModelProperty: true },
    ]
  };
  recentSalesTableData$: Observable<any[]>;
  advancedPieChartOptions: AdvancedPieChartWidgetOptions = {
    title: 'Sales by country',
    subTitle: 'Top 3 countries sold 34% more items this month\n'
  };
  advancedPieChartData$: Observable<ChartData>;
  private _realtimeUsersDataSubject = new ReplaySubject<RealtimeUsersWidgetData>(30);
  realtimeUsersData$: Observable<RealtimeUsersWidgetData> = this._realtimeUsersDataSubject.asObservable();
  private _realtimeUsersPagesSubject = new ReplaySubject<RealtimeUsersWidgetPages[]>(1);
  realtimeUsersPages$: Observable<RealtimeUsersWidgetPages[]> = this._realtimeUsersPagesSubject.asObservable();
  /**
   * Needed for the Layout
   */
  private _gap = 16;
  gap = `${this._gap}px`;

  customerCount: string;
  orderCount:string;
  declineOrderCount:string;
  refundOrderCount:string;
  chargebackOrderCount:string;
  transections:string;
  straightSale:string;
  constructor(private dashboardService: DashboardService, private router: Router, private apiService: ApiService) {

    /**
     * Edge wrong drawing fix
     * Navigate anywhere and on Promise right back
     */
    if (/Edge/.test(navigator.userAgent)) {
      if (DashboardComponent.isInitialLoad) {
        this.router.navigate(['/apps/chat']).then(() => {
          this.router.navigate(['/']);
        });

        DashboardComponent.isInitialLoad = false;
      }
    }

  }

  col(colAmount: number) {
    return `1 1 calc(${100 / colAmount}% - ${this._gap - (this._gap / colAmount)}px)`;
  }

  /**
   * Everything implemented here is purely for Demo-Demonstration and can be removed and replaced with your implementation
   */
   getData(){
    this.getMidGraphData();
    this.getCustomerOrderData();
   }
  ngOnInit() {
    this.salesData$ = this.dashboardService.getSales();
    this.visitsData$ = this.dashboardService.getVisits();
    this.clicksData$ = this.dashboardService.getClicks();
    this.conversionsData$ = this.dashboardService.getConversions();
    this.salesSummaryData$ = this.dashboardService.getSalesSummary();
    this.top5CategoriesData$ = this.dashboardService.getTop5Categories();
    this.selectDate('today');
    this.getData();
    // Audience Overview Widget
    this.dashboardService.getAudienceOverviewUsers().subscribe(response => {
      this.audienceOverviewOptions.push({
        label: 'Users',
        data: response
      } as AudienceOverviewWidgetOptions);
    });
    this.dashboardService.getAudienceOverviewSessions().subscribe(response => {
      this.audienceOverviewOptions.push({
        label: 'Sessions',
        data: response
      } as AudienceOverviewWidgetOptions);
    });
    this.dashboardService.getAudienceOverviewBounceRate().subscribe(response => {
      const property: AudienceOverviewWidgetOptions = {
        label: 'Bounce Rate',
        data: response
      };

      // Calculate Bounce Rate Average
      const data = response.datasets[0].data as number[];
      property.sum = `${(data.reduce((sum, x) => sum + x) / data.length).toFixed(2)}%`;

      this.audienceOverviewOptions.push(property);
    });

    this.dashboardService.getAudienceOverviewSessionDuration().subscribe(response => {
      const property: AudienceOverviewWidgetOptions = {
        label: 'Session Duration',
        data: response
      };

      // Calculate Average Session Duration and Format to Human Readable Format
      const data = response.datasets[0].data as number[];
      const averageSeconds = (data.reduce((sum, x) => sum + x) / data.length).toFixed(0);
      property.sum = `${averageSeconds} sec`;

      this.audienceOverviewOptions.push(property);
    });

    // Prefill realtimeUsersData with 30 random values
    for (let i = 0; i < 30; i++) {
      this._realtimeUsersDataSubject.next(
        {
          label: moment().fromNow(),
          value: Math.round(Math.random() * (100 - 10) + 10)
        } as RealtimeUsersWidgetData);
    }

    // Simulate incoming values for Realtime Users Widget
    setInterval(() => {
      this._realtimeUsersDataSubject.next(
        {
          label: moment().fromNow(),
          value: Math.round(Math.random() * (100 - 10) + 10)
        } as RealtimeUsersWidgetData);
    }, 5000);

    // Prefill realtimeUsersPages with 3 random values
    const demoPages = [];
    const demoPagesPossibleValues = ['/components', '/tables/all-in-one-table', '/apps/inbox', '/apps/chat', '/dashboard', '/login', '/register', '/apps/calendar', '/forms/form-elements'];
    for (let i = 0; i < 3; i++) {
      const nextPossibleValue = demoPagesPossibleValues[+Math.round(Math.random() * (demoPagesPossibleValues.length - 1))];
      if (demoPages.indexOf(nextPossibleValue) === -1) {
        demoPages.push(nextPossibleValue);
      }

      this._realtimeUsersPagesSubject.next(demoPages.map(pages => {
        return { 'page': pages } as RealtimeUsersWidgetPages;
      }));
    }

    // Simulate incoming values for Realtime Users Widget
    setInterval(() => {
      const nextPossibleValue = demoPagesPossibleValues[+Math.round(Math.random() * (demoPagesPossibleValues.length - 1))];
      if (demoPages.indexOf(nextPossibleValue) === -1) {
        demoPages.push(nextPossibleValue);
      }

      if (demoPages.length > Math.random() * (5 - 1) + 1) {
        demoPages.splice(Math.round(Math.random() * demoPages.length), 1);
      }

      this._realtimeUsersPagesSubject.next(demoPages.map(pages => {
        return { 'page': pages } as RealtimeUsersWidgetPages;
      }));
    }, 5000);

    this.recentSalesTableData$ = this.dashboardService.getRecentSalesTableData();
    this.recentSalesData$ = this.dashboardService.getRecentSalesData();

    this.advancedPieChartData$ = this.dashboardService.getAdvancedPieChartData();


    this.dashboardService.getDashboardData().then(data =>{
      this.customerCount = data.data.customers;
      this.orderCount = data.data.orders;
      this.declineOrderCount = data.data.decline_orders;
      this.refundOrderCount = data.data.refund_orders;
      this.chargebackOrderCount = data.data.chargeback_orders;
      this.transections = data.data.orders + data.data.decline_orders;
      this.straightSale = data.data.orders;
    });

  async selectDate(param) {
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
    return;
  }
}
