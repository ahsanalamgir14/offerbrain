import { DatePipe } from '@angular/common'
const datePipe: DatePipe = new DatePipe('en-US')
let nf = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 });

export class MidGroup {
    id: string;
    group_name: string;
    group_alias: string;
    assigned_mids: string;
    mids_data: string;
    gross_revenue: string;
    bank_per: string;
    target_bank_balance: string;
    quick_balance: string;
    updated_at: string;
    balance : number;
    color: string;

    constructor(midGroup) {
        this.id = midGroup.id;
        this.group_name = midGroup.group_name;
        this.group_alias = midGroup.group_alias;
        this.assigned_mids = midGroup.assigned_mids;
        this.mids_data = midGroup.mids_data;
        this.gross_revenue = '$' + nf.format(midGroup.gross_revenue);
        this.bank_per = midGroup.bank_per + ' %';
        this.balance = (midGroup.gross_revenue * midGroup.bank_per) / 100;
        //this.target_bank_balance = '$' + nf.format(this.balance);
        this.target_bank_balance = nf.format(midGroup.target_bank_balance);
        this.quick_balance = midGroup.quick_balance;
        this.updated_at = datePipe.transform(midGroup.updated_at, 'MM-dd-yyyy');
        this.color = midGroup.quick_balance < midGroup.target_bank_balance?'red':'black';
    }
}