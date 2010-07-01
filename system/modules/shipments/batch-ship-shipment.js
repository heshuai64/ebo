Ext.onReady(function(){
    var shipmentGridStore = new Ext.data.JsonStore({
        root: 'records',
        totalProperty: 'totalCount',
        idProperty: 'id',
        //remoteSort: true,
        fields: ['id', 'itemImage', 'address'],
        url:'connect.php?moduleId=qo-shipments&action=getBatchShipShipment'
    });
    
    var shipmentGrid = new Ext.grid.GridPanel({
        autoHeight: true,
        store: shipmentGridStore,
        columns:[{
            header: "Shipment Id",
            dataIndex: 'id',
            width: 110,
            align: 'center',
            sortable: true
        },{
            header: "Image",
            dataIndex: 'itemImage',
            width: 500,
            align: 'center',
            sortable: true
        },{
            header: "Address",
            dataIndex: 'address',
            width: 200,
            align: 'center',
            sortable: true
        }],
        tbar: [{
            text: 'Batch Ship',
            handler: function(){
                if(shipmentGrid.selModel.getCount() == 0){
                    Ext.MessageBox.alert('Warning','Please choice need ship shipment.'); 
                }else{
                    var shipShipment = function(btn){
                        if(btn=='yes'){
                                    var selections = shipmentGrid.selModel.getSelections();
                                    //console.log(selections);
                                    //var prez = [];
                                    var ids = "";
                                    for(i = 0; i< shipmentGrid.selModel.getCount(); i++){
                                        //prez.push(selections[i].data.id);
                                        ids += selections[i].data.id + ","
                                    }
                                    ids = ids.slice(0,-1);
                                    //console.log(prez);
                                    //var encoded_array = Ext.encode(prez);
                                    Ext.Ajax.request({  
                                        waitMsg: 'Please Wait',
                                        url: 'connect.php?moduleId=qo-shipments&action=batchShipShipment', 
                                        params: { 
                                          //ids:  encoded_array
                                          ids: ids
                                        }, 
                                        success: function(response){
                                            Ext.MessageBox.alert('Notification', response.responseText);
                                            shipmentGridStore.reload();
                                        },
                                        failure: function(response){
                                            var result=response.responseText;
                                            Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                        }
                                    });      
                        }
                    }
                    Ext.MessageBox.confirm('Confirmation', 'Ship Shipments?', shipShipment);
                }
            }
        }],
        bbar: new Ext.PagingToolbar({
                pageSize: 20,
                store: shipmentGridStore,
                displayInfo: true
        })
    });
    
    shipmentGrid.render("batch-shipment");
    shipmentGridStore.load({params:{start:0, limit:20}});
})