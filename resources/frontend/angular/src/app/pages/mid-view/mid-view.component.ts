import { AfterViewInit, Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';

//self imports
import { FormGroup, FormControl } from '@angular/forms';
import { MidViewService } from './mid-view.service';
import { Subscription } from 'rxjs';
import { formatDate } from '@angular/common';
import { environment } from 'src/environments/environment';
import { ApiService } from 'src/app/api.service';
import { Router, ActivatedRoute, ParamMap, Params } from '@angular/router';

@Component({
  selector: 'fury-mid-view',
  templateUrl: './mid-view.component.html',
  styleUrls: ['./mid-view.component.scss']
})
export class MidViewComponent implements OnInit {

  mid: any;
  alias: string;
  getSubscription: Subscription;

  constructor(private dialog: MatDialog, private midViewService: MidViewService, private apiService: ApiService, private router: Router, private route: ActivatedRoute) { }

  ngOnInit() {
    // this.route.queryParams
    // .subscribe(params => {
    //   this.alias = params.alias;
    //   console.log(this.alias);
    // });
    this.route.params.subscribe((params: Params) => this.alias = params['alias']);
    console.log('this.alias :', this.alias);

    this.getData();
  }

  getData() {
    this.midViewService.getMid(this.alias)
      .then(mid => {
        console.log('paginate data is: ', mid.data);
        this.mid = mid.data
      });
  }
  ngOnDestroy() {
  }
}