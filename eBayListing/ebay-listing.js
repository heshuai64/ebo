Ext.onReady(function(){

    // NOTE: This is an example showing simple state management. During development,
    // it is generally best to disable state management as dynamically-generated ids
    // can change across page loads, leading to unpredictable results.  The developer
    // should ensure that stable state ids are set for stateful components in real apps.
    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
    
   var viewport = new Ext.Viewport({
        layout:'border',
        items:[
            new Ext.BoxComponent({ // raw
                region:'north',
                el: 'north',
                height:32
            }),{
                region:'south',
                contentEl: 'south',
                split:true,
                height: 100,
                minSize: 100,
                maxSize: 200,
                collapsible: true,
                title:'South',
                margins:'0 0 0 0'
            }, {
                region:'east',
                title: 'East Side',
                collapsible: true,
                split:true,
                width: 225,
                minSize: 175,
                maxSize: 400,
                layout:'fit',
                margins:'0 5 0 0',
                items:
                    new Ext.TabPanel({
                        border:false,
                        activeTab:1,
                        tabPosition:'bottom',
                        items:[{
                            html:'<p>A TabPanel component can be a region.</p>',
                            title: 'A Tab',
                            autoScroll:true
                        },
                        new Ext.grid.PropertyGrid({
                            title: 'Property Grid',
                            closable: true,
                            source: {
                                "(name)": "Properties Grid",
                                "grouping": false,
                                "autoFitColumns": true,
                                "productionQuality": false,
                                "created": new Date(Date.parse('10/15/2006')),
                                "tested": false,
                                "version": .01,
                                "borderWidth": 1
                            }
                        })]
                    })
             },{
                region:'west',
                id:'west-panel',
                title:'West',
                split:true,
                width: 200,
                minSize: 175,
                maxSize: 400,
                collapsible: true,
                margins:'0 0 0 5',
                layout:'accordion',
                layoutConfig:{
                    animate:true
                },
                items: [{
                    contentEl: 'west',
                    title:'Navigation',
                    border:false,
                    iconCls:'nav'
                },{
                    title:'Settings',
                    html:'<p>Some settings in here.</p>',
                    border:false,
                    iconCls:'settings'
                }]
            },
            new Ext.TabPanel({
                region:'center',
                deferredRender:false,
                activeTab:0,
                items:[{
                    contentEl:'center1',
                    title: 'Close Me',
                    closable:true,
                    autoScroll:true
                },{
                    contentEl:'center2',
                    title: 'Center Panel',
                    autoScroll:true
                }]
            })
         ]
    });
    Ext.get("hideit").on('click', function() {
       var w = Ext.getCmp('west-panel');
       w.collapsed ? w.expand() : w.collapse(); 
    });
});