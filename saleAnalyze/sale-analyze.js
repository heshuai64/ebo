Ext.onReady(function(){
    if (!Ext.grid.GridView.prototype.templates) {  
       Ext.grid.GridView.prototype.templates = {};  
    }
    
    Ext.grid.GridView.prototype.templates.cell = new Ext.Template(
        '<td class="x-grid3-col x-grid3-cell x-grid3-td-{id} x-selectable {css}" style="{style}" tabIndex="0" {cellAttr}>',
            '<div class="x-grid3-cell-inner x-grid3-col-{id}" {attr}>{value}</div>',
        '</td>'
    )

    Ext.Ajax.on('beforerequest', showSpinner);
    Ext.Ajax.on('requestcomplete', hideSpinner);
    Ext.Ajax.on('requestexception', hideSpinner);
    
    function showSpinner(){
        Ext.Msg.wait('Please waiting ...');
    }
    
    function hideSpinner(){
        Ext.Msg.hide();
    }
    
    var store = new Ext.data.JsonStore({
        root: 'records',
        totalProperty: 'totalrecords',
        fields: ['sku', 'title', 'cn_title', 'status', 'day_1', 'week_1', 'week_2', 'week_3', 'month_1', 'v_stock'],
        url: 'service.php?action=statisticsSkuSale'
    });
    
    var grid = new Ext.grid.GridPanel({
        //id:"sku-grid",
        //width:700,
        height:500,
        //autoHeight:true,
        //title:'ExtJS.com - Browse Forums',
        store: store,
        //sm: new Ext.grid.CellSelectionModel(),
        columns:[{
            header: "SKU",
            dataIndex: 'sku',
            sortable: true,
            width: 70
        },{
            header: "Title",
            dataIndex: 'title',
            width: 170
        },{
            header: "CN Title",
            dataIndex: 'cn_title',
            width: 170
        },{
            header: "Status",
            dataIndex: 'status',
            width: 70
        },{
            header: "Toady",
            dataIndex: 'day_1',
            sortable: true,
            width: 50    
        },{
            header: "7 Day",
            dataIndex: 'week_1',
            sortable: true,
            width: 50    
        },{
            header: "14 Day",
            dataIndex: 'week_2',
            sortable: true,
            width: 50    
        },{
            header: "21 Day",
            dataIndex: 'week_3',
            sortable: true,
            width: 50    
        },{
            header: "30 Day",
            dataIndex: 'month_1',
            sortable: true,
            width: 50    
        },{
            header: "Virtual Stock",
            dataIndex: 'v_stock',
            sortable: true,
            width: 80    
        }]
    })
    
    var store2 = new Ext.data.JsonStore({
        root: 'records',
        totalProperty: 'totalrecords',
        fields: ['Seller', 'SKU', 'Status', 'TemplateID', 'Title', 'PrimaryCategoryCategoryName', 'ListingType', 'StartPrice', 'BuyItNowPrice', 'SkuLowPrice', 'ScheduleTemplateName', 'ForeverListingTime', 'ListingDuration', 'TotalQuantitySold', 'SoldRate', 'V_Stock'],
        url: 'service.php?action=getLingSale'
    });
    
    var grid2 = new Ext.grid.GridPanel({
        //id:"sku-grid",
        //width:700,
        height:500,
        //autoHeight:true,
        //title:'ExtJS.com - Browse Forums',
        store: store2,
        //width: 600,
        //sm: new Ext.grid.CellSelectionModel(),
        columns:[{
            header: "Seller",
            dataIndex: 'Seller',
            width: 90
        },{
            header: "SKU",
            dataIndex: 'SKU',
            width: 70
        },{
            header: "Status",
            dataIndex: 'Status',
            width: 70
        },{
            header: "Template",
            dataIndex: 'TemplateID',
            width: 55
        },{
            header: "Title",
            dataIndex: 'Title',
            width: 320    
        },{
            header: "Category",
            dataIndex: 'PrimaryCategoryCategoryName',
            width: 80    
        },{
            header: "Sales Type",
            dataIndex: 'ListingType',
            width: 80    
        },{
            header: "Start Price",
            dataIndex: 'StartPrice',
            width: 70    
        },{
            header: "Price",
            dataIndex: 'BuyItNowPrice',
            width: 50    
        },{
            header: "Lowest Price",
            dataIndex: 'SkuLowPrice',
            width: 80    
        },{
            header: "Schedule",
            dataIndex: 'ScheduleTemplateName',
            width: 100        
        },{
            header: "Forever Time",
            dataIndex: 'ForeverListingTime',
            width: 80        
        },{
            header: "Duration",
            dataIndex: 'ListingDuration',
            width: 60        
        },{
            header: "Sales Of Quantity",
            dataIndex: 'TotalQuantitySold',
            width: 100        
        },{
            header: "Sales Of Rate",
            dataIndex: 'SoldRate',
            width: 80        
        },{
            header: "Virtual Stock",
            dataIndex: 'V_Stock',
            width: 80        
        }]
    })
    
    var viewport = new Ext.Viewport({
        layout: 'border',
        items: [{
            region: 'west',
            id: 'west-panel', // see Ext.getCmp() below
            //title: 'Function',
            split: true,
            width: 200,
            minSize: 175,
            maxSize: 400,
            collapsible: true,
            margins: '0 0 0 5',
            layout: {
                type: 'accordion',
                animate: true
            },
            items: [{
                title: 'Function',
                layout: {
                    type:'vbox',
                    padding:'5',
                    align:'stretch'
                },
                items: [{
                    xtype:'button',
                    text: 'SKU Sales Statistics',
                    handler:function(){
                        var searchForm = new Ext.FormPanel({
                            labelWidth: 160,
                            items:[{
                                id:"sku",
                                fieldLabel: 'SKU(support multi-line)',
                                xtype: 'textarea', 
                                name: 'sku',
                                width: 350,
                                height: 80
                            },{
                                id:"title",
                                fieldLabel: 'Title',
                                xtype: 'textfield', 
                                name: 'title',
                                width: 350    
                            },{
                                id:"cn_title",
                                fieldLabel: 'CN Title',
                                xtype: 'textfield', 
                                name: 'cn_title',
                                width: 350    
                            },{
                                id:"status",
                                fieldLabel: 'Status',
                                xtype: 'combo', 
                                name: 'status',
                                editable:false,
                                mode: 'local',
                                triggerAction: 'all',
                                store: ['new', 'waiting for approve', 'under review', 'active', 'inactive', 'out of stock']
                            }]  
                        })
                        
                        var searchWindows = new Ext.Window({
                            width: 560,
                            height:260,
                            items: searchForm,
                            buttonAlign: 'center',
                            buttons: [{
                                text:'Search',
                                handler: function(){
                                    store.setBaseParam("sku", Ext.getCmp("sku").getValue());
                                    store.setBaseParam("title", Ext.getCmp("title").getValue());
                                    store.setBaseParam("cn_title", Ext.getCmp("cn_title").getValue());
                                    store.setBaseParam("status", Ext.getCmp("status").getValue());
                                    store.load({params: {start: 0, limit: 15}});
                                    searchWindows.close();
                                    Ext.getCmp("tab-panel").activate("sku-sales-statistics");
                                }
                            },{
                                text:'Close',
                                handler: function(){
                                    searchWindows.close();
                                }
                            }]
                        });
                        searchWindows.show();
                    }
                },{
                    xtype:'button',
                    text: 'Listing Sales Analyze',
                    handler:function(){
                        var searchForm = new Ext.FormPanel({
                            labelWidth: 160,
                            items:[{
                                id:"sku",
                                fieldLabel: 'SKU(support multi-line)',
                                xtype: 'textarea', 
                                name: 'sku',
                                width: 350,
                                height: 80
                            },{
                                id:"title",
                                fieldLabel: 'Title',
                                xtype: 'textfield', 
                                name: 'title',
                                width: 350    
                            },{
                                id:"cn_title",
                                fieldLabel: 'CN Title',
                                xtype: 'textfield', 
                                name: 'cn_title',
                                width: 350    
                            },{
                                id:"status",
                                fieldLabel: 'Status',
                                xtype: 'combo', 
                                name: 'status',
                                editable:false,
                                mode: 'local',
                                triggerAction: 'all',
                                store: ['new', 'waiting for approve', 'under review', 'active', 'inactive', 'out of stock']
                            },{
                                layout:"column",
                                //defaults:{labelWidth: 130},
                                items:[{
                                    columnWidth:0.5,
                                    layout:"form",
                                    border:false,
                                    items:[{
                                        id:"start",    
                                        fieldLabel: 'From',
                                        xtype: 'datefield',
                                        format: 'Y-m-d'
                                    }]
                                },{
                                    columnWidth:0.5,
                                    layout:"form",
                                    border:false,
                                    items:[{
                                        id:"end",    
                                        fieldLabel: 'To',
                                        xtype: 'datefield',
                                        format: 'Y-m-d'
                                    }]
                                }]
                            }]  
                        })
                        
                        var searchWindows = new Ext.Window({
                            width: 560,
                            height:260,
                            items: searchForm,
                            buttonAlign: 'center',
                            buttons: [{
                                text:'Search',
                                handler: function(){
                                    store2.setBaseParam("sku", Ext.getCmp("sku").getValue());
                                    store2.setBaseParam("title", Ext.getCmp("title").getValue());
                                    store2.setBaseParam("cn_title", Ext.getCmp("cn_title").getValue());
                                    store2.setBaseParam("status", Ext.getCmp("status").getValue());
                                    store2.setBaseParam("start", Ext.getCmp("start").getValue().format("Y-m-d"));
                                    store2.setBaseParam("end", Ext.getCmp("end").getValue().format("Y-m-d"));
                                    store2.load();
                                    searchWindows.close();
                                    Ext.getCmp("tab-panel").activate("listing-sales-analyze");
                                }
                            },{
                                text:'Close',
                                handler: function(){
                                    searchWindows.close();
                                }
                            }]
                        });
                        searchWindows.show();
                    }
                }]
            }]
        },
        new Ext.TabPanel({
            id: "tab-panel",
            region: 'center', // a center region is ALWAYS required for border layout
            deferredRender: false,
            activeTab: 0,     // first tab initially active
            items: [{
                id:"sku-sales-statistics",
                title: 'Sku Sales Statistics',
                autoScroll: true,
                items: grid
            },{
                id:"listing-sales-analyze",
                title: 'Listing Sales Analyze',
                autoScroll: true,
                items: grid2    
            }]
        })]
    })
    
    
})