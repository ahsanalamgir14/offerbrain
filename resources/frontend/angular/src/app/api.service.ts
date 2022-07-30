import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../environments/environment';

@Injectable({ providedIn: 'root' })
export class ApiService {
  endPoint = '';
  constructor(private http: HttpClient) {
    this.endPoint = environment.endpoint;
  }

  getData(url): Promise<any> {
    const response = fetch(`${this.endPoint}/api/${url}`)
      .catch(error => {
        if (error.response) {
          console.log(error)
        }
      });
    return response;
  }

  getQuickdata(url, midGroupId, account_id): Promise<any> {
    const response = fetch(`${this.endPoint}/api/${url}/${midGroupId}/${account_id}`)
      .catch(error => {
        if (error.response) {
          console.log(error)
        }
      });
    return response;
  }

  getQuickBalance(url, midGroupId, account_id, status): Promise<any> {
    const response = fetch(`${this.endPoint}/api/${url}/${midGroupId}/${account_id}/${status}`)
      .catch(error => {
        if (error.response) {
          console.log(error)
        }
      });
    return response;
  }

  getInvoice(url, data): Promise<any> {
    const response = fetch(`${this.endPoint}/api/${url}/${data}`)
      .catch(error => {
        if (error.response) {
          console.log(error)
        }
      });
    return response;
  }

  postData(url, data): Promise<any> {
    console.log("in api service,API data is ", data);
    const response = fetch(`${this.endPoint}/api/${url}`, {
      method: 'POST',
      body: JSON.stringify(data),
      headers: {
        'Accept': 'application/json',
        "Content-type": "application/json; charset=UTF-8",
      },
      credentials: 'same-origin'
    })
      .catch(error => {
        if (error.response) {
          console.log(error)
        }
      });
    console.log("API response", response);
    return response;
  }

  updateData(url, data): Promise<any> {
    console.log("api update data", data);
    const response = fetch(`${this.endPoint}/api/${url}`, {
      method: 'PUT',
      body: JSON.stringify(data),
      headers: { "Content-type": "application/json; charset=UTF-8" },
    })
      .catch(error => {
        if (error.response) {
          console.log(error)
        }
      });
    return response;
  }

  deleteData(url): Promise<any> {
    const response = fetch(`${this.endPoint}/api/${url}`, {
      method: 'Delete',
      body: JSON.stringify({}),
      headers: { "Content-type": "application/json; charset=UTF-8" }
    })
      .catch(error => {
        if (error.response) {
          console.log(error)
        }
      });
    return response;
  }
  downloadPdf(url): Promise<any> {
    const response = fetch(`${this.endPoint}/api/${url}`)
      .catch(error => {
        if (error.response) {
          console.log(error)
        }
      });
    console.log('api response ', response);
    return response;
  }
  downloadData(url): Promise<any> {
    console.log('api url ', url);
    const response = fetch(`${this.endPoint}/api/${url}`, {
      method: 'GET',
      body: JSON.stringify({}),
      headers: { "Content-type": "application/json; charset=UTF-8" }
    })
      .catch(error => {
        if (error.response) {
          console.log(error)
        }
      });
    return response;
  }
  logout() {
    document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    fetch(`${environment.endpoint}/api/logout`)
      .then(response => response.json())
      .then(data => {
        location.reload();
      })
      .catch((error) => {
        location.reload();
      });
  }
}