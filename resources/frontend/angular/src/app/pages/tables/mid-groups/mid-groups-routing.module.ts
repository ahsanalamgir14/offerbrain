import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { MidGroupsComponent } from './mid-groups.component';
// import { MidsComponent } from '../mids/mids.component';

const routes: Routes = [
  {
    path: '',
    component: MidGroupsComponent
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class MidGroupsRoutingModule {
}
