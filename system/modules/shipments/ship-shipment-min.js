Ext.onReady(function(){new Ext.FormPanel({id:"ship-shipment-form",title:"Ship Shipment",renderTo:"ship-shipment",url:"connect.php?moduleId=qo-shipments&action=shipShipment",autoScroll:true,items:[{xtype:"textfield",fieldLabel:"Shipment Id",name:"id",listeners:{specialkey:function(d,a){a.getKey()==13&&Ext.getCmp("postalReferenceNo").disabled==true?Ext.getCmp("ship-shipment-form").form.submit({method:"POST",waitTitle:"Connecting",waitMsg:"Sending data...",success:function(a,b){var c=Ext.util.JSON.decode(b.response.responseText);
document.getElementById("message").innerHTML=c.info;Ext.getCmp("ship-shipment-form").form.findField("id").focus(true)},failure:function(a,b){var c=Ext.util.JSON.decode(b.response.responseText);document.getElementById("message").innerHTML=c.errors.reason;Ext.getCmp("ship-shipment-form").form.findField("id").focus(true)}}):a.getKey()==13&&Ext.getCmp("ship-shipment-form").form.findField("postalReferenceNo").focus(true)}}},{layout:"column",items:[{columnWidth:0.6,layout:"form",border:false,items:[{id:"postalReferenceNo",
xtype:"textfield",fieldLabel:"Postal Referece",name:"postalReferenceNo",listeners:{specialkey:function(d,a){a.getKey()==13&&Ext.getCmp("ship-shipment-form").form.submit({method:"POST",waitTitle:"Connecting",waitMsg:"Sending data...",success:function(a,b){var c=Ext.util.JSON.decode(b.response.responseText);document.getElementById("message").innerHTML=c.info;Ext.getCmp("ship-shipment-form").form.findField("id").focus(true)},failure:function(a,b){var c=Ext.util.JSON.decode(b.response.responseText);document.getElementById("message").innerHTML=
c.errors.reason;Ext.getCmp("ship-shipment-form").form.findField("id").focus(true)}})}}}]},{columnWidth:0.4,layout:"form",border:false,items:[{id:"button",xtype:"button",name:"textvalue",text:"Disable",handler:function(){Ext.getCmp("postalReferenceNo").disabled==true?(Ext.getCmp("postalReferenceNo").setDisabled(false),Ext.getCmp("button").setText("Disable")):(Ext.getCmp("postalReferenceNo").setDisabled(true),Ext.getCmp("button").setText("Enable"))}}]}]}],buttons:[{text:"Submit",handler:function(){Ext.getCmp("ship-shipment-form").form.submit({method:"POST",
waitTitle:"Connecting",waitMsg:"Sending data...",success:function(d,a){var e=Ext.util.JSON.decode(a.response.responseText);document.getElementById("message").innerHTML=e.info;Ext.getCmp("ship-shipment-form").form.findField("id").focus(true)},failure:function(d,a){var e=Ext.util.JSON.decode(a.response.responseText);document.getElementById("message").innerHTML=e.errors.reason;Ext.getCmp("ship-shipment-form").form.findField("id").focus(true)}})}}]})});
