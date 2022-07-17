import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AutomationBuilderComponent } from './automation-builder.component'

const routes: Routes = [
  {
    path: '',
    component: AutomationBuilderComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AutomationBuilderRoutingModule {
}
