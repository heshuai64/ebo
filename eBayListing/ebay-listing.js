Ext.onReady(function(){

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