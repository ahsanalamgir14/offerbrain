




import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { ListColumn } from '../../../../@fury/shared/list/list-column.model';
import { fadeInRightAnimation } from '../../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../../@fury/animations/fade-in-up.animation';
import { FormGroup, FormControl } from '@angular/forms';
import { TicketWeeklyService } from './ticket-weekly.service';
import { CampaignService } from './../campaign.service';
import { Subscription } from 'rxjs';
import { formatDate } from '@angular/common';
import { Notyf } from 'notyf';


@Component({
  selector: 'fury-ticket-weekly',
  templateUrl: './ticket-weekly.component.html',
  styleUrls: ['./ticket-weekly.component.scss'],
  animations: [fadeInRightAnimation, fadeInUpAnimation]
})
export class TicketWeeklyComponent implements OnInit {

  tickets: [];

  //customer coding
  getSubscription: Subscription;
  isLoading = false;
  totalRows = 0;
  pageSize = 25;
  currentPage = 0;
  pageSizeOptions: number[] = [5, 10, 25, 100];
  filters = {};
  // month: string = null;
  // months: string[] = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  // year: string = null;
  // years: number[] = [];
    notyf = new Notyf({ types: [ { type: 'info', background: '#6495ED', icon: '<i class="fa-solid fa-clock"></i>' }] });

  @Input()
  columns: ListColumn[] = [

    { name: 'Checkbox', property: 'checkbox', visible: false },
    { name: 'Week', property: 'week', visible: true, isModelProperty: true },
    // { name: 'Year', property: 'year', visible: true, isModelProperty: true },
    { name: 'Volume', property: 'volume', visible: true, isModelProperty: true },
    { name: 'Rebills', property: 'rebills', visible: true, isModelProperty: true },
    { name: 'Rebill %', property: 'rebill_per', visible: true, isModelProperty: true },
    { name: 'AVG Day %', property: 'avg_per', visible: true, isModelProperty: true },
    { name: '% Filled', property: 'filled_per', visible: true, isModelProperty: true },
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

  constructor(private dialog: MatDialog, private goldenTicketService: TicketWeeklyService, private campaignService: CampaignService) {
  }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }


  /**
   * Example on how to get data and pass it to the table - usually you would want a dedicated service with a HTTP request for this
   * We are simulating this request here.
   */

  ngOnInit() {
    this.getSubscription = this.campaignService.ticketWeeklyResponse$.subscribe(data => this.manageGetResponse(data));
    this.getData();

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
    await this.campaignService.getWeeklyTicket()
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

  async refresh() {
    this.notyf.open({ type: 'info', message: 'Records will be refreshed soon...' });
    this.isLoading = true;
    await this.campaignService.refreshWeeklyTicket()
      .then(tickets => {
        this.tickets = tickets.data;
        this.dataSource.data = tickets.data;
        this.notyf.success('Records are updated successfully');
        this.isLoading = false;
      }, error => {
        this.isLoading = false;
      });
  }

  ngOnDestroy() {
  }
}