import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AffiliatesNetworkComponent } from './affiliates-network.component';

describe('AffiliatesComponent', () => {
  let component: AffiliatesNetworkComponent;
  let fixture: ComponentFixture<AffiliatesNetworkComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AffiliatesNetworkComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(AffiliatesNetworkComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
