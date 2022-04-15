import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TicketDailyComponent } from './ticket-daily.component';

describe('TicketDailyComponent', () => {
  let component: TicketDailyComponent;
  let fixture: ComponentFixture<TicketDailyComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ TicketDailyComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(TicketDailyComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
