import { Injectable } from '@angular/core';
import { BehaviorSubject, of } from 'rxjs';
import { ApiService } from 'src/app/api.service';

@Injectable({
  providedIn: 'root'
})
export class AutomationBuilderService {

  data: any;
  public saveResponse = new BehaviorSubject([]);
  public getProductsResponse = new BehaviorSubject([]);
  public getOptionsResponse = new BehaviorSubject([]);

  saveResponse$ = this.saveResponse.asObservable();
  getProductsResponse$ = this.getProductsResponse.asObservable();
  getOptionsResponse$ = this.getOptionsResponse.asObservable();

  constructor(private apiService: ApiService) { }

  async save(a, b, c): Promise<any> {
    var data = {};
    data = { ...data, ...a, ...b, ...c}
    await this.apiService.postData(`automation_builder`, data).then(res => res.json()).then((data) => {
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
}
