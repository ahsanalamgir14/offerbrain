import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ChartData } from 'chart.js';
import { BehaviorSubject, of } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from 'src/environments/environment';
import { ApiService } from 'src/app/api.service';

/**
 * @class DashboardService
 * This is just a pages service for populating the charts on the dashboard.
 * You will have to implement a similiar service for the data to be populated.
 * Examples are provided below :)
 */

@Injectable()
export class OrdersService {

  orders: any;
  gateway: any;
  public ordersGetResponse = new BehaviorSubject([]);
  public getCampaignsResponse = new BehaviorSubject([]);
  public getProductsResponse = new BehaviorSubject([]);

  ordersGetResponse$ = this.ordersGetResponse.asObservable();
  getCampaignsResponse$ = this.getCampaignsResponse.asObservable();
  getProductsResponse$ = this.getProductsResponse.asObservable();

  constructor(private apiService: ApiService) { }

  async getOrders(filters): Promise<any> {
    await this.apiService.getData(`orders?pageno=${filters.currentPage}&per_page=${filters.pageSize}
    &start_date=${filters.start}&end_date=${filters.end}&fields=${filters.all_fields}&values=${filters.all_values}&search=${filters.search}&gateway_id=${filters.gateway_id}
    &affiliate=${filters.affiliate}&sub_affiliate=${filters.sub_affiliate}`)
      .then(res => res.json()).then((data) => {
        this.orders = data;
        this.ordersGetResponse.next(data);
      });
    return this.orders;
  }
  async getDropContent(): Promise<any> {
    await this.apiService.getData(`getDropDownContent`)
      .then(res => res.json()).then((data) => {
        this.gateway = data;
        this.ordersGetResponse.next(data);
      });
    return this.gateway;
  }
  async getCampaigns(): Promise<any> {
    await this.apiService.getData(`campaigns`).then(res => res.json()).then((data) => {
      this.getCampaignsResponse.next(data);
    });
  }
  async getProducts(): Promise<any> {
    await this.apiService.getData(`products`).then(res => res.json()).then((data) => {
      this.getProductsResponse.next(data);
    });
  }


}
