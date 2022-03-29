"use strict";(self.webpackChunkfury=self.webpackChunkfury||[]).push([[244],{2244:(ct,u,n)=>{n.r(u),n.d(u,{ProspectsModule:()=>rt});var c=n(6019),p=n(9133),T=n(6113),x=n(4625),S=n(7615),f=n(4382),h=n(4762),d=n(2968),g=n(2262),r=n(240),Z=n(2919),b=n(2411),A=n(8735);class R{constructor(i){this.prospect_id=i.prospect_id,this.first_name=i.first_name,this.last_name=i.last_name,this.address=i.address,this.address2=i.address2,this.city=i.city,this.state=i.state,this.state_id=i.state_id,this.zip=i.zip,this.country=i.country,this.phone=i.phone,this.email=i.email,this.ip_address=i.ip_address,this.month_created=i.month_created,this.year_created=i.year_created,this.date_created=i.date_created,this.risk_flag=i.risk_flag,this.affiliate=i.affiliate,this.sub_affiliate=i.sub_affiliate}}var w=n(1168),N=n(9190),z=n(8260),J=n(7182),t=n(3668),y=n(5304),v=n(4099),P=n(238);let O=(()=>{class a{constructor(e){this.apiService=e,this.GetProspectsResponse=new v.X([]),this.deleteResponse=new v.X([]),this.getProspectResponse$=this.GetProspectsResponse.asObservable(),this.deleteResponse$=this.deleteResponse.asObservable()}getProspects(e){return(0,h.mG)(this,void 0,void 0,function*(){return yield this.apiService.getData(`prospects?page=${e.currentPage}&per_page=${e.pageSize}&search=${e.search}`).then(s=>s.json()).then(s=>{this.prospects=s,this.GetProspectsResponse.next(s)}),this.prospects})}deleteProposal(e){return(0,h.mG)(this,void 0,void 0,function*(){yield this.apiService.deleteData(`prospects/${e}`).then(s=>s.json()).then(s=>{this.deleteResponse.next(s)})})}}return a.\u0275fac=function(e){return new(e||a)(t.LFG(P.s))},a.\u0275prov=t.Yz7({token:a,factory:a.\u0275fac,providedIn:"root"}),a})();var I=n(1136),$=n(7635),F=n(4643),C=n(9009),Q=n(904),D=n(86),m=n(3530),M=n(9112);function V(a,i){1&a&&t._UZ(0,"mat-progress-bar",14)}function Y(a,i){1&a&&(t.TgZ(0,"th",15),t.TgZ(1,"mat-checkbox",16),t.NdJ("click",function(s){return s.stopPropagation()}),t.qZA(),t.qZA())}function G(a,i){1&a&&(t.TgZ(0,"td",17),t.TgZ(1,"mat-checkbox",16),t.NdJ("click",function(s){return s.stopPropagation()}),t.qZA(),t.qZA())}function j(a,i){if(1&a&&(t.TgZ(0,"th",22),t._uU(1),t.qZA()),2&a){const e=t.oxw(2).$implicit;t.xp6(1),t.hij(" ",e.name,"")}}function U(a,i){if(1&a&&(t.TgZ(0,"td",23),t._uU(1),t.qZA()),2&a){const e=i.$implicit,s=t.oxw(2).$implicit;t.xp6(1),t.hij(" ",e[s.property]," ")}}function B(a,i){if(1&a&&(t.ynx(0,19),t.YNc(1,j,2,1,"th",20),t.YNc(2,U,2,1,"td",21),t.BQk()),2&a){const e=t.oxw().$implicit;t.Q6J("matColumnDef",e.property)}}function L(a,i){if(1&a&&(t.ynx(0),t.YNc(1,B,3,1,"ng-container",18),t.BQk()),2&a){const e=i.$implicit;t.xp6(1),t.Q6J("ngIf",e.isModelProperty)}}function H(a,i){1&a&&t._UZ(0,"th",24)}function X(a,i){if(1&a){const e=t.EpF();t.TgZ(0,"td",17),t.TgZ(1,"button",25),t.NdJ("click",function(o){return o.stopPropagation()}),t.TgZ(2,"mat-icon"),t._uU(3,"more_horiz"),t.qZA(),t.qZA(),t.TgZ(4,"mat-menu",26,27),t.TgZ(6,"button",28),t.NdJ("click",function(){const l=t.CHM(e).$implicit;return t.oxw().deleteProspect(l.prospect_id)}),t.TgZ(7,"span"),t.TgZ(8,"mat-icon"),t._uU(9,"delete"),t.qZA(),t._uU(10,"Delete Prospect"),t.qZA(),t.qZA(),t.qZA(),t.qZA()}if(2&a){const e=t.MAs(5);t.xp6(1),t.Q6J("matMenuTriggerFor",e)}}function _(a,i){1&a&&t._UZ(0,"tr",29)}function E(a,i){if(1&a){const e=t.EpF();t.TgZ(0,"tr",30),t.NdJ("click",function(){const l=t.CHM(e).$implicit;return t.oxw().openDialog(l.id)}),t.qZA()}}const W=[{path:"",component:(()=>{class a{constructor(e,s,o){this.dialog=e,this.prospectsService=s,this.apiService=o,this.subject$=new Z.t(1),this.data$=this.subject$.asObservable(),this.isLoading=!1,this.totalRows=0,this.pageSize=25,this.currentPage=0,this.all_fields=[],this.all_values=[],this.filterData=[],this.filters={},this.endPoint="",this.range=new p.cw({start:new p.NI,end:new p.NI}),this.notyf=new J.Iq({types:[{type:"info",background:"#6495ED",icon:'<i class="fa-solid fa-clock"></i>'}]}),this.selected="transactionCreatedDate",this.campaign="allCampaigns",this.campaignCategory="allCategories",this.product="allProducts",this.productCategory="allCategories",this.campaignProduct="allCampaignProducts",this.affiliate="allAffiliates",this.callCenter="allCallCenters",this.billType="allBillings",this.billingCycle="all",this.recycleNo="all",this.txnType="all",this.currency="allCurrencies",this.country="allCountries",this.state="allStates",this.gateway="all",this.ccType="all",this.is_3d_protected="all",this.gatewayCategory="allGatewayCategories",this.gatewayType="all",this.creditOrDebit="all",this.search="",this.cardOptions=["visa","master"],this.pageSizeOptions=[5,10,25,100],this.columns=[{name:"Actions",property:"actions",visible:!0},{name:"Id",property:"prospect_id",isModelProperty:!0},{name:"First Name",property:"first_name",visible:!0,isModelProperty:!0},{name:"Last Name",property:"last_name",visible:!0,isModelProperty:!0},{name:"Email",property:"email",visible:!0,isModelProperty:!0},{name:"Address",property:"address",visible:!0,isModelProperty:!0},{name:"City",property:"city",visible:!0,isModelProperty:!0},{name:"State Id",property:"state_id",visible:!0,isModelProperty:!0},{name:"Zip",property:"zip",visible:!0,isModelProperty:!0},{name:"Country",property:"country",visible:!0,isModelProperty:!0},{name:"Ip Address",property:"ip_address",visible:!0,isModelProperty:!0},{name:"Risk Flag",property:"risk_flag",visible:!0,isModelProperty:!0},{name:"Affiliate",property:"affiliate",visible:!0,isModelProperty:!0},{name:"Sub Affiliate",property:"sub_affiliate",visible:!0,isModelProperty:!0}],this.endPoint=z.N.endpoint}get visibleColumns(){return this.columns.filter(e=>e.visible).map(e=>e.property)}ngOnInit(){this.deleteSubscription=this.prospectsService.deleteResponse$.subscribe(e=>this.manageDeleteResponse(e)),this.getData(),this.dataSource=new r.by,this.data$.pipe((0,A.h)(e=>!!e)).subscribe(e=>{this.prospects=e,this.dataSource.data=e})}mapData(){return(0,b.of)(this.prospects.map(e=>new R(e)))}ngAfterViewInit(){this.dataSource.paginator=this.paginator,this.dataSource.sort=this.sort}pageChanged(e){this.pageSize=e.pageSize,this.currentPage=e.pageIndex,console.log(this.pageSize),console.log(this.currentPage),this.getData()}getData(){this.isLoading=!0,this.filters={currentPage:this.currentPage,pageSize:this.pageSize,start:(0,c.p6)(this.range.get("start").value,"yyyy/MM/dd","en"),end:(0,c.p6)(this.range.get("end").value,"yyyy/MM/dd","en"),all_fields:this.all_fields,all_values:this.all_values,search:this.search},this.prospectsService.getProspects(this.filters).then(e=>{console.log("paginate data is: ",e.data.data),this.prospects=e.data.data,setTimeout(()=>{this.paginator.pageIndex=this.currentPage,this.paginator.length=e.pag.count}),this.mapData().subscribe(s=>{this.subject$.next(s)}),this.isLoading=!1},e=>{this.isLoading=!1})}getDropData(){return(0,h.mG)(this,void 0,void 0,function*(){fetch(`${this.endPoint}/api/getDropDownContent`).then(s=>s.json()).then(s=>{this.filterData=s,console.log("Drop Data is: ",this.filterData)})})}commonFilter(e,s){if(-1===this.all_fields.indexOf(s))this.all_fields.push(s),this.all_values.push(e);else{let o=this.all_fields.indexOf(s);this.all_values[o]=e}}manageGetResponse(e){e.status?(this.prospects=e.data.data,this.dataSource.data=e.data.data,setTimeout(()=>{this.paginator.pageIndex=this.currentPage,this.paginator.length=e.pag.count}),this.isLoading=!1):this.isLoading=!1}manageDeleteResponse(e){e.status&&(this.notyf.success(e.message),this.getData())}manageCampaignsResponse(e){e.status&&(this.campaignOptions=e.data),console.log("campaign data",this.campaignOptions)}manageProductsResponse(e){e.status&&(this.productOptions=e.data),console.log("campaign data",this.productOptions)}onFilterChange(e){e=e.toLowerCase(),this.search=e,clearTimeout(this.timer),this.timer=setTimeout(()=>{this.getData()},500)}selectDate(e){var s=new Date,o=new Date;"today"==e?(this.range.get("start").setValue(new Date),this.range.get("end").setValue(new Date)):"yesterday"==e?(this.range.get("start").setValue(new Date(s.setDate(s.getDate()-1))),this.range.get("end").setValue(new Date)):"thisMonth"==e?(this.range.get("start").setValue(new Date(s.setMonth(s.getMonth()-1))),this.range.get("end").setValue(new Date)):"pastWeek"==e?(this.range.get("start").setValue(new Date(s.setDate(s.getDate()-7))),this.range.get("end").setValue(new Date)):"pastTwoWeek"==e?(this.range.get("start").setValue(new Date(s.setDate(s.getDate()-14))),this.range.get("end").setValue(new Date)):"lastMonth"==e?(this.range.get("start").setValue(new Date(s.setMonth(s.getMonth()-2))),this.range.get("end").setValue(new Date(o.setMonth(o.getMonth()-1)))):"lastThreeMonths"==e?(this.range.get("start").setValue(new Date(s.setMonth(s.getMonth()-4))),this.range.get("end").setValue(new Date(o.setMonth(o.getMonth()-1)))):"lastSixMonths"==e&&(this.range.get("start").setValue(new Date(s.setMonth(s.getMonth()-7))),this.range.get("end").setValue(new Date(o.setMonth(o.getMonth()-1))))}deleteProspect(e){this.prospectsService.deleteProposal(e)}ngOnDestroy(){this.deleteSubscription&&(this.prospectsService.deleteResponse.next([]),this.deleteSubscription.unsubscribe())}}return a.\u0275fac=function(e){return new(e||a)(t.Y36(y.uw),t.Y36(O),t.Y36(P.s))},a.\u0275cmp=t.Xpm({type:a,selectors:[["fury-prospects"]],viewQuery:function(e,s){if(1&e&&(t.Gf(d.NW,7),t.Gf(g.YE,7)),2&e){let o;t.iGM(o=t.CRH())&&(s.paginator=o.first),t.iGM(o=t.CRH())&&(s.sort=o.first)}},inputs:{columns:"columns"},decls:16,vars:11,consts:[["mode","simple"],["name","Prospects",3,"columns","filterChange"],["mode","indeterminate",4,"ngIf"],["mat-table","","matSort","",3,"dataSource"],["matColumnDef","checkbox"],["class","actions-cell","mat-header-cell","",4,"matHeaderCellDef"],["class","actions-cell","mat-cell","",4,"matCellDef"],[4,"ngFor","ngForOf"],["matColumnDef","actions"],["class","actions-cell","mat-header-cell","","mat-sort-header","",4,"matHeaderCellDef"],["mat-header-row","",4,"matHeaderRowDef"],["class","clickable route-animations-elements","mat-row","",3,"click",4,"matRowDef","matRowDefColumns"],["aria-label","Select page",3,"length","pageIndex","pageSize","pageSizeOptions","page"],["paginator",""],["mode","indeterminate"],["mat-header-cell","",1,"actions-cell"],["color","primary",3,"click"],["mat-cell","",1,"actions-cell"],[3,"matColumnDef",4,"ngIf"],[3,"matColumnDef"],["mat-header-cell","","mat-sort-header","",4,"matHeaderCellDef"],["mat-cell","",4,"matCellDef"],["mat-header-cell","","mat-sort-header",""],["mat-cell",""],["mat-header-cell","","mat-sort-header","",1,"actions-cell"],["type","button","mat-icon-button","",3,"matMenuTriggerFor","click"],["yPosition","below","xPosition","before"],["actionsMenu","matMenu"],["mat-menu-item","",3,"click"],["mat-header-row",""],["mat-row","",1,"clickable","route-animations-elements",3,"click"]],template:function(e,s){1&e&&(t.TgZ(0,"fury-page-layout",0),t.TgZ(1,"fury-page-layout-content"),t.TgZ(2,"fury-list",1),t.NdJ("filterChange",function(l){return s.onFilterChange(l)}),t.YNc(3,V,1,0,"mat-progress-bar",2),t.TgZ(4,"table",3),t.ynx(5,4),t.YNc(6,Y,2,0,"th",5),t.YNc(7,G,2,0,"td",6),t.BQk(),t.YNc(8,L,2,1,"ng-container",7),t.ynx(9,8),t.YNc(10,H,1,0,"th",9),t.YNc(11,X,11,1,"td",6),t.BQk(),t.YNc(12,_,1,0,"tr",10),t.YNc(13,E,1,0,"tr",11),t.qZA(),t.TgZ(14,"mat-paginator",12,13),t.NdJ("page",function(l){return s.pageChanged(l)}),t.qZA(),t.qZA(),t.qZA(),t.qZA()),2&e&&(t.xp6(1),t.Q6J("@fadeInUp",void 0),t.xp6(1),t.Q6J("columns",s.columns),t.xp6(1),t.Q6J("ngIf",s.isLoading),t.xp6(1),t.Q6J("dataSource",s.dataSource),t.xp6(4),t.Q6J("ngForOf",s.columns),t.xp6(4),t.Q6J("matHeaderRowDef",s.visibleColumns),t.xp6(1),t.Q6J("matRowDefColumns",s.visibleColumns),t.xp6(1),t.Q6J("length",s.totalRows)("pageIndex",s.currentPage)("pageSize",s.pageSize)("pageSizeOptions",s.pageSizeOptions))},directives:[I.N,$.d,F.n,c.O5,r.BZ,g.YE,r.w1,r.fO,r.Dz,c.sg,r.as,r.nj,d.NW,C.pW,r.ge,Q.oG,r.ev,g.nU,D.lW,m.p6,M.Hw,m.VK,m.OP,r.XQ,r.Gk],styles:[""],data:{animation:[w.M,N.X]}}),a})()}];let k=(()=>{class a{}return a.\u0275fac=function(e){return new(e||a)},a.\u0275mod=t.oAB({type:a}),a.\u0275inj=t.cJS({imports:[[f.Bz.forChild(W)],f.Bz]}),a})();var K=n(9198),q=n(6153),tt=n(138),et=n(9859),st=n(6400),at=n(8727),it=n(6731),nt=n(3050),ot=n(8898);let rt=(()=>{class a{}return a.\u0275fac=function(e){return new(e||a)},a.\u0275mod=t.oAB({type:a}),a.\u0275inj=t.cJS({imports:[[c.ez,k,p.u5,p.UX,S.q,K.Z,q.o9,y.Is,tt.c,D.ot,M.Ps,et.Fk,st.LD,at.FA,it.XK,nt.To,ot.IJ,x.p,T.J,r.p0,d.TU,C.Cv]]}),a})()}}]);