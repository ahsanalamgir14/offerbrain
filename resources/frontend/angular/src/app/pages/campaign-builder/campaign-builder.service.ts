import { Injectable } from '@angular/core';
import { BehaviorSubject, of } from 'rxjs';
import { ApiService } from 'src/app/api.service';

@Injectable({
  providedIn: 'root'
})
export class CampaignBuilderService {

  data: any;
  public saveResponse = new BehaviorSubject([]);
  public getProductsResponse = new BehaviorSubject([]);
  public getOptionsResponse = new BehaviorSubject([]);
  public refreshCampaignsResponse = new BehaviorSubject({});
  public refreshNetworksResponse = new BehaviorSubject({});

  saveResponse$ = this.saveResponse.asObservable();
  getProductsResponse$ = this.getProductsResponse.asObservable();
  getOptionsResponse$ = this.getOptionsResponse.asObservable();
  refreshCampaignsResponse$ = this.refreshCampaignsResponse.asObservable();
  refreshNetworksResponse$ = this.refreshNetworksResponse.asObservable();

  constructor(private apiService: ApiService) { }

  async save(a, b, c, d): Promise<any> {
    var data = {};
    data = { ...data, ...a, ...b, ...c, ...d }
    await this.apiService.postData(`campaigns_builder`, data).then(res => res.json()).then((data) => {
      this.saveResponse.next(data);
    });
  }

  async getProducts(): Promise<any> {
    await this.apiService.getData(`products`).then(res => res.json()).then((data) => {
      this.getProductsResponse.next(data);
    });
  }

  async getOptionsData(): Promise<any> {
    await this.apiService.getData(`campaign-builder-options`).then(res => res.json()).then((data) => {
      this.getOptionsResponse.next(data);
    });
  }

  async refreshCampaignsOptions(): Promise<any> {
    return await this.apiService.getData(`refresh_campaigns`).then(res => res.json()).then((data) => {
      return data;
    });
  }

  async refreshNetworksOptions(): Promise<any> {
    return await this.apiService.getData(`pull_affiliates`).then(res => res.json()).then((data) => {
      return data;
    });
  }
}
