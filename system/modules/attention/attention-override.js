
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
		end_date: end_date
	    },
	    //autoLoad: true,
	    fields: ['id', 'txnId', 'transactionTime', 'status', 'amountCurrency', 'amountValue', 'payeeId', 'payerId'],
	    url:'connect.php?moduleId=qo-attention&action=getUnmapTransaction'
	});
	//alert(new Date(new Date()-24*60*60))
	function renderStatus(v, p, r){
	    return lang.attention.transactions_status_json[v];
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
		autoScroll: true,
                columns:[{
		    header: lang.attention.grid_transactions_payee_id,
                    dataIndex: 'payeeId',
                    width: 110,
                    align: 'center',
                    sortable: true    
		},{
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
		tbar: [
		       {xtype: 'tbtext', text: 'Seller:'},
		       /*{xtype:"textfield", id:"payeeId", name:"payeeId"},*/
		       {
			    id: 'payeeId',
			    xtype: 'combo',
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
			    name: 'sellerId',
			    hiddenName:'sellerId'
			},
		       {xtype: 'tbseparator'},
		       {xtype: 'tbtext', text: 'Start Date:'},
		       {xtype:"datefield", id:"start_date", name:"start_date", format:'Y-m-d', value:start_date},
		       {xtype: 'tbseparator'},
		       {xtype: 'tbtext', text: 'End Date:'},
		       {xtype:"datefield", id:"end_date", name:"end_date", format:'Y-m-d', value:end_date},
		       {xtype:"tbbutton", text: 'Submit',
			    disabled: (get_cookie('qo-attention.getUnmapTransaction') == 0)?true:false,
			    handler:function(){
			    //console.log(unMapTransactionGridStore);
			    unMapTransactionGridStore.baseParams = {
				payeeId: document.getElementById("payeeId").value,
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
	
	var waitingAuditRefundGridStore = new Ext.data.JsonStore({
            root: 'records',
	    totalProperty: 'totalCount',
	    idProperty: 'id',
	    //remoteSort: true,
	    baseParams:{
		start_date: start_date,
		end_date: end_date
	    },
	    //autoLoad: true,
	    fields: ['id', 'txnId', 'transactionTime', 'status', 'amountCurrency', 'amountValue', 'payeeId', 'payerId', 'createdBy', 'createdOn', 'transactionReason'],
	    url:'connect.php?moduleId=qo-attention&action=getWaitingAuditRefund'
	});
	
	function renderTransactionReason(v, p, r){
	    return lang.attention.transactions_reason_json[v];
	}
	
	var waitingAuditRefundGrid = new Ext.grid.GridPanel({
		autoHeight: true,
                store: waitingAuditRefundGridStore,
		autoScroll: true,
                columns:[/*{
		    header: lang.attention.grid_transactions_payee_id,
                    dataIndex: 'payeeId',
                    width: 110,
                    align: 'center',
                    sortable: true    
		},*/{
                    header: lang.attention.grid_transactions_id,
                    dataIndex: 'id',
                    width: 110,
                    align: 'center',
                    sortable: true
                },/*{
                    header: lang.attention.grid_payer_id,
                    dataIndex: 'payerId',
                    width: 120,
                    align: 'center',
                    sortable: true
                },*/{
                    header: lang.attention.grid_created_by,
                    dataIndex: 'createdBy',
                    width: 120,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.attention.grid_created_on,
                    dataIndex: 'createdOn',
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
                    header: lang.attention.grid_transactions_reason,
                    dataIndex: 'transactionReason',
                    width: 100,
                    renderer: renderTransactionReason,
                    align: 'center',
                    sortable: true
                }],
		tbar: [
		       {xtype: 'tbtext', text: 'Seller:'},
		       /*{xtype:"textfield", id:"payeeId", name:"payeeId"},*/
		       {
			    id: 'waitingAuditRefundPayeeId',
			    xtype: 'combo',
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
			    name: 'sellerId',
			    hiddenName:'sellerId'
			},
		       {xtype: 'tbseparator'},
		       {xtype: 'tbtext', text: 'Start Date:'},
		       {xtype:"datefield", id:"waiting_wudit_refund_start_date", name:"start_date", format:'Y-m-d', value:start_date},
		       {xtype: 'tbseparator'},
		       {xtype: 'tbtext', text: 'End Date:'},
		       {xtype:"datefield", id:"waiting_wudit_refund_end_date", name:"end_date", format:'Y-m-d', value:end_date},
		       {xtype:"tbbutton", text: 'Submit',
			    disabled: (get_cookie('qo-attention.getWaitingAuditRefund') == 0)?true:false,
			    handler:function(){
			    //console.log(unMapTransactionGridStore);
			    waitingAuditRefundGridStore.baseParams = {
				payeeId: document.getElementById("waitingAuditRefundPayeeId").value,
				start_date: document.getElementById("waiting_wudit_refund_start_date").value,
				end_date: document.getElementById("waiting_wudit_refund_end_date").value
			    };
			    waitingAuditRefundGridStore.load({params:{start:0, limit:20}});
		       }
		       }
		    ],
                bbar: new Ext.PagingToolbar({
		    pageSize: 20,
		    store: waitingAuditRefundGridStore,
		    displayInfo: true
                })
        });
	
	waitingAuditRefundGrid.on("rowdblclick", function(oGrid){
	    var oRecord = oGrid.getSelectionModel().getSelected();
	    window.open("/eBayBO/transactions.php?id="+oRecord.data['id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");        
	});
	
	var waitDealShipmentGridStore = new Ext.data.JsonStore({
            root: 'records',
	    totalProperty: 'totalCount',
	    idProperty: 'id',
	    //remoteSort: true,
	    baseParams:{
		start_date: start_date,
		end_date: end_date
	    },
	    //autoLoad: true,
	    fields: ['id', 'shipmentReason', 'shippingFeeCurrency', 'shippingFeeValue', 'createdBy', 'createdOn'],
	    url:'connect.php?moduleId=qo-attention&action=getWaitDealShipment'
	});
	
	function renderShippingFee(v, p, r){
	    return String.format('{0} {1}', r.data.shippingFeeCurrency, v);
	}
	
	var waitDealShipmentGrid = new Ext.grid.GridPanel({
		autoHeight: true,
                store: waitDealShipmentGridStore,
		autoScroll: true,
                columns:[{
		    header: lang.attention.wait_deal_shipment_id,
                    dataIndex: 'id',
                    width: 110,
                    align: 'center',
                    sortable: true    
		},{
                    header: lang.attention.wait_deal_shipment_reason,
                    dataIndex: 'shipmentReason',
                    width: 150,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.attention.wait_deal_shipment_shipping_fee,
                    dataIndex: 'shippingFeeValue',
                    width: 120,
		    renderer: renderShippingFee,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.attention.wait_deal_shipment_created_by,
                    dataIndex: 'createdBy',
                    width: 120,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.attention.wait_deal_shipment_created_on,
                    dataIndex: 'createdOn',
                    width: 120,
                    align: 'center',
                    sortable: true
                }],
                bbar: new Ext.PagingToolbar({
		    pageSize: 20,
		    store: waitDealShipmentGridStore,
		    displayInfo: true
                })
        });
	
	waitDealShipmentGrid.on("rowdblclick", function(oGrid){
	    var oRecord = oGrid.getSelectionModel().getSelected();
	    window.open("/eBayBO/shipments.php?id="+oRecord.data['id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");        
	});
	
	if(!win){
            win = desktop.createWindow({
                id: 'attention-win',
                title:lang.attention.window_title,
                width:830,
                height:550,
                iconCls: 'attention-icon',
                shim:false,
                animCollapse:false,
                constrainHeader:true,
		layout: 'fit',
		autoScroll: true,
		items:[{
		    xtype: 'panel',
		    collapsible: true,
		    title: lang.attention.un_map_transaction_grid_title,
		    autoHeight: true,
		    items: unMapTransactionGrid
                },{
		    xtype: 'panel',
		    collapsible: true,
		    title: lang.attention.waiting_audit_refund_grid_title,
		    autoHeight: true,
		    items: waitingAuditRefundGrid
                }/*{
		    xtype: 'panel',
		    collapsible: true,
		    title: lang.attention.wait_deal_shipment_title,
		    autoHeight: true,
		    items: waitDealShipmentGrid	    
		}*/],
		//html: 'test',
                taskbuttonTooltip: '<b>注意</b><br />系统提醒'
            });
	    
        }
        
        win.show();
    }
})