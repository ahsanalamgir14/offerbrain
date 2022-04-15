import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { TicketWeeklyComponent } from './ticket-weekly.component';

const routes: Routes = [
  {
    path: '',
    component: TicketWeeklyComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class TicketWeeklyRoutingModule {
}
