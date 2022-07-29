import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild, ChangeDetectorRef } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Observable, of, ReplaySubject, Subject } from 'rxjs';
import { ActivatedRoute } from '@angular/router';
import { filter, takeUntil, map } from 'rxjs/operators';
import { ListColumn } from '../../../@fury/shared/list/list-column.model';
import { fadeInRightAnimation } from '../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../@fury/animations/fade-in-up.animation';
import { FormGroup, FormBuilder, Validators, FormControl, NgForm, FormGroupDirective } from '@angular/forms';
import { CampaignBuilderService } from './campaign-builder.service';
import { Subscription } from 'rxjs';
import { formatDate } from '@angular/common';
import { environment } from '../../../environments/environment';
import { ApiService } from 'src/app/api.service';
import { MatSnackBar } from '@angular/material/snack-bar';
import { scaleInAnimation } from '../../../@fury/animations/scale-in.animation';
import { Notyf } from "notyf";
import { MatStepper } from '@angular/material/stepper';
import { IDropdownSettings } from 'ng-multiselect-dropdown';
import { Router } from '@angular/router';


@Component({
  selector: 'fury-campaign-builder',
  templateUrl: './campaign-builder.component.html',
  styleUrls: ['./campaign-builder.component.scss'],
  // providers: [{ provide: CdkStepper }],
})
export class CampaignBuilderComponent implements OnInit, OnDestroy {

  campaignSearchCtrl: FormControl = new FormControl();
  networkSearchCtrl: FormControl = new FormControl();
  productSearchCtrl: FormControl = new FormControl();

  filteredCampaigns: ReplaySubject<any[]> = new ReplaySubject<any[]>(1);
  filteredNetworks: ReplaySubject<any[]> = new ReplaySubject<any[]>(1);
  filteredProducts: ReplaySubject<any[]> = new ReplaySubject<any[]>(1);
  _onDestroy: Subject<void> = new Subject<void>();

  campaignFormGroup: FormGroup;
  upsellFormGroup: FormGroup;
  cyclesFormGroup: FormGroup;
  miscFormGroup: FormGroup;

  getProductsSubscription: Subscription;
  getOptionsSubscription: Subscription;
  saveSubscription: Subscription;
  updateSubscription: Subscription;

  /** snake case due to back-end variables */
  no_of_upsells: number = 0;
  no_of_downsells: number = 0;
  no_of_cycles: number = 0;
  upsell_products = [];
  downsell_products = [];
  cycle_products = [];
  array = [];
  arr_upsell = [];
  arr_downsell = [];
  arr_cycleProducts = [];
  lastupSellSelected = [];
  // arr_cycle = [];
  upProducts = [];
  downProducts = [];
  cycleProducts = [];

  campaignTypeOptions = ['Straight Sale'];
  trackingCampaignOptions = [];
  trackingNetworkOptions = [];
  noOfUpsellsOptions = [0, 1, 2, 3, 4, 5];
  noOfDownsellsOptions = [0, 1, 2, 3, 4, 5];
  noOfCyclesOptions = [0, 1, 2, 3];
  productOptions = [];
  notyf = new Notyf({ types: [{ type: 'info', background: '#6495ED', icon: '<i class="fa-solid fa-clock"></i>' }] });
  @ViewChild('stepper', { read: MatStepper }) stepper: MatStepper;

  dropdownSettings = {};
  dropdownSettingsForSingle: IDropdownSettings;
  isRefreshingCampaign = false;
  isRefreshingNetwork = false;
  isRefreshingProduct = false;

  selectedCampaigns: [];
  paramId: null;

  constructor(private fb: FormBuilder,
    private cd: ChangeDetectorRef,
    private snackbar: MatSnackBar,
    public campaignBuilderService: CampaignBuilderService,
    public route: ActivatedRoute,
    public router: Router) {
  }

  ngOnInit() {
    this.getOptionsSubscription = this.campaignBuilderService.getOptionsResponse$.subscribe(data => this.manageOptionsResponse(data))
    this.saveSubscription = this.campaignBuilderService.saveResponse$.subscribe(data => this.manageSaveResponse(data))
    this.updateSubscription = this.campaignBuilderService.updateResponse$.subscribe(data => this.manageUpdateResponse(data))

    this.campaignFormGroup = this.fb.group({
      name: [null, Validators.required],
      campaign_type: ['Straight Sale', Validators.required],
      tracking_campaigns: [null, Validators.required],
      tracking_networks: [null, Validators.required],
    });

    this.dropdownSettingsForSingle = {
      singleSelection: true,
      idField: 'product_id',
      textField: 'full_name',
      allowSearchFilter: true
    };

    this.upsellFormGroup = this.fb.group({
      no_of_upsells: [null],
      no_of_downsells: [null],
      upsell_products: [null],
      downsell_products: [null],
    });

    this.cyclesFormGroup = this.fb.group({
      no_of_cycles: [null],
      cycle_products: [null],
    });

    this.miscFormGroup = this.fb.group({
      cogs_track: [null],
      cpa_track: [null],
      third_party_track: [null],
    });

    this.campaignBuilderService.getOptionsData();

    this.route.params.subscribe((params: any) => {
      this.paramId = params.id;
      if (this.paramId) {
        this.getCampaignData();
      }
    });

    this.campaignSearchCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.filterCampaignOptions();
      });
    this.networkSearchCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.filterNetworkOptions();
      });
    this.productSearchCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.filterProductOptions();
      });
  }

  manageOptionsResponse(data) {
    if (data.status) {
      this.productOptions = data.data.products;
      this.trackingCampaignOptions = data.data.campaigns;
      this.trackingNetworkOptions = data.data.networks;
      this.filteredCampaigns.next(this.trackingCampaignOptions.slice());
      this.filteredNetworks.next(this.trackingNetworkOptions.slice());
      this.filteredProducts.next(this.productOptions.slice());
    }
  }

  manageSaveResponse(data) {
    if (data.status) {
      this.notyf.success(data.message);
      this.stepper.reset();
      this.campaignFormGroup.reset();
      this.upsellFormGroup.reset();
      this.cyclesFormGroup.reset();
      this.miscFormGroup.reset();
    }
    else if (!data.status) {
      if (data.message) {
        this.notyf.error(data.message);
      }
    }
  }

  async manageUpdateResponse(data) {
    if (data.status) {
      this.notyf.success(data.message);
      await this.getCampaignData();
      this.stepper.selectedIndex = 0;
      // this.router.navigate(['edit-campaign', this.paramId]);
    }
    else if (!data.status) {
      if (data.message) {
        this.notyf.error(data.message);
      }
    }
  }

  async getCampaignData() {
    await this.campaignBuilderService.getCampaignData(this.paramId).then((data) => {
      if (data.status) {
        this.campaignFormGroup.patchValue({
          name: data.data.name,
          campaign_type: data.data.campaign_type,
          tracking_campaigns: data.data.tracking_campaigns,
          tracking_networks: data.data.tracking_networks
        });
        this.no_of_upsells = data.data.no_of_upsells;
        this.no_of_downsells = data.data.no_of_downsells;
        this.upsellFormGroup.patchValue({
          no_of_upsells: data.data.no_of_upsells,
          no_of_downsells: data.data.no_of_downsells,
        });
        for (var i = 0; i < this.no_of_upsells; i++) {
          this.arr_upsell[i] = data.data.upsell_products[i].full_name;
        }
        for (var i = 0; i < this.no_of_upsells; i++) {
          this.arr_downsell[i] = data.data.downsell_products[i].full_name;
        }
        this.upProducts = data.data.upsell_products;
        this.downProducts = data.data.downsell_products;
        for (var i = 0; i < this.no_of_upsells; i++) {
          this.upsell_products[i] = [data.data.upsell_products[i]];
        }
        for (var i = 0; i < this.no_of_upsells; i++) {
          this.downsell_products[i] = [data.data.downsell_products[i]];
        }
        this.no_of_cycles = data.data.no_of_cycles;
        this.cycleProducts = data.data.cycle_products;
        for (var i = 0; i < this.no_of_cycles + 1; i++) {
          this.cycle_products[i] = [data.data.cycle_products[i]];
        }
        for (var i = 0; i < this.no_of_cycles + 1; i++) {
          this.arr_cycleProducts[i] = data.data.cycle_products[i].full_name;
        }
        this.cyclesFormGroup.patchValue({
          no_of_cycles: data.data.no_of_cycles,
        });

        this.miscFormGroup.patchValue({
          cogs_track: data.data.cogs_track,
          cpa_track: data.data.cpa_track,
          third_party_track: data.data.third_party_track,
        });
      }
    });
  }

  counter(N: number) {
    return Array.from({ length: N }, (v, i) => i);
  }

  countercycle(N: number) {
    N = ++N;
    return Array.from({ length: N }, (v, i) => i);
  }
  clearSelection(param) {
    if (param == 'upsells') {
      this.arr_upsell = this.arr_upsell.slice(0, this.no_of_upsells);
    }
    if (param == 'downsells') {
      this.arr_downsell = this.arr_downsell.slice(0, this.no_of_downsells);
    }
    if (param == 'cycles') {
      let cyclelength = this.no_of_cycles;
      cyclelength = ++cyclelength;
      this.arr_cycleProducts = this.arr_cycleProducts.slice(0, cyclelength);
    }
  }

  checkDropdownValue(param, param1) {
    if (param) {
      let uppArr = [];
      let downArr = [];
      let cycleArr = [];
      this.upsell_products.forEach(key => {
        if (key != undefined) {
          uppArr.push(key[0]);
        }
      });
      this.downsell_products.forEach(key => {
        if (key != undefined) {
          downArr.push(key[0]);
        }
      });
      this.cycle_products.forEach(key => {
        if (key != undefined) {
          cycleArr.push(key[0]);
        }
      });
      let cyclelength = this.no_of_cycles;
      cyclelength = ++cyclelength;
      this.upProducts = uppArr.slice(0, this.no_of_upsells);
      this.downProducts = downArr.slice(0, this.no_of_downsells);
      this.cycleProducts = cycleArr.slice(0, cyclelength);
    }
    let cyclelength = this.no_of_cycles;
    cyclelength = ++cyclelength;
    if (this.arr_upsell.length < this.no_of_upsells && param1 == 'isupsell' || this.arr_upsell.includes('[]')) {
      this.notyf.error('Value missing in dropdown, please select all values in upsells');
    }
    else if (this.arr_downsell.length < this.no_of_downsells && param1 == 'isupsell' || this.arr_downsell.includes('[]')) {
      this.notyf.error('Value missing in dropdown, please select all values in downsells');
    }
    else if (this.arr_cycleProducts.length < cyclelength && param1 == 'iscycle' || this.arr_cycleProducts.includes('[]')) {
      this.notyf.error('Value missing in dropdown, please select all values cycles');
    }
    else {
      this.stepper.next();
    }
  }
  avoidDuplication(event, param, index) {
    let item = event.full_name;
    if (param == 'upsellDeSelect') {
      this.arr_upsell[index] = '[]';
      this.upsell_products[index] = [];
    }
    if (param == 'downsellDeSelect') {
      this.arr_downsell[index] = '[]';
      // var ind = this.arr_downsell.indexOf(item);
      // this.arr_downsell.splice(ind, 1);
      console.log('this.arr_downsell :', this.arr_downsell);
      this.downsell_products[index] = [];
    }
    if (param == 'cycleproductsDeSelect') {
      // var ind = this.arr_cycleProducts.indexOf(item);
      // this.arr_cycleProducts.splice(ind, 1);
      this.arr_cycleProducts[index] = '[]';
      this.cycle_products[index] = [];
      console.log('this.arr_cycleProducts', this.arr_cycleProducts);
    }
    if (param == 'upsell') {
      console.log('Upsell Products are ', this.upsell_products);
      if (this.arr_upsell[index] != undefined) {
        // var selectedIndex = this.arr_upsell.indexOf(this.arr_upsell[index]);
        // this.arr_upsell.splice(selectedIndex, 1);
        this.arr_upsell[index] = '[]';
        console.log('this.arr_upsell :', this.arr_upsell);
      }
      if (this.arr_upsell.indexOf(item) !== -1) {
        this.notyf.error('Value already exist in upsell');
        this.upsell_products[index] = [];
        this.arr_upsell[index] = '[]';
      } else if (this.arr_downsell.indexOf(item) !== -1) {
        this.notyf.error('Value already exist in downsell');
        this.upsell_products[index] = [];
        this.arr_upsell[index] = '[]';
      } else {
        this.arr_upsell.splice(index, 1, item);
        // this.arr_upsell = this.arr_upsell.slice(0, this.no_of_upsells);
      }
    } else if (param == 'downsell') {
      if (this.arr_downsell[index] != undefined) {
        this.arr_downsell[index] = '[]';
        // var selectedIndex = this.arr_downsell.indexOf(this.arr_downsell[index]);
        // this.arr_downsell.splice(selectedIndex, 1);
      }
      if (this.arr_downsell.indexOf(item) !== -1) {
        this.notyf.error('Value already exist in downsell');
        this.arr_downsell[index] = '[]';
        this.downsell_products[index] = [];
      } else if (this.arr_upsell.indexOf(item) !== -1) {
        this.notyf.error('Value already exist in upsell');
        this.arr_downsell[index] = '[]';
        this.downsell_products[index] = [];
      } else {
        this.arr_downsell.splice(index, 1, item);
        // this.arr_downsell = this.arr_downsell.slice(0, this.no_of_downsells);
      }
    } else if (param == 'cycleproducts') {
      console.log('this.arr_cycleProducts', this.arr_cycleProducts);
      if (this.arr_cycleProducts[index] != undefined) {
        this.arr_cycleProducts[index] = '[]';
        // var selectedIndex = this.arr_cycleProducts.indexOf(this.arr_cycleProducts[index]);
        // this.arr_cycleProducts.splice(selectedIndex, 1);
      }
      if (this.arr_cycleProducts.indexOf(item) !== -1) {
        this.notyf.error('Value already exist in cycle products');
        this.arr_cycleProducts[index] = '[]';
        this.cycle_products[index] = [];
      } else if (this.arr_upsell.indexOf(item) !== -1) {
        this.notyf.error('Value already exist in upsell products');
        this.cycle_products[index] = [];
        this.arr_cycleProducts[index] = '[]';
      } else if (this.arr_downsell.indexOf(item) !== -1) {
        this.notyf.error('Value already exist in Downsell products');
        this.cycle_products[index] = [];
        this.arr_cycleProducts[index] = '[]';
      } else {
        this.arr_cycleProducts.splice(index, 1, item);
        // this.arr_cycleProducts = this.arr_cycleProducts.slice(0, this.no_of_cycles);
      }
    }
  }
  getSelectValue(event, param, index) {
    if (param == 'upsell') {
      if (this.upsell_products[index] != undefined && this.upsell_products[index] != []) {
        this.arr_upsell[index] = this.upsell_products[index][0].full_name;
      }
    } else if (param == 'downsell') {
      if (this.arr_downsell[index] != undefined) {
        this.arr_downsell[index] = this.downsell_products[index][0].full_name;
      }
    } else if (param == 'cycleproducts') {
      if (this.arr_cycleProducts[index] != undefined) {
        this.arr_cycleProducts[index] = this.cycle_products[index][0].full_name;
      }
    }
  }

  clear(form: NgForm): void {
    form.resetForm();
    Object.keys(form.controls).forEach(key => {
      form.controls[key].setErrors(null)
    });
  }

  submit() {
    this.upsellFormGroup.get('upsell_products').setValue(this.upProducts);
    this.upsellFormGroup.get('downsell_products').setValue(this.downProducts);
    this.cyclesFormGroup.get('cycle_products').setValue(this.cycleProducts);
    if (this.paramId) {
      var saved = this.campaignBuilderService.update(this.campaignFormGroup.value, this.upsellFormGroup.value, this.cyclesFormGroup.value, this.miscFormGroup.value, this.paramId)
    }
    else {
      var saved = this.campaignBuilderService.save(this.campaignFormGroup.value, this.upsellFormGroup.value, this.cyclesFormGroup.value, this.miscFormGroup.value)
    }
    if (saved) {
      this.upsell_products = [];
      this.downsell_products = [];
      this.cycle_products = [];
      this.upProducts = [];
      this.downProducts = [];
      this.cycleProducts = [];
    }
  }

  protected filterCampaignOptions() {
    if (!this.trackingCampaignOptions) {
      return;
    }
    let search = this.campaignSearchCtrl.value;
    if (!search) {
      this.filteredCampaigns.next(this.trackingCampaignOptions.slice());
      return;
    } else {
      search = search.toLowerCase();
    }
    this.filteredCampaigns.next(
      this.trackingCampaignOptions.filter(bank => bank.name.toLowerCase().indexOf(search) > -1)
    );
  }

  protected filterNetworkOptions() {
    if (!this.trackingNetworkOptions) {
      return;
    }
    let search = this.networkSearchCtrl.value;
    if (!search) {
      this.filteredNetworks.next(this.trackingNetworkOptions.slice());
      return;
    } else {
      search = search.toLowerCase();
    }
    this.filteredNetworks.next(
      this.trackingNetworkOptions.filter(bank => bank.name.toLowerCase().indexOf(search) > -1)
    );
  }

  protected filterProductOptions() {
    if (!this.productOptions) {
      return;
    }

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

  async refreshCampaigns() {
    this.notyf.open({ type: 'info', message: 'OfferBrain is pulling new campaigns for you!' });
    this.isRefreshingCampaign = true;
    await this.campaignBuilderService.refreshCampaignsOptions().then((data) => {
      if (data.status) {
        this.trackingCampaignOptions = data.data.campaigns;
        this.filteredCampaigns.next(this.trackingCampaignOptions.slice());
        this.notyf.success('Campaign Options refreshed successfully')
        this.isRefreshingCampaign = false;
      }
    });
  }
  async refreshNetworks() {
    this.notyf.open({ type: 'info', message: 'OfferBrain is pulling new networks for you!' });
    this.isRefreshingNetwork = true;
    await this.campaignBuilderService.refreshNetworksOptions().then((data) => {
      if (data.status) {
        this.trackingNetworkOptions = data.data.networks;
        this.filteredNetworks.next(this.trackingNetworkOptions.slice());
        this.notyf.success('Network Options refreshed successfully');
        this.isRefreshingNetwork = false;

      }
    });
  }

  async refreshProducts() {
    this.notyf.open({ type: 'info', message: 'OfferBrain is pulling new products for you!' });
    this.isRefreshingProduct = true;
    await this.campaignBuilderService.refreshProductOptions().then((data) => {
      if (data.status) {
        this.productOptions = data.data.products;
        this.filteredProducts.next(this.productOptions.slice());
        this.notyf.success('Product Options refreshed successfully')
        this.isRefreshingProduct = false;
      }
    });
  }
  ngOnDestroy() {
    if (this.getOptionsSubscription) {
      this.getOptionsSubscription.unsubscribe();
      this.campaignBuilderService.getOptionsResponse.next([]);
    }
    if (this.saveSubscription) {
      this.saveSubscription.unsubscribe();
      this.campaignBuilderService.saveResponse.next([]);
    }
    if (this.updateSubscription) {
      this.updateSubscription.unsubscribe();
      this.campaignBuilderService.updateResponse.next([]);
    }
  }
}
