import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { ListColumn } from 'src/@fury/shared/list/list-column.model';
// import { Order } from './order.model';

import { fadeInRightAnimation } from 'src/@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from 'src/@fury/animations/fade-in-up.animation';

//self imports
import { FormGroup, FormControl } from '@angular/forms';
import { GoldenTicketComponent } from 'src/app/pages/campaigns/golden-ticket/golden-ticket.component';
import { MyCampaignsService } from '../my-campaigns.service';
import { CampaignService } from 'src/app/pages/campaigns/campaign.service';
import { Subscription } from 'rxjs';
import { formatDate } from '@angular/common';
import { Notyf } from 'notyf';
import { GoldenTicketService } from 'src/app/pages/campaigns/golden-ticket/golden-ticket.service';
import { Router, ActivatedRoute, ParamMap, Params } from '@angular/router';


@Component({
  selector: 'fury-campaign-view',
  templateUrl: './campaign-view.component.html',
  styleUrls: ['./campaign-view.component.scss']
})
export class CampaignViewComponent implements OnInit {
  tickets: [];
   name: string;
  //customer coding
  getSubscription: Subscription;
  isLoading = false;
  totalRows = 0;
  pageSize = 25;
  currentPage = 0;
  pageSizeOptions: number[] = [5, 10, 25, 100];
  filters = {};
  month: string = null;
  months: string[] = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  year: string = null;
  years: number[] = [];
  notyf = new Notyf({ types: [{ type: 'info', background: '#6495ED', icon: '<i class="fa-solid fa-clock"></i>' }] });

  @Input()
  columns: ListColumn[] = [
    { name: 'Checkbox', property: 'checkbox', visible: false },
    { name: 'Month', property: 'month', visible: true, isModelProperty: true },
    { name: 'Year', property: 'year', visible: true, isModelProperty: true },
    { name: 'Initials', property: 'initials', visible: true, isModelProperty: true },
    { name: 'Rebills', property: 'rebills', visible: true, isModelProperty: true },
    { name: 'Cycle 1 %', property: 'cycle_1_per', visible: true, isModelProperty: true },
    { name: 'Cycle 2', property: 'cycle_2', visible: true, isModelProperty: true },
    { name: 'Cycle 2 %', property: 'cycle_2_per', visible: true, isModelProperty: true },
    { name: 'Cycle 3+', property: 'cycle_3_plus', visible: true, isModelProperty: true },
    { name: 'Cycle 3+ %', property: 'cycle_3_plus_per', visible: true, isModelProperty: true },
    { name: 'Avg Ticket', property: 'avg_ticket', visible: true, isModelProperty: true },
    { name: 'Revenue', property: 'revenue', visible: true, isModelProperty: true },
    { name: 'Refund', property: 'refund', visible: true, isModelProperty: true },
    { name: 'Refund Rate', property: 'refund_rate', visible: true, isModelProperty: true },
    { name: 'CBs', property: 'CBs', visible: true, isModelProperty: true },
    { name: 'CB %', property: 'CB_per', visible: true, isModelProperty: true },
    { name: 'CB $', property: 'CB_currency', visible: true, isModelProperty: true },
    { name: 'Fulfillment', property: 'fulfillment', visible: true, isModelProperty: true },
    { name: 'Processing', property: 'processing', visible: true, isModelProperty: true },
    { name: 'CPA', property: 'cpa', visible: true, isModelProperty: true },
    { name: 'CPA AVG', property: 'cpa_avg', visible: true, isModelProperty: true },
    { name: 'Net', property: 'net', visible: true, isModelProperty: true },
    { name: 'CLV', property: 'clv', visible: true, isModelProperty: true },

  ] as ListColumn[];
  dataSource: MatTableDataSource<null>;

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;

  constructor(private dialog: MatDialog, private goldenTicketService: GoldenTicketService, private campaignService: CampaignService, private router: Router, private route: ActivatedRoute) {
    this.getYearsArray(new Date);
  }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  getYearsArray(date) {
    var c_year = date.getFullYear();
    for (var i = c_year; i > c_year - 5; i--) {
      this.years.unshift(i);
    }
    for (var i = c_year + 1; i < c_year + 5; i++) {
      this.years.push(i);
    }
  }

  /**
   * Example on how to get data and pass it to the table - usually you would want a dedicated service with a HTTP request for this
   * We are simulating this request here.
   */

  ngOnInit() {
    this.route.params.subscribe((params: Params) => this.name = params['name']);
    this.getSubscription = this.campaignService.ticketsGetResponse$
      .subscribe(data => this.manageGetResponse(data));
    this.getData();
    // this.getDropData();

    this.dataSource = new MatTableDataSource();
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

  async getData() {
    this.isLoading = true;
    await this.campaignService.getGoldenTicketData()
      .then(tickets => {
        this.tickets = tickets.data;
        this.dataSource.data = tickets.data;
        setTimeout(() => {
          // this.paginator.pageIndex = this.currentPage;
          // this.paginator.length = tickets.pag.count;
        });
        this.isLoading = false;
      }, error => {
        this.isLoading = false;
      });
  }

  manageGetResponse(tickets) {
    if (tickets.status) {
      this.tickets = tickets.data;
      this.dataSource.data = tickets.data;
      setTimeout(() => {
        // this.paginator.pageIndex = this.currentPage;
        // this.paginator.length = tickets.pag.count;
      });
      this.isLoading = false;
    } else {
      this.isLoading = false;
    }
  }

  onFilterChange(value) {
    if (!this.dataSource) {
      return;
    }
    value = value.trim();
    value = value.toLowerCase();
    this.dataSource.filter = value;
  }

  async filterRecord() {
    if (this.month != null || this.year != null) {
      // this.isLoading = true;
      await this.campaignService.filterGoldenTicket(this.month, this.year)
        .then(response => {
          if (response.data.length != 0) {
            this.tickets = response.data;
            this.dataSource.data = response.data;
            this.isLoading = false;
            this.notyf.success('Data Found Successfully');
          } else {
            this.notyf.error('Oops! No Data Found');
          }
        }, error => {
          this.isLoading = false;
          this.notyf.error(error.message);
        });
    } else { this.notyf.error("Please select filter options"); }
  }

  async addCustomMonth() {
    if(this.month && this.year){
      await this.campaignService.addCurrentMonth(this.month, this.year)
      .then(response => {
        if (response.status == true) {
          // this.isLoading = false;
          this.notyf.success(response.message);
          this.getData();
        } else {
          this.notyf.success(response.message);
        }
      }, error => {
        this.isLoading = false;
        this.notyf.error(error.message);
      });
    }else{ this.notyf.error("Select Month and Year");}
  }

  async addCurrentMonth() {
    var currentMonth = this.months[new Date().getMonth()];
    var currentYear = new Date().getFullYear();
    await this.campaignService.addCurrentMonth(currentMonth, currentYear)
      .then(response => {
        if (response.status == true) {
          // this.isLoading = false;
          this.notyf.success(response.message);
          this.getData();
        } else {
          this.notyf.success(response.message);
        }
      }, error => {
        this.isLoading = false;
        this.notyf.error(error.message);
      });
  }

  resetFilters() {
    this.month = null;
    this.year = null;
  }

  async refresh() {
    this.resetFilters();
    this.isLoading = true;
        this.notyf.open({ type: 'info', message: 'Records will be refreshed soon...' });
    await this.campaignService.refreshGoldenTicket()
      .then(tickets => {
        this.tickets = tickets.data;
        this.dataSource.data = tickets.data;
        setTimeout(() => {
          // this.paginator.pageIndex = this.currentPage;
          // this.paginator.length = tickets.pag.count;
        });
        this.isLoading = false;
      }, error => {
        this.isLoading = false;
      });
  }

  ngOnDestroy() {
  }

}
