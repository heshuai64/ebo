Ext.override(QoDesk.Shipments, {
  
    createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('shipment-win');
        
        if(!win){
            var searchShipment = function(){
                var searchShipmentForm = Ext.getCmp("search-shipment-form").getForm();
                var shipmentGridStore = new Ext.data.JsonStore({
                    root: 'records',
                    totalProperty: 'totalCount',
                    idProperty: 'id',
                    remoteSort: true,
                    baseParams:{id:searchShipmentForm.findField('shipmentsId').getValue(), ordersId:searchShipmentForm.findField('ordersId').getValue(),
                                shippingMethod:searchShipmentForm.findField('shippingMethod').getValue(),sellerId:searchShipmentForm.findField('sellerId').getValue(),
                                shipToName:searchShipmentForm.findField('shipToName').getValue(),shipToAddressLine:searchShipmentForm.findField('shipToAddressLine').getValue(),
                                postalReferenceNo:searchShipmentForm.findField('postalReferenceNo').getValue(),status:searchShipmentForm.findField('status').getValue(),
                                itemId:searchShipmentForm.findField('itemId').getValue(),itemTitle:searchShipmentForm.findField('itemTitle').getValue(),
                                skuId:searchShipmentForm.findField('skuId').getValue(),skuTitle:searchShipmentForm.findField('skuTitle').getValue(),
                                createdOnFrom:searchShipmentForm.findField('createdOnFrom').getValue(),createdOnTo:searchShipmentForm.findField('createdOnTo').getValue(),
                                packedOnFrom:searchShipmentForm.findField('packedOnFrom').getValue(),packedOnTo:searchShipmentForm.findField('packedOnTo').getValue(),
                                shippedOnFrom:searchShipmentForm.findField('shippedOnFrom').getValue(),shippedOnTo:searchShipmentForm.findField('shippedOnTo').getValue()
                                },
                    fields: ['id', 'shipToName', 'shipToEmail', 'ordersId', 'sellerId', 'createdOn','packedOn','shippedOn','status'],
                    url:'connect.php?moduleId=qo-shipments&action=searchShipment'
                });
                
                var shipmentGrid = new Ext.grid.GridPanel({
                    store: shipmentGridStore,
                    columns:[{
                        header: "Shipment Id",
                        dataIndex: 'id',
                        width: 110,
                        align: 'center',
                        sortable: true
                    },{
                        header: "shipTo",
                        dataIndex: 'shipToName',
                        width: 120,
                        align: 'center',
                        sortable: true
                    },{
                        header: "Order Id",
                        dataIndex: 'ordersId',
                        width: 110,
                        align: 'center',
                        sortable: true
                    },{
                        header: "Seller Id",
                        dataIndex: 'sellerId',
                        width: 100,
                        align: 'center',
                        sortable: true
                    },{
                        header: "Created On",
                        dataIndex: 'createdOn',
                        width: 150,
                        align: 'center',
                        sortable: true
                    },{
                        header: "Packed On",
                        dataIndex: 'packedOn',
                        width: 150,
                        align: 'center',
                        sortable: true
                    },{
                        header: "Shipped On",
                        dataIndex: 'shippedOn',
                        width: 150,
                        align: 'center',
                        sortable: true
                    },{
                        header: "Status",
                        dataIndex: 'status',
                        width: 100,
                        //renderer: renderStatus,
                        align: 'center',
                        sortable: true
                    }],
                    bbar: new Ext.PagingToolbar({
                            pageSize: 20,
                            store: shipmentGridStore,
                            displayInfo: true
                    })
                });
                
                shipmentGrid.on("rowdblclick", function(oGrid){
                var oRecord = oGrid.getSelectionModel().getSelected();
                    window.open("http://127.0.0.1:6666/eBayBO/shipments.php?id="+oRecord.data['id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");        
                });
                 
                shipmentGridStore.load({params:{start:0, limit:20}});
                
                win = desktop.createWindow({
                   title:'查询结果',
                   width:700,
                   height:400,
                   iconCls: 'shipments-icon',
                   shim:false,
                   animCollapse:false,
                   constrainHeader:true,
                   layout: 'fit',
                   items: shipmentGrid,
                   taskbuttonTooltip: '<b>查询结果</b><br />付款货运结果列表'
                })
                win.show();
            
                
            }
	    
            
            win = desktop.createWindow({
                id: 'shipment-win',
                title:'Search Shipment',
                width:600,
                height:560,
                iconCls: 'shipments-icon',
                shim:false,
                animCollapse:false,
                constrainHeader:true,
		layout: 'fit',
                items: [{
                    id:"search-shipment-form",
                    xtype:"form",
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
                                    fieldLabel:"Shipment Id",
                                    name:"shipmentsId"
                                },{
                                    xtype:"textfield",
                                    fieldLabel:"Order Id",
                                    name:"ordersId"
                                },{
                                    xtype:"combo",
                                    fieldLabel:"Shipment Method",
                                    name:"shippingMethod",
                                    hiddenName:"shippingMethod"
                                },{
                                    xtype:"combo",
                                    fieldLabel:"Seller Id",
                                    name:"sellerId",
                                    hiddenName:"sellerId"
                              }]
                          },{
                            columnWidth:0.5,
                            layout:"form",
                            defaults:{
                                width:180
                            },
                            items:[{
                                xtype:"textfield",
                                fieldLabel:"Shipment Name",
                                name:"shipToName"
                              },{
                                xtype:"textfield",
                                fieldLabel:"Shipment Address",
                                name:"shipToAddressLine"
                              },{
                                xtype:"textfield",
                                fieldLabel:"Postal Reference",
                                name:"postalReferenceNo"
                              },{
                                xtype:"combo",
                                fieldLabel:"Shipment Status",
                                name:"status",
                                hiddenName:"status"
                              }]
                          }]
                          },{
                        xtype:"fieldset",
                        title:"Item",
                        autoHeight:true,
                        items:[{
                            layout:"column",
                            items:[{
                            title:"ID",
                            columnWidth:0.5,
                            items:[{
                                xtype:"textarea",
                                name:"itemId",
                                width:250
                              }]
                              },{
                            title:"Title",
                            columnWidth:0.5,
                            items:[{
                                xtype:"textarea",
                                name:"itemTitle",
                                width:250
                              }]
                              }]
                          }]
                          },{
                        xtype:"fieldset",
                        title:"SKU",
                        autoHeight:true,
                        items:[{
                            layout:"column",
                            items:[{
                            title:"ID",
                            columnWidth:0.5,
                            items:[{
                                xtype:"textarea",
                                name:"skuId",
                                width:250
                              }]
                              },{
                            title:"Title",
                            columnWidth:0.5,
                            items:[{
                                xtype:"textarea",
                                name:"skuTitle",
                                width:250
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
                                html:"Created:"
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:"From",
                                    name:"createdOnFrom",
                                    format:'Y-m-d'
                                }]
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "transactions-search-date",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:"To",
                                    name:"createdOnTo",
                                    format:'Y-m-d'
                                }]
                              },{
                                html:"Packed:"
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "transactions-search-date",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:"From",
                                    name:"packedOnFrom",
                                    format:'Y-m-d'
                                }]
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:"To",
                                    name:"packedOnTo",
                                    format:'Y-m-d'
                                }]
                              },{
                                html:"shipped:"
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "transactions-search-date",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:"From",
                                    name:"shippedOnFrom",
                                    format:'Y-m-d'
                                }]
                              },{
                                layout:"form",
                                labelWidth:30,
                                cellCls: "orders-search-create-time",
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:"To",
                                    name:"shippedOnTo",
                                    format:'Y-m-d'
                                }]
                              }]
                          }],
                    buttons: [{
                        text: 'Search',
                        handler: function(){
                            searchShipment();
                        }
		    },{
			text: 'Verify Shipment',
			handler: function(){
			    win.close();
			    window.open("http://127.0.0.1/ebayBO/verifyShipment.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");   
			}
		    },{
			text: 'Pack Shipment',
			handler: function(){
			    win.close();
			    window.open("http://127.0.0.1/ebayBO/packShipment.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=500, height=400");  
			}
		    }]
                }],
                taskbuttonTooltip: '<b>Search Shipment</b><br />Search Shipment Info'
            });
        }
        
        win.show();
    }
})