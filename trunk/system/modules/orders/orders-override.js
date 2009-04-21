
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
                //remoteSort: true,
                baseParams:{id:searchOrderForm.findField('id').getValue(), sellerId:searchOrderForm.findField('sellerId').getValue(),
                            status:searchOrderForm.findField('status').getValue(),remarks:searchOrderForm.findField('remarks').getValue(),
                            buyerId:searchOrderForm.findField('buyerId').getValue(),buyerName:searchOrderForm.findField('buyerName').getValue(),
                            buyerEmail:searchOrderForm.findField('buyerEmail').getValue(),buyerAddress:searchOrderForm.findField('buyerAddress').getValue(),
			    skuId:searchOrderForm.findField('skuId').getValue(),skuTitle:searchOrderForm.findField('skuTitle').getValue(),
			    itemId:searchOrderForm.findField('itemId').getValue(),itemTitle:searchOrderForm.findField('itemTitle').getValue(),
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
                return lang.orders.orders_status_json[v];
            }
            
            function renderBuyerInfo(v, p, r){
                return String.format('{0}<br>{1}<br>{2}', r.data.buyerId, r.data.ebayName, r.data.ebayEmail);
            }
            
            var orderGrid = new Ext.grid.GridPanel({
                store: orderGridStore,
                columns:[{
                    header: lang.orders.grid_orders_id_header,
                    dataIndex: 'id',
                    width: 110,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.orders.grid_seller_id_header,
                    dataIndex: 'sellerId',
                    width: 100,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.orders.grid_buyer_id_header,
                    dataIndex: 'buyerId',
                    width: 150,
                    renderer: renderBuyerInfo,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.orders.grid_grand_total_header,
                    dataIndex: 'grandTotalValue',
                    width: 100,
                    renderer: renderGrandTotalValue,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.orders.grid_amount_pay_header,
                    dataIndex: 'amountPayValue',
                    width: 100,
                    renderer: renderAmountPayValue,
                    align: 'center',
                    sortable: true
                },{
                    header: lang.orders.grid_status_header,
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
                window.open("/eBayBO/orders.php?id="+oRecord.data['id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");
	    });
             
            orderGridStore.load({params:{start:0, limit:20}});
            
            var search_result_win = desktop.createWindow({
               title:lang.orders.search_result,
               width:717,
               height:400,
               iconCls: 'orders-icon',
               shim:false,
               animCollapse:false,
               constrainHeader:true,
               layout: 'fit',
               items: orderGrid,
               taskbuttonTooltip: lang.orders.task_button_tooltip
            })
            search_result_win.show();
        }
        
        var createOrder = function(){
            window.open("/eBayBO/create_orders.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");
        }
        
        if(!win){
            win = desktop.createWindow({
                id: 'orders-win',
                title:lang.orders.search_orders,
                width:600,
                height:400,
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
                                    fieldLabel:lang.orders.form_orders_id,
                                    name:"id"
                                  },{
                                    xtype: 'combo',
                                    fieldLabel:lang.orders.form_seller_id,
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
                                    xtype:"combo",
                                    fieldLabel:lang.orders.form_status,
                                    store: new Ext.data.SimpleStore({
                                        fields: ["id", "name"],
                                        data: lang.orders.orders_status
                                    }),
                                    mode: 'local',
                                    valueField: 'id',
                                    displayField: 'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    name: 'status',
                                    hiddenName:'status'
                                  },{
                                    xtype:"textarea",
                                    fieldLabel:lang.orders.form_remarks,
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
                                    fieldLabel:lang.orders.form_buyer_id,
                                    name:"buyerId"
                                  },{
                                    xtype:"textfield",
                                    fieldLabel:lang.orders.form_buyer_name,
                                    name:"buyerName"
                                  },{
                                    xtype:"textfield",
                                    fieldLabel:lang.orders.form_buyer_email,
                                    name:"buyerEmail"
                                  },{
                                    xtype:"textarea",
                                    fieldLabel:lang.orders.form_buyer_address,
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
			    width:600,
                            items:[{
                                html:lang.orders.form_sku
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"textfield",
                                    fieldLabel:lang.orders.form_sku_id,
                                    name:"skuId"
                                }]
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"textfield",
                                    fieldLabel:lang.orders.form_sku_title,
                                    name:"skuTitle"
                                }]
                              },{
                                html:lang.orders.form_item
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"textfield",
                                    fieldLabel:lang.orders.form_item_id,
                                    name:"itemId"
                                }]
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"textfield",
                                    fieldLabel:lang.orders.form_item_title,
                                    name:"itemTitle"
                                }]
                              },{
                                html:lang.orders.form_created_date
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:lang.orders.form_start,
                                    name:"createdOnFrom",
                                    format:'Y-m-d'
                                }]
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:lang.orders.form_end,
                                    name:"createdOnTo",
                                    format:'Y-m-d'
                                }]
                              },{
                                html:lang.orders.form_modified_date
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:lang.orders.form_start,
                                    name:"modifiedOnFrom",
                                    format:'Y-m-d'
                                }]
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:lang.orders.form_end,
                                    name:"modifiedOnTo",
                                    format:'Y-m-d'
                                }]
                              }]
                          }],
                        buttons: [{
                                text: lang.orders.submit,
                                handler: function(){
                                    searchOrders();
                                    win.close();
                                }
                            },{
                                text: lang.orders.create_orders,
                                handler: function(){
                                    createOrder();
                                }
                            },{
                                text: lang.orders.close_windows,
                                handler: function(){
                                    win.close();
                                }
                            }]
                    }],
                taskbuttonTooltip: '<b>订单查询</b><br />查询eBay订单'
            });
        }
        
        win.show();
    }
})