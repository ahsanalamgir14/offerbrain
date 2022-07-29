import { Pipe, PipeTransform } from '@angular/core';

@Pipe({ name: 'tooltipList' })
export class TooltipListPipe implements PipeTransform {

  transform(lines: any, table: string): string {
    if (table === 'mids') {
      let list: string = '';
      lines.forEach(line => {
        list += '• ' + line + '\n';
      });
      return list;
    }
    else {
      let list: string = '  ';
      lines.forEach(line => {
        list += '• ' + line.name + '\n';
      });
      return list;
    }
  }
}