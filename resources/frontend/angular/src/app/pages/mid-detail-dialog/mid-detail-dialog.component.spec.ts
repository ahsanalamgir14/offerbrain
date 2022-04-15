import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MidDetailDialogComponent } from './mid-detail-dialog.component';

describe('MidDetailDialogComponent', () => {
  let component: MidDetailDialogComponent;
  let fixture: ComponentFixture<MidDetailDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ MidDetailDialogComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(MidDetailDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
