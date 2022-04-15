import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { TicketDailyComponent } from './ticket-daily.component';

const routes: Routes = [
  {
    path: '',
    component: TicketDailyComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class TicketDailyRoutingModule {
}
