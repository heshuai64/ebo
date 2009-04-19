Ext.override(QoDesk.Transactions, {
  
    createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('transactions-win');
        
        var searchTransaction = function(){
            var searchTransactionForm = Ext.getCmp("search-transaction-form").getForm();
            var transactionGridStore = new Ext.data.JsonStore({
                root: 'records',
                totalProperty: 'totalCount',
                idProperty: 'id',
                //remoteSort: true,
                baseParams:{transactionsId:searchTransactionForm.findField('transactionsId').getValue(), ordersId:searchTransactionForm.findField('ordersId').getValue(),
                            payeeId:searchTransactionForm.findField('payeeId').getValue(),status:searchTransactionForm.findField('status').getValue(),
                            txnId:searchTransactionForm.findField('txnId').getValue(),payer:searchTransactionForm.findField('payer').getValue(),
                            payerEmail:searchTransactionForm.findField('payerEmail').getValue(),payerAddressLine:searchTransactionForm.findField('payerAddressLine').getValue(),
                            transactionTimeFrom:searchTransactionForm.findField('transactionTimeFrom').getValue(),transactionTimeTo:searchTransactionForm.findField('transactionTimeTo').getValue(),
                            createdOnFrom:searchTransactionForm.findField('createdOnFrom').getValue(),createdOnOnTo:searchTransactionForm.findField('createdOnOnTo').getValue(),
                            modifiedOnFrom:searchTransactionForm.findField('modifiedOnFrom').getValue(),modifiedOnTo:searchTransactionForm.findField('modifiedOnTo').getValue()
                            },
                fields: ['id', 'txnId', 'transactionTime', 'status', 'amountCurrency', 'amountValue','payerId'],
                url:'connect.php?moduleId=qo-transactions&action=searchTransaction'
            });
            
            function renderStatus(v, p, r){
                return lang.transactions.transactions_status_json[v]
            }
            
            function renderAmountValue(v, p, r){
                if(v > 0){
                    var color = 'green';
                }else{
                    var color = 'red';
                }
                return String.format('<font color="'+color+'">{0} {1}</font>', r.data.amountCurrency, v);
            }
            
            var transactionGrid = new Ext.grid.GridPanel({
                store: transactionGridStore,
                columns:[{
                    header: lang.transactions.grid_transactions_id,
                    dataIndex: 'id',
                    width: 110,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.transactions.grid_payer_id,
                    dataIndex: 'payerId',
                    width: 120,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.transactions.grid_txn_id,
                    dataIndex: 'txnId',
                    width: 100,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.transactions.grid_transactions_time,
                    dataIndex: 'transactionTime',
                    width: 150,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.transactions.grid_status,
                    dataIndex: 'status',
                    width: 100,
                    renderer: renderStatus,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.transactions.grid_amount,
                    dataIndex: 'amountValue',
                    width: 100,
                    renderer: renderAmountValue,
                    align: 'center',
                    sortable: true
                }],
                bbar: new Ext.PagingToolbar({
                                    pageSize: 20,
                                    store: transactionGridStore,
                                    displayInfo: true
                            })
            });
            
            transactionGrid.on("rowdblclick", function(oGrid){
                var oRecord = oGrid.getSelectionModel().getSelected();
                window.open("/eBayBO/transactions.php?id="+oRecord.data['id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");        
	    });
             
            transactionGridStore.load({params:{start:0, limit:20}});
            
            var search_result_win = desktop.createWindow({
               title:lang.transactions.search_result,
               width:700,
               height:400,
               iconCls: 'transactions-icon',
               shim:false,
               animCollapse:false,
               constrainHeader:true,
               layout: 'fit',
               items: transactionGrid,
               taskbuttonTooltip: lang.transactions.task_button_tooltip
            })
            search_result_win.show();
        }
        
        if(!win){
             win = desktop.createWindow({
                id: 'transactions-win',
                title: lang.transactions.search_transactions,
                width:600,
                height:350,
                iconCls: 'transactions-icon',
                shim:false,
                animCollapse:false,
                constrainHeader:true,
		layout: 'fit',
                items: [{
                    id:"search-transaction-form",
                    xtype:"form",
                    items:[{
                        layout:"column",
                        items:[{
                            columnWidth:0.5,
                            layout:"form",
                            items:[{
                                xtype:"fieldset",
                                title:"System Info",
                                autoHeight:true,
                                defaults:{
                                    width:150
                                },
                                items:[{
                                    xtype:"textfield",
                                    fieldLabel:lang.transactions.form_transactions_id,
                                    name:"transactionsId"
                                  },{
                                    xtype:"textfield",
                                    fieldLabel:lang.transactions.form_orders_id,
                                    name:"ordersId"
                                  },{
                                    xtype: 'combo',
                                    fieldLabel:lang.transactions.form_payee_id,
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
                                    name: 'payeeId',
                                    hiddenName:'payeeId'
                                  },{
                                    xtype:'combo',
                                    fieldLabel: lang.transactions.form_status,
                                    store: new Ext.data.SimpleStore({
                                        fields: ["statusValue", "statusName"],
                                        data: lang.transactions.transactions_status
                                    }),		  
                                    mode: 'local',
                                    displayField: 'statusName',
                                    valueField: 'statusValue',
                                    triggerAction: 'all',
                                    editable: false,
                                    name: 'status',
                                    hiddenName:'status'
                                  }]
                            }]
                          },{
                                columnWidth:0.5,
                                layout:"form",
                                items:[{
                                    xtype:"fieldset",
                                    title:"Buyer PayPal Info",
                                    autoHeight:true,
                                    defaults:{
                                        width:150
                                    },
                                    items:[{
                                        xtype:"textfield",
                                        fieldLabel:lang.transactions.form_txn_id,
                                        name:"txnId"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:lang.transactions.form_payer_id,
                                        name:"payer"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:lang.transactions.form_payer_email,
                                        name:"payerEmail"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:lang.transactions.form_payer_address,
                                        name:"payerAddressLine"
                                      }]
                                }]
                          }]
                      },{
                            layout:"table",
                            layoutConfig:{
                              columns:3
                            },
                            defaults:{
                                border:false
                            },
                            items:[{
                                html:lang.transactions.form_payment_date
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:lang.transactions.form_start,
                                    name:"transactionTimeFrom",
                                    format:'Y-m-d'
                                }]
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "transactions-search-date",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:lang.transactions.form_end,
                                    name:"transactionTimeTo",
                                    format:'Y-m-d'
                                }]
                              },{
                                html:lang.transactions.form_created_date
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "transactions-search-date",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:lang.transactions.form_start,
                                    name:"createdOnFrom",
                                    format:'Y-m-d'
                                }]
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:lang.transactions.form_end,
                                    name:"createdOnOnTo",
                                    format:'Y-m-d'
                                }]
                              },{
                                html:lang.transactions.form_modified_date
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "transactions-search-date",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:lang.transactions.form_start,
                                    name:"modifiedOnFrom",
                                    format:'Y-m-d'
                                }]
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:lang.transactions.form_end,
                                    name:"modifiedOnTo",
                                    format:'Y-m-d'
                                }]
                              }]
                          }],
                        buttons: [{
                            text: lang.transactions.submit,
                            handler: function(){
				searchTransaction();
                                win.close();
			    }
                        },{
                            text: lang.transactions.create_transactions,
                            handler: function(){
				
			    }
                        },{
                            text: lang.transactions.close,
                            handler: function(){
				win.close();
			    }
                        }]
                }],
                taskbuttonTooltip: '<b>付款查询</b><br />查询PayPal付款'
            });
        }
        
        win.show();
    }
})