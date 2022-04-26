import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { SubAffiliatesComponent } from './sub-affiliates.component';

const routes: Routes = [
  {
    path: '',
    component: SubAffiliatesComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class SubAffiliatesRoutingModule {
}
