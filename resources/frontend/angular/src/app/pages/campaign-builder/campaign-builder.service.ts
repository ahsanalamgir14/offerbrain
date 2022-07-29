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
  public updateResponse = new BehaviorSubject([]);

  saveResponse$ = this.saveResponse.asObservable();
  getProductsResponse$ = this.getProductsResponse.asObservable();
  getOptionsResponse$ = this.getOptionsResponse.asObservable();
  updateResponse$ = this.updateResponse.asObservable();

  constructor(private apiService: ApiService) { }

  async save(a, b, c, d): Promise<any> {
    var data = {};
    data = { ...data, ...a, ...b, ...c, ...d }
    await this.apiService.postData(`campaigns_builder`, data).then(res => res.json()).then((data) => {
      this.saveResponse.next(data);
    });
  }

  async update(a, b, c, d, id): Promise<any> {
    var data = {};
    data = { ...data, ...a, ...b, ...c, ...d }
    await this.apiService.updateData(`campaigns_builder/${id}`, data).then(res => res.json()).then((data) => {
      this.updateResponse.next(data);
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

  async refreshProductOptions(): Promise<any> {
    return await this.apiService.getData(`pull_user_products`).then(res => res.json()).then((data) => {
      return data;
    });
  }

  async getCampaignData(id): Promise<any> {
    return await this.apiService.getData(`campaigns_builder/${id}`).then(res => res.json()).then((data) => {
      return data;
    });
  }

  selectCampaigns(o1: any, o2: any): boolean {
    return o1.id === o2.id && o1.campaign_id === o2.campaign_id;
  }

  selectNetworks(o1: any, o2: any): boolean {
    return o1.id === o2.id;
  }

  selectUpsellProducts(o1: any, o2: any): boolean {
    return o1.product_id === o2.product_id;
  }

  selectDownsells(o1: any, o2: any): boolean {
    return o1.product_id === o2.product_id;
  }
}
