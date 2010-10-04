/*!
 * Ext JS Library 3.2.1
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
Ext.onReady(function(){
    Ext.BLANK_IMAGE_URL = "../../ext-3.2.1/resources/images/default/s.gif";
    
    function showWait(){
        Ext.MessageBox.wait("please wait, thank you.");
    }
    
    function hideWait(){
        Ext.MessageBox.hide();
    }
    
    function exception(){
        Ext.Msg.alert('Failure', 'network error, please try again.');
    }
    
    Ext.Ajax.on('beforerequest', showWait);
    Ext.Ajax.on('requestcomplete', hideWait);
    Ext.Ajax.on('requestexception', exception);
     
    // NOTE: This is an example showing simple state management. During development,
    // it is generally best to disable state management as dynamically-generated ids
    // can change across page loads, leading to unpredictable results.  The developer
    // should ensure that stable state ids are set for stateful components in real apps.
    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

    // create some portlet tools using built in Ext tool ids
    var tools = [{
        id:'gear',
        handler: function(){
            Ext.Msg.alert('Message', 'The Settings tool was clicked.');
        }
    },{
        id:'close',
        handler: function(e, target, panel){
            panel.ownerCt.remove(panel, true);
        }
    }];
    
    var skuSaleGroupByCombo = new Ext.form.ComboBox({
        mode: 'local',
        store: ['', 'sellerId', 'ebayCity', 'ebayCountry', 'sku'],
        triggerAction: 'all',
        editable: false,
        selectOnFocus:true,
        name:'skuSaleGroupBy',
        hiddenName:'skuSaleGroupBy',
        listWidth: 100,
        width:100
    })
    
    var skuSaleStore = new Ext.data.JsonStore({
        autoLoad: true,
        root: 'records',
        totalProperty: 'totalCount',
        //idProperty: 'name',
        sortInfo: {
            field: 'sku',
            direction: 'ASC'
        },
        remoteSort: true,
        fields: ['sku', 'skuCost', 'skuLowestPrice', 'itemId', 'itemTitle', 'sellerId', 'buyerId', 'ebayCity', 'ebayCountry', 'salePrice', 'number'],
        url: 'service.php?action=getSkuSale'
    });
    
    var skuSaleGrid = new Ext.grid.GridPanel({
            //title: 'Waiting To Upload SKU List',
            store: skuSaleStore,
            //autoHeight: true,
            //autoScroll: true,
            //width: 1024,
            height: 475,
            selModel: new Ext.grid.RowSelectionModel({}),
            columns:[
                {header: "SKU", width: 70, align: 'center', sortable: true, dataIndex: 'sku'},
                {header: "Price", width: 40, align: 'center', sortable: true, dataIndex: 'salePrice'},
                {header: "Cost", width: 40, align: 'center', sortable: true, dataIndex: 'skuCost'},
                {header: "LP", width: 40, align: 'center', sortable: true, dataIndex: 'skuLowestPrice'},
                /*{header: "Item ID", width: 80, align: 'center', sortable: true, dataIndex: 'itemId'},*/
                {header: "Item Title", width: 300, align: 'center', sortable: true, dataIndex: 'itemTitle'},
                {header: "Seller", width: 90, align: 'center', sortable: true, dataIndex: 'sellerId'},
                /*{header: "Buyer", width: 80, align: 'center', sortable: true, dataIndex: 'buyerId'},*/
                {header: "City", width: 100, align: 'center', sortable: true, dataIndex: 'ebayCity'},
                {header: "Country", width: 80, align: 'center', sortable: true, dataIndex: 'ebayCountry'},
                {header: "Saled Num", width: 80, align: 'center', sortable: true, dataIndex: 'number'}
                /*{header: "Ship On", width: 80, align: 'center', sortable: true, dataIndex: 'shippedOn'}*/
            ],
            tbar:[{xtype: 'tbtext', text: 'Date Range:'},
                  {id: 'sku_sale_date_start', xtype: 'datefield', format: 'Y-m-d'},
                  {xtype: 'tbtext', text: '--'},
                  {id: 'sku_sale_date_end', xtype: 'datefield', format: 'Y-m-d'},
                  '-',{xtype: 'tbtext', text: 'Group By:'},
                  skuSaleGroupByCombo,
                  {text: "Submit", handler: function(){
                            skuSaleStore.baseParams = {date_start: Ext.getCmp('sku_sale_date_start').getValue(), date_end: Ext.getCmp('sku_sale_date_end').getValue(), group: skuSaleGroupByCombo.getValue()};
                            skuSaleStore.load();
                        }
                  },'-'],
            bbar: new Ext.PagingToolbar({
                store: skuSaleStore,
                displayInfo: true
            })
    });
    
    //------------------------------------------------------------------------------------------
    
    var skuShipGroupByCombo = new Ext.form.ComboBox({
        mode: 'local',
        store: ['', 'sellerId', 'shipToCity', 'shipToCountry', 'sku'],
        triggerAction: 'all',
        editable: false,
        selectOnFocus:true,
        name:'skuShipGroupBy',
        hiddenName:'skuShipGroupBy',
        listWidth: 100,
        width:100
    })
    
    var skuShipStore = new Ext.data.JsonStore({
        autoLoad: true,
        root: 'records',
        totalProperty: 'totalCount',
        //idProperty: 'name',
        sortInfo: {
            field: 'sku',
            direction: 'ASC'
        },
        remoteSort: true,
        fields: ['sku', 'skuCost', 'skuLowestPrice', 'itemId', 'itemTitle', 'sellerId', 'buyerId', 'shipToCity', 'shipToCountry', 'shippedOn', 'salePrice', 'number'],
        url: 'service.php?action=getSkuShip'
    });
    
    var skuShipGrid = new Ext.grid.GridPanel({
            //title: 'Waiting To Upload SKU List',
            store: skuShipStore,
            //autoHeight: true,
            //autoScroll: true,
            //width: 1024,
            height: 475,
            selModel: new Ext.grid.RowSelectionModel({}),
            columns:[
                {header: "SKU", width: 70, align: 'center', sortable: true, dataIndex: 'sku'},
                {header: "Price", width: 40, align: 'center', sortable: true, dataIndex: 'salePrice'},
                {header: "Cost", width: 40, align: 'center', sortable: true, dataIndex: 'skuCost'},
                {header: "LP", width: 40, align: 'center', sortable: true, dataIndex: 'skuLowestPrice'},
                /*{header: "Item ID", width: 80, align: 'center', sortable: true, dataIndex: 'itemId'},*/
                {header: "Item Title", width: 300, align: 'center', sortable: true, dataIndex: 'itemTitle'},
                {header: "Seller", width: 90, align: 'center', sortable: true, dataIndex: 'sellerId'},
                /*{header: "Buyer", width: 80, align: 'center', sortable: true, dataIndex: 'buyerId'},*/
                {header: "City", width: 100, align: 'center', sortable: true, dataIndex: 'shipToCity'},
                {header: "Country", width: 80, align: 'center', sortable: true, dataIndex: 'shipToCountry'},
                {header: "Shiped Num", width: 80, align: 'center', sortable: true, dataIndex: 'number'}
                /*{header: "Ship On", width: 80, align: 'center', sortable: true, dataIndex: 'shippedOn'}*/
            ],
            tbar:[{xtype: 'tbtext', text: 'Date Range:'},
                  {id: 'date_start', xtype: 'datefield', format: 'Y-m-d'},
                  {xtype: 'tbtext', text: '--'},
                  {id: 'date_end', xtype: 'datefield', format: 'Y-m-d'},
                  '-',{xtype: 'tbtext', text: 'Group By:'},
                  skuShipGroupByCombo,
                  {text: "Submit", handler: function(){
                            skuShipStore.baseParams = {date_start: Ext.getCmp('date_start').getValue(), date_end: Ext.getCmp('date_end').getValue(), group: skuShipGroupByCombo.getValue()};
                            skuShipStore.load();
                        }
                  },'-'],
            bbar: new Ext.PagingToolbar({
                store: skuShipStore,
                displayInfo: true
            })
    });
    
    var store = new Ext.data.JsonStore({
        fields:['name', 'visits', 'views'],
        data: [
            {name:'Jul 07', visits: 245000, views: 3000000},
            {name:'Aug 07', visits: 240000, views: 3500000},
            {name:'Sep 07', visits: 355000, views: 4000000},
            {name:'Oct 07', visits: 375000, views: 4200000},
            {name:'Nov 07', visits: 490000, views: 4500000},
            {name:'Dec 07', visits: 495000, views: 5800000},
            {name:'Jan 08', visits: 520000, views: 6000000},
            {name:'Feb 08', visits: 620000, views: 7500000}
        ]
    });
    
    var viewport = new Ext.Viewport({
        layout:'border',
        items:[/*{
            region:'west',
            id:'west-panel',
            title:'West',
            split:true,
            width: 200,
            minSize: 175,
            maxSize: 400,
            collapsible: true,
            margins:'35 0 5 5',
            cmargins:'35 5 5 5',
            layout:'accordion',
            layoutConfig:{
                animate:true
            },
            items: [{
                html: Ext.example.shortBogusMarkup,
                title:'Navigation',
                autoScroll:true,
                border:false,
                iconCls:'nav'
            },{
                title:'Settings',
                html: Ext.example.shortBogusMarkup,
                border:false,
                autoScroll:true,
                iconCls:'settings'
            }]
        },*/{
            xtype:'portal',
            region:'center',
            margins:'5 5 5 5',
            items:[{
                columnWidth:.7,
                style:'padding:10px 0 10px 10px',
                items:[{
                    title: 'Sku Saled',
                    layout:'fit',
                    tools: tools,
                    items: skuSaleGrid
                },{
                    title: 'Sku Shipped',
                    layout:'fit',
                    tools: tools,
                    items: skuShipGrid
                }]
            },{
                columnWidth:.3,
                style:'padding:10px 0 10px 10px',
                items:[{
                    title: 'Seller Sale Chart',
                    tools: tools,
                    html: "test"
                },{
                    title: 'Seller Shipped Chart',
                    tools: tools,
                    width:500,
                    height:300,
                    items: {
                        xtype: 'linechart',
                        store: store,
                        url: '../../ext-3.2.1/resources/charts.swf',
                        
                        /*
                        xField: 'name',
                        yField: 'visits',
                        yAxis: new Ext.chart.NumericAxis({
                            displayName: 'Visits',
                            labelRenderer : Ext.util.Format.numberRenderer('0,0')
                        }),
                        tipRenderer : function(chart, record){
                            return Ext.util.Format.number(record.data.visits, '0,0') + ' visits in ' + record.data.name;
                        }
                        */
                        xField: 'name',
                        series: [{
                            type:'line',
                            displayName: 'Page Views',
                            yField: 'views'/*,
                            style: {
                                color:0x99BBE8
                            }*/
                        },{
                            type:'line',
                            displayName: 'Visits',
                            yField: 'visits'/*,
                            style: {
                                color: 0x15428B
                            }*/
                        }]
                    }
                }]
            }]
            
            /*
             * Uncomment this block to test handling of the drop event. You could use this
             * to save portlet position state for example. The event arg e is the custom 
             * event defined in Ext.ux.Portal.DropZone.
             */
//            ,listeners: {
//                'drop': function(e){
//                    Ext.Msg.alert('Portlet Dropped', e.panel.title + '<br />Column: ' + 
//                        e.columnIndex + '<br />Position: ' + e.position);
//                }
//            }
        }]
    });
});

