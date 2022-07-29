import { Injectable } from '@angular/core';
import { BehaviorSubject, of } from 'rxjs';
import { ApiService } from 'src/app/api.service';

@Injectable({
  providedIn: 'root'
})
export class SubAffiliatesService {

  affiliates: any;
  gateway: any;
  public affiliatesGetResponse = new BehaviorSubject([]);
  public affOptionsResponse = new BehaviorSubject([]);
  public grossRevenueResponse = new BehaviorSubject([]);

  affiliatesGetResponse$ = this.affiliatesGetResponse.asObservable();
  affOptionsResponse$ = this.affOptionsResponse.asObservable();
  grossRevenueResponse$ = this.grossRevenueResponse.asObservable();

  constructor(private apiService: ApiService) { }

  // async getSubAffiliates(): Promise<any> {
  //   await this.apiService.getData(`sub-affiliates`).then(res => res.json()).then((data) => {
  //     this.affiliates = data;
  //     // this.affiliatesGetResponse.next(data);
  //   });
  //   return this.affiliates;
  // }

  async getSubAffiliates(filters): Promise<any> {
    await this.apiService.getData(`sub-affiliates?pageno=${filters.currentPage}&per_page=${filters.pageSize}&start_date=${filters.start}&end_date=${filters.end}&affiliate_id=${filters.affiliate_id}&sub1=${filters.sub1}&sub2=${filters.sub2}&sub3=${filters.sub3}`)
      .then(res => res.json()).then((data) => {
        console.log('data in service', data);
        this.affiliates = data;
        // this.affiliatesGetResponse.next(data);
      });
    return this.affiliates;
  }

  async getAffiliateOptions(): Promise<any> {
    await this.apiService.getData(`networks`).then(res => res.json()).then((data) => {
      console.log(data);
      this.affOptionsResponse.next(data);
    });
  }

  async getGrossRevenue(data): Promise<any> {
    await this.apiService.postData(`sub_affiliate_gross_revenue`, data).then(res => res.json()).then((data) => {
      console.log(data);
      this.grossRevenueResponse.next(data);
    });
  }

  async getAPIKey(): Promise<any> {
    return await this.apiService.getData(`get_EF_key`).then(res => res.json()).then((data) => {
      if (data.status) {
        console.log('return data.key; :', data.key);
        return data.key;
      }
    });
  }
}
