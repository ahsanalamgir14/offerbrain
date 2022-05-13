import { AfterViewInit, Component, ElementRef, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { Router, ActivatedRoute, ParamMap } from '@angular/router';
import { Observable, of, ReplaySubject, Subscription } from 'rxjs';
import { filter } from 'rxjs/operators';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatTableDataSource } from '@angular/material/table';
import { formatDate } from '@angular/common';
import { FormGroup, FormControl } from '@angular/forms';
import { MatDialog } from '@angular/material/dialog';
import { MatSort } from '@angular/material/sort';
import { fadeInRightAnimation } from '../../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../../@fury/animations/fade-in-up.animation';
import { ConfirmationDialogComponent } from '../../confirmation-dialog/confirmation-dialog.component';
import { MidDetailDialogComponent } from '../../mid-detail-dialog/mid-detail-dialog.component';
import { ConfirmationDialogModel } from '../../confirmation-dialog/confirmation-dialog';
import { GroupDialogComponent } from './group-dialog/group-dialog.component';
import { GroupDialogModel } from './group-dialog/group-dialog';
import { MidGroupsComponent } from '../mid-groups/mid-groups.component';
import { Pipe, PipeTransform } from '@angular/core';
import { environment } from 'src/environments/environment';
import { ApiService } from 'src/app/api.service';
import { MidsService } from './mids.service';
import { Mid } from './mid.model';
import { Notyf } from 'notyf';
import { ListService } from 'src/@fury/shared/list/list.service';
import { ListComponent } from 'src/@fury/shared/list/list.component';
import { RevenueDialogModel } from './revenue-dialog/revenue-dialog.model';
import { RevenueDialogComponent } from './revenue-dialog/revenue-dialog.component';

@Pipe({ name: 'tooltipList' })
export class TooltipListPipe implements PipeTransform {

  transform(lines: string[]): string {
    let list: string = '';
    lines.forEach(line => {
      list += '• ' + line + '\n';
    });
    return list;
  }
}
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
  mids: Mid[];
  savedMids: Mid[];

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
  isLoading = false;
  totalRows = 0;
  pageSize = 25;
  currentPage = 0;
  all_fields = [];
  all_values = [];
  filterData: any = [];
  filters = {};
  endPoint = '';
  start_date = '';
  end_date = '';
  filteredProduct = '';
  product = "allProducts";

  skeletonLoader = true; 
  pageSizeOptions: number[] = [5, 10, 25, 100];
  totalMids: number = 0;
  assignedMids: number = 0;
  unAssignedMids: number = 0;
  unInitializedMids: number = 0;
  totalClosed: number = 0;
  totalPaused: number = 0;
  selectedRows: Mid[] = [];
  selectAll: boolean = false;
  isBulkUpdate: boolean = false;
  columns: any = [];
  notyf = new Notyf({ types: [{ type: 'info', background: '#6495ED', icon: '<i class="fa-solid fa-clock"></i>' }] });
  toolTipDeclines = [];
  toolTipMidCount = [];
  productOptions = [];
  // product = "allProducts";
  timer = null;
  products = [];
  // pageSize = 20000;
  dataSource: MatTableDataSource<Mid> | null;

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;
  @ViewChild(ListComponent, { static: true }) ListComponent: ListComponent;

  constructor(private dialog: MatDialog, private midsService: MidsService, private apiService: ApiService, private router: Router, public midGroupComponent: MidGroupsComponent, private listService: ListService) {
    this.endPoint = environment.endpoint;
    this.notyf.dismissAll();
  }

  ngOnInit() {
    
    this.notyf.dismissAll();
    this.refreshSubscription = this.midsService.refreshResponse$.subscribe(data => this.manageRefreshResponse(data))
    this.assignSubscription = this.midsService.assignGroupResponse$.subscribe(data => this.manageAssignResponse(data))
    this.unAssignSubscription = this.midsService.unAssignGroupResponse$.subscribe(data => this.manageUnassignResponse(data))
    this.bulkUpdateSubscription = this.midsService.assignBulkGroupResponse$.subscribe(data => this.manageBulkGroupResponse(data))
    this.searchSubscription = this.listService.searchResponse$.subscribe(data => this.manageSearchResponse(data))
    this.selectDate('lastThreeMonths');
    this.getProducts();
    this.getData();
    //this.midsService.getOrderProduct();
    // console.log(this.getProductsName());
    this.getProductsName();
    this.dataSource = new MatTableDataSource();
    this.data$.pipe(
      filter(data => !!data)
    ).subscribe((mids) => {
      this.mids = mids;
      this.dataSource.data = mids;
    });
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

  async getData() {
    this.isLoading = true;
    if (this.range.get('start').value != null) {
      this.start_date = formatDate(this.range.get('start').value, 'yyyy/MM/dd', 'en')
    }
    if (this.range.get('end').value != null) {
      this.end_date = formatDate(this.range.get('end').value, 'yyyy/MM/dd', 'en')
    }


    this.filters = {
      // "product_value": this.product_value, 
      "start": this.start_date,
      "end": this.end_date,
      "all_fields": this.all_fields,
      "all_values": this.all_values,
    }

    await this.midsService.getColumns().then(columns => {
      this.columns = columns.data;
    });
    await this.midsService.getMids(this.filters).then(mids => {
      this.mids = mids.data
      this.savedMids = mids.data;
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
    for (let i = 0; i < this.mids.length; i++) {
      // this.toolTipDeclines[i] = this.getTooltipDeclines(this.mids[i]);
      this.toolTipMidCount[i] = this.getTooltipMidCounts(this.mids[i]);
    }
  }
  async getProducts(){
    await this.midsService.getProducts().then(products => {
      this.products = products.data;
    });
  }

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

  getTooltipMidCounts(mid) {
    var midCountArray = [];
    let data = [];
    if (mid.mid_count.mid_count_data) {
      data = mid.mid_count.mid_count_data.sort((a, b) => (a.name > b.name) ? 1 : -1);
      console.log('data :', data);
    }
    let totalMids = mid.mid_count.mid_count;
    if (totalMids != 0) {
      data.forEach(function (v) {
        if (v.name != undefined) {
          let list = '';
          list += v.name + '\xa0\xa0\xa0 | \xa0\xa0\xa0' + v.count + '\xa0\xa0\xa0 | \xa0\xa0\xa0' + v.percentage + '%';
          if (!midCountArray.includes(list)) {
            midCountArray.push(list);
          }
        }
      });
      midCountArray.push('Total: ' + '\xa0\xa0\xa0 | \xa0\xa0\xa0' + totalMids + '\xa0\xa0\xa0 | \xa0\xa0\xa0' + (totalMids / 100).toFixed(2) + '%');
    }
    return midCountArray;
  }

  openDialog(id, gateway_id, evt: MouseEvent, total_count, status, type){
    clearTimeout(this.timer); 
    this.timer = setTimeout(() =>{
        const target = new ElementRef(evt.currentTarget);
        const dialogRef = this.dialog.open(MidDetailDialogComponent, {
          data: { trigger: target, id: id, gateway_id : gateway_id, start_date : this.start_date, end_date : this.end_date, total_count : total_count, status : status, type : type, product : this.filteredProduct }
        });
    },500)
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

      if (mid.gateway_alias.indexOf("CLOSED") !== -1) {
        this.totalClosed++;
      }
      // if(mid.gateway_alias.indexOf("CLOSING") !== -1){
      //   this.totalPaused++;
      // }

      if (mid.current_monthly_amount == '0.00') {
        this.totalPaused++;
      }
    });
  }

  async getDropData() {
    const response = fetch(`${this.endPoint}/api/getDropDownContent`)
      .then(res => res.json()).then((data) => {
        this.filterData = data;
      });
  }

  // manageGetResponse(mids) {
  //   if (mids.status) {
  //     this.mids = mids.data;
  //     this.dataSource.data = mids.data;
  //     this.isLoading = false;
  //   } else {
  //     this.isLoading = false;
  //   }
  // }

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

  manageSearchResponse(mids) {
    if (mids.status) {
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

      this.countContent();
      for (let i = 0; i < this.mids.length; i++) {
        this.toolTipDeclines[i] = this.getTooltipDeclines(this.mids[i]);
        this.toolTipMidCount[i] = this.getTooltipMidCounts(this.mids[i]);
      }
    }
    console.log('search data :', mids);

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
        // this.isLoading = true;
        // this.dataSource.data = [];
        // this.idArray = [];
      }
    });
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
  openRevenueDialog(mid) {
    const dialogData = new RevenueDialogModel('Revenue Details: ' + mid.gateway_alias, '', mid);
    const dialogRef = this.dialog.open(RevenueDialogComponent, {
      maxWidth: '500px',
      closeOnNavigation: true,
      data: dialogData
    })
    dialogRef.afterClosed().subscribe(groupName => {
      // if (groupName) {
      //   this.midsService.assignGroup(alias, groupName);
      // }
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
  }

  async getProductsName() {
    const response = fetch(`${this.endPoint}/api/order-products`)
      .then(res => res.json()).then((data) => {
        this.productOptions = data.data;
        this.productOptions =  Object.entries(this.productOptions).map(([type, value]) => ({type, value}));
      });
       return this.productOptions;            
  }

  // commonFilter(value, field) {
  //   // console.log(this.all_fields.indexOf(field));
  //   if (this.all_fields.indexOf(field) === -1) {
  //     this.all_fields.push(field);
  //     this.all_values.push(value);
  //   } else {
  //     let index = this.all_fields.indexOf(field);
  //     this.all_values[index] = value;
  //   }
  //   // this.getData();
  // }

}