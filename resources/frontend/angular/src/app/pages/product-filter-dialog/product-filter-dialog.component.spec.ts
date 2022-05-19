import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ProductFilterDialogComponent } from './product-filter-dialog.component';

describe('ProductFilterDialogComponent', () => {
  let component: ProductFilterDialogComponent;
  let fixture: ComponentFixture<ProductFilterDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ProductFilterDialogComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(ProductFilterDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
