import { Injectable } from '@angular/core';
import { BehaviorSubject, of } from 'rxjs';
import { ApiService } from 'src/app/api.service';

@Injectable({
  providedIn: 'root'
})
export class MidsService {

  mids: any;
  columns: any;
  products: any;
  gateway: any;
  midOptions: any = [];
  public getResponse = new BehaviorSubject({});
  public refreshResponse = new BehaviorSubject({});
  public getProductsResponse = new BehaviorSubject([]);
  public assignGroupResponse = new BehaviorSubject({});
  public unAssignGroupResponse = new BehaviorSubject({});
  public assignBulkGroupResponse = new BehaviorSubject({});
  public removeBulkGroupResponse = new BehaviorSubject({});
  public columnsResponse = new BehaviorSubject([]);
  public resetInitialsResponse = new BehaviorSubject([]);
  public refreshInitialsResponse = new BehaviorSubject([]);

  getResponse$ = this.getResponse.asObservable();
  refreshResponse$ = this.refreshResponse.asObservable();
  assignGroupResponse$ = this.assignGroupResponse.asObservable();
  unAssignGroupResponse$ = this.unAssignGroupResponse.asObservable();
  assignBulkGroupResponse$ = this.assignBulkGroupResponse.asObservable();
  removeBulkGroupResponse$ = this.removeBulkGroupResponse.asObservable();
  columnsResponse$ = this.columnsResponse.asObservable();
  getProductsResponse$ = this.getProductsResponse.asObservable();
  resetInitialsResponse$ = this.resetInitialsResponse.asObservable();
  refreshInitialsResponse$ = this.refreshInitialsResponse.asObservable();

  constructor(private apiService: ApiService) { }

  async getMids(filters): Promise<any> {
    await this.apiService.getData(`mids?start_date=${filters.start}&end_date=${filters.end}&fields=${filters.all_fields}&values=${filters.all_values}&product_id=${filters.product_id}&selected_mids=${filters.selected_mids}`).then(res => res.json()).then((data) => {
      this.mids = data;
      this.getResponse.next(data);
    });
    return this.mids;
  }

  async refresh(): Promise<any> {
    await this.apiService.getData(`pull_payment_router_view`).then(res => res.json()).then((data) => {
      this.refreshResponse.next(data);
    });
  }

  async deleteData(alias): Promise<any> {
    await this.apiService.deleteData(`mids/${alias}`).then(res => res.json()).then((data) => {
      this.unAssignGroupResponse.next(data);
    });
  }

  async assignGroup(alias, groupName): Promise<any> {
    await this.apiService.getData(`assign_mid_group?alias=${alias}&&group_name=${groupName}`).then(res => res.json()).then((data) => {
      this.assignGroupResponse.next(data);
    });
  }

  async assignBulkGroup(groupName, data): Promise<any> {
    await this.apiService.postData(`assign_bulk_group?group_name=${groupName}`, data).then(res => res.json()).then((data) => {
      this.assignBulkGroupResponse.next(data);
    });
  }

  async getColumns(): Promise<any> {
    await this.apiService.getData(`columns/${'mids'}`).then(res => res.json()).then((data) => {
      this.columns = data;
      this.columnsResponse.next(data);
    });
    return this.columns;
  }

  async getProducts(start_date, end_date): Promise<any> {
    await this.apiService.getData(`date-range-products?start_date=${start_date}&end_date=${end_date}`).then(res => res.json()).then((data) => {
      // this.products = data;
      console.log(data);
      this.getProductsResponse.next(data);
    });
    return this.products;
  }

  async getMidOptions(): Promise<any> {
    await this.apiService.getData(`get-active-mids`).then(res => res.json()).then((data) => {
      this.midOptions = data;
    });
    return this.midOptions;
  }

  async refreshInitials(): Promise<any> {
    await this.apiService.getData(`refresh-initials`).then(res => res.json()).then((data) => {
      this.refreshInitialsResponse.next(data); 
    });
  }

  async resetInitials(): Promise<any> {
    await this.apiService.getData(`reset-initials`).then(res => res.json()).then((data) => {
      this.resetInitialsResponse.next(data);     
    });
  }
}
