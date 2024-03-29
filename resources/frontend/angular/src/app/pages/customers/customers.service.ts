import { Injectable } from '@angular/core';
import { BehaviorSubject, of } from 'rxjs';
import { ApiService } from 'src/app/api.service';

@Injectable()
export class CustomersService {

  customers: any;
  gateway: any;
  customer_id: any;
  public customersGetResponse = new BehaviorSubject([]);
  public customersIdResponse = new BehaviorSubject([]);
  public deleteResponse = new BehaviorSubject([]);

  customersGetResponse$ = this.customersGetResponse.asObservable();
  customersIdResponse$ = this.customersIdResponse.asObservable();
  deleteResponse$ = this.deleteResponse.asObservable();

  constructor(private apiService: ApiService) { }

  async getCustomers(filters): Promise<any> {
    await this.apiService.getData(`customers?page=${filters.currentPage}&per_page=${filters.pageSize}&search=${filters.search}&customer_id=${filters.customer_id}`)
      .then(res => res.json()).then((data) => {
        this.customers = data;
        this.customersGetResponse.next(data);
      });
    return this.customers;
  }
  
  async getOrdersCount(id): Promise<any> {
    await this.apiService.getData(`getOrdersCount?id=${id}`)
      .then(res => res.json()).then((data) => {
        this.customer_id = data;
        this.customersIdResponse.next(data);
      });
    return this.customer_id;
  }

  async deleteData(data): Promise<any> {
    await this.apiService.postData(`destroy_customers`, data).then(res => res.json()).then((data) => {
      this.deleteResponse.next(data);
    });
  }
}
