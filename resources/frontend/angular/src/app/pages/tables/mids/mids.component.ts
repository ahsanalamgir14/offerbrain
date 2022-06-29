import { formatDate } from '@angular/common';
import { AfterViewInit, Component, ElementRef, OnDestroy, OnInit, Pipe, ViewChild } from '@angular/core';
import { FormControl, FormGroup } from '@angular/forms';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSelect } from '@angular/material/select';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Router } from '@angular/router';
import { Notyf } from 'notyf';
import { Observable, of, ReplaySubject, Subject, Subscription, timer } from 'rxjs';
import { filter, takeUntil, map } from 'rxjs/operators';
import { ListComponent } from 'src/@fury/shared/list/list.component';
import { ListService } from 'src/@fury/shared/list/list.service';
import { ApiService } from 'src/app/api.service';
import { environment } from 'src/environments/environment';
import { fadeInRightAnimation } from '../../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../../@fury/animations/fade-in-up.animation';
import { ConfirmationDialogModel } from '../../confirmation-dialog/confirmation-dialog';
import { ConfirmationDialogComponent } from '../../confirmation-dialog/confirmation-dialog.component';
import { MidDetailDialogComponent } from '../../mid-detail-dialog/mid-detail-dialog.component';
import { ProductFilterDialogComponent } from '../../product-filter-dialog/product-filter-dialog.component';
import { MidGroupsComponent } from '../mid-groups/mid-groups.component';
import { GroupDialogModel } from './group-dialog/group-dialog';
import { GroupDialogComponent } from './group-dialog/group-dialog.component';
import { Mid } from './mid.model';
import { MidsService } from './mids.service';

@Component({
  selector: 'fury-mids',
  templateUrl: './mids.component.html',
  styleUrls: ['./mids.component.scss'],
  animations: [fadeInRightAnimation, fadeInUpAnimation],
  providers: [MidGroupsComponent],
})
export class MidsComponent implements OnInit, AfterViewInit, OnDestroy {

  subject$: ReplaySubject<Mid[]> = new ReplaySubject<Mid[]>(1);
  data$: Observable<Mid[]> = this.subject$.asObservable();
  filteredProducts: ReplaySubject<any[]> = new ReplaySubject<any[]>(1);
  filteredMids: ReplaySubject<any[]> = new ReplaySubject<any[]>(1);
  _onDestroy: Subject<void> = new Subject<void>();
  mids: Mid[];

  range = new FormGroup({
    start: new FormControl(),
    end: new FormControl()
  });

  refreshSubscription: Subscription;
  assignSubscription: Subscription;
  unAssignSubscription: Subscription;
  bulkUpdateSubscription: Subscription;
  columnsSubscription: Subscription;
  searchSubscription: Subscription;
  getProductsSubscription: Subscription;
  resetInitialsSubscription: Subscription;
  timerSubscription: Subscription;
  refreshInitialsSubscription: Subscription;
  isLoading = false;
  totalRows = 0;
  pageSize = 25;
  currentPage = 0;
  all_fields = [];
  all_values = [];
  filters = {};
  endPoint = '';
  start_date = '';
  end_date = '';
  selectedMids = '';
  filteredProduct = '';
  filteredMid = new FormControl();
  productSearchCtrl: FormControl = new FormControl();
  midSearchCtrl: FormControl = new FormControl();
  skeletonLoader = true;
  pageSizeOptions: number[] = [5, 10, 25, 100];
  totalMids: number = 0;
  assignedMids: number = 0;
  unAssignedMids: number = 0;
  unInitializedMids: number = 0;
  totalActive: number = 0;
  totalClosed: number = 0;
  selectedRows: Mid[] = [];
  selectAll: boolean = false;
  isBulkUpdate: boolean = false;
  isColumnLoading: boolean = true;
  isProductLoaded: boolean = false;
  columns: any = [];
  notyf = new Notyf({ types: [{ type: 'info', background: '#6495ED', icon: '<i class="fa-solid fa-clock"></i>' }] });
  productOptions = [];
  filterProducts = [];
  midOptions = [];
  timer = null;
  productId = [];
  products = [];
  dataSource: MatTableDataSource<Mid> | null;


  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;
  @ViewChild(ListComponent, { static: true }) ListComponent: ListComponent;
  @ViewChild('singleSelect', { static: true }) singleSelect: MatSelect;

  constructor(private dialog: MatDialog, public midsService: MidsService, private apiService: ApiService, private router: Router, public midGroupComponent: MidGroupsComponent, private listService: ListService) {
    this.endPoint = environment.endpoint;
    this.notyf.dismissAll();
  }

  ngOnInit() {

    this.notyf.dismissAll();
    this.refreshSubscription = this.midsService.refreshResponse$.subscribe(data => this.manageRefreshResponse(data))
    this.assignSubscription = this.midsService.assignGroupResponse$.subscribe(data => this.manageAssignResponse(data))
    this.unAssignSubscription = this.midsService.unAssignGroupResponse$.subscribe(data => this.manageUnassignResponse(data))
    this.bulkUpdateSubscription = this.midsService.assignBulkGroupResponse$.subscribe(data => this.manageBulkGroupResponse(data))
    this.getProductsSubscription = this.midsService.getProductsResponse$.subscribe(data => this.manageProductsResponse(data))
    this.resetInitialsSubscription = this.midsService.resetInitialsResponse$.subscribe(data => this.manageResetInitialsResponse(data))
    this.refreshInitialsSubscription = this.midsService.refreshInitialsResponse$.subscribe(data => this.manageRefreshInitialsResponse(data))
    this.selectDate('thisMonth');
    this.getMidOptions();
    this.getProductFilterData();
    this.getData();
    this.dataSource = new MatTableDataSource();
    this.data$.pipe(
      filter(data => !!data)
    ).subscribe((mids) => {
      this.mids = mids;
      this.dataSource.data = mids;
    });

    this.productSearchCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.filterProductOptions();
      });

    this.midSearchCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.filterMidOptions();
      });


    this.timerSubscription = timer(3600 * 1000, 3600 * 1000).pipe(
      map(() => {
        this.midsService.refreshInitials();
      })
    ).subscribe();
  }
  
  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  mapData() {
    return of(this.mids.map(mid => new Mid(mid)));
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

  onItemSelect(item: any) {
    console.log(item);
  }
  onSelectAll(items: any) {
    console.log(items);
  }

  async getData() {
    this.getProductFilterData();
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
      "all_fields": this.all_fields,
      "all_values": this.all_values,
      "product_id": this.filteredProduct,
      'selected_mids': this.selectedMids
    }

    await this.midsService.getColumns().then(columns => {
      this.columns = columns.data;
    });
    await this.midsService.getMids(this.filters).then(mids => {
      this.mids = mids.data
      this.totalMids = mids.data.length
      this.mapData().subscribe(mids => {
        this.subject$.next(mids);
      });
      this.skeletonLoader = false;
      this.isLoading = false;
      this.selectAll = false;
      this.isBulkUpdate = false;
      this.selectedRows = [];
      this.ListComponent.filter.nativeElement.value = '';
    }, error => {
      this.skeletonLoader = false;
      this.isLoading = false;
    });
    this.countContent();
    // for (let i = 0; i < this.mids.length; i++) {
    // this.toolTipDeclines[i] = this.getTooltipDeclines(this.mids[i]);
    // this.toolTipMidCount[i] = this.getTooltipMidCounts(this.mids[i]);

    // this.filterProducts.indexOf(this.mids[i].product_name) === -1 ? this.filterProducts.push(this.mids[i].product_name) : console.log("This item already exists");
    // }
  }
  // getProducts() {
  //   this.midsService.getProducts();
  // }

  getTooltipDeclines(mid) {
    var productNames = [];
    // let data = {};
    // if (mid.decline_orders.decline_data) {
    //   data = mid.decline_orders.decline_data;
    // }
    // let totalDeclinedOrders = mid.decline_orders.total_declined;
    // if (totalDeclinedOrders != 0) {
    //   Object.values(data).forEach(v => {
    //     if (v['name'] != undefined) {
    //       let list = '';
    //       list += v['name'] + '\xa0\xa0\xa0 | \xa0\xa0\xa0' + v['count'] + '\xa0\xa0\xa0 | \xa0\xa0\xa0' + v['percentage'] + '%';
    //       if (!productNames.includes(list)) {
    //         productNames.push(list);
    //       }
    //     }
    //   });
    //   productNames.push('Total: ' + '\xa0\xa0\xa0 | \xa0\xa0\xa0' + totalDeclinedOrders + '\xa0\xa0\xa0 | \xa0\xa0\xa0' + (totalDeclinedOrders / 100).toFixed(2) + '%');
    // }
    return productNames;
  }

  openDialog(id, gateway_id, evt, total_count, status, type) {
    // if (total_count != 0) {
      let targetAttr = evt.target.getBoundingClientRect();
      clearTimeout(this.timer);
      this.timer = setTimeout(() => {
        const target = new ElementRef(evt.currentTarget);
        const dialogRef = this.dialog.open(MidDetailDialogComponent, {
          data: { trigger: target, id: id, gateway_id: gateway_id, start_date: this.start_date, end_date: this.end_date, total_count: total_count, status: status, type: type, product: this.filteredProduct }
        });
      }, 500)
    // }
  }

  openDialogForProductFilter(event, start_date, end_date, field) {
    let filterProducts = this.filterProducts
    let targetAttr = event.target.getBoundingClientRect();
    const dialogRef = this.dialog.open(ProductFilterDialogComponent, {
      height: '500px',
      data: { start_date: start_date, end_date: end_date, field: field, filterProducts: filterProducts }
    })
    dialogRef.afterClosed().subscribe(id => {
      if (id) {
        this.filteredProduct = id;
        this.getData();
      }
    });
  }

  async getProductFilterData() {
    const startDate = formatDate(this.range.get('start').value, 'yyyy/MM/dd', 'en');
    const endDate = formatDate(this.range.get('end').value, 'yyyy/MM/dd', 'en');
    await this.midsService.getProducts(startDate, endDate).then(products => {
      this.filterProducts = products.data;
      this.isProductLoaded = true;
    })
  }

  countContent() {
    this.assignedMids = 0;
    this.unAssignedMids = 0;
    this.unInitializedMids = 0;

    this.mids.forEach((mid) => {
      if (mid.current_monthly_amount == '0.00') {
        this.unInitializedMids++;
      }
      else if (!mid.mid_group) {
        this.unAssignedMids++;
      } else {
        this.assignedMids++;
      }

      if (mid.is_active == 1) {
        this.totalActive++;
      } else if(mid.is_active == 0) {
        this.totalClosed++;
      }

      // if (mid.current_monthly_amount == '0.00') {
      //   this.totalPaused++;
      // }
    });
  }

  commonFilter(value, field) {
    this.filteredProduct = value;
    if (this.all_fields.indexOf(field) === -1) {
      this.all_fields.push(field);
      this.all_values.push(value);
    } else {
      let index = this.all_fields.indexOf(field);
      this.all_values[index] = value;
    }
  }

  async manageAssignResponse(data) {
    if (Object.keys(data).length) {
      if (data.status) {
        await this.getData();
        this.notyf.success(data.message);
        this.midGroupComponent.refresh();
      } else if (!data.status) {
        this.notyf.error({ duration: 0, dismissible: true, message: data.message });
      }
    }
  }

  async manageUnassignResponse(data) {
    if (Object.keys(data).length) {
      if (data.status) {
        await this.getData();
        this.notyf.success(data.message);
        this.midGroupComponent.refresh();
      }
    }
  }

  async manageBulkGroupResponse(data) {
    if (data.status) {
      await this.getData();
      this.notyf.success(data.message);
      this.midGroupComponent.refresh();
      this.isBulkUpdate = false;
    }
  }

  manageProductsResponse(data) {
    if (data.status) {
      this.productOptions = data.data;
      this.filteredProducts.next(this.productOptions.slice());
      console.log(' this.productOptions  :', this.productOptions);
    }
  }

  manageResetInitialsResponse(data) {
    if (data.status) {
      this.getData();
      this.notyf.success(data.message);
    }
  }

  manageRefreshInitialsResponse(data) {
    if (data.status) {
      this.getData();
      this.notyf.success(data.message);
    }
  }

  async manageRefreshResponse(data) {
    if (data.status) {
      await this.getData();
      this.notyf.success(data.data.new_mids + ' New Mids Found and ' + data.data.updated_mids + ' Mids Updated');
      this.midGroupComponent.refresh();
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

  viewMidDetails(alias) {
    this.router.navigate(['mid-view', alias]);
  }

  refresh() {
    this.isLoading = true;
    this.midsService.refresh();
  }

  handleDeleteAction(alias) {
    const dialogData = new ConfirmationDialogModel('Confirm Delete', 'Are you sure to remove this from group?');
    const dialogRef = this.dialog.open(ConfirmationDialogComponent, {
      maxWidth: '500px',
      closeOnNavigation: true,
      data: dialogData
    })
    dialogRef.afterClosed().subscribe(dialogResult => {
      if (dialogResult) {
        this.midsService.deleteData(alias);
      }
    });
  }

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

  handleBulkDeleteAction() {
    const dialogData = new ConfirmationDialogModel('Confirm Delete', 'Are you sure to remove these mids from group?');
    const dialogRef = this.dialog.open(ConfirmationDialogComponent, {
      maxWidth: '500px',
      closeOnNavigation: true,
      data: dialogData
    })
    dialogRef.afterClosed().subscribe(dialogResult => {
      if (dialogResult) {
        this.midsService.assignBulkGroup('', this.selectedRows);
        this.selectedRows = [];
      }
    });
  }

  openAssignDialog(alias) {
    const dialogData = new GroupDialogModel('Assign New Group to: ' + alias, 'Please select Mid-Group from the following options.', []);
    const dialogRef = this.dialog.open(GroupDialogComponent, {
      maxWidth: '500px',
      closeOnNavigation: true,
      data: dialogData
    })
    dialogRef.afterClosed().subscribe(groupName => {
      if (groupName) {
        this.midsService.assignGroup(alias, groupName);
      }
    });
  }

  updateCheck() {
    this.selectedRows = [];
    if (this.selectAll === true) {
      this.mids.map((mid) => {
        mid.checked = true;
        this.selectedRows.push(mid);
        this.isBulkUpdate = true;
      });

    } else {
      this.mids.map((mid) => {
        mid.checked = false;
        this.isBulkUpdate = false;
      });
      this.isBulkUpdate = false;
    }
    console.log(this.selectedRows);
  }

  assignBulkGroup() {
    const dialogData = new GroupDialogModel('Assign New Group to: ', 'Please select Mid-Group from the following options.', this.selectedRows);
    const dialogRef = this.dialog.open(GroupDialogComponent, {
      // maxHeight: '650px',
      maxWidth: '500px',
      closeOnNavigation: true,
      data: dialogData
    })
    dialogRef.afterClosed().subscribe(groupName => {
      if (groupName) {
        this.midsService.assignBulkGroup(groupName, this.selectedRows);
        this.selectedRows = [];
      }
    });
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

  async refreshColumns() {
    await this.midsService.getColumns().then(columns => {
      this.columns = columns.data;
    });
  }

  ngOnDestroy(): void {
    this.notyf.dismissAll();
    if (this.refreshSubscription) {
      this.midsService.refreshResponse.next({});
      this.refreshSubscription.unsubscribe();
    }
    if (this.assignSubscription) {
      this.midsService.assignGroupResponse.next({});
      this.assignSubscription.unsubscribe();
    }
    if (this.bulkUpdateSubscription) {
      this.midsService.assignBulkGroupResponse.next({});
      this.bulkUpdateSubscription.unsubscribe();
    }
    if (this.unAssignSubscription) {
      this.midsService.unAssignGroupResponse.next({});
      this.unAssignSubscription.unsubscribe();
    }
    if (this.searchSubscription) {
      this.listService.searchResponse.next([]);
      this.searchSubscription.unsubscribe();
    }

    this._onDestroy.next();
    this._onDestroy.complete();
  }


  async getMidOptions() {
    this.midsService.getMidOptions().then(data => {
      this.midOptions = data.data;
      this.filteredMids.next(data.data);
    });
  }

  reset() {
    this.selectedMids = '';
    this.filteredProduct = '';
    this.getMidOptions();
    this.getProductFilterData();
    this.selectDate('thisMonth');
  }

  handleDateChange() {
    if (this.range.get('end').value != null) {
      this.getProductFilterData();
    }
  }

  protected filterProductOptions() {
    if (!this.productOptions) {
      return;
    }
    // get the search keyword
    let search = this.productSearchCtrl.value;
    if (!search) {
      this.filteredProducts.next(this.productOptions.slice());
      return;
    } else {
      search = search.toLowerCase();
    }
    this.filteredProducts.next(
      this.productOptions.filter(bank => bank.name.toLowerCase().indexOf(search) > -1)
    );
  }

  protected filterMidOptions() {
    if (!this.midOptions) {
      return;
    }
    let search = this.midSearchCtrl.value;
    if (!search) {
      this.filteredMids.next(this.midOptions.slice());
      return;
    } else {
      search = search.toLowerCase();
    }
    this.filteredMids.next(
      this.midOptions.filter(bank => bank.gateway_alias.toLowerCase().indexOf(search) > -1)
    );
  }
}