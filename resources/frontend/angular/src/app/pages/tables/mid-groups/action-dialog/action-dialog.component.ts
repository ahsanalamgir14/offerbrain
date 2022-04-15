import { Component, Inject, Optional } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { ActionDialogModel } from './action-dialog.model';
import { environment } from 'src/environments/environment';

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
  bankPercentage: string;
  timeout: any = null;
  isExecute = false;
  assignedMids: string;
  endPoint = '';

  constructor(
    public dialogRef: MatDialogRef<ActionDialogComponent>,
    //@Optional() is used to prevent error if no data is passed
    @Optional() @Inject(MAT_DIALOG_DATA) public data: ActionDialogModel) {
    this.local_data = { ...data };
    this.action = this.local_data.action;
    this.title = data.title;
    this.message = data.message;
    this.groupName = data.group_name;
    this.bankPercentage = data.bank_per;
    this.endPoint = environment.endpoint;
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


}
