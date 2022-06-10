import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild, ChangeDetectorRef } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Observable, of, ReplaySubject } from 'rxjs';
import { ActivatedRoute } from '@angular/router';
import { filter } from 'rxjs/operators';
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
// import { ErrorStateMatcher } from '@angular/material';

@Component({
  selector: 'fury-campaign-builder',
  templateUrl: './campaign-builder.component.html',
  styleUrls: ['./campaign-builder.component.scss'],
  // providers: [{ provide: CdkStepper }],
})
export class CampaignBuilderComponent implements OnInit, OnDestroy {
  campaignFormGroup: FormGroup;
  upsellFormGroup: FormGroup;
  cyclesFormGroup: FormGroup;
  miscFormGroup: FormGroup;

  getProductsSubscription: Subscription;
  getOptionsSubscription: Subscription;
  saveSubscription: Subscription;

  /** snake case due to back-end variables */
  no_of_upsells: number = 1;
  no_of_downsells: number = 0;
  no_of_cycles: number = 0;
  upsell_products = [];
  downsell_products = [];
  cycle_products = [];
  array = [];
  arr_upsell = [];
  arr_downsell = []; 
  arr_cycle = [];

  campaignTypeOptions = ['Straight Sale'];
  trackingCampaignOptions = [];
  trackingNetworkOptions = [];
  noOfUpsellsOptions = ['0', '1', '2', '3', '4', '5'];
  noOfDownsellsOptions = ['0', '1', '2', '3', '4', '5'];
  noOfCyclesOptions = ['0', '1', '2', '3', '4', '5'];
  productOptions = [];

  notyf = new Notyf({ types: [{ type: 'info', background: '#6495ED', icon: '<i class="fa-solid fa-clock"></i>' }] });
  @ViewChild('stepper', { read: MatStepper }) stepper: MatStepper;

  constructor(private fb: FormBuilder,
    private cd: ChangeDetectorRef,
    private snackbar: MatSnackBar,
    public campaignBuilderService: CampaignBuilderService) {
  }

  ngOnInit() {

    this.getOptionsSubscription = this.campaignBuilderService.getOptionsResponse$.subscribe(data => this.manageOptionsResponse(data))
    this.saveSubscription = this.campaignBuilderService.saveResponse$.subscribe(data => this.manageSaveResponse(data))

    this.campaignFormGroup = this.fb.group({
      name: [null, Validators.required],
      campaign_type: ['Straight Sale', Validators.required],
      tracking_campaigns: [null, Validators.required],
      tracking_networks: [null, Validators.required],
    });

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
  }



  manageOptionsResponse(data) {
    if (data.status) {
      this.productOptions = data.data.products;
      this.trackingCampaignOptions = data.data.campaigns;
      this.trackingNetworkOptions = data.data.networks;
    }
  }

  manageSaveResponse(data) {
    console.log('inside');
    if (data.status) {
      this.notyf.success(data.message);
      this.stepper.reset();
      this.campaignFormGroup.reset();
      this.upsellFormGroup.reset();
      this.cyclesFormGroup.reset();
      this.miscFormGroup.reset();
      // this.campaignBuilderService.markAllAsUntouched();
      // Object.keys(this.campaignFormGroup.controls).forEach(key => {
      //   this.campaignFormGroup.get(key).setErrors(null);
      // });
    }
    else if (!data.status) {
      this.notyf.error(data.message);
    }
  }

  counter(N: number) {
    return Array.from({ length: N }, (v, i) => i);
  }

  clearSelection() {
    // this.noOfUpsells = null;
    // this.upsell_products.setValue('');
  }

  avoidDuplication(value, param, index) {
    let item = value.value;
    if(param == 'cycleproducts'){
      if(this.arr_cycle.indexOf(item) !== -1){
        this.notyf.error('Value already selected');
        this.cycle_products[index] = [];
      }
       else {
        this.arr_cycle.push(item);
      }
    }
    if(param == 'upsell'){
      if(this.arr_upsell.indexOf(item) !== -1){
        this.notyf.error('Value already existed in upsell');
        this.upsell_products[index] = [];
      } else if(this.arr_downsell.indexOf(item) !== -1){
        this.notyf.error('Value already exists in downcell');
        this.upsell_products[index] = [];
      } else {
        this.arr_upsell.push(item);
      }
    } else if(param == 'downsell'){
      if(this.arr_downsell.indexOf(item) !== -1){
        this.notyf.error('Value already exists in downcell');
        this.downsell_products[index] = [];
      } else if(this.arr_upsell.indexOf(item) !== -1){
        this.notyf.error('Value already existed in upsell');
        this.downsell_products[index] = [];
      } else {
        this.arr_downsell.push(item);
      }
    }
  }
  AddProductList1() {
    // console.log(this.upsell_products);
  }

  clear(form: NgForm): void {
    form.resetForm();
    Object.keys(form.controls).forEach(key =>{
       form.controls[key].setErrors(null)
    });

  }

  submit() {
    this.upsellFormGroup.get('upsell_products').setValue(this.upsell_products);
    this.upsellFormGroup.get('downsell_products').setValue(this.downsell_products);
    this.cyclesFormGroup.get('cycle_products').setValue(this.cycle_products);
    let saved =  this.campaignBuilderService.save(this.campaignFormGroup.value, this.upsellFormGroup.value, this.cyclesFormGroup.value, this.miscFormGroup.value)
    if(saved){
      this.upsell_products = [];
      this.downsell_products = [];
      this.cycle_products = [];
    } 
    // this.snackbar.open('You successfully created new campaign.', null, {
    //   duration: 5000
    // });
  }

  ngOnDestroy() {
    if (this.saveSubscription) {
      this.saveSubscription.unsubscribe();
      this.campaignBuilderService.saveResponse.next([]);
    }
  }
}