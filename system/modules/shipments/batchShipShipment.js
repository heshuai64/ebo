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
            width: 120,
            align: 'center',
            sortable: true
        },{
            header: "Address",
            dataIndex: 'address',
            width: 150,
            align: 'center',
            sortable: true
        }],
        tbar: [{
            text: 'Ship',
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
                                        },
                                        failure: function(response){
                                            var result=response.responseText;
                                            Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                        }
                                    });      
                        }
                    }
                    Ext.MessageBox.confirm('Confirmation', 'Split these items?', shipShipment);
                }
            }
        }],
        bbar: new Ext.PagingToolbar({
                pageSize: 20,
                store: shipmentGridStore,
                displayInfo: true
        })
    });
    
    shipmentGridStore.load({params:{start:0, limit:20}});
    
    shipmentGrid.render("batch-shipment");
})

/*
 INSERT INTO `ebaybo`.`qo_modules_actions` (
`id` ,
`qo_modules_id` ,
`name` ,
`description`
)
VALUES (
NULL , '12', 'getBatchShipShipment', NULL
), (
NULL , '12', 'batchShipShipment', NULL
);

INSERT INTO `ebaybo`.`qo_privileges_has_module_actions` (
`id` ,
`qo_privileges_id` ,
`qo_modules_actions_id`
)
VALUES (
NULL , '6', '64'
), (
NULL , '6', '65'
);

*/