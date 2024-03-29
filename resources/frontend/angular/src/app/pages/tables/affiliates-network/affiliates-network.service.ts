import { Injectable } from '@angular/core';
import { BehaviorSubject, of } from 'rxjs';
import { ApiService } from 'src/app/api.service';

@Injectable({
  providedIn: 'root'
})
export class AffiliatesNetworkService {

  networks: any;
  gateway: any;
  columns: any;
  public affiliatesGetResponse = new BehaviorSubject([]);
  public deleteResponse = new BehaviorSubject([]);
  public columnsResponse = new BehaviorSubject([]);
  public affOptionsResponse = new BehaviorSubject([]);
  public refreshResponse = new BehaviorSubject({});


  affiliatesGetResponse$ = this.affiliatesGetResponse.asObservable();
  deleteResponse$ = this.deleteResponse.asObservable();
  columnsResponse$ = this.columnsResponse.asObservable();
  affOptionsResponse$ = this.affOptionsResponse.asObservable();
  refreshResponse$ = this.refreshResponse.asObservable();


  constructor(private apiService: ApiService) { }

  async getAffiliates(filters): Promise<any> {
    await this.apiService.getData(`networks?start_date=${filters.start}&end_date=${filters.end}&fields=${filters.all_fields}&values=${filters.all_values}&search=${filters.search}`).then(res => res.json()).then((data) => {
      this.networks = data;
    });
    return this.networks;
  }
  
  async deleteData(id): Promise<any> {
    await this.apiService.getData(`w?id=${id}`).then(res => res.json()).then((data) => {
      this.deleteResponse.next(data);
    });
  }

  async getColumns(): Promise<any> {
    await this.apiService.getData(`columns/${'affiliates'}`).then(res => res.json()).then((data) => {
      this.columns = data;
      this.columnsResponse.next(data);
    });
    return this.columns;
  }

  async getAffiliateOptions(): Promise<any> {
    await this.apiService.getData(`networks`).then(res => res.json()).then((data) => {
      console.log(data);
      this.affOptionsResponse.next(data);
    });
  }

  async refresh(): Promise<any> {
    await this.apiService.getData(`pull_affiliates`).then(res => res.json()).then((data) => {
      this.refreshResponse.next(data);
    });
  }
}
