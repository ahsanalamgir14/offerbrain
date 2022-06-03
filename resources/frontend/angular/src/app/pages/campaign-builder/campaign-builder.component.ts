import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild, ChangeDetectorRef } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatTableDataSource } from '@angular/material/table';
import { Observable, of, ReplaySubject } from 'rxjs';
import { ActivatedRoute } from '@angular/router';
import { filter } from 'rxjs/operators';
import { ListColumn } from '../../../@fury/shared/list/list-column.model';
// import { Order } from './order.model';
import { fadeInRightAnimation } from '../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../@fury/animations/fade-in-up.animation';
import { FormGroup, FormControl, FormBuilder, Validators } from '@angular/forms';
import { CampaignBuilderService } from './campaign-builder.service';
import { Subscription } from 'rxjs';
import { formatDate } from '@angular/common';
import { environment } from '../../../environments/environment';
import { ApiService } from 'src/app/api.service';
import { MatSnackBar } from '@angular/material/snack-bar';
import { scaleInAnimation } from '../../../@fury/animations/scale-in.animation';


@Component({
  selector: 'fury-campaign-builder',
  templateUrl: './campaign-builder.component.html',
  styleUrls: ['./campaign-builder.component.scss']
})
export class CampaignBuilderComponent implements OnInit {
  campaignFormGroup: FormGroup;
  upsellFormGroup: FormGroup;
  cyclesFormGroup: FormGroup;
  miscFormGroup: FormGroup;

  noOfUpsells: number = 1;
  noOfDownsells: number = 0;
  noOfCycles: number = 0;
  upsellProductsList1 = [];
  downsellsProductsList1 = [];
  cycleProductsList = [];

  campaignTypeOptions = ['Straight Sale'];
  trackingCampaignOptions = ['type1', 'type2'];
  trackingNetworkOptions = ['network1', 'network2'];
  noOfUpsellsOptions = ['0', '1', '2', '3', '4', '5'];
  noOfDownsellsOptions = ['0', '1', '2', '3', '4', '5'];
  noOfCyclesOptions = ['0', '1', '2', '3', '4', '5'];
  productOptions = ['product1', 'product2', 'product3'];

  passwordInputType = 'password';

  constructor(private fb: FormBuilder,
    private cd: ChangeDetectorRef,
    private snackbar: MatSnackBar) {
  }

  ngOnInit() {
    /**
     * Horizontal Stepper
     * @type {FormGroup}
     */
    this.campaignFormGroup = this.fb.group({
      campaignName: [null, Validators.required],
      campaignType: ['Straight Sale', Validators.required],
      trackingCampaigns: [null, Validators.required],
      trackingNetworks: [null, Validators.required],
    });

    this.upsellFormGroup = this.fb.group({
      noOfUpsells: [null, Validators.required],
      noOfDownsells: [null, Validators.required],
      // upsellProductsList1: [null, Validators.required],
      // upsellProductsList2: [null, Validators.required],
    });

    this.cyclesFormGroup = this.fb.group({
      noOfCycles: [null, Validators.required],

    });

    this.miscFormGroup = this.fb.group({
      // cogsTrack: [null, Validators.required],
      // cpaTrack: [null, Validators.required],
      // thirdPartyTrack: [null, Validators.required],
    });
  }

  counter(N: number) {
    return Array.from({ length: N }, (v, i) => i);
  }

  showPassword() {
    this.passwordInputType = 'text';
    this.cd.markForCheck();
  }

  hidePassword() {
    this.passwordInputType = 'password';
    this.cd.markForCheck();
  }

  clearSelection() {
    // this.noOfUpsells = null;
    this.upsellProductsList1 = [];
  }

  AddProductList1() {
    console.log(this.upsellProductsList1);
  }

  submit() {
    this.snackbar.open('You successfully created new campaign.', null, {
      duration: 5000
    });
  }
}
