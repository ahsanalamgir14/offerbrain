import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TooltipListPipe } from './tooltip-list.pipe';


@NgModule({
  imports: [
    CommonModule,
  ],
  declarations: [TooltipListPipe],
  exports: [TooltipListPipe],

})
export class TooltipListModule { }