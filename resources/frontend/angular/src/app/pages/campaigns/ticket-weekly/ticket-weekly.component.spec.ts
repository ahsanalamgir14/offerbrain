import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TicketWeeklyComponent } from './ticket-weekly.component';

describe('TicketWeeklyComponent', () => {
  let component: TicketWeeklyComponent;
  let fixture: ComponentFixture<TicketWeeklyComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ TicketWeeklyComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(TicketWeeklyComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
