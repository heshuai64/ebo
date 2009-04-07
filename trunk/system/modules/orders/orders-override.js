var currency_data = [['USD','USD'],['EUR','EUR'],['GBP','GBP'],['AUD','AUD'],['RMB','RMB'],['CAD','CAD']];
Ext.override(QoDesk.Orders, {
    createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('orders-win');
        
        var searchOrders = function(){
            var searchOrderForm = Ext.getCmp("search-order-form").getForm();
            //console.log(searchOrderForm);
            var orderGridStore = new Ext.data.JsonStore({
                root: 'records',
                totalProperty: 'totalCount',
                idProperty: 'id',
                remoteSort: true,
                baseParams:{id:searchOrderForm.findField('id').getValue(), sellerId:searchOrderForm.findField('sellerId').getValue(),
                            status:searchOrderForm.findField('status').getValue(),remarks:searchOrderForm.findField('remarks').getValue(),
                            buyerId:searchOrderForm.findField('buyerId').getValue(),buyerName:searchOrderForm.findField('buyerName').getValue(),
                            buyerEmail:searchOrderForm.findField('buyerEmail').getValue(),buyerAddress:searchOrderForm.findField('buyerAddress').getValue(),
                            createdOnFrom:searchOrderForm.findField('createdOnFrom').getValue(),createdOnTo:searchOrderForm.findField('createdOnTo').getValue(),
                            modifiedOnFrom:searchOrderForm.findField('modifiedOnFrom').getValue(),modifiedOnTo:searchOrderForm.findField('modifiedOnTo').getValue()
                            },
                fields: ['id', 'sellerId', 'buyerId', 'ebayName', 'ebayEmail', 'grandTotalCurrency', 'grandTotalValue', 'amountPayCurrency', 'amountPayValue', 'status'],
                url:'connect.php?moduleId=qo-orders&action=searchOrder'
            });
         
            function renderGrandTotalValue(v, p, r){
                return String.format('<font color="red">{0} {1}</font>', r.data.grandTotalCurrency, v);
            }
            
            function renderAmountPayValue(v, p, r){
                if(v == null){
                    return "";
                }else{
                    return String.format('<font color="green">{0} {1}</font>', r.data.amountPayCurrency, v);
                }
            }
            
            function renderStatus(v, p, r){
                switch(v){
                    case "W":
                        return "未完成的购买";
                    break;
                
                    case "P":
                        return "交易成功";
                    break;
                
                    case "V":
                        return "审查中";
                    break;
                
                    case "S":
                        return "付款不足";
                    break;
                
                    case "C":
                        return "付款过多";
                    break;
                
                    case "X":
                        return "交易取消";
                    break;
                }
            }
            
            function renderBuyerInfo(v, p, r){
                return String.format('{0}<br>{1}', r.data.ebayName, r.data.ebayEmail);
            }
            
            var orderGrid = new Ext.grid.GridPanel({
                store: orderGridStore,
                columns:[{
                    header: "订单号",
                    dataIndex: 'id',
                    width: 110,
                    align: 'center',
                    sortable: true
                },{
                    header: "销售帐号",
                    dataIndex: 'sellerId',
                    width: 100,
                    align: 'center',
                    sortable: true
                },{
                    header: "买家信息",
                    dataIndex: 'buyerId',
                    width: 150,
                    renderer: renderBuyerInfo,
                    align: 'center',
                    sortable: true
                },{
                    header: "总价钱",
                    dataIndex: 'grandTotalValue',
                    width: 100,
                    renderer: renderGrandTotalValue,
                    align: 'center',
                    sortable: true
                },{
                    header: "总付款",
                    dataIndex: 'amountPayValue',
                    width: 100,
                    renderer: renderAmountPayValue,
                    align: 'center',
                    sortable: true
                },{
                    header: "状态",
                    dataIndex: 'status',
                    width: 120,
                    renderer: renderStatus,
                    align: 'center',
                    sortable: true
                }],
                bbar: new Ext.PagingToolbar({
                                    pageSize: 20,
                                    store: orderGridStore,
                                    displayInfo: true
                            })
            });
            
            orderGrid.on("rowdblclick", function(oGrid){
                        
                        var oRecord = oGrid.getSelectionModel().getSelected();
                        //console.log(oRecord);
                        /*
                        var orderDetailGridStore = new Ext.data.JsonStore({
                            root: 'records',
                            totalProperty: 'totalCount',
                            idProperty: 'id',
                            fields: ['id','itemId', 'itemTitle', 'skuId', 'quantity', 'unitPriceCurrency', 'unitPriceValue'],
                            url:'connect.php?moduleId=qo-orders&action=getOrderDetail&id='+oRecord.data['id']
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
                                width: 50,
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
                                width: 80,
                                align: 'center',
                                sortable: true
                            }],
                            bbar: [{
                                    text: 'Add Detail',
                                    handler: function(){
                                        var add_order_detail_form = Ux_Get_Form("ADD_ORDER_DETAIL", null);
                                        var addDetailWindow = new Ext.Window({
                                            //id: 'PresidentCreateWindow',
                                            title: 'Add ' + aArgument['ordersId'] + ' Detail' ,
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
                                                        url: '/backOffice/orders/detail',
                                                        params: {
                                                                action: "create",
                                                                ordersId: aArgument['ordersId'],
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
                                                                    oStore.reload();
                                                                    addDetailWindow.hide();
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
                                                      addDetailWindow.hide();
                                                }
                                            }]
                                        });
                                        addDetailWindow.show();
                                        }
                                    },{
                                        text: 'Delete Detail',
                                        handler: function(){
                                            if(grid.selModel.getCount() >= 1){
                                                Ext.MessageBox.confirm('Confirmation','Delete those Details?', deleteDetails);
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
                            fields: ['id', 'txnId', 'amountCurrency', 'amountValue', 'status', 'transactionTime'],
                            url:'connect.php?moduleId=qo-orders&action=getOrderTransaction&id='+oRecord.data['id']
                        });
                        
                        var orderTransactionGrid = new Ext.grid.EditorGridPanel({
                            autoHeight: true,
                            store: orderTransactionStore,
                            columns:[{
                                header: "Transaction ID",
                                dataIndex: 'id',
                                width: 100,
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
                                width: 50,
                                align: 'center',
                                sortable: true
                            }]
                        });
                         
                        var orderShipmentStore = new Ext.data.JsonStore({
                            root: 'records',
                            totalProperty: 'totalCount',
                            idProperty: 'id',
                            fields: ['id', 'createdOn', 'packedOn', 'shippedOn','status'],
                            url:'connect.php?moduleId=qo-orders&action=getOrderShipment&id='+oRecord.data['id']
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
                            }]
                        });
                                                
                        var orderDetailForm = new Ext.FormPanel({
                                autoScroll:true,
                                reader:new Ext.data.JsonReader({
                                    }, ['id','createdOn','sellerId','ebayName','ebayEmail','ebayAddress1','ebayAddress2','ebayCity','ebayStateOrProvince',
                                        'ebayPostalCode','ebayCountry','ebayPhone','paypalName','paypalEmail','paypalAddress1','paypalAddress2',
                                        'paypalCity','paypalStateOrProvince','paypalPostalCode','paypalCountry','paypalPhone','status',
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
                                        columnWidth:0.34,
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
                                        columnWidth:0.33,
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
                                        columnWidth:0.33,
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
                                ]
                                
                        })
                        
                        orderDetailGridStore.load();
                        orderTransactionStore.load();
                        orderShipmentStore.load();
                        
                        orderDetailForm.getForm().load({url:'connect.php?moduleId=qo-orders&action=getOrder', 
                                method:'GET', 
                                params: {id: oRecord.data['id']}, 
                                waitMsg:'加载中,请稍候'
                                }
                            );
                        
                        
                        
                        win = desktop.createWindow({
                            id: oRecord.data['id'],
                            title:'订单详情',
                            width:750,
                            height:600,
                            iconCls: 'orders-icon',
                            shim:false,
                            autoScroll: true,
                            animCollapse:false,
                            constrainHeader:true,
                            layout: 'fit',
                            items: orderDetailForm,
                            taskbuttonTooltip: '<b>订单详情</b><br />显示订单详细信息'
                        })
                        win.show();
                        */
                        window.open("http://127.0.0.1:6666/eBayBO/orders.php?id="+oRecord.data['id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");
	    });
             
            orderGridStore.load({params:{start:0, limit:20}});
            
            win = desktop.createWindow({
               title:'查询结果',
               width:700,
               height:400,
               iconCls: 'orders-icon',
               shim:false,
               animCollapse:false,
               constrainHeader:true,
               layout: 'fit',
               items: orderGrid,
               taskbuttonTooltip: '<b>查询结果</b><br />订单查询结果列表'
            })
            win.show();
        }
        
        var createOrder = function(){
            
        }
        
        if(!win){
            win = desktop.createWindow({
                id: 'orders-win',
                title:'订单查询',
                width:600,
                height:300,
                iconCls: 'orders-icon',
                shim:false,
                animCollapse:false,
                constrainHeader:true,
		layout: 'fit',
                items: [{
                        id:"search-order-form",
                        xtype:"form",
                        width:600,
                        border: false,
                        items:[{
                            layout:"column",
                            items:[{
                                columnWidth:0.5,
                                layout:"form",
                                defaults:{
                                    width:180
                                },
                                items:[{
                                    xtype:"textfield",
                                    fieldLabel:"订单编号",
                                    name:"id"
                                  },{
                                    xtype:"combo",
                                    fieldLabel:"销售帐号",
                                    name:"sellerId",
                                    hiddenName:"sellerId"
                                  },{
                                    xtype:"combo",
                                    fieldLabel:"状态",
                                    name:"status",
                                    hiddenName:"status"
                                  },{
                                    xtype:"textarea",
                                    fieldLabel:"备注",
                                    name:"remarks"
                                  }]
                              },{
                                columnWidth:0.5,
                                layout:"form",
                                defaults:{
                                    width:180
                                },
                                items:[{
                                    xtype:"textfield",
                                    fieldLabel:"买家帐号",
                                    name:"buyerId"
                                  },{
                                    xtype:"textfield",
                                    fieldLabel:"买家名",
                                    name:"buyerName"
                                  },{
                                    xtype:"textfield",
                                    fieldLabel:"买家邮件地址",
                                    name:"buyerEmail"
                                  },{
                                    xtype:"textarea",
                                    fieldLabel:"买家地址",
                                    name:"buyerAddress"
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
                                html:"创建日期:"
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
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
                                    name:"createdOnTo",
                                    format:'Y-m-d'
                                }]
                              },{
                                html:"修改日期:"
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
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
				searchOrders();
			    }
                        },{
                            text: '创建订单',
                            handler: function(){
				createOrder();
			    }
                        }]
                        }],
                taskbuttonTooltip: '<b>订单查询</b><br />查询eBay订单'
            });
        }
        
        win.show();
    }
})