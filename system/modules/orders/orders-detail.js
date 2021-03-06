Ext.onReady(function(){
            var orderDetailGridStore = new Ext.data.JsonStore({
                root: 'records',
                totalProperty: 'totalCount',
                idProperty: 'id',
                autoLoad:true,
                fields: ['id','itemId', 'itemTitle', 'skuId', 'quantity', 'skuStock', 'unitPriceCurrency', 'unitPriceValue', 'complaintsStatus'],
                url:'connect.php?moduleId=qo-orders&action=getOrderDetail&id='+ordersId
            });
            
            function renderComplaintsStatus(v, p, r){
                var x = ['N', 'Y'];
                return x[v];
            }
            
            var orderDetailGrid = new Ext.grid.EditorGridPanel({
                autoHeight: true,
                store: orderDetailGridStore,
                selModel: new Ext.grid.RowSelectionModel({}),
                columns:[{
                    header: "Item Id",
                    dataIndex: 'itemId',
                    width: 100,
                    align: 'center',
                    editor: new Ext.form.TextField({}),
                    sortable: true
                },{
                    header: "Item Title",
                    dataIndex: 'itemTitle',
                    width: 350,
                    editor: new Ext.form.TextField({}),
                    align: 'center'
                },{
                    header: "SKU",
                    dataIndex: 'skuId',
                    width: 160,
                    align: 'center',
                    sortable: true
                },{
                    header: "Quantity",
                    dataIndex: 'quantity',
                    width: 60,
                    align: 'center',
                    sortable: true
                },{
                    header: "Stock",
                    dataIndex: 'skuStock',
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
                             data: lang.orders.currency
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
                },{
                    header: "Complaints",
                    dataIndex: 'complaintsStatus',
                    renderer: renderComplaintsStatus,
                    width: 90,
                    align: 'center',
                    sortable: true     
                }],
                bbar: [{
                        text: 'Add Detail',
                        disabled: (get_cookie('qo-orders.addOrderDetail') == 0)?true:false,
                        handler: function(){
                            var add_order_detail_form = new Ext.FormPanel({
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
                                                            data: lang.orders.currency
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
                                                    },{
                                                    xtype: 'numberfield',
                                                    name: 'quantity',
                                                    allowBlank: false,
                                                    fieldLabel: 'Quantity',
                                                    width: 80
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
                                                    //allowBlank: false,
                                                    fieldLabel: 'sku Title'
                                                    },{
                                                    xtype: 'textfield',
                                                    name: 'unitPriceValue',
                                                    allowBlank: false,
                                                    fieldLabel: 'Unit Price'
                                                },{
                                                    xtype: 'textfield',
                                                    name: 'barCode',
                                                    //allowBlank: false,
                                                    fieldLabel: 'Bar Code'
                                            }]
                                           }]
                                    }]
                            })
                            
                            var addOrderDetailWindow = new Ext.Window({
                                title: 'Add '+ordersId+' Detail' ,
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
                                                    ordersId: ordersId,
                                                    itemId: add_order_detail_form.form.findField('itemId').getValue(),
                                                    itemTitle: add_order_detail_form.form.findField('itemTitle').getValue(),
                                                    skuId: add_order_detail_form.form.findField('skuId').getValue(),
                                                    skuTitle: add_order_detail_form.form.findField('skuTitle').getValue(),
                                                    quantity: add_order_detail_form.form.findField('quantity').getValue(),
                                                    barCode: add_order_detail_form.form.findField('barCode').getValue(),
                                                    unitPriceCurrency: add_order_detail_form.form.findField('unitPriceCurrency').getValue(),
                                                    unitPriceValue: add_order_detail_form.form.findField('unitPriceValue').getValue()
                                            },
                                            success: function(response){
                                                var result = eval(response.responseText);
                                                switch (result) {
                                                    case 1:
                                                        orderDetailGridStore.reload();
                                                        addOrderDetailWindow.close();
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
                                          addOrderDetailWindow.close();
                                    }
                                }]
                            });
                            addOrderDetailWindow.show();
                        }
                        },'-',{
                                    text: 'Revise Detail',
                                    disabled: (get_cookie('qo-orders.updateOrderDetailInfo') == 0)?true:false,
                                    handler: function(){
                                                var selections = orderDetailGrid.selModel.getSelections();
                                                //console.log(selections[0].data.id);
                                                var revise_order_detail_form = new Ext.FormPanel({
                                                            reader:new Ext.data.JsonReader({
                                                            }, ['id','itemId','itemTitle','skuId','skuTitle','unitPriceValue','unitPriceCurrency','quantity'
                                                            ]),
                                                            labelAlign: 'top',
                                                            bodyStyle:'padding:5px',     
                                                            items: [{
                                                                        xtype:'hidden',
                                                                        name:'id'
                                                            },{
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
                                                                                        data: lang.orders.currency
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
                                                                                },{
                                                                                xtype: 'numberfield',
                                                                                name: 'quantity',
                                                                                allowBlank: false,
                                                                                fieldLabel: 'Quantity',
                                                                                width: 80
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
                                                                                //allowBlank: false,
                                                                                fieldLabel: 'sku Title'
                                                                                },{
                                                                                xtype: 'textfield',
                                                                                name: 'unitPriceValue',
                                                                                allowBlank: false,
                                                                                fieldLabel: 'Unit Price'
                                                                            },{
                                                                                xtype: 'textfield',
                                                                                name: 'barCode',
                                                                                //allowBlank: false,
                                                                                fieldLabel: 'Bar Code'
                                                                        }]
                                                                       }]
                                                                }]
                                                            })
                                                
                                                            revise_order_detail_form.getForm().load({url:'connect.php?moduleId=qo-orders&action=getOrderDetailInfo', 
                                                                        method:'GET', 
                                                                        params: {id: selections[0].data.id}, 
                                                                        waitMsg:'Please wait...'
                                                            }
                                                );
                                                 
                                                var reviseOrderDetailWindow = new Ext.Window({
                                                            title: 'Revise '+ordersId+' Detail' ,
                                                            closable:true,
                                                            width: 400,
                                                            height: 300,
                                                            plain:true,
                                                            layout: 'fit',
                                                            items: revise_order_detail_form,
                                                            
                                                            buttons: [{
                                                                text: 'Save and Close',
                                                                handler: function(){
                                                                    Ext.Ajax.request({
                                                                        waitMsg: 'Please wait...',
                                                                        url: 'connect.php?moduleId=qo-orders&action=updateOrderDetailInfo',
                                                                        params: {
                                                                                id: selections[0].data.id,
                                                                                itemId: revise_order_detail_form.form.findField('itemId').getValue(),
                                                                                itemTitle: revise_order_detail_form.form.findField('itemTitle').getValue(),
                                                                                skuId: revise_order_detail_form.form.findField('skuId').getValue(),
                                                                                skuTitle: revise_order_detail_form.form.findField('skuTitle').getValue(),
                                                                                quantity: revise_order_detail_form.form.findField('quantity').getValue(),
                                                                                barCode: revise_order_detail_form.form.findField('barCode').getValue(),
                                                                                unitPriceCurrency: revise_order_detail_form.form.findField('unitPriceCurrency').getValue(),
                                                                                unitPriceValue: revise_order_detail_form.form.findField('unitPriceValue').getValue()
                                                                        },
                                                                        success: function(response){
                                                                            var result = eval(response.responseText);
                                                                            switch (result) {
                                                                                case 1:
                                                                                    orderDetailGridStore.reload();
                                                                                    reviseOrderDetailWindow.close();
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
                                                                      reviseOrderDetailWindow.close();
                                                                }
                                                            }]
                                                });
                                                reviseOrderDetailWindow.show();
                                    }
                        }/*,{
                                    text: 'Split Items',
                                    handler: function(){
                                                if(orderDetailGrid.selModel.getCount() == 0){
                                                            Ext.MessageBox.alert('Warning','Please choice need split ITEM.'); 
                                                }else{
                                                            var splitOrderDetail = function(btn){
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
                                                                                        url: 'connect.php?moduleId=qo-orders&action=splitOrderDetail', 
                                                                                        params: { 
                                                                                          //ids:  encoded_array
                                                                                          ids: ids
                                                                                        }, 
                                                                                        success: function(response){
                                                                                                Ext.MessageBox.alert('Notification', response.responseText);
                                                                                        },
                                                                                        failure: function(response){
                                                                                            var result=response.responseText;
                                                                                            Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                                                        }
                                                                                    });      
                                                                        }
                                                            }
                                                            Ext.MessageBox.confirm('Confirmation', 'Split these items?', splitOrderDetail);
                                                }
                                    }
                        }*/,'-',{
                                    text: '<font color="red"><b>Complaints SKU</b></font>',
                                    handler: function(){
                                                var selections = orderDetailGrid.selModel.getSelections();
                                                //console.log(selections[0].data.complaintsStatus);
                                                if(selections[0].data.complaintsStatus == 1){
                                                            Ext.MessageBox.alert('Warning','SKU have been complaints.');
                                                            return 0;
                                                }
                                                if(orderDetailGrid.selModel.getCount() >= 1){
                                                    //Ext.MessageBox.confirm('Confirmation','Complaints this sku?', complaintsSku);
                                                    Ext.Msg.prompt('Complaints this sku', 'Please enter content:', function(btn, text){
                                                            if (btn == 'ok'){
                                                                //console.log(text);
                                                                var selections = orderDetailGrid.selModel.getSelections();
                                                                //console.log(selections);
                                                                //var prez = [];
                                                                var skus = selections[0].data.skuId;
                                                                /*
                                                                for(i = 0; i< orderDetailGrid.selModel.getCount(); i++){
                                                                    //prez.push(selections[i].data.id);
                                                                    //console.log(selections[i].data);
                                                                    skus += selections[i].data.skuId + ","
                                                                }
                                                                skus = skus.slice(0,-1);
                                                                */
                                                                //console.log(skus);
                                                                //var encoded_array = Ext.encode(prez);
                                                                Ext.Ajax.request({  
                                                                    waitMsg: 'Please Wait',
                                                                    url: 'service.php?action=complaints', 
                                                                    params: { 
                                                                      ordersDetailId:  selections[0].data.id,
                                                                      sku: skus,
                                                                      content: text
                                                                    }, 
                                                                    success: function(response){
                                                                        var result=eval(response.responseText);
                                                                        switch(result){
                                                                           case 1:  // Success : simply reload
                                                                                orderDetailGridStore.reload();
                                                                           break;
                                                                           default:
                                                                                Ext.MessageBox.alert('Warning','Please notice admin.');
                                                                           break;
                                                                        }
                                                                    },
                                                                    failure: function(response){
                                                                        var result=response.responseText;
                                                                        Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                                    }
                                                                });
                                                            }
                                                    });
                                                } else {
                                                    Ext.MessageBox.alert('Uh oh...','Please select a sku.');
                                                }
                                    }
                        }/*{
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
                        },{
                                    text: 'Complaint Sku',
                                    handler: function(){
                                                
                                    }
                        }*/
                    ]
            });
                      
            
            var orderTransactionStore = new Ext.data.JsonStore({
                root: 'records',
                totalProperty: 'totalCount',
                idProperty: 'id',
                autoLoad:true,
                fields: ['id', 'txnId', 'amountCurrency', 'amountValue', 'status', 'transactionTime', 'transactionReason'],
                url:'connect.php?moduleId=qo-orders&action=getOrderTransaction&id='+ordersId
            });
            
            function renderTransactionStatus(v, p, r){
                return lang.orders.transactions_status_json[v];
            }
            
            function renderShipmentStatus(v, p, r){
                return lang.orders.shipments_status_json[v];
            }
            
            function renderTransactionReason(v, p, r){
                return lang.orders.transactions_reason_json[v];       
            }
            
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
                            data: lang.orders.currency
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
                    header: "Reason",
                    dataIndex: 'transactionReason',
                    renderer: renderTransactionReason,
                    width: 180,
                    align: 'center',
                    sortable: true
                },{
                    header: "Status",
                    dataIndex: 'status',
                    renderer: renderTransactionStatus,
                    width: 120,
                    align: 'center',
                    sortable: true
                }],
                bbar: [{
                    text: 'Add Transaction',
                    //tooltip: 'Great tooltips...',
                    disabled: (get_cookie('qo-orders.addOrderTransaction') == 0)?true:false,
                    handler: function(){
                                var add_order_transaction_form = new Ext.FormPanel({
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
                                                        /*
                                                            xtype:'combo',
                                                            store: new Ext.data.SimpleStore({
                                                                fields: ["id", "name"],
                                                                data: lang.orders.currency
                                                            }),
                                                            listWidth: 50,
                                                            width: 50,			  
                                                            mode: 'local',
                                                            valueField: 'id',
                                                            displayField: 'name',
                                                            triggerAction: 'all',
                                                            editable: false,
                                                            selectOnFocus:true,
                                                            fieldLabel: 'Amount',
                                                            id: 'amountCurrency',
                                                            name: 'amountCurrency',
                                                            hiddenName:'amountCurrency'
                                                        */   
                                                            xtype:'combo',
                                                            store: new Ext.data.SimpleStore({
                                                                fields: ["amountCurrencyValue", "amountCurrencyName"],
                                                                data: lang.orders.currency
                                                            }),
                                                            listWidth: 60,
                                                            width: 60,			  
                                                            mode: 'local',
                                                            displayField: 'amountCurrencyName',
                                                            valueField: 'amountCurrencyValue',
                                                            triggerAction: 'all',
                                                            editable: false,
                                                            fieldLabel: 'Amount',
                                                            id: 'amountCurrency',
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
                                                            id: 'amountValue',
                                                            name:"amountValue",
                                                            labelSeparator:""
                                                          }]
                                                    }]
                                                },{
                                                    xtype:'combo',
                                                    store: new Ext.data.SimpleStore({
                                                        fields: ["statusValue", "statusName"],
                                                        data: lang.orders.transactions_status
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
                                                    xtype: 'combo',
                                                    fieldLabel:"Country",
                                                    listWidth: 160,
                                                    width: 160,	
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
                                                    name: 'payerCountry',
                                                    hiddenName:'payerCountry'
                                          }]
                                        }]
                                    }]
                                })
                                
                                var addOrderTransactionWindow = new Ext.Window({
                                    title: 'Add '+ordersId+' Transaction' ,
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
                                                                ordersId: ordersId,
                                                                txnId: add_order_transaction_form.form.findField('txnId').getValue(),
                                                                payeeId: add_order_transaction_form.form.findField('payeeId').getValue(),
                                                                payerId: add_order_transaction_form.form.findField('payerId').getValue(),
                                                                amountCurrency: document.getElementById('amountCurrency').value,
                                                                amountValue: document.getElementById('amountValue').value,
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
                                                                                addOrderTransactionWindow.close();
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
                                                    addOrderTransactionWindow.close();
                                                }
                                            }]
                                              
                                })
                                addOrderTransactionWindow.show();
                    }
                },'-',{
                    text: 'Map Transaction',
                    disabled: (get_cookie('qo-orders.mapOrderTransaction') == 0)?true:false,
                    handler: function(){
                            var map_order_transaction_data_store = new Ext.data.JsonStore({
                                root: 'records',
                                totalProperty: 'totalCount',
                                idProperty: 'id',
                                autoLoad:true,
                                fields: ['id', 'payerName', 'payerEmail', 'amountCurrency', 'amountValue', 'transactionTime', 'itemId'],
                                url:'connect.php?moduleId=qo-orders&action=readMapOrderTransaction&id='+ordersId
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
                                title: 'Map '+ordersId,
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
                                                        ordersId: ordersId,
                                                        transactionId: map_order_transaction_grid.getSelectionModel().getSelected().data['id'],
                                                        amountCurrency: map_order_transaction_grid.getSelectionModel().getSelected().data['amountCurrency'],
                                                        amountValue: map_order_transaction_grid.getSelectionModel().getSelected().data['amountValue']
                                                },
                                                success: function(response){
                                                        var result = eval(response.responseText);
                                                        switch (result) {
                                                            case 1:
                                                                orderTransactionStore.reload();
                                                                map_order_transaction_window.close();
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
                },'-',{
                    text: 'Delete Transaction',
                    disabled: (get_cookie('qo-orders.deleteOrderTransaction') == 0)?true:false,
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
                },'-',{
                        xtype: 'combo',
                        //fieldLabel: "Reason",
                        store: new Ext.data.SimpleStore({
                            fields: ["id", "name"],
                            data: lang.orders.transaction_reason
                        }),		  
                        mode: 'local',
                        valueField: 'id',
                        displayField: 'name',
                        triggerAction: 'all',
                        editable: false,
                        name: 'transactionReason',
                        hiddenName:'transactionReason',
                        listeners: {
                                    select: function(c, r, i){
                                                if(Ext.isEmpty(get_cookie('qo-orders.addOrderRefund'))){
                                                      Ext.getCmp("addOrderRefund").enable();    
                                                }
                                    }
                        }
                },{
                        id: "addOrderRefund",
                        text: 'Creat Refund',
                        disabled: true,
                        //disabled: (get_cookie('qo-orders.addOrderRefund') == 0)?true:false,
                        handler: function(){
                                    var add_order_refund_form = new Ext.FormPanel({
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
                                                                                    id: 'refundPayerEmail',
                                                                                    xtype:"textfield",
                                                                                    fieldLabel:"Email",
                                                                                    name:"refundPayerEmail",
                                                                                    allowBlank:false
                                                                        }]
                                                            },{
                                                                        columnWidth:0.5,
                                                                        layout:"form",
                                                                        border:false,
                                                                        defaults:{
                                                                            width:200
                                                                        },
                                                                        items:[{
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
                                                                                                    data: lang.orders.currency
                                                                                                }),
                                                                                                listWidth: 60,
                                                                                                width: 60,			  
                                                                                                mode: 'local',
                                                                                                displayField: 'amountCurrencyName',
                                                                                                valueField: 'amountCurrencyValue',
                                                                                                triggerAction: 'all',
                                                                                                editable: false,
                                                                                                fieldLabel: 'Amount',
                                                                                                id: 'refundAmountCurrency',
                                                                                                name: 'refundAmountCurrency',
                                                                                                hiddenName:'refundAmountCurrency',
                                                                                                allowBlank:false
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
                                                                                                id: 'refundAmountValue',
                                                                                                name:"refundAmountValue",
                                                                                                labelSeparator:"",
                                                                                                allowBlank:false
                                                                                              }]
                                                                                        }]
                                                                        }]            
                                                            }]
                                                }]
                                    })
                                    
                                    var toolbar = orderTransactionGrid.getBottomToolbar();
                                    //console.log(toolbar);
                                    var addOrderRefundWindow = new Ext.Window({
                                                title: 'Add '+ordersId+' Refund' ,
                                                closable:true,
                                                width: 500,
                                                height: 200,
                                                plain:true,
                                                layout: 'fit',
                                                items: add_order_refund_form,                                           
                                                buttons: [{
                                                            text: 'Save and Close',
                                                            handler: function(){
                                                                if(add_order_refund_form.getForm().isValid()){
                                                                        Ext.Ajax.request({
                                                                            waitMsg: 'Please wait...',
                                                                            url: 'connect.php?moduleId=qo-orders&action=addOrderRefund',
                                                                            params: {
                                                                                    ordersId: ordersId,
                                                                                    transactionReason: toolbar.items.items[6].value,
                                                                                    payerEmail: document.getElementById('refundPayerEmail').value,
                                                                                    amountCurrency: document.getElementById('refundAmountCurrency').value,
                                                                                    amountValue: document.getElementById('refundAmountValue').value
                                                                            },
                                                                            success: function(response){
                                                                                    var result = eval(response.responseText);
                                                                                    switch (result) {
                                                                                            case 1:
                                                                                                    orderTransactionStore.reload();
                                                                                                    addOrderRefundWindow.close();
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
                                                                }else{
                                                                        Ext.MessageBox.alert('warning', 'please fill form.');
                                                                }
                                                            }
                                                            },{
                                                                        text: 'Cancel',
                                                                        handler: function(){
                                                                            addOrderRefundWindow.close();
                                                                        }
                                                            }]
                                    })
                                    addOrderRefundWindow.show();
                        }
                }]
            });
             
            var orderShipmentStore = new Ext.data.JsonStore({
                root: 'records',
                totalProperty: 'totalCount',
                idProperty: 'id',
                autoLoad:true,
                fields: ['id', 'createdOn', 'packedOn', 'shippedOn','status'],
                url:'connect.php?moduleId=qo-orders&action=getOrderShipment&id='+ordersId
            });
            
            orderTransactionGrid.on("rowdblclick", function(oGrid){
                var oRecord = oGrid.getSelectionModel().getSelected();
                window.open("/eBayBO/transactions.php?id="+oRecord.data['id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");
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
                    renderer: renderShipmentStatus,
                    width: 150,
                    align: 'center',
                    sortable: true
                }],
                bbar: [{
                                    xtype: 'combo',
                                    //fieldLabel: "Reason",
                                    store: new Ext.data.SimpleStore({
                                        fields: ["id", "name"],
                                        data: lang.orders.shipment_reason
                                    }),		  
                                    mode: 'local',
                                    valueField: 'id',
                                    displayField: 'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    //id: 'shipmentReason',
                                    name: 'shipmentReason',
                                    hiddenName:'shipmentReason',
                                    listeners: {
                                    select: function(c, r, i){
                                                if(Ext.isEmpty(get_cookie('qo-orders.addOrderShipment'))){
                                                      Ext.getCmp("addOrderShipment").enable();    
                                                }
                                    }
                        }
                        },{
                        id: 'addOrderShipment',
                        text: 'Create Shipment',
                        //tooltip: 'Great tooltips...',
                        disabled: true,
                        //disabled: (get_cookie('qo-orders.addOrderShipment') == 0)?true:false,
                        handler: function(){
                                    var toolbar = orderShipmentGrid.getBottomToolbar();
                                    if(Ext.isEmpty(toolbar.items.items[0].value)){
                                                Ext.MessageBox.alert('Error', 'Please select Reason.');
                                                return 0; 
                                    }
                            Ext.Msg.show({
                                title:'Create Shipment?',
                                msg: ' Would you like to create shipment?',
                                buttons: Ext.Msg.YESNO,
                                fn: function(btn,text){        
                                    if(btn=='yes'){
                                                            Ext.Ajax.request({
                                                                waitMsg: 'Please wait...',
                                                                url: 'connect.php?moduleId=qo-orders&action=addOrderShipment',
                                                                params: { 
                                                                    id: ordersId,
                                                                    shipmentReason: toolbar.items.items[0].value
                                                                }, 
                                                                success: function(response){
                                                                    var result = eval(response.responseText);
                                                                    if(result=='1')
                                                                    {
                                                                            orderShipmentStore.reload();
                                                                            Ext.MessageBox.alert('Attention','Shipment Create Successfully!');
                                                                    }else{
                                                                            Ext.MessageBox.alert('Attention','Shipment Create Failed!');
                                                                    }      
                                                                },
                                                                failure: function(response){
                                                                    Ext.MessageBox.alert('Attention', 'could not connect to the server. retry later');
                                                                }
                                                            })
                                                }
                                    return 1;
                                    },
                                animEl: 'elId',
                                icon: Ext.MessageBox.QUESTION
                            });
                        }
                        }/*,{
                                    text:'Delete Shipment',
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
                                    }
                        }*/]	
            });
            
            
            
            orderShipmentGrid.on("rowdblclick", function(oGrid){
                var oRecord = oGrid.getSelectionModel().getSelected();
                window.open("/eBayBO/shipments.php?id="+oRecord.data['id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");
	    });
            
            //var beforeLoad = function(F, a){
            Ext.Ajax.request({  
                    waitMsg: 'Please Wait',
                    url: 'connect.php?moduleId=qo-orders&action=getConfigure',
                    success: function(response){
                        var result = Ext.decode(response.responseText);
                        var countries = result.countries;
                        var seller = result.seller;
                        //console.log(seller);
                        var orderDetailForm = new Ext.FormPanel({
                                autoScroll:true,
                                reader:new Ext.data.JsonReader({
                                    }, ['id','createdBy','createdOn','modifiedBy','modifiedOn','sellerId','buyerId','ebayName','ebayEmail','ebayAddress1','ebayAddress2','ebayCity','ebayStateOrProvince',
                                        'ebayPostalCode','ebayCountry','ebayPhone','paypalName','paypalEmail','paypalAddress1','paypalAddress2',
                                        'paypalCity','paypalStateOrProvince','paypalPostalCode','paypalCountry','paypalPhone','status','grandTotalCurrency','grandTotalValue',
                                        'remarks','shippingMethod','shippingFeeCurrency','shippingFeeValue','insuranceCurrency','insuranceValue','discountCurrency','discountValue'
                                ]),
                                items:[{
                                    xtype:"textfield",
                                    fieldLabel:lang.orders.form_orders_id,
                                    readOnly:true,
                                    name:"id"
                                  },{
                                    layout:"table",
                                    layoutConfig:{
                                      columns:3
                                    },
                                    width:320,
                                    border:false,
                                    items:[{
                                        width:105,
                                        html:"<font size=2>"+lang.orders.form_created+"</font>",
                                        border:false
                                      },{
                                        layout:"form",
                                        border:false,
                                        labelWidth:0,
                                        hideLabels:true,
                                        labelSeparator:"",
                                        items:[{
                                            xtype:"textfield",
                                            readOnly:true,
                                            fieldLabel:"",
                                            name:"createdBy",
                                            width:80
                                          }]
                                      },{
                                        layout:"form",
                                        border:false,
                                        labelWidth:0,
                                        hideLabels:true,
                                        labelSeparator:"",
                                        items:[{
                                            xtype:"textfield",
                                            readOnly:true,
                                            fieldLabel:"",
                                            name:"createdOn",
                                            width:125
                                          }]
                                      }]
                                  },{
                                    layout:"table",
                                    layoutConfig:{
                                      columns:3
                                    },
                                    width:320,
                                    border:false,
                                    items:[{
                                        width:105,
                                        html:"<font size=2>"+lang.orders.form_modified+"</font>",
                                        border:false
                                      },{
                                        layout:"form",
                                        border:false,
                                        labelWidth:0,
                                        hideLabels:true,
                                        labelSeparator:"",
                                        items:[{
                                            xtype:"textfield",
                                            readOnly:true,
                                            fieldLabel:"",
                                            name:"modifiedBy",
                                            width:80
                                          }]
                                      },{
                                        layout:"form",
                                        border:false,
                                        labelWidth:0,
                                        hideLabels:true,
                                        labelSeparator:"",
                                        items:[{
                                            xtype:"textfield",
                                            readOnly:true,
                                            fieldLabel:"",
                                            name:"modifiedOn",
                                            width:125
                                          }]
                                      }]
                                  },{
                                    xtype: 'combo',
                                    fieldLabel:lang.orders.form_seller_id,
                                    mode: 'local',
                                    store: new Ext.data.SimpleStore({
                                        fields: ['sellerId', 'sellerName'],
                                        data : seller
                                    }),
                                    valueField:'sellerId',
                                    displayField:'sellerName',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    name: 'sellerId',
                                    hiddenName:'sellerId'
                                  },{
                                    layout:"column",
                                    items:[{
                                        title:lang.orders.form_ebay_address_title,
                                        columnWidth:0.5,
                                        layout:"form",
                                        defaults:{
                                            width:200
                                        },
                                        items:[/*{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_ebay_name,
                                            name:"ebayName"
                                          },*/{
                                                layout:"table",
                                                layoutConfig:{
                                                  columns:3
                                                },
                                                width:320,
                                                border:false,
                                                items:[{
                                                    width:105,
                                                    html:"<font size=2>"+lang.orders.form_ebay_name+"</font>",
                                                    border:false
                                                  },{
                                                    layout:"form",
                                                    border:false,
                                                    labelWidth:0,
                                                    hideLabels:true,
                                                    labelSeparator:"",
                                                    items:[{
                                                        xtype:"textfield",
                                                        readOnly:true,
                                                        fieldLabel:"",
                                                        name:"buyerId",
                                                        width:80
                                                      }]
                                                  },{
                                                    layout:"form",
                                                    border:false,
                                                    labelWidth:0,
                                                    hideLabels:true,
                                                    labelSeparator:"",
                                                    items:[{
                                                        xtype:"textfield",
                                                        readOnly:true,
                                                        fieldLabel:"",
                                                        name:"ebayName",
                                                        width:125
                                                      }]
                                                  }]
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_ebay_email,
                                            name:"ebayEmail"
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_ebay_address1,
                                            name:"ebayAddress1"
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_ebay_address2,
                                            name:"ebayAddress2"
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_ebay_city,
                                            name:"ebayCity"
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_ebay_state,
                                            name:"ebayStateOrProvince"
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_ebay_postal,
                                            name:"ebayPostalCode"
                                          },{
                                            xtype: 'combo',
                                            fieldLabel:lang.orders.form_ebay_country,
                                            mode: 'local',
                                            store: new Ext.data.SimpleStore({
                                                fields: ['countryId', 'countryName'],
                                                data : countries
                                            }),
                                            valueField:'countryId',
                                            displayField:'countryName',
                                            triggerAction: 'all',
                                            editable: false,
                                            selectOnFocus:true,
                                            name: 'ebayCountry',
                                            hiddenName:'ebayCountry'
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_ebay_phone,
                                            name:"ebayPhone"
                                          },{
                                             xtype:"button",
                                             text: "Copy Address Info",
                                             handler: function(){
                                                var eBayAddressInfo = orderDetailForm.getForm().findField("ebayName").getValue()+"\n"+
                                                //orderDetailForm.getForm().findField("ebayEmail").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("ebayAddress1").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("ebayAddress2").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("ebayCity").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("ebayStateOrProvince").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("ebayPostalCode").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("ebayCountry").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("ebayPhone").getValue();
                                                var copyForm = new Ext.FormPanel({
                                                            labelWidth:0,
                                                            hideLabels:true,
                                                            labelSeparator:"",
                                                            items: [{xtype:"textarea",
                                                                     fieldLabel:"",
                                                                     width: 380,
                                                                     height: 280,
                                                                     value:eBayAddressInfo,
                                                                     name: 'eBayAddressInfo'}
                                                            ]
                                                        })
                                                        
                                                var copyWindow = new Ext.Window({
                                                    title: 'eBay Address Info' ,
                                                    closable:true,
                                                    width: 400,
                                                    height: 300,
                                                    plain:true,
                                                    layout: 'fit',
                                                    items: copyForm,
                                                    buttons: [{
                                                        text: 'Close',
                                                        handler: function(){
                                                              copyWindow.close();
                                                        }
                                                    }]
                                                });
                                                copyWindow.show();
                                             }
                                          }]
                                      },{
                                        title:lang.orders.form_paypal_address_title,
                                        columnWidth:0.5,
                                        layout:"form",
                                        defaults:{
                                            width:200
                                        },
                                        items:[{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_paypal_name,
                                            name:"paypalName"
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_paypal_email,
                                            name:"paypalEmail"
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_paypal_address1,
                                            name:"paypalAddress1"
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_paypal_address2,
                                            name:"paypalAddress2"
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_paypal_city,
                                            name:"paypalCity"
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_paypal_state,
                                            name:"paypalStateOrProvince"
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_paypal_postal,
                                            name:"paypalPostalCode"
                                          },{
                                            xtype: 'combo',
                                            fieldLabel:lang.orders.form_ebay_country,
                                            mode: 'local',
                                            store: new Ext.data.SimpleStore({
                                                fields: ['countryId', 'countryName'],
                                                data : countries
                                            }),
                                            valueField:'countryId',
                                            displayField:'countryName',
                                            triggerAction: 'all',
                                            editable: false,
                                            selectOnFocus:true,
                                            name: 'paypalCountry',
                                            hiddenName:'paypalCountry'
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_paypal_phone,
                                            name:"paypalPhone"
                                          },{
                                             xtype:"button",
                                             text: "Copy Address Info",
                                             handler: function(){
                                               var eBayAddressInfo = orderDetailForm.getForm().findField("paypalName").getValue()+"\n"+
                                                //orderDetailForm.getForm().findField("paypalEmail").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("paypalAddress1").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("paypalAddress2").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("paypalCity").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("paypalStateOrProvince").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("paypalPostalCode").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("paypalCountry").getValue()+"\n"+
                                                orderDetailForm.getForm().findField("paypalPhone").getValue();
                                                var copyForm = new Ext.FormPanel({
                                                            labelWidth:0,
                                                            hideLabels:true,
                                                            labelSeparator:"",
                                                            items: [{xtype:"textarea",
                                                                     fieldLabel:"",
                                                                     width: 380,
                                                                     height: 280,
                                                                     value:eBayAddressInfo,
                                                                     name: 'eBayAddressInfo'}
                                                            ]
                                                        })
                                                        
                                                var copyWindow = new Ext.Window({
                                                    title: 'PayPal Address Info' ,
                                                    closable:true,
                                                    width: 400,
                                                    height: 300,
                                                    plain:true,
                                                    layout: 'fit',
                                                    items: copyForm,
                                                    buttons: [{
                                                        text: 'Close',
                                                        handler: function(){
                                                              copyWindow.close();
                                                        }
                                                    }]
                                                });
                                                copyWindow.show();
                                             }
                                          }]
                                      }]
                                  },{
                                             xtype:"button",
                                             text: "Send Email To Buyer",
                                             handler: function(){
                                                var ebayName = orderDetailForm.getForm().findField("ebayName").getValue();
                                                var ebayEmail = orderDetailForm.getForm().findField("ebayEmail").getValue();
                                                var sendEmailForm = new Ext.FormPanel({
                                                            //labelWidth:0,
                                                            //hideLabels:true,
                                                            //labelSeparator:"",
                                                            items: [{
                                                                        xtype:"textfield",
                                                                        fieldLabel:lang.orders.send_to_name,
                                                                        value:ebayName,
                                                                        name:"toName"
                                                                     },{
                                                                        xtype:"textfield",
                                                                        fieldLabel:lang.orders.send_to_email,
                                                                        value:ebayEmail,
                                                                        name:"toEmail"
                                                                     },{xtype:"textarea",
                                                                        fieldLabel:"",
                                                                        width: 380,
                                                                        height: 280,
                                                                        //value:eBayAddressInfo,
                                                                        fieldLabel:lang.orders.send_to_content,
                                                                        name: 'toContent'}
                                                            ]
                                                        })
                                                        
                                                var sendEmailWindow = new Ext.Window({
                                                    title: 'eBay Address Info' ,
                                                    closable:true,
                                                    width: 520,
                                                    height: 410,
                                                    plain:true,
                                                    layout: 'fit',
                                                    items: sendEmailForm,
                                                    buttons: [{
                                                                        text: 'Send',
                                                                        handler: function(){
                                                                                    Ext.Ajax.request({
                                                                                                waitMsg: 'Please wait...',
                                                                                                url: 'service.php?action=sendEmail',
                                                                                                params: {
                                                                                                    toName: sendEmailForm.form.findField('toName').getValue(),
                                                                                                    toEmail: sendEmailForm.form.findField('toEmail').getValue(),
                                                                                                    toContent: sendEmailForm.form.findField('toContent').getValue()
                                                                                                },
                                                                                                success: function(response){
                                                                                                    var result = eval(response.responseText);
                                                                                                    switch (result) {
                                                                                                        case 1:
                                                                                                            Ext.MessageBox.alert('Success', 'Send Email Success!');
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
                                                                                    sendEmailWindow.close();
                                                                        }
                                                    }]
                                                });
                                                sendEmailWindow.show();
                                             }
                                    },{
                                    xtype:"combo",
                                    fieldLabel:lang.orders.form_status,
                                    store: new Ext.data.SimpleStore({
                                        fields: ["statusValue", "statusName"],
                                        data: lang.orders.orders_status
                                    }),
                                    mode: 'local',
                                    displayField: 'statusName',
                                    valueField: 'statusValue',
                                    triggerAction: 'all',
                                    editable: false,
                                    name: 'status',
                                    hiddenName:'status'
                                  },{
                                    xtype:"combo",
                                    fieldLabel:lang.orders.form_shipping_method,
                                    store: new Ext.data.SimpleStore({
                                        fields: ["shippingMethodValue", "shippingMethodName"],
                                        data: lang.orders.shipping_method
                                    }),
                                    mode: 'local',
                                    displayField: 'shippingMethodName',
                                    valueField: 'shippingMethodValue',
                                    triggerAction: 'all',
                                    editable: false,
                                    name: 'shippingMethod',
                                    hiddenName:'shippingMethod'
                                  },{
                                    xtype:"textarea",
                                    fieldLabel:lang.orders.form_remarks,
                                    width: 200,
                                    name: 'remarks'
                                  },{
                                    layout:"column",
                                    items:[{
                                        title:lang.orders.form_shipping_title,
                                        columnWidth:0.25,
                                        layout:"form",
                                        labelWidth:50,
                                        defaults:{
                                            width:80
                                        },
                                        items:[{
                                            xtype:"combo",
                                            fieldLabel:lang.orders.form_currency,
                                            store: new Ext.data.SimpleStore({
                                                fields: ["shippingFeeCurrencyValue", "shippingFeeCurrencyName"],
                                                data: lang.orders.currency
                                            }),
                                            mode: 'local',
                                            displayField: 'shippingFeeCurrencyName',
                                            valueField: 'shippingFeeCurrencyValue',
                                            triggerAction: 'all',
                                            editable: false,
                                            name: 'shippingFeeCurrency',
                                            hiddenName:'shippingFeeCurrency'
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_fee,
                                            name:"shippingFeeValue"
                                          }]
                                      },{
                                        title:lang.orders.form_insurance_title,
                                        columnWidth:0.25,
                                        layout:"form",
                                        labelWidth:50,
                                        defaults:{
                                            width:80
                                        },
                                        items:[{
                                            xtype:"combo",
                                            fieldLabel:lang.orders.form_currency,
                                            store: new Ext.data.SimpleStore({
                                                fields: ["insuranceCurrencyValue", "insuranceCurrencyName"],
                                                data: lang.orders.currency
                                            }),
                                            mode: 'local',
                                            displayField: 'insuranceCurrencyName',
                                            valueField: 'insuranceCurrencyValue',
                                            triggerAction: 'all',
                                            editable: false,
                                            name: 'insuranceCurrency',
                                            hiddenName:'insuranceCurrency'
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_fee,
                                            name:"insuranceValue"
                                          }]
                                      },{
                                        title:lang.orders.form_discount_title,
                                        columnWidth:0.25,
                                        layout:"form",
                                        labelWidth:50,
                                        defaults:{
                                            width:80
                                        },
                                        items:[{
                                            xtype:"combo",
                                            fieldLabel:lang.orders.form_currency,
                                            store: new Ext.data.SimpleStore({
                                                fields: ["discountCurrencyValue", "discountCurrencyName"],
                                                data: lang.orders.currency
                                            }),
                                            mode: 'local',
                                            displayField: 'discountCurrencyName',
                                            valueField: 'discountCurrencyValue',
                                            triggerAction: 'all',
                                            editable: false,
                                            name: 'discountCurrency',
                                            hiddenName:'discountCurrency'
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_fee,
                                            name:"discountValue"
                                          }]
                                      },{
                                        title:lang.orders.form_total_title,
                                        columnWidth:0.25,
                                        layout:"form",
                                        labelWidth:50,
                                        defaults:{
                                            width:80
                                        },
                                        items:[{
                                            xtype:"combo",
                                            fieldLabel:lang.orders.form_currency,
                                            store: new Ext.data.SimpleStore({
                                                fields: ["grandTotalCurrencyValue", "grandTotalCurrencyName"],
                                                data: lang.orders.currency
                                            }),
                                            mode: 'local',
                                            displayField: 'grandTotalCurrencyName',
                                            valueField: 'grandTotalCurrencyValue',
                                            triggerAction: 'all',
                                            editable: false,
                                            name: 'grandTotalCurrency',
                                            hiddenName:'grandTotalCurrency'
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_fee,
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
                                    disabled: (get_cookie('qo-orders.updateOrder') == 0)?true:false,
                                    handler: function(){
                                        orderDetailForm.getForm().submit({
                                            url: "connect.php?moduleId=qo-orders&action=updateOrder",
                                            success: function(f, a){
                                                var response = Ext.decode(a.response.responseText);
                                                if(response.success){
                                                        Ext.Msg.alert('Success', 'Update orders success!');
                                                }else{
                                                        Ext.Msg.alert('Failure', 'Update orders failure!');
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
                                }]
                                
                        })
                                    
                        orderDetailForm.getForm().load({url:'connect.php?moduleId=qo-orders&action=getOrderInfo', 
                                method:'GET', 
                                params: {id: ordersId}, 
                                waitMsg:'Please wait...'
                                }
                        );
                        orderDetailForm.render(document.body);
                    },
                    failure: function(response){
                        var result=response.responseText;
                        Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                    }
                });
            //}
            
            //orderDetailForm.getForm().addListener("beforeaction", beforeLoad);
});