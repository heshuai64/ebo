<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="PRAGMA" content="NO-CACHE">
        <meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
        <meta http-equiv="EXPIRES" content="-1">
        
        <title><?=$_GET['id']?></title>
        
        <!-- EXT JS LIBRARY -->
        <link rel="stylesheet" type="text/css" href="../Ext/2.2/resources/css/ext-all.css" />
        <script src="../Ext/2.2/adapter/ext/ext-base.js"></script>
        <script src="../Ext/2.2/ext-all-debug.js"></script>
</head>
<body>
    <script type="text/javascript">
        var currency_data = [['USD','USD'],['EUR','EUR'],['GBP','GBP'],['AUD','AUD'],['RMB','RMB'],['CAD','CAD']];
        var shipmentDetailStore = new Ext.data.JsonStore({
                root: 'records',
                totalProperty: 'totalCount',
                idProperty: 'id',
                autoLoad:true,
                fields: ['id', 'skuId', 'skuTitle', 'itemId', 'itemTitle', 'quantity'],
                url:'connect.php?moduleId=qo-shipments&action=getShipmentDetail&id=<?=$_GET['id']?>'
        });
        
        function renderSkuImage(v, p, r){
                return String.format('<img src="http://m2.sourcingmap.com/smap/images/item/medium/ux_{0}_ux_m.jpg"', v);
        }
            
         var shipmentDetailGrid = new Ext.grid.EditorGridPanel({
                autoHeight: true,
                store: shipmentDetailStore,
                selModel: new Ext.grid.RowSelectionModel({}),
                columns:[{
                    header: "Image",
                    dataIndex: 'skuId',
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
                    width: 120,
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
                    width: 120,
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
                            title: 'Add <?=$_GET['id']?> Detail' ,
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
                                                shipmentsId: '<?=$_GET['id']?>',
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
                            width:200
                    },
                    items:[{
                        xtype:"textfield",
                        fieldLabel:"Shipment Id",
                        name:"id"
                      },{
                        xtype:"textfield",
                        fieldLabel:"Order Id",
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
                                xtype:"combo",
                                fieldLabel:"",
                                name:"shippingFeeCurrency",
                                hiddenName:"shippingFeeCurrency",
                                width:50
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
                        xtype:"combo",
                        fieldLabel:"Status",
                        name:"status",
                        hiddenName:"status"
                      },{
                        layout:"table",
                        layoutConfig:{
                          columns:3
                        },
                        width:320,
                        border:false,
                        items:[{
                            width:105,
                            html:"<font size=2>Created:</font>",
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
                        xtype:"combo",
                        fieldLabel:"Country",
                        name:"shipToCountry",
                        hiddenName:"shipToCountry"
                      },{
                        xtype:"textfield",
                        fieldLabel:"Phone",
                        name:"shipToPhoneNo"
                      }]
                  }]
              },{
                xtype: 'panel',
                title: "Detail",
                autoHeight: true,
                items: shipmentDetailGrid
            }],
            buttons: [{
                        text: 'Save',
                        handler: function(){
                            Ext.Ajax.request({
                                waitMsg: 'Please wait...',
                                url: 'connect.php?moduleId=qo-shipments&action=saveShipmentInfo',
                                params: {
                                        id: '<?=$_GET['id']?>',
                                        ordersId: shipmentDetailForm.form.findField('ordersId').getValue(),
                                        shippingFeeCurrency: shipmentDetailForm.form.findField('shippingFeeCurrency').getValue(),
                                        shippingFeeValue: shipmentDetailForm.form.findField('shippingFeeValue').getValue(),
                                        status: shipmentDetailForm.form.findField('status').getValue(),
                                        remarks: shipmentDetailForm.form.findField('remarks').getValue(),
                                        shipToName: shipmentDetailForm.form.findField('shipToName').getValue(),
                                        shipToEmail: shipmentDetailForm.form.findField('shipToEmail').getValue(),
                                        shipToAddressLine1: shipmentDetailForm.form.findField('shipToAddressLine1').getValue(),
                                        shipToAddressLine2: shipmentDetailForm.form.findField('shipToAddressLine2').getValue(),
                                        shipToCity: shipmentDetailForm.form.findField('shipToCity').getValue(),
                                        shipToStateOrProvince: shipmentDetailForm.form.findField('shipToStateOrProvince').getValue(),
                                        shipToPostalCode: shipmentDetailForm.form.findField('shipToPostalCode').getValue(),
                                        shipToCountry: shipmentDetailForm.form.findField('shipToCountry').getValue(),
                                        shipToPhoneNo: shipmentDetailForm.form.findField('shipToPhoneNo').getValue()
                                },
                                success: function(response){
                                        var result = eval(response.responseText);
                                        switch (result) {
                                            case 1:
                                                 Ext.MessageBox.alert('Success', 'save Order Info success!');
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
        })
        
        shipmentDetailForm.getForm().load({url:'connect.php?moduleId=qo-shipments&action=getShipmentInfo', 
                method:'GET', 
                params: {id: '<?=$_GET['id']?>'}, 
                waitMsg:'Please wait...'
            }
        );
        
        shipmentDetailForm.render(document.body);
    </script>
</body>
</html>