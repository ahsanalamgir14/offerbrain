import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Params } from '@angular/router';
import { Subscription } from 'rxjs';
import { fadeInRightAnimation } from '../../../@fury/animations/fade-in-right.animation';
import { fadeInUpAnimation } from '../../../@fury/animations/fade-in-up.animation';
import { MidViewService } from './mid-view.service';

@Component({
  selector: 'fury-mid-view',
  templateUrl: './mid-view.component.html',
  styleUrls: ['./mid-view.component.scss'],
  animations: [fadeInRightAnimation, fadeInUpAnimation]

})
export class MidViewComponent implements OnInit {

  mid: any;
  alias: string;
  getSubscription: Subscription;

  constructor(private midViewService: MidViewService, private route: ActivatedRoute) { }

  ngOnInit() {
    this.route.params.subscribe((params: Params) => this.alias = params['alias']);
    this.getData();
  }

  getData() {
    this.midViewService.getMid(this.alias)
      .then(mid => {
        this.mid = mid.data
      });
  }
  ngOnDestroy() {
  }
}