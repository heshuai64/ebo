Ext.onReady(function(){
        Ext.Ajax.request({
                waitMsg: 'Please wait...',
                url: 'connect.php?moduleId=qo-transactions&action=getTransactionId',
                success: function(response){
                    var transactionsId = response.responseText;
                    var transactionOrderStore = new Ext.data.JsonStore({
                            root: 'records',
                            totalProperty: 'totalCount',
                            idProperty: 'id',
                            autoLoad:true,
                            fields: ['ordersId', 'grandTotalCurrency', 'grandTotalValue', 'createdBy', 'createdOn', 'modifiedBy', 'modifiedOn', 'status'],
                            url:'connect.php?moduleId=qo-transactions&action=getTransactionOrder&id='+transactionsId
                    });
                    
                    var transactionOrderGrid = new Ext.grid.EditorGridPanel({
                            autoHeight: true,
                            store: transactionOrderStore,
                            selModel: new Ext.grid.RowSelectionModel({}),
                            columns:[{
                                header: "Order ID",
                                dataIndex: 'ordersId',
                                width: 105,
                                align: 'center',
                                sortable: true
                            },{
                                header: 'Currency',
                                dataIndex: 'grandTotalCurrency',
                                width: 60, 
                                editor: new Ext.form.ComboBox({
                                    store: new Ext.data.SimpleStore({
                                        fields: ["grandTotalCurrencyValue", "grandTotalCurrencyName"],
                                        data: lang.transactions.currency
                                    }),
                                    mode: 'local',
                                    displayField: 'grandTotalCurrencyName',
                                    valueField: 'grandTotalCurrencyValue',		            
                                    triggerAction: 'all',
                                    editable: false,
                                    name: 'grandTotalCurrency',
                                    hiddenName:'grandTotalCurrency'
                                })    
                            },{
                                header: "Amount",
                                dataIndex: 'grandTotalValue',
                                width: 80,
                                align: 'center',
                                sortable: true
                            },{
                                header: "Creator",
                                dataIndex: 'createdBy',
                                width: 120,
                                align: 'center',
                                sortable: true
                            },{
                                header: "Created Time",
                                dataIndex: 'createdOn',
                                width: 120,
                                align: 'center',
                                sortable: true
                            },{
                                header: "mender",
                                dataIndex: 'modifiedBy',
                                width: 120,
                                align: 'center',
                                sortable: true
                            },{
                                header: "modified Time",
                                dataIndex: 'modifiedOn',
                                width: 120,
                                align: 'center',
                                sortable: true
                            },{
                                header: "Status",
                                dataIndex: 'status',
                                width: 80,
                                align: 'center',
                                sortable: true
                            }],
                            bbar: [{
                                text: 'Map Orders',
                                handler: function(){
                                    var map_transaction_order_data_store = new Ext.data.JsonStore({
                                            root: 'records',
                                            totalProperty: 'totalCount',
                                            idProperty: 'id',
                                            autoLoad:true,
                                            fields: ['id', 'buyerId', 'ebayName', 'ebayEmail', 'grandTotalCurrency', 'grandTotalValue'],
                                            url:'connect.php?moduleId=qo-transactions&action=readMapTransactionOrder&id='+transactionsId
                                    });
                                
                                    var map_transaction_order_grid = new Ext.grid.GridPanel({
                                            store: map_transaction_order_data_store,
                                            columns:[{
                                                    header: 'Order Id',
                                                    readOnly: true,
                                                    dataIndex: 'id',
                                                    width: 110
                                                  },{
                                                    header: 'buyer Id',
                                                    dataIndex: 'buyerId',
                                                    width: 120
                                                  },{
                                                    header: 'Name',
                                                    dataIndex: 'ebayName',
                                                    width: 120
                                                  },{
                                                    header: 'Email',
                                                    dataIndex: 'ebayEmail',
                                                    width: 120
                                                  },{
                                                    header: 'Currency',
                                                    dataIndex: 'grandTotalCurrency',
                                                    width: 60
                                                  },{
                                                    header: 'Value',
                                                    dataIndex: 'grandTotalValue',
                                                    width: 60
                                                  }],
                                            autoHeight: true,
                                            selModel: new Ext.grid.RowSelectionModel({}),
                                            bbar:[{xtype:'tbtext',text:'Order Id : '},{xtype:'textfield',id:'orderId'},{xtype:'tbbutton',text:'load',handler:function(){map_transaction_order_data_store.reload({params:{orderId:Ext.getCmp('orderId').getValue()}});}}] 
                                    });
                                    
                                    var map_transaction_order_window = new Ext.Window({
                                        title: 'Map ' + transactionsId,
                                        closable:true,
                                        width: 750,
                                        autoHeight: true,
                                        plain:true,
                                        layout: 'fit',
                                        items: map_transaction_order_grid,
                                        buttons: [{
                                            text: 'Map',
                                                handler: function(){
                                                    Ext.Ajax.request({
                                                        waitMsg: 'Please wait...',
                                                        url: 'connect.php?moduleId=qo-transactions&action=mapTransactionOrder',
                                                        params: {
                                                                transactionsId: transactionsId,
                                                                ordersId: map_transaction_order_grid.getSelectionModel().getSelected().data['id'],
                                                                amountCurrency: map_transaction_order_grid.getSelectionModel().getSelected().data['grandTotalCurrency'],
                                                                amountValue: map_transaction_order_grid.getSelectionModel().getSelected().data['grandTotalValue']
                                                        },
                                                        success: function(response){
                                                                var result = eval(response.responseText);
                                                                switch (result) {
                                                                    case 1:
                                                                        transactionOrderStore.reload();
                                                                        map_transaction_order_window.hide();
                                                                        break;
                                                                    default:
                                                                        Ext.MessageBox.alert('Uh uh...', 'We couldn\'t save him...');
                                                                        break;
                                                                }
                                                        },
                                                        failure: function(response){
                                                                var result = response.responseText;
                                                                Ext.MessageBox.alert('error', 'could not connect to the database. retry later');
                                                        }
                                                    });		
                                                }
                                        }]
                                    });    
                                    map_transaction_order_window.show();
                                        
                                }
                            }]
                    });
                                
                    var transactionDetailForm = new Ext.FormPanel({
                        autoScroll:true,
                        reader:new Ext.data.JsonReader({
                            }, ['id','txnId','transactionTime','amountCurrency','amountValue','status','remarks',
                                'createdBy','createdOn','modifiedBy','modifiedOn','payeeId','payerId','payerName',
                                'payerEmail','payerAddressLine1','payerAddressLine2','payerCity','payerStateOrProvince',
                                'payerPostalCode','payerCountry','itemId'
                        ]),
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
                                        width:240
                                    },
                                    labelWidth:110,
                                    items:[{
                                        xtype:"textfield",
                                        fieldLabel:"Transaction Id",
                                        value: transactionsId,
                                        name:"id"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:"Reference Number",
                                        name:"txnId"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:"Transaction Time",
                                        name:"transactionTime"
                                      },{
                                        xtype: 'combo',
                                        fieldLabel:"Payee ID",
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
                                        layout:"table",
                                        layoutConfig:{
                                          columns:3
                                        },
                                        border:false,
                                        items:[{
                                            width:60,
                                            html:"<font size=2>Amount:</font>",
                                            border:false
                                          },{
                                            layout:"form",
                                            labelWidth:0,
                                            hideLabels:true,
                                            labelSeparator:"",
                                            border:false,
                                            style:"padding-left:55px",
                                            items:[{
                                                xtype:'combo',
                                                store: new Ext.data.SimpleStore({
                                                    fields: ["amountCurrencyValue", "amountCurrencyName"],
                                                    data: lang.transactions.currency
                                                }),
                                                width: 60,			  
                                                mode: 'local',
                                                displayField: 'amountCurrencyName',
                                                valueField: 'amountCurrencyValue',
                                                triggerAction: 'all',
                                                editable: false,
                                                fieldLabel: '',
                                                name: 'amountCurrency',
                                                hiddenName:'amountCurrency'
                                              }]
                                          },{
                                            layout:"form",
                                            labelWidth:0,
                                            hideLabels:true,
                                            labelSeparator:"",
                                            border:false,
                                            style:"padding-left:10px",
                                            items:[{
                                                xtype:"textfield",
                                                fieldLabel:"",
                                                name:"amountValue",
                                                width:80
                                              }]
                                          }]
                                      },{
                                        xtype:'combo',
                                        fieldLabel: "Status",
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
                                      },{
                                        xtype:"textarea",
                                        fieldLabel:"Remarks",
                                        name:"remarks"
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
                                        width:200
                                    },
                                    labelWidth:90,
                                    items:[{
                                        xtype:"textfield",
                                        fieldLabel:"Payer ID",
                                        name:"payerId"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:"Name",
                                        name:"payerName"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:"Email",
                                        name:"payerEmail"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:"Address 1",
                                        name:"payerAddressLine1"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:"Address 2",
                                        name:"payerAddressLine2"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:"City",
                                        name:"payerCity"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:"State/Province",
                                        name:"payerStateOrProvince"
                                      },{
                                        xtype:"textfield",
                                        fieldLabel:"Postal Code/Zip",
                                        name:"payerPostalCode"
                                      },{
                                        xtype: 'combo',
                                        fieldLabel:"Country",
                                        mode: 'local',
                                        store: new Ext.data.JsonStore({
                                            autoLoad: true,
                                            fields: ['id', 'name'],
                                            url: "connect.php?moduleId=qo-transactions&action=getCountries"
                                        }),
                                        valueField:'id',
                                        displayField:'name',
                                        triggerAction: 'all',
                                        editable: false,
                                        selectOnFocus:true,
                                        name: 'paypayerCountryeeId',
                                        hiddenName:'payerCountry'
                                      }]
                                  }]
                              }]
                        },{
                            xtype: 'panel',
                            title: "Orders",
                            autoHeight: true,
                            items: transactionOrderGrid
                        }
                        ],
                        buttons: [{
                                    text: 'Create',
                                    handler: function(){
                                        
                                        transactionDetailForm.getForm().submit({
                                            url: "connect.php?moduleId=qo-transactions&action=createTransaction",
                                            success: function(f, a){
                                                //console.log(a);
                                                var response = Ext.decode(a.response.responseText);
                                                if(response.success){
                                                        Ext.Msg.alert('Success', 'Transaction Create Successfully!');
                                                }else{
                                                        Ext.Msg.alert('Failure', 'Transaction Create Failed!');
                                                }
                                            },
                                            waitMsg: "Please wait..."
                                        });
                                    }
                                },{
                                    text: 'Close',
                                    handler: function(){
                                        window.close();
                                    }
                                }
                        ]
                    })
                
                
                    transactionDetailForm.render(document.body);
        
            },
            failure: function(response){
                var result = response.responseText;
                Ext.MessageBox.alert('error', 'could not connect to the database. retry later');
            }
        });
})