import { HttpClientModule } from '@angular/common/http';
import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations'; // Needed for Touch functionality of Material Components
import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { LayoutModule } from './layout/layout.module';
import { PendingInterceptorModule } from '../@fury/shared/loading-indicator/pending-interceptor.module';
import { MAT_FORM_FIELD_DEFAULT_OPTIONS, MatFormFieldDefaultOptions } from '@angular/material/form-field';
import { MAT_SNACK_BAR_DEFAULT_OPTIONS, MatSnackBarConfig } from '@angular/material/snack-bar';
import { OrdersService } from './pages/tables/orders/orders.service';
import { CustomersService } from './pages/customers/customers.service';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { MidGroupsRoutingModule } from './pages/tables/mid-groups/mid-groups-routing.module';
import { MidDetailDialogComponent } from './pages/mid-detail-dialog/mid-detail-dialog.component';
import { Spinner2Component } from './pages/spinner/spinner2/spinner2.component';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';


@NgModule({
  imports: [
    BrowserModule,
    BrowserAnimationsModule,
    HttpClientModule,
    AppRoutingModule,
    MidGroupsRoutingModule,
    LayoutModule,
    PendingInterceptorModule,
    NgbModule,
    MatProgressSpinnerModule,
  ],
  declarations: [AppComponent, MidDetailDialogComponent, Spinner2Component],
  bootstrap: [AppComponent],
  providers: [
    {
      provide: MAT_FORM_FIELD_DEFAULT_OPTIONS,
      useValue: {
        appearance: 'fill'
      } as MatFormFieldDefaultOptions,
    },
    {
      provide: MAT_SNACK_BAR_DEFAULT_OPTIONS,
      useValue: {
        duration: 5000,
        horizontalPosition: 'end',
        verticalPosition: 'bottom'
      } as MatSnackBarConfig
    },
    OrdersService,
    CustomersService
  ]
})
export class AppModule {
}
