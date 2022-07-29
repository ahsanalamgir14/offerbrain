import { Component, Inject, Optional } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { ActionDialogModel } from './action-dialog.model';
import { environment } from 'src/environments/environment';
import { ActionDialogService } from './action-dialog.service';
import { ApiService } from 'src/app/api.service';
import { MidGroupsService } from '../mid-groups.service';

@Component({
  selector: 'fury-action-dialog',
  templateUrl: './action-dialog.component.html',
  styleUrls: ['./action-dialog.component.scss']
})
export class ActionDialogComponent {

  action: string;
  local_data: any;
  title: string;
  message: string;
  groupName: string;
  bankPercentage: number;
  timeout: any = null;
  isExecute = false;
  assignedMids: string;
  endPoint = '';
  authUrl:any;
  midGroupId : number;
  quick_balance:any;
  account_id:number;
  account_obj:any;


  constructor(
    
    public dialogRef: MatDialogRef<ActionDialogComponent>,
    private actionService : ActionDialogService,
    private apiService : ApiService,
    private midGroupService: MidGroupsService,
    //@Optional() is used to prevent error if no data is passed
    @Optional() @Inject(MAT_DIALOG_DATA) public data: ActionDialogModel) {
      
    this.local_data = { ...data };

    this.action = this.local_data.action;
    this.title = data.title;
    this.message = data.message;
    this.groupName = data.group_name;
    this.bankPercentage = data.bank_per;
    this.endPoint = environment.endpoint;
    this.midGroupId = this.local_data.id;
    this.quick_balance = this.local_data.quick_balance;
    console.log('(in construct)account id is '+ this.account_id);
    this.getAccountNames();


  }

  


  doAction() {
    var data: {};
    data = {
      'id': this.local_data.id,
      'group_name': this.groupName,
      'bank_per': this.bankPercentage
    };
    this.dialogRef.close({ event: this.action, data: data });
  }


  async checkTotalMids(value: string){
    clearTimeout(this.timeout);
    if(value != ''){
      this.timeout = setTimeout(()=>{  
        const response = fetch(`${this.endPoint}/api/get_assigned_mids?value=${value}`).then(res => res.json()).then((data) => {
            this.isExecute = true;
            this.assignedMids = data.mids;
          });
        }, 500);
      } else {
        this.isExecute = false;
    }
  }
  closeDialog() {
    this.dialogRef.close({ event: 'Cancel' });
  }


  
 async quickbookConnect(midGroupId:number)
  {
    console.log('quickbook action-dialog component for Mid-Group ID#'+midGroupId);

    await this.actionService.quickbookCon('quickbookConnect',midGroupId,this.account_id)
    .then(res => {
      this.authUrl = res;
      //this.bankAccounts();
      console.log(res);
      console.log('(in quick connect)account id is '+ this.account_id);

    }, error => {
      console.log('action-dialg component error in quickbookConnect');
    });

    // const res = this.actionService.quickbookCon('quickbookConnect', midGroupId);
    // console.log('auth url is '+res);
  }

   // get account balance from api for respcted midgroup id
   bankAccounts(){
    this.midGroupService.getAccounts('bankAccounts').subscribe(
     {next:(res)=>{console.log(res);
       this.updateQuickBalance(res);
     // this.bankAccount = res;
     },
     error:(err)=>console.log(err)}
   );
 }

   updateQuickBalance(data)
   {
     this.midGroupService.updateQuickBalance(data,'updateQuickBalance').subscribe(
       {next:(res)=>{console.log(res);
       //this.getData()
      },
       error:(err)=>console.log(err)}
     );
   }

  quickbookDisconnect(midGroupId:number)
  {
    console.log('quickbook action-dialog component disconnecting for mid-group ID#'+midGroupId);
    this.actionService.quickbookDisconnect(midGroupId,'quickbookDisconnect').subscribe({
      next:(res)=>{console.log(res);
      this.refreshPage()
     },
      error:(err)=>console.log(err)
    });
  }

  refreshPage() {
    window.location.reload();
   }

   // get all respective accounts fro database(quick_accounts)
   getAccountNames(){
    this.midGroupService.getAccounts('accountNames').subscribe(
     {next:(res)=>{
      this.account_obj = res.accountNames; 
      console.log(res);
       
      
     },
     error:(err)=>console.log(err)}
   );
  }
 



}
