import { AfterViewInit, Component, ElementRef, EventEmitter, Input, Output, ViewChild, ViewEncapsulation } from '@angular/core';
import { fromEvent } from 'rxjs';
import { debounceTime, distinctUntilChanged } from 'rxjs/operators';
import { ListColumn } from './list-column.model';
import { ListService } from './list.service';

@Component({
  selector: 'fury-list',
  templateUrl: './list.component.html',
  styleUrls: ['./list.component.scss'],
  encapsulation: ViewEncapsulation.None
})
export class ListComponent implements AfterViewInit {

  @Input() name: string;
  @Input() columns: ListColumn[];

  @ViewChild('filter') filter: ElementRef;
  @Output() filterChange = new EventEmitter<string>();
  @Output() refresh = new EventEmitter<string>();
  @Output() enableLoading = new EventEmitter<string>();

  @Input() hideHeader: boolean;

  constructor(private listService: ListService) {
  }

  ngAfterViewInit() {
    if (!this.hideHeader) {
      fromEvent(this.filter.nativeElement, 'keyup').pipe(
        // distinctUntilChanged(),
        // debounceTime(150)
      ).subscribe(() => {
        this.filterChange.emit(this.filter.nativeElement.value);
      });
    }
  }

  //to be deleted
  async changeFilterValue(value) {
    if (this.filter.nativeElement.value != '') {
      this.filter.nativeElement.value = value;
      await this.recordSearch();
    }
  }

  async clearSearch(){
    this.filter.nativeElement.value = '';
    await this.recordSearch();
  }
  //up to here

  async toggleColumnVisibility(column, event) {
    event.stopPropagation();
    event.stopImmediatePropagation();
    column.visible = !column.visible;
    await this.listService.changeColumn(this.name, column, event.checked).then(data => {
      // this.refresh.next('');
    });
  }

  async changeVisibility(column, event: any) {
    await this.listService.changeColumn(this.name, column, event.checked).then(data => {
      // this.refresh.next('');
    });
  }

  //delete function
  async recordSearch() {
    let keyword = this.filter.nativeElement.value;
    if (this.name == 'Mids') {
      this.enableLoading.emit(null);
      await this.listService.search(this.name.toLowerCase(), keyword).then(data => {
      });
    }
  }
}
