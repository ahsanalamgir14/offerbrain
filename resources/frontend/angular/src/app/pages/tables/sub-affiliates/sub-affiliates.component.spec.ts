import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SubAffiliatesComponent } from './sub-affiliates.component';

describe('SubAffiliatesComponent', () => {
  let component: SubAffiliatesComponent;
  let fixture: ComponentFixture<SubAffiliatesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SubAffiliatesComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(SubAffiliatesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
