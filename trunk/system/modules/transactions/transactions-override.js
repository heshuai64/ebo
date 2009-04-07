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
                remoteSort: true,
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
                switch(v){
                    case "P":
                        return "完成付款";
                    break;
                
                    case "V":
                        return "Reversed";
                    break;
                
                    case "C":
                        return "Canceled Reversal";
                    break;
                
                    case "R":
                        return "退款";
                    break;
                    
                }
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
                    header: "系统付款号",
                    dataIndex: 'id',
                    width: 110,
                    align: 'center',
                    sortable: true
                },{
                    header: "PayPal付款帐号",
                    dataIndex: 'payerId',
                    width: 120,
                    align: 'center',
                    sortable: true
                },{
                    header: "PayPal付款号",
                    dataIndex: 'txnId',
                    width: 100,
                    align: 'center',
                    sortable: true
                },{
                    header: "付款时间",
                    dataIndex: 'transactionTime',
                    width: 150,
                    align: 'center',
                    sortable: true
                },{
                    header: "状态",
                    dataIndex: 'status',
                    width: 100,
                    renderer: renderStatus,
                    align: 'center',
                    sortable: true
                },{
                    header: "总付款",
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
                window.open("http://127.0.0.1:6666/eBayBO/transactions.php?id="+oRecord.data['id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");        
	    });
             
            transactionGridStore.load({params:{start:0, limit:20}});
            
            win = desktop.createWindow({
               title:'查询结果',
               width:700,
               height:400,
               iconCls: 'transactions-icon',
               shim:false,
               animCollapse:false,
               constrainHeader:true,
               layout: 'fit',
               items: transactionGrid,
               taskbuttonTooltip: '<b>查询结果</b><br />付款查询结果列表'
            })
            win.show();
        }
        
        if(!win){
             win = desktop.createWindow({
                id: 'transactions-win',
                title:'付款查询',
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
                                title:"System",
                                autoHeight:true,
                                defaults:{
                                    width:150
                                },
                                items:[{
                                    xtype:"textfield",
                                    fieldLabel:"付款编号",
                                    name:"transactionsId"
                                  },{
                                    xtype:"textfield",
                                    fieldLabel:"订单编号",
                                    name:"ordersId"
                                  },{
                                    xtype:"combo",
                                    fieldLabel:"收款帐号",
                                    name:"payeeId",
                                    hiddenName:"payeeId"
                                  },{
                                    xtype:"combo",
                                    fieldLabel:"付款状态",
                                    name:"status",
                                    hiddenName:"status"
                                  }]
                            }]
                          },{
                                columnWidth:0.5,
                                layout:"form",
                                items:[{
                                    xtype:"fieldset",
                                    title:"PayPal",
                                    autoHeight:true,
                                    defaults:{
                                        width:150
                                    },
                                    items:[{
                                        xtype:"textfield",
                                        fieldLabel:"付款编号",
                                        name:"txnId"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:"付款帐号",
                                        name:"payer"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:"付款邮件地址",
                                        name:"payerEmail"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:"付款地址",
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
                                html:"付款日期:"
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:"开始",
                                    name:"transactionTimeFrom",
                                    format:'Y-m-d'
                                }]
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "transactions-search-date",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:"结束",
                                    name:"transactionTimeTo",
                                    format:'Y-m-d'
                                }]
                              },{
                                html:"创建日期:"
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "transactions-search-date",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:"开始",
                                    name:"createdOnFrom",
                                    format:'Y-m-d'
                                }]
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:"结束",
                                    name:"createdOnOnTo",
                                    format:'Y-m-d'
                                }]
                              },{
                                html:"修改日期:"
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "transactions-search-date",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:"开始",
                                    name:"modifiedOnFrom",
                                    format:'Y-m-d'
                                }]
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:"结束",
                                    name:"modifiedOnTo",
                                    format:'Y-m-d'
                                }]
                              }]
                          }],
                        buttons: [{
                            text: '提交查询',
                            handler: function(){
				searchTransaction();
			    }
                        },{
                            text: '创建付款',
                            handler: function(){
				
			    }
                        }]
                }],
                taskbuttonTooltip: '<b>付款查询</b><br />查询PayPal付款'
            });
        }
        
        win.show();
    }
})