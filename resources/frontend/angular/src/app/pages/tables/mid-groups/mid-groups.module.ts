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
import { FurySharedModule } from 'src/@fury/fury-shared.module';
import { BreadcrumbsModule } from 'src/@fury/shared/breadcrumbs/breadcrumbs.module';
import { ListModule } from 'src/@fury/shared/list/list.module';
import { MaterialModule } from 'src/@fury/shared/material-components.module';
import { MidGroupsRoutingModule } from './mid-groups-routing.module';
import { MidGroupsComponent } from './mid-groups.component';
import { MidsDetailComponent } from './mids-detail/mids-detail.component';
import { RouterModule } from '@angular/router';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MidsModule } from '../mids/mids.module';
import { ActionDialogComponent } from './action-dialog/action-dialog.component';
import { NgxSkeletonLoaderModule } from 'ngx-skeleton-loader';
import { TooltipListModule } from 'src/@fury/shared/@pipes/tooltip-list.module';


@NgModule({
  imports: [
    CommonModule,
    MidGroupsRoutingModule,
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
    RouterModule,
    MatTooltipModule,
    MidsModule,
    NgxSkeletonLoaderModule,
    MidsModule,
    TooltipListModule
  ],
  declarations: [MidGroupsComponent, MidsDetailComponent, ActionDialogComponent],
  exports: [MidGroupsComponent],

})
export class MidGroupsModule { }