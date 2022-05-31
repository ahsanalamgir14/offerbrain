import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, of } from 'rxjs';
import { ApiService } from 'src/app/api.service';

@Injectable({
  providedIn: 'root'
})
export class CampaignService {

  data: any;
  gateway: any;
  public ticketsGetResponse = new BehaviorSubject([]);
  public addNewMonthResponse = new BehaviorSubject([]);
  public ticketDailyResponse = new BehaviorSubject([]);
  public ticketWeeklyResponse = new BehaviorSubject([]);
  public ticketMonthlyResponse = new BehaviorSubject([]);

  ticketsGetResponse$ = this.ticketsGetResponse.asObservable();
  addNewMonthResponse$ = this.addNewMonthResponse.asObservable();
  ticketDailyResponse$ = this.ticketDailyResponse.asObservable();
  ticketWeeklyResponse$ = this.ticketWeeklyResponse.asObservable();
  ticketMonthlyResponse$ = this.ticketMonthlyResponse.asObservable();

  constructor(private apiService: ApiService) { }

  async getGoldenTicketData(): Promise<any> {
    await this.apiService.getData('golden-ticket').then(res => res.json()).then((data) => {
      this.data = data;
      this.ticketsGetResponse.next(data);
    });
    return this.data;
  }
  async filterGoldenTicket(month, year): Promise<any> {
    await this.apiService.getData(`filter-golden-ticket/${month}/${year}`).then(res => res.json()).then((data) => {
      this.data = data;
      // this.ticketsGetResponse.next(data);
    });
    return this.data;
  }
  async addCurrentMonth(month, year): Promise<any> {
    await this.apiService.getData(`golden-ticket/create/${month}/${year}`).then(res => res.json()).then((data) => {
      this.data = data;
      this.addNewMonthResponse.next(data);
    });
    return this.data;
  }
  async getDailyTicket(): Promise<any> {
    await this.apiService.getData(`ticket-daily`).then(res => res.json()).then((data) => {
      this.data = data;
      this.ticketDailyResponse.next(data);
    });
    return this.data;
  }
  async getWeeklyTicket(): Promise<any> {
    await this.apiService.getData(`ticket-weekly`).then(res => res.json()).then((data) => {
      this.data = data;
      this.ticketWeeklyResponse.next(data);
    });
    return this.data;
  }
  async getMonthlyTicket(): Promise<any> {
    await this.apiService.getData(`ticket-monthly`).then(res => res.json()).then((data) => {
      this.data = data;
      this.ticketMonthlyResponse.next(data);
    });
    return this.data;
  }
  async refreshGoldenTicket(): Promise<any> {
    await this.apiService.getData(`refresh-golden-ticket`).then(res => res.json()).then((data) => {
      this.data = data;
      this.ticketDailyResponse.next(data);
    });
    return this.data;
  }
  async refreshDailyTicket(): Promise<any> {
    await this.apiService.getData(`refresh-daily`).then(res => res.json()).then((data) => {
      this.data = data;
      this.ticketDailyResponse.next(data);
    });
    return this.data;
  }
  async refreshAllDailyTicket(): Promise<any> {
    await this.apiService.getData(`refresh-all-daily`).then(res => res.json()).then((data) => {
      this.data = data;
      this.ticketDailyResponse.next(data);
    });
    return this.data;
  }
  async refreshWeeklyTicket(): Promise<any> {
    await this.apiService.getData(`refresh-weekly`).then(res => res.json()).then((data) => {
      this.data = data;
      this.ticketWeeklyResponse.next(data);
    });
    return this.data;
  }
  async refreshMonthlyTicket(): Promise<any> {
    await this.apiService.getData(`refresh-monthly`).then(res => res.json()).then((data) => {
      this.data = data;
      this.ticketMonthlyResponse.next(data);
    });
    return this.data;
  }
  async refreshAllMonthlyTicket(): Promise<any> {
    await this.apiService.getData(`refresh-all-monthly`).then(res => res.json()).then((data) => {
      this.data = data;
      // this.ticketMonthlyResponse.next(data);
    });
    return this.data;
  }
}