import { Injectable } from '@angular/core';
import { BehaviorSubject, of } from 'rxjs';
import { ApiService } from 'src/app/api.service';

@Injectable({
  providedIn: 'root'
})
export class AffiliatesService {

  affiliates: any;
  gateway: any;
  public affiliatesGetResponse = new BehaviorSubject([]);
  public deleteResponse = new BehaviorSubject([]);

  affiliatesGetResponse$ = this.affiliatesGetResponse.asObservable();
  deleteResponse$ = this.deleteResponse.asObservable();

  constructor(private apiService: ApiService) { }

  async getSubAffiliates(): Promise<any> {
    await this.apiService.getData(`sub-affiliates`).then(res => res.json()).then((data) => {
      this.affiliates = data;
      // this.affiliatesGetResponse.next(data);
    });
    return this.affiliates;
  }
  async deleteData(id): Promise<any> {
    await this.apiService.getData(`destroy_affiliates?id=${id}`).then(res => res.json()).then((data) => {
      this.deleteResponse.next(data);
    });
  }
}
