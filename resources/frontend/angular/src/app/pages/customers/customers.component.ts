import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { ListColumn } from '../../../@fury/shared/list/list-column.model';
import { fadeInRightAnimation } from '../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../@fury/animations/fade-in-up.animation';
import { FormGroup, FormControl } from '@angular/forms';
import { CustomersService } from './customers.service';
import { Subscription } from 'rxjs';
import { formatDate } from '@angular/common';
import { Customer } from './Customers.model';
import { CustomerDetailComponent } from './customer-detail/customer-detail.component';
import { SelectionModel } from '@angular/cdk/collections';
import { Notyf } from 'notyf';
import { ConfirmationDialogComponent } from '../confirmation-dialog/confirmation-dialog.component';
import { ConfirmationDialogModel } from '../confirmation-dialog/confirmation-dialog';

@Component({
  selector: 'fury-customers',
  templateUrl: './customers.component.html',
  styleUrls: ['./customers.component.scss'],
  animations: [fadeInRightAnimation, fadeInUpAnimation]
})
export class CustomersComponent implements OnInit, AfterViewInit, OnDestroy {

  // subject$: ReplaySubject<Customer[]> = new ReplaySubject<Customer[]>(1);
  // data$: Observable<Customer[]> = this.subject$.asObservable();
  customers: [];

  //customer coding
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

  @Input()
  // @Input() public customerId;
  columns: ListColumn[] = [
    { name: 'Checkbox', property: 'checkbox', visible: true },
    { name: 'Customer Id', property: 'id', visible: true, isModelProperty: true },
    { name: 'Email', property: 'email', visible: true, isModelProperty: true },
    { name: 'First Name', property: 'first_name', visible: true, isModelProperty: true },
    { name: 'Last Name', property: 'last_name', visible: true, isModelProperty: true },
    { name: 'Phone', property: 'phone', visible: true, isModelProperty: true },
    { name: 'Actions', property: 'actions', visible: true },
  ] as ListColumn[];
  dataSource: MatTableDataSource<Customer>;
  selection = new SelectionModel<Customer>(true, []);

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;

  constructor(private dialog: MatDialog, private customersService: CustomersService) { }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  ngOnInit(): void {
    this.getSubscription = this.customersService.customersGetResponse$.subscribe(data => this.manageGetResponse(data));
    this.deleteSubscription = this.customersService.deleteResponse$.subscribe(data => this.manageDeleteResponse(data));
    this.getData();
    this.dataSource = new MatTableDataSource();
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
    this.filters = {
      "currentPage": this.currentPage,
      "pageSize": this.pageSize,
      "search": this.search,
    }
    await this.customersService.getCustomers(this.filters)
      .then(customers => {
        this.allIdArray = [];
        this.customers = customers.data.data;
        this.dataSource.data = customers.data.data;
        for(var i = 0; i < customers.data.data.length; i++){
          this.allIdArray.push(customers.data.data[i].id);
        }
        setTimeout(() => {
          this.paginator.pageIndex = this.currentPage;
          this.paginator.length = customers.pag.count;
        });
        this.isLoading = false;
      }, error => {
        this.isLoading = false;
      });
  }

  onFilterChange(value) {
    // if (!this.dataSource) {
    //   return;
    // }
    // value = value.trim();
    // value = value.toLowerCase();
    // this.dataSource.filter = value;
    value = value.toLowerCase();
    this.search = value;
    clearTimeout(this.timer); 
    this.timer = setTimeout(() => { this.getData() }, 500)
  }

  manageGetResponse(customers) {
    if (customers.status) {
      this.customers = customers.data.data;
      this.dataSource.data = customers.data.data;
      setTimeout(() => {
        this.paginator.pageIndex = this.currentPage;
        this.paginator.length = customers.pag.count;
      });
      this.isLoading = false;
    } else {
      this.isLoading = false;
    }
  }

  manageDeleteResponse(data) {
    if (data.status) {
      this.notyf.success(data.message);
      this.getData();
    }
  }

  openDialog(id) {
    const dialogRef = this.dialog.open(CustomerDetailComponent, {
      data: { id: id }
    });

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
    if(event.checked == false){
      this.idArray = [];
      this.idArray.length = 0;
    } else {
      this.idArray = this.allIdArray;
    }
    if(this.idArray.length != 0){
      this.isChecked = true;
    } else {
      this.isChecked = false;
    }
  }
  selectToggle(event, value) {
    if(event.checked){
      this.idArray.push(value);
    } else {
      this.idArray.splice(this.idArray.indexOf(value), 1);
    }
    if(this.idArray.length != 0){
      this.isChecked = true;
    } else {
      this.isChecked = false;
    }
  }
  deleteRecord(){
    this.handleDeleteAction(this.idArray);
  }


  handleDeleteAction(id) {
    const dialogData = new ConfirmationDialogModel('Confirm Delete', 'Are you sure you want to delete this customer?');
    const dialogRef = this.dialog.open(ConfirmationDialogComponent, {
      maxWidth: '500px',
      closeOnNavigation: true,
      data: dialogData
    })
    dialogRef.afterClosed().subscribe(dialogResult => {
      if (dialogResult) {
        this.customersService.deleteData(id);
        this.dataSource.data = [];
        this.idArray = [];
      }
    });
  }

  ngOnDestroy(): void {
    if(this.deleteSubscription){
      this.customersService.deleteResponse.next([]);
      this.deleteSubscription.unsubscribe();
    }
  }
}
