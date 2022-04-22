import { Component, OnInit, Inject, Input, AfterViewInit, OnDestroy, ViewChild } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { fadeInRightAnimation } from 'src/@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from 'src/@fury/animations/fade-in-up.animation';
import { Subscription, Observable, of, ReplaySubject } from 'rxjs';
import { filter } from 'rxjs/operators';
import { ListColumn } from 'src/@fury/shared/list/list-column.model';
import { MatTableDataSource } from '@angular/material/table';
import { Mid } from './mids-detail.model';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { Notyf } from 'notyf';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  selector: 'fury-mids-detail',
  templateUrl: './mids-detail.component.html',
  styleUrls: ['./mids-detail.component.scss'],
  animations: [fadeInRightAnimation, fadeInUpAnimation]

})

export class MidsDetailComponent implements OnInit, AfterViewInit, OnDestroy {

  subject$: ReplaySubject<Mid[]> = new ReplaySubject<Mid[]>(1);
  data$: Observable<Mid[]> = this.subject$.asObservable();
  mids: Mid[];
  group: string;

  getSubscription: Subscription;
  refreshSubscription: Subscription;
  getProductsSubscription: Subscription;
  isLoading = false;
  totalRows = 0;
  pageSize = 25;
  currentPage = 0;
  all_fields = [];
  all_values = [];
  filterData: any = [];
  filters = {};
  endPoint = '';
  pageSizeOptions: number[] = [5, 10, 25, 100];
  notyf = new Notyf({ types: [{ type: 'info', background: '#6495ED', icon: '<i class="fa-solid fa-clock"></i>' }] });

  @Input()
  columns: ListColumn[] = [

    // { name: 'Id', property: 'id', visible: true, isModelProperty: true },
    // { name: 'router_id', property: 'router_id', visible: true, isModelProperty: true },
    { name: 'Gateway Id', property: 'gateway_id', visible: true, isModelProperty: true },
    { name: 'Gateway Alias', property: 'gateway_alias', visible: true, isModelProperty: true },
    // { name: 'Group Name', property: 'mid_group', visible: true, isModelProperty: false },
    { name: 'Router Date In', property: 'router_date_in', visible: false, isModelProperty: true },
    { name: 'Router Desc', property: 'router_desc', visible: false, isModelProperty: true },
    { name: 'Mid Group Setting Id', property: 'mid_group_setting_id', visible: false, isModelProperty: true },
    { name: 'Mid Group Setting', property: 'mid_group_setting', visible: false, isModelProperty: true },
    { name: 'Strict Preserve', property: 'is_strict_preserve', visible: false, isModelProperty: true },
    { name: 'Campaign Id', property: 'campaign_id', visible: false, isModelProperty: true },
    { name: 'Global Monthly Cap', property: 'global_monthly_cap', visible: true, isModelProperty: true },
    { name: 'Current Monthly Amount', property: 'current_monthly_amount', visible: true, isModelProperty: true },
    { name: 'Processing Percent', property: 'processing_percent', visible: true, isModelProperty: true },
    { name: '3d Routed', property: 'is_three_d_routed', visible: false, isModelProperty: true },
    { name: 'Created On', property: 'created_on', visible: false, isModelProperty: true },
    // { name: 'Actions', property: 'actions', visible: true },

  ] as ListColumn[];

  dataSource: MatTableDataSource<Mid> | null;

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  constructor(@Inject(MAT_DIALOG_DATA) private data: any, private dialogRef: MatDialogRef<MidsDetailComponent>, private router: Router, private route: ActivatedRoute, ) {
    if (data) {
      this.mids = data.mids;
      this.group = data.group;
      this.dataSource = new MatTableDataSource(this.mids);
      this.mapData().subscribe(mids => {
        this.subject$.next(mids);
      });
    }
  }

  ngOnInit(): void {
    this.data$.pipe(
      filter(data => !!data)
    ).subscribe((mids) => {
      this.mids = mids;
      this.dataSource.data = mids;
    });
  }

  mapData() {
    return of(this.mids.map(mid => new Mid(mid)));
  }

  ngAfterViewInit() {
    // this.dataSource.paginator = this.paginator;
    // this.dataSource.sort = this.sort;
  }

  pageChanged(event: PageEvent) {
    // this.pageSize = event.pageSize;
    // this.currentPage = event.pageIndex;
    // this.getData();
  }

  viewMidDetails(alias) {
    this.dialogRef.close();
    this.router.navigate(['mid-view', alias]);
  }

  ngOnDestroy() {
    // if (this.refreshSubscription) {
    //   this.midsService.refreshResponse.next([]);
    //   this.refreshSubscription.unsubscribe();
    // }
  }
}