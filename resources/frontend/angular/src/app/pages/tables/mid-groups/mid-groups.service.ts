import { Injectable } from '@angular/core';
import { BehaviorSubject, of, Observable } from 'rxjs';
import { environment } from 'src/environments/environment';
import { ApiService } from 'src/app/api.service';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class MidGroupsService {

  midGroups: any;
  gateway: any;
  accounts: any;
  endPoint = '';
  public getResponse = new BehaviorSubject({});
  public refreshResponse = new BehaviorSubject({});
  public addGroupResponse = new BehaviorSubject({});
  public updateGroupResponse = new BehaviorSubject({});
  public deleteGroupResponse = new BehaviorSubject({});

  getResponse$ = this.getResponse.asObservable();
  refreshResponse$ = this.refreshResponse.asObservable();
  addGroupResponse$ = this.addGroupResponse.asObservable();
  deleteGroupResponse$ = this.deleteGroupResponse.asObservable();
  updateGroupResponse$ = this.updateGroupResponse.asObservable();

  constructor(private apiService: ApiService, private http: HttpClient) {
    this.endPoint = environment.endpoint;
  }

  async getMidGroups(filters): Promise<any> {
    await this.apiService.getData(`mid-groups?start_date=${filters.start}&end_date=${filters.end}`)
      .then(res => res.json()).then((data) => {
        this.midGroups = data;
        this.getResponse.next(data);
      });
    return this.midGroups;
  }

  async refresh(): Promise<any> {
    await this.apiService.getData(`refresh_mids_groups`).then(res => res.json()).then((data) => {
      this.refreshResponse.next(data);
    });
  }

  async addGroup(data): Promise<any> {
    await this.apiService.postData(`mid-groups`, data).then(res => res.json()).then((data) => {
      this.addGroupResponse.next(data);
    });
  }

  async updateGroup(data): Promise<any> {
    await this.apiService.updateData(`mid-groups/${data.id}`, data).then(res => res.json()).then((data) => {
      this.updateGroupResponse.next(data);
    });
  }

  async deleteGroup(data): Promise<any> {
    await this.apiService.deleteData(`mid-groups/${data.id}`).then(res => res.json()).then((data) => {
      this.deleteGroupResponse.next(data);
    });
  }

  getAccounts(url): Observable<any> {
    // await this.apiService.getData(url)
    // .then(res => res.json()).then((data) => {
    //     this.accounts = data;
    //     console.log(this.accounts);
    //     //this.getResponse.next(data);
    //   });
    //     return this.accounts;

    return this.http.get(`${this.endPoint}/api/${url}`);


  }

  updateQuickBalance(data, url): Observable<any> {
    return this.http.put(`${this.endPoint}/api/${url}`, data);
  }

}
