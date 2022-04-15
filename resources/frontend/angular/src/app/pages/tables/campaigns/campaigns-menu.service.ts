import { Injectable } from '@angular/core';
import { BehaviorSubject, of } from 'rxjs';
import { ApiService } from 'src/app/api.service';

@Injectable({
  providedIn: 'root'
})
export class CampaignsMenuService {
  campaigns: any;
  gateway: any;
  columns: any;
  public campaignsGetResponse = new BehaviorSubject([]);
  public deleteResponse = new BehaviorSubject([]);
  public columnsResponse = new BehaviorSubject([]);

  campaignsGetResponse$ = this.campaignsGetResponse.asObservable();
  deleteResponse$ = this.deleteResponse.asObservable();
  columnsResponse$ = this.columnsResponse.asObservable();

  constructor(private apiService: ApiService) { }

  async getCampaigns(filters): Promise<any> {
    await this.apiService.getData(`campaigns?start_date=${filters.start}&end_date=${filters.end}&fields=${filters.all_fields}&values=${filters.all_values}&search=${filters.search}`).then(res => res.json()).then((data) => {
      this.campaigns = data;
      // this.campaignsGetResponse.next(data);
    });
    return this.campaigns;
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
}
