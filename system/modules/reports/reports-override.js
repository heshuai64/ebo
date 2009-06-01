
Ext.override(QoDesk.Reports, {
    createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('reports-win');
        
	var generateSkuSellReport = function(seller_id, start_date, end_date){
	    window.open("/eBayBO/reports.php?type=skuSell&seller_id="+seller_id+"&start_date="+start_date+"&end_date="+end_date,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=10, height=10");
	}
	
	var skuSellReport = function(){
	    var skuSellReportWin = desktop.getWindow('sku-sell-win');
	    if(!skuSellReportWin){
		skuSellReportWin = desktop.createWindow({
		    id: 'sku-sell-win',
		    title:lang.reports.search_sku_sell_window_title,
		    width:300,
		    height:200,
		    iconCls: 'sku-sell-icon',
		    shim:false,
		    animCollapse:false,
		    constrainHeader:true,
		    layout: 'fit',
		    //html:'test',
		    items:[{
			xtype:"form",
			labelWidth:80,
			items:[{
			    //id:'sellerId',
			    xtype: 'combo',
			    fieldLabel:lang.reports.seller_id,
			    mode: 'local',
			    store: new Ext.data.JsonStore({
				autoLoad: true,
				fields: ['id', 'name'],
				url: "connect.php?moduleId=qo-transactions&action=getSeller"
			    }),
			    valueField:'id',
			    displayField:'name',
			    triggerAction: 'all',
			    editable: false,
			    selectOnFocus:true,
			    width:160,
			    listWidth:160,
			    name: 'sellerId',
			    hiddenName:'sellerId'
			},{
			    id:'start_date',
			    xtype:"datefield",
			    fieldLabel:lang.reports.start_date,
			    format:'Y-m-d',
			    name:"start_date"
			},{
			    id:'end_date',
			    xtype:"datefield",
			    fieldLabel:lang.reports.end_date,
			    format:'Y-m-d',
			    name:"end_date"
			}],
			buttons: [{
			    text: lang.reports.submit,
			    handler: function(){
				generateSkuSellReport(document.getElementById("sellerId").value, document.getElementById("start_date").value,document.getElementById("end_date").value);
				skuSellReportWin.close();
			    }
			},{
			    text: lang.reports.close,
			    handler: function(){
				skuSellReportWin.close();
			    }
			}]
		    }],
		    taskbuttonTooltip: '<b>Statistical Reports</b><br>Sku sales statistical reports'
		});
		
	    }
	    
	    skuSellReportWin.show();
	}
	
	var salesReport = function(){
	    
	    var salesReportSearchWin = desktop.getWindow('sales-report-win');
	    if(!salesReportSearchWin){
		salesReportSearchWin = desktop.createWindow({
		    id: 'sales-report-win',
		    title:lang.reports.search_sales_report_window_title,
		    width:300,
		    height:100,
		    iconCls: 'sales-report-icon',
		    shim:false,
		    animCollapse:false,
		    constrainHeader:true,
		    layout: 'fit',
		    //html:'test',
		    items:[{
			xtype:"form",
			labelWidth:80,
			items:[{
			    //id:'sellerId',
			    xtype: 'combo',
			    fieldLabel:lang.reports.seller_id,
			    mode: 'local',
			    store: new Ext.data.JsonStore({
				autoLoad: true,
				fields: ['id', 'name'],
				url: "connect.php?moduleId=qo-transactions&action=getSeller"
			    }),
			    valueField:'id',
			    displayField:'name',
			    triggerAction: 'all',
			    editable: false,
			    selectOnFocus:true,
			    width:160,
			    listWidth:160,
			    name: 'sellerId',
			    hiddenName:'sellerId'
			}],
			buttons: [{
			    text: lang.reports.submit,
			    handler: function(){
				salesReportSearchWin.close();
				window.open("/eBayBO/salesReport.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=600, height=400");
			    }
			},{
			    text: lang.reports.close,
			    handler: function(){
				salesReportSearchWin.close();
			    }
			}]
		    }],
		    taskbuttonTooltip: '<b>Statistical Reports</b><br>Sales statistical reports'
		});
		
	    }
	    
	    salesReportSearchWin.show();
	}
	
	if(!win){
            win = desktop.createWindow({
                id: 'reports-win',
                title:lang.reports.window_title,
                width:340,
                height:200,
                iconCls:'reports-icon',
                shim:false,
                animCollapse:false,
                constrainHeader:true,
		layout: 'fit',
		html: '<div class="manage-button"><div class="sku-sell"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">'+lang.reports.sell_sell+'</div></div>\
		       <div class="manage-button"><div class="sales-report"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">'+lang.reports.sales_report+'</div></div>',
                taskbuttonTooltip: '<b>Statistical Reports</b><br>A variety of statistical reports'
            });
	    Ext.EventManager.on(Ext.DomQuery.select("div[@class='sku-sell']")[0], "click", skuSellReport);
	    Ext.EventManager.on(Ext.DomQuery.select("div[@class='sales-report']")[0], "click", salesReport);
        }
        
        win.show();
    }
})