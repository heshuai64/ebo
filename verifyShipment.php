<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="PRAGMA" content="NO-CACHE">
        <meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
        <meta http-equiv="EXPIRES" content="-1">
        
        <title>Verify Shipment</title>
        
        <!-- EXT JS LIBRARY -->
        <link rel="stylesheet" type="text/css" href="../Ext/2.2/resources/css/ext-all.css" />
        <script src="../Ext/2.2/adapter/ext/ext-base.js"></script>
        <script src="../Ext/2.2/ext-all-debug.js"></script>
</head>
<body>
    <script type="text/javascript">
        var verifyshipmentStore = new Ext.data.JsonStore({
            root: 'records',
            totalProperty: 'totalCount',
            idProperty: 'id',
            fields: ['id', 'skuId', 'skuTitle', 'itemId', 'itemTitle', 'quantity'],
            url:'connect.php?moduleId=qo-shipments&action=verifyShipment'
        });
        
        function renderSkuImage(v, p, r){
            return String.format('<img src="http://m2.sourcingmap.com/smap/images/item/medium/ux_{0}_ux_m.jpg"', v);
        }
        
        function renderShipmentInfo(v, p, r){
            return String.format(' X {0} <br><br> {1} <br><br> {2}', r.data.quantity , r.data.skuId , r.data.skuTitle);
        }
        
        var verifyshipmentGrid = new Ext.grid.EditorGridPanel({
                autoHeight: true,
                store: verifyshipmentStore,
                selModel: new Ext.grid.RowSelectionModel({}),
                columns:[{
                    header: "Image",
                    dataIndex: 'skuId',
                    renderer: renderSkuImage,
                    width: 105,
                    align: 'center'
                },{
                    header: "Info",
                    dataIndex: 'skuId',
                    renderer: renderShipmentInfo,
                    width: 500,
                    align: 'center'
                }]
        });
                
        var verifyShipmentForm = new Ext.FormPanel({
            id:"verify-shipment-form",
            autoScroll:true,
            title:"Verify Shipment",
            items:[{
                    layout:"column",
                    items:[{
                        columnWidth:0.3,
                        layout:"form",
                        border:false,
                        items:[{
                            xtype:"textfield",
                            fieldLabel:"ShipmentId",
                            name:"id",
                            listeners:{specialkey: function(t, e){
                                    if(e.getKey() == 13){
                                        verifyshipmentStore.load({params:{id:verifyShipmentForm.getForm().findField('id').getValue()}});
                                        Ext.getCmp('verify-shipment-form').form.findField('id').focus(true);
                                    }
                                }
                            }
                        }]   
                      },{
                        columnWidth:0.7,
                        layout:"form",
                        border:false,
                        items:[{
                            xtype:"button",
                            text:"Submit",
                            handler: function(){
                                verifyshipmentStore.load({params:{id:verifyShipmentForm.getForm().findField('id').getValue()}});
                                Ext.getCmp('verify-shipment-form').form.findField('id').focus(true);
                            }
                          }]
                      }]
                    },{
                        xtype: 'panel',
                        autoHeight: true,
                        items: verifyshipmentGrid
                    }
                ]
        });
        
        verifyShipmentForm.render(document.body);
         
    </script>
</body>
</html>