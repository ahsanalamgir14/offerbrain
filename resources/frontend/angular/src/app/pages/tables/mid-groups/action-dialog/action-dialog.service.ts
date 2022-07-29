import { Injectable } from '@angular/core';
import { Observable, of, BehaviorSubject } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../../../environments/environment';
import { ApiService } from 'src/app/api.service';


@Injectable({
  providedIn: 'root'
})
    export class ActionDialogService {

    //authUrl:any;
    //authUrl = '';
    connect:any;
    is_valid:any;
    authUrl='https://appcenter.intuit.com/connect/oauth2?client_id=ABYkMNEjxULZh9YxOGY7Qf6wlSW3a7d5fZG0f6qr6WwBZDydNz&scope=com.intuit.quickbooks.accounting&redirect_uri=http%3A%2F%2Foffer-brain.test%2Fcallback.php+&response_type=code&state=WXILD';

    endPoint = '';
    // authUrl = '';
    authUrl='https://appcenter.intuit.com/connect/oauth2?client_id=ABYkMNEjxULZh9YxOGY7Qf6wlSW3a7d5fZG0f6qr6WwBZDydNz&scope=com.intuit.quickbooks.accounting&redirect_uri='+encodeURI(environment.endpoint)+'%2Fcallback.php+&response_type=code&state=WXILD';
    constructor(private http : HttpClient, private apiService:ApiService) { 
        this.endPoint = environment.endpoint;
    }

async quickbookCon(url:any,midGroupId:any,account_id)
  {
    await this.apiService.getQuickdata(url,midGroupId,account_id)
    .then(res => res.json()).then((data) => {
        this.connect = data;
        //this.getResponse.next(data);
        //console.log('in actionService '+data.is_valid);
       this.is_valid = data.is_valid;
      });
    //   if(!this.is_valid)
    //   {
    //     this.oauth.loginPopup();
    //   }
        return this.connect;
  }

  async quickbookGet(url:any,midGroupId:any, account_id, status)
  {
    await this.apiService.getQuickBalance(url,midGroupId,account_id,status)
    .then(res => res.json()).then((data) => {
        this.connect = data;
        //this.getResponse.next(data);
        //console.log('in actionService '+data.is_valid);
       this.is_valid = data.is_valid;
      });
    //   if(!this.is_valid)
    //   {
    //     this.oauth.loginPopup();
    //   }
        return this.connect;
   
  }

  async generateInvoice(url:any,data:any[])
  {
    await this.apiService.postData(url,data)
    .then(res => res.json()).then((data) => {
        this.connect = data;
        //this.getResponse.next(data);
        //console.log('in actionService '+data.is_valid);
       this.is_valid = data.is_valid;
      });
    //   if(!this.is_valid)
    //   {
    //     this.oauth.loginPopup();
    //   }
        return this.connect;
  }
  

   OAuthCode = function(authUrl) {
    this.loginPopup = function (parameter) {
        this.loginPopupUri(parameter);
    }

    this.loginPopupUri = function (parameter) {

        // Launch Popup
        var parameters = "location=1,width=800,height=650";
        parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;

        var win = window.open(authUrl, 'connectPopup', parameters);
        var pollOAuth = window.setInterval(function () {
            try {

                if (win.document.URL.indexOf("code") != -1) {
                    window.clearInterval(pollOAuth);
                    win.close();
                    //location.reload();
                }
            } catch (e) {
                console.log(e)
            }
        }, 100);
    }



}

   oauth = new this.OAuthCode(this.authUrl);

   quickbookDisconnect(midGroupId,url):Observable<any>
   {
       const data = {'quick_balance':null};
        return this.http.put(`${this.endPoint}/api/${url}/${midGroupId}`,data);
   }

}


