import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ChartData } from 'chart.js';
import { BehaviorSubject, of } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from 'src/environments/environment';
import { ApiService } from 'src/app/api.service';

@Injectable()
export class ProductDetailService {
  details: any;
  product_details: any;
  gateway: any;
  public productDetailGetResponse = new BehaviorSubject([]);

  productDetailGetResponse$ = this.productDetailGetResponse.asObservable();

  constructor(private apiService: ApiService) { }
  async getMidDetail(group_name): Promise<any> {
    await this.apiService.getData(`getMidDetail?group_name=${group_name}`)
    .then(res => res.json()).then((data) => {
      this.details = data;
    });
    return this.details;
  }
  async getProductDetail(id): Promise<any> {
    await this.apiService.getData(`getProductDetail?id=${id}`)
    .then(res => res.json()).then((data) => {
      this.product_details = data;
    });
    return this.product_details;
  }
}
