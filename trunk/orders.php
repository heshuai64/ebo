<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="PRAGMA" content="NO-CACHE">
        <meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
        <meta http-equiv="EXPIRES" content="-1">
        
        <title><?=$_GET['id']?></title>
        
        <!-- EXT JS LIBRARY -->
        <link rel="stylesheet" type="text/css" href="../Ext/2.2/resources/css/ext-all.css" />
        <script src="../Ext/2.2/adapter/ext/ext-base.js"></script>
        <script src="../Ext/2.2/ext-all-debug.js"></script>
</head>
<body>
    <script type="text/javascript">
            var currency_data = [['USD','USD'],['EUR','EUR'],['GBP','GBP'],['AUD','AUD'],['RMB','RMB'],['CAD','CAD']];
            var transaction_status_data = [['P','Completed'],['N','Pending'],['D','Denied'],['F','Failed'],['R','Refunded'],['V','Reversed'],['C','Canceled Reversal'],['X','Canceled']];
            var orderDetailGridStore = new Ext.data.JsonStore({
                root: 'records',
                totalProperty: 'totalCount',
                idProperty: 'id',
                autoLoad:true,
                fields: ['id','itemId', 'itemTitle', 'skuId', 'quantity', 'unitPriceCurrency', 'unitPriceValue'],
                url:'connect.php?moduleId=qo-orders&action=getOrderDetail&id=<?=$_GET['id']?>'
            });
            
            var orderDetailGrid = new Ext.grid.EditorGridPanel({
                autoHeight: true,
                store: orderDetailGridStore,
                selModel: new Ext.grid.RowSelectionModel({}),
                columns:[{
                    header: "Item Id",
                    dataIndex: 'itemId',
                    width: 100,
                    align: 'center',
                    sortable: true
                },{
                    header: "Item Title",
                    dataIndex: 'itemTitle',
                    width: 350,
                    align: 'center'
                },{
                    header: "SKU",
                    dataIndex: 'skuId',
                    width: 100,
                    align: 'center',
                    sortable: true
                },{
                    header: "Quantity",
                    dataIndex: 'quantity',
                    width: 60,
                    align: 'center',
                    sortable: true
                },{
                    header: 'Currency',
                    dataIndex: 'unitPriceCurrency',
                    width: 60,
                    editor: new Ext.form.ComboBox({
                         store: new Ext.data.SimpleStore({
                             fields: ["unitPriceCurrencyValue", "unitPriceCurrencyName"],
                             data: currency_data
                         }),
                         mode: 'local',
                         displayField: 'unitPriceCurrencyName',
                         valueField: 'unitPriceCurrencyValue',
                         triggerAction: 'all',
                         editable: false,
                         name: 'unitPriceCurrency',
                         hiddenName:'unitPriceCurrency'
                    })
                    
                },{
                    header: "Unit Price",
                    dataIndex: 'unitPriceValue',
                    width: 90,
                    align: 'center',
                    sortable: true
                }],
                bbar: [{
                        text: 'Add Detail',
                        handler: function(){
                            var add_order_detail_form =  form = new Ext.FormPanel({
                                labelAlign: 'top',
                                bodyStyle:'padding:5px',     
                                items: [{
                                        layout: 'column',
                                        border: false,
                                        items:[{
                                            columnWidth:0.5,
                                            layout: 'form',
                                            border:false,
                                            items: [{ xtype: 'textfield',
                                                    name: 'itemId',
                                                    allowBlank: false,
                                                    fieldLabel: 'Item Id'
                                                    },{
                                                        xtype: 'textfield',
                                                        name: 'skuId',
                                                        allowBlank: false,
                                                        fieldLabel: 'Sku'
                                                    },{
                                                        xtype:'combo',
                                                        store: new Ext.data.SimpleStore({
                                                                fields: ["unitPriceCurrencyValue", "unitPriceCurrencyName"],
                                                                data: currency_data
                                                                }),
                                                                listWidth: 60,
                                                                width: 60,			  
                                                                mode: 'local',
                                                                displayField: 'unitPriceCurrencyName',
                                                                valueField: 'unitPriceCurrencyValue',
                                                                triggerAction: 'all',
                                                                editable: false,
                                                                fieldLabel: 'Currency',
                                                                name: 'unitPriceCurrency',
                                                                hiddenName:'unitPriceCurrency'
                                                    }]
                                           },{
                                            columnWidth:0.5,
                                            layout: 'form',
                                            border:false,
                                            items: [{ xtype: 'textfield',
                                                    name: 'itemTitle',
                                                    allowBlank: false,
                                                    fieldLabel: 'Item Title'
                                                    },{
                                                    xtype: 'textfield',
                                                    name: 'skuTitle',
                                                    allowBlank: false,
                                                    fieldLabel: 'sku Title'
                                                    },{
                                                    xtype: 'textfield',
                                                    name: 'unitPriceValue',
                                                    allowBlank: false,
                                                    fieldLabel: 'Unit Price'
                                                }]
                                           }]
                                    },{
                                        xtype: 'numberfield',
                                        name: 'quantity',
                                        allowBlank: false,
                                        fieldLabel: 'Quantity',
                                        width: 80
                                }]
                            })
                            
                            var addOrderDetailWindow = new Ext.Window({
                                title: 'Add <?=$_GET['id']?> Detail' ,
                                closable:true,
                                width: 400,
                                height: 300,
                                plain:true,
                                layout: 'fit',
                                items: add_order_detail_form,
                                
                                buttons: [{
                                    text: 'Save and Close',
                                    handler: function(){
                                        Ext.Ajax.request({
                                            waitMsg: 'Please wait...',
                                            url: 'connect.php?moduleId=qo-orders&action=addOrderDetail',
                                            params: {
                                                    ordersId: '<?=$_GET['id']?>',
                                                    itemId: add_order_detail_form.form.findField('itemId').getValue(),
                                                    itemTitle: add_order_detail_form.form.findField('itemTitle').getValue(),
                                                    skuId: add_order_detail_form.form.findField('skuId').getValue(),
                                                    skuTitle: add_order_detail_form.form.findField('skuTitle').getValue(),
                                                    quantity: add_order_detail_form.form.findField('quantity').getValue(),
                                                    unitPriceCurrency: add_order_detail_form.form.findField('unitPriceCurrency').getValue(),
                                                    unitPriceValue: add_order_detail_form.form.findField('unitPriceValue').getValue()
                                            },
                                            success: function(response){
                                                var result = eval(response.responseText);
                                                switch (result) {
                                                    case 1:
                                                        orderDetailGridStore.reload();
                                                        addOrderDetailWindow.hide();
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
                                },{
                                    text: 'Cancel',
                                    handler: function(){
                                          addOrderDetailWindow.hide();
                                    }
                                }]
                            });
                            addOrderDetailWindow.show();
                        }
                        },{
                            text: 'Delete Detail',
                            handler: function(){
                                var deleteOrderDetail = function(btn){
                                    if(btn=='yes'){
                                        var selections = orderDetailGrid.selModel.getSelections();
                                        //console.log(selections);
                                        //var prez = [];
                                        var ids = "";
                                        for(i = 0; i< orderDetailGrid.selModel.getCount(); i++){
                                            //prez.push(selections[i].data.id);
                                            ids += selections[i].data.id + ","
                                        }
                                        ids = ids.slice(0,-1);
                                        //console.log(prez);
                                        //var encoded_array = Ext.encode(prez);
                                        Ext.Ajax.request({  
                                            waitMsg: 'Please Wait',
                                            url: 'connect.php?moduleId=qo-orders&action=deleteOrderDetail', 
                                            params: { 
                                              //ids:  encoded_array
                                              ids: ids
                                            }, 
                                            success: function(response){
                                                var result=eval(response.responseText);
                                                switch(result){
                                                case 1:  // Success : simply reload
                                                  orderDetailGridStore.reload();
                                                  break;
                                                default:
                                                  Ext.MessageBox.alert('Warning','Could not delete the entire selection.');
                                                  break;
                                                }
                                            },
                                            failure: function(response){
                                                var result=response.responseText;
                                                Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                            }
                                        });
                                    }  
                                }
                            
                                if(orderDetailGrid.selModel.getCount() >= 1){
                                    Ext.MessageBox.confirm('Confirmation','Delete those Details?', deleteOrderDetail);
                                } else {
                                    Ext.MessageBox.alert('Uh oh...','You can\'t really delete something you haven\'t selected huh?');
                                }
                            }
                        }
                    ]
            });
                      
            
            var orderTransactionStore = new Ext.data.JsonStore({
                root: 'records',
                totalProperty: 'totalCount',
                idProperty: 'id',
                autoLoad:true,
                fields: ['id', 'txnId', 'amountCurrency', 'amountValue', 'status', 'transactionTime'],
                url:'connect.php?moduleId=qo-orders&action=getOrderTransaction&id=<?=$_GET['id']?>'
            });
            
            var orderTransactionGrid = new Ext.grid.EditorGridPanel({
                autoHeight: true,
                store: orderTransactionStore,
                selModel: new Ext.grid.RowSelectionModel({}),
                columns:[{
                    header: "Transaction ID",
                    dataIndex: 'id',
                    width: 105,
                    align: 'center',
                    sortable: true
                },{
                    header: "Reference No",
                    dataIndex: 'txnId',
                    width: 120,
                    align: 'center'
                },{
                    header: 'Currency',
                    dataIndex: 'amountCurrency',
                    width: 60, 
                    editor: new Ext.form.ComboBox({
                        store: new Ext.data.SimpleStore({
                            fields: ["amountCurrencyValue", "amountCurrencyName"],
                            data: currency_data
                        }),
                        mode: 'local',
                        displayField: 'amountCurrencyName',
                        valueField: 'amountCurrencyValue',		            
                        triggerAction: 'all',
                        editable: false,
                        name: 'amountCurrency',
                        hiddenName:'amountCurrency'
                    })    
                },{
                    header: "Amount",
                    dataIndex: 'amountValue',
                    width: 80,
                    align: 'center',
                    sortable: true
                },{
                    header: "Transaction Time",
                    dataIndex: 'transactionTime',
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
                    text: 'Add Transaction',
                    //tooltip: 'Great tooltips...',
                    handler: function(){
                                var add_order_transaction_form =  form = new Ext.FormPanel({
                                    labelAlign: 'top',
                                    bodyStyle:'padding:5px',
                                    //width: 400,        
                                    items: [{
                                        layout:"column",
                                        border:false,
                                        items:[{
                                        columnWidth:0.5,
                                        layout:"form",
                                        border:false,
                                        defaults:{
                                            width:200
                                        },
                                        items:[{
                                                xtype:"textfield",
                                                fieldLabel:"Reference No",
                                                name:"txnId"
                                              },{
                                                xtype:"combo",
                                                fieldLabel:"Payee ID",
                                                name:"payeeId",
                                                hiddenName:"payeeId"
                                              },{
                                                xtype:"textfield",
                                                fieldLabel:"Payer ID",
                                                name:"payerId"
                                              },{
                                                layout:"column",
                                                border:false,
                                                items:[{
                                                    columnWidth:0.3,
                                                    layout:"form",
                                                    border:false,
                                                    items:[{
                                                            xtype:'combo',
                                                            store: new Ext.data.SimpleStore({
                                                                fields: ["amountCurrencyValue", "amountCurrencyName"],
                                                                data: currency_data
                                                            }),
                                                            listWidth: 50,
                                                            width: 50,			  
                                                            mode: 'local',
                                                            displayField: 'amountCurrencyName',
                                                            valueField: 'amountCurrencyValue',
                                                            triggerAction: 'all',
                                                            editable: false,
                                                            fieldLabel: 'Amount',
                                                            name: 'amountCurrency',
                                                            hiddenName:'amountCurrency'
                                                        }]
                                                    },{
                                                        columnWidth:0.7,
                                                        layout:"form",
                                                        //hideLabels:true,
                                                        border:false,
                                                        items:[{
                                                            xtype:"textfield",
                                                            fieldLabel:"",
                                                            width:100,
                                                            name:"amountValue",
                                                            labelSeparator:""
                                                          }]
                                                    }]
                                                },{
                                                    xtype:'combo',
                                                    store: new Ext.data.SimpleStore({
                                                        fields: ["statusValue", "statusName"],
                                                        data: transaction_status_data
                                                    }),
                                                    listWidth: 120,
                                                    width: 120,			  
                                                    mode: 'local',
                                                    displayField: 'statusName',
                                                    valueField: 'statusValue',
                                                    triggerAction: 'all',
                                                    editable: false,
                                                    fieldLabel: 'Status',
                                                    name: 'status',
                                                    hiddenName:'status'
                                                },{
                                                    xtype:"datefield",
                                                    fieldLabel:"Transaction Time",
                                                    name:"transactionTime"
                                                }]
                                          },{
                                            columnWidth:0.5,
                                            layout:"form",
                                            border:false,
                                            defaults:{
                                                width:200
                                            },
                                            items:[{
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
                                                    xtype:'combo',
                                                    store: new Ext.data.SimpleStore({
                                                        fields: ["countryValue", "countryName"]
                                                        //data: countries
                                                    }),
                                                    listWidth: 160,
                                                    width: 160,			  
                                                    mode: 'local',
                                                    displayField: 'countryName',
                                                    valueField: 'countryValue',
                                                    triggerAction: 'all',
                                                    editable: false,
                                                    fieldLabel: 'Courtry',
                                                    name: 'payerCountry',
                                                    hiddenName:'payerCountry'
                                          }]
                                        }]
                                    }]
                                })
                                
                                var addOrderTransactionWindow = new Ext.Window({
                                    title: 'Add <?=$_GET['id']?> Transaction' ,
                                    closable:true,
                                    width: 500,
                                    height: 500,
                                    plain:true,
                                    layout: 'fit',
                                    items: add_order_transaction_form,                                           
                                    buttons: [{
                                                text: 'Save and Close',
                                                handler: function(){
                                                    Ext.Ajax.request({
                                                        waitMsg: 'Please wait...',
                                                        url: 'connect.php?moduleId=qo-orders&action=addOrderTransaction',
                                                        params: {
                                                                ordersId: '<?=$_GET['id']?>',
                                                                txnId: add_order_transaction_form.form.findField('txnId').getValue(),
                                                                payeeId: add_order_transaction_form.form.findField('payeeId').getValue(),
                                                                payerId: add_order_transaction_form.form.findField('payerId').getValue(),
                                                                amountCurrency: add_order_transaction_form.form.findField('amountCurrency').getValue(),
                                                                amountValue: add_order_transaction_form.form.findField('amountValue').getValue(),
                                                                status: add_order_transaction_form.form.findField('status').getValue(),
                                                                transactionTime: add_order_transaction_form.form.findField('transactionTime').getValue(),
                                                                payerName: add_order_transaction_form.form.findField('payerName').getValue(),
                                                                payerEmail: add_order_transaction_form.form.findField('payerEmail').getValue(),
                                                                payerAddressLine1: add_order_transaction_form.form.findField('payerAddressLine1').getValue(),
                                                                payerAddressLine2: add_order_transaction_form.form.findField('payerAddressLine2').getValue(),
                                                                payerCity: add_order_transaction_form.form.findField('payerCity').getValue(),
                                                                payerStateOrProvince: add_order_transaction_form.form.findField('payerStateOrProvince').getValue(),
                                                                payerPostalCode: add_order_transaction_form.form.findField('payerPostalCode').getValue(),
                                                                payerCountry: add_order_transaction_form.form.findField('payerCountry').getValue()
                                                        },
                                                        success: function(response){
                                                                var result = eval(response.responseText);
                                                                switch (result) {
                                                                        case 1:
                                                                                orderTransactionStore.reload();
                                                                                addOrderTransactionWindow.hide();
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
                                            },{
                                                text: 'Cancel',
                                                handler: function(){
                                                    addOrderTransactionWindow.hide();
                                                }
                                            }]
                                              
                                })
                                addOrderTransactionWindow.show();
                    }
                },{
                    text: 'Map Transaction',
                    handler: function(){
                            var map_order_transaction_data_store = new Ext.data.JsonStore({
                                root: 'records',
                                totalProperty: 'totalCount',
                                idProperty: 'id',
                                autoLoad:true,
                                fields: ['id', 'payerName', 'payerEmail', 'amountCurrency', 'amountValue', 'transactionTime', 'itemId'],
                                url:'connect.php?moduleId=qo-orders&action=readMapOrderTransaction&id=<?=$_GET['id']?>'
                            });
                                        
                            var map_order_transaction_grid = new Ext.grid.GridPanel({
                                    store: map_order_transaction_data_store,
                                    columns:[{
                                            header: 'Transaction ID',
                                            readOnly: true,
                                            dataIndex: 'id',
                                            width: 110
                                          },{
                                            header: 'Name',
                                            dataIndex: 'payerName',
                                            width: 100
                                          },{
                                            header: 'Email',
                                            dataIndex: 'payerEmail',
                                            width: 120
                                          },{
                                            header: 'Currency',
                                            dataIndex: 'amountCurrency',
                                            width: 50
                                          },{
                                            header: 'Value',
                                            dataIndex: 'amountValue',
                                            width: 50
                                          },{
                                            header: 'Transaction Time',
                                            dataIndex: 'transactionTime',
                                            width: 120
                                          },{
                                             header: 'Item Id',
                                            dataIndex: 'itemId',
                                            width: 180
                                        }],
				    autoHeight: true,
				    selModel: new Ext.grid.RowSelectionModel({}),
				    bbar:[{xtype:'tbtext',text:'Transaction Id : '},{xtype:'textfield',id:'transactionId'},{xtype:'tbbutton',text:'load',handler:function(){map_order_transaction_data_store.reload({params:{transactionId:Ext.getCmp('transactionId').getValue()}});}}] 
                            });
                                                        
                            var map_order_transaction_window = new Ext.Window({
                                title: 'Map <?=$_GET['id']?>',
                                closable:true,
                                width: 750,
                                autoHeight: true,
                                plain:true,
                                layout: 'fit',
                                items: map_order_transaction_grid,
                                buttons: [{
                                    text: 'Map',
                                        handler: function(){
                                            Ext.Ajax.request({
                                                waitMsg: 'Please wait...',
                                                url: 'connect.php?moduleId=qo-orders&action=mapOrderTransaction',
                                                params: {
                                                        ordersId: '<?=$_GET['id']?>',
                                                        transactionId: map_order_transaction_grid.getSelectionModel().getSelected().data['id'],
                                                        amountCurrency: map_order_transaction_grid.getSelectionModel().getSelected().data['amountCurrency'],
                                                        amountValue: map_order_transaction_grid.getSelectionModel().getSelected().data['amountValue']
                                                },
                                                success: function(response){
                                                        var result = eval(response.responseText);
                                                        switch (result) {
                                                            case 1:
                                                                orderTransactionStore.reload();
                                                                map_order_transaction_window.hide();
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
                            map_order_transaction_window.show();
                    }
                },{
                    text: 'Delete Transaction',
                    handler: function(){
                        var deleteOrderTransaction = function(btn){
                            if(btn=='yes'){
                                var selections = orderTransactionGrid.selModel.getSelections();
                                //console.log(selections);
                                //var prez = [];
                                var ids = "";
                                for(i = 0; i< orderTransactionGrid.selModel.getCount(); i++){
                                    //prez.push(selections[i].data.id);
                                    ids += selections[i].data.id + ","
                                }
                                ids = ids.slice(0,-1);
                                Ext.Ajax.request({  
                                    waitMsg: 'Please Wait',
                                    url: 'connect.php?moduleId=qo-orders&action=deleteOrderTransaction', 
                                    params: { 
                                      ids: ids
                                    }, 
                                    success: function(response){
                                        var result=eval(response.responseText);
                                        switch(result){
                                        case 1:  // Success : simply reload
                                          orderTransactionStore.reload();
                                          break;
                                        default:
                                          Ext.MessageBox.alert('Warning','Could not delete the entire selection.');
                                          break;
                                        }
                                    },
                                    failure: function(response){
                                        var result=response.responseText;
                                        Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                    }
                                });
                            }  
                        }
                        if(orderTransactionGrid.selModel.getCount() >= 1){
                            Ext.MessageBox.confirm('Confirmation','Delete those Transactions?', deleteOrderTransaction);
                        } else {
                            Ext.MessageBox.alert('Uh oh...','You can\'t really delete something you haven\'t selected huh?');
                        }
                    }
                }]
            });
             
            var orderShipmentStore = new Ext.data.JsonStore({
                root: 'records',
                totalProperty: 'totalCount',
                idProperty: 'id',
                autoLoad:true,
                fields: ['id', 'createdOn', 'packedOn', 'shippedOn','status'],
                url:'connect.php?moduleId=qo-orders&action=getOrderShipment&id=<?=$_GET['id']?>'
            });
            
            var orderShipmentGrid = new Ext.grid.EditorGridPanel({
                autoHeight: true,
                store: orderShipmentStore,
                selModel: new Ext.grid.RowSelectionModel({}),
                columns:[{
                    header: "Shipment ID",
                    dataIndex: 'id',
                    width: 100,
                    align: 'center',
                    sortable: true
                },{
                    header: "created On",
                    dataIndex: 'createdOn',
                    width: 120,
                    align: 'center'
                },{
                    header: "packed On",
                    dataIndex: 'packedOn',
                    width: 120,
                    align: 'center',
                    sortable: true
                },{
                    header: "shipped On",
                    dataIndex: 'shippedOn',
                    width: 120,
                    align: 'center',
                    sortable: true
                },{
                    header: "Status",
                    dataIndex: 'status',
                    width: 50,
                    align: 'center',
                    sortable: true
                }],
                bbar: [{
                        text: 'Create Shipment',
                        //tooltip: 'Great tooltips...',
                        handler: function(){
                            Ext.Msg.show({
                                title:'Create Shipment?',
                                msg: ' Would you like to create shipment?',
                                buttons: Ext.Msg.YESNO,
                                fn: function(btn,text){ 
                                    if(btn=='yes')
                                        Ext.Ajax.request({
                                            waitMsg: 'Please wait...',
                                            url: 'connect.php?moduleId=qo-orders&action=addOrderShipment&id=<?$_GET['id']?>',
                                            success: function(response){
                                                var result = eval(response.responseText);
                                                if(result=='1')
                                                {
                                                    Ext.MessageBox.alert('Attention','Shipment Create Successfully!');																oStore.reload();
                                                }else{
                                                    Ext.MessageBox.alert('Attention','Shipment Create Failed!');
                                                }      
                                            },
                                            failure: function(response){
                                                Ext.MessageBox.alert('Attention', 'could not connect to the server. retry later');
                                            }
                                        });},
                                animEl: 'elId',
                                icon: Ext.MessageBox.QUESTION
                            });
                        }
                        },{text:'Delete Shipment',
                            handler: function(){
                                    var deleteOrderShipment = function(btn){
                                        if(btn=='yes'){
                                            var selections = orderShipmentGrid.selModel.getSelections();
                                            var ids = "";
                                            for(i = 0; i< orderShipmentGrid.selModel.getCount(); i++){
                                                ids += selections[i].data.id + ","
                                            }
                                            ids = ids.slice(0,-1);
                                            Ext.Ajax.request({  
                                                waitMsg: 'Please Wait',
                                                url: 'connect.php?moduleId=qo-orders&action=deleteOrderShipment', 
                                                params: { 
                                                  ids: ids
                                                }, 
                                                success: function(response){
                                                    var result=eval(response.responseText);
                                                    switch(result){
                                                    case 1:  // Success : simply reload
                                                      orderShipmentStore.reload();
                                                      break;
                                                    default:
                                                      Ext.MessageBox.alert('Warning','Could not delete the entire selection.');
                                                      break;
                                                    }
                                                },
                                                failure: function(response){
                                                    var result=response.responseText;
                                                    Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                }
                                            });
                                        }  
                                    }
                        
                                if(orderShipmentGrid.selModel.getCount() >= 1){
                                    Ext.MessageBox.confirm('Confirmation','Delete those Shipments?', deleteOrderShipment);
                                } else {
                                    Ext.MessageBox.alert('Attention','You can\'t delete shipments because you haven\'t selected !');
                                }
                            }}]	
            });
                                    
            var orderDetailForm = new Ext.FormPanel({
                    autoScroll:true,
                    reader:new Ext.data.JsonReader({
                        }, ['id','createdOn','sellerId','ebayName','ebayEmail','ebayAddress1','ebayAddress2','ebayCity','ebayStateOrProvince',
                            'ebayPostalCode','ebayCountry','ebayPhone','paypalName','paypalEmail','paypalAddress1','paypalAddress2',
                            'paypalCity','paypalStateOrProvince','paypalPostalCode','paypalCountry','paypalPhone','status','grandTotalCurrency','grandTotalValue',
                            'shippingFeeCurrency','shippingFeeValue','insuranceCurrency','insuranceValue','discountCurrency','discountValue'
                    ]),
                    items:[{
                        xtype:"textfield",
                        fieldLabel:"订单号",
                        name:"id"
                      },{
                        xtype:"textfield",
                        fieldLabel:"创建时间",
                        name:"createdOn"
                      },{
                        xtype:"combo",
                        fieldLabel:"卖家帐号",
                        name:"sellerId",
                        hiddenName:"combovalue"
                      },{
                        layout:"column",
                        items:[{
                            title:"eBay 地址",
                            columnWidth:0.5,
                            layout:"form",
                            defaults:{
                                width:200
                            },
                            items:[{
                                xtype:"textfield",
                                fieldLabel:"帐号",
                                name:"ebayName"
                              },{
                                xtype:"textfield",
                                fieldLabel:"邮件地址",
                                name:"ebayEmail"
                              },{
                                xtype:"textfield",
                                fieldLabel:"地址一",
                                name:"ebayAddress1"
                              },{
                                xtype:"textfield",
                                fieldLabel:"地址二",
                                name:"ebayAddress2"
                              },{
                                xtype:"textfield",
                                fieldLabel:"城市",
                                name:"ebayCity"
                              },{
                                xtype:"textfield",
                                fieldLabel:"州/省",
                                name:"ebayStateOrProvince"
                              },{
                                xtype:"textfield",
                                fieldLabel:"邮政编码",
                                name:"ebayPostalCode"
                              },{
                                xtype:"textfield",
                                fieldLabel:"国家",
                                name:"ebayCountry"
                              },{
                                xtype:"textfield",
                                fieldLabel:"电话",
                                name:"ebayPhone"
                              }]
                          },{
                            title:"PayPal 地址",
                            columnWidth:0.5,
                            layout:"form",
                            defaults:{
                                width:200
                            },
                            items:[{
                                xtype:"textfield",
                                fieldLabel:"帐号",
                                name:"paypalName"
                              },{
                                xtype:"textfield",
                                fieldLabel:"邮件地址",
                                name:"paypalEmail"
                              },{
                                xtype:"textfield",
                                fieldLabel:"地址一",
                                name:"paypalAddress1"
                              },{
                                xtype:"textfield",
                                fieldLabel:"地址二",
                                name:"paypalAddress2"
                              },{
                                xtype:"textfield",
                                fieldLabel:"城市",
                                name:"paypalCity"
                              },{
                                xtype:"textfield",
                                fieldLabel:"州/省",
                                name:"paypalStateOrProvince"
                              },{
                                xtype:"textfield",
                                fieldLabel:"邮政编码",
                                name:"paypalPostalCode"
                              },{
                                xtype:"textfield",
                                fieldLabel:"国家",
                                name:"paypalCountry"
                              },{
                                xtype:"textfield",
                                fieldLabel:"电话",
                                name:"paypalPhone"
                              }]
                          }]
                      },{
                        xtype:"combo",
                        fieldLabel:"状态",
                        name:"status",
                        hiddenName:"status"
                      },{
                        layout:"column",
                        items:[{
                            title:"运费",
                            columnWidth:0.25,
                            layout:"form",
                            labelWidth:40,
                            defaults:{
                                width:80
                            },
                            items:[{
                                xtype:"combo",
                                fieldLabel:"货币",
                                name:"shippingFeeCurrency",
                                hiddenName:"shippingFeeCurrency"
                              },{
                                xtype:"textfield",
                                fieldLabel:"值",
                                name:"shippingFeeValue"
                              }]
                          },{
                            title:"保险",
                            columnWidth:0.25,
                            layout:"form",
                            labelWidth:40,
                            defaults:{
                                width:80
                            },
                            items:[{
                                xtype:"combo",
                                fieldLabel:"货币",
                                name:"insuranceCurrency",
                                hiddenName:"insuranceCurrency"
                              },{
                                xtype:"textfield",
                                fieldLabel:"值",
                                name:"insuranceValue"
                              }]
                          },{
                            title:"优惠",
                            columnWidth:0.25,
                            layout:"form",
                            labelWidth:40,
                            defaults:{
                                width:80
                            },
                            items:[{
                                xtype:"combo",
                                fieldLabel:"货币",
                                name:"discountCurrency",
                                hiddenName:"discountCurrency"
                              },{
                                xtype:"textfield",
                                fieldLabel:"值",
                                name:"discountValue"
                              }]
                          },{
                            title:"总金额",
                            columnWidth:0.25,
                            layout:"form",
                            labelWidth:40,
                            defaults:{
                                width:80
                            },
                            items:[{
                                xtype:"combo",
                                fieldLabel:"货币",
                                name:"grandTotalCurrency",
                                hiddenName:"grandTotalCurrency"
                              },{
                                xtype:"textfield",
                                fieldLabel:"值",
                                name:"grandTotalValue"
                              }]
                          }]
                    },{
                        xtype: 'panel',
                        title: "Order Detail",
                        autoHeight: true,
                        items: orderDetailGrid
                    },{
                        xtype: 'panel',
                        title: "Transaction",
                        autoHeight: true,
                        items: orderTransactionGrid
                    },{
                        xtype: 'panel',
                        title: "Shipment",
                        autoHeight: true,
                        items: orderShipmentGrid
                    }
                    ],
                    buttons: [{
                        text: 'Save',
                        handler: function(){
                            Ext.Ajax.request({
                                waitMsg: 'Please wait...',
                                url: 'connect.php?moduleId=qo-orders&action=saveOrderInfo',
                                params: {
                                        id: '<?=$_GET['id']?>',
                                        sellerId: orderDetailForm.form.findField('sellerId').getValue(),
                                        ebayName: orderDetailForm.form.findField('ebayName').getValue(),
                                        ebayEmail: orderDetailForm.form.findField('ebayEmail').getValue(),
                                        ebayAddress1: orderDetailForm.form.findField('ebayAddress1').getValue(),
                                        ebayAddress2: orderDetailForm.form.findField('ebayAddress2').getValue(),
                                        ebayCity: orderDetailForm.form.findField('ebayCity').getValue(),
                                        ebayStateOrProvince: orderDetailForm.form.findField('ebayStateOrProvince').getValue(),
                                        ebayPostalCode: orderDetailForm.form.findField('ebayPostalCode').getValue(),
                                        ebayCountry: orderDetailForm.form.findField('ebayCountry').getValue(),
                                        ebayPhone: orderDetailForm.form.findField('ebayPhone').getValue(),
                                        paypalName: orderDetailForm.form.findField('paypalName').getValue(),
                                        paypalEmail: orderDetailForm.form.findField('paypalEmail').getValue(),
                                        paypalAddress1: orderDetailForm.form.findField('paypalAddress1').getValue(),
                                        paypalAddress2: orderDetailForm.form.findField('paypalAddress2').getValue(),
                                        paypalCity: orderDetailForm.form.findField('paypalCity').getValue(),
                                        paypalStateOrProvince: orderDetailForm.form.findField('paypalStateOrProvince').getValue(),
                                        paypalPostalCode: orderDetailForm.form.findField('paypalPostalCode').getValue(),
                                        paypalCountry: orderDetailForm.form.findField('paypalCountry').getValue(),
                                        paypalPhone: orderDetailForm.form.findField('paypalPhone').getValue(),
                                        status: orderDetailForm.form.findField('status').getValue(),
                                        shippingFeeCurrency: orderDetailForm.form.findField('shippingFeeCurrency').getValue(),
                                        shippingFeeValue: orderDetailForm.form.findField('shippingFeeValue').getValue(),
                                        insuranceCurrency: orderDetailForm.form.findField('insuranceCurrency').getValue(),
                                        insuranceValue: orderDetailForm.form.findField('insuranceValue').getValue(),
                                        discountCurrency: orderDetailForm.form.findField('discountCurrency').getValue(),
                                        discountValue: orderDetailForm.form.findField('discountValue').getValue()
                                },
                                success: function(response){
                                        var result = eval(response.responseText);
                                        switch (result) {
                                            case 1:
                                                 Ext.MessageBox.alert('Success', 'save Order Info success!');
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
                    },{
                        text: 'Close',
                        handler: function(){
                              
                        }
                    }]
                    
            })

            orderDetailForm.getForm().load({url:'connect.php?moduleId=qo-orders&action=getOrderInfo', 
                   method:'GET', 
                   params: {id: '<?=$_GET['id']?>'}, 
                   waitMsg:'Please wait...'
                   }
            );
            orderDetailForm.render(document.body);
    </script>
</body>
</html>