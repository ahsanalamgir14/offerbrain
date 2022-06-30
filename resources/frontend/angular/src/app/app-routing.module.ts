import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { RoleGuard } from './role.guard';
import { LayoutComponent } from './layout/layout.component';
import { NotFoundComponent } from './layout/not-found/not-found.component';

const routes: Routes = [
  {
    path: '',
    component: LayoutComponent, canActivate: [RoleGuard], data: { roles: ['super_admin', 'user'], permissions: ['can-redirect'] },
    children: [
      {
        path: '',
        loadChildren: () => import('./pages/dashboard/dashboard.module').then(m => m.DashboardModule),
        pathMatch: 'full'
      },
      {
        path: 'customers',
        loadChildren: () => import('./pages/customers/customers.module').then(m => m.CustomersModule),
        // pathMatch: 'full'
      },
      {
        path: 'customer-detail',
        loadChildren: () => import('./pages/customers/customer-detail/customer-detail.module').then(m => m.CustomerDetailModule),
      },
      {
        path: 'product-filter-dialog',
        loadChildren: () => import('./pages/product-filter-dialog/product-filter-dialog.module').then(m => m.ProductFilterDialogModule),
      },
      {
        path: 'prospects',
        loadChildren: () => import('./pages/tables/prospects/prospects.module').then(m => m.ProspectsModule),
      },
      {
        path: 'orders',
        loadChildren: () => import('./pages/tables/orders/orders.module').then(m => m.OrdersModule),
      },
      {
        path: 'mids',
        loadChildren: () => import('./pages/tables/mids/mids.module').then(m => m.MidsModule),
      },
      {
        path: `mid-view/:alias`,
        loadChildren: () => import('./pages/mid-view/mid-view.module').then(m => m.MidViewModule),
      },
      {
        path: 'mid-groups',
        loadChildren: () => import('./pages/tables/mid-groups/mid-groups.module').then(m => m.MidGroupsModule),
      },
      {
        path: 'affiliates-network',
        loadChildren: () => import('./pages/tables/affiliates-network/affiliates-network.module').then(m => m.AffiliatesNetworkModule),
      },
      {
        path: 'sub-affiliates',
        loadChildren: () => import('./pages/tables/sub-affiliates/sub-affiliates.module').then(m => m.SubAffiliatesModule),
      },
      {
        path: 'orders/product-detail',
        loadChildren: () => import('./pages/tables/orders/product-detail/product-detail.module').then(m => m.ProductDetailModule),
      },
      {
        path: 'view/formulas',
        loadChildren: () => import('./pages/tables/view-formulas/view-formulas.module').then(m => m.ViewFormulasModule),
      },
      {
        path: 'create/formula',
        loadChildren: () => import('./pages/create-formula/create-formula.module').then(m => m.CreateFormulaModule),
      },
      {
        path: 'campaigns/golden-ticket',
        loadChildren: () => import('./pages/campaigns/golden-ticket/golden-ticket.module').then(m => m.GoldenTicketModule),
      },
      {
        path: 'campaigns/ticket-daily',
        loadChildren: () => import('./pages/campaigns/ticket-daily/ticket-daily.module').then(m => m.TicketDailyModule),
      },
      {
        path: 'campaigns/ticket-weekly',
        loadChildren: () => import('./pages/campaigns/ticket-weekly/ticket-weekly.module').then(m => m.TicketWeeklyModule),
      },
      {
        path: 'campaigns/ticket-monthly',
        loadChildren: () => import('./pages/campaigns/ticket-monthly/ticket-monthly.module').then(m => m.TicketMonthlyModule),
      },
      {
        path: 'campaign-builder',
        loadChildren: () => import('./pages/campaign-builder/campaign-builder.module').then(m => m.CampaignBuilderModule),
      },
      {
        path: 'my-campaigns',
        loadChildren: () => import('./pages/tables/my-campaigns/my-campaigns.module').then(m => m.MyCampaignsModule),
      },
      {
        path: 'campaign-view/:name',
        loadChildren: () => import('./pages/tables/my-campaigns/campaign-view/campaign-view.module').then(m => m.CampaignViewModule),
      },
    ]
  },
  { path: 'not-found', component: NotFoundComponent }
];

@NgModule({
  imports: [RouterModule.forRoot(routes, {
    initialNavigation: 'enabled',
    // preloadingStrategy: PreloadAllModules,
    scrollPositionRestoration: 'enabled',
    anchorScrolling: 'enabled',
    relativeLinkResolution: 'legacy'
  })],
  exports: [RouterModule]
})
export class AppRoutingModule {
}
