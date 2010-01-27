Ext.onReady(function(){
    
    var outstandingShipmentStore = new Ext.data.JsonStore({
        root: 'records',
        totalProperty: 'totalCount',
        idProperty: 'id',
        //remoteSort: true,
        fields: ['id', 'shipToName', 'shipToEmail', 'ordersId', 'sellerId', 'createdOn','shipmentMethod','desc','status'],
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
        columns:[expander,{
            header: "Shipment Id",
            dataIndex: 'id',
            width: 100,
            align: 'center',
            sortable: true
        },{
            header: "shipTo",
            dataIndex: 'shipToName',
            width: 120,
            align: 'center',
            sortable: true
        },{
            header: "Order Id",
            dataIndex: 'ordersId',
            width: 110,
            align: 'center',
            sortable: true
        },{
            header: "Seller Id",
            dataIndex: 'sellerId',
            width: 100,
            align: 'center',
            sortable: true
        },{
            header: "Created On",
            dataIndex: 'createdOn',
            width: 150,
            align: 'center',
            sortable: true
        },{
            header: "ship Method",
            dataIndex: 'shipmentMethod',
            width: 100,
            renderer: renderShipmentMethod,
            align: 'center',
            sortable: true
        },{
            header: "Status",
            dataIndex: 'status',
            width: 100,
            renderer: renderStatus,
            align: 'center',
            sortable: true
        }],
        bbar: new Ext.PagingToolbar({
                pageSize: 20,
                store: outstandingShipmentStore,
                displayInfo: true
        })
    });
    
    outstandingShipmentStore.load({params:{start:0, limit:20}});
})