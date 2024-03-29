import { DOCUMENT } from '@angular/common';
import { Component, Inject, Renderer2 } from '@angular/core';
import { MatIconRegistry } from '@angular/material/icon';
import { SidenavService } from './layout/sidenav/sidenav.service';
import { ThemeService } from '../@fury/services/theme.service';
import { ActivatedRoute } from '@angular/router';
import { filter } from 'rxjs/operators';
import { Platform } from '@angular/cdk/platform';
import { SplashScreenService } from '../@fury/services/splash-screen.service';

@Component({
  selector: 'fury-root',
  templateUrl: './app.component.html'
})
export class AppComponent {

  constructor(private sidenavService: SidenavService,
    private iconRegistry: MatIconRegistry,
    private renderer: Renderer2,
    private themeService: ThemeService,
    @Inject(DOCUMENT) private document: Document,
    private platform: Platform,
    private route: ActivatedRoute,
    private splashScreenService: SplashScreenService) {
    this.route.queryParamMap.pipe(
      filter(queryParamMap => queryParamMap.has('style'))
    ).subscribe(queryParamMap => this.themeService.setStyle(queryParamMap.get('style')));

    this.iconRegistry.setDefaultFontSetClass('material-icons-outlined');
    this.themeService.theme$.subscribe(theme => {
      if (theme[0]) {
        this.renderer.removeClass(this.document.body, theme[0]);
      }

      this.renderer.addClass(this.document.body, theme[1]);
    });

    if (this.platform.BLINK) {
      this.renderer.addClass(this.document.body, 'is-blink');
    }

    //Note: Do not remove any comment in this file!!!
    this.sidenavService.addItems([
      // {
      //   name: 'APPS',
      //   position: 5,
      //   type: 'subheading',
      //   customClass: 'first-subheading'
      // },
      {
        name: 'Dashboard',
        routeOrFunction: '/',
        icon: 'dashboard',
        position: 6,
        pathMatchExact: true
      },
      {
        name: 'Customers',
        routeOrFunction: '/customers',
        icon: 'group',
        badgeColor: "#2196F3",
        position: 7,
        // pathMatchExact: true
      },
      {
        name: 'Prospects',
        routeOrFunction: '/prospects',
        icon: 'search',
        badgeColor: '#2196F3',
        position: 8,
      },
      {
        name: 'Orders',
        routeOrFunction: '/orders',
        icon: 'shopping_cart',
        badgeColor: '#2196F3',
        position: 9,
      },
      {
        name: 'Mid-Reports',
        icon: 'data_usage',
        position: 10,
        subItems: [
          {
            name: 'Mids',
            routeOrFunction: '/mids',
            position: 11
          },
          {
            name: 'Mid Groups',
            routeOrFunction: '/mid-groups',
            position: 12
          }
        ]
      },
      {
        name: 'Affiliates Menu',
        icon: 'equalizer',
        position: 11,
        subItems: [
          {
            name: 'Affiliates Network',
            routeOrFunction: '/affiliates-network',
            position: 11
          },
          {
            name: 'Sub-Affiliates',
            routeOrFunction: '/sub-affiliates',
            position: 12
          }
        ]
      },
      {
        name: 'Campaigns',
        icon: 'device_hub',
        position: 12,
        subItems: [
          {
            name: 'Campaign Builder',
            routeOrFunction: '/campaign-builder',
            position: 13
          },
          {
            name: 'My Campaigns',
            routeOrFunction: '/my-campaigns',
            position: 14
          },
        ]
      },
      // {
      //   name: 'Formula Section',
      //   position: 12,
      //   type: 'subheading',
      // },
      // {
      //   name: 'Formulas Builder',
      //   routeOrFunction: '/create/formula',
      //   icon: 'functions',
      //   badgeColor: '#2196F3',
      //   position: 13,
      // },
      // {
      //   name: 'View Formulas',
      //   routeOrFunction: '/view/formulas',
      //   icon: 'assignment',
      //   badgeColor: '#2196F3',
      //   position: 14,
      // },
      // {
      //   name: 'Campaigns Section',
      //   type: 'subheading',
      //   position: 30
      // },
      // {
      //   name: 'All Campaigns',
      //   icon: 'local_mall',
      //   position: 31,
      //   subItems: [
      //     {
      //       name: 'Golden Ticket',
      //       routeOrFunction: '/campaigns/golden-ticket',
      //       position: 12
      //     },
      //     {
      //       name: 'Golden Ticket (Daily)',
      //       routeOrFunction: '/campaigns/ticket-daily',
      //       position: 13
      //     },
      //     {
      //       name: 'Golden Ticket (Weekly)',
      //       routeOrFunction: '/campaigns/ticket-weekly',
      //       position: 14
      //     },
      //     {
      //       name: 'Golden Ticket (Monthly)',
      //       routeOrFunction: '/campaigns/ticket-monthly',
      //       position: 15
      //     }
      //   ]
      // },
      {
        name: 'Automation',
        icon: 'device_hub',
        position: 12,
        subItems: [
          {
            name: 'Automation Builder',
            routeOrFunction: '/automation-builder',
            position: 13
          },
          {
            name: 'My Automations',
            routeOrFunction: '/my-automations',
            position: 14
          },
        ]
      },
      {
        name: 'Rewards',
        type: 'item',
        icon: 'whatshot',
        customClass: 'add-background',
        badgeColor: '#2196F3',
        position: 40
      },
      {
        name: 'Library',
        type: 'item',
        icon: 'library_add',
        customClass: 'add-background',
        position: 41
      },
      {
        name: 'Alerts',
        type: 'item',
        icon: 'alarm',
        customClass: 'add-background',
        position: 42
      },
      {
        name: 'Support',
        type: 'item',
        icon: 'insert_emoticon',
        customClass: 'add-background',
        position: 43
      },
      {
        name: 'Reporting',
        type: 'item',
        icon: 'zoom_in',
        customClass: 'add-background',
        position: 44
      }
    ]);
  }
}
