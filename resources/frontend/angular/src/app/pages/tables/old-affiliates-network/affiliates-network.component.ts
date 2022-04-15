import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Observable, of, ReplaySubject } from 'rxjs';
import { filter } from 'rxjs/operators';
import { ListColumn } from '../../../../@fury/shared/list/list-column.model';
import { Network } from './network.model';
import { fadeInRightAnimation } from '../../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../../@fury/animations/fade-in-up.animation';

//self imports
import { FormGroup, FormControl } from '@angular/forms';
import { AffiliatesNetworkService } from './affiliates-network.service';
import { Subscription } from 'rxjs';
import { SelectionModel } from '@angular/cdk/collections';
import { Notyf } from 'notyf';

@Component({
  selector: 'fury-affiliates-network',
  templateUrl: './affiliates-network.component.html',
  styleUrls: ['./affiliates-network.component.scss'],
  animations: [fadeInRightAnimation, fadeInUpAnimation]
})
export class AffiliatesNetworkComponent implements OnInit {

  networks: [];

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
  columns: ListColumn[] = [
    { name: 'Checkbox', property: 'checkbox', visible: true },
    { name: 'Network Id', property: 'network_id', visible: true, isModelProperty: true },
    { name: 'Customer Id', property: 'customer_id', visible: true, isModelProperty: true },
    { name: 'Name', property: 'name', visible: true, isModelProperty: true },
    { name: 'identifier', property: 'identifier', visible: true, isModelProperty: true },
    { name: 'account_status', property: 'account_status', visible: true, isModelProperty: true },
    { name: 'displayed_name', property: 'displayed_name', visible: true, isModelProperty: true },
    { name: 'is_show_name', property: 'is_show_name', visible: true, isModelProperty: true },
    { name: 'timezone_id', property: 'timezone_id', visible: true, isModelProperty: true },
    { name: 'language_id', property: 'language_id', visible: true, isModelProperty: true },
    { name: 'currency_id', property: 'currency_id', visible: true, isModelProperty: true },
    { name: 'logo_image_url', property: 'logo_image_url', visible: true, isModelProperty: true },
    { name: 'favicon_image_url', property: 'favicon_image_url', visible: true, isModelProperty: true },
    { name: 'support_email', property: 'support_email', visible: true, isModelProperty: true },
    { name: 'email_background_logo_color', property: 'email_background_logo_color', visible: true, isModelProperty: true },
    { name: 'time_created', property: 'time_created', visible: true, isModelProperty: true },
    { name: 'time_saved', property: 'time_saved', visible: true, isModelProperty: true },
    { name: 'relationships', property: 'relationships', visible: true, isModelProperty: true },
    { name: 'Actions', property: 'actions', visible: true },

  ] as ListColumn[];
  dataSource: MatTableDataSource<Network>;
  selection = new SelectionModel<Network>(true, []);

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;

  constructor(private dialog: MatDialog, private networkService: AffiliatesNetworkService) { }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  ngOnInit(): void {
    // this.getSubscription = this.networkService.customersGetResponse$.subscribe(data => this.manageGetResponse(data));
    // this.deleteSubscription = this.networkService.deleteResponse$.subscribe(data => this.manageDeleteResponse(data));
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
    await this.networkService.getNetworks()
      .then(networks => {
        this.allIdArray = [];
        this.networks = networks.data;
        this.dataSource.data = networks.data;
        setTimeout(() => {
          // this.paginator.pageIndex = this.currentPage;
          // this.paginator.length = networks.pag.count;
        });
        this.isLoading = false;
      }, error => {
        this.isLoading = false;
      });
  }

  onFilterChange(value) {
    if (!this.dataSource) {
      return;
    }
    value = value.trim();
    value = value.toLowerCase();
    this.dataSource.filter = value;
  }

  viewDetails(id) {
    console.log(id);
  }

  handleDeleteAction(id) {
    console.log(id);
  }

  ngOnDestroy(): void {
    if (this.deleteSubscription) {
      // this.networkService.deleteResponse.next([]);
      // this.deleteSubscription.unsubscribe();
    }
  }
}

