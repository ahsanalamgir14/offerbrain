import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { TicketMonthlyComponent } from './ticket-monthly.component';

const routes: Routes = [
  {
    path: '',
    component: TicketMonthlyComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class TicketMonthlyRoutingModule {
}
