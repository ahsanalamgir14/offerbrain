"use strict";(self.webpackChunkfury=self.webpackChunkfury||[]).push([[645],{1645:(Dt,C,i)=>{i.r(C),i.d(C,{CustomersModule:()=>vt});var c=i(6019),_=i(9133),N=i(6113),J=i(4625),R=i(7615),y=i(4382),p=i(4762),h=i(2968),m=i(2262),r=i(240),Q=i(1168),M=i(9190),u=i(5304),w=i(4099),t=i(3668),I=i(238);let x=(()=>{class o{constructor(e){this.apiService=e,this.customerDetailGetResponse=new w.X([]),this.customerDetailGetResponse$=this.customerDetailGetResponse.asObservable()}getCustomerDetail(e){return(0,p.mG)(this,void 0,void 0,function*(){return yield this.apiService.getData(`get_customer_detail?id=${e}`).then(n=>n.json()).then(n=>{this.details=n}),this.details})}}return o.\u0275fac=function(e){return new(e||o)(t.LFG(I.s))},o.\u0275prov=t.Yz7({token:o,factory:o.\u0275fac}),o})();var v=i(1136),T=i(7635),D=i(4643),g=i(9009),Z=i(904);function Y(o,s){1&o&&t._UZ(0,"mat-progress-bar",10)}function k(o,s){1&o&&(t.TgZ(0,"th",11),t.TgZ(1,"mat-checkbox",12),t.NdJ("click",function(n){return n.stopPropagation()}),t.qZA(),t.qZA())}function P(o,s){1&o&&(t.TgZ(0,"td",13),t.TgZ(1,"mat-checkbox",12),t.NdJ("click",function(n){return n.stopPropagation()}),t.qZA(),t.qZA())}function F(o,s){if(1&o&&(t.TgZ(0,"th",18),t._uU(1),t.qZA()),2&o){const e=t.oxw(2).$implicit;t.xp6(1),t.hij(" ",e.name,"")}}function z(o,s){if(1&o&&t._uU(0),2&o){const e=t.oxw().$implicit,n=t.oxw(2).$implicit;t.AsE("",e[n.property].name,"/",e[n.property].email,"")}}function O(o,s){if(1&o&&t._uU(0),2&o){const e=t.oxw().$implicit,n=t.oxw(2).$implicit;t.Oqu(e[n.property])}}function U(o,s){if(1&o&&(t.TgZ(0,"td",19),t.YNc(1,z,1,2,"ng-template",20),t.YNc(2,O,1,1,"ng-template",20),t.qZA()),2&o){const e=t.oxw(2).$implicit;t.xp6(1),t.Q6J("ngIf","creator"==e.property),t.xp6(1),t.Q6J("ngIf","creator"!=e.property)}}function $(o,s){if(1&o&&(t.ynx(0,15),t.YNc(1,F,2,1,"th",16),t.YNc(2,U,3,2,"td",17),t.BQk()),2&o){const e=t.oxw().$implicit;t.Q6J("matColumnDef",e.property)}}function G(o,s){if(1&o&&(t.ynx(0),t.YNc(1,$,3,1,"ng-container",14),t.BQk()),2&o){const e=s.$implicit;t.xp6(1),t.Q6J("ngIf",e.isModelProperty)}}function H(o,s){1&o&&t._UZ(0,"tr",21)}function B(o,s){if(1&o){const e=t.EpF();t.TgZ(0,"tr",22),t.NdJ("click",function(){const l=t.CHM(e).$implicit;return t.oxw().updateCustomer(l)}),t.qZA()}}let L=(()=>{class o{constructor(e,n,a){this.data=e,this.dialogRef=n,this.customersService=a,this.message="",this.isLoading=!1,this.cancelButtonText="Cancel",this.columns=[{name:"Creator Name/Email",property:"creator",visible:!0,isModelProperty:!0},{name:"Country",property:"country",visible:!0,isModelProperty:!0},{name:"City",property:"city",visible:!0,isModelProperty:!0},{name:"State",property:"state",visible:!0,isModelProperty:!0},{name:"Street",property:"street",visible:!0,isModelProperty:!0},{name:"Zip",property:"zip",visible:!0,isModelProperty:!0},{name:"Created At",property:"created_at",visible:!0,isModelProperty:!0}],e&&this.getData(e.id),this.dialogRef.updateSize("300vw","300vw")}get visibleColumns(){return this.columns.filter(e=>e.visible).map(e=>e.property)}getData(e){return(0,p.mG)(this,void 0,void 0,function*(){yield this.customersService.getCustomerDetail(e).then(n=>{this.details=n.data,this.address_data=n.address_data,this.dataSource.data=n.address_data,console.log(this.details),console.log(this.address_data)})})}onFilterChange(e){!this.dataSource||(e=(e=e.trim()).toLowerCase(),this.dataSource.filter=e)}ngOnInit(){this.dataSource=new r.by}}return o.\u0275fac=function(e){return new(e||o)(t.Y36(u.WI),t.Y36(u.so),t.Y36(x))},o.\u0275cmp=t.Xpm({type:o,selectors:[["fury-customer-detail"]],inputs:{columns:"columns"},features:[t._Bn([x])],decls:11,vars:7,consts:[["mode","simple"],["name","Customers",3,"columns","filterChange"],["mode","indeterminate",4,"ngIf"],["mat-table","","matSort","",3,"dataSource"],["matColumnDef","checkbox"],["class","actions-cell","mat-header-cell","",4,"matHeaderCellDef"],["class","actions-cell","mat-cell","",4,"matCellDef"],[4,"ngFor","ngForOf"],["mat-header-row","",4,"matHeaderRowDef"],["class","clickable route-animations-elements","mat-row","",3,"click",4,"matRowDef","matRowDefColumns"],["mode","indeterminate"],["mat-header-cell","",1,"actions-cell"],["color","primary",3,"click"],["mat-cell","",1,"actions-cell"],[3,"matColumnDef",4,"ngIf"],[3,"matColumnDef"],["mat-header-cell","","mat-sort-header","",4,"matHeaderCellDef"],["mat-cell","",4,"matCellDef"],["mat-header-cell","","mat-sort-header",""],["mat-cell",""],[3,"ngIf"],["mat-header-row",""],["mat-row","",1,"clickable","route-animations-elements",3,"click"]],template:function(e,n){1&e&&(t.TgZ(0,"fury-page-layout",0),t.TgZ(1,"fury-page-layout-content"),t.TgZ(2,"fury-list",1),t.NdJ("filterChange",function(l){return n.onFilterChange(l)}),t.YNc(3,Y,1,0,"mat-progress-bar",2),t.TgZ(4,"table",3),t.ynx(5,4),t.YNc(6,k,2,0,"th",5),t.YNc(7,P,2,0,"td",6),t.BQk(),t.YNc(8,G,2,1,"ng-container",7),t.YNc(9,H,1,0,"tr",8),t.YNc(10,B,1,0,"tr",9),t.qZA(),t.qZA(),t.qZA(),t.qZA()),2&e&&(t.xp6(1),t.Q6J("@fadeInUp",void 0),t.xp6(1),t.Q6J("columns",n.columns),t.xp6(1),t.Q6J("ngIf",n.isLoading),t.xp6(1),t.Q6J("dataSource",n.dataSource),t.xp6(4),t.Q6J("ngForOf",n.columns),t.xp6(1),t.Q6J("matHeaderRowDef",n.visibleColumns),t.xp6(1),t.Q6J("matRowDefColumns",n.visibleColumns))},directives:[v.N,T.d,D.n,c.O5,r.BZ,m.YE,r.w1,r.fO,r.Dz,c.sg,r.as,r.nj,g.pW,r.ge,Z.oG,r.ev,m.nU,r.XQ,r.Gk],styles:[""]}),o})();var E=i(5351),j=i(7182),W=i(2109),X=i(4236),V=i(9716),A=i(515),S=i(86),f=i(3530),b=i(9112);function K(o,s){if(1&o){const e=t.EpF();t.TgZ(0,"div"),t.TgZ(1,"button",16),t.NdJ("click",function(){return t.CHM(e),t.oxw().deleteRecord()}),t._uU(2,"Delete Checked Record"),t.qZA(),t.qZA()}}function q(o,s){1&o&&t._UZ(0,"mat-progress-bar",17)}function tt(o,s){if(1&o){const e=t.EpF();t.TgZ(0,"th",18),t.TgZ(1,"mat-checkbox",19),t.NdJ("change",function(a){t.CHM(e);const l=t.oxw();return a?l.masterToggle(a):null}),t.qZA(),t.qZA()}if(2&o){const e=t.oxw();t.xp6(1),t.Q6J("checked",e.selection.hasValue()&&e.isAllSelected())("indeterminate",e.selection.hasValue()&&!e.isAllSelected())}}function et(o,s){if(1&o){const e=t.EpF();t.TgZ(0,"td",20),t.TgZ(1,"mat-checkbox",21),t.NdJ("click",function(a){return a.stopPropagation()})("change",function(a){const d=t.CHM(e).$implicit,Tt=t.oxw();return a?Tt.selectToggle(a,d.id):null}),t.qZA(),t.qZA()}if(2&o){const e=s.$implicit,n=t.oxw();t.xp6(1),t.Q6J("checked",n.selection.isSelected(e))}}function ot(o,s){if(1&o&&(t.TgZ(0,"th",26),t._uU(1),t.qZA()),2&o){const e=t.oxw(2).$implicit;t.xp6(1),t.hij(" ",e.name,"")}}function nt(o,s){if(1&o&&(t.TgZ(0,"td",27),t._uU(1),t.qZA()),2&o){const e=s.$implicit,n=t.oxw(2).$implicit;t.xp6(1),t.hij(" ",e[n.property]," ")}}function it(o,s){if(1&o&&(t.ynx(0,23),t.YNc(1,ot,2,1,"th",24),t.YNc(2,nt,2,1,"td",25),t.BQk()),2&o){const e=t.oxw().$implicit;t.Q6J("matColumnDef",e.property)}}function st(o,s){if(1&o&&(t.ynx(0),t.YNc(1,it,3,1,"ng-container",22),t.BQk()),2&o){const e=s.$implicit;t.xp6(1),t.Q6J("ngIf",e.isModelProperty)}}function at(o,s){1&o&&t._UZ(0,"th",28)}function rt(o,s){if(1&o){const e=t.EpF();t.TgZ(0,"td",20),t.TgZ(1,"button",29),t.NdJ("click",function(a){return a.stopPropagation()}),t.TgZ(2,"mat-icon"),t._uU(3,"more_horiz"),t.qZA(),t.qZA(),t.TgZ(4,"mat-menu",30,31),t.TgZ(6,"button",32),t.NdJ("click",function(){const l=t.CHM(e).$implicit;return t.oxw().openDialog(l.id)}),t.TgZ(7,"span"),t._uU(8,"View Details"),t.qZA(),t.qZA(),t.TgZ(9,"button",32),t.NdJ("click",function(){const l=t.CHM(e).$implicit;return t.oxw().handleDeleteAction(l.id)}),t.TgZ(10,"span"),t._uU(11,"Delete"),t.qZA(),t.qZA(),t.qZA(),t.qZA()}if(2&o){const e=t.MAs(5);t.xp6(1),t.Q6J("matMenuTriggerFor",e)}}function lt(o,s){1&o&&t._UZ(0,"tr",33)}function ct(o,s){1&o&&t._UZ(0,"tr",34)}const mt=[{path:"",component:(()=>{class o{constructor(e,n){this.dialog=e,this.customersService=n,this.isLoading=!1,this.totalRows=0,this.pageSize=25,this.currentPage=1,this.pageSizeOptions=[5,10,25,100],this.filters={},this.address=[],this.search="",this.notyf=new j.Iq,this.idArray=[],this.allIdArray=[],this.isChecked=!1,this.columns=[{name:"Checkbox",property:"checkbox",visible:!0},{name:"Customer Id",property:"id",visible:!0,isModelProperty:!0},{name:"Email",property:"email",visible:!0,isModelProperty:!0},{name:"First Name",property:"first_name",visible:!0,isModelProperty:!0},{name:"Last Name",property:"last_name",visible:!0,isModelProperty:!0},{name:"Phone",property:"phone",visible:!0,isModelProperty:!0},{name:"Actions",property:"actions",visible:!0}],this.selection=new E.Ov(!0,[])}get visibleColumns(){return this.columns.filter(e=>e.visible).map(e=>e.property)}ngOnInit(){this.getSubscription=this.customersService.customersGetResponse$.subscribe(e=>this.manageGetResponse(e)),this.deleteSubscription=this.customersService.deleteResponse$.subscribe(e=>this.manageDeleteResponse(e)),this.getData(),this.dataSource=new r.by}ngAfterViewInit(){this.dataSource.paginator=this.paginator,this.dataSource.sort=this.sort}pageChanged(e){this.pageSize=e.pageSize,this.currentPage=e.pageIndex,this.getData()}getData(){return(0,p.mG)(this,void 0,void 0,function*(){this.isLoading=!0,this.isChecked=!1,this.filters={currentPage:this.currentPage,pageSize:this.pageSize,search:this.search},yield this.customersService.getCustomers(this.filters).then(e=>{this.allIdArray=[],this.customers=e.data.data,this.dataSource.data=e.data.data;for(var n=0;n<e.data.data.length;n++)this.allIdArray.push(e.data.data[n].id);setTimeout(()=>{this.paginator.pageIndex=this.currentPage,this.paginator.length=e.pag.count}),this.isLoading=!1},e=>{this.isLoading=!1})})}onFilterChange(e){e=e.toLowerCase(),this.search=e,clearTimeout(this.timer),this.timer=setTimeout(()=>{this.getData()},500)}manageGetResponse(e){e.status?(this.customers=e.data.data,this.dataSource.data=e.data.data,setTimeout(()=>{this.paginator.pageIndex=this.currentPage,this.paginator.length=e.pag.count}),this.isLoading=!1):this.isLoading=!1}manageDeleteResponse(e){e.status&&(this.notyf.success(e.message),this.getData())}openDialog(e){this.dialog.open(L,{data:{id:e}}).afterClosed().subscribe(a=>{})}isAllSelected(){return this.selection.selected.length===this.dataSource.data.length}masterToggle(e){this.isAllSelected()?this.selection.clear():this.dataSource.data.forEach(n=>this.selection.select(n)),0==e.checked?(this.idArray=[],this.idArray.length=0):this.idArray=this.allIdArray,this.isChecked=0!=this.idArray.length}selectToggle(e,n){e.checked?this.idArray.push(n):this.idArray.splice(this.idArray.indexOf(n),1),this.isChecked=0!=this.idArray.length}deleteRecord(){this.handleDeleteAction(this.idArray)}handleDeleteAction(e){const n=new X.f("Confirm Delete","Are you sure you want to delete this customer?");this.dialog.open(W.z,{maxWidth:"500px",closeOnNavigation:!0,data:n}).afterClosed().subscribe(l=>{l&&(this.customersService.deleteData(e),this.dataSource.data=[],this.idArray=[])})}ngOnDestroy(){this.deleteSubscription&&(this.customersService.deleteResponse.next([]),this.deleteSubscription.unsubscribe())}}return o.\u0275fac=function(e){return new(e||o)(t.Y36(u.uw),t.Y36(V.v))},o.\u0275cmp=t.Xpm({type:o,selectors:[["fury-customers"]],viewQuery:function(e,n){if(1&e&&(t.Gf(h.NW,7),t.Gf(m.YE,7)),2&e){let a;t.iGM(a=t.CRH())&&(n.paginator=a.first),t.iGM(a=t.CRH())&&(n.sort=a.first)}},inputs:{columns:"columns"},decls:18,vars:12,consts:[["fxLayout","","fxLayoutAlign","space-between center"],[4,"ngIf"],["mode","simple"],["name","Customers",3,"columns","filterChange"],["mode","indeterminate",4,"ngIf"],["mat-table","","matSort","",3,"dataSource"],["matColumnDef","checkbox"],["class","actions-cell","mat-header-cell","",4,"matHeaderCellDef"],["class","actions-cell","mat-cell","",4,"matCellDef"],[4,"ngFor","ngForOf"],["matColumnDef","actions"],["class","actions-cell","mat-header-cell","","mat-sort-header","",4,"matHeaderCellDef"],["mat-header-row","",4,"matHeaderRowDef"],["class","clickable route-animations-elements","mat-row","",4,"matRowDef","matRowDefColumns"],["aria-label","Select page",3,"length","pageIndex","pageSize","pageSizeOptions","page"],["paginator",""],["mat-raised-button","","color","warn",1,"ml-1",3,"click"],["mode","indeterminate"],["mat-header-cell","",1,"actions-cell"],[3,"checked","indeterminate","change"],["mat-cell","",1,"actions-cell"],["color","primary",3,"checked","click","change"],[3,"matColumnDef",4,"ngIf"],[3,"matColumnDef"],["mat-header-cell","","mat-sort-header","",4,"matHeaderCellDef"],["mat-cell","",4,"matCellDef"],["mat-header-cell","","mat-sort-header",""],["mat-cell",""],["mat-header-cell","","mat-sort-header","",1,"actions-cell"],["type","button","mat-icon-button","",3,"matMenuTriggerFor","click"],["yPosition","below","xPosition","before"],["actionsMenu","matMenu"],["mat-menu-item","",3,"click"],["mat-header-row",""],["mat-row","",1,"clickable","route-animations-elements"]],template:function(e,n){1&e&&(t.TgZ(0,"fury-page-layout-content",0),t.YNc(1,K,3,0,"div",1),t.qZA(),t.TgZ(2,"fury-page-layout",2),t.TgZ(3,"fury-page-layout-content"),t.TgZ(4,"fury-list",3),t.NdJ("filterChange",function(l){return n.onFilterChange(l)}),t.YNc(5,q,1,0,"mat-progress-bar",4),t.TgZ(6,"table",5),t.ynx(7,6),t.YNc(8,tt,2,2,"th",7),t.YNc(9,et,2,1,"td",8),t.BQk(),t.YNc(10,st,2,1,"ng-container",9),t.ynx(11,10),t.YNc(12,at,1,0,"th",11),t.YNc(13,rt,12,1,"td",8),t.BQk(),t.YNc(14,lt,1,0,"tr",12),t.YNc(15,ct,1,0,"tr",13),t.qZA(),t.TgZ(16,"mat-paginator",14,15),t.NdJ("page",function(l){return n.pageChanged(l)}),t.qZA(),t.qZA(),t.qZA(),t.qZA()),2&e&&(t.xp6(1),t.Q6J("ngIf",n.isChecked),t.xp6(2),t.Q6J("@fadeInUp",void 0),t.xp6(1),t.Q6J("columns",n.columns),t.xp6(1),t.Q6J("ngIf",n.isLoading),t.xp6(1),t.Q6J("dataSource",n.dataSource),t.xp6(4),t.Q6J("ngForOf",n.columns),t.xp6(4),t.Q6J("matHeaderRowDef",n.visibleColumns),t.xp6(1),t.Q6J("matRowDefColumns",n.visibleColumns),t.xp6(1),t.Q6J("length",n.totalRows)("pageIndex",n.currentPage)("pageSize",n.pageSize)("pageSizeOptions",n.pageSizeOptions))},directives:[T.d,A.xw,A.Wh,c.O5,v.N,D.n,r.BZ,m.YE,r.w1,r.fO,r.Dz,c.sg,r.as,r.nj,h.NW,S.lW,g.pW,r.ge,Z.oG,r.ev,m.nU,f.p6,b.Hw,f.VK,f.OP,r.XQ,r.Gk],styles:[""],data:{animation:[Q.M,M.X]}}),o})()}];let ut=(()=>{class o{}return o.\u0275fac=function(e){return new(e||o)},o.\u0275mod=t.oAB({type:o}),o.\u0275inj=t.cJS({imports:[[y.Bz.forChild(mt)],y.Bz]}),o})();var dt=i(9198),pt=i(6153),ht=i(138),gt=i(9859),ft=i(6400),Ct=i(8727),_t=i(6731),yt=i(3050),xt=i(8898);let vt=(()=>{class o{}return o.\u0275fac=function(e){return new(e||o)},o.\u0275mod=t.oAB({type:o}),o.\u0275inj=t.cJS({imports:[[c.ez,ut,_.u5,_.UX,R.q,dt.Z,pt.o9,u.Is,ht.c,S.ot,b.Ps,gt.Fk,ft.LD,Ct.FA,_t.XK,yt.To,xt.IJ,J.p,N.J,r.p0,h.TU,g.Cv]]}),o})()}}]);