import { Component, HostBinding, HostListener, Input, OnDestroy, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription, Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { SidenavItem } from './sidenav-item/sidenav-item.interface';
import { SidenavService } from './sidenav.service';
import { ToolbarService } from '../toolbar/toolbar.service';
import { ThemeService } from '../../../@fury/services/theme.service';

@Component({
  selector: 'fury-sidenav',
  templateUrl: './sidenav.component.html',
  styleUrls: ['./sidenav.component.scss']
})
export class SidenavComponent implements OnInit, OnDestroy {

  userName: string;
  userEmail: string;
  userDetailsSubscription: Subscription;
  sidenavUserVisible$ = this.themeService.config$.pipe(map(config => config.sidenavUserVisible));

  constructor(private router: Router, private sidenavService: SidenavService, private themeService: ThemeService, private toolbarService: ToolbarService) { }


  // @Input()
  // @HostBinding('class.collapsed')
  // collapsed: boolean;

  // @Input()
  // @HostBinding('class.expanded')
  // expanded: boolean;

  items$: Observable<SidenavItem[]>;



  ngOnInit() {
    this.getData();
    this.items$ = this.sidenavService.items$.pipe(
      map((items: SidenavItem[]) => this.sidenavService.sortRecursive(items, 'position'))
    );
    this.userDetailsSubscription = this.sidenavService.userDetailsResponse$.subscribe(data => this.manageUserDetails(data));
  }

  // toggleCollapsed() {
  //   this.sidenavService.toggleCollapsed();
  // }

  // @HostListener('mouseenter')
  // @HostListener('touchenter')
  // onMouseEnter() {
  //   this.sidenavService.setExpanded(true);
  // }

  // @HostListener('mouseleave')
  // @HostListener('touchleave')
  // onMouseLeave() {
  //   this.sidenavService.setExpanded(false);
  // }

  getData() {
    this.sidenavService.getUserDetails();
  }

  manageUserDetails(data) {
    if (data.status) {
      this.userName = data.name;
      this.userEmail = data.email;
    }
  }


  logout() {
    this.toolbarService.logout();
  }

  ngOnDestroy() {
  }
}
