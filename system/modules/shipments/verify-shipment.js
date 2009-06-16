Ext.onReady(function(){
        var verifyshipmentStore = new Ext.data.JsonStore({
            root: 'records',
            totalProperty: 'totalCount',
            idProperty: 'id',
            fields: ['id', 'skuId', 'skuTitle', 'itemId', 'itemTitle', 'quantity', 'galleryURL'],
            url:'connect.php?moduleId=qo-shipments&action=verifyShipment'
        });
        
        function renderSkuImage(v, p, r){
            return String.format('<img src="{0}"', v);
        }
        
        function renderShipmentInfo(v, p, r){
            return String.format(' {0} X {1} <br> {2}', r.data.skuId , r.data.quantity, r.data.itemTitle);
        }
        
        var verifyshipmentGrid = new Ext.grid.EditorGridPanel({
                autoHeight: true,
                store: verifyshipmentStore,
                selModel: new Ext.grid.RowSelectionModel({}),
                columns:[{
                    header: "Image",
                    dataIndex: 'galleryURL',
                    renderer: renderSkuImage,
                    width: 450,
                    align: 'center'
                },{
                    header: "Info",
                    dataIndex: 'skuId',
                    renderer: renderShipmentInfo,
                    width: 450,
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
})