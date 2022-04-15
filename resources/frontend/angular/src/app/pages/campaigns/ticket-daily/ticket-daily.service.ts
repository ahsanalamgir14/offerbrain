// import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, of } from 'rxjs';
import { ApiService } from 'src/app/api.service';

@Injectable({
  providedIn: 'root'
})
export class TicketDailyService {

  orders: any;
  gateway: any;
  public formulasGetResponse = new BehaviorSubject([]);
  public ticketsGetResponse = new BehaviorSubject([]);

  formulasGetResponse$ = this.formulasGetResponse.asObservable();

  constructor(private apiService: ApiService) { }

  async getFormulas(): Promise<any> {
    await this.apiService.getData('formulas').then(res => res.json()).then((data) => {
      this.orders = data;
      this.formulasGetResponse.next(data);
    });
    return this.orders;
  }
}

