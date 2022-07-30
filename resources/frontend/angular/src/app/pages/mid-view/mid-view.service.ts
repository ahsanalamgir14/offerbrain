import { Injectable } from '@angular/core';
import { ApiService } from 'src/app/api.service';

@Injectable({
  providedIn: 'root'
})
export class MidViewService {

  mid: any;

  constructor(private apiService: ApiService) { }

  async getMid(alias): Promise<any> {
    await this.apiService.getData(`mids/${alias}`)
      .then(res => res.json()).then((data) => {
        this.mid = data;
      });
    return this.mid;
  }

}
