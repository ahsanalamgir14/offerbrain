
import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { Observable, of, ReplaySubject } from 'rxjs';
import { MatTableDataSource } from '@angular/material/table';
import { ListColumn } from '../../../../@fury/shared/list/list-column.model';
import { fadeInRightAnimation } from '../../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../../@fury/animations/fade-in-up.animation';
import { FormGroup, FormControl } from '@angular/forms';
import { TicketMonthlyService } from './ticket-monthly.service';
import { CampaignService } from './../campaign.service';
import { TicketMonthly } from './ticket-monthly.model';
import { filter, takeUntil, map } from 'rxjs/operators';
import { Subscription } from 'rxjs';
import { formatDate } from '@angular/common';
import { Notyf } from 'notyf';

@Component({
  selector: 'fury-ticket-monthly',
  templateUrl: './ticket-monthly.component.html',
  styleUrls: ['./ticket-monthly.component.scss'],
  animations: [fadeInRightAnimation, fadeInUpAnimation]
})
export class TicketMonthlyComponent implements OnInit {

  subject$: ReplaySubject<TicketMonthly[]> = new ReplaySubject<TicketMonthly[]>(1);
  data$: Observable<TicketMonthly[]> = this.subject$.asObservable();
  tickets: TicketMonthly[];

  getSubscription: Subscription;
  isLoading = false;
  totalRows = 0;
  pageSize = 25;
  currentPage = 0;
  pageSizeOptions: number[] = [5, 10, 25, 100];
  filters = {};
  notyf = new Notyf({ types: [{ type: 'info', background: '#6495ED', icon: '<i class="fa-solid fa-clock"></i>' }] });

  @Input()
  columns: ListColumn[] = [
    { name: 'Checkbox', property: 'checkbox', visible: false },
    { name: 'Month', property: 'month', visible: true, isModelProperty: true },
    // { name: 'Year', property: 'year', visible: true, isModelProperty: true },
    { name: 'Initials', property: 'initials', visible: true, isModelProperty: true },
    { name: 'Rebills', property: 'rebills', visible: true, isModelProperty: true },
    { name: 'Cycle 1 %', property: 'cycle_1_per', visible: true, isModelProperty: true },
    { name: 'Cycle 2', property: 'cycle_2', visible: true, isModelProperty: true },
    { name: 'Cycle 2 %', property: 'cycle_2_per', visible: true, isModelProperty: true },
    { name: 'Cycle 3+', property: 'cycle_3_plus', visible: true, isModelProperty: true },
    { name: 'Cycle 3+ %', property: 'cycle_3_plus_per', visible: true, isModelProperty: true },
    { name: 'AVG Day', property: 'avg_day', visible: false, isModelProperty: true },
    { name: '% Filled', property: 'filled_per', visible: false, isModelProperty: true },
    { name: 'Avg Ticket', property: 'avg_ticket', visible: true, isModelProperty: true },
    { name: 'Revenue', property: 'revenue', visible: true, isModelProperty: true },
    { name: 'Refund', property: 'refund', visible: true, isModelProperty: true },
    { name: 'Refund Rate', property: 'refund_rate', visible: true, isModelProperty: true },
    { name: 'CBs', property: 'CBs', visible: true, isModelProperty: true },
    { name: 'CB %', property: 'CB_per', visible: true, isModelProperty: true },
    { name: 'CB $', property: 'CB_currency', visible: true, isModelProperty: true },
    { name: 'COGS', property: 'COGS', visible: true, isModelProperty: true },
    // { name: 'Fulfillment', property: 'fulfillment', visible: false, isModelProperty: true },
    { name: 'Processing', property: 'processing', visible: true, isModelProperty: true },
    { name: 'CPA', property: 'cpa', visible: true, isModelProperty: true },
    { name: 'CPA AVG', property: 'cpa_avg', visible: true, isModelProperty: true },
    { name: 'Net', property: 'net', visible: true, isModelProperty: true },
    { name: 'CLV', property: 'clv', visible: true, isModelProperty: true },
  ] as ListColumn[];

  dataSource: MatTableDataSource<TicketMonthly> | null;

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;

  constructor(private dialog: MatDialog, private goldenTicketService: TicketMonthlyService, private campaignService: CampaignService) {
  }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  mapData() {
    return of(this.tickets.map(ticket => new TicketMonthly(ticket)));
  }

  ngOnInit() {
    // this.getSubscription = this.campaignService.ticketMonthlyResponse$.subscribe(data => this.manageGetResponse(data));
    this.getData();

    this.dataSource = new MatTableDataSource();
    this.data$.pipe(
      filter(data => !!data)
    ).subscribe((tickets) => {
      this.tickets = tickets;
      this.dataSource.data = tickets;
    });
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
    await this.campaignService.getMonthlyTicket()
      .then(tickets => {
        this.tickets = tickets.data;
        this.dataSource.data = tickets.data;
        this.mapData().subscribe(tickets => {
          this.subject$.next(tickets);
        });
        this.isLoading = false;
      }, error => {
        this.isLoading = false;
      });
  }

  manageGetResponse(data) {
    if (data.status) {
      this.tickets = data.data;
      this.dataSource.data = data.data;
      this.mapData().subscribe(tickets => {
        this.subject$.next(tickets);
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
    await this.campaignService.refreshMonthlyTicket()
      .then(tickets => {
        this.manageGetResponse(tickets)
        this.notyf.success('Records are updated successfully')
      }, error => {
        this.isLoading = false
      });
  }

  async refreshAll() {
    this.notyf.open({ type: 'info', message: 'Records will be refreshed soon...' });
    this.isLoading = true;
    await this.campaignService.refreshAllMonthlyTicket()
      .then(tickets => {
        this.manageGetResponse(tickets)
        this.notyf.success('Records are updated successfully');
      }, error => {
        this.isLoading = false;
      });
  }

  ngOnDestroy() {
  }
}