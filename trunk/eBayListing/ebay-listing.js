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
                         inventory_store.load({params:{start:0, limit:20}});
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
          title: 'Inventory SKU List',
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
              pageSize: 20,
              store: inventory_store,
              displayInfo: true
          })
     })
     
     inventory_grid.on("rowdblclick", function(oGrid){
          var oRecord = oGrid.getSelectionModel().getSelected();
          /*
          var form = {
               xtype:"form",
               labelAlign:"top",
               items:[{
                   layout:"column",
                   items:[{
                       columnWidth:0.7,
                       layout:"form",
                       items:[{
                           xtype:"panel",
                           title:"Title and Category",
                           layout:"form",
                           items:[{
                               xtype:"textfield",
                               fieldLabel:"Title",
                               name:"textvalue"
                             },{
                               xtype:"textfield",
                               fieldLabel:"Subtitle",
                               name:"textvalue"
                             },{
                               xtype:"combo",
                               fieldLabel:"Category",
                               name:"combovalue",
                               hiddenName:"combovalue"
                             },{
                               xtype:"combo",
                               fieldLabel:"2nd Category",
                               name:"combovalue",
                               hiddenName:"combovalue"
                             },{
                               xtype:"combo",
                               fieldLabel:"Store Category",
                               name:"combovalue",
                               hiddenName:"combovalue"
                             },{
                               xtype:"combo",
                               fieldLabel:"2nd Store Category",
                               name:"combovalue",
                               hiddenName:"combovalue"
                             }]
                         },{
                           xtype:"panel",
                           title:"Pictures and Description",
                           layout:"form",
                           items:[{
                               xtype:"panel",
                               title:"Pictures"
                             },{
                               xtype:"textfield",
                               fieldLabel:"Descritpion",
                               name:"textvalue"
                             }]
                         },{
                           xtype:"panel",
                           title:"Inventory Information",
                           layout:"form",
                           items:[{
                               xtype:"textfield",
                               fieldLabel:"SKU",
                               name:"textvalue"
                             }]
                         }]
                     },{
                       columnWidth:0.3,
                       layout:"form",
                       items:[{
                           xtype:"panel",
                           title:"Selling Format"
                         },{
                           xtype:"panel",
                           title:"Shipping Options"
                         },{
                           xtype:"panel",
                           title:"Payment Method",
                           layout:"form",
                           items:[{
                               xtype:"fieldset",
                               title:"PayPal",
                               autoHeight:true,
                               items:[{
                                   xtype:"checkbox",
                                   fieldLabel:"Label",
                                   boxLabel:"Credit crads via PayPal",
                                   name:"checkbox",
                                   inputValue:"cbvalue"
                                 },{
                                   xtype:"textfield",
                                   fieldLabel:"PayPal Account Email",
                                   name:"textvalue"
                                 }]
                             },{
                               xtype:"checkbox",
                               fieldLabel:"Label",
                               boxLabel:"Box label",
                               name:"checkbox",
                               inputValue:"cbvalue"
                             },{
                               xtype:"checkbox",
                               fieldLabel:"Label",
                               boxLabel:"Box label",
                               name:"checkbox",
                               inputValue:"cbvalue"
                             },{
                               xtype:"checkbox",
                               fieldLabel:"Label",
                               boxLabel:"Box label",
                               name:"checkbox",
                               inputValue:"cbvalue"
                             },{
                               xtype:"checkbox",
                               fieldLabel:"Label",
                               boxLabel:"Box label",
                               name:"checkbox",
                               inputValue:"cbvalue"
                             }]
                         }]
                     }]
                 }]
          }
          
          var add_task_window = new Ext.Window({
               title: 'XXX' ,
               closable:true,
               width: 500,
               height: 610,
               plain:true,
               layout: 'fit',
               items: form
          });
          
          add_task_window.show();
          */
          
          window.open("/eBayBO/eBaylisting/create_new_item.php?id="+oRecord.data['id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800");
          console.log(oRecord);
     })
     
     var inventoryTree = new Ext.tree.TreePanel({
          useArrows:true,
          autoScroll:true,
          animate:true,
          height: 500,
          // auto create TreeLoader
          dataUrl: inventory_service_address+'?action=getCategoriesTree',
          root: {
              nodeType: 'async',
              text: 'SKU Categories',
              draggable:false,
              expanded: true,
              id: '0'
          },
          listeners:{
               click: function(n, e){
                    //console.log(n);
                    inventory_store.baseParams = {
                         category_id: n.id
                    };
                    inventory_store.load({params:{start:0, limit:20}});
               }
               
          }
     });
     
     var tabPanel = new Ext.TabPanel({
                         region:'center',
                         deferredRender:false,
                         activeTab:0,
                         items:[{
                              id:'inventory-tab',
                              title: 'Inventory',
                              iconCls: 'inventory',
                              items: [inventory_search_form, inventory_grid],
                              closable: true,
                              autoScroll:true
                         }]
     })
     
     var waitOpen = false;
     var activityOpen = false;
     
     var viewport = new Ext.Viewport({
          layout:'border',
          items:[
               new Ext.BoxComponent({ // raw
                   region:'north',
                   el: 'north',
                   height:32
               }),/*{
                   region:'south',
                   contentEl: 'south',
                   split:true,
                   height: 100,
                   minSize: 100,
                   maxSize: 200,
                   collapsible: true,
                   title:'South',
                   margins:'0 0 0 0'
               },*/{
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
                         title:'Inventory',
                         border:false,
                         items: inventoryTree,
                         iconCls:'inventory',
                         listeners:{
                              expand: function(p){
                                   tabPanel.activate('inventory-tab');
                              }
                         }
                   },{
                       title:'Waiting To Upload',
                       html:'xxx',
                       border:false,
                       iconCls:'waiting-to-upload',
                       listeners:{
                         expand: function(p){
                              var wait_store =new Ext.data.JsonStore({
                                   root: 'records',
                                   totalProperty: 'totalCount',
                                   idProperty: 'id',
                                   //autoLoad:true,
                                   fields: ['inventory_model_code', 'short_description', 'long_description', 'category', 'manufacturer', 'Weight', 'Cost'],
                                   //url: 'service.php?action=getWait'
                                   url: inventory_service_address + '?action=getAllSkus'
                              })
                              
                              var wait_grid = new Ext.grid.GridPanel({
                                   title: 'Waiting To Upload SKU List',
                                   store: wait_store,
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
                                       pageSize: 20,
                                       store: wait_store,
                                       displayInfo: true
                                   })
                              })
                              
                              if(waitOpen == true){
                                   tabPanel.activate('waiting-to-upload-tab');
                              }else{
                                   wait_store.load();
                                   tabPanel.add({
                                        id:'waiting-to-upload-tab',
                                        iconCls: 'waiting-to-upload',
                                        title: "Waiting To Upload",
                                        items: wait_grid,
                                        autoScroll:true
                                   })
                                   tabPanel.doLayout();
                                   tabPanel.activate('waiting-to-upload-tab');
                              }
                              
                              /*
                              //if(tabPanel.isVisible('waiting-to-upload-tab'))
                                   //tabPanel.remove('waiting-to-upload-tab');
                              
                              console.log(tabPanel.find('waiting-to-upload-tab'));
                              
                              if(tabPanel.find('waiting-to-upload-tab')){
                                   tabPanel.activate('waiting-to-upload-tab');
                              }
                              */
                              
                              
                              waitOpen = true;
                         }
                       }
                   },{
                       title:'Listing Activity',
                       html:'xxx',
                       border:false,
                       iconCls:'listing-activity',
                       listeners:{
                              expand: function(p){
                                   var activity_store =new Ext.data.JsonStore({
                                        root: 'records',
                                        totalProperty: 'totalCount',
                                        idProperty: 'id',
                                        //autoLoad:true,
                                        fields: ['inventory_model_code', 'short_description', 'long_description', 'category', 'manufacturer', 'Weight', 'Cost'],
                                        //url: 'service.php?action=getWait'
                                        url: inventory_service_address + '?action=getAllSkus'
                                   })
                                   
                                   var activity_grid = new Ext.grid.GridPanel({
                                        title: 'Waiting To Upload SKU List',
                                        store: activity_store,
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
                                            pageSize: 20,
                                            store: activity_store,
                                            displayInfo: true
                                        })
                                   })
                                   
                                   if(activityOpen == true){
                                        tabPanel.activate('activity-tab');
                                   }else{
                                        activity_store.load();
                                        tabPanel.add({
                                             id:'activity-tab',
                                             iconCls: 'listing-activity',
                                             title: "Waiting To Upload",
                                             items: activity_grid,
                                             autoScroll:true
                                        })
                                        tabPanel.doLayout();
                                        tabPanel.activate('activity-tab');
                                   }
                                   
                                   /*
                                   //if(tabPanel.isVisible('waiting-to-upload-tab'))
                                        //tabPanel.remove('waiting-to-upload-tab');
                                   
                                   console.log(tabPanel.find('waiting-to-upload-tab'));
                                   
                                   if(tabPanel.find('waiting-to-upload-tab')){
                                        tabPanel.activate('waiting-to-upload-tab');
                                   }
                                   */
                                   
                                   
                                   activityOpen = true;
                              }
                         }
                   }
                   ]
               },tabPanel
            ]
       });
       
});