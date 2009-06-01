Ext.onReady(function(){
    var salesReportStore = new Ext.data.JsonStore({
        root: 'records',
        totalProperty: 'totalCount',
        idProperty: 'sku_id',
        autoLoad:true,
        fields: ['sku_id', '1_Mon_quantity', '1_Tue_quantity', '1_Wed_quantity', '1_Thu_quantity', '1_Fri_quantity', '1_Sat_quantity', '1_Sun_quantity', '1_total_num', '2_Mon_quantity', '2_Tue_quantity', '2_Wed_quantity', '2_Thu_quantity', '2_Fri_quantity', '2_Sat_quantity', '2_Sun_quantity', '2_total_num', '3_Mon_quantity', '3_Tue_quantity', '3_Wed_quantity', '3_Thu_quantity', '3_Fri_quantity', '3_Sat_quantity', '3_Sun_quantity', '3_total_num', '4_Mon_quantity', '4_Tue_quantity', '4_Wed_quantity', '4_Thu_quantity', '4_Fri_quantity', '4_Sat_quantity', '4_Sun_quantity', '4_total_num',],
        url:'reports.php?type=salesReport'
    });
    
    var salesReportGrid = new Ext.grid.GridPanel({
        //id:'button-grid',
        store: salesReportStore,
        //autoHeight: true,
        width: 999,
        height: 720,
        frame:true,
        //autoScroll: true,
        selModel: new Ext.grid.RowSelectionModel({}),
        columns:[
            {header: "SKU", width: 100, align: 'center', sortable: true, dataIndex: 'sku_id'},
            {header: "11", width: 50, align: 'center', sortable: true, dataIndex: '1_Mon_quantity'},
            {header: "12", width: 50, align: 'center', sortable: true, dataIndex: '1_Tue_quantity'},
            {header: "13", width: 50, align: 'center', sortable: true, dataIndex: '1_Wed_quantity'},
            {header: "14", width: 50, align: 'center', sortable: true, dataIndex: '1_Thu_quantity'},
            {header: "15", width: 50, align: 'center', sortable: true, dataIndex: '1_Fri_quantity'},
            {header: "16", width: 50, align: 'center', sortable: true, dataIndex: '1_Sat_quantity'},
            {header: "17", width: 50, align: 'center', sortable: true, dataIndex: '1_Sun_quantity'},
            {header: "21", width: 50, align: 'center', sortable: true, dataIndex: '2_Mon_quantity'},
            {header: "22", width: 50, align: 'center', sortable: true, dataIndex: '2_Tue_quantity'},
            {header: "23", width: 50, align: 'center', sortable: true, dataIndex: '2_Wed_quantity'},
            {header: "24", width: 50, align: 'center', sortable: true, dataIndex: '2_Thu_quantity'},
            {header: "25", width: 50, align: 'center', sortable: true, dataIndex: '2_Fri_quantity'},
            {header: "26", width: 50, align: 'center', sortable: true, dataIndex: '2_Sat_quantity'},
            {header: "27", width: 50, align: 'center', sortable: true, dataIndex: '2_Sun_quantity'},
            {header: "31", width: 50, align: 'center', sortable: true, dataIndex: '3_Mon_quantity'},
            {header: "32", width: 50, align: 'center', sortable: true, dataIndex: '3_Tue_quantity'},
            {header: "33", width: 50, align: 'center', sortable: true, dataIndex: '3_Wed_quantity'},
            {header: "34", width: 50, align: 'center', sortable: true, dataIndex: '3_Thu_quantity'},
            {header: "35", width: 50, align: 'center', sortable: true, dataIndex: '3_Fri_quantity'},
            {header: "36", width: 50, align: 'center', sortable: true, dataIndex: '3_Sat_quantity'},
            {header: "37", width: 50, align: 'center', sortable: true, dataIndex: '3_Sun_quantity'},
            {header: "41", width: 50, align: 'center', sortable: true, dataIndex: '4_Mon_quantity'},
            {header: "42", width: 50, align: 'center', sortable: true, dataIndex: '4_Tue_quantity'},
            {header: "43", width: 50, align: 'center', sortable: true, dataIndex: '4_Wed_quantity'},
            {header: "44", width: 50, align: 'center', sortable: true, dataIndex: '4_Thu_quantity'},
            {header: "45", width: 50, align: 'center', sortable: true, dataIndex: '4_Fri_quantity'},
            {header: "46", width: 50, align: 'center', sortable: true, dataIndex: '4_Sat_quantity'},
            {header: "47", width: 50, align: 'center', sortable: true, dataIndex: '4_Sun_quantity'}
        ]
    })
    
    //salesReportGrid.render();
    /*
    var p = Ext.Panel({
        autoScroll: true,
        title: "Sales Report",
        autoHeight: true,
        width: 600,
        html: 'test',
        items: salesReportGrid
    })
    
    p.render(document.body);
    */
    
    var viewport = new Ext.Viewport({
        layout:'border',
        items:[{
                xtype: 'panel',
                title:'Sales Report',
                region:'center',
                autoScroll: true,
                //width: 800,
                items:salesReportGrid
        }]
    })
    
})