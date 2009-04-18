Ext.onReady(function(){
        var shipmentDetailStore = new Ext.data.JsonStore({
                root: 'records',
                totalProperty: 'totalCount',
                idProperty: 'id',
                autoLoad:true,
                fields: ['id', 'skuId', 'skuTitle', 'itemId', 'itemTitle', 'quantity','galleryURL'],
                url:'connect.php?moduleId=qo-shipments&action=getShipmentDetail&id='+shipmentsId
        });
        
        function renderSkuImage(v, p, r){
                return String.format('<img width="100" height="100" src="{0}"', v);
        }
            
         var shipmentDetailGrid = new Ext.grid.EditorGridPanel({
                autoHeight: true,
                store: shipmentDetailStore,
                selModel: new Ext.grid.RowSelectionModel({}),
                columns:[{
                    header: "Image",
                    dataIndex: 'galleryURL',
                    renderer: renderSkuImage,
                    width: 105,
                    align: 'center',
                    sortable: true
                },{
                    header: "SKU",
                    dataIndex: 'skuId',
                    width: 110,
                    align: 'center',
                    sortable: true
                },{
                    header: "Sku Title",
                    dataIndex: 'skuTitle',
                    width: 200,
                    align: 'center',
                    sortable: true
                },{
                    header: "Item Id",
                    dataIndex: 'itemId',
                    width: 110,
                    align: 'center',
                    sortable: true
                },{
                    header: "Item Title",
                    dataIndex: 'itemTitle',
                    width: 200,
                    align: 'center',
                    sortable: true
                },{
                    header: "Quantity",
                    dataIndex: 'quantity',
                    width: 80,
                    align: 'center',
                    sortable: true
                }],
                bbar: [{
                    text: 'Add Detail',
                    handler: function(){
                        var add_shipment_detail_form =  form = new Ext.FormPanel({
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
                        
                        var addShipmentDetailWindow = new Ext.Window({
                            title: 'Add '+shipmentsId+' Detail' ,
                            closable:true,
                            width: 400,
                            height: 300,
                            plain:true,
                            layout: 'fit',
                            items: add_shipment_detail_form,
                            
                            buttons: [{
                                text: 'Save and Close',
                                handler: function(){
                                    Ext.Ajax.request({
                                        waitMsg: 'Please wait...',
                                        url: 'connect.php?moduleId=qo-shipments&action=addShipmentDetail',
                                        params: {
                                                shipmentsId: shipmentsId,
                                                itemId: add_shipment_detail_form.form.findField('itemId').getValue(),
                                                itemTitle: add_shipment_detail_form.form.findField('itemTitle').getValue(),
                                                skuId: add_shipment_detail_form.form.findField('skuId').getValue(),
                                                skuTitle: add_shipment_detail_form.form.findField('skuTitle').getValue(),
                                                quantity: add_shipment_detail_form.form.findField('quantity').getValue()
                                        },
                                        success: function(response){
                                            var result = eval(response.responseText);
                                            switch (result) {
                                                case 1:
                                                    shipmentDetailStore.reload();
                                                    addShipmentDetailWindow.hide();
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
                                      addShipmentDetailWindow.hide();
                                }
                            }]
                        });
                        addShipmentDetailWindow.show();
                    }
                },{
                    text: 'Delete Detail',
                    handler: function(){
                        var deleteshipmentDetail = function(btn){
                                if(btn=='yes'){
                                    var selections = shipmentDetailGrid.selModel.getSelections();
                                    //console.log(selections);
                                    //var prez = [];
                                    var ids = "";
                                    for(i = 0; i< shipmentDetailGrid.selModel.getCount(); i++){
                                        //prez.push(selections[i].data.id);
                                        ids += selections[i].data.id + ","
                                    }
                                    ids = ids.slice(0,-1);
                                    //console.log(prez);
                                    //var encoded_array = Ext.encode(prez);
                                    Ext.Ajax.request({  
                                        waitMsg: 'Please Wait',
                                        url: 'connect.php?moduleId=qo-shipments&action=deleteShipmentDetail', 
                                        params: { 
                                          //ids:  encoded_array
                                          ids: ids
                                        }, 
                                        success: function(response){
                                            var result=eval(response.responseText);
                                            switch(result){
                                            case 1:  // Success : simply reload
                                              shipmentDetailStore.reload();
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
                        
                        if(shipmentDetailGrid.selModel.getCount() >= 1){
                            Ext.MessageBox.confirm('Confirmation','Delete those Details?', deleteshipmentDetail);
                        } else {
                            Ext.MessageBox.alert('Uh oh...','You can\'t really delete something you haven\'t selected huh?');
                        }
                    }
                }]
        });
         
        var shipmentDetailForm = new Ext.FormPanel({
            autoScroll:true,
            reader:new Ext.data.JsonReader({
                }, ['id','ordersId','status','shipmentMethod','packedBy','packedOn','shippedBy','shippedOn',
                    'remarks','postalReferenceNo','shippingFeeCurrency','shippingFeeValue','shipToName','shipToEmail',
                    'shipToAddressLine1','shipToAddressLine2','shipToCity','shipToStateOrProvince','shipToPostalCode',
                    'shipToCountry','shipToPhoneNo','createdBy','createdOn','modifiedBy','modifiedOn'
            ]),
            items:[{
                layout:"column",
                items:[{
                    columnWidth:0.5,
                    layout:"form",
                    title:"System",
                    defaults:{
                            width:220
                    },
                    items:[{
                        xtype:"textfield",
                        fieldLabel:"Shipments Id",
                        readOnly:true,
                        name:"id"
                      },{
                        xtype:"textfield",
                        fieldLabel:"Orders Id",
                        readOnly:true,
                        name:"ordersId"
                      },{
                        layout:"table",
                        layoutConfig:{
                          columns:3
                        },
                        border:false,
                        items:[{
                            width:100,
                            html:"<font size=2>Shipping Fee:</font>",
                            border:false
                          },{
                            layout:"form",
                            labelWidth:0,
                            hideLabels:true,
                            labelSeparator:"",
                            border:false,
                            style:"padding-left:5px",
                            items:[{
                                xtype:'combo',
                                store: new Ext.data.SimpleStore({
                                    fields: ["shippingFeeCurrencyValue", "shippingFeeCurrencyName"],
                                    data: lang.shipments.currency
                                }),
                                width: 60,			  
                                mode: 'local',
                                displayField: 'shippingFeeCurrencyName',
                                valueField: 'shippingFeeCurrencyValue',
                                triggerAction: 'all',
                                editable: false,
                                name: 'shippingFeeCurrency',
                                hiddenName:'shippingFeeCurrency'
                              }]
                          },{
                            layout:"form",
                            labelWidth:0,
                            hideLabels:true,
                            labelSeparator:"",
                            border:false,
                            style:"padding-left:10px",
                            items:[{
                                xtype:"textfield",
                                fieldLabel:"",
                                name:"shippingFeeValue",
                                width:80
                              }]
                          }]
                      },{ 
                        xtype:'combo',
                        fieldLabel:"Status",
                        store: new Ext.data.SimpleStore({
                            fields: ["statusValue", "statusName"],
                            data: lang.shipments.shipments_status
                        }),			  
                        mode: 'local',
                        displayField: 'statusName',
                        valueField: 'statusValue',
                        triggerAction: 'all',
                        editable: false,
                        name: 'status',
                        hiddenName:'status'
                      },{
                        xtype:'combo',
                        fieldLabel:"Shipment Method",
                        store: new Ext.data.SimpleStore({
                            fields: ["id", "name"],
                            data: lang.shipments.shipment_method
                        }),			  
                        mode: 'local',
                        valueField: 'id',
                        displayField: 'name',
                        triggerAction: 'all',
                        editable: false,
                        name: 'shipmentMethod',
                        hiddenName:'shipmentMethod'
                      },{
                        layout:"table",
                        layoutConfig:{
                          columns:3
                        },
                        width:320,
                        border:false,
                        items:[{
                            width:105,
                            html:"<font size=2>packed:</font>",
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
                                name:"packedBy",
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
                                name:"packedOn",
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
                            html:"<font size=2>shipped:</font>",
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
                                name:"shippedBy",
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
                                name:"shippedOn",
                                width:125
                              }]
                          }]
                      },{
                            xtype:"textarea",
                            height:48,
                            width:200,
                            fieldLabel:"Remarks",
                            name:"remarks"
                        }]
                  },{
                    columnWidth:0.5,
                    layout:"form",
                    title:"Address",
                    defaults:{
                            width:200
                    },
                    items:[{
                        xtype:"textfield",
                        fieldLabel:"Name",
                        name:"shipToName"
                      },{
                        xtype:"textfield",
                        fieldLabel:"Email",
                        name:"shipToEmail"
                      },{
                        xtype:"textfield",
                        fieldLabel:"Address 1",
                        name:"shipToAddressLine1"
                      },{
                        xtype:"textfield",
                        fieldLabel:"Address 2",
                        name:"shipToAddressLine2"
                      },{
                        xtype:"textfield",
                        fieldLabel:"City",
                        name:"shipToCity"
                      },{
                        xtype:"textfield",
                        fieldLabel:"State/Province",
                        name:"shipToStateOrProvince"
                      },{
                        xtype:"textfield",
                        fieldLabel:"Postal Code/Zip",
                        name:"shipToPostalCode"
                      },{
                        xtype: 'combo',
                        fieldLabel:"Country",
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
                        name: 'shipToCountry',
                        hiddenName:'shipToCountry'
                      },{
                        xtype:"textfield",
                        fieldLabel:"Phone",
                        name:"shipToPhoneNo"
                      }]
                  }]
              },{
                xtype: 'panel',
                title: "Details",
                autoHeight: true,
                items: shipmentDetailGrid
            }],
            buttons: [{
                        text: 'Save',
                        handler: function(){
                            shipmentDetailForm.getForm().submit({
                                          url: "connect.php?moduleId=qo-shipments&action=saveShipmentInfo",
                                          success: function(f, a){
                                              //console.log(a);
                                              var response = Ext.decode(a.response.responseText);
                                              if(response.success){
                                                      Ext.Msg.alert('Success', 'Update shipments success.');
                                              }else{
                                                      Ext.Msg.alert('Failure', 'Update shipments failure.');
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
        
        shipmentDetailForm.getForm().load({url:'connect.php?moduleId=qo-shipments&action=getShipmentInfo', 
                method:'GET', 
                params: {id: shipmentsId}, 
                waitMsg:'Please wait...'
            }
        );
        
        shipmentDetailForm.render(document.body);  
});