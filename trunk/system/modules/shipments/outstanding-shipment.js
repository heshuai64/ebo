Ext.onReady(function(){
    
    var outstandingShipmentStore = new Ext.data.JsonStore({
        root: 'records',
        totalProperty: 'totalCount',
        idProperty: 'id',
        //remoteSort: true,
        fields: ['id', 'shipToName', 'shipToEmail', 'ordersId', 'sellerId', 'createdOn','shipmentMethod','desc','status','remarks'],
        url:'connect.php?moduleId=qo-shipments&action=outstandingShipment'
    });
    
    function renderStatus(v, p, r){
        return lang.shipments.shipments_status_json[v]
    }
    
    function renderShipmentMethod(v, p, r){
        return lang.shipments.shipments_method_json[v]
    }         
    
    var expander = new Ext.ux.grid.RowExpander({
        tpl : new Ext.Template(
            '{desc}'
        )
    });
    
    var outstandingShipmentGrid = new Ext.grid.GridPanel({
        store: outstandingShipmentStore,
        renderTo: "outstanding-shipment",
        height: 600,
        plugins: expander,
        selModel: new Ext.grid.RowSelectionModel({}),
        tbar:[{
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
        },{
            xtype: 'combo',
            mode: 'local',
            store: new Ext.data.SimpleStore({
                fields: ['id', 'name'],
                data: [['Y', 'In Stock'], ['N', 'Out Stock'], ['', 'All']]
            }),
            valueField:'id',
            displayField:'name',
            triggerAction: 'all',
            editable: false,
            selectOnFocus:true,
            name: 'stock',
            hiddenName:'stock',
            width: 100
        },{
            text: 'Filter',
            handler: function(){
                //outstandingShipmentStore.setBaseParam('sellerId', {bar:3});
                var toolbar = outstandingShipmentGrid.getTopToolbar();
                
                outstandingShipmentStore.baseParams = { searchType: '',
                                                        searchValue: ''};
                                                            
                if(!Ext.isEmpty(toolbar.items.items[0].value)){
                    outstandingShipmentStore.baseParams = { sellerId: toolbar.items.items[0].value,
                                                            searchType: '',
                                                            searchValue: ''};
                }
                
                if(!Ext.isEmpty(toolbar.items.items[1].value)){
                    outstandingShipmentStore.baseParams = { stock: toolbar.items.items[1].value,
                                                            searchType: '',
                                                            searchValue: ''};
                }
                
                if(!Ext.isEmpty(toolbar.items.items[0].value) && !Ext.isEmpty(toolbar.items.items[1].value)){
                    outstandingShipmentStore.baseParams = {
                                                            sellerId: toolbar.items.items[0].value,
                                                            stock: toolbar.items.items[1].value,
                                                            searchType: '',
                                                            searchValue: ''
                                                        };
                }
                outstandingShipmentStore.load({params:{start:0, limit:200}});
            }
        },'-',{
            xtype: 'combo',
            mode: 'local',
            store: new Ext.data.SimpleStore({
                fields: ['id', 'name'],
                data: [['1', 'SKU'], ['2', 'Order ID'], ['3', 'Shipment ID']]
            }),
            valueField:'id',
            displayField:'name',
            triggerAction: 'all',
            editable: false,
            selectOnFocus:true,
            name: 'searchType',
            hiddenName:'searchType',
            width: 100    
        },{
            id:'searchValue',
            xtype:'textfield',
            name:'searchValue',
            hiddenName:'searchValue'
        },{
            text: 'Search',
            handler: function(){
                var toolbar = outstandingShipmentGrid.getTopToolbar();
                if(!Ext.isEmpty(toolbar.items.items[4].value)){
                    outstandingShipmentStore.baseParams = {searchType: toolbar.items.items[4].value, searchValue: Ext.getCmp('searchValue').getValue()};
                    outstandingShipmentStore.load({params:{start:0, limit:200}});
                }
            }
        },'-',{
            text: 'Set Remark',
            handler: function(){
                var selections = outstandingShipmentGrid.selModel.getSelections();
                //console.log(selections[0].data.id);
                if(outstandingShipmentGrid.selModel.getCount() == 0){
                    Ext.MessageBox.alert('Warning','Please select a row.');
                    return 0;
                }
                var  remarkWindow = new Ext.Window({
                    title: 'Remark' ,
                    closable:true,
                    width: 400,
                    height: 400,
                    plain:true,
                    layout: 'form',
                    items: [{
                                fieldLabel:'Email',
                                xtype:'textfield',
                                readOnly:true,
                                value:selections[0].data.shipToEmail,
                                width:250
                         },{
                                id:'remarks',
                                fieldLabel:'Remark',
                                xtype:'textarea',
                                width: 250,
                                height: 280,
                                value:selections[0].data.remarks
                         }
                    ],
                    buttons: [{
                                   text: 'Submit',
                                   handler: function(){
                                        Ext.Ajax.request({  
                                            waitMsg: 'Please Wait',
                                            url: 'connect.php?moduleId=qo-shipments&action=outstandingShipment&subAction=updateRemark', 
                                            params: {
                                                shipmentId: selections[0].data.id,
                                                remarks: Ext.getCmp('remarks').getValue()
                                            }, 
                                            success: function(response){
                                                var result = eval(response.responseText);
                                                 //console.log(result);
                                                 if(result[0].success){
                                                      Ext.MessageBox.alert('Success', result[0].msg);      
                                                 }else{
                                                      Ext.MessageBox.alert('Failure', result[0].msg);      
                                                 }
                                            },
                                            failure: function(response){
                                                var result=response.responseText;
                                                Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                            }
                                        });
                                        
                                        outstandingShipmentStore.load({params:{start:0, limit:200}});
                                        remarkWindow.close();
                                   }
                              },{
                                   text: 'Close',
                                   handler: function(){
                                        remarkWindow.close();
                                   }
                              }]
                              
               })
               
               remarkWindow.show();
               return 1;
            }
        }],
        columns:[expander,{
            header: "Shipment Id",
            dataIndex: 'id',
            width: 100,
            align: 'center',
            sortable: true
        }/*,{
            header: "shipTo",
            dataIndex: 'shipToName',
            width: 120,
            align: 'center',
            sortable: true
        }*/,{
            header: "Order Id",
            dataIndex: 'ordersId',
            width: 100,
            align: 'center',
            sortable: true
        },{
            header: "Email",
            dataIndex: 'shipToEmail',
            width: 150,
            align: 'center',
            sortable: true
        },{
            header: "Seller Id",
            dataIndex: 'sellerId',
            width: 110,
            align: 'center',
            sortable: true
        },{
            header: "Created On",
            dataIndex: 'createdOn',
            width: 110,
            align: 'center',
            sortable: true
        },{
            header: "ship Method",
            dataIndex: 'shipmentMethod',
            width: 65,
            renderer: renderShipmentMethod,
            align: 'center',
            sortable: true
        },{
            header: "Status",
            dataIndex: 'status',
            width: 60,
            renderer: renderStatus,
            align: 'center',
            sortable: true
        },{
            header: "Remark",
            dataIndex: 'remarks',
            width: 180,
            align: 'center',
            sortable: true
        }],
        bbar: new Ext.PagingToolbar({
                pageSize: 200,
                store: outstandingShipmentStore,
                displayInfo: true
        })
    });
    
    outstandingShipmentStore.load({params:{start:0, limit:200}});
})