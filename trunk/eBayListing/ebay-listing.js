Ext.onReady(function(){
     Ext.BLANK_IMAGE_URL = "../../ext-3.0.0/resources/images/default/s.gif";
     
     var inventory_service_address = "/inventory/service.php";
     Ext.QuickTips.init();
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
          url: inventory_service_address + '?action=getAllSkus',
          listeners: {
               load: function(t, r){
                    //console.log(t.totalLength);
                    Ext.getCmp('inventory-accordion').setTitle('Inventory ('+t.totalLength+')');
               }
          }
     })
     
     
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
          //console.log(oRecord);
          window.open("/eBayBO/eBayListing/sku.php?id="+oRecord.data['inventory_model_code'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
     })
     
     var inventory_categories_tree = new Ext.tree.TreePanel({
          useArrows:true,
          autoScroll:true,
          animate:true,
          height: 500,
          // auto create TreeLoader
          dataUrl: inventory_service_address+'?action=getCategoriesTree',
          root: {
               id: '0',
               nodeType: 'async',
               text: 'All Categories',
               draggable:false,
               expanded: true
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
     })
     
     var template_search_form = new Ext.FormPanel({
          title: 'Search',
          items:[{
               layout:"column",
               items:[{
                    columnWidth:0.4,
                    layout:"form",
                    items:[{
                         xtype:"textfield",
                         width: 200,
                         fieldLabel:"Sku",
                         name:"SKU"
                    }]
               },{
                    columnWidth:0.6,
                    layout:"form",
                    items:[{
                         xtype:"textfield",
                         width: 400,
                         fieldLabel:"Title",
                         name:"Title"
                    }]
               }]
          }],
          buttons: [{
                    text: 'Submit',
                    handler: function(){
                         template_store.baseParams = {
                              SKU: template_search_form.getForm().findField("SKU").getValue(),
                              Title: template_search_form.getForm().findField("Title").getValue()
                         };
                         template_store.load({params:{start:0, limit:20}});
                    }
          }]
     })
          
     var template_store = new Ext.data.JsonStore({
          root: 'records',
          totalProperty: 'totalCount',
          idProperty: 'id',
          //autoLoad:true,
          fields: ['Id', 'SKU', 'Title'],
          url: 'service.php?action=getAllTemplate',
          listeners: {
               load: function(t, r){
                    //console.log(t.totalLength);
                    Ext.getCmp('template-accordion').setTitle('Template ('+t.totalLength+')');
               }
          }
     })
     
     var template_grid = new Ext.grid.GridPanel({
          title: 'Template List',
          store: template_store,
          autoHeight: true,
          selModel: new Ext.grid.RowSelectionModel({}),
          columns:[
              {header: "Sku", width: 200, align: 'center', sortable: true, dataIndex: 'SKU'},
              {header: "Title", width: 600, align: 'center', sortable: true, dataIndex: 'Title'}
          ],
          tbar:[{
               text: 'Add to upload',
               icon: './images/arrow_up.png',
               tooltip:'add selected template to upload queue',
               handler: function(){
                    var selections = template_grid.selModel.getSelections();
                    var ids = "";
                    for(var i = 0; i< template_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    //console.log(ids);
                    
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=templateAddToUpload', 
                         params: { 
                                ids: ids
                         }, 
                         success: function(response){
                             var result=eval(response.responseText);
                             switch(result){
                                case 1:  // Success : simply reload
                                  template_store.reload();
                                  break;
                                default:
                                  Ext.MessageBox.alert('Warning','Could not delete the entire selection.');
                                  break;
                             }
                         },
                         failure: function(response){
                             var result=response.responseText;
                             Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                         }
                    });
               }
          },'-',{
               text: 'Delete',
               icon: './images/cancel.png',
               tooltip:'Delete selected template',
               handler: function(){
                    var selections = template_grid.selModel.getSelections();
                    var ids = "";
                    for(var i = 0; i< template_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    //console.log(ids);
                    
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=templateDelete', 
                         params: { 
                                ids: ids
                         }, 
                         success: function(response){
                             var result=eval(response.responseText);
                             switch(result){
                                case 1:  // Success : simply reload
                                  template_store.reload();
                                  break;
                                default:
                                  Ext.MessageBox.alert('Warning','Could not delete the entire selection.');
                                  break;
                             }
                         },
                         failure: function(response){
                             var result=response.responseText;
                             Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                         }
                    });
               }
          },'-',{
               xtype: 'form',
               items: [{
                    id:'11',
                    xtype:'datefield',
                    width: 100
               },{
                    id:'22',
                    xtype: 'timefield',
                    increment:1,
                    triggerAction: 'all',
                    editable: false,
                    selectOnFocus:true,
                    width:100,
                    listWidth: 100
               }] 
          }],
          bbar: new Ext.PagingToolbar({
              pageSize: 20,
              store: template_store,
              displayInfo: true
          })
     })
     
     template_grid.on("rowdblclick", function(oGrid){
          var oRecord = oGrid.getSelectionModel().getSelected();
          //console.log(oRecord);
          window.open("/eBayBO/eBayListing/template.php?id="+oRecord.data['Id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
     })
                                   
     var template_category_tree = new Ext.tree.TreePanel({
          useArrows:true,
          autoScroll:true,
          animate:true,
          height: 500,
          // auto create TreeLoader
          dataUrl: 'service.php?action=getTemplateTree',
          root: {
               id: '0',
               nodeType: 'async',
               text: 'All Templates',
               draggable:false,
               expanded: true
          },
          listeners:{
               click: function(n, e){
                    //console.log(n);
                    template_store.baseParams = {
                         parent_id: n.id
                    };
                    template_store.load({params:{start:0, limit:20}});
               }
               
          }
     })
     
     var template_category_form = new Ext.FormPanel({
          title: 'Template Categories Manage',
          border: false,
          items:[{
               xtype:"textfield",
               //width: 400,
               name:"name",
               hideLabel:true
          }],
          buttons: [{
                    text: 'Add',
                    handler: function(){
                        
                    }
          },{
               text: 'Delete',
               handler: function(){
                    
               }
          }]
     })
     
     var listing_activity_tree = new Ext.tree.TreePanel({
          useArrows:true,
          autoScroll:true,
          animate:true,
          height: 500,
          root: {
               id: '0',
               text: 'All Listing',
               draggable:false,
               expanded: true,
               rootVisible: false,
               children:[ 
                    {"text" : "Scheduled Listings", "id" : 1, "leaf" : true},
                    {"text" : "Active Listings", "id" : 2, "leaf" : true},
                    {"text" : "Ended Listings", "id" : 3, "leaf" : true}
               ]
 
 
          },
          listeners:{
               click: function(n, e){
                    //console.log(n);
                    switch(n.id){
                         case 1:
                              
                         break;
                    
                         case 2:
                              var activity_store = new Ext.data.JsonStore({
                                   root: 'records',
                                   totalProperty: 'totalCount',
                                   idProperty: 'id',
                                   //autoLoad:true,
                                   fields: ['SKU', 'Title', 'Site', 'ListingType', 'Quantity', 'ListingDuration', 'StartPrice', 'BuyItNowPrice', 'Description', 'SubTitle', 'CategoryID', 'SecondaryCategory', 'StoreCategoryID', 'StoreCategory2ID'],
                                   //url: 'service.php?action=getWait'
                                   url: 'service.php?action=getActiveItem'
                              })
                              
                              var activity_grid = new Ext.grid.GridPanel({
                                   //title: 'Waiting To Upload SKU List',
                                   store: activity_store,
                                   //autoHeight: true,
                                   //autoScroll: true,
                                   //width: 600,
                                   //height: 500,
                                   selModel: new Ext.grid.RowSelectionModel({}),
                                   columns:[
                                       {header: "SKU", width: 120, align: 'center', sortable: true, dataIndex: 'SKU'},
                                       {header: "Item Title", width: 120, align: 'center', sortable: true, dataIndex: 'Title'},
                                       {header: "Site", width: 50, align: 'center', sortable: true, dataIndex: 'Site'},
                                       {header: "Format", width: 100, align: 'center', sortable: true, dataIndex: 'ListingType'},
                                       {header: "Qty", width: 50, align: 'center', sortable: true, dataIndex: 'Quantity'},
                                       {header: "Duration", width: 60, align: 'center', sortable: true, dataIndex: 'ListingDuration'},
                                       {header: "Start Price", width: 60, align: 'center', sortable: true, dataIndex: 'StartPrice'},
                                       {header: "Buy It Now Price", width: 120, align: 'center', sortable: true, dataIndex: 'BuyItNowPrice'},
                                       {header: "Sub Title", width: 120, align: 'center', sortable: true, dataIndex: 'SubTitle'},
                                       {header: "Category 1", width: 120, align: 'center', sortable: true, dataIndex: 'CategoryID'},
                                       {header: "Category 2", width: 120, align: 'center', sortable: true, dataIndex: 'SecondaryCategory'},
                                       {header: "Store Category 1", width: 120, align: 'center', sortable: true, dataIndex: 'StoreCategoryID'},
                                       {header: "Store Category 2", width: 120, align: 'center', sortable: true, dataIndex: 'StoreCategory2ID'}
                                   ],
                                   bbar: new Ext.PagingToolbar({
                                       pageSize: 20,
                                       store: activity_store,
                                       displayInfo: true
                                   })
                              })
                              
                              if(tabPanel.isVisible('activity-tab'))
                                   tabPanel.remove('activity-tab');

                              activity_store.load();
                              tabPanel.add({
                                   id:'activity-tab',
                                   iconCls: 'listing-activity',
                                   title: "Listing Activity",
                                   items: activity_grid,
                                   closable: true,
                                   autoScroll:true
                              })
                              
                              tabPanel.activate('activity-tab');
                              tabPanel.doLayout();
                         break;
                    
                         case 3:
                              
                         break;
                    }
               }
               
          }
     });
     
     
     var tabPanel = new Ext.TabPanel({
          region:'center',
          deferredRender:false,
          activeTab:0,
          autoScroll: true,
          items:[{
               id:'inventory-tab',
               title: 'Inventory',
               iconCls: 'inventory',
               items: [inventory_search_form, inventory_grid],
               //closable: true,
               autoScroll:true
          }]
     })
     
     
     var viewport = new Ext.Viewport({
          layout:'border',
          items:[
               //new Ext.BoxComponent({ // raw
               {
                   region:'north',
                   xtype:"panel",
                   items:[{
                         xtype:"button",
                         text:"Ebay Account Manage",
                         style:"float:left;margin-right:5px",
                         iconCls:'user'
                    },{
                         xtype:"button",
                         text:"Ebay Account Manage",
                         style:"float:left;margin-right:5px"
                    },{
                         xtype:"button",
                         text:"Ebay Account Manage",
                         style:"float:left;margin-right:5px"
                    }],
                   //el: 'north',
                   height:32
               },{
                    region:'south',
                    //contentEl: 'south',
                    split:true,
                    height: 100,
                    autoScroll: true,
                    minSize: 100,
                    maxSize: 150,
                    collapsible: true,
                    title:'Log',
                    html:'test',
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
                         id: 'inventory-accordion',
                         title:'Inventory',
                         border:false,
                         items: inventory_categories_tree,
                         iconCls:'inventory',
                         listeners:{
                              expand: function(p){
                                   tabPanel.activate('inventory-tab');
                              }
                         }
                   },{
                         id:'template-accordion',
                         title:'Template',
                         border:false,
                         items:[template_category_form, template_category_tree],
                         iconCls:'template',
                         listeners:{
                              expand: function(p){
                                   //console.log(tabPanel.isVisible('template-tab'));
                                   template_category_tree.root.reload()
                                   if(Ext.getCmp("template-tab")){
                                        //console.log("test1");
                                        tabPanel.activate('template-tab');
                                   }else{
                                        //console.log("test2");
                                        template_store.load();
                                        tabPanel.add({
                                             id:'template-tab',
                                             iconCls: 'template',
                                             title: "Template",
                                             items: [template_search_form, template_grid],
                                             //closable: true,
                                             autoScroll:true
                                        })
                                        tabPanel.doLayout();
                                        tabPanel.activate('template-tab');
                                   }
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
                              
                              if(tabPanel.isVisible('waiting-to-upload-tab'))
                                   tabPanel.remove('waiting-to-upload-tab');
                                   
                              //if(waitOpen == true){
                                   //tabPanel.activate('waiting-to-upload-tab');
                              //}else{
                                   wait_store.load();
                                   tabPanel.add({
                                        id:'waiting-to-upload-tab',
                                        iconCls: 'waiting-to-upload',
                                        title: "Waiting To Upload",
                                        items: wait_grid,
                                        closable: true,
                                        autoScroll:true
                                   })
                                   tabPanel.doLayout();
                                   tabPanel.activate('waiting-to-upload-tab');
                              //}
                              
                              /*
                              //if(tabPanel.isVisible('waiting-to-upload-tab'))
                                   //tabPanel.remove('waiting-to-upload-tab');
                              
                              console.log(tabPanel.find('waiting-to-upload-tab'));
                              
                              if(tabPanel.find('waiting-to-upload-tab')){
                                   tabPanel.activate('waiting-to-upload-tab');
                              }
                              */
                              
                         }
                       }
                   },{
                         title:'Listing Activity',
                         items:listing_activity_tree,
                         border:false,
                         iconCls:'listing-activity',
                         listeners:{
                              expand: function(p){
                                   
                                   
                              }
                         }
                   },{
                         title:'Sales Report',
                         border:false,
                         iconCls:'sales-report',
                         listeners:{
                              expand: function(p){
                                   var sales_report_store = new Ext.data.JsonStore({
                                        root: 'records',
                                        totalProperty: 'totalCount',
                                        idProperty: 'id',
                                        //autoLoad:true,
                                        fields: ['inventory_model_code', 'short_description', 'long_description', 'category', 'manufacturer', 'Weight', 'Cost'],
                                        //url: 'service.php?action=getWait'
                                        url: inventory_service_address + '?action=getSalesReport'
                                   })
                                   
                                   var sales_report_grid = new Ext.grid.GridPanel({
                                        title: 'Sales Report',
                                        store: sales_report_store,
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
                                            store: sales_report_store,
                                            displayInfo: true
                                        })
                                   })
                                   
                                   if(tabPanel.isVisible('sales-report-tab'))
                                        tabPanel.remove('sales-report-tab');

                                   sales_report_store.load();
                                   tabPanel.add({
                                        id:'sales-report-tab',
                                        iconCls: 'sales-report',
                                        title: "Sales Report",
                                        items: sales_report_grid,
                                        closable: true,
                                        autoScroll:true
                                   })
                                   tabPanel.doLayout();
                                   tabPanel.activate('sales-report-tab');
                              }
                         }
                   }]
               },tabPanel
            ]
       });
       
});