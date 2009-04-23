
Ext.override(QoDesk.Attention, {
    createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('attention-win');
	
	var start_date = new Date();
	start_date.setDate(start_date.getDate()- 7);
	var end_date = new Date()
	
	var unMapTransactionGridStore = new Ext.data.JsonStore({
            root: 'records',
	    totalProperty: 'totalCount',
	    idProperty: 'id',
	    //remoteSort: true,
	    baseParams:{
		start_date: start_date,
		end_date: end_date,
		start:0,
		limit:20
	    },
	    autoLoad: true,
	    fields: ['id', 'txnId', 'transactionTime', 'status', 'amountCurrency', 'amountValue','payerId'],
	    url:'connect.php?moduleId=qo-attention&action=getUnmapTransaction'
	});
	//alert(new Date(new Date()-24*60*60))
	function renderStatus(v, p, r){
	    return lang.attention.transactions_status_json[v]
	}
	
	function renderAmountValue(v, p, r){
	    if(v > 0){
		var color = 'green';
	    }else{
		var color = 'red';
	    }
	    return String.format('<font color="'+color+'">{0} {1}</font>', r.data.amountCurrency, v);
	}
	    
	var unMapTransactionGrid = new Ext.grid.GridPanel({
		autoHeight: true,
                store: unMapTransactionGridStore,
                columns:[{
                    header: lang.attention.grid_transactions_id,
                    dataIndex: 'id',
                    width: 110,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.attention.grid_payer_id,
                    dataIndex: 'payerId',
                    width: 120,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.attention.grid_txn_id,
                    dataIndex: 'txnId',
                    width: 120,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.attention.grid_transactions_time,
                    dataIndex: 'transactionTime',
                    width: 150,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.attention.grid_amount,
                    dataIndex: 'amountValue',
                    width: 100,
                    renderer: renderAmountValue,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.attention.grid_status,
                    dataIndex: 'status',
                    width: 100,
                    renderer: renderStatus,
                    align: 'center',
                    sortable: true
                }],
		tbar: [{xtype: 'tbtext', text: 'Start Date:'},
		       {xtype:"datefield", id:"start_date", name:"start_date", format:'Y-m-d', value:start_date},
		       {xtype: 'tbseparator'},
		       {xtype: 'tbtext', text: 'End Date:'},
		       {xtype:"datefield", id:"end_date", name:"end_date", format:'Y-m-d', value:end_date},
		       {xtype:"tbbutton", text: 'Submit', handler:function(){
			    //console.log(unMapTransactionGridStore);
			    unMapTransactionGridStore.baseParams = {
				start_date: document.getElementById("start_date").value,
				end_date: document.getElementById("end_date").value
			    };
			    unMapTransactionGridStore.load({params:{start:0, limit:20}});
		       }
		       }
		    ],
                bbar: new Ext.PagingToolbar({
                                    pageSize: 20,
                                    store: unMapTransactionGridStore,
                                    displayInfo: true
                })
        });
	
	unMapTransactionGrid.on("rowdblclick", function(oGrid){
	    var oRecord = oGrid.getSelectionModel().getSelected();
	    window.open("/eBayBO/transactions.php?id="+oRecord.data['id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");        
	});
	 
	if(!win){
            win = desktop.createWindow({
                id: 'attention-win',
                title:lang.attention.window_title,
                width:720,
                height:400,
                iconCls: 'attention-icon',
                shim:false,
                animCollapse:false,
                constrainHeader:true,
		layout: 'fit',
		items:[{
			xtype: 'panel',
			title: lang.attention.un_map_transaction_grid_title,
			autoHeight: true,
			items: unMapTransactionGrid
                        }],
		//html: 'test',
                taskbuttonTooltip: '<b>注意</b><br />系统提醒'
            });
	    
        }
        
        win.show();
    }
})