import { Component, OnInit, Inject } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { ProductDetailService } from './product-detail.service';
import { Subscription, Observable, of, ReplaySubject } from 'rxjs';
import { ProductDetailModel } from './product-detail.model';

@Component({
  selector: 'fury-product-detail',
  templateUrl: './product-detail.component.html',
  styleUrls: ['./product-detail.component.scss'],
  providers: [ProductDetailService]
})
export class ProductDetailComponent implements OnInit {
  details: [];
  mids: any;
  getSubscription: Subscription;
  message: string = ""
  isLoading = false;
  cancelButtonText = "Cancel";
  subject$: ReplaySubject<ProductDetailModel[]> = new ReplaySubject<ProductDetailModel[]>(1);
  data$: Observable<ProductDetailModel[]> = this.subject$.asObservable();
  products: ProductDetailModel[];
  
  constructor(@Inject(MAT_DIALOG_DATA) private data: any, private dialogRef: MatDialogRef<ProductDetailComponent>, private ProductDetailService: ProductDetailService) { 
    if (data) {
      this.isLoading = true;
      this.ProductDetailService.getMidDetail(data.group_name)
        .then(data => {
          this.mids = data.data;
          this.isLoading = false;
        })
    }
  }

  ngOnInit(): void {
  }
  async getMidDetaildata(id){
    this.ProductDetailService.getProductDetail(id)
      .then(data => {
        this.details = data.data;
        this.isLoading = false;
      })
  }
}
