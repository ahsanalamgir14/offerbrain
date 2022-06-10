import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { MyCampaignsComponent } from './my-campaigns.component';

const routes: Routes = [
  {
    path: '',
    component: MyCampaignsComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class MyCampaignsRoutingModule {
}
