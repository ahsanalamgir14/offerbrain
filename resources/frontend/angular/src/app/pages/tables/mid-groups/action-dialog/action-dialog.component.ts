import { Component, Inject, Optional } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { environment } from 'src/environments/environment';
import { MidGroupsService } from '../mid-groups.service';
import { ActionDialogModel } from './action-dialog.model';
import { ActionDialogService } from './action-dialog.service';

@Component({
  selector: 'fury-action-dialog',
  templateUrl: './action-dialog.component.html',
  styleUrls: ['./action-dialog.component.scss']
})
export class ActionDialogComponent {

  action: string;
  quick: string;
  local_data: any;
  title: string;
  message: string;
  groupName: string;
  bankPercentage: any;
  timeout: any = null;
  isExecute = false;
  assignedMids: string;
  endPoint = '';
  connect: any;
  midGroupId: number;
  quick_balance: any;
  account_id: number;
  account_obj: any;
  msg: any;
  midGroup: any;
  invoices: any;
  authUrl: any = '';
  //authUrl='https://appcenter.intuit.com/connect/oauth2?client_id=ABYkMNEjxULZh9YxOGY7Qf6wlSW3a7d5fZG0f6qr6WwBZDydNz&scope=com.intuit.quickbooks.accounting&redirect_uri=http%3A%2F%2Foffer-brain.test%2Fcallback.php+&response_type=code&state=WXILD';

  constructor(

    public dialogRef: MatDialogRef<ActionDialogComponent>,
    private actionService: ActionDialogService,
    private midGroupService: MidGroupsService,
    //@Optional() is used to prevent error if no data is passed
    @Optional() @Inject(MAT_DIALOG_DATA) public data: ActionDialogModel) {

    this.local_data = { ...data };
    this.midGroup = this.local_data.midRow;
    this.action = this.local_data.action;
    this.quick = this.local_data.quick;
    this.title = data.title;
    this.message = data.message;
    this.groupName = data.group_name;
    this.bankPercentage = data.bank_per;
    this.endPoint = environment.endpoint;
    this.midGroupId = this.local_data.id;
    this.quick_balance = this.local_data.quick_balance;
    this.getAccountNames();
    this.getInvoices();
  }

  async getQuickAccounts(mid_group_id) {

    await this.actionService.quickbookCon('quickbookConnect', mid_group_id, 0)
      .then(res => {


        const is_valid = res.is_valid
        this.authUrl = res.authUrl;
        const event = '';
        if (!is_valid) {
          // const oauth = new this.OAuthCode(this.authUrl);
          // oauth.loginPopup();
          this.logingPopupUri(this.authUrl, event)
        }
        //this.bankAccounts(event);

      }, error => {
      });
  }

  changeInvoice(target_bank_balance, target_balance, i) {
    //alert('change is '+e);
    var one = target_bank_balance.replace(/,/g, '');
    var two = target_balance.replace(/,/g, '');

    one = parseFloat(one);
    two = parseFloat(two);
    if (one < two) {
      document.getElementById("balance_input" + i).style.color = "red";
    }
    else {
      document.getElementById("balance_input" + i).style.color = "black";
    }
    document.getElementById("update" + i).style.color = "#6495ed";

  }


  deleteInvoice(i) {
    var x = confirm('Are you sure to remove this invoice!');
    if (x) {
      this.midGroup.splice(0, 1);
    }
  }

  updateInvoice(i, target_bank_balance) {
    const a = this.midGroup[i]['target_bank_balance'] = target_bank_balance;
    document.getElementById("update" + i).style.color = "#39404d";
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

  doQuickAction(status) {
    if (!this.account_id) {
      this.msg = 'Please Choose an Account!';
      return;
    }
    if (this.local_data.id) {
      this.quickbookConnect(this.local_data.id, status)
      return;
    }
    var data: {};
    data = {
      'id': this.local_data.id,
      'group_name': this.groupName,
      'bank_per': this.bankPercentage,
      'account_id': this.account_id,
    };
    this.dialogRef.close({ event: this.action, data: data });

  }

  async checkTotalMids(value: string) {
    clearTimeout(this.timeout);
    if (value != '') {
      this.timeout = setTimeout(() => {
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

  async quickbookConnect(midGroupId: number, status) {


    await this.actionService.quickbookGet('quickbookGet', midGroupId, this.account_id, status)
      .then(res => {
        this.connect = res;
        const is_valid = res.is_valid
        this.authUrl = res.authUrl;
        const event = 'connect';
        if (!is_valid) {
          // const oauth = new this.OAuthCode(this.authUrl);
          // oauth.loginPopup();
          this.logingPopupUri(this.authUrl, event)
        }
      }, error => {
      });
  }

  async generateInvoice(data: any[]) {
    await this.actionService.generateInvoice('generateInvoice', data)
      .then(res => {
        this.connect = res;
        const is_valid = res.is_valid
        this.authUrl = res.authUrl;
        const event = 'connect';
        if (!is_valid) {
          this.logingPopupUri(this.authUrl, event)
        }

      }, error => {
      });
  }

  // get account balance from api for respcted midgroup id
  bankAccounts(event) {
    //alert('bankAccounts()');
    this.midGroupService.getAccounts('bankAccounts', 0).subscribe(
      {
        next: (res) => {
          this.updateQuickBalance(res, event);
        },
      }
    );


  }

  updateQuickBalance(data, event) {
    this.midGroupService.updateQuickBalance(data, 'updateQuickBalance').subscribe(
      {
        next: (res) => {
          //this.getData()
          if (event != '') {
            //alert('event not empty '+event);
            this.dialogRef.close({ event: event });
          }
          else {
            //alert('event is empty '+event);
            //this.midGroupService.refresh();
            this.getAccountNames();
          }
        },
      }
    );

  }

  quickbookDisconnect(midGroupId: number) {
    this.actionService.quickbookDisconnect(midGroupId, 'quickbookDisconnect').subscribe({
      next: (res) => {
        //this.refreshPage()
        this.dialogRef.close({ event: 'disConnect' });
      },
    });
  }

  refreshPage() {
    window.location.reload();
  }

  // get all respective accounts from database(quick_accounts)
  getAccountNames() {
    this.midGroupService.getAccounts('accountNames', this.midGroupId).subscribe(
      {
        next: (res) => {
          this.account_obj = res.accountNames;
        },
      }
    );
  }

  // get invoices from database(quick_accounts)
  getInvoices() {
    this.midGroupService.getInvoices('getInvoices', this.midGroupId).subscribe(
      {
        next: (res) => {
          this.invoices = res.invoices;
        },
      }
    );
  }



  logingPopupUri(authUrl, event) {
    // Launch Popup
    var parameters = "location=1,width=800,height=650";
    parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;

    var win = window.open(authUrl, 'connectPopup', parameters);
    var pollOAuth = window.setInterval(() => {
      try {

        if (win.document.URL.indexOf("code") != -1) {
          window.clearInterval(pollOAuth);
          //win.close();
          //location.reload();
          this.bankAccounts(event);
        }
      } catch (e) {
      }
    }, 100);
  }
}