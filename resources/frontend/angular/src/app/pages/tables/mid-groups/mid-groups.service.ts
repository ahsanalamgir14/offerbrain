import { Injectable } from '@angular/core';
import { BehaviorSubject, of, Observable } from 'rxjs';
import { environment } from 'src/environments/environment';
import { ApiService } from 'src/app/api.service';
import { HttpClient } from '@angular/common/http';
import { ActionDialogService } from './action-dialog/action-dialog.service';

@Injectable({
  providedIn: 'root'
})
export class MidGroupsService {

  midGroups: any;
  gateway: any;
  accounts: any;
  endPoint = '';
  connect:any;
  mid_group_id:any;
  authUrl:any = '';

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

  constructor(private apiService: ApiService, private http:HttpClient, 
    private actionService:ActionDialogService) {
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
  
  async addGroup(mydata): Promise<any> {
    await this.apiService.postData(`mid-groups`, mydata).then(res => res.json()).then((data) => {
      console.log('in mid-group-services, addGroup() response, mid-group id is '+data['mid_group_id']);
      //this.mid_group_id = data['mid_group_id'];
      if(mydata.account_id)
      {
        this.quickbookConnect(data['mid_group_id'],mydata.account_id)
      }
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

  getAccounts(url, midGroupId):Observable<any>
  {
    return this.http.get(`${this.endPoint}/api/${url}/${midGroupId}`);
  }

  getInvoices(url, id):Observable<any>
  {
    return this.http.get(`${this.endPoint}/api/${url}/${id}`);
  }

  updateQuickBalance(data, url): Observable<any> {
    return this.http.put(`${this.endPoint}/api/${url}`, data);
  }

  updateQuickAccounts(midGroupId, url):Observable<any>
  {
   return this.http.put(`${this.endPoint}/api/${url}/${midGroupId}`,midGroupId);
  }

  async quickbookConnect(midGroupId:number,account_id:number)
  {
    
    console.log('quickbook action-dialog component for Mid-Group ID#'+midGroupId);
    console.log('(in quick connect)account id is '+ account_id);
    await this.actionService.quickbookCon('quickbookConnect',midGroupId,account_id)
    .then(res => {
      this.connect = res;
      console.log('in mid-group services quickbookConnect() '+this.connect);
      const is_valid = res.is_valid
      this.authUrl = res.authUrl;
      const event = 'connect';
      if(!is_valid)
      {
        // const oauth = new this.OAuthCode(this.authUrl);
        // oauth.loginPopup();
        this.logingPopupUri(this.authUrl, midGroupId, event)
      }
      //this.bankAccounts(event);
      
    }, error => {
      console.log('action-dialg component error in quickbookConnect');
    });
  }

     // get account balance for respcted midgroup 
     bankAccounts(midGroupId ,event){
      //alert('bankAccounts()');
     this.getAccounts('bankAccounts',0).subscribe(
      {next:(res)=>{console.log(res);
        this.updateMyQuickAccounts(midGroupId);
        //alert('ok');
        this.updateMyQuickBalance(res, event);
      },
      error:(err)=>console.log(err)}
    );
   
    
  }
 
    updateMyQuickBalance(data, event)
    {
      this.updateQuickBalance(data,'updateQuickBalance').subscribe(
        {next:(res)=>{console.log(res);
        this.refresh()
        //this.dialogRef.close({event: 'connect'});
       
       },
        error:(err)=>console.log(err)}
      );
   
    }

    updateMyQuickAccounts(midGroupId)
    {
      this.updateQuickAccounts(midGroupId,'updateQuickAccounts').subscribe(
        {next:(res)=>{console.log(res);
       console.log('in mid-group-services updateQuickAccounts() '+res);
       console.log('in mid-group-services updateQuickAccounts() '+res.mid_group_id)
       },
        error:(err)=>console.log(err)}
      );
   
    }

    checkQuickAccounts(url)
    {
      return this.http.get(`${this.endPoint}/api/${url}`);
    }

    logingPopupUri(authUrl, midGroupId, event)
    {
          // Launch Popup
          var parameters = "location=1,width=800,height=650";
          parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;
   
          var win = window.open(authUrl, 'connectPopup', parameters);
          var pollOAuth = window.setInterval( ()=> {
              try {
   
                  if (win.document.URL.indexOf("code") != -1) {
                      window.clearInterval(pollOAuth);
                      win.close();
                      //location.reload();
                      this.bankAccounts(midGroupId ,event);
                  }
              } catch (e) {
                  console.log(e)
              }
          }, 100);
    }
}
