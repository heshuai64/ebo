Ext.override(QoDesk.Reports,{createWindow:function(){var e=this.app.getDesktop();var d=e.getWindow("reports-win");var a=function(h,f,g){window.open("/eBayBO/reports.php?type=skuSell&seller_id="+h+"&start_date="+f+"&end_date="+g,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=10, height=10")};var c=function(){var f=e.getWindow("sku-sell-win");if(!f){f=e.createWindow({id:"sku-sell-win",title:lang.reports.search_sku_sell_window_title,width:300,height:200,iconCls:"sku-sell-icon",shim:false,animCollapse:false,constrainHeader:true,layout:"fit",items:[{xtype:"form",labelWidth:80,items:[{xtype:"combo",fieldLabel:lang.reports.seller_id,mode:"local",store:new Ext.data.JsonStore({autoLoad:true,fields:["id","name"],url:"connect.php?moduleId=qo-reports&action=getSeller"}),valueField:"id",displayField:"name",triggerAction:"all",editable:false,selectOnFocus:true,width:160,listWidth:160,name:"sellerId",hiddenName:"sellerId"},{id:"start_date",xtype:"datefield",fieldLabel:lang.reports.start_date,format:"Y-m-d",name:"start_date"},{id:"end_date",xtype:"datefield",fieldLabel:lang.reports.end_date,format:"Y-m-d",name:"end_date"}],buttons:[{text:lang.reports.submit,handler:function(){a(document.getElementById("sellerId").value,document.getElementById("start_date").value,document.getElementById("end_date").value);f.close()}},{text:lang.reports.close,handler:function(){f.close()}}]}],taskbuttonTooltip:"<b>Statistical Reports</b><br>Sku sales statistical reports"})}f.show()};var b=function(){var f=e.getWindow("sales-report-win");if(!f){f=e.createWindow({id:"sales-report-win",title:lang.reports.search_sales_report_window_title,width:500,height:300,iconCls:"sales-report-icon",shim:false,animCollapse:false,constrainHeader:true,layout:"fit",items:[{xtype:"form",labelWidth:80,items:[{xtype:"combo",fieldLabel:lang.reports.seller_id,mode:"local",store:new Ext.data.JsonStore({autoLoad:true,fields:["id","name"],url:"connect.php?moduleId=qo-reports&action=getSeller"}),valueField:"id",displayField:"name",triggerAction:"all",editable:false,selectOnFocus:true,width:160,listWidth:160,name:"sellerId",hiddenName:"sellerId"},{id:"skus",xtype:"textarea",fieldLabel:"SKUS",width:300,height:180}],buttons:[{text:lang.reports.submit,handler:function(){var h=document.getElementById("sellerId").value;var j=Ext.getCmp("skus").getValue();var k=j.split("\n");var l="";for(var g=0;g<k.length;g++){l+=k[g]+","}l=l.substring(0,l.length-1);for(var g=1;g<5;g++){setTimeout('window.open("/eBayBO/salesReport.php?week='+g+"&sellerId="+h+"&skus="+l+'", "_blank", "toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=700")',g*5000)}f.close()}},{text:lang.reports.close,handler:function(){f.close()}}]}],taskbuttonTooltip:"<b>Statistical Reports</b><br>Sales statistical reports"})}f.show()};if(!d){d=e.createWindow({id:"reports-win",title:lang.reports.window_title,width:340,height:200,iconCls:"reports-icon",shim:false,animCollapse:false,constrainHeader:true,layout:"fit",html:'<div class="manage-button"><div class="sku-sell"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">'+lang.reports.sell_sell+'</div></div>		       <div class="manage-button"><div class="sales-report"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">'+lang.reports.sales_report+"</div></div>",taskbuttonTooltip:"<b>Statistical Reports</b><br>A variety of statistical reports"});Ext.EventManager.on(Ext.DomQuery.select("div[@class='sku-sell']")[0],"click",c);Ext.EventManager.on(Ext.DomQuery.select("div[@class='sales-report']")[0],"click",b)}d.show()}});