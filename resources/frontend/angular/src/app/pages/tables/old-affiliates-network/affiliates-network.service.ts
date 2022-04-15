import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ChartData } from 'chart.js';
import { BehaviorSubject, of } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from 'src/environments/environment';
import { ApiService } from 'src/app/api.service';

@Injectable({
  providedIn: 'root'
})
export class AffiliatesNetworkService {

  networks: any;
  gateway: any;
  public customersGetResponse = new BehaviorSubject([]);
  public deleteResponse = new BehaviorSubject([]);

  customersGetResponse$ = this.customersGetResponse.asObservable();
  deleteResponse$ = this.deleteResponse.asObservable();

  constructor(private apiService: ApiService) { }

  async getNetworks(): Promise<any> {
    await this.apiService.getData(`networks`).then(res => res.json()).then((data) => {
      this.networks = data;
      // this.customersGetResponse.next(data);
    });
    return this.networks;
  }
  async deleteData(id): Promise<any> {
    await this.apiService.getData(`destroy_customers?id=${id}`).then(res => res.json()).then((data) => {
      this.deleteResponse.next(data);
    });
  }
}
