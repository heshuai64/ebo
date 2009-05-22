Ext.onReady(function(){
     var inventory_service_address = "/einv2/service.php";
     Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
     
     var inventory_search_form = new Ext.FormPanel({
          title: 'Search',
          items:[{
              layout:"column",
              text:"test",
              items:[{
                  columnWidth:0.5,
                  layout:"form",
                  items:[{
                      xtype:"textfield",
                      fieldLabel:"Sku",
                      name:"inventory_model_code"
                    },{
                      xtype:"textfield",
                      fieldLabel:"Model",
                      name:"short_description"
                    }]
                },{
                  columnWidth:0.5,
                  layout:"form",
                  items:[{
                         xtype: 'combo',
                         fieldLabel:"Category",
                         mode: 'local',
                         store: new Ext.data.JsonStore({
                             autoLoad: true,
                             fields: ['id', 'name'],
                             url: inventory_service_address + "?action=getCategories"
                         }),
                         valueField:'id',
                         displayField:'name',
                         triggerAction: 'all',
                         editable: false,
                         selectOnFocus:true,
                         name: 'category_id',
                         allowBlank: false,
                         hiddenName:'category_id'
                    },{
                         xtype:"combo",
                         xtype: 'combo',
                         fieldLabel:"Supplier",
                         mode: 'local',
                         store: new Ext.data.JsonStore({
                             autoLoad: true,
                             fields: ['id', 'name'],
                             url: inventory_service_address + "?action=getSuppliers"
                         }),
                         valueField:'id',
                         displayField:'name',
                         triggerAction: 'all',
                         editable: false,
                         selectOnFocus:true,
                         name: 'manufacturer_id',
                         allowBlank: false,
                         hiddenName:'manufacturer_id'
                    }]
                }]
               },{
                 xtype:"textfield",
                 fieldLabel:"Description",
                 name:"long_description"
          }],
          buttons: [{
                    text: 'Submit',
                    handler: function(){
                         inventory_store.baseParams = {
			      inventory_model_code: inventory_search_form.getForm().findField("inventory_model_code").getValue(),
                              short_description: inventory_search_form.getForm().findField("short_description").getValue(),
                              long_description: inventory_search_form.getForm().findField("long_description").getValue(),
                              category_id: inventory_search_form.getForm().findField("category_id").getValue(),
                              manufacturer_id: inventory_search_form.getForm().findField("manufacturer_id").getValue()
                         };
                         inventory_store.load({params:{start:0, limit:10}});
                    }
          }]
     })
               
     
     var inventory_store = new Ext.data.JsonStore({
                         root: 'records',
                         totalProperty: 'totalCount',
                         idProperty: 'id',
                         autoLoad:true,
                         fields: ['inventory_model_code', 'short_description', 'long_description', 'category', 'manufacturer', 'Weight', 'Cost'],
                         url: inventory_service_address + '?action=getAllSkus'
     });
     
     
     var inventory_grid = new Ext.grid.GridPanel({
          title: 'List',
          store: inventory_store,
          autoHeight: true,
          selModel: new Ext.grid.RowSelectionModel({}),
          columns:[
              {header: "Sku", width: 120, align: 'center', sortable: true, dataIndex: 'inventory_model_code'},
              {header: "Model", width: 120, align: 'center', sortable: true, dataIndex: 'short_description'},
              {header: "Description", width: 180, align: 'center', sortable: true, dataIndex: 'long_description'},
              {header: "Categpru", width: 100, align: 'center', sortable: true, dataIndex: 'category'},
              {header: "Supplier", width: 120, align: 'center', sortable: true, dataIndex: 'manufacturer'},
              {header: "Weight", width: 60, align: 'center', sortable: true, dataIndex: 'Weight'},
              {header: "Cost", width: 60, align: 'center', sortable: true, dataIndex: 'Cost'}
          ],
          bbar: new Ext.PagingToolbar({
              pageSize: 10,
              store: inventory_store,
              displayInfo: true
          })
     })
     
     inventory_grid.on("rowdblclick", function(oGrid){
          var oRecord = oGrid.getSelectionModel().getSelected();
          var form = 
          console.log(oRecord);
     })
     
     
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
                       title: 'Inventory Sku',
                       items: [inventory_search_form, inventory_grid],
                       autoScroll:true
                   },{
                       contentEl:'center2',
                       title: 'Listed Success Sku',
                       autoScroll:true
                   },{
                       contentEl:'center2',
                       title: 'Listed Failure Sku',
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