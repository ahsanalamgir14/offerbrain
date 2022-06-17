import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { BreadcrumbsModule } from '../../../@fury/shared/breadcrumbs/breadcrumbs.module';
import { ListModule } from '../../../@fury/shared/list/list.module';
import { MaterialModule } from '../../../@fury/shared/material-components.module';
import { CampaignBuilderRoutingModule } from './campaign-builder-routing.module';
import { CampaignBuilderComponent } from './campaign-builder.component';
import { FurySharedModule } from '../../../@fury/fury-shared.module';
import { FlexLayoutModule } from '@angular/flex-layout';
import { MatButtonModule } from '@angular/material/button';
import { MatDialogModule } from '@angular/material/dialog';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatRadioModule } from '@angular/material/radio';
import { MatSelectModule } from '@angular/material/select';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { MatExpansionModule } from '@angular/material/expansion'
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule } from '@angular/material/paginator';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { NgxSkeletonLoaderModule } from 'ngx-skeleton-loader';
import { MatStepperModule } from '@angular/material/stepper';
import { MatSelectSearchVersion } from 'ngx-mat-select-search';
import { NgxMatSelectSearchModule } from 'ngx-mat-select-search';
import { NgMultiSelectDropDownModule } from 'ng-multiselect-dropdown';

@NgModule({
  imports: [
    NgMultiSelectDropDownModule.forRoot(),
    CommonModule,
    CampaignBuilderRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    MaterialModule,
    FurySharedModule,
    FlexLayoutModule,
    MatDialogModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatRadioModule,
    MatSelectModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MatExpansionModule,
    NgbModule,
    ListModule,
    BreadcrumbsModule,
    MatTableModule,
    MatPaginatorModule,
    MatProgressBarModule,
    NgxSkeletonLoaderModule,
    MatStepperModule,
    NgxMatSelectSearchModule 
  ],
  declarations: [CampaignBuilderComponent],
  exports: [CampaignBuilderComponent],

})
export class CampaignBuilderModule {
  matSelectSearchVersion = MatSelectSearchVersion;
 }
