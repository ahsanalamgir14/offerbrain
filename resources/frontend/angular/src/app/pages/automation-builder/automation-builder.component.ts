import { Component, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { ReplaySubject, Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { FormGroup, FormBuilder, FormControl, NgForm, Validators } from '@angular/forms';
import { AutomationBuilderService } from './automation-builder.service';
import { Subscription } from 'rxjs';
import { Notyf } from "notyf";
import { MatStepper } from '@angular/material/stepper';
import { IDropdownSettings } from 'ng-multiselect-dropdown';

@Component({
  selector: 'fury-automation-builder',
  templateUrl: './automation-builder.component.html',
  styleUrls: ['./automation-builder.component.scss'],
})
export class AutomationBuilderComponent implements OnInit, OnDestroy {

  networkSearchCtrl: FormControl = new FormControl();

  filteredNetworks: ReplaySubject<any[]> = new ReplaySubject<any[]>(1);
  _onDestroy: Subject<void> = new Subject<void>();

  automationFormGroup: FormGroup;
  triggerFormGroup: FormGroup;
  ActionFormGroup: FormGroup;

  getOptionsSubscription: Subscription;
  saveSubscription: Subscription;

  lastupSellSelected = [];
  automation_type = ['pre-fire', 'automation', 'full-automation'];
  automation_resource = ['everflow', 'konnektive', 'subscribe-funnels'];
  operator = ['Less Than', 'Greater Than', 'Around'];
  triggers = ['EPC', 'CVR'];
  lookbacks = ['30 Minutes', '45 Minutes', '1 Hour'];
  throttle_action = ['Release Throttle'];
  prefire_resource = ['PREPAID', 'INTERNAL', 'MAGIC'];
  timeframe = ['EVERYDAY'];
  trackingNetworkOptions = [];

  notyf = new Notyf({ types: [{ type: 'info', background: '#6495ED', icon: '<i class="fa-solid fa-clock"></i>' }] });
  @ViewChild('stepper', { read: MatStepper }) stepper: MatStepper;

  dropdownSettings = {};
  dropdownSettingsForSingle: IDropdownSettings;

  constructor(private fb: FormBuilder,
    public automationBuilderService: AutomationBuilderService) {
  }

  ngOnInit() {
    this.getOptionsSubscription = this.automationBuilderService.getOptionsResponse$.subscribe(data => this.manageOptionsResponse(data))
    this.saveSubscription = this.automationBuilderService.saveResponse$.subscribe(data => this.manageSaveResponse(data))

    this.automationFormGroup = this.fb.group({
      name: [null, Validators.required],
      automation_type: [null, Validators.required],
      automation_resource: [null, Validators.required],
      networks: [null, Validators.required],
      affiliate: [null, Validators.required],
      cpa: [null, Validators.required],
      cap: [null, Validators.required],
    });


    this.networkSearchCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.filterNetworkOptions();
      });

    this.triggerFormGroup = this.fb.group({
      trigger: [null, Validators.required],
      operator: [null, Validators.required],
      lookback: [null, Validators.required],
    });

    this.ActionFormGroup = this.fb.group({
      throttle_action: [null, Validators.required],
      prefire_target: [null],
      prefire_resource: [null],
      timeframe: [null, Validators.required],
      is_per_day: [null],
      time_from: [null],
      time_to: [null],
    });

    this.automationBuilderService.getOptionsData();
  }
  manageOptionsResponse(data) {
    if (data.status) {
      this.trackingNetworkOptions = data.data.networks;
      this.filteredNetworks.next(this.trackingNetworkOptions.slice());
    }
  }

  manageSaveResponse(data) {
    if (data.status) {
      this.notyf.success(data.message);
      this.stepper.reset();
      this.automationFormGroup.reset();
      this.triggerFormGroup.reset();
      this.ActionFormGroup.reset();
    }
    else if (!data.status) {
      if (data.message) {
        this.notyf.error(data.message);
      }
    }
  }

  submit() {
    let saved = this.automationBuilderService.save(this.automationFormGroup.value, this.triggerFormGroup.value, this.ActionFormGroup.value)
    if (saved) {
      
    }
  }
  clear(form: NgForm): void {
    form.resetForm();
    Object.keys(form.controls).forEach(key => {
      form.controls[key].setErrors(null)
    });

  }
  checkDropdownValue(param) {
    this.stepper.next();
  }

  ngOnDestroy() {
    if (this.saveSubscription) {
      this.saveSubscription.unsubscribe();
      this.automationBuilderService.saveResponse.next([]);
    }
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
}
