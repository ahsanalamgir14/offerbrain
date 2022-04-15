import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AffiliatesNetworkComponent } from './affiliates-network.component';


const routes: Routes = [
  {
    path: '',
    component: AffiliatesNetworkComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AffiliatesNetworkRoutingModule {
}
