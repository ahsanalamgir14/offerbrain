import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Observable, of, ReplaySubject, observable } from 'rxjs';
import { filter } from 'rxjs/operators';
import { ListColumn } from 'src/@fury/shared/list/list-column.model';
import { fadeInRightAnimation } from 'src/@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from 'src/@fury/animations/fade-in-up.animation';
import { FormGroup, FormControl } from '@angular/forms';
import { Network } from './affiliates-network.model';
import { AffiliatesNetworkService } from './affiliates-network.service';
import { Subscription } from 'rxjs';
import { SelectionModel } from '@angular/cdk/collections';
import { formatDate } from '@angular/common';
import { environment } from 'src/environments/environment';
import { ApiService } from 'src/app/api.service';
import { Pipe, PipeTransform } from '@angular/core';
import { ConfirmationDialogComponent } from '../../confirmation-dialog/confirmation-dialog.component';
import { ConfirmationDialogModel } from '../../confirmation-dialog/confirmation-dialog';
import { Notyf } from 'notyf';

@Component({
  selector: 'fury-affiliates-network',
  templateUrl: './affiliates-network.component.html',
  styleUrls: ['./affiliates-network.component.scss'],
  animations: [fadeInRightAnimation, fadeInUpAnimation]

})
export class AffiliatesNetworkComponent implements OnInit {
  subject$: ReplaySubject<Network[]> = new ReplaySubject<Network[]>(1);
  data$: Observable<Network[]> = this.subject$.asObservable();

  affiliates: Network[];
  AffOptionsSubscription: Subscription;
  getSubscription: Subscription;
  deleteSubscription: Subscription;
  isLoading = false;
  totalRows = 0;
  pageSizeOptions: number[] = [5, 10, 25, 100];
  filters = {};
  address = [];
  networks = [];
  all_fields = [];
  all_values = [];
  search = '';
  notyf = new Notyf();
  name: string;
  id: number;
  idArray = [];
  allIdArray = [];
  timer: any;
  isChecked = false;
  start_date = '';
  end_date = '';
  columns: any = [];
  range = new FormGroup({
    start: new FormControl(),
    end: new FormControl()
  });
  network_affiliate_id: '';
  affiliateOptions = [];



  @Input()
  dataSource: MatTableDataSource<Network>;
  selection = new SelectionModel<Network>(true, []);

  @ViewChild(MatPaginator, { static: true }) paginator: MatPaginator;
  @ViewChild(MatSort, { static: true }) sort: MatSort;

  constructor(private dialog: MatDialog, private affiliatesService: AffiliatesNetworkService) { }

  get visibleColumns() {
    return this.columns.filter(column => column.visible).map(column => column.property);
  }

  mapData() {
    return of(this.affiliates.map(midGroup => new Network(midGroup)));
  }

  ngOnInit(): void {
    this.AffOptionsSubscription = this.affiliatesService.affOptionsResponse$.subscribe(data => this.manageAffOptionsResponse(data))
    this.affiliatesService.getAffiliateOptions();
    this.selectDate('thisMonth');
    this.getData();
    this.dataSource = new MatTableDataSource();
    this.data$.pipe(
      filter(data => !!data)
    ).subscribe((affiliates) => {
      this.affiliates = affiliates;
      this.dataSource.data = affiliates;
    });
  }

  ngAfterViewInit(): void {
    this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }

  pageChanged(event: PageEvent) {
    this.getData();
  }

  async getData() {
    this.isLoading = true;
    this.isChecked = false;
    if (this.range.get('start').value != null) {
      this.start_date = formatDate(this.range.get('start').value, 'yyyy/MM/dd', 'en')
    }
    if (this.range.get('end').value != null) {
      this.end_date = formatDate(this.range.get('end').value, 'yyyy/MM/dd', 'en')
    }
    this.filters = {
      "search": this.search,
      "start": this.start_date,
      "end": this.end_date,
      'all_fields': this.all_fields,
      'all_values': this.all_values,
    }
    await this.affiliatesService.getColumns().then(columns => {
      this.columns = columns.data;
    });
    await this.affiliatesService.getAffiliates(this.filters)
      .then(affiliates => {
        this.allIdArray = [];
        this.affiliates = affiliates.data.affiliates;
        this.dataSource.data = affiliates.data.affiliates;
        this.networks = affiliates.data.networks;
        console.log('Affiliates are ', this.affiliates)
        this.mapData().subscribe(affiliates => {
          this.subject$.next(affiliates);
        });
        this.isLoading = false;
      }, error => {
        this.isLoading = false;
      });
  }
  manageAffOptionsResponse(data) {
    if (data.status) {
      this.affiliateOptions = data.data.affiliates;
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
  commonFilter(value, field) {
    if (this.all_fields.indexOf(field) === -1) {
      this.all_fields.push(field);
      this.all_values.push(value);
    } else {
      let index = this.all_fields.indexOf(field);
      this.all_values[index] = value;
    }
    // this.getData();
  }


  viewDetails(id) {
    console.log(id);
  }

  handleDeleteAction(id) {
    const dialogData = new ConfirmationDialogModel('Confirm Delete', 'Are you sure you want to delete this redord?');
    const dialogRef = this.dialog.open(ConfirmationDialogComponent, {
      maxWidth: '500px',
      closeOnNavigation: true,
      disableClose: true,
      data: dialogData
    })
    dialogRef.afterClosed().subscribe(dialogResult => {
      if (dialogResult) {
        this.affiliatesService.deleteData(id);
        this.getData();
        this.notyf.success('Network deleted successfully!');
      }
    });
  }

  ngOnDestroy(): void {
    if (this.deleteSubscription) {
      // this.affiliateservice.deleteResponse.next([]);
      // this.deleteSubscription.unsubscribe();
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
}
