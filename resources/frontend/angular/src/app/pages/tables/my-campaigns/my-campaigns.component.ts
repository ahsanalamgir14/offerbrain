import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { ListColumn } from '../../../../@fury/shared/list/list-column.model';
import { fadeInRightAnimation } from '../../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../../@fury/animations/fade-in-up.animation';
import { FormGroup, FormControl } from '@angular/forms';
import { MyCampaignsService } from './my-campaigns.service';
import { Subscription, Observable, of, ReplaySubject } from 'rxjs';
import { filter } from 'rxjs/operators';
import { formatDate } from '@angular/common';
import { Campaign } from './my-campaigns.model';
import { SelectionModel } from '@angular/cdk/collections';
import { Notyf } from 'notyf';
import { ConfirmationDialogComponent } from '../../confirmation-dialog/confirmation-dialog.component';
import { ConfirmationDialogModel } from '../../confirmation-dialog/confirmation-dialog';
import { Location } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  selector: 'fury-campaigns',
  templateUrl: './my-campaigns.component.html',
  styleUrls: ['./my-campaigns.component.scss'],
  animations: [fadeInRightAnimation, fadeInUpAnimation],
})

export class MyCampaignsComponent implements OnInit {
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
  start_date = '';
  end_date = '';
  range = new FormGroup({
    start: new FormControl(),
    end: new FormControl()
  });

  @Input()
  columns: ListColumn[] = [
    // { name: 'id', property: 'id', visible: false, isModelProperty: true },
    { name: 'Campaign ID', property: 'campaign_id', visible: true, isModelProperty: true },
    // { name: 'gateway_id', property: 'gateway_id', visible: true, isModelProperty: true },
    // { name: 'is_active', property: 'is_active', visible: true, isModelProperty: true},
    // { name: 'tax_provider_id', property: 'tax_provider_id', visible: false, isModelProperty: false },
    // { name: 'data_verification_provider_id', property: 'data_verification_provider_id', visible: false, isModelProperty: false },
    // { name: 'site_url', property: 'site_url', visible: false, isModelProperty: false },
    // { name: 'is_archived', property: 'is_archived', visible: false, isModelProperty: false },
    // { name: 'prepaid_blocked', property: 'prepaid_blocked', visible: false, isModelProperty: false },
    // { name: 'is_custom_price_allowed', property: 'is_custom_price_allowed', visible: false, isModelProperty: false },
    // { name: 'is_avs_enabled', property: 'is_avs_enabled', visible: false, isModelProperty: false },
    // { name: 'is_collections_enabled', property: 'is_collections_enabled', visible: false, isModelProperty: false },
    // { name: 'archived_at', property: 'archived_at', visible: false, isModelProperty: false },
    { name: 'Name', property: 'name', visible: true, isModelProperty: true },
    { name: 'Networks', property: 'tracking_networks', visible: true, isModelProperty: false },
    { name: 'Campaigns', property: 'tracking_campaigns', visible: true, isModelProperty: false },
    { name: 'Initials', property: 'initials', visible: true, isModelProperty: false },
    { name: 'Rebills', property: 'rebills', visible: true, isModelProperty: false },
    { name: 'C1', property: 'c1', visible: true, isModelProperty: false },
    { name: 'C1 $', property: 'c1_revenue', visible: true, isModelProperty: false },
    { name: 'C1 %', property: 'cycle_1_per', visible: true, isModelProperty: false },
    { name: 'C1 Decline %', property: 'c1_decline_per', visible: true, isModelProperty: false },
    { name: 'C2', property: 'c2', visible: true, isModelProperty: false },
    { name: 'C2 $', property: 'c2_revenue', visible: true, isModelProperty: false },
    { name: 'C2 %', property: 'cycle_2_per', visible: true, isModelProperty: false },
    { name: 'C2 Decline %', property: 'c2_decline_per', visible: true, isModelProperty: false },
    { name: 'C3', property: 'c3', visible: true, isModelProperty: false },
    { name: 'C3 $', property: 'c3_revenue', visible: true, isModelProperty: false },
    { name: 'C3 %', property: 'cycle_3_per', visible: true, isModelProperty: false },
    { name: 'C3 Decline %', property: 'c3_decline_per', visible: true, isModelProperty: false },
    { name: 'Avg Ticket', property: 'avg_ticket', visible: true, isModelProperty: false },
    { name: 'Revenue', property: 'revenue', visible: true, isModelProperty: false },
    { name: 'Refund', property: 'refund', visible: true, isModelProperty: false },
    { name: 'Refund Rate', property: 'refund_rate', visible: true, isModelProperty: false },
    { name: 'CBs', property: 'CBs', visible: true, isModelProperty: false },
    { name: 'CB %', property: 'CB_per', visible: true, isModelProperty: false },
    { name: 'CB $', property: 'CB_currency', visible: true, isModelProperty: false },
    // { name: 'Fulfillment', property: 'fulfillment', visible: true, isModelProperty: true },
    // { name: 'Processing', property: 'processing', visible: true, isModelProperty: true },
    // { name: 'CPA', property: 'cpa', visible: true, isModelProperty: true },
    // { name: 'CPA AVG', property: 'cpa_avg', visible: true, isModelProperty: true },
    // { name: 'Net', property: 'net', visible: true, isModelProperty: false },
    { name: 'CLV', property: 'clv', visible: true, isModelProperty: true },
    // { name: 'Upsell Poducts', property: 'upsell_products', visible: false, isModelProperty: false },
    // { name: 'Downsell Products', property: 'downsell_products', visible: false, isModelProperty: false },
    // { name: 'Cycle Products', property: 'cycle_products', visible: false, isModelProperty: false },
    // { name: 'Cogs Track', property: 'cogs_track', visible: false, isModelProperty: true },
    // { name: 'Cpa Track', property: 'cpa_track', visible: false, isModelProperty: true },
    // { name: 'Third Party Track', property: 'third_party_track', visible: false, isModelProperty: true },
    { name: 'Created At', property: 'created_at', visible: true, isModelProperty: true },
    // { name: 'updated_at', property: 'updated_at', visible: true, isModelProperty: true },
    { name: 'actions', property: 'actions', visible: true, isModelProperty: false },
  ] as ListColumn[];

  dataSource: MatTableDataSource<Campaign> | null;
  selection = new SelectionModel<Campaign>(true, []);

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;

  constructor(private dialog: MatDialog, private campaignsService: MyCampaignsService, private router: Router, private route: ActivatedRoute, ) { }

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
    this.selectDate('thisMonth');
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
    await this.campaignsService.getCampaigns(this.filters)
      .then(campaigns => {
        // this.allIdArray = [];
        this.campaigns = campaigns.data;
        this.dataSource.data = campaigns.data;
        setTimeout(() => {
          // this.paginator.pageIndex = this.currentPage;
          // this.paginator.length = campaigns.pag.count;
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
    if (!this.dataSource) {
      return;
    }
    value = value.trim();
    value = value.toLowerCase();
    this.dataSource.filter = value;
    // value = value.toLowerCase();
    // this.search = value;
    // clearTimeout(this.timer);
    // this.timer = setTimeout(() => { this.getData() }, 500)
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
    console.log('id :', id);
    const dialogData = new ConfirmationDialogModel('Confirm Delete', 'Are you sure you want to delete this campaign?');
    const dialogRef = this.dialog.open(ConfirmationDialogComponent, {
      maxWidth: '500px',
      closeOnNavigation: true,
      disableClose: true,
      data: dialogData
    })
    dialogRef.afterClosed().subscribe(dialogResult => {
      if (dialogResult) {
        this.campaignsService.deleteData(id).then((data) => {
          if (data.status) {
            this.notyf.success(data.message);
            this.getData();
            this.isDeleting = false;
          }
        });
        this.isDeleting = true;
        // this.dataSource.data = [];
        this.idArray = [];
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

  ngOnDestroy(): void {
    if (this.deleteSubscription) {
      // this.campaignsService.deleteResponse.next([]);
      this.deleteSubscription.unsubscribe();
    }
  }
  viewCampaignDetails(name) {
    // alert(name);
    this.router.navigate(['campaign-view', name]);
  }
}
