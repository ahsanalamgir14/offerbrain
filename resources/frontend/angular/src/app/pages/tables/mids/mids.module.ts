import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FlexLayoutModule } from '@angular/flex-layout';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatNativeDateModule } from '@angular/material/core';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatDialogModule } from '@angular/material/dialog';
import { MatExpansionModule } from '@angular/material/expansion';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatPaginatorModule } from '@angular/material/paginator';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatRadioModule } from '@angular/material/radio';
import { MatSelectModule } from '@angular/material/select';
import { MatTableModule } from '@angular/material/table';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { FurySharedModule } from '../../../../@fury/fury-shared.module';
import { BreadcrumbsModule } from '../../../../@fury/shared/breadcrumbs/breadcrumbs.module';
import { ListModule } from '../../../../@fury/shared/list/list.module';
import { MaterialModule } from '../../../../@fury/shared/material-components.module';
import { MidsRoutingModule } from './mids-routing.module';
import { NgxSkeletonLoaderModule } from 'ngx-skeleton-loader';
import { MidsComponent, TooltipListPipe } from './mids.component';
import { GroupDialogComponent } from './group-dialog/group-dialog.component';
import { RevenueDialogComponent } from './revenue-dialog/revenue-dialog.component';
// import { NgMultiSelectDropDownModule } from 'ng-multiselect-dropdown';

@NgModule({
  imports: [
    CommonModule,
    MidsRoutingModule,
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
    // NgMultiSelectDropDownModule.forRoot()
  ],
  declarations: [MidsComponent, GroupDialogComponent, TooltipListPipe, RevenueDialogComponent],
  exports: [MidsComponent],

})
export class MidsModule { }
