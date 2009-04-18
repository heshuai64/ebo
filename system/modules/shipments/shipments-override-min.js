Ext.override(QoDesk.Shipments,{createWindow:function(){var c=this.app.getDesktop();var b=c.getWindow("shipment-win");if(!b){var a=function(){var h=Ext.getCmp("search-shipment-form").getForm();var g=new Ext.data.JsonStore({root:"records",totalProperty:"totalCount",idProperty:"id",baseParams:{id:h.findField("shipmentsId").getValue(),ordersId:h.findField("ordersId").getValue(),shippingMethod:h.findField("shippingMethod").getValue(),sellerId:h.findField("sellerId").getValue(),shipToName:h.findField("shipToName").getValue(),shipToAddressLine:h.findField("shipToAddressLine").getValue(),postalReferenceNo:h.findField("postalReferenceNo").getValue(),status:h.findField("status").getValue(),itemId:h.findField("itemId").getValue(),itemTitle:h.findField("itemTitle").getValue(),skuId:h.findField("skuId").getValue(),skuTitle:h.findField("skuTitle").getValue(),createdOnFrom:h.findField("createdOnFrom").getValue(),createdOnTo:h.findField("createdOnTo").getValue(),packedOnFrom:h.findField("packedOnFrom").getValue(),packedOnTo:h.findField("packedOnTo").getValue(),shippedOnFrom:h.findField("shippedOnFrom").getValue(),shippedOnTo:h.findField("shippedOnTo").getValue()},fields:["id","shipToName","shipToEmail","ordersId","sellerId","createdOn","packedOn","shippedOn","status"],url:"connect.php?moduleId=qo-shipments&action=searchShipment"});function d(i,k,j){return lang.shipments.shipments_status_json[i];}var e=new Ext.grid.GridPanel({store:g,columns:[{header:"Shipment Id",dataIndex:"id",width:110,align:"center",sortable:true},{header:"shipTo",dataIndex:"shipToName",width:120,align:"center",sortable:true},{header:"Order Id",dataIndex:"ordersId",width:110,align:"center",sortable:true},{header:"Seller Id",dataIndex:"sellerId",width:100,align:"center",sortable:true},{header:"Created On",dataIndex:"createdOn",width:150,align:"center",sortable:true},{header:"Packed On",dataIndex:"packedOn",width:150,align:"center",sortable:true},{header:"Shipped On",dataIndex:"shippedOn",width:150,align:"center",sortable:true},{header:"Status",dataIndex:"status",width:100,renderer:d,align:"center",sortable:true}],bbar:new Ext.PagingToolbar({pageSize:20,store:g,displayInfo:true})});e.on("rowdblclick",function(j){var i=j.getSelectionModel().getSelected();window.open("/ebayBO/shipments.php?id="+i.data.id,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");});g.load({params:{start:0,limit:20}});var f=c.createWindow({title:lang.shipments.search_result,width:700,height:400,iconCls:"shipments-icon",shim:false,animCollapse:false,constrainHeader:true,layout:"fit",items:e,taskbuttonTooltip:lang.shipments.task_button_tooltip});f.show();};b=c.createWindow({id:"shipment-win",title:"Search Shipment",width:600,height:560,iconCls:"shipments-icon",shim:false,animCollapse:false,constrainHeader:true,layout:"fit",items:[{id:"search-shipment-form",xtype:"form",items:[{layout:"column",items:[{columnWidth:0.5,layout:"form",defaults:{width:180},items:[{xtype:"textfield",fieldLabel:"Shipment Id",name:"shipmentsId"},{xtype:"textfield",fieldLabel:"Order Id",name:"ordersId"},{xtype:"combo",fieldLabel:"Shipment Method",store:new Ext.data.SimpleStore({fields:["id","name"],data:lang.shipments.shipment_method}),mode:"local",valueField:"id",displayField:"name",triggerAction:"all",editable:false,name:"shippingMethod",hiddenName:"shippingMethod"},{xtype:"combo",fieldLabel:"Seller Id",mode:"local",store:new Ext.data.JsonStore({autoLoad:true,fields:["id","name"],url:"connect.php?moduleId=qo-transactions&action=getSeller"}),valueField:"id",displayField:"name",triggerAction:"all",editable:false,selectOnFocus:true,name:"sellerId",hiddenName:"sellerId"}]},{columnWidth:0.5,layout:"form",defaults:{width:180},items:[{xtype:"textfield",fieldLabel:"Shipment Name",name:"shipToName"},{xtype:"textfield",fieldLabel:"Shipment Address",name:"shipToAddressLine"},{xtype:"textfield",fieldLabel:"Postal Reference",name:"postalReferenceNo"},{xtype:"combo",fieldLabel:"Shipment Status",store:new Ext.data.SimpleStore({fields:["id","name"],data:lang.shipments.shipments_status}),mode:"local",valueField:"id",displayField:"name",triggerAction:"all",editable:false,name:"status",hiddenName:"status"}]}]},{xtype:"fieldset",title:"Item",autoHeight:true,items:[{layout:"column",items:[{title:"ID",columnWidth:0.5,items:[{xtype:"textarea",name:"itemId",width:250,height:50}]},{title:"Title",columnWidth:0.5,items:[{xtype:"textarea",name:"itemTitle",width:250,height:50}]}]}]},{xtype:"fieldset",title:"SKU",autoHeight:true,items:[{layout:"column",items:[{title:"ID",columnWidth:0.5,items:[{xtype:"textarea",name:"skuId",width:250,height:50}]},{title:"Title",columnWidth:0.5,items:[{xtype:"textarea",name:"skuTitle",width:250,height:50}]}]}]},{layout:"table",layoutConfig:{columns:3},defaults:{border:false},items:[{html:"Created:"},{layout:"form",labelWidth:30,cellCls:"orders-search-create-time",items:[{xtype:"datefield",fieldLabel:"From",name:"createdOnFrom",format:"Y-m-d"}]},{layout:"form",labelWidth:30,cellCls:"transactions-search-date",items:[{xtype:"datefield",fieldLabel:"To",name:"createdOnTo",format:"Y-m-d"}]},{html:"Packed:"},{layout:"form",labelWidth:30,cellCls:"transactions-search-date",items:[{xtype:"datefield",fieldLabel:"From",name:"packedOnFrom",format:"Y-m-d"}]},{layout:"form",labelWidth:30,cellCls:"orders-search-create-time",items:[{xtype:"datefield",fieldLabel:"To",name:"packedOnTo",format:"Y-m-d"}]},{html:"shipped:"},{layout:"form",labelWidth:30,cellCls:"transactions-search-date",items:[{xtype:"datefield",fieldLabel:"From",name:"shippedOnFrom",format:"Y-m-d"}]},{layout:"form",labelWidth:30,cellCls:"orders-search-create-time",items:[{xtype:"datefield",fieldLabel:"To",name:"shippedOnTo",format:"Y-m-d"}]}]}],buttons:[{text:"Search",handler:function(){a();b.close();}},{text:"Verify Shipment",handler:function(){b.close();window.open("/ebayBO/verifyShipment.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");}},{text:"Pack Shipment",handler:function(){b.close();window.open("/ebayBO/packShipment.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=500, height=400");}},{text:"Ship Shipment",handler:function(){b.close();window.open("/ebayBO/shipShipment.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=500, height=400");}},{text:"Close",handler:function(){b.close();}}]}],taskbuttonTooltip:"<b>Search Shipment</b><br />Search Shipment Info"});}b.show();}});