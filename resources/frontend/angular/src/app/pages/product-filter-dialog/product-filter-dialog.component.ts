import { Component, Inject, OnInit, Input, ViewChild } from '@angular/core';
import { ProductFilterDialogModel } from './product-filter-dialog';
import { environment } from 'src/environments/environment';

import { Subscription, Observable, of, ReplaySubject } from 'rxjs';
import { Product } from './product-filter-dialog.model';
import { ListColumn } from '../../../@fury/shared/list/list-column.model';
import { MatTableDataSource } from '@angular/material/table';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatDialog, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { filter } from 'rxjs/operators';
import { SelectionModel } from '@angular/cdk/collections';
import { fadeInRightAnimation } from '../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../@fury/animations/fade-in-up.animation';
import { FormGroup, FormControl } from '@angular/forms';


@Component({
  selector: 'fury-product-filter-dialog',
  templateUrl: './product-filter-dialog.component.html',
  styleUrls: ['./product-filter-dialog.component.scss'],
  animations: [fadeInRightAnimation, fadeInUpAnimation]
})
export class ProductFilterDialogComponent implements OnInit {
  subject$: ReplaySubject<Product[]> = new ReplaySubject<Product[]>(1);
  data$: Observable<Product[]> = this.subject$.asObservable();
  getSubscription: Subscription;
  deleteSubscription: Subscription;
  search = '';
  customers: Product[];

  // id : string;
  field: string;
  start_date : string;
  end_date : string;
  filterProducts : any;
  endPoint = '';
  products = [];
  idArray = [];
  allIdArray = [];
  isChecked: boolean = false;
  isLoading: boolean = true;
  timer: any;

  @Input()
  columns: ListColumn[] = [
    { name: 'Checkbox', property: 'checkbox', visible: true },
    // { name: 'ID', property: 'id', visible: false, isModelProperty: true },
    { name: 'Name', property: 'name', visible: true, isModelProperty: true },
  ] as ListColumn[];
  dataSource: MatTableDataSource<Product> | null;
  selection = new SelectionModel<Product>(true, []);

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;
  
  constructor(public dialogRef: MatDialogRef<ProductFilterDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: ProductFilterDialogModel, private dialog: MatDialog) {
      this.start_date = data.start_date;
      this.end_date = data.end_date;
      // this.field = data.field;
      this.endPoint = environment.endpoint;
      this.products = data.filterProducts;
      this.isLoading = false;
      this.mapData().subscribe(products => {
        this.subject$.next(products);
        this.isLoading = false;
      });
  }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  mapData() {
    return of(this.products.map(product => new Product(product)));
  }

  ngAfterViewInit() {
    this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }

  ngOnInit(): void {
    // this.getData();
    this.dataSource = new MatTableDataSource();
    this.data$.pipe(
      filter(data => !!data)
    ).subscribe((products) => {
      this.products = products;
      this.dataSource.data = products;
    });
  }
  async getData(){
    // const response = fetch(`${this.endPoint}/api/getProductForFilter?start_date=${this.start_date}&end_date=${this.end_date}&field=${this.field}`).then(res => res.json()).then((data) => {
    //   if(data.status){
    //     this.products = data.data;
    //       this.mapData().subscribe(products => {
    //         this.subject$.next(products);
    //         this.isLoading = false;
    //       });
    //     }
    // });
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
  async getSelectedProductList(){
    this.dialogRef.close(this.idArray);
  }
  onFilterChange(value) {
    value = value.toLowerCase();
    this.search = value;
    clearTimeout(this.timer);
    this.timer = setTimeout(() => { this.getData() }, 500)
  }
}
