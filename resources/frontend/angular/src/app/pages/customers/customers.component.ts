import { SelectionModel } from '@angular/cdk/collections';
import { Location } from '@angular/common';
import { AfterViewInit, Component, ElementRef, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Notyf } from 'notyf';
import { Observable, of, ReplaySubject, Subscription } from 'rxjs';
import { filter } from 'rxjs/operators';
import { fadeInRightAnimation } from '../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../@fury/animations/fade-in-up.animation';
import { ListColumn } from '../../../@fury/shared/list/list-column.model';
import { ConfirmationDialogModel } from '../confirmation-dialog/confirmation-dialog';
import { ConfirmationDialogComponent } from '../confirmation-dialog/confirmation-dialog.component';
import { CustomerDetailComponent } from './customer-detail/customer-detail.component';
import { Customer } from './Customers.model';
import { CustomersService } from './customers.service';

@Component({
  selector: 'fury-customers',
  templateUrl: './customers.component.html',
  styleUrls: ['./customers.component.scss'],
  animations: [fadeInRightAnimation, fadeInUpAnimation]
})
export class CustomersComponent implements OnInit, AfterViewInit, OnDestroy {

  subject$: ReplaySubject<Customer[]> = new ReplaySubject<Customer[]>(1);
  data$: Observable<Customer[]> = this.subject$.asObservable();
  getSubscription: Subscription;
  deleteSubscription: Subscription;
  search = '';
  customers: Customer[];
  filters = {};
  idArray = [];
  allIdArray = [];
  all_fields = [];
  all_values = [];
  totalRows = 0;
  pageSize = 25;
  currentPage = 1;
  pageSizeOptions: number[] = [5, 10, 25, 100];
  name: string;
  isChecked: boolean = false;
  isLoading: boolean = false;
  isDeleting: boolean = false;
  timer: any;
  notyf = new Notyf();
  customer_id = '';

  @Input()
  columns: ListColumn[] = [
    { name: 'Checkbox', property: 'checkbox', visible: true },
    { name: 'Customer Id', property: 'id', visible: true, isModelProperty: true },
    // { name: 'Date Last Order', property: 'order_last_date', visible: true, isModelProperty: true },
    { name: 'Orders Count', property: 'orders_count', visible: true, isModelProperty: true },
    { name: 'First Name', property: 'first_name', visible: true, isModelProperty: true },
    { name: 'Last Name', property: 'last_name', visible: true, isModelProperty: true },
    { name: 'Email', property: 'email', visible: true, isModelProperty: true },
    { name: 'Phone', property: 'phone', visible: true, isModelProperty: true },
    { name: 'IP Address', property: 'ip_address', visible: true, isModelProperty: true },
    { name: 'Actions', property: 'actions', visible: true },
  ] as ListColumn[];
  dataSource: MatTableDataSource<Customer> | null;
  selection = new SelectionModel<Customer>(true, []);

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;

  constructor(private dialog: MatDialog, private customersService: CustomersService, private location: Location) { }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  mapData() {
    return of(this.customers.map(customer => new Customer(customer)));
  }

  ngAfterViewInit() {
    this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }

  ngOnInit(): void {
    this.location.replaceState('/customer');
    this.deleteSubscription = this.customersService.deleteResponse$.subscribe(data => this.manageDeleteResponse(data));
    this.getData();
    this.dataSource = new MatTableDataSource();
    this.data$.pipe(

      filter(data => !!data)
    ).subscribe((customers) => {
      this.customers = customers;
      this.dataSource.data = customers;
    });
  }

  pageChanged(event: PageEvent) {
    this.pageSize = event.pageSize;
    this.currentPage = event.pageIndex;
    this.getData();
  }

  async getData() {
    this.isDeleting = false;
    this.isLoading = true;
    this.isChecked = false;
    this.filters = {
      "currentPage": this.currentPage,
      "pageSize": this.pageSize,
      "search": this.search,
      'all_fields': this.all_fields,
      'all_values': this.all_values,
      'customer_id': this.customer_id,
    }
    await this.customersService.getCustomers(this.filters)
      .then(customers => {
        this.allIdArray = [];
        this.customers = customers.data.data;
        setTimeout(() => {
          this.paginator.pageIndex = this.currentPage;
          this.paginator.length = customers.pag.count;
        });
        this.mapData().subscribe(customers => {
          this.subject$.next(customers);
        });
        for (var i = 0; i < customers.data.data.length; i++) {
          this.allIdArray.push(customers.data.data[i].id);
        }
        this.isLoading = false;
      }, error => {
        this.isLoading = false;
      });
  }
  
  commonFilter(value, field) {
    if (this.all_fields.indexOf(field) === -1) {
      this.all_fields.push(field);
      this.all_values.push(value);
    } else {
      let index = this.all_fields.indexOf(field);
      this.all_values[index] = value;
    }
  }

  onFilterChange(value) {
    value = value.toLowerCase();
    this.search = value;
    clearTimeout(this.timer);
    this.timer = setTimeout(() => { this.getData() }, 500)
  }

  async manageDeleteResponse(data) {
    if (data.status) {
      await this.getData();
      this.notyf.success({ duration: 5000, message: data.message });
    }
  }

  openDialog(id) {
    const dialogRef = this.dialog.open(CustomerDetailComponent, {
      disableClose: true,
      data: { id: id }
    });
    dialogRef.updateSize('1000px');
    dialogRef.afterClosed().subscribe(result => {
    });
  }

  isAllSelected() {
    const numSelected = this.selection.selected.length;
    const numRows = this.dataSource.data.length;
    return numSelected === numRows;
  }

  masterToggle(event) {
    this.isAllSelected() ?
      this.selection.clear() :
      this.dataSource.data.forEach(
        row => this.selection.select(row)
      );
    if (event.checked == false) {
      this.idArray = [];
      this.idArray.length = 0;
    } else {
      this.idArray = this.allIdArray;
    }
    if (this.idArray.length != 0) {
      this.isChecked = true;
    } else {
      this.isChecked = false;
    }
  }

  selectToggle(event, value) {
    if (event.checked) {
      this.idArray.push(value);
    } else {
      this.idArray.splice(this.idArray.indexOf(value), 1);
    }
    if (this.idArray.length != 0) {
      this.isChecked = true;
    } else {
      this.isChecked = false;
    }
  }

  deleteRecord() {
    this.handleDeleteAction(this.idArray);
  }

  handleDeleteAction(id) {
    const dialogData = new ConfirmationDialogModel('Confirm Delete', 'Are you sure you want to delete this customer?');
    const dialogRef = this.dialog.open(ConfirmationDialogComponent, {
      maxWidth: '500px',
      closeOnNavigation: true,
      disableClose: true,
      data: dialogData
    })
    dialogRef.afterClosed().subscribe(dialogResult => {
      if (dialogResult) {
        this.customersService.deleteData(id);
        this.isDeleting = true;
        // this.dataSource.data = [];
        this.idArray = [];
      }
    });
  }

  ngOnDestroy(): void {
    if (this.deleteSubscription) {
      this.customersService.deleteResponse.next([]);
      this.deleteSubscription.unsubscribe();
    }
  }
}
