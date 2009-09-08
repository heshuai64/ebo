Ext.onReady(function(){
     Ext.BLANK_IMAGE_URL = "../../ext-3.0.0/resources/images/default/s.gif";
     
     var inventory_service_address = "/inventory/service.php";
     Ext.QuickTips.init();
     
     /*
     var cp = new Ext.state.CookieProvider({
          path: "/eBayBO/eBayListing/"
     });
     Ext.state.Manager.setProvider(cp);
     */
     Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
     
     var getCookie = function(c_name){
          if (document.cookie.length>0){
               c_start=document.cookie.indexOf(c_name + "=");
               if (c_start!=-1){
                    c_start=c_start + c_name.length+1;
                    c_end=document.cookie.indexOf(";",c_start);
                    if (c_end==-1) c_end=document.cookie.length;
                    return unescape(document.cookie.substring(c_start,c_end));
               }
          }
          return "";
     }

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
          fields: ['Id', 'SKU', 'Title', 'Price', 'Quantity', 'ListingDuration', 'ListingType'],
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
               {header: "Sku", width: 80, align: 'center', sortable: true, dataIndex: 'SKU'},
               {header: "Title", width: 300, align: 'center', sortable: true, dataIndex: 'Title'},
               {header: "Price", width: 60, align: 'center', sortable: true, dataIndex: 'Price'},
               {header: "Qty", width: 30, align: 'center', sortable: true, dataIndex: 'Quantity'},
               {header: "Duration", width: 100, align: 'center', sortable: true, dataIndex: 'ListingDuration'},
               {header: "ListingType", width: 100, align: 'center', sortable: true, dataIndex: 'ListingType'}
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
               text: 'Import Csv',
               icon: './images/folder_database.png',
               tooltip:'Import csv file, include sku and tiitle',
               handler: function(){
                    var  importCsvWindow = new Ext.Window({
                         title: 'Import CSV File' ,
                         closable:true,
                         width: 320,
                         height: 150,
                         plain:true,
                         layout: 'fit',
                         items: [{
                              xtype:'form',
                              id:'csv-form',
                              fileUpload: true,
                              frame: true,
                              autoHeight: true,
                              bodyStyle: 'padding: 10px 10px 0 10px;',
                              labelWidth: 50,
                              defaults: {
                                  anchor: '95%',
                                  allowBlank: false
                              },
                              items:[{
                                   xtype: 'fileuploadfield',
                                   id: 'csv',
                                   emptyText: 'Select an csv file',
                                   fieldLabel: 'CSV',
                                   //hideLabel:true,
                                   name: 'csv',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              }]
                         }],                                           
                         buttons: [{
                                        text: 'OK',
                                        handler: function(){
                                             fp = Ext.getCmp("csv-form");
                                             if(fp.getForm().isValid()){
                                                  fp.getForm().submit({
                                                       url: 'service.php?action=templateImportCsv',
                                                       waitMsg: 'Uploading your csv...',
                                                       success: function(fp, o){
                                                           console.log(o);
                                                       }
                                                  });
                                             }
                                        }
                                 },{
                                        text: 'Cancel',
                                        handler: function(){
                                             importCsvWindow.close();
                                        }
                                 }]
                                   
                    })
                    importCsvWindow.show();   
               }
          },'-',/*{
               xtype:"datefield",
               id:"11",
               name:"11",
               format:'Y-m-d'
          },'-',{
               xtype:"tbtext",
               id:"22",
               name:"22"
          }*/{
               xtype:'form',
               width: 400,
               labelWidth: 50,
               layout: 'column',
               items: [{
                    columnWidth:0.4,
                    layout:"form",
                    border:false,
                    items:[{
                         id:'interval-date',
                         fieldLabel:'Start Time',
                         xtype:'datefield',
                         format:'Y-m-d'
                    }]
               },{
                    columnWidth:0.25,
                    layout:"form",
                    border:false,
                    items:[{
                         id:'interval-time',
                         hideLabel:true,
                         xtype:'timefield',
                         increment:1,
                         triggerAction: 'all',
                         editable: false,
                         selectOnFocus:true,
                         listWidth:80,
                         width:80
                    }]
               },{
                    columnWidth:0.35,
                    layout:"form",
                    border:false,
                    labelWidth: 40,
                    items:[{
                         //hideLabel:true,
                         id:'interval-minute',
                         fieldLabel:'Interval',
                         xtype:"combo",
                         store:[1,2,3,4,5,6,7,8,9,10],
                         listWidth:60,
                         width:60
                    }]
               }]
          
          },{
               text:'submit',
               handler: function(){
                    var selections = template_grid.selModel.getSelections();
                    var ids = "";
                    for(var i = 0; i< template_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=templateIntervalUpload', 
                         params: {
                              ids: ids,
                              date: Ext.getCmp('interval-date').getValue(),
                              time: Ext.getCmp('interval-time').getValue(),
                              minute: Ext.getCmp('interval-minute').getValue()
                         }, 
                         success: function(response){
                             var result=eval(response.responseText);
                             switch(result){
                                case 1:  // Success : simply reload
                                  
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
          collapsible: true,
          collapsed: true,
          items:[{
               id:"template_category_name",
               xtype:"textfield",
               //width: 400,
               name:"name",
               hideLabel:true
          }],
          buttons: [{
                    text: 'Add',
                    handler: function(){
                         Ext.Ajax.request({  
                              waitMsg: 'Please Wait',
                              url: 'service.php?action=addTemplateCateogry', 
                              params: { 
                                   templateCateogryParentId: template_category_tree.getSelectionModel().getSelectedNode().id ,
                                   templateCategoryName: Ext.getCmp("template_category_name").getValue()
                              }, 
                              success: function(response){
                                  var result=eval(response.responseText);
                                  switch(result){
                                     case 1:  // Success : simply reload
                                       template_category_tree.root.reload();
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
          },{
               text: 'Delete',
               handler: function(){
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=deleteTemplateCateogry', 
                         params: { 
                              templateCateogryId: template_category_tree.getSelectionModel().getSelectedNode().id
                         }, 
                         success: function(response){
                             var result=eval(response.responseText);
                             switch(result){
                                case 1:  // Success : simply reload
                                  template_category_tree.root.reload();
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
                    {"text" : "Active Listings", "id" : 1, "leaf" : true},
                    {"text" : "Ended Listings",  "id" : 2, "leaf" : true}
               ]
          },
          listeners:{
               click: function(n, e){
                    //console.log(n);
                    switch(n.id){
                         case 1:
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
                    
                         case 2:
                              
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
                    collapsed: true,
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
                                   template_category_tree.root.reload();
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
                         id:'waiting-to-upload',
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
                                   fields: ['Id', 'SKU', 'Title', 'Price', 'Quantity', 'ListingDuration', 'ListingType', 'ScheduleTime'],
                                   url: 'service.php?action=getWaitingUploadItem',
                                   listeners: {
                                        load: function(t, r){
                                             Ext.getCmp('waiting-to-upload').setTitle('Waiting To Upload ('+t.totalLength+')');
                                        }
                                   }
                              })
                              
                              var wait_grid = new Ext.grid.GridPanel({
                                   title: 'Waiting To Upload SKU List',
                                   store: wait_store,
                                   autoHeight: true,
                                   selModel: new Ext.grid.RowSelectionModel({}),
                                   columns:[
                                        {header: "Sku", width: 80, align: 'center', sortable: true, dataIndex: 'SKU'},
                                        {header: "Title", width: 300, align: 'center', sortable: true, dataIndex: 'Title'},
                                        {header: "Price", width: 60, align: 'center', sortable: true, dataIndex: 'Price'},
                                        {header: "Qty", width: 30, align: 'center', sortable: true, dataIndex: 'Quantity'},
                                        {header: "Duration", width: 100, align: 'center', sortable: true, dataIndex: 'ListingDuration'},
                                        {header: "ListingType", width: 100, align: 'center', sortable: true, dataIndex: 'ListingType'},
                                        {header: "UploadTime", width: 250, align: 'center', sortable: true, dataIndex: 'ScheduleTime'}
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
                         title:'Manage',
                         hidden:(getCookie("role")=='admin')?false:true,
                         items:{
                              xtype: 'buttongroup',
                              columns: 1,
                              items: [{
                                   text: 'eBay Account Manage',
                                   iconCls: 'user',
                                   handler: function(){
                                        var store = new Ext.data.JsonStore({
                                             root: 'result',
                                             autoLoad: true,
                                             fields: ['id', 'name', 'password', 'token', 'tokenExpiry', 'status'],
                                             url:'service.php?action=getAlleBayAccount'
                                        });
                                         
                                        var ebayManageForm = new Ext.FormPanel({
                                                 id: 'ebay-manage-form',
                                                 frame: true,
                                                 labelAlign: 'left',
                                                 bodyStyle:'padding:5px',
                                                 labelWidth:75,
                                                 //width: 750,
                                                 layout:"column",
                                                 items:[{
                                                     columnWidth: 0.3,
                                                     layout: 'fit',
                                                     items: {
                                                            id:'ebay-manage-grid',
                                                            xtype: 'grid',
                                                            store: store,
                                                            columns:[
                                                                    {id:'name', header: "Name", width: 200, sortable: true, dataIndex: 'name'}
                                                                ],
                                                            sm: new Ext.grid.RowSelectionModel({
                                                                 singleSelect: true,
                                                                 listeners: {
                                                                      rowselect: function(sm, row, rec) {
                                                                          Ext.getCmp("ebay-manage-form").getForm().loadRecord(rec);
                                                                      }
                                                                 }
                                                            }),
                                                            height: 350,
                                                            title:'eBay Account List',
                                                            border: true,
                                                            listeners: {
                                                                      render: function(g) {
                                                                            g.getSelectionModel().selectRow(0);
                                                                      },
                                                                      delay: 10 // Allow rows to be rendered.
                                                            }
                                                         }
                                                 },{
                                                     columnWidth:0.7,
                                                     layout:"form",
                                                     items:[{
                                                         layout:"column",
                                                         items:[{
                                                             columnWidth:0.5,
                                                             layout:"form",
                                                             items:[{
                                                                 xtype:"hidden",
                                                                 name:"id"
                                                               },{
                                                                 xtype:"textfield",
                                                                 fieldLabel:"Name",
                                                                 name:"name"
                                                               },{
                                                                 xtype:"textfield",
                                                                 fieldLabel:"Password",
                                                                 name:"password"
                                                               }]
                                                           },{
                                                             columnWidth:0.5,
                                                             layout:"form",
                                                             items:[{
                                                                 xtype:"combo",
                                                                 fieldLabel:"Status",
                                                                 name:"status",
                                                                 width:80,
                                                                 hiddenName:"status"
                                                               },{
                                                                 xtype:"textfield",
                                                                 fieldLabel:"Token Expiry",
                                                                 name:"tokenExpiry"
                                                               }]
                                                           }]
                                                       },{
                                                         xtype:"textarea",
                                                         fieldLabel:"Token",
                                                         height:200,
                                                         width:350,
                                                         name:"token"
                                                     }]
                                             }],
                                                 buttons: [{
                                                     text: 'Save Selected eBay Account',
                                                     handler: function(){
                                                         Ext.Ajax.request({
                                                             waitMsg: 'Please wait...',
                                                             url: 'service.php?action=updateeBayAccount',
                                                             params: {
                                                                 id: ebayManageForm.form.findField('id').getValue(),
                                                                 name: ebayManageForm.form.findField('name').getValue(),
                                                                 password: ebayManageForm.form.findField('password').getValue(),
                                                                 token: ebayManageForm.form.findField('token').getValue(),
                                                                 status: ebayManageForm.form.findField('status').getValue()
                                                             },
                                                             success: function(response){
                                                                     var result = eval(response.responseText);
                                                                     switch (result) {
                                                                             case 1:
                                                                                 store.reload();
                                                                                 break;
                                                                             default:
                                                                                 Ext.MessageBox.alert('Uh uh...', 'We couldn\'t save him...');
                                                                                 break;
                                                                     }
                                                             },
                                                             failure: function(response){
                                                                     var result = response.responseText;
                                                                     Ext.MessageBox.alert('error', 'could not connect to the database. retry later');
                                                             }
                                                         });		
                                                     }
                                                 },{
                                                     text: 'Add eBay Account',
                                                     handler: function(){
                                                         var addEbaySellerForm = new Ext.FormPanel({
                                                             frame: true,
                                                             labelAlign: 'left',
                                                             bodyStyle:'padding:5px',
                                                             labelWidth:80,
                                                             items:[{
                                                                 layout:"column",
                                                                 items:[{
                                                                     columnWidth:0.5,
                                                                     layout:"form",
                                                                     items:[{
                                                                         xtype:"textfield",
                                                                         fieldLabel:"Name",
                                                                         name:"name"
                                                                       },{
                                                                         xtype:"textfield",
                                                                         fieldLabel:"Password",
                                                                         name:"password"
                                                                       }]
                                                                   },{
                                                                     columnWidth:0.5,
                                                                     layout:"form",
                                                                     items:[{
                                                                         xtype:"combo",
                                                                         fieldLabel:"Status",
                                                                         name:"status",
                                                                         width:80,
                                                                         hiddenName:"status"
                                                                       },{
                                                                         xtype:"textfield",
                                                                         fieldLabel:"Token Expiry",
                                                                         name:"tokenExpiry"
                                                                       }]
                                                                   }]
                                                               },{
                                                                 xtype:"textarea",
                                                                 fieldLabel:"Token",
                                                                 height:200,
                                                                 width:350,
                                                                 name:"token"
                                                             }],
                                                             buttons: [{
                                                                 text: 'Save',
                                                                 handler: function(){
                                                                      Ext.Ajax.request({
                                                                         waitMsg: 'Please wait...',
                                                                         url: 'service.php?action=addeBayAccount',
                                                                         params: {
                                                                                name: addEbaySellerForm.form.findField('name').getValue(),
                                                                                password: addEbaySellerForm.form.findField('password').getValue(),
                                                                                tokenExpiry: addEbaySellerForm.form.findField('tokenExpiry').getValue(),
                                                                                token: addEbaySellerForm.form.findField('token').getValue(),
                                                                                status: addEbaySellerForm.form.findField('status').getValue()
                                                                         },
                                                                         success: function(response){
                                                                             var result = eval(response.responseText);
                                                                             switch (result) {
                                                                                 case 1:
                                                                                     store.reload();
                                                                                     addeBayAccountWin.close();
                                                                                     break;
                                                                                 default:
                                                                                     Ext.MessageBox.alert('Uh uh...', 'We couldn\'t save him...');
                                                                                     break;
                                                                             }
                                                                         },
                                                                         failure: function(response){
                                                                             var result = response.responseText;
                                                                             Ext.MessageBox.alert('error', 'could not connect to the database. retry later');
                                                                         }
                                                                     });		
                                                                 }
                                                            },{
                                                                 text: 'Close',
                                                                 handler: function(){
                                                                     addeBayAccountWin.close();
                                                                 }
                                                             }]
                                                       });
                                                  
                                                       var addeBayAccountWin = new Ext.Window({
                                                            title: 'Add eBay Account' ,
                                                            closable:true,
                                                            width: 600,
                                                            height: 400,
                                                            plain:true,
                                                            layout: 'fit',
                                                            items: addEbaySellerForm
                                                       })
                                                       
                                                       addeBayAccountWin.show();
                                                     }
                                                 },{
                                                     text: 'Delete Selected eBay Account',
                                                     handler: function(){
                                                         //console.log(Ext.getCmp("ebay-manage-grid").getSelectionModel().getSelected());
                                                         Ext.Ajax.request({
                                                             waitMsg: 'Please wait...',
                                                             url: 'service.php?action=deleteeBayAccount',
                                                             params: {
                                                                 id: Ext.getCmp("ebay-manage-grid").getSelectionModel().getSelected().data.id
                                                             },
                                                             success: function(response){
                                                                 var result = eval(response.responseText);
                                                                 switch (result) {
                                                                     case 1:
                                                                         store.reload();
                                                                         break;
                                                                     default:
                                                                         Ext.MessageBox.alert('Uh uh...', 'We couldn\'t save him...');
                                                                         break;
                                                                 }
                                                             },
                                                             failure: function(response){
                                                                 var result = response.responseText;
                                                                 Ext.MessageBox.alert('error', 'could not connect to the database. retry later');
                                                             }
                                                         });		
                                                     }
                                                 },{
                                                     text: 'Close',
                                                     handler: function(){
                                                         ebayManageWin.close();
                                                     }
                                                 }]
                                        });
                                        
                                        var ebayManageWin = new Ext.Window({
                                             title: 'eBay Account Manage' ,
                                             closable:true,
                                             width: 720,
                                             height: 500,
                                             plain:true,
                                             layout: 'fit',
                                             items: ebayManageForm
                                        })
                                        
                                        ebayManageWin.show();
                                   }
                              },{
                                   text: 'eBay Proxy Manage',
                                   iconCls: 'proxy',
                                   handler: function(){
                                        
                                        var store = new Ext.data.JsonStore({
                                             root: 'result.proxy',
                                             autoLoad: true,
                                             fields: ['id','account_id','account_name','host','port'],
                                             url:'service.php?action=getAllEbayProxy'
                                        });

                                        var loadComplete = function(S, r){
                                             if(!ebayProxyManageWin){
                                                 var seller = S.reader.jsonData.result.seller;
                                                 
                                                 var gridForm = new Ext.FormPanel({
                                                     id: 'ebay-proxy-manage-form',
                                                     frame: true,
                                                     labelAlign: 'left',
                                                     //title: '用户管理',
                                                     bodyStyle:'padding:5px',
                                                     //width: 750,
                                                     layout: 'column',	
                                                     items: [{
                                                         columnWidth: 0.55,
                                                         layout: 'fit',
                                                         items: {
                                                                 xtype: 'grid',
                                                                 store: store,
                                                                 columns:[
                                                                         {header: "id", width: 0, sortable: true,  dataIndex: 'id', hidden:true},
                                                                         {header: "eBay id", width: 0, sortable: true,  dataIndex: 'account_id', hidden:true},
                                                                         {header: "eBay account", width: 135, sortable: true,  dataIndex: 'account_name'},
                                                                         {header: "proxy host", width: 90, sortable: true, dataIndex: 'host'},
                                                                         {header: "proxy port", width: 80, sortable: true, dataIndex: 'port'}
                                                                     ],
                                                                 sm: new Ext.grid.RowSelectionModel({
                                                                     singleSelect: true,
                                                                     listeners: {
                                                                         rowselect: function(sm, row, rec) {
                                                                             Ext.getCmp("ebay-proxy-manage-form").getForm().loadRecord(rec);
                                                                         }
                                                                     }
                                                                 }),
                                                                 height: 350,
                                                                 border: true,
                                                                 listeners: {
                                                                         render: function(g) {
                                                                                 g.getSelectionModel().selectRow(0);
                                                                         },
                                                                         delay: 10 // Allow rows to be rendered.
                                                                 }
                                                             }
                                                     },{
                                                         columnWidth: 0.45,
                                                         xtype: 'fieldset',
                                                         labelWidth: 55,
                                                         //title:'用户信息',
                                                         defaults: {width: 150},	// Default config options for child items
                                                         defaultType: 'textfield',
                                                         autoHeight: true,
                                                         bodyStyle: Ext.isIE ? 'padding:0 0 5px 15px;' : 'padding:10px 15px;',
                                                         border: false,
                                                         style: {
                                                             "margin-left": "10px", // when you add custom margin in IE 6...
                                                             "margin-right": Ext.isIE6 ? (Ext.isStrict ? "-10px" : "-13px") : "0"  // you have to adjust for it somewhere else
                                                         },
                                                         items: [{
                                                             xtype: 'hidden',
                                                             name:'id'
                                                             },{
                                                             xtype: 'combo',
                                                             fieldLabel: 'account',
                                                             mode: 'local',
                                                             store: new Ext.data.JsonStore({
                                                                 fields: ['account_id', 'account_name'],
                                                                 data : seller
                                                             }),
                                                             valueField:'account_id',
                                                             displayField:'account_name',
                                                             triggerAction: 'all',
                                                             editable: false,
                                                             selectOnFocus:true,
                                                             name: 'account_id',
                                                             hiddenName:'account_id'
                                                         },{
                                                             fieldLabel: 'host',
                                                             name: 'host'
                                                         },{
                                                             fieldLabel: 'port',
                                                             name: 'port'
                                                         }]
                                                     }],
                                                     buttons: [{
                                                         text: 'Add eBay Proxy',
                                                         handler: function(){
                                                             var add_ebay_proxy_form =  form = new Ext.FormPanel({
                                                                 labelAlign: 'top',
                                                                 bodyStyle:'padding:5px',
                                                                 defaultType: 'textfield',
                                                                 items: [{
                                                                         xtype: 'combo',
                                                                         fieldLabel: 'eBay account',
                                                                         mode: 'local',
                                                                         store: new Ext.data.JsonStore({
                                                                             fields: ['account_id', 'account_name'],
                                                                             data : seller
                                                                         }),
                                                                         valueField:'account_id',
                                                                         displayField:'account_name',
                                                                         triggerAction: 'all',
                                                                         editable: false,
                                                                         selectOnFocus:true,
                                                                         name: 'account_id',
                                                                         hiddenName:'account_id'
                                                                     },{
                                                                         fieldLabel: 'host',
                                                                         name: 'host'
                                                                     },{
                                                                         fieldLabel: 'port',
                                                                         name: 'port'
                                                                     }]
                                                             })
                                                            
                                                            var addeBayProxyWindow = new Ext.Window({
                                                                 title: 'Add eBay Proxy' ,
                                                                 closable:true,
                                                                 width: 400,
                                                                 height: 300,
                                                                 plain:true,
                                                                 layout: 'fit',
                                                                 items: add_ebay_proxy_form,buttons: [{
                                                                           text: 'Save',
                                                                           handler: function(){
                                                                               Ext.Ajax.request({
                                                                                   waitMsg: 'Please wait...',
                                                                                   url: 'service.php?action=addeBayProxy',
                                                                                   params: {
                                                                                          account_id: add_ebay_proxy_form.form.findField('account_id').getValue(),
                                                                                          host: add_ebay_proxy_form.form.findField('host').getValue(),
                                                                                          port: add_ebay_proxy_form.form.findField('port').getValue()
                                                                                   },
                                                                                   success: function(response){
                                                                                       var result = eval(response.responseText);
                                                                                       switch (result) {
                                                                                           case 1:
                                                                                               store.reload();
                                                                                               addeBayProxyWindow.close();
                                                                                               break;
                                                                                           default:
                                                                                               Ext.MessageBox.alert('Uh uh...', 'We couldn\'t save him...');
                                                                                               break;
                                                                                       }
                                                                                   },
                                                                                   failure: function(response){
                                                                                       var result = response.responseText;
                                                                                       Ext.MessageBox.alert('error', 'could not connect to the database. retry later');
                                                                                   }
                                                                               });		
                                                                           }
                                                                       },{
                                                                           text: 'Close',
                                                                           handler: function(){
                                                                                addeBayProxyWindow.close();
                                                                           }
                                                                 }]
                                                            })
                                                              
                                                            addeBayProxyWindow.show();
                                                            
                                                         }
                                                     },{
                                                         text: 'Save Selected eBay Proxy',
                                                         handler: function(){
                           
                                                             var form = Ext.getCmp("ebay-proxy-manage-form").getForm();
                                                             Ext.Ajax.request({
                                                                 url: "service.php?action=updateeBayProxy",
                                                                 params: {
                                                                     id: form.findField('id').getValue(),
                                                                     account_id: form.findField('account_id').getValue(),
                                                                     host: form.findField('host').getValue(),
                                                                     port: form.findField('port').getValue()
                                                                 },
                                                                 success: function(o){
                                                                     store.reload();
                                                                 },
                                                                 failure: function(){
                                                                     
                                                                 },
                                                                 scope: this
                                                             });
                                                         }
                                                     },{
                                                         text: 'Delete Selected eBay Proxy',
                                                         handler: function(){
                                                           
                                                            var form = Ext.getCmp("ebay-proxy-manage-form").getForm();
                                                            Ext.Ajax.request({
                                                                 url: "service.php?action=deleteeBayProxy",
                                                                 params: {
                                                                     id: form.findField('id').getValue()
                                                                 },
                                                                 success: function(o){
                                                                     store.reload();
                                                                 },
                                                                 failure: function(){
                                                                    
                                                                 },
                                                                 scope: this
                                                            });
                                                         }
                                                     },{
                                                         text: 'Close',
                                                         handler: function(){
                                                            ebayProxyManageWin.close();
                                                         }
                                                     }]
                                                  });
                                                 
                                                  var ebayProxyManageWin = new Ext.Window({
                                                       title: 'eBay Proxy Manage' ,
                                                       closable:true,
                                                       width:600,
                                                       height:500,
                                                       plain:true,
                                                       layout: 'fit',
                                                       items: gridForm
                                                  })
                                                 
                                                  ebayProxyManageWin.show();
                                             }else{
                                                  ebayProxyManageWin.show();
                                             }
                                        }
                                        store.on('load', loadComplete);
                                   }
                              }]                      
                         },
                         border:false,
                         iconCls:'manage'
                    },{
                         title:'Log',
                         items:{
                              xtype: 'buttongroup',
                              columns: 1,
                              items: [{
                                   text: 'System Upload Log',
                                   iconCls: 'upload-log',
                                   handler: function(){
                                        //console.log("test");
                                        var log_store = new Ext.data.JsonStore({
                                             root: 'records',
                                             totalProperty: 'totalCount',
                                             idProperty: 'id',
                                             //autoLoad:true,
                                             fields: ['id', 'level', 'content', 'time'],
                                             url: 'service.php?action=getUploadLog'
                                        })
                                        //console.log("test1");
                                        var log_grid = new Ext.grid.GridPanel({
                                             title: 'Upload Log',
                                             store: log_store,
                                             autoHeight: true,
                                             selModel: new Ext.grid.RowSelectionModel({}),
                                             columns:[
                                                  {header: "Level", width: 80, align: 'center', sortable: true, dataIndex: 'level'},
                                                  {header: "Content", width: 600, align: 'center', sortable: true, dataIndex: 'content'},
                                                  {header: "Time", width: 110, align: 'center', sortable: true, dataIndex: 'time'}
                                             ],
                                             bbar: new Ext.PagingToolbar({
                                                 pageSize: 20,
                                                 store: log_store,
                                                 displayInfo: true
                                             })
                                        })
                                        //console.log(tabPanel);
                                        if(tabPanel.isVisible('upload-log-tab'))
                                             tabPanel.remove('upload-log-tab');
                                             
     
                                        log_store.load();
                                        tabPanel.add({
                                             id:'upload-log-tab',
                                             iconCls: 'upload-log',
                                             title: "Log",
                                             items: log_grid,
                                             closable: true,
                                             autoScroll:true
                                        })
                                        tabPanel.doLayout();
                                        tabPanel.activate('upload-log-tab');
                                   }
                              },{
                                   text: 'Template Change Log',
                                   iconCls: 'template-log'
                              }]                      
                         },
                         border:false,
                         iconCls:'log',
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