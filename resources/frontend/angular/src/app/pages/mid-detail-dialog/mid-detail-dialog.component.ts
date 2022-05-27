import { Component, Inject, OnInit } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MidDetailDialogModel } from './mid-detail-dialog';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'fury-mid-detail-dialog',
  templateUrl: './mid-detail-dialog.component.html',
  styleUrls: ['./mid-detail-dialog.component.scss']
})
export class MidDetailDialogComponent implements OnInit {
  id: string;
  gateway_id: string;
  start_date: string;
  end_date: string;
  total_count: number;
  status: number;
  products: string;
  type: string;
  endPoint = '';
  filters = {};
  isLoading = true;
  details = [];
  count_sum = 0;
  percentage_sum = 0;

  constructor(public dialogRef: MatDialogRef<MidDetailDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: MidDetailDialogModel) {
    this.gateway_id = data.gateway_id;
    this.start_date = data.start_date;
    this.end_date = data.end_date;
    this.total_count = data.total_count;
    this.status = data.status;
    this.type = data.type;
    this.products = data.product;
    this.endPoint = environment.endpoint;
  }

  ngOnInit(): void {
    const response = fetch(`${this.endPoint}/api/get_mid_count_detail?gateway_id=${this.gateway_id}&start_date=${this.start_date}&end_date=${this.end_date}&total_count=${this.total_count}&status=${this.status}&type=${this.type}&product=${this.products}`).then(res => res.json()).then((data) => {
      if (data.status) {
        data.data.forEach((v) => {
          this.percentage_sum = +this.percentage_sum + +v.percentage;
          this.count_sum = +this.count_sum + +v.total_count;
        });
        this.details = data.data;
        this.isLoading = false;
        this.products = this.replaceCommaLine(this.products);
        console.log(this.products);
      }
    });
  }
  replaceCommaLine(data) {
    let dataToArray = data.toString().split(',').map(item => item.trim());
    return dataToArray.join("|");
  }
  onConfirm(): void {

  }

  onDismiss(): void {
    this.dialogRef.close(false);
  }
  selection(data) {
  }
}
