import { Component, OnInit } from '@angular/core';
import {ThemePalette} from '@angular/material/core';
import {ProgressSpinnerMode} from '@angular/material/progress-spinner';

@Component({
  selector: 'fury-spinner2',
  templateUrl: './spinner2.component.html',
  styleUrls: ['./spinner2.component.scss']
})
export class Spinner2Component implements OnInit {
  color: ThemePalette = 'primary';
  mode: ProgressSpinnerMode = 'determinate';

  value = 50;
  constructor() { }

  ngOnInit(): void {
  }

}
