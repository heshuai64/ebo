Ext.onReady(function(){
              
        var packShipmentForm = new Ext.FormPanel({
            id:"pick-shipment-form",
            title:"Pack Shipment",
            renderTo: "pack-shipment",
            url:'connect.php?moduleId=qo-shipments&action=packShipment',
            autoScroll:true,
            items:[{
                xtype:"textfield",
                fieldLabel:"Shipment Id",
                name:"id",
                listeners:{specialkey: function(t, e){
                                    if(e.getKey() == 13){
                                        Ext.getCmp('pick-shipment-form').form.submit({ 
                                            method:'POST', 
                                            waitTitle:'Connecting', 
                                            waitMsg:'Sending data...',
                                            success:function(form,action){ 
                                                    var obj = Ext.util.JSON.decode(action.response.responseText);
                                                    document.getElementById("message").innerHTML = obj.info;
                                                    Ext.getCmp('pick-shipment-form').form.findField('id').focus(true);
                                                    //var packInfoPanel = Ext.getCmp('pack_info');
                                                    //packInfoPanel.body.dom.innerHTML=obj.info;
                                            },
                                            failure:function(form1, action){
                                                var obj = Ext.util.JSON.decode(action.response.responseText);
                                                document.getElementById("message").innerHTML = obj.errors.reason;
                                                Ext.getCmp('pick-shipment-form').form.findField('id').focus(true);
                                                //var packInfoPanel = Ext.getCmp('pack_info');
                                                //packInfoPanel.body.dom.innerHTML=obj.errors.reason;
                                            }
                                        })
                                    }
                                }
                        }
              },{
                layout:"column",
                items:[{
                    columnWidth:0.6,
                    layout:"form",
                    border:false,
                    items:[{
                        id:"postalReferenceNo",
                        xtype:"textfield",
                        fieldLabel:"Postal Referece",
                        name:"postalReferenceNo"
                      }]
                  },{
                    columnWidth:0.4,
                    layout:"form",
                    border:false,
                    items:[{
                        id:"button",
                        xtype:"button",
                        name:"textvalue",
                        text:"Disable",
                        handler: function(){
                            if(Ext.getCmp("postalReferenceNo").disabled == true){
                                Ext.getCmp("postalReferenceNo").setDisabled(false);
                                Ext.getCmp("button").setText("Disable");
                            }else{
                                Ext.getCmp("postalReferenceNo").setDisabled(true);
                                Ext.getCmp("button").setText("Enable");
                            }
                            
                        }
                      }]
                  }]
            }],
            buttons: [{
                text: 'Submit',
                handler: function(){
                     Ext.getCmp('pick-shipment-form').form.submit({ 
                        method:'POST', 
                        waitTitle:'Connecting', 
                        waitMsg:'Sending data...',
                        success:function(form,action){ 
                                var obj = Ext.util.JSON.decode(action.response.responseText);
                                document.getElementById("message").innerHTML = obj.info;
                                //var packInfoPanel = Ext.getCmp('pack_info');
                                //packInfoPanel.body.dom.innerHTML=obj.info;
                        },
                        failure:function(form1, action){
                            var obj = Ext.util.JSON.decode(action.response.responseText);
                            document.getElementById("message").innerHTML = obj.errors.reason;
                            //var packInfoPanel = Ext.getCmp('pack_info');
                            //packInfoPanel.body.dom.innerHTML=obj.errors.reason;
                        }
                    })
                }
            }]
        });
        
})