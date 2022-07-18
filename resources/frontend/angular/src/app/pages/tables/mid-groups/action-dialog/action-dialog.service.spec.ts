import { TestBed } from '@angular/core/testing';

import { ActionDialogService } from './action-dialog.service';

describe('ActionDialogService', () => {
  let service: ActionDialogService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ActionDialogService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
