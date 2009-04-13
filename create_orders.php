<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="PRAGMA" content="NO-CACHE">
        <meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
        <meta http-equiv="EXPIRES" content="-1">
        
        <title>Create Orders</title>
        
        <!-- EXT JS LIBRARY -->
        <link rel="stylesheet" type="text/css" href="../Ext/2.2/resources/css/ext-all.css" />
        <script src="../Ext/2.2/adapter/ext/ext-base.js"></script>
        <script src="../Ext/2.2/ext-all-debug.js"></script>
        <script src="system/modules/orders/lang.js"></script>
</head>
<body>
    <script type="text/javascript">
        
        Ext.Ajax.request({
                waitMsg: 'Please wait...',
                url: 'connect.php?moduleId=qo-orders&action=getOrderId',
                success: function(response){
                        var ordersId = response.responseText;
                        var orderDetailGridStore = new Ext.data.JsonStore({
                            root: 'records',
                            totalProperty: 'totalCount',
                            idProperty: 'id',
                            fields: ['id','itemId', 'itemTitle', 'skuId', 'quantity', 'barCode', 'unitPriceCurrency', 'unitPriceValue'],
                            url:'connect.php?moduleId=qo-orders&action=getOrderDetail&id='+ordersId
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
                                header: "Bar Code",
                                dataIndex: 'barCode',
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
                                                                allowBlank: false,
                                                                fieldLabel: 'sku Title'
                                                                },{
                                                                xtype: 'textfield',
                                                                name: 'unitPriceValue',
                                                                allowBlank: false,
                                                                fieldLabel: 'Unit Price'
                                                            },{
                                                                xtype: 'textfield',
                                                                name: 'barCode',
                                                                allowBlank: false,
                                                                fieldLabel: 'Bar Code'
                                                        }]
                                                       }]
                                                }]
                                        })
                                        
                                        var addOrderDetailWindow = new Ext.Window({
                                            title: 'Add Detail' ,
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
                        var orderDetailForm = new Ext.FormPanel({
                                autoScroll:true,
                                reader:new Ext.data.JsonReader({
                                    }, ['id','createdBy','createdOn','modifiedBy','modifiedOn','sellerId','ebayName','ebayEmail','ebayAddress1','ebayAddress2','ebayCity','ebayStateOrProvince',
                                        'ebayPostalCode','ebayCountry','ebayPhone','paypalName','paypalEmail','paypalAddress1','paypalAddress2',
                                        'paypalCity','paypalStateOrProvince','paypalPostalCode','paypalCountry','paypalPhone','status','grandTotalCurrency','grandTotalValue',
                                        'remarks','shippingMethod','shippingFeeCurrency','shippingFeeValue','insuranceCurrency','insuranceValue','discountCurrency','discountValue'
                                ]),
                                items:[{
                                    xtype:"textfield",
                                    fieldLabel:lang.orders.form_orders_id,
                                    name:"id",
                                    value: ordersId,
                                    readOnly: true
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
                                        items:[{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_ebay_name,
                                            name:"ebayName"
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
                                            name: 'ebayCountry',
                                            hiddenName:'ebayCountry'
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_ebay_phone,
                                            name:"ebayPhone"
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
                                            name: 'paypalCountry',
                                            hiddenName:'paypalCountry'
                                          },{
                                            xtype:"textfield",
                                            fieldLabel:lang.orders.form_paypal_phone,
                                            name:"paypalPhone"
                                          }]
                                      }]
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
                                }
                                ],
                                buttons: [{
                                    id: "create-button",
                                    text: 'Create',
                                    handler: function(){
                                        orderDetailForm.getForm().submit({
                                            url: "connect.php?moduleId=qo-orders&action=createOrder",
                                            success: function(f, a){
                                                //console.log(a);
                                                //window.close();
                                                var response = Ext.decode(a.response.responseText);
                                                if(response.success){
                                                        Ext.Msg.alert('Success', 'Order Create Successfully!');
                                                }else{
                                                        Ext.Msg.alert('Failure', 'Order Create Failed!');
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
                        orderDetailForm.render(document.body);
                },
                failure: function(response){
                    var result = response.responseText;
                    Ext.MessageBox.alert('error', 'could not connect to the database. retry later');
                }
        });
        
        
    </script>
</body>
</html>