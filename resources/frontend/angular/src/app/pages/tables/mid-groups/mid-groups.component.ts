import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Observable, of, ReplaySubject, observable } from 'rxjs';
import { filter } from 'rxjs/operators';
import { ListColumn } from 'src/@fury/shared/list/list-column.model';
import { MidGroup } from './mid-groups.model';
import { fadeInRightAnimation } from 'src/@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from 'src/@fury/animations/fade-in-up.animation';
import { FormGroup, FormControl } from '@angular/forms';
import { MidGroupsService } from './mid-groups.service';
import { Subscription } from 'rxjs';
import { formatDate } from '@angular/common';
import { ApiService } from 'src/app/api.service';
import { MidsDetailComponent } from './mids-detail/mids-detail.component';
import { ActionDialogComponent } from './action-dialog/action-dialog.component';
import { Notyf } from 'notyf';
import { ListService } from 'src/@fury/shared/list/list.service';
import { ActionDialogService } from './action-dialog/action-dialog.service'


@Component({
  selector: 'fury-mid-groups',
  templateUrl: './mid-groups.component.html',
  styleUrls: ['./mid-groups.component.scss'],
  animations: [fadeInRightAnimation, fadeInUpAnimation],
})
export class MidGroupsComponent implements OnInit, AfterViewInit, OnDestroy {

  subject$: ReplaySubject<MidGroup[]> = new ReplaySubject<MidGroup[]>(1);
  data$: Observable<MidGroup[]> = this.subject$.asObservable();
  midGroups: any;
  authUrl: any;
  getSubscription: Subscription;
  refreshSubscription: Subscription;
  addGroupSubscription: Subscription;
  deleteGroupSubscription: Subscription;
  updateGroupSubscription: Subscription;
  searchSubscription: Subscription;
  isLoading = false;
  totalRows = 0;
  pageSize = 25;
  currentPage = 0;
  all_fields = [];
  all_values = [];
  filterData: any = [];
  filters = {};
  pageSizeOptions: number[] = [5, 10, 25, 100];
  toolTipMids = [];
  start_date = '';
  end_date = '';
  selectAll: boolean = false;
  selectedRows: MidGroup[] = [];
  isBulkUpdate: boolean = false;
  width: string;
  range = new FormGroup({
    start: new FormControl(),
    end: new FormControl()
  });

  skeletonloader = true;
  notyf = new Notyf({ types: [{ type: 'info', background: '#6495ED', icon: '<i class="fa-solid fa-clock"></i>' }] });
  @Input()
  columns: ListColumn[] = [
    // { name: 'Id', property: 'id', visible: true, isModelProperty: true },
    // { name: 'router_id', property: 'router_id', visible: true, isModelProperty: true },
    { name: 'Checkbox', property: 'checkbox', visible: true },
    { name: 'Id', property: 'id', visible: true, isModelProperty: true },
    { name: 'Group Name', property: 'group_name', visible: true, isModelProperty: false },
    { name: 'Assigned Mids', property: 'assigned_mids', visible: true, isModelProperty: false },
    { name: 'Quick Balance', property: 'quick_balance', visible: true, isModelProperty: false },
    { name: 'Suggested Invoice', property: 'suggested_invoice', visible: false, isModelProperty: false },
    { name: 'Last Invoice Date', property: 'last_invoice_date', visible: false, isModelProperty: false },
    { name: 'Last Invoice Amount', property: 'last_invoice_amount', visible: false, isModelProperty: false },
    { name: 'Gross Revenue', property: 'gross_revenue', visible: true, isModelProperty: true },
    { name: 'Bank %', property: 'bank_per', visible: true, isModelProperty: true },
    { name: 'Target Bank Balance', property: 'target_bank_balance', visible: true, isModelProperty: true },
    { name: 'Updated At', property: 'updated_at', visible: true, isModelProperty: true },
    { name: 'Actions', property: 'actions', visible: true },

  ] as ListColumn[];
  dataSource: MatTableDataSource<MidGroup> | null;

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;
  @ViewChild(MidGroupsComponent, { static: true }) MidGroupComponent: MidGroupsComponent;

  constructor(private dialog: MatDialog, private midGroupService: MidGroupsService, private actionService: ActionDialogService) {
    this.notyf.dismissAll();
  }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  ngOnInit() {
    this.notyf.dismissAll();
    this.selectDate('thisMonth');
    this.getSubscription = this.midGroupService.getResponse$.subscribe(data => this.manageGetResponse(data));
    this.refreshSubscription = this.midGroupService.refreshResponse$.subscribe(data => this.manageRefreshResponse(data));
    this.addGroupSubscription = this.midGroupService.addGroupResponse$.subscribe(data => this.manageAddGroupResponse(data));
    this.deleteGroupSubscription = this.midGroupService.deleteGroupResponse$.subscribe(data => this.manageDeleteGroupResponse(data));
    this.updateGroupSubscription = this.midGroupService.updateGroupResponse$.subscribe(data => this.manageUpdateResponse(data));
    this.bankAccounts();
    this.getData();
    this.dataSource = new MatTableDataSource();
    this.data$.pipe(
      filter(data => !!data)
    ).subscribe((midGroups) => {
      this.midGroups = midGroups;
      this.dataSource.data = midGroups;
    });
  }

  checkQuickAccounts() {
    this.midGroupService.checkQuickAccounts('checkQuickAccounts').subscribe(
      {
        next: (res) => {
          this.getData()
        },
      }
    );
  }

  updateCheck() {
    this.selectedRows = [];
    if (this.selectAll === true) {
      this.midGroups.map((midGroups) => {
        midGroups.checked = true;
        this.selectedRows.push(midGroups);
        this.isBulkUpdate = true;
      });

    } else {
      this.midGroups.map((midGroups) => {
        midGroups.checked = false;
        this.isBulkUpdate = false;
      });
      this.isBulkUpdate = false;
    }
  }

  updateCheckedRow(event: any, row) {
    if (event.checked) {
      row.checked = true;
      this.selectedRows.push(row);
      this.isBulkUpdate = true;
    } else {
      row.checked = false;
      this.selectedRows.splice(this.selectedRows.indexOf(row), 1);
      if (this.selectedRows.length === 0) {
        this.isBulkUpdate = false;
      }
    }
  }

  bankAccounts() {
    this.midGroupService.getAccounts('bankAccounts', 0).subscribe(
      {
        next: (res) => {
          this.updateQuickBalance(res);
        },
      }
    );

  }

  updateQuickBalance(data) {
    this.midGroupService.updateQuickBalance(data, 'updateQuickBalance').subscribe(
      {
        next: (res) => {
          this.getData()
        },
      }
    );
  }

  async getQuickAccounts() {

    await this.actionService.quickbookCon('quickbookConnect', 0, 0)
      .then(res => {
        let data = res;
        this.bankAccounts();

      }, error => {
      });
  }

  mapData() {
    return of(this.midGroups.map(midGroup => new MidGroup(midGroup)));
  }

  ngAfterViewInit() {
    // this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }

  // pageChanged(event: PageEvent) {
  //   this.pageSize = event.pageSize;
  //   this.currentPage = event.pageIndex;
  //   this.getData();
  // }

  async getData() {
    this.isLoading = true;
    if (this.range.get('start').value != null) {
      this.start_date = formatDate(this.range.get('start').value, 'yyyy/MM/dd', 'en')
    }
    if (this.range.get('end').value != null) {
      this.end_date = formatDate(this.range.get('end').value, 'yyyy/MM/dd', 'en')
    }
    this.filters = {
      "start": this.start_date,
      "end": this.end_date,
    }

    await this.midGroupService.getMidGroups(this.filters)
      .then(midGroups => {
        this.midGroups = midGroups.data;
        this.dataSource.data = midGroups.data;
        this.mapData().subscribe(midGroups => {
          this.subject$.next(midGroups);
        });
        this.skeletonloader = false;
        this.isLoading = false;
      }, error => {
        this.skeletonloader = false;
        this.isLoading = false;
      });
    for (let i = 0; i < this.midGroups.length; i++) {
      this.toolTipMids[i] = this.getAssignedMids(this.midGroups[i]);
    }
  }

  getAssignedMids(midGroup) {
    var mid_names = [];
    midGroup.mids_data.forEach(function (mid) {
      let list = '';
      list += mid.gateway_alias + '\xa0\xa0\xa0 | \xa0\xa0\xa0' + mid.current_monthly_amount + '\xa0\xa0\xa0 | \xa0\xa0\xa0' + mid.processing_percent;
      mid_names.push(list);
    });
    return mid_names;
  }

  actionDialog(action, quick, obj) {
    obj.action = action;
    obj.quick = quick;
    obj.midRow = this.selectedRows;
    if (quick == 'invoice' || quick == 'Invoice History') {
      this.width = '700px';
    } else {
      this.width = '500px';
    }
    if (action == 'Add') {
      this.checkQuickAccounts();
    }
    const dialogRef = this.dialog.open(ActionDialogComponent, {
      width: this.width,
      disableClose: true,
      data: obj
    });

    dialogRef.afterClosed().subscribe(result => {
      if (result.event == 'Add') {
        this.addNewGroup(result.data);

      } else if (result.event == 'Update') {
        this.updateRowData(result.data);
      } else if (result.event == 'Delete') {
        this.deleteRowData(result.data);
      }
      else if (result.event == 'connect') {
        //alert('to dialog close action(connect)')
        this.updateRowData(result.data);
        this.getData();
      }
      else if (result.event == 'disConnect') {
        //alert('to dialog close action(disConnect)')
        this.getData();
      }
    });
  }

  addNewGroup(data) {
    if (data) {
      this.midGroupService.addGroup(data);

    }
  }

  updateRowData(data) {
    if (data) {
      this.midGroupService.updateGroup(data);
    }
  }

  deleteRowData(data) {
    if (data) {
      this.midGroupService.deleteGroup(data);
    }
    // this.dataSource = this.dataSource.filter((value,key)=>{
    //   return value.id != row_obj.id;
    // });
  }


  manageGetResponse(data) {
    // this.midGroups = data.data;
    // for (let i = 0; i < this.midGroups.length; i++) {
    //   this.midGroups[i].mids_data.forEach(function (mid) {
    //     this.toolTipMids[i] = mid.alias;
    //   });
    // }
  }

  async manageRefreshResponse(data) {
    if (Object.keys(data).length) {
      if (data.status) {
        await this.getData();
        this.notyf.success(data.data.new + ' New Mid Groups Found and ' + data.data.updated + ' Mids Updated');
      }
    }
  }

  async manageAddGroupResponse(data) {
    if (Object.keys(data).length) {
      if (data.status) {
        await this.getData();
        this.notyf.success(data.data.message);
      }
    }
  }

  async manageDeleteGroupResponse(data) {
    if (Object.keys(data).length) {
      if (data.status) {
        await this.getData();
        this.notyf.success(data.data.message);
      }
    }
  }

  async manageUpdateResponse(data) {
    if (Object.keys(data).length) {
      if (data.status) {
        await this.getData();
        this.notyf.success(data.data.message);
      }
    }
  }

  // manageSearchResponse(midGroups) {
  //   if (midGroups.status) {
  //     this.midGroups = midGroups.data;
  //     this.dataSource.data = midGroups.data;
  //     this.mapData().subscribe(midGroups => {
  //       this.subject$.next(midGroups);
  //     });
  //     this.skeletonloader = false;
  //     this.isLoading = false;
  //   }
  //   for (let i = 0; i < this.midGroups.length; i++) {
  //     this.toolTipMids[i] = this.getAssignedMids(this.midGroups[i]);
  //   }
  // }

  onFilterChange(value) {
    if (!this.dataSource) {
      return;
    }
    value = value.trim();
    value = value.toLowerCase();
    this.dataSource.filter = value;
  }

  openDialog(data) {
    if (data.assigned_mids != 0) {
      const dialogRef = this.dialog.open(MidsDetailComponent, {
        disableClose: true,
        data: {
          group: data.group_name,
          mids: data.mids_data
        }
      });
      dialogRef.afterClosed().subscribe(result => {
      });
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

  refresh() {
    this.isLoading = true;
    this.midGroupService.refresh();
  }

  ngOnDestroy() {
    this.notyf.dismissAll();
    if (this.refreshSubscription) {
      this.midGroupService.refreshResponse.next({});
      this.refreshSubscription.unsubscribe();
    }
    if (this.getSubscription) {
      this.midGroupService.getResponse.next({});
      this.getSubscription.unsubscribe();
    }
    if (this.addGroupSubscription) {
      this.midGroupService.addGroupResponse.next({});
      this.addGroupSubscription.unsubscribe();
    }
    if (this.deleteGroupSubscription) {
      this.midGroupService.deleteGroupResponse.next({});
      this.deleteGroupSubscription.unsubscribe();
    }
    if (this.updateGroupSubscription) {
      this.midGroupService.updateGroupResponse.next({});
      this.updateGroupSubscription.unsubscribe();
    }
  }
}