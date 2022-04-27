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

  affiliatesGetResponse$ = this.affiliatesGetResponse.asObservable();
  affOptionsResponse$ = this.affOptionsResponse.asObservable();

  constructor(private apiService: ApiService) { }

  async getSubAffiliates(): Promise<any> {
    await this.apiService.getData(`sub-affiliates`).then(res => res.json()).then((data) => {
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
}
