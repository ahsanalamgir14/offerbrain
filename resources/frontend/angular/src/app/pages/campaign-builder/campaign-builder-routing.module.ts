import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { CampaignBuilderComponent } from './campaign-builder.component'

const routes: Routes = [
  {
    path: '',
    component: CampaignBuilderComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class CampaignBuilderRoutingModule {
}
