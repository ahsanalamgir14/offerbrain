import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { ListColumn } from '../../../../@fury/shared/list/list-column.model';
import { fadeInRightAnimation } from '../../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../../@fury/animations/fade-in-up.animation';
import { FormGroup, FormControl } from '@angular/forms';
import { CampaignsMenuService } from './campaigns-menu.service';
import { Subscription, Observable, of, ReplaySubject } from 'rxjs';
import { filter } from 'rxjs/operators';
import { formatDate } from '@angular/common';
import { Campaign } from './campaigns.model';
// import { CustomerDetailComponent } from './campaign-detail/campaign-detail.component';
import { SelectionModel } from '@angular/cdk/collections';
import { Notyf } from 'notyf';
import { ConfirmationDialogComponent } from '../../confirmation-dialog/confirmation-dialog.component';
import { ConfirmationDialogModel } from '../../confirmation-dialog/confirmation-dialog';
import { Location } from '@angular/common';

@Component({
  selector: 'fury-campaigns',
  templateUrl: './campaigns.component.html',
  styleUrls: ['./campaigns.component.scss']
})
export class CampaignsComponent implements OnInit {
  subject$: ReplaySubject<Campaign[]> = new ReplaySubject<Campaign[]>(1);
  data$: Observable<Campaign[]> = this.subject$.asObservable();
  getSubscription: Subscription;
  deleteSubscription: Subscription;
  search = '';
  campaigns: Campaign[];
  filters = {};
  address = [];
  idArray = [];
  allIdArray = [];
  id: number;
  totalRows = 0;
  pageSize = 25;
  currentPage = 0;
  pageSizeOptions: number[] = [5, 10, 25, 100];
  name: string;
  isChecked: boolean = false;
  isLoading: boolean = false;
  isDeleting: boolean = false;
  timer: any;
  notyf = new Notyf();

  @Input()
  columns: ListColumn[] = [
    { name: 'id', property: 'id', visible: true, isModelProperty: true },
    { name: 'campaign_id', property: 'campaign_id', visible: true, isModelProperty: true },
    // { name: 'gateway_id', property: 'gateway_id', visible: true, isModelProperty: true },
    // { name: 'is_active', property: 'is_active', visible: true, isModelProperty: true},
    { name: 'tax_provider_id', property: 'tax_provider_id', visible: false, isModelProperty: false },
    { name: 'data_verification_provider_id', property: 'data_verification_provider_id', visible: false, isModelProperty: false },
    { name: 'site_url', property: 'site_url', visible: false, isModelProperty: false },
    { name: 'is_archived', property: 'is_archived', visible: false, isModelProperty: false },
    { name: 'is_archivedprepaid_blocked', property: 'is_archivedprepaid_blocked', visible: false, isModelProperty: false },
    { name: 'is_custom_price_allowed', property: 'is_custom_price_allowed', visible: false, isModelProperty: false },
    { name: 'is_avs_enabled', property: 'is_avs_enabled', visible: false, isModelProperty: false },
    { name: 'is_collections_enabled', property: 'is_collections_enabled', visible: false, isModelProperty: false },
    { name: 'archived_at', property: 'archived_at', visible: false, isModelProperty: false },
    { name: 'name', property: 'name', visible: true, isModelProperty: true },
    { name: 'description', property: 'description', visible: true, isModelProperty: true },
    { name: 'pre_auth_amount', property: 'pre_auth_amount', visible: false, isModelProperty: false },
    { name: 'creator', property: 'creator', visible: false, isModelProperty: false },
    { name: 'updator', property: 'updator', visible: false, isModelProperty: false },
    { name: 'countries', property: 'countries', visible: false, isModelProperty: false },
    { name: 'fulfillment_id', property: 'fulfillment_id', visible: false, isModelProperty: false },
    { name: 'check_provider_id', property: 'check_provider_id', visible: false, isModelProperty: false },
    { name: 'membership_provider_id', property: 'membership_provider_id', visible: false, isModelProperty: false },
    { name: 'call_confirm_provider_id', property: 'call_confirm_provider_id', visible: false, isModelProperty: false },
    { name: 'chargeback_provider_id', property: 'chargeback_provider_id', visible: false, isModelProperty: false },
    { name: 'prospect_provider_id', property: 'prospect_provider_id', visible: false, isModelProperty: false },
    { name: 'email_provider_id', property: 'email_provider_id', visible: false, isModelProperty: false },
    { name: 'offers', property: 'offers', visible: false, isModelProperty: false },
    { name: 'channel', property: 'channel', visible: false, isModelProperty: false },
    { name: 'payment_methods', property: 'payment_methods', visible: false, isModelProperty: false },
    { name: 'gateway', property: 'gateway', visible: false, isModelProperty: false },
    { name: 'alternative_payments', property: 'alternative_payments', visible: false, isModelProperty: false },
    { name: 'shipping_profiles', property: 'shipping_profiles', visible: false, isModelProperty: false },
    { name: 'return_profiles', property: 'return_profiles', visible: false, isModelProperty: false },
    { name: 'postback_profiles', property: 'postback_profiles', visible: false, isModelProperty: false },
    { name: 'coupon_profiles', property: 'coupon_profiles', visible: false, isModelProperty: false },
    { name: 'fraud_providers', property: 'fraud_providers', visible: false, isModelProperty: false },
    { name: 'volume_discounts', property: 'volume_discounts', visible: false, isModelProperty: false },
    { name: 'created_at', property: 'created_at', visible: true, isModelProperty: true },
    // { name: 'updated_at', property: 'updated_at', visible: true, isModelProperty: true },
  ] as ListColumn[];
  dataSource: MatTableDataSource<Campaign> | null;
  selection = new SelectionModel<Campaign>(true, []);

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;

  constructor(private dialog: MatDialog, private campaignsService: CampaignsMenuService, private location: Location) { }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property); ''
  }

  mapData() {
    return of(this.campaigns.map(campaign => new Campaign(campaign)));
  }

  ngAfterViewInit() {
    this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }

  ngOnInit(): void {
    // this.getSubscription = this.campaignsService.customersGetResponse$.subscribe(data => this.manageGetResponse(data));
    // this.deleteSubscription = this.campaignsService.deleteResponse$.subscribe(data => this.manageDeleteResponse(data));
    this.getData();
    this.dataSource = new MatTableDataSource();
    this.data$.pipe(
      filter(data => !!data)
    ).subscribe((campaigns) => {
      this.campaigns = campaigns;
      this.dataSource.data = campaigns;
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
      // "currentPage": this.currentPage,
      // "pageSize": this.pageSize,
      // "search": this.search,
    }
    await this.campaignsService.getCampaigns(this.filters)
      .then(campaigns => {
        // this.allIdArray = [];
        this.campaigns = campaigns.data;
        this.dataSource.data = campaigns.data;
        setTimeout(() => {
          this.paginator.pageIndex = this.currentPage;
          this.paginator.length = campaigns.pag.count;
        });
        this.mapData().subscribe(prospects => {
          this.subject$.next(prospects);
        });
        // for (var i = 0; i < campaigns.data.data.length; i++) {
        //   this.allIdArray.push(campaigns.data.data[i].id);
        // }
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

  // manageGetResponse(campaigns) {
  //   if (campaigns.status) {
  //     this.campaigns = campaigns.data.data;
  //     this.dataSource.data = campaigns.data.data;
  //     setTimeout(() => {
  //       this.paginator.pageIndex = this.currentPage;
  //       this.paginator.length = campaigns.pag.count;
  //     });
  //     this.isLoading = false;
  //   } else {
  //     this.isLoading = false;
  //   }
  // }


  async manageDeleteResponse(data) {
    if (data.status) {
      await this.getData();
      this.notyf.success({ duration: 5000, message: data.message });
    }
  }

  openDialog(id) {
    // const dialogRef = this.dialog.open(CustomerDetailComponent, {
    //   disableClose: true,
    //   data: { id: id }
    // });
    // dialogRef.updateSize('1000px');
    // dialogRef.afterClosed().subscribe(result => {
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

  deleteRecord() {
    this.handleDeleteAction(this.idArray);
  }

  handleDeleteAction(id) {
    const dialogData = new ConfirmationDialogModel('Confirm Delete', 'Are you sure you want to delete this campaign?');
    const dialogRef = this.dialog.open(ConfirmationDialogComponent, {
      maxWidth: '500px',
      closeOnNavigation: true,
      disableClose: true,
      data: dialogData
    })
    dialogRef.afterClosed().subscribe(dialogResult => {
      if (dialogResult) {
        // this.campaignsService.deleteData(id);
        this.isDeleting = true;
        // this.dataSource.data = [];
        this.idArray = [];
      }
    });
  }

  ngOnDestroy(): void {
    if (this.deleteSubscription) {
      // this.campaignsService.deleteResponse.next([]);
      this.deleteSubscription.unsubscribe();
    }
  }
}
