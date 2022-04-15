import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TicketMonthlyComponent } from './ticket-monthly.component';

describe('TicketMonthlyComponent', () => {
  let component: TicketMonthlyComponent;
  let fixture: ComponentFixture<TicketMonthlyComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ TicketMonthlyComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(TicketMonthlyComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
