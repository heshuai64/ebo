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
                    //remoteSort: true,
                    baseParams:{id:searchShipmentForm.findField('shipmentsId').getValue(), ordersId:searchShipmentForm.findField('ordersId').getValue(),
                                shippingMethod:searchShipmentForm.findField('shippingMethod').getValue(),shipmentReason:searchShipmentForm.findField('shipmentReason').getValue(),
                                sellerId:searchShipmentForm.findField('sellerId').getValue(),shipToName:searchShipmentForm.findField('shipToName').getValue(),
                                shipToEmail:searchShipmentForm.findField('shipToEmail').getValue(),shipToAddressLine:searchShipmentForm.findField('shipToAddressLine').getValue(),
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
                
                function renderStatus(v, p, r){
                    return lang.shipments.shipments_status_json[v]
                }
            
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
                        renderer: renderStatus,
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
                    window.open("/eBayBO/shipments.php?id="+oRecord.data['id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");        
                });
                 
                shipmentGridStore.load({params:{start:0, limit:20}});
                
                var search_result_win = desktop.createWindow({
                   title:lang.shipments.search_result,
                   width:700,
                   height:400,
                   iconCls: 'shipments-icon',
                   shim:false,
                   animCollapse:false,
                   constrainHeader:true,
                   layout: 'fit',
                   items: shipmentGrid,
                   taskbuttonTooltip: lang.shipments.task_button_tooltip
                })
                search_result_win.show();  
            }
	    
            
            win = desktop.createWindow({
                id: 'shipment-win',
                title:'Search Shipment',
                width:600,
                height:500,
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
                                    xtype:'combo',
                                    fieldLabel: "Method",
                                    store: new Ext.data.SimpleStore({
                                        fields: ["id", "name"],
                                        data: lang.shipments.shipment_method
                                    }),		  
                                    mode: 'local',
                                    valueField: 'id',
                                    displayField: 'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    name: 'shippingMethod',
                                    hiddenName:'shippingMethod'
                                },{
                                    xtype:'combo',
                                    fieldLabel: "Reason",
                                    store: new Ext.data.SimpleStore({
                                        fields: ["id", "name"],
                                        data: lang.shipments.shipment_reason
                                    }),		  
                                    mode: 'local',
                                    valueField: 'id',
                                    displayField: 'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    name: 'shipmentReason',
                                    hiddenName:'shipmentReason'
                                },{
                                    xtype: 'combo',
                                    fieldLabel:"Seller Id",
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
                              }]
                          },{
                            columnWidth:0.5,
                            layout:"form",
                            defaults:{
                                width:180
                            },
                            items:[{
                                xtype:"textfield",
                                fieldLabel:"Name",
                                name:"shipToName"
                              },{
                                xtype:"textfield",
                                fieldLabel:"Address",
                                name:"shipToAddressLine"
                              },{
                                xtype:"textfield",
                                fieldLabel:"Email",
                                name:"shipToEmail"
                              },{
                                xtype:"textfield",
                                fieldLabel:"Postal Reference",
                                name:"postalReferenceNo"
                              },{
                                xtype:'combo',
                                fieldLabel: "Status",
                                store: new Ext.data.SimpleStore({
                                    fields: ["id", "name"],
                                    data: lang.shipments.shipments_status
                                }),		  
                                mode: 'local',
                                valueField: 'id',
                                displayField: 'name',
                                triggerAction: 'all',
                                editable: false,
                                name: 'status',
                                hiddenName:'status'
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
                                width:250,
				height:20
                              }]
                              },{
                            title:"Title",
                            columnWidth:0.5,
                            items:[{
                                xtype:"textarea",
                                name:"itemTitle",
                                width:250,
				height:20
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
                                width:250,
				height:20
                              }]
                              },{
                            title:"Title",
                            columnWidth:0.5,
                            items:[{
                                xtype:"textarea",
                                name:"skuTitle",
                                width:250,
				height:20
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
			disabled: (get_cookie('qo-shipment.searchShipment') == 0)?true:false,
                        handler: function(){
                            searchShipment();
                            win.close();
                        }
		    },{
			text: 'Verify',
			disabled: (get_cookie('qo-shipment.verifyShipment') == 0)?true:false,
			handler: function(){
			    win.close();
			    window.open("/eBayBO/verifyShipment.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");   
			}
		    },{
			text: 'Pack',
			disabled: (get_cookie('qo-shipment.packShipment') == 0)?true:false,
			handler: function(){
			    win.close();
			    window.open("/eBayBO/packShipment.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=500, height=400");  
			}
		    },{
			text: 'Ship',
			disabled: (get_cookie('qo-shipment.shipShipment') == 0)?true:false,
			handler: function(){
			    win.close();
			    window.open("/eBayBO/shipShipment.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=500, height=400");  
			}
		    },{
			text: 'Batch Ship',
			handler: function(){
			    win.close();
			    window.open("/eBayBO/batchShipShipment.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");  
			}
		    }/*,{
			text: 'Registered List',
			handler: function(){
			    window.open("/eBayBO/list.php?type=shipmentRegistered&shippedOnFrom="+Ext.getCmp("search-shipment-form").getForm().findField('shippedOnFrom').getValue()+"&shippedOnTo="+Ext.getCmp("search-shipment-form").getForm().findField('shippedOnTo').getValue(),"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=900, height=700");  
			    win.close();
			}
		    }*/,{
			text: 'Outstanding',
			disabled: (get_cookie('qo-shipment.outstandingShipment') == 0)?true:false,
			handler: function(){
			    window.open("/eBayBO/outstandingShipmen.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=700");  
			    win.close();
			}
		    },{
			text: 'Close',
			handler: function(){
			    win.close();
			}
		    }
                    ]
                }],
                taskbuttonTooltip: '<b>Search Shipment</b><br />Search Shipment Info'
            });
        }
        
        win.show();
    }
})