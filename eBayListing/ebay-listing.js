Ext.onReady(function(){
     Ext.BLANK_IMAGE_URL = "../ext-3.0.0/resources/images/default/s.gif";
     
     var inventory_service_address = "/inventory/service.php";
     Ext.QuickTips.init();
     
     var path = "/eBayBO/eBayListing/";
     //var path = "/eBayListing/";
     
     /*
     var cp = new Ext.state.CookieProvider({
          path: "/eBayBO/eBayListing/"
     });
     Ext.state.Manager.setProvider(cp);
     */

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

     Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
     function renderFlag(v, p, r){
          return "<img src='./images/"+v.toLowerCase()+".gif'>";
     }
     
     function renderTemplateStatus(v, p, r){
          switch(v){
               case "1":
                    return "<img src='./images/lock.png'>";
               break;
          
               case "2":
                    return "<img src='./images/magnifier.png'>";
               break;
          
               case "3":
                    return "<img src='./images/cart_error.png'>";
               break;
          }
          return "";
     }
     
     var listingTemplateDurationStore =  new Ext.data.JsonStore({
        //root: 'records',
        //totalProperty: 'totalCount',
        //idProperty: 'id',
        fields: ['id', 'name'],
        url:'service.php?action=getTemplateDurationStore'
     })
     
     var templateCategoryStore =  new Ext.data.JsonStore({
          autoLoad: true,
          fields: ['id', 'name'],
          url: "service.php?action=getTemplateCategory"
     })
     
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
          buttonAlign: 'left',
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
                         inventory_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
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
              {header: "Model", width: 180, align: 'center', sortable: true, dataIndex: 'short_description'},
              {header: "Description", width: 250, align: 'center', sortable: true, dataIndex: 'long_description'},
              {header: "Categpru", width: 100, align: 'center', sortable: true, dataIndex: 'category'}
              //{header: "Supplier", width: 120, align: 'center', sortable: true, dataIndex: 'manufacturer'},
              //{header: "Weight", width: 60, align: 'center', sortable: true, dataIndex: 'Weight'},
              //{header: "Cost", width: 60, align: 'center', sortable: true, dataIndex: 'Cost'}
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
          window.open(path + "sku.php?id="+oRecord.data['inventory_model_code'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
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
                    inventory_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
               }
               
          }
     })
     
     var template_search_form = new Ext.FormPanel({
          width: 1024,
          title: 'Search',
          buttonAlign: 'left',
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
                         template_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                    }
          }]
     })
          
     var template_category_editor = new Ext.ux.grid.RowEditor({
          saveText: 'Update',
          listeners: {
               afteredit : function(a, b, c, d){
                    //console.log([a, b, c, d]);
                    //console.log(selections[0].data.Id);
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=updateFields', 
                         params: { 
                              id: c.data.Id,
                              table: 'template',
                              Title: b.Title,
                              Price: b.Price,
                              Quantity: b.Quantity,
                              ListingDuration : b.ListingDuration,
                              Category: b.Category
                         }, 
                         success: function(response){
                             var result = eval(response.responseText);
                             if(result[0].success){
                                   Ext.MessageBox.alert('Success', result[0].msg);
                                   template_store.reload();
                              }else{
                                   Ext.MessageBox.alert('Failure', result[0].msg);
                                   //template_store.reload();
                              }
                         },
                         failure: function(response){
                             var result=response.responseText;
                             Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                         }
                    });
               }
          }
     });
     
     var template_status_editor = new Ext.ux.grid.RowEditor({
          saveText: 'Update',
          listeners: {
               afteredit : function(a, b, c, d){
                    //console.log([a, b, c, d]);
                    //console.log(selections[0].data.Id);
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=updateFields', 
                         params: { 
                              id: c.data.Id,
                              table: 'template',
                              Title: b.Title,
                              Price: b.Price,
                              Quantity: b.Quantity,
                              ListingDuration : b.ListingDuration,
                              Category: b.Category
                         }, 
                         success: function(response){
                             var result = eval(response.responseText);
                             if(result[0].success){
                                   Ext.MessageBox.alert('Success', result[0].msg);
                                   template_status_store.reload();
                              }else{
                                   Ext.MessageBox.alert('Failure', result[0].msg);
                                   //template_store.reload();
                              }
                         },
                         failure: function(response){
                             var result=response.responseText;
                             Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                         }
                    });
               }
          }
     });
     
     var template_store = new Ext.data.JsonStore({
          root: 'records',
          totalProperty: 'totalCount',
          idProperty: 'id',
          //autoLoad:true,
          fields: ['Id', 'Site', 'SKU', 'Title', 'Price', 'shippingTemplateName', 'Quantity', 'ListingDuration', 'ListingType', 'Category', 'status'],
          sortInfo: {
               field: 'Id',
               direction: 'ASC'
          },
          remoteSort: true,
          url: 'service.php?action=getAllTemplate',
          listeners: {
               load: function(t, r){
                    //console.log(t.totalLength);
                    Ext.getCmp('template-accordion').setTitle('Template Category('+t.totalLength+')');
               }
          }
     })
     
     var template_grid = new Ext.grid.GridPanel({
          title: 'Template Under Category List',
          store: template_store,
          //autoHeight: true,
          width: 1024,
          height: 460,
          selModel: new Ext.grid.RowSelectionModel({}),
          plugins: [template_category_editor],
          columns:[
               {header: "ID", width: 60, align: 'center', sortable: true, dataIndex: 'Id'},
               {header: "Site", width: 30, align: 'center', sortable: true, dataIndex: 'Site', renderer: renderFlag},
               {header: "Sku", width: 80, align: 'center', sortable: true, dataIndex: 'SKU'},
               {header: "Title", width: 350, align: 'center', sortable: true, dataIndex: 'Title',
                    editor: {
                         xtype: 'textfield',
                         allowBlank: false
                    }
               },
               {header: "Listing Type", width: 100, align: 'center', sortable: true, dataIndex: 'ListingType'},
               {header: "Price", width: 60, align: 'center', sortable: true, dataIndex: 'Price',
                    editor: {
                         xtype: 'numberfield',
                         allowBlank: false
                    }
               },
               //{header: "Shipping Fee", width: 80, align: 'center', sortable: true, dataIndex: 'ShippingFee'},
               {header: "Shipping TP", width: 80, align: 'center', sortable: true, dataIndex: 'shippingTemplateName'},
               {header: "Qty", width: 30, align: 'center', sortable: true, dataIndex: 'Quantity',
                    editor: {
                         xtype: 'numberfield',
                         allowBlank: false
                    }
               },
               {header: "Duration", width: 70, align: 'center', sortable: true, dataIndex: 'ListingDuration',
                    editor: {
                         xtype: 'combo',
                         allowBlank: false,
                         store: listingTemplateDurationStore,
                         mode: 'local',
                         valueField:'id',
                         displayField:'name',
                         triggerAction: 'all',
                         editable: false,
                         selectOnFocus:true,
                         listeners: {
                              focus: function(t){
                                   var selections = template_grid.selModel.getSelections();
                                   //console.log(selections[0].data.Id);
                                   listingTemplateDurationStore.load({params: {Id: selections[0].data.Id}}); 
                              }
                         }
                    }
               },
               {header: "Category", width: 100, align: 'center', sortable: true, dataIndex: 'Category',
                    editor: {
                         xtype: 'combo',
                         //allowBlank: false,
                         mode: 'local',
                         store: templateCategoryStore,
                         valueField:'id',
                         displayField:'name',
                         triggerAction: 'all',
                         editable: false,
                         selectOnFocus:true
                    }
               },
               {header: "Status", width: 40, align: 'center', dataIndex: 'status', renderer: renderTemplateStatus}
          ],
          tbar:[{
               text: 'Search',
               icon: './images/magnifier.png',
               handler: function(){
                    var  searchWindow = new Ext.Window({
                              title: 'Search Templage' ,
                              closable:true,
                              width: 300,
                              height: 180,
                              plain:true,
                              layout: 'form',
                              items: [/*{
                                        id:'interval-date',
                                        fieldLabel:'Date',
                                        xtype:'datefield',
                                        format:'Y-m-d',
                                        minValue: new Date(),
                                        selectOnFocus:true
                                   },*/{
                                        id:'TID',
                                        fieldLabel:'TID',
                                        xtype:'numberfield'
                                   },{
                                        id:'SKU',
                                        fieldLabel:'SKU',
                                        xtype:'textfield'
                                   },{
                                        id:'Title',
                                        fieldLabel:'Item Title',
                                        xtype:'textfield'
                                   },{
                                        id:'ListingDuration',
                                        fieldLabel:'Duration',
                                        xtype:"combo",
                                        store:['', 'Days_3', 'Days_5', 'Days_7', 'Days_10', 'Days_30', 'Days_60', 'Days_90'],
                                        triggerAction: 'all',
                                        editable: false,
                                        selectOnFocus:true,
                                        listWidth:100,
                                        width:100
                                   }
                              ],
                              buttons: [{
                                             text: 'Submit',
                                             handler: function(){
                                                  template_store.baseParams = {
                                                       TID: Ext.getCmp("TID").getValue(),
                                                       SKU: Ext.getCmp("SKU").getValue(),
                                                       Title: Ext.getCmp("Title").getValue(),
                                                       ListingDuration: Ext.getCmp("ListingDuration").getValue()
                                                  };
                                                  template_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                                                  searchWindow.close();
                                             }
                                        },{
                                             text: 'Close',
                                             handler: function(){
                                                  searchWindow.close();
                                             }
                                        }]
                                        
                         })
                         
                         searchWindow.show();
               }
          },'-',{
                    text: 'Preview',
                    icon: './images/camera.png',
                    tooltip:'Preview Description',
                    handler: function(){
                         var selections = template_grid.selModel.getSelections();
                         if(template_grid.selModel.getCount() == 0){
                              Ext.MessageBox.alert('Warning','Please select the template you want to preview.');
                              return 0;
                         }
                         var ids = "";
                         for(var i = 0; i< template_grid.selModel.getCount(); i++){
                              ids += selections[i].data.Id + ","
                         }
                         ids = ids.slice(0,-1);
                         window.open(path + "preview.php?h=s&id="+ids,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                         return 1;
                    }
               },'-',{id: 'template_copy_num', xtype: 'numberfield', width: 30},{
                    text:'Copy',
                    icon: './images/plugin_link.png',
                    tooltip:'copy template',
                    handler: function(){
                         var selections = template_grid.selModel.getSelections();
                         if(template_grid.selModel.getCount() == 0){
                              Ext.MessageBox.alert('Warning','Please select the template you want to copy.');
                              return 0;
                         }
                         var ids = "";
                         for(var i = 0; i< template_grid.selModel.getCount(); i++){
                              ids += selections[i].data.Id + ","
                         }
                         ids = ids.slice(0,-1);
                         Ext.Ajax.request({  
                              waitMsg: 'Please Wait',
                              url: 'service.php?action=copyTemplate', 
                              params: { 
                                   ids: ids,
                                   copy_num: Ext.getCmp("template_copy_num").getValue()
                              }, 
                              success: function(response){
                                  var result=eval(response.responseText);
                                  //console.log(result);
                                  if(result[0].success){
                                        template_store.reload();
                                        template_category_tree.root.reload();
                                        alert(result[0].msg);
                                  }else{
                                        Ext.MessageBox.alert('Warning','Data error, please check template data.');
                                  }
                              },
                              failure: function(response){
                                  var result=response.responseText;
                                  Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                              }
                         });
                         return 1;
                    }
               },'-',{
                    text:'Edit',
                    icon: './images/plugin_edit.png',
                    tooltip:'edit multi template',
                    handler: function(){
                         var selections = template_grid.selModel.getSelections();
                         if(template_grid.selModel.getCount() == 0){
                              Ext.MessageBox.alert('Warning','Please select the template you want to edit.');
                              return 0;
                         }
                         var ids = "";
                         for(var i = 0; i< template_grid.selModel.getCount(); i++){
                              ids += selections[i].data.Id + ","
                         }
                         ids = ids.slice(0,-1);
                         if(template_grid.selModel.getCount() > 1){
                              window.open(path + "mtemplate.php?id="+ids,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                         }else{
                              window.open(path + "template.php?id="+ids,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                         }     
                         return 1;
                    }     
               }/*,'-',{
               text: 'Delete',
               icon: './images/cancel.png',
               tooltip:'Delete selected template',
               handler: function(){
                    var selections = template_grid.selModel.getSelections();
                    if(template_grid.selModel.getCount() == 0){
                         Ext.MessageBox.alert('Warning','Please select template.');
                         return 0;
                    }
                    var ids = "";
                    for(var i = 0; i< template_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    //console.log(ids);
                    Ext.Msg.confirm('Confirm', 'Delete template ' + ids, function(a, b, c){
                         if (a == 'yes'){
                              Ext.Ajax.request({  
                                   waitMsg: 'Please Wait',
                                   url: 'service.php?action=deleteTemplate', 
                                   params: { 
                                          ids: ids
                                   }, 
                                   success: function(response){
                                       var result=eval(response.responseText);
                                       switch(result){
                                          case 1:  // Success : simply reload
                                            template_store.reload();
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
                    });
                    return 1;
               }
          }*/,'-',{
               text: 'Import',
               icon: './images/folder_database.png',
               tooltip:'Import CSV file, include sku and tiitle / sku and price / sku and quantiry',
               handler: function(){
                    var  importCsvWindow = new Ext.Window({
                         title: 'Import CSV File' ,
                         closable:true,
                         width: 410,
                         height: 560,
                         plain:true,
                         layout: 'fit',
                         items: [{
                              xtype:'form',
                              id:'csv-form',
                              fileUpload: true,
                              frame: true,
                              autoHeight: true,
                              bodyStyle: 'padding: 10px 10px 0 10px;',
                              labelWidth: 160,
                              defaults: {
                                  anchor: '95%'
                                  //allowBlank: false
                              },
                              items:[{
                                   title:"Update Content(update template information)",
                                   xtype:"fieldset",
                                   items:[{
                                        xtype: 'textfield',
                                        inputType: 'password',
                                        id: 'update_password',
                                        fieldLabel: 'Password'
                                   },{
                                        xtype: 'fileuploadfield',
                                        id: 'spcsv',
                                        emptyText: 'Select an csv file',
                                        fieldLabel: 'Update Price By SKU',
                                        //hideLabel:true,
                                        name: 'spcsv',
                                        buttonText: '',
                                        buttonCfg: {
                                            iconCls: 'upload-icon'
                                        }
                                   },{
                                        xtype: 'button',
                                        text: 'Upload',
                                        handler: function(){
                                             var fp = Ext.getCmp("csv-form");
                                             if(fp.getForm().isValid()){
                                                  fp.getForm().submit({
                                                       url: 'service.php?action=importTemplateFromCSV&type=spcsv',
                                                       waitMsg: 'Uploading your csv...',
                                                       success: function(form, action){
                                                            //template_store.reload();
                                                            //importCsvWindow.close();
                                                            Ext.MessageBox.alert('Notice', action.result.msg);
                                                            //Ext.MessageBox.alert('Success','Update template sku price success!');
                                                       }
                                                  });
                                             }
                                        }
                                   },{
                                        xtype: 'fileuploadfield',
                                        id: 'sqcsv',
                                        emptyText: 'Select an csv file',
                                        fieldLabel: 'Update Qty By SKU',
                                        //hideLabel:true,
                                        name: 'sqcsv',
                                        buttonText: '',
                                        buttonCfg: {
                                            iconCls: 'upload-icon'
                                        }
                                   },{
                                        xtype: 'button',
                                        text: 'Upload',
                                        handler: function(){
                                             var fp = Ext.getCmp("csv-form");
                                             if(fp.getForm().isValid()){
                                                  fp.getForm().submit({
                                                       url: 'service.php?action=importTemplateFromCSV&type=sqcsv',
                                                       waitMsg: 'Uploading your csv...',
                                                       success: function(form, action){
                                                            //template_store.reload();
                                                            //importCsvWindow.close();
                                                            Ext.MessageBox.alert('Notice', action.result.msg);
                                                            //Ext.MessageBox.alert('Success','Update template sku quantiry success!');
                                                       }
                                                  });
                                             }
                                        }
                                   },{
                                        xtype: 'fileuploadfield',
                                        id: 'stpcsv',
                                        emptyText: 'Select an csv file',
                                        fieldLabel: 'Update Price By SKU and Title',
                                        //hideLabel:true,
                                        name: 'stpcsv',
                                        buttonText: '',
                                        buttonCfg: {
                                            iconCls: 'upload-icon'
                                        }
                                   },{
                                        xtype: 'button',
                                        text: 'Upload',
                                        handler: function(){
                                             var fp = Ext.getCmp("csv-form");
                                             if(fp.getForm().isValid()){
                                                  fp.getForm().submit({
                                                       url: 'service.php?action=importTemplateFromCSV&type=stpcsv',
                                                       waitMsg: 'Uploading your csv...',
                                                       success: function(form, action){
                                                            //template_store.reload();
                                                            //importCsvWindow.close();
                                                            Ext.MessageBox.alert('Notice', action.result.msg);
                                                            //Ext.MessageBox.alert('Success','Update template sku price success!');
                                                       }
                                                  });
                                             }
                                        }
                                   },{
                                        xtype: 'fileuploadfield',
                                        id: 'dtcsv',
                                        emptyText: 'Select an csv file',
                                        fieldLabel: 'Update Duration By TemplateID',
                                        //hideLabel:true,
                                        name: 'dtcsv',
                                        buttonText: '',
                                        buttonCfg: {
                                            iconCls: 'upload-icon'
                                        }
                                   },{
                                        xtype: 'button',
                                        text: 'Upload',
                                        handler: function(){
                                             var fp = Ext.getCmp("csv-form");
                                             if(fp.getForm().isValid()){
                                                  fp.getForm().submit({
                                                       url: 'service.php?action=importTemplateFromCSV&type=dtcsv',
                                                       waitMsg: 'Uploading your csv...',
                                                       success: function(form, action){
                                                            //template_store.reload();
                                                            //importCsvWindow.close();
                                                            Ext.MessageBox.alert('Notice', action.result.msg);
                                                            //Ext.MessageBox.alert('Success','Update template sku price success!');
                                                       }
                                                  });
                                             }
                                        }
                                   }]
                              },{
                                   title:"Add TO Upload(change template to item)",
                                   xtype:"fieldset",
                                   items:[{
                                        xtype: 'textfield',
                                        inputType: 'password',
                                        id: 'upload_password',
                                        fieldLabel: 'Password'
                                   },{
                                        xtype: 'fileuploadfield',
                                        id: 'stcsv',
                                        emptyText: 'Select an csv file',
                                        fieldLabel: 'Sku and Title',
                                        //hideLabel:true,
                                        name: 'stcsv',
                                        buttonText: '',
                                        buttonCfg: {
                                            iconCls: 'upload-icon'
                                        }
                                   },{
                                        xtype: 'button',
                                        text: 'Upload',
                                        handler: function(){
                                             var fp = Ext.getCmp("csv-form");
                                             if(fp.getForm().isValid()){
                                                  fp.getForm().submit({
                                                       url: 'service.php?action=importTemplateFromCSV&type=stcsv',
                                                       waitMsg: 'Uploading your csv...',
                                                       success: function(form, action){
                                                            //importCsvWindow.close();
                                                            Ext.MessageBox.alert('Notice', action.result.msg);
                                                            //Ext.MessageBox.alert('Success','add to waiting to upload success!');
                                                       }
                                                  });
                                             }
                                        }
                                   },{
                                        xtype: 'fileuploadfield',
                                        id: 'tcsv',
                                        emptyText: 'Select an csv file',
                                        fieldLabel: 'Template ID',
                                        //hideLabel:true,
                                        name: 'tcsv',
                                        buttonText: '',
                                        buttonCfg: {
                                            iconCls: 'upload-icon'
                                        }
                                   },{
                                        xtype: 'button',
                                        text: 'Upload',
                                        handler: function(){
                                             var fp = Ext.getCmp("csv-form");
                                             if(fp.getForm().isValid()){
                                                  fp.getForm().submit({
                                                       url: 'service.php?action=importTemplateFromCSV&type=tcsv',
                                                       waitMsg: 'Uploading your csv...',
                                                       success: function(form, action){
                                                            //importCsvWindow.close();
                                                            Ext.MessageBox.alert('Notice', action.result.msg);
                                                            //Ext.MessageBox.alert('Success','add to waiting to upload success!');
                                                       }
                                                  });
                                             }
                                        }
                                   }]
                              }]
                              /*
                              items:[{
                                   xtype: 'fileuploadfield',
                                   id: 'spcsv',
                                   emptyText: 'Select an csv file',
                                   fieldLabel: 'Sku and Price',
                                   //hideLabel:true,
                                   name: 'spcsv',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              },{
                                   xtype: 'button',
                                   text: 'Upload',
                                   handler: function(){
                                        var fp = Ext.getCmp("csv-form");
                                        if(fp.getForm().isValid()){
                                             fp.getForm().submit({
                                                  url: 'service.php?action=importTemplateToCSV&type=spcsv',
                                                  waitMsg: 'Uploading your csv...',
                                                  success: function(fp, o){
                                                       template_store.reload();
                                                       Ext.MessageBox.alert('Success','Update template sku price success!');
                                                  }
                                             });
                                        }
                                   }
                              },{
                                   xtype: 'fileuploadfield',
                                   id: 'sqcsv',
                                   emptyText: 'Select an csv file',
                                   fieldLabel: 'Sku and Qty',
                                   //hideLabel:true,
                                   name: 'sqcsv',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              },{
                                   xtype: 'button',
                                   text: 'Upload',
                                   handler: function(){
                                        var fp = Ext.getCmp("csv-form");
                                        if(fp.getForm().isValid()){
                                             fp.getForm().submit({
                                                  url: 'service.php?action=importTemplateToCSV&type=sqcsv',
                                                  waitMsg: 'Uploading your csv...',
                                                  success: function(fp, o){
                                                       template_store.reload();
                                                       Ext.MessageBox.alert('Success','Update template sku quantiry success!');
                                                  }
                                             });
                                        }
                                   }
                              }]
                              */
                         }],                                           
                         buttons: [{
                                        text: 'Close',
                                        handler: function(){
                                             importCsvWindow.close();
                                        }
                                   }]
                                   
                    })
                    importCsvWindow.show();   
               }
          },'-',{
               text: 'Export',
               icon: './images/plugin_go.png',
               tooltip:'Export Template to Excel',
               handler: function(){
                    var  exportWindow = new Ext.Window({
                         title: 'Please select the export conditions' ,
                         closable:true,
                         width: 300,
                         height: 220,
                         plain:true,
                         layout: 'form',
                         items: [{
                                   id:'SKU',
                                   fieldLabel:'SKU',
                                   xtype:'textfield'
                              },{
                                   id:'Title',
                                   fieldLabel:'Title',
                                   xtype:'textfield'
                              },{
                                   id:'ListingType',
                                   fieldLabel:'Listing Type',
                                   xtype:"combo",
                                   store:['', 'Chinese', 'Dutch', 'StoresFixedPrice', 'FixedPriceItem'],
                                   triggerAction: 'all',
                                   editable: false,
                                   selectOnFocus:true,
                                   listWidth:100,
                                   width:100
                              },{
                                   id:'ListingDuration',
                                   fieldLabel:'Listing Duration',
                                   xtype: 'combo',
                                   store:['', 'Days_3', 'Days_5', 'Days_7', 'Days_10', 'Days_30'],
                                   triggerAction: 'all',
                                   editable: false,
                                   selectOnFocus:true,
                                   listWidth:100,
                                   width:100
                              },{
                                   id:'TemplateCategory',
                                   fieldLabel:'Category',
                                   xtype:"combo",
                                   mode: 'local',
                                   store: templateCategoryStore,
                                   valueField:'id',
                                   displayField:'name',
                                   triggerAction: 'all',
                                   editable: false,
                                   selectOnFocus:true
                              },{
                                   id:'TemplateStatus',
                                   fieldLabel:'Status',
                                   xtype: 'combo',
                                   store:['New', 'Waiting For Approve', 'Active', 'Out Of Stock', 'Under Review', 'Inactive', 'Forever Inactive', 'Forever Listing'],
                                   triggerAction: 'all',
                                   editable: false,
                                   selectOnFocus:true,
                                   listWidth:100,
                                   width:100
                              }
                         ],
                         buttons: [{
                                        text: 'Submit',
                                        handler: function(){
                                             window.open("service.php?action=exportTemplateToExcel&"+Ext.urlEncode({'SKU': Ext.getCmp('SKU').getValue(), 'Title': Ext.getCmp('Title').getValue(),
                                                          'ListingType': Ext.getCmp('ListingType').getValue(), 'ListingDuration': Ext.getCmp('ListingDuration').getValue(),
                                                          'TemplateCategory': Ext.getCmp('TemplateCategory').getValue(),
                                                          'TemplateStatus': Ext.getCmp('TemplateStatus').getValue()}),"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=100, height=100");  
                                             exportWindow.close();
                                        }
                                   },{
                                        text: 'Close',
                                        handler: function(){
                                             exportWindow.close();
                                        }
                                   }]
                                   
                    })
                    
                    exportWindow.show();
               }
          },'-',{
               text:'Add To Upload',
               icon: './images/package_go.png',
               tooltip:'add selected template to waiting to upload(no set date time)',
               handler: function(){
                    var selections = template_grid.selModel.getSelections();
                    if(template_grid.selModel.getCount() == 0){
                         Ext.MessageBox.alert('Warning','Please select template.');
                         return 0;
                    }
                    var ids = "";
                    for(var i = 0; i< template_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=addTemplateToUpload', 
                         params: { 
                              ids: ids
                         }, 
                         success: function(response){
                             var result = eval(response.responseText);
                              //console.log(result);
                              if(result[0].success){
                                   Ext.MessageBox.alert('Success', result[0].msg);      
                              }else{
                                   Ext.MessageBox.alert('Failure', result[0].msg);      
                              }
                         },
                         failure: function(response){
                             var result=response.responseText;
                             Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                         }
                    });
                    
                    return 1;
               }
          },'-',{
               text:'Immediately Upload',
               icon: './images/arrow_up.png',
               tooltip:'Immediately upload selected template',
               handler: function(){
                    var selections = template_grid.selModel.getSelections();
                    if(template_grid.selModel.getCount() == 0){
                         Ext.MessageBox.alert('Warning','Please select template.');
                         return 0;
                    }
                    var ids = "";
                    for(var i = 0; i< template_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=immediatelyUploadTemplate', 
                         params: { 
                              ids: ids
                         }, 
                         success: function(response){
                             var result = eval(response.responseText);
                              //console.log(result);
                              if(result[0].success){
                                   Ext.MessageBox.alert('Success', result[0].msg);      
                              }else{
                                   Ext.MessageBox.alert('Failure', result[0].msg);      
                              }
                         },
                         failure: function(response){
                             var result=response.responseText;
                             Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                         }
                    });
                    
                    return 1;
               }
          },'-',{
               text: 'Schedule Upload',
               icon: './images/date_go.png',
               tooltip:'add selected template to waiting to upload based on schedule date',
               handler: function(){
                    var selections = template_grid.selModel.getSelections();
                    if(template_grid.selModel.getCount() == 0){
                         Ext.MessageBox.alert('Warning','Please select the template you want to upload.');
                         return 0;
                    }
                    var ids = "";
                    for(var i = 0; i< template_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    //console.log(ids);
                    
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=scheduleUploadTemplate', 
                         params: { 
                                ids: ids
                         }, 
                         success: function(response){
                             var result = eval(response.responseText);
                              //console.log(result);
                              if(result[0].success){
                                   Ext.MessageBox.alert('Success', result[0].msg);      
                              }else{
                                   Ext.MessageBox.alert('Failure', result[0].msg);      
                              }
                         },
                         failure: function(response){
                             var result=response.responseText;
                             Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                         }
                    });
                    return 1;
               }
          },'-',{
                    text:'Interval Upload',
                    icon: './images/clock_add.png',
                    tooltip:'add selected template to waiting to upload based on interval time',
                    handler: function(){
                         var selections = template_grid.selModel.getSelections();
                         if(template_grid.selModel.getCount() == 0){
                              Ext.MessageBox.alert('Warning','Please select template.');
                              return 0;
                         }
                         var ids = "";
                         for(var i = 0; i< template_grid.selModel.getCount(); i++){
                              ids += selections[i].data.Id + ","
                         }
                         ids = ids.slice(0,-1);
                         
                         //var today = new Date();
                         //console.log(today.getFullYear()+'-'+minMonth+'-'+minDay);
                         
                         var  intervalUploadWindow = new Ext.Window({
                              title: 'Interval Upload Set' ,
                              closable:true,
                              width: 300,
                              height: 180,
                              plain:true,
                              layout: 'form',
                              items: [{
                                        id:'interval-date',
                                        fieldLabel:'Date',
                                        xtype:'datefield',
                                        format:'Y-m-d',
                                        minValue: new Date(),
                                        selectOnFocus:true
                                   },{
                                        id:'interval-time',
                                        fieldLabel:'Time',
                                        xtype:'timefield',
                                        increment:1,
                                        triggerAction: 'all',
                                        editable: false,
                                        selectOnFocus:true,
                                        listWidth:80,
                                        width:80  
                                   },{
                                        id:'interval-minute',
                                        fieldLabel:'Interval',
                                        xtype:"combo",
                                        store:[0,1,2,3,4,5,6,7,8,9,10],
                                        listWidth:60,
                                        width:60
                                   }
                              ],
                              buttons: [{
                                             text: 'Ok',
                                             handler: function(){
                                                  Ext.Ajax.request({  
                                                       waitMsg: 'Please Wait',
                                                       url: 'service.php?action=intervalUploadTemplate', 
                                                       params: {
                                                            ids: ids,
                                                            date: Ext.getCmp('interval-date').getValue(),
                                                            time: Ext.getCmp('interval-time').getValue(),
                                                            minute: Ext.getCmp('interval-minute').getValue()
                                                       }, 
                                                       success: function(response){
                                                            //console.log(response);
                                                            var result = eval(response.responseText);
                                                            //console.log(result);
                                                            if(result[0].success){
                                                                 //template_store.reload();
                                                                 intervalUploadWindow.close();
                                                                 Ext.MessageBox.alert('Success', result[0].msg);
                                                            }else{
                                                                 Ext.MessageBox.alert('Warning', result[0].msg);
                                                            }
                                                       },
                                                       failure: function(response){
                                                           var result=response.responseText;
                                                           Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                       }
                                                  });
                                             }
                                        },{
                                             text: 'Close',
                                             handler: function(){
                                                  intervalUploadWindow.close();
                                             }
                                        }]
                                        
                         })
                         
                         intervalUploadWindow.show();
                         return 1;
                    }
          }],
          bbar: [new Ext.PagingToolbar({
              pageSize: parseInt(getCookie("pagination")),
              store: template_store,
              displayInfo: true
          }),'-',{
               text: 'Import SpoonFeeder',
               icon: './images/spoon.png',
               tooltip:'Import SpoonFeeder template file',
               handler: function(){
                    var  importAieWindow = new Ext.Window({
                         title: 'Import SpoonFeeder Template' ,
                         closable:true,
                         width: 320,
                         height: 400,
                         plain:true,
                         iconCls: 'import-spoonfeeder',
                         layout: 'fit',
                         items: [{
                              xtype:'form',
                              id:'aie-form',
                              fileUpload: true,
                              frame: true,
                              autoHeight: true,
                              bodyStyle: 'padding: 10px 10px 0 10px;',
                              labelWidth: 80,
                              defaults: {
                                  anchor: '95%'
                                  //allowBlank: false
                              },
                              items:[{
                                   xtype: 'fileuploadfield',
                                   id: 'aie-1',
                                   emptyText: 'Select an file',
                                   fieldLabel: 'Template 1',
                                   //hideLabel:true,
                                   name: 'aie-1',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              },{
                                   xtype: 'fileuploadfield',
                                   id: 'aie-2',
                                   emptyText: 'Select an file',
                                   fieldLabel: 'Template 2',
                                   //hideLabel:true,
                                   name: 'aie-2',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              },{
                                   xtype: 'fileuploadfield',
                                   id: 'aie-3',
                                   emptyText: 'Select an file',
                                   fieldLabel: 'Template 3',
                                   //hideLabel:true,
                                   name: 'aie-3',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              },{
                                   xtype: 'fileuploadfield',
                                   id: 'aie-4',
                                   emptyText: 'Select an file',
                                   fieldLabel: 'Template 4',
                                   //hideLabel:true,
                                   name: 'aie-4',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              },{
                                   xtype: 'fileuploadfield',
                                   id: 'aie-5',
                                   emptyText: 'Select an file',
                                   fieldLabel: 'Template 5',
                                   //hideLabel:true,
                                   name: 'aie-5',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              },{
                                   xtype: 'fileuploadfield',
                                   id: 'aie-6',
                                   emptyText: 'Select an file',
                                   fieldLabel: 'Template 6',
                                   //hideLabel:true,
                                   name: 'aie-6',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              },{
                                   xtype: 'fileuploadfield',
                                   id: 'aie-7',
                                   emptyText: 'Select an file',
                                   fieldLabel: 'Template 7',
                                   //hideLabel:true,
                                   name: 'aie-7',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              },{
                                   xtype: 'fileuploadfield',
                                   id: 'aie-8',
                                   emptyText: 'Select an file',
                                   fieldLabel: 'Template 8',
                                   //hideLabel:true,
                                   name: 'aie-8',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              },{
                                   xtype: 'fileuploadfield',
                                   id: 'aie-9',
                                   emptyText: 'Select an file',
                                   fieldLabel: 'Template 9',
                                   //hideLabel:true,
                                   name: 'aie-9',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              },{
                                   xtype: 'fileuploadfield',
                                   id: 'aie-10',
                                   emptyText: 'Select an file',
                                   fieldLabel: 'Template 10',
                                   //hideLabel:true,
                                   name: 'aie-10',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              },{
                                   xtype: 'combo',
                                   fieldLabel:"Category",
                                   mode: 'local',
                                   store: new Ext.data.JsonStore({
                                       autoLoad: true,
                                       fields: ['id', 'name'],
                                       url: "service.php?action=getTemplateCategory"
                                   }),
                                   valueField:'id',
                                   displayField:'name',
                                   triggerAction: 'all',
                                   editable: false,
                                   selectOnFocus:true,
                                   name: 'template_category_id',
                                   hiddenName:'template_category_id'
                              },{
                                   xtype: 'button',
                                   text: 'Upload',
                                   handler: function(){
                                        var fp = Ext.getCmp("aie-form");
                                        //if(fp.getForm().isValid()){
                                             fp.getForm().submit({
                                                  url: 'service.php?action=importTemplateFromSpoonFeeder',
                                                  waitMsg: 'Import SpoonFeeder Template...',
                                                  success: function(f, a){
                                                       importAieWindow.close();
                                                       template_store.reload();
                                                       if(a.result.success){
                                                            Ext.MessageBox.alert('Success', a.result.msg);
                                                       }else{
                                                            Ext.MessageBox.alert('Failure', a.result.msg);
                                                       }
                                                  }
                                             });
                                        //}
                                   }
                              }]
                         }],                                           
                         buttons: [{
                                        text: 'Close',
                                        handler: function(){
                                             importAieWindow.close();
                                        }
                                   }]
                                   
                    })
                    importAieWindow.show();   
               }
          },'-',{
                    text: 'Import Turbo Lister',
                    icon: './images/tb.png',
                    tooltip:'Import turbo lister template file',
                    handler: function(){
                    var  importTbWindow = new Ext.Window({
                         title: 'Import Turbo Lister Template' ,
                         closable:true,
                         width: 320,
                         height: 180,
                         plain:true,
                         layout: 'fit',
                         iconCls: 'import-turbo-lister',
                         items: [{
                              xtype:'form',
                              id:'tb-form',
                              fileUpload: true,
                              frame: true,
                              autoHeight: true,
                              bodyStyle: 'padding: 10px 10px 0 10px;',
                              labelWidth: 80,
                              defaults: {
                                  anchor: '95%',
                                  allowBlank: false
                              },
                              items:[{
                                   xtype: 'fileuploadfield',
                                   id: 'turboLister',
                                   emptyText: 'Select an file',
                                   fieldLabel: 'Template',
                                   //hideLabel:true,
                                   name: 'turboLister',
                                   buttonText: '',
                                   buttonCfg: {
                                       iconCls: 'upload-icon'
                                   }
                              },{
                                   xtype: 'combo',
                                   fieldLabel:"Category",
                                   mode: 'local',
                                   store: new Ext.data.JsonStore({
                                       autoLoad: true,
                                       fields: ['id', 'name'],
                                       url: "service.php?action=getTemplateCategory"
                                   }),
                                   valueField:'id',
                                   displayField:'name',
                                   triggerAction: 'all',
                                   editable: false,
                                   selectOnFocus:true,
                                   name: 'template_category_id',
                                   hiddenName:'template_category_id'
                              },{
                                   xtype: 'button',
                                   text: 'Import',
                                   handler: function(){
                                        var fp = Ext.getCmp("tb-form");
                                        if(fp.getForm().isValid()){
                                             fp.getForm().submit({
                                                  url: 'service.php?action=importTemplateFromTurboLister',
                                                  waitMsg: 'Import Turbo Lister Template...',
                                                  success: function(f, a){
                                                       //console.log(a);
                                                       importTbWindow.close();
                                                       template_store.reload();
                                                       if(a.result.success){
                                                            Ext.MessageBox.alert('Success', a.result.msg);
                                                       }else{
                                                            Ext.MessageBox.alert('Failure', a.result.msg);
                                                       }
                                                  }
                                             });
                                        }
                                   }
                              }]
                         }],                                           
                         buttons: [{
                                   text: 'Close',
                                   handler: function(){
                                        importTbWindow.close();
                                   }
                         }]
                    })
                    importTbWindow.show();   
               }
          }]
     })
     
     var template_status_store = new Ext.data.JsonStore({
          root: 'records',
          totalProperty: 'totalCount',
          idProperty: 'id',
          //autoLoad:true,
          fields: ['Id', 'Site', 'SKU', 'Title', 'Price', 'shippingTemplateName', 'Quantity', 'ListingDuration', 'ListingType', 'Category', 'ForeverListingTime'],
          sortInfo: {
               field: 'Id',
               direction: 'ASC'
          },
          remoteSort: true,
          url: 'service.php?action=getTemplateByStatus',
          listeners: {
               load: function(t, r){
                    Ext.getCmp('template-status').setTitle('Template Status('+t.totalLength+')');
               }
          }
     })
     
     var template_status_grid = new Ext.grid.GridPanel({
          title: 'Template Under Status List',
          store: template_status_store,
          //autoHeight: true,
          width: 1024,
          height: 460,
          selModel: new Ext.grid.RowSelectionModel({}),
          plugins: [template_status_editor],
          columns:[
               {header: "ID", width: 60, align: 'center', sortable: true, dataIndex: 'Id'},
               {header: "Site", width: 30, align: 'center', sortable: true, dataIndex: 'Site', renderer: renderFlag},
               {header: "Sku", width: 80, align: 'center', sortable: true, dataIndex: 'SKU'},
               {header: "Title", width: 350, align: 'center', sortable: true, dataIndex: 'Title',
                    editor: {
                         xtype: 'textfield',
                         allowBlank: false
                    }
               },
               {header: "Listing Type", width: 80, align: 'center', sortable: true, dataIndex: 'ListingType'},
               {header: "Price", width: 60, align: 'center', sortable: true, dataIndex: 'Price',
                    editor: {
                         xtype: 'numberfield',
                         allowBlank: false
                    }
               },
               //{header: "Shipping Fee", width: 80, align: 'center', sortable: true, dataIndex: 'ShippingFee'},
               {header: "Shipping TP", width: 80, align: 'center', sortable: true, dataIndex: 'shippingTemplateName'},
               {header: "Qty", width: 30, align: 'center', sortable: true, dataIndex: 'Quantity',
                    editor: {
                         xtype: 'numberfield',
                         allowBlank: false
                    }
               },
               {header: "Duration", width: 70, align: 'center', sortable: true, dataIndex: 'ListingDuration',
                    editor: {
                         xtype: 'combo',
                         allowBlank: false,
                         store: listingTemplateDurationStore,
                         mode: 'local',
                         valueField:'id',
                         displayField:'name',
                         triggerAction: 'all',
                         editable: false,
                         selectOnFocus:true,
                         listeners: {
                              focus: function(t){
                                   var selections = template_status_grid.selModel.getSelections();
                                   //console.log(selections[0].data.Id);
                                   listingTemplateDurationStore.load({params: {Id: selections[0].data.Id}}); 
                              }
                         }
                    }
               },
               {header: "Category", width: 100, align: 'center', sortable: true, dataIndex: 'Category',
                    editor: {
                         xtype: 'combo',
                         //allowBlank: false,
                         mode: 'local',
                         store: templateCategoryStore,
                         valueField:'id',
                         displayField:'name',
                         triggerAction: 'all',
                         editable: false,
                         selectOnFocus:true
                    }
               },{
                   header: "Forever Time", width: 80, align: 'center', sortable: true, dataIndex: 'ForeverListingTime'
               }
          ],
          tbar:[{
               id: 'template-status-combo',
               xtype: 'combo',
               mode: 'local',
               width: 100,
               listWidth: 120,
               store: new Ext.data.ArrayStore({
                    fields: [
                        'id',
                        'name'
                    ],
                    data: [[5,'Freeze SKU']] 
               }),
               valueField: 'id',
               displayField: 'name',
               triggerAction: 'all',
               editable: false
          },{xtype: 'tbspacer', width: 120},{
               text: 'Change Status',
               icon: './images/cog_go.png',
               handler: function(){
                    var status_array = ['new', 'waiting for approve', 'active', 'out of stock', 'under review', 'inactive', 'forever inactive', 'forever listing'];
                    var selections = template_status_grid.selModel.getSelections();
                    if(template_status_grid.selModel.getCount() == 0){
                         Ext.MessageBox.alert('Warning','Please select the template you want to preview.');
                         return 0;
                    }
                    var ids = "";
                    for(var i = 0; i< template_status_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    Ext.Msg.confirm('Confirm', 'Change template status ' + ids + ' to <font color="red">' + status_array[Ext.getCmp('template-status-combo').getValue()] + '</font>', function(a, b, c){
                         if (a == 'yes'){
                              Ext.Ajax.request({  
                                   waitMsg: 'Please Wait',
                                   url: 'service.php?action=changeTemplateStatus', 
                                   params: { 
                                        ids: ids,
                                        status: Ext.getCmp('template-status-combo').getValue()
                                   }, 
                                   success: function(response){
                                       var result=eval(response.responseText);
                                       switch(result){
                                          case 1:  // Success : simply reload
                                            template_status_store.reload();
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
                    });
                    return 1;
               }
          },'-',{
               text: 'Search',
               icon: './images/magnifier.png',
               handler: function(){
                    var  searchWindow = new Ext.Window({
                              title: 'Search Templage' ,
                              closable:true,
                              width: 300,
                              height: 180,
                              plain:true,
                              layout: 'form',
                              items: [/*{
                                        id:'interval-date',
                                        fieldLabel:'Date',
                                        xtype:'datefield',
                                        format:'Y-m-d',
                                        minValue: new Date(),
                                        selectOnFocus:true
                                   },*/{
                                        id:'TID',
                                        fieldLabel:'TID',
                                        xtype:'numberfield'
                                   },{
                                        id:'SKU',
                                        fieldLabel:'SKU',
                                        xtype:'textfield'
                                   },{
                                        id:'Title',
                                        fieldLabel:'Item Title',
                                        xtype:'textfield'
                                   },{
                                        id:'ListingDuration',
                                        fieldLabel:'Duration',
                                        xtype:"combo",
                                        store:['', 'Days_3', 'Days_5', 'Days_7', 'Days_10', 'Days_30', 'Days_60', 'Days_90'],
                                        triggerAction: 'all',
                                        editable: false,
                                        selectOnFocus:true,
                                        listWidth:100,
                                        width:100
                                   }
                              ],
                              buttons: [{
                                             text: 'Submit',
                                             handler: function(){
                                                  template_status_store.setBaseParam("TID", Ext.getCmp("TID").getValue());
                                                  template_status_store.setBaseParam("SKU", Ext.getCmp("SKU").getValue());
                                                  template_status_store.setBaseParam("Title", Ext.getCmp("Title").getValue());
                                                  template_status_store.setBaseParam("ListingDuration", Ext.getCmp("ListingDuration").getValue());
                                                  template_status_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                                                  searchWindow.close();
                                             }
                                        },{
                                             text: 'Close',
                                             handler: function(){
                                                  searchWindow.close();
                                             }
                                        }]
                                        
                         })
                         
                         searchWindow.show();
               }
          },'-',{
                    text: 'Preview',
                    icon: './images/camera.png',
                    tooltip:'Preview Description',
                    handler: function(){
                         var selections = template_status_grid.selModel.getSelections();
                         if(template_status_grid.selModel.getCount() == 0){
                              Ext.MessageBox.alert('Warning','Please select the template you want to preview.');
                              return 0;
                         }
                         var ids = "";
                         for(var i = 0; i< template_status_grid.selModel.getCount(); i++){
                              ids += selections[i].data.Id + ","
                         }
                         ids = ids.slice(0,-1);
                         window.open(path + "preview.php?h=s&id="+ids,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                         return 1;
                    }
               },'-',{id: 'template_copy_num', xtype: 'numberfield', width: 30},{
                    text:'Copy',
                    icon: './images/plugin_link.png',
                    tooltip:'copy template',
                    handler: function(){
                         var selections = template_status_grid.selModel.getSelections();
                         if(template_status_grid.selModel.getCount() == 0){
                              Ext.MessageBox.alert('Warning','Please select the template you want to copy.');
                              return 0;
                         }
                         var ids = "";
                         for(var i = 0; i< template_status_grid.selModel.getCount(); i++){
                              ids += selections[i].data.Id + ","
                         }
                         ids = ids.slice(0,-1);
                         Ext.Ajax.request({  
                              waitMsg: 'Please Wait',
                              url: 'service.php?action=copyTemplate', 
                              params: { 
                                   ids: ids,
                                   copy_num: Ext.getCmp("template_copy_num").getValue()
                              }, 
                              success: function(response){
                                  var result=eval(response.responseText);
                                  //console.log(result);
                                  if(result[0].success){
                                        template_status_store.reload();
                                        alert(result[0].msg);
                                  }else{
                                        Ext.MessageBox.alert('Warning','Data error, please check template data.');
                                  }
                              },
                              failure: function(response){
                                  var result=response.responseText;
                                  Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                              }
                         });
                         return 1;
                    }
               },'-',{
                    text:'Edit',
                    icon: './images/plugin_edit.png',
                    tooltip:'edit multi template',
                    handler: function(){
                         var selections = template_status_grid.selModel.getSelections();
                         if(template_status_grid.selModel.getCount() == 0){
                              Ext.MessageBox.alert('Warning','Please select the template you want to edit.');
                              return 0;
                         }
                         var ids = "";
                         for(var i = 0; i< template_status_grid.selModel.getCount(); i++){
                              ids += selections[i].data.Id + ","
                         }
                         ids = ids.slice(0,-1);
                         if(template_status_grid.selModel.getCount() > 1){
                              window.open(path + "mtemplate.php?id="+ids,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                         }else{
                              window.open(path + "template.php?id="+ids,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                         }     
                         return 1;
                    }     
               }/*,'-',{
               text: 'Delete',
               icon: './images/cancel.png',
               tooltip:'Delete selected template',
               handler: function(){
                    var selections = template_status_grid.selModel.getSelections();
                    if(template_status_grid.selModel.getCount() == 0){
                         Ext.MessageBox.alert('Warning','Please select template.');
                         return 0;
                    }
                    var ids = "";
                    for(var i = 0; i< template_status_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    //console.log(ids);
                    Ext.Msg.confirm('Confirm', 'Delete template ' + ids, function(a, b, c){
                         if (a == 'yes'){
                              Ext.Ajax.request({  
                                   waitMsg: 'Please Wait',
                                   url: 'service.php?action=deleteTemplate', 
                                   params: { 
                                          ids: ids
                                   }, 
                                   success: function(response){
                                       var result=eval(response.responseText);
                                       switch(result){
                                          case 1:  // Success : simply reload
                                            template_status_store.reload();
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
                    });
                    return 1;
               }
          }*/,'-',{
               text:'Add To Upload',
               icon: './images/package_go.png',
               tooltip:'add selected template to waiting to upload(no set date time)',
               handler: function(){
                    var selections = template_status_grid.selModel.getSelections();
                    if(template_status_grid.selModel.getCount() == 0){
                         Ext.MessageBox.alert('Warning','Please select template.');
                         return 0;
                    }
                    var ids = "";
                    for(var i = 0; i< template_status_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=addTemplateToUpload', 
                         params: { 
                              ids: ids
                         }, 
                         success: function(response){
                             var result = eval(response.responseText);
                              //console.log(result);
                              if(result[0].success){
                                   Ext.MessageBox.alert('Success', result[0].msg);      
                              }else{
                                   Ext.MessageBox.alert('Failure', result[0].msg);      
                              }
                         },
                         failure: function(response){
                             var result=response.responseText;
                             Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                         }
                    });
                    
                    return 1;
               }
          },'-',{
               text:'Immediately Upload',
               icon: './images/arrow_up.png',
               tooltip:'Immediately upload selected template',
               handler: function(){
                    var selections = template_status_grid.selModel.getSelections();
                    if(template_status_grid.selModel.getCount() == 0){
                         Ext.MessageBox.alert('Warning','Please select template.');
                         return 0;
                    }
                    var ids = "";
                    for(var i = 0; i< template_status_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=immediatelyUploadTemplate', 
                         params: { 
                              ids: ids
                         }, 
                         success: function(response){
                             var result = eval(response.responseText);
                              //console.log(result);
                              if(result[0].success){
                                   Ext.MessageBox.alert('Success', result[0].msg);      
                              }else{
                                   Ext.MessageBox.alert('Failure', result[0].msg);      
                              }
                         },
                         failure: function(response){
                             var result=response.responseText;
                             Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                         }
                    });
                    
                    return 1;
               }
          },'-',{
                    text:'Interval Upload',
                    icon: './images/clock_add.png',
                    tooltip:'add selected template to waiting to upload based on interval time',
                    handler: function(){
                         var selections = template_status_grid.selModel.getSelections();
                         if(template_status_grid.selModel.getCount() == 0){
                              Ext.MessageBox.alert('Warning','Please select template.');
                              return 0;
                         }
                         var ids = "";
                         for(var i = 0; i< template_status_grid.selModel.getCount(); i++){
                              ids += selections[i].data.Id + ","
                         }
                         ids = ids.slice(0,-1);
                         
                         //var today = new Date();
                         //console.log(today.getFullYear()+'-'+minMonth+'-'+minDay);
                         
                         var  intervalUploadWindow = new Ext.Window({
                              title: 'Interval Upload Set' ,
                              closable:true,
                              width: 300,
                              height: 180,
                              plain:true,
                              layout: 'form',
                              items: [{
                                        id:'interval-date',
                                        fieldLabel:'Date',
                                        xtype:'datefield',
                                        format:'Y-m-d',
                                        minValue: new Date(),
                                        selectOnFocus:true
                                   },{
                                        id:'interval-time',
                                        fieldLabel:'Time',
                                        xtype:'timefield',
                                        increment:1,
                                        triggerAction: 'all',
                                        editable: false,
                                        selectOnFocus:true,
                                        listWidth:80,
                                        width:80  
                                   },{
                                        id:'interval-minute',
                                        fieldLabel:'Interval',
                                        xtype:"combo",
                                        store:[0,1,2,3,4,5,6,7,8,9,10],
                                        listWidth:60,
                                        width:60
                                   }
                              ],
                              buttons: [{
                                             text: 'Ok',
                                             handler: function(){
                                                  Ext.Ajax.request({  
                                                       waitMsg: 'Please Wait',
                                                       url: 'service.php?action=intervalUploadTemplate', 
                                                       params: {
                                                            ids: ids,
                                                            date: Ext.getCmp('interval-date').getValue(),
                                                            time: Ext.getCmp('interval-time').getValue(),
                                                            minute: Ext.getCmp('interval-minute').getValue()
                                                       }, 
                                                       success: function(response){
                                                            //console.log(response);
                                                            var result = eval(response.responseText);
                                                            //console.log(result);
                                                            if(result[0].success){
                                                                 //template_store.reload();
                                                                 intervalUploadWindow.close();
                                                                 Ext.MessageBox.alert('Success', result[0].msg);
                                                            }else{
                                                                 Ext.MessageBox.alert('Warning', result[0].msg);
                                                            }
                                                       },
                                                       failure: function(response){
                                                           var result=response.responseText;
                                                           Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                       }
                                                  });
                                             }
                                        },{
                                             text: 'Close',
                                             handler: function(){
                                                  intervalUploadWindow.close();
                                             }
                                        }]
                                        
                         })
                         
                         intervalUploadWindow.show();
                         return 1;
                    }
          }],
          bbar: new Ext.PagingToolbar({
              pageSize: parseInt(getCookie("pagination")),
              store: template_status_store,
              displayInfo: true
          })
     })
     
     function template_status_tab(status){
          switch(status){
               case 0:
                    var status_name = "New";
                    Ext.getCmp('template-status-combo').getStore().loadData([[1,'Submit Approve']]);
               break;
          
               case 1:
                    var status_name = "Waiting For Approve";
                    Ext.getCmp('template-status-combo').getStore().loadData([[2,'Approve'], [4,'Not Approve']]);
               break;
          
               case 2:
                    var status_name = "Active";
                    if(!Ext.isEmpty(Ext.getCmp('template-status-combo'))){
                         Ext.getCmp('template-status-combo').getStore().loadData([[5,'Freeze SKU'], [7,'Forever Listing']]);
                    }
               break;
          
               case 3:
                    var status_name = "Out Of Stock";
               break;
          
               case 4:
                    var status_name = "Under Review";
                    Ext.getCmp('template-status-combo').getStore().loadData([[1, "Re Approve"], [5, "Freeze SKU"]]);
               break;
          
               case 5:
                    var status_name = "Inactive";
                    Ext.getCmp('template-status-combo').getStore().loadData([[4,'Reactivation SKU'], [6,'Forever Inactive']]);
               break;
          
               case 6:
                    var status_name = "Forever Inactive";
                    Ext.getCmp('template-status-combo').getStore().loadData([]);
               break;
          
               case 7:
                    var status_name = "Forever Listing";
                    Ext.getCmp('template-status-combo').getStore().loadData([[5,'Freeze SKU']]);
               break;
          }
          if(Ext.getCmp('template-status-tab')){
               template_status_store.baseParams = {'status': status};
               template_status_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
               tabPanel.activate('template-status-tab');
               tabPanel.getActiveTab().setTitle("Template Under <font color='red'>"+status_name+"</font> Status");
          }else{
               template_status_store.baseParams = {'status': status};
               template_status_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
               tabPanel.add({
                    id:'template-status-tab',
                    iconCls: 'template-status',
                    title: "Template Under <font color='red'>"+status_name+"</font> Status",
                    autoScroll: true,
                    items: template_status_grid,
                    //closable: true,
                    autoScroll:true
               })
               tabPanel.doLayout();
               tabPanel.activate('template-status-tab');
          }
     }
     
     /*
     template_grid.on("rowdblclick", function(oGrid){
          var oRecord = oGrid.getSelectionModel().getSelected();
          //console.log(oRecord);
          window.open(path + "template.php?id="+oRecord.data['Id'],"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
     })
     */                             
     var template_category_tree = new Ext.tree.TreePanel({
          useArrows:true,
          autoScroll:true,
          animate:true,
          enableDD: true,
          //height: 500,
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
                    Ext.getCmp("template_category_name").setValue(n.text.slice(0, n.text.indexOf('(')));
                    //console.log(n);
                    template_store.baseParams = {
                         parent_id: n.id
                    };
                    template_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
               },
               movenode: function(tree, node, oldParent, newParent, index){
                    //console.log([node, newParent]);
                    //console.log([node.id, newParent.id]);
                    Ext.Ajax.request({  
                          waitMsg: 'Please Wait',
                          url: 'service.php?action=moveTemplateCateogry', 
                          params: { 
                              id: node.id,
                              newParent: newParent.id
                          }, 
                          success: function(response){
                              var result=eval(response.responseText);
                              switch(result){
                                 case 1:  // Success : simply reload
                                   //template_category_tree.root.reload();
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
          }
     })
     
     var template_category_form = new Ext.FormPanel({
          title: 'Manage Categories',
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
                    width: 50,
                    text: 'Add',
                    handler: function(){
                         if(Ext.isEmpty(template_category_tree.getSelectionModel().getSelectedNode())){
                              Ext.MessageBox.alert('Warning','Please select a template category as parent category.');
                         }else{
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
                    }
          },{
               width: 50,
               text: 'modify',
               handler: function(){
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=modifyTemplateCateogry', 
                         params: { 
                              templateCateogryId: template_category_tree.getSelectionModel().getSelectedNode().id ,
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
               width: 50,
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
     
     var activity_store = new Ext.data.JsonStore({
          root: 'records',
          totalProperty: 'totalCount',
          idProperty: 'id',
          //autoLoad:true,
          fields: ['Id', 'TemplateID', 'SKU', 'ItemID', 'Title', 'Site', 'ListingType', 'Quantity', 'ListingDuration', 'Price', 'StartTime', 'EndTime', 'ViewItemURL'],
          sortInfo: {
               field: 'Id',
               direction: 'ASC'
          },
          remoteSort: true,
          //url: 'service.php?action=getWait'
          url: 'service.php?action=getActiveItem'
     })
     
     var activity_grid_editor = new Ext.ux.grid.RowEditor({
          saveText: 'Update',
          listeners: {
               afteredit : function(a, b, c, d){
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=updateFields', 
                         params: { 
                              id: c.data.Id,
                              table: 'items',
                              Title: b.Title,
                              Quantity: b.Quantity,
                              Price: b.Price
                         }, 
                         success: function(response){
                             var result = eval(response.responseText);
                             if(result[0].success){
                                   Ext.MessageBox.alert('Success', result[0].msg);
                              }else{
                                   Ext.MessageBox.alert('Failure', result[0].msg);
                              }
                         },
                         failure: function(response){
                             var result=response.responseText;
                             Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                         }
                    });
               }
          }
     })
     
     var activity_grid = new Ext.grid.GridPanel({
          //title: 'Waiting To Upload SKU List',
          store: activity_store,
          plugins: [activity_grid_editor],
          //autoHeight: true,
          width: 1024,
          //autoScroll: true,
          //width: 600,
          height: 460,
          selModel: new Ext.grid.RowSelectionModel({}),
          columns:[
               {header: "ID", width: 60, align: 'center', sortable: true, dataIndex: 'Id'},
               {header: "TID", width: 60, align: 'center', sortable: true, dataIndex: 'TemplateID'},
               {header: "Site", width: 30, align: 'center', sortable: true, dataIndex: 'Site', renderer: renderFlag},
               {header: "SKU", width: 80, align: 'center', sortable: true, dataIndex: 'SKU'},
               {header: "Item ID", width: 80, align: 'center', sortable: true, dataIndex: 'ItemID'},
               {header: "Item Title", width: 235, align: 'center', sortable: true, dataIndex: 'Title', 
                    editor: {
                         xtype: 'textfield',
                         allowBlank: false
                    }
               },
               {header: "Format", width: 80, align: 'center', sortable: true, dataIndex: 'ListingType'},
               {header: "Qty", width: 40, align: 'center', sortable: true, dataIndex: 'Quantity', 
                    editor: {
                         xtype: 'numberfield',
                         allowBlank: false
                    }
               },
               {header: "Duration", width: 60, align: 'center', sortable: true, dataIndex: 'ListingDuration'},
               {header: "Price", width: 50, align: 'center', sortable: true, dataIndex: 'Price', 
                    editor: {
                         xtype: 'numberfield',
                         allowBlank: false
                    }
               },
               {header: "Start Time", width: 120, align: 'center', sortable: true, dataIndex: 'StartTime'},
               {header: "End Time", width: 120, align: 'center', sortable: true, dataIndex: 'EndTime'}
          ],
          //clicksToEdit: 1,
          tbar:[{
               text: 'Search',
               icon: './images/magnifier.png',
               handler: function(){
                    var  searchWindow = new Ext.Window({
                              title: 'Active Listing Search' ,
                              closable:true,
                              width: 300,
                              height: 180,
                              plain:true,
                              layout: 'form',
                              items: [/*{
                                        id:'interval-date',
                                        fieldLabel:'Date',
                                        xtype:'datefield',
                                        format:'Y-m-d',
                                        minValue: new Date(),
                                        selectOnFocus:true
                                   },*/{
                                        id:'SKU',
                                        fieldLabel:'SKU',
                                        xtype:'textfield'
                                   },{
                                        id:'ItemID',
                                        fieldLabel:'Item ID',
                                        xtype:'textfield'
                                   },{
                                        id:'Title',
                                        fieldLabel:'Item Title',
                                        xtype:'textfield'
                                   },{
                                        id:'ListingDuration',
                                        fieldLabel:'Duration',
                                        xtype:"combo",
                                        store:['', 'Days_3', 'Days_5', 'Days_7', 'Days_10', 'Days_30', 'Days_60', 'Days_90'],
                                        triggerAction: 'all',
                                        editable: false,
                                        selectOnFocus:true,
                                        listWidth:100,
                                        width:100
                                   }
                              ],
                              buttons: [{
                                             text: 'Submit',
                                             handler: function(){
                                                  activity_store.baseParams = {
                                                       SKU: Ext.getCmp("SKU").getValue(),
                                                       ItemID: Ext.getCmp("ItemID").getValue(),
                                                       Title: Ext.getCmp("Title").getValue(),
                                                       ListingDuration: Ext.getCmp("ListingDuration").getValue()
                                                  };
                                                  activity_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                                                  searchWindow.close();
                                             }
                                        },{
                                             text: 'Close',
                                             handler: function(){
                                                  searchWindow.close();
                                             }
                                        }]
                                        
                         })
                         
                         searchWindow.show();
               }
          },'-',{
               text: "Revise",
               icon: "./images/building_edit.png",
               handler: function(){
                    var selections = activity_grid.selModel.getSelections();
                    if(activity_grid.selModel.getCount() == 0){
                         Ext.MessageBox.alert('Warning','Please select the item you want to revise.');
                         return 0;
                    }
                    var ids = "";
                    for(var i = 0; i< activity_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    if(activity_grid.selModel.getCount() > 1){
                         window.open(path + "mitem.php?id="+ids+"&Status=3","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                    }else{
                         window.open(path + "item.php?id="+ids+"&Status=3","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                    }     
                    return 1;
               }
          },'-',{
               text: "Batch Revise",
               icon: "./images/building_edit.png",
               handler: function(){
                    var selections = activity_grid.selModel.getSelections();
                    /*
                    if(activity_grid.selModel.getCount() <= 1){
                         Ext.MessageBox.alert('Warning','Please select the mulit items.');
                         return 0;
                    }
                    */
                    var ids = "";
                    for(var i = 0; i< activity_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    
                    
                    var  batchReviseWindow = new Ext.Window({
                         title: 'Batch Revise Online Items(' + ids + ')' ,
                         closable:true,
                         width: 450,
                         height: 230,
                         plain:true,
                         layout: 'form',
                         items: [{
                              id: 'batch_price',
                              fieldLabel: 'Price',
                              xtype: 'numberfield',
                              name: 'price'
                         },{
                              id: 'batch_title',
                              fieldLabel: 'Title',
                              xtype: 'textfield',
                              name: 'title',
                              maxLength: 55,
                              width: 250
                         },{
                              id: 'batch_gallery_image',
                              fieldLabel: 'Gallery Image',
                              xtype: 'textfield',
                              name: 'image',
                              width: 250
                         },{
                              id: 'batch_product_image',
                              fieldLabel: 'Product Image',
                              xtype: 'textfield',
                              name: 'image',
                              width: 250
                         }],                                           
                         buttons: [{
                                        text: 'Batch Revise',
                                        handler: function(){
                                             Ext.Ajax.request({  
                                                  waitMsg: 'Please Wait',
                                                  url: 'service.php?action=batchReviseItem', 
                                                  params: {
                                                       ids: ids,
                                                       batch_price: Ext.getCmp('batch_price').getValue(),
                                                       batch_title: Ext.getCmp('batch_title').getValue(),
                                                       batch_gallery_image: Ext.getCmp('batch_gallery_image').getValue(),
                                                       batch_product_image: Ext.getCmp('batch_product_image').getValue()
                                                  }, 
                                                  success: function(response){
                                                       //console.log(response);
                                                       //alert(response.responseText);
                                                  
                                                       var result = eval(response.responseText);
                                                       if(result[0].success){
                                                            activity_store.reload();
                                                            Ext.MessageBox.alert('Success', result[0].msg);
                                                            batchReviseWindow.close();
                                                       }else{
                                                            Ext.MessageBox.alert('Warning', result[0].msg);
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
                    batchReviseWindow.show();
                    
                    return 1;
               }
          },'-',{
               text: 'Export Excel',
               icon: './images/table_go.png',
               tooltip:'export excel file',
               handler: function(){
                    /*
                    var selections = template_grid.selModel.getSelections();
                    var ids = "";
                    for(var i = 0; i< template_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    */
                    //console.log(ids);
                    window.open("service.php?action=activeItemExport","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=100, height=100");
               }
          },'-',{
               text: "Import Excel Update",     
               icon: './images/table_lightning.png',
               tooltip:'Import Excel update active listings title,description,price',
               handler: function(){
                    var  importExcelWindow = new Ext.Window({
                         title: 'Import Excel File' ,
                         closable:true,
                         width: 360,
                         height: 180,
                         plain:true,
                         layout: 'fit',
                         items: [{
                              xtype:'form',
                              id:'excel-form',
                              fileUpload: true,
                              frame: true,
                              autoHeight: true,
                              bodyStyle: 'padding: 10px 10px 0 10px;',
                              labelWidth: 80,
                              defaults: {
                                  anchor: '95%'
                                  //allowBlank: false
                              },
                              items:[{
                                   title:"Add TO Upload",
                                   xtype:"fieldset",
                                   items:[{
                                        xtype: 'fileuploadfield',
                                        id: 'stcsv',
                                        emptyText: 'Select an excel file',
                                        fieldLabel: 'File',
                                        //hideLabel:true,
                                        name: 'alexcel',
                                        buttonText: '',
                                        buttonCfg: {
                                            iconCls: 'upload-icon'
                                        }
                                   },{
                                        xtype: 'button',
                                        text: 'Upload',
                                        handler: function(){
                                             var fp = Ext.getCmp("excel-form");
                                             if(fp.getForm().isValid()){
                                                  fp.getForm().submit({
                                                       url: 'service.php?action=activeItemImport',
                                                       waitMsg: 'Uploading Your Excel...',
                                                       success: function(fp, o){
                                                            importExcelWindow.close();
                                                            Ext.MessageBox.alert('Success','update active listings success!');
                                                       }
                                                  });
                                             }
                                        }
                                   }]
                              }]
                         }],                                           
                         buttons: [{
                                        text: 'Close',
                                        handler: function(){
                                             importExcelWindow.close();
                                        }
                                   }]
                                   
                    })
                    importExcelWindow.show();   
               }
          },'-',{
               text: "View Online",     
               icon: './images/table_link.png',
               tooltip:'View Online Item',
               handler: function(){
                    var selections = activity_grid.selModel.getSelections();
                    if(activity_grid.selModel.getCount() == 0){
                         Ext.MessageBox.alert('Warning','View online item, please first select the item.');
                         return 0;
                    }
                    window.open(selections[0].data.ViewItemURL,"_blank");
                    return 1;
               }
          },'-',{
               text: "Sell Similar",     
               icon: './images/table_relationship.png',
               tooltip:'Sell Similar Item',
               handler: function(){
                    var selections = activity_grid.selModel.getSelections();
                    if(activity_grid.selModel.getCount() == 0){
                         Ext.MessageBox.alert('Warning','Please select the item.');
                         return 0;
                    }
                    var ids = "";
                    for(var i = 0; i< activity_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=copyItem&type=wait', 
                         params: { 
                              ids: ids
                         }, 
                         success: function(response){
                             var result=eval(response.responseText);
                             switch(result){
                                case 1:  // Success : simply reload
                                  //activity_store.reload();
                                  Ext.MessageBox.alert('Success','Submit sell similar item success.');
                                  break;
                                default:
                                  Ext.MessageBox.alert('Warning','Failure, please notice admin.');
                                  break;
                             }
                         },
                         failure: function(response){
                             var result=response.responseText;
                             Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                         }
                    });
                    
                    return 1;
               }
          },'-',{
               text: "End",     
               icon: './images/table_delete.png',
               tooltip:'End Item',
               handler: function(){
                    var selections = activity_grid.selModel.getSelections();
                    if(activity_grid.selModel.getCount() == 0){
                         Ext.MessageBox.alert('Warning','Please select the item.');
                         return 0;
                    }
                    var ids = "";
                    for(var i = 0; i< activity_grid.selModel.getCount(); i++){
                         ids += selections[i].data.Id + ","
                    }
                    ids = ids.slice(0,-1);
                    
                    Ext.Ajax.request({  
                         waitMsg: 'Please Wait',
                         url: 'service.php?action=stopListingItem', 
                         params: { 
                              ids: ids
                         }, 
                         success: function(response){
                             var result=eval(response.responseText);
                             switch(result){
                                case 1:  // Success : simply reload
                                   //Ext.MessageBox.alert('Success','End item success.');
                                   activity_store.reload();
                                  break;
                                default:
                                  Ext.MessageBox.alert('Warning','Failure, please notice admin.');
                                  break;
                             }
                         },
                         failure: function(response){
                             var result=response.responseText;
                             Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                         }
                    });
                    
                    return 1;
               }
          }],
          bbar: new Ext.PagingToolbar({
              pageSize: parseInt(getCookie("pagination")),
              store: activity_store,
              displayInfo: true
          })
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
                    {"text" : "Active Listings", "id" : 1, "icon": "./images/hourglass.png", "leaf" : true},
                    {"text" : "Ended Listings",  "id" : 2, "leaf" : false,
                              children: [
                                   {"text": "Sold", "id" : 21, "icon": "./images/money_add.png", "leaf" : true},
                                   {"text": "Unsold", "id" : 22, "icon": "./images/money_delete.png", "leaf" : true}
                              ]}
               ]
          },
          listeners:{
               click: function(n, e){
                    //console.log(n);
                    switch(n.id){
                         case 1:
                              activity_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                              tabPanel.add({
                                   id:'activity-tab',
                                   iconCls: 'active-item-tab',
                                   title: "Listing Activity",
                                   items: activity_grid,
                                   //closable: true,
                                   //height: 768,
                                   autoScroll:true
                              })
                              tabPanel.activate('activity-tab');
                              tabPanel.doLayout();
                              
                              /*
                              activity_store.load();
                              tabPanel.add({
                                   id:'activity-tab',
                                   iconCls: 'active-item-tab',
                                   title: "Listing Activity",
                                   items: activity_grid,
                                   //closable: true,
                                   //height: 768,
                                   autoScroll:true
                              })
                              
                              tabPanel.activate('activity-tab');
                              tabPanel.doLayout();
                              */
                         break;
                    
                         case 21:
                              var sold_item_store = new Ext.data.JsonStore({
                                   root: 'records',
                                   totalProperty: 'totalCount',
                                   idProperty: 'id',
                                   //autoLoad:true,
                                   fields: ['Id', 'TemplateID', 'SKU', 'ItemID', 'Title', 'Site', 'ListingType', 'Quantity', 'QuantitySold', 'ListingDuration', 'Price', 'EndTime', 'Relist'],
                                   sortInfo: {
                                        field: 'Id',
                                        direction: 'ASC'
                                   },
                                   remoteSort: true,
                                   url: 'service.php?action=getSoldItem'
                              })
                              
                              var sold_item_grid = new Ext.grid.GridPanel({
                                   //title: 'Waiting To Upload SKU List',
                                   store: sold_item_store,
                                   //autoHeight: true,
                                   //autoScroll: true,
                                   width: 1024,
                                   height: 460,
                                   selModel: new Ext.grid.RowSelectionModel({}),
                                   columns:[
                                        {header: "ID", width: 50, align: 'center', sortable: true, dataIndex: 'Id'},
                                        {header: "TID", width: 50, align: 'center', sortable: true, dataIndex: 'TemplateID'},
                                        {header: "Site", width: 40, align: 'center', sortable: true, dataIndex: 'Site', renderer: renderFlag},
                                        {header: "SKU", width: 80, align: 'center', sortable: true, dataIndex: 'SKU'},
                                        {header: "Item ID", width: 80, align: 'center', sortable: true, dataIndex: 'ItemID'},
                                        {header: "Item Title", width: 250, align: 'center', sortable: true, dataIndex: 'Title'},
                                        {header: "Format", width: 100, align: 'center', sortable: true, dataIndex: 'ListingType'},
                                        {header: "Qty", width: 40, align: 'center', sortable: true, dataIndex: 'Quantity'},
                                        {header: "SQty", width: 40, align: 'center', sortable: true, dataIndex: 'QuantitySold'},
                                        {header: "Duration", width: 60, align: 'center', sortable: true, dataIndex: 'ListingDuration'},
                                        {header: "Price", width: 60, align: 'center', sortable: true, dataIndex: 'Price'},
                                        {header: "End Time", width: 120, align: 'center', sortable: true, dataIndex: 'EndTime'},
                                        {header: "Relist", width: 40, align: 'center', sortable: true, dataIndex: 'Relist'}
                                   ],
                                   tbar:[{
                                        text: "Relist",
                                        icon: './images/arrow_redo.png',
                                        tooltip:'Relist Item To eBay',
                                        handler: function(){
                                             var selections = sold_item_grid.selModel.getSelections();
                                             if(sold_item_grid.selModel.getCount() == 0){
                                                  Ext.MessageBox.alert('Warning','Please select the item you want to relist.');
                                                  return 0;
                                             }
                                             var ids = "";
                                             for(var i = 0; i< sold_item_grid.selModel.getCount(); i++){
                                                  ids += selections[i].data.Id + ","
                                             }
                                             ids = ids.slice(0,-1);
                                             //console.log(ids);
                                             //return 0;
                                             /*
                                             if(sold_item_grid.selModel.getCount() > 1){
                                                  window.open(path + "mitem.php?id="+ids+"&Status=4","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                                             }else{
                                                  window.open(path + "item.php?id="+ids+"&Status=4","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                                             }
                                             */
                                             Ext.Ajax.request({  
                                                  waitMsg: 'Please Wait',
                                                  url: 'service.php?action=copyItem&type=relist', 
                                                  params: { 
                                                       ids: ids
                                                  }, 
                                                  success: function(response){
                                                      var result=eval(response.responseText);
                                                      switch(result){
                                                         case 1:  // Success : simply reload
                                                           sold_item_store.reload();
                                                           break;
                                                         default:
                                                           Ext.MessageBox.alert('Warning','Relist failure, please notice admin.');
                                                           break;
                                                      }
                                                  },
                                                  failure: function(response){
                                                      var result=response.responseText;
                                                      Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                  }
                                             });
                                             
                                             return 1;
                                        }
                                   },'-',{
                                        text: "Export CSV",     
                                        icon: './images/server_go.png',
                                        tooltip:'export csv file',
                                        handler: function(){
                                             var  searchWindow = new Ext.Window({
                                                  title: 'Export Sold Item' ,
                                                  closable:true,
                                                  width: 250,
                                                  height: 120,
                                                  plain:true,
                                                  layout: 'form',
                                                  items: [{
                                                            id:'StartTime',
                                                            fieldLabel:'Start Date',
                                                            format:'Y-m-d',
                                                            allowBlank:false,
                                                            xtype:'datefield'
                                                       },{
                                                            id:'EndTime',
                                                            fieldLabel:'End Date',
                                                            format:'Y-m-d',
                                                            allowBlank:false,
                                                            xtype:'datefield'
                                                       }
                                                  ],
                                                  buttons: [{
                                                                 text: 'Submit',
                                                                 handler: function(){
                                                                      window.open("service.php?action=soldItemExport&StartTime="+Ext.getCmp("StartTime").getValue().format("Y-m-d")+"&EndTime="+Ext.getCmp("EndTime").getValue().format("Y-m-d"),"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=100, height=100");
                                                                      searchWindow.close();
                                                                 }
                                                            },{
                                                                 text: 'Close',
                                                                 handler: function(){
                                                                      searchWindow.close();
                                                                 }
                                                            }]
                                                            
                                             })
                                             
                                             searchWindow.show();
                                        }
                                   }],
                                   bbar: new Ext.PagingToolbar({
                                       pageSize: parseInt(getCookie("pagination")),
                                       store: sold_item_store,
                                       displayInfo: true
                                   })
                              })
                              
                              if(tabPanel.isVisible('sold-item-tab'))
                                   tabPanel.remove('sold-item-tab');

                              sold_item_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                              tabPanel.add({
                                   id:'sold-item-tab',
                                   iconCls: 'sold-item-tab',
                                   title: "Sold Item",
                                   items: sold_item_grid,
                                   closable: true,
                                   autoScroll:true
                              })
                              
                              tabPanel.activate('sold-item-tab');
                              tabPanel.doLayout();
                         break;
                    
                         case 22:
                              var unsold_item_store = new Ext.data.JsonStore({
                                   root: 'records',
                                   totalProperty: 'totalCount',
                                   idProperty: 'id',
                                   //autoLoad:true,
                                   fields: ['Id', 'TemplateID', 'SKU', 'ItemID', 'Title', 'Site', 'ListingType', 'Quantity', 'ListingDuration', 'Price', 'EndTime'],
                                   sortInfo: {
                                        field: 'Id',
                                        direction: 'ASC'
                                   },
                                   remoteSort: true,
                                   url: 'service.php?action=getUnSoldItem'
                              })
                              
                              var unsold_item_grid = new Ext.grid.GridPanel({
                                   //title: 'Waiting To Upload SKU List',
                                   store: unsold_item_store,
                                   //autoHeight: true,
                                   //autoScroll: true,
                                   width: 1024,
                                   height: 460,
                                   selModel: new Ext.grid.RowSelectionModel({}),
                                   columns:[
                                        {header: "ID", width: 50, align: 'center', sortable: true, dataIndex: 'Id'},
                                        {header: "TID", width: 50, align: 'center', sortable: true, dataIndex: 'TemplateID'},
                                        {header: "Site", width: 40, align: 'center', sortable: true, dataIndex: 'Site', renderer: renderFlag},
                                        {header: "SKU", width: 100, align: 'center', sortable: true, dataIndex: 'SKU'},
                                        {header: "Item ID", width: 120, align: 'center', sortable: true, dataIndex: 'ItemID'},
                                        {header: "Item Title", width: 200, align: 'center', sortable: true, dataIndex: 'Title'},
                                        {header: "Format", width: 100, align: 'center', sortable: true, dataIndex: 'ListingType'},
                                        {header: "Qty", width: 50, align: 'center', sortable: true, dataIndex: 'Quantity'},
                                        {header: "Duration", width: 60, align: 'center', sortable: true, dataIndex: 'ListingDuration'},
                                        {header: "Price", width: 60, align: 'center', sortable: true, dataIndex: 'Price'},
                                        {header: "End Time", width: 120, align: 'center', sortable: true, dataIndex: 'EndTime'}
                                   ],
                                   tbar:[{
                                        text: "Export CSV",     
                                        icon: './images/server_go.png',
                                        tooltip:'export csv file',
                                        handler: function(){
                                             var  searchWindow = new Ext.Window({
                                                  title: 'Export unSold Item' ,
                                                  closable:true,
                                                  width: 250,
                                                  height: 120,
                                                  plain:true,
                                                  layout: 'form',
                                                  items: [{
                                                            id:'StartTime',
                                                            fieldLabel:'Start Date',
                                                            format:'Y-m-d',
                                                            allowBlank:false,
                                                            xtype:'datefield'
                                                       },{
                                                            id:'EndTime',
                                                            fieldLabel:'End Date',
                                                            format:'Y-m-d',
                                                            allowBlank:false,
                                                            xtype:'datefield'
                                                       }
                                                  ],
                                                  buttons: [{
                                                                 text: 'Submit',
                                                                 handler: function(){
                                                                      window.open("service.php?action=unSoldItemExport&StartTime="+Ext.getCmp("StartTime").getValue().format("Y-m-d")+"&EndTime="+Ext.getCmp("EndTime").getValue().format("Y-m-d"),"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=100, height=100");
                                                                      searchWindow.close();
                                                                 }
                                                            },{
                                                                 text: 'Close',
                                                                 handler: function(){
                                                                      searchWindow.close();
                                                                 }
                                                            }]
                                                            
                                             })
                                             
                                             searchWindow.show();
                                        }
                                   }],
                                   bbar: new Ext.PagingToolbar({
                                       pageSize: parseInt(getCookie("pagination")),
                                       store: unsold_item_store,
                                       displayInfo: true
                                   })
                              })
                              
                              if(tabPanel.isVisible('unsold-item-tab'))
                                   tabPanel.remove('unsold-item-tab');

                              unsold_item_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                              tabPanel.add({
                                   id:'unsold-item-tab',
                                   iconCls: 'unsold-item-tab',
                                   title: "UnSold Item",
                                   items: unsold_item_grid,
                                   closable: true,
                                   autoScroll:true
                              })
                              
                              tabPanel.activate('unsold-item-tab');
                              tabPanel.doLayout();
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
                   html: '<div style="background-image: url(images/logo.bmp) !important;background-repeat:no-repeat;width:100%;height:100%">',
                   items:[{
                         xtype:"button",
                         text:"Log Out",
                         style:"float:left;margin-right:5px",
                         icon:'images/door_out.png',
                         handler: function(){
                              Ext.Ajax.request({
                                   url: 'service.php?action=logout',
                                   success: function(o){
                                        window.location = "/eBayListing/login.html";
                                   },
                                   failure: function(){
                                          alert('Lost connection to server.');
                                   }
                              });
                         }
                    },{
                         xtype:"button",
                         text: getCookie("account_name"),
                         style:"float:left;margin-left:5px",
                         icon:'images/user.png',
                         handler: function(){
                              var  userWindow = new Ext.Window({
                                   title: 'Account Info' ,
                                   closable:true,
                                   width: 300,
                                   height: 220,
                                   plain:true,
                                   layout: 'form',
                                   items: [{
                                             id:'mPassword',
                                             fieldLabel:'Admin Password',
                                             xtype:'textfield',
                                             listeners: {
                                                  blur: function(t){
                                                       //console.log(t.getValue());
                                                       Ext.Ajax.request({  
                                                            waitMsg: 'Please Wait',
                                                            url: 'service.php?action=getPayPalEmailAndPagination', 
                                                            params: {
                                                                 mPassword: Ext.getCmp('mPassword').getValue()
                                                            }, 
                                                            success: function(response){
                                                                 //console.log(response);
                                                                 var result = eval(response.responseText);
                                                                 //console.log(result);
                                                                 if(result[0].success){
                                                                      Ext.getCmp("PayPalEmailAddress").setValue(result[0].paypalEmail);
                                                                      Ext.getCmp("pagination").setValue(result[0].pagination);
                                                                 }else{
                                                                      Ext.MessageBox.alert('Warning', result[0].msg);
                                                                 }
                                                            },
                                                            failure: function(response){
                                                                var result=response.responseText;
                                                                Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                            }
                                                       });
                                                  }
                                             }
                                        },{
                                             id:'oPassword',
                                             fieldLabel:'Old Password',
                                             xtype:'textfield'
                                        },{
                                             id:'nPassword',
                                             fieldLabel:'New Password',
                                             xtype:'textfield'
                                        },{
                                             id:'pagination',
                                             fieldLabel:'Pagination Size',
                                             xtype:'numberfield'
                                        },{
                                             id:'PayPalEmailAddress',
                                             fieldLabel:'PayPal Email',
                                             readOnly:true,
                                             xtype:'textfield'
                                        }
                                   ],
                                   buttons: [{
                                                  text: 'Ok',
                                                  handler: function(){
                                                       Ext.Ajax.request({  
                                                            waitMsg: 'Please Wait',
                                                            url: 'service.php?action=updateAccountInfo', 
                                                            params: {
                                                                 name: getCookie("account_name"),
                                                                 oPassword: Ext.getCmp('oPassword').getValue(),
                                                                 nPassword: Ext.getCmp('nPassword').getValue(),
                                                                 mPassword: Ext.getCmp('mPassword').getValue(),
                                                                 pagination: Ext.getCmp('pagination').getValue(),
                                                                 PayPalEmailAddress: Ext.getCmp('PayPalEmailAddress').getValue()
                                                            }, 
                                                            success: function(response){
                                                                 //console.log(response);
                                                                 var result = eval(response.responseText);
                                                                 //console.log(result);
                                                                 if(result[0].success){
                                                                      userWindow.close();
                                                                      Ext.MessageBox.alert('Success', result[0].msg);
                                                                 }else{
                                                                      Ext.MessageBox.alert('Warning', result[0].msg);
                                                                 }
                                                            },
                                                            failure: function(response){
                                                                var result=response.responseText;
                                                                Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                            }
                                                       });
                                                  }
                                             },{
                                                  text: 'Close',
                                                  handler: function(){
                                                       userWindow.close();
                                                  }
                                             }]
                                             
                              })
                              userWindow.show();
                         }
                    }/*,{
                         xtype:"button",
                         text:"Ebay Account Manage",
                         style:"float:left;margin-right:5px"
                    },{
                         xtype:"button",
                         text:"Ebay Account Manage",
                         style:"float:left;margin-right:5px"
                    }*/],
                   //el: 'north',
                   height:32
               },{
                    region:'south',
                    id:'log-watch',
                    //contentEl: 'south',
                    split:true,
                    height: 100,
                    autoScroll: true,
                    minSize: 100,
                    maxSize: 150,
                    collapsible: true,
                    collapsed: true,
                    title:'Log',
                    //html:'test',
                    margins:'0 0 0 0'
               },{
                    region:'west',
                    id:'west-panel',
                    title:'Function Palette',
                    split:true,
                    width: 180,
                    minSize: 160,
                    maxSize: 300,
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
                         title:'Template Category',
                         border:false,
                         autoScroll:true,
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
                                        //template_store.load();
                                        template_store.baseParams = {
                                             parent_id: 0
                                        };
                                        template_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                                        
                                        tabPanel.add({
                                             id:'template-tab',
                                             iconCls: 'template',
                                             title: "Template",
                                             autoScroll: true,
                                             items: template_grid,
                                             //closable: true,
                                             autoScroll:true
                                        })
                                        tabPanel.doLayout();
                                        tabPanel.activate('template-tab');
                                   }
                              }
                         }
                    },{
                         id:'template-status',
                         title:'Template Status',
                         border:false,
                         autoScroll:true,
                         iconCls:'template-status',
                         layout:'vbox',
                         items: [{
                              xtype:'button',
                              text: 'NEW',
                              width:160,
                              handler: function(){
                                   template_status_tab(0);
                              }
                         },{
                              xtype:'button',
                              text: 'Waiting For Approve',
                              width:160,
                              handler: function(){
                                   template_status_tab(1);
                              }
                         },{
                              xtype:'button',
                              text: 'Active',
                              width:160,
                              handler: function(){
                                   template_status_tab(2);
                              }
                         },{
                              xtype:'button',
                              text: 'Out Of Stock',
                              width:160,
                              handler: function(){
                                  template_status_tab(3);
                              }
                         },{
                              xtype:'button',
                              text: 'Under Review',
                              width:160,
                              handler: function(){
                                   template_status_tab(4);
                              }
                         },{
                              xtype:'button',
                              text: 'Inactive',
                              width:160,
                              handler: function(){
                                   template_status_tab(5);
                              }
                         },{
                              xtype:'button',
                              text: 'Forever Inactive',
                              width:160,
                              handler: function(){
                                   template_status_tab(6);
                              }
                         },{
                              xtype:'button',
                              text: '<font color="red">Forever Listing</font>',
                              width:160,
                              handler: function(){
                                   template_status_tab(7);
                              }
                         }],
                         listeners:{
                              expand: function(p){
                                   template_status_tab(2);
                              }
                         }
                    },{
                         id:'waiting-to',
                         title:'Waiting To ...',
                         border:false,
                         iconCls:'waiting-to',
                         items: [{
                              xtype:'button',
                              text:'Upload',
                              icon:'images/arrow_up.png',
                              width: 160,
                              handler: function(){
                                   var wait_search =new Ext.FormPanel({
                                        width: 1040,
                                        title: 'Search',
                                        buttonAlign: 'left',
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
                                                       wait_store.baseParams = {
                                                            SKU: wait_search.getForm().findField("SKU").getValue(),
                                                            Title: wait_search.getForm().findField("Title").getValue()
                                                       };
                                                       wait_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                                                  }
                                        }]
                                   })
                              
                                   var wait_store =new Ext.data.JsonStore({
                                        root: 'records',
                                        totalProperty: 'totalCount',
                                        idProperty: 'id',
                                        //autoLoad:true,
                                        fields: ['Id', 'TemplateID', 'Site', 'SKU', 'Title', 'Price', 'ShippingFee', 'Quantity', 'ListingDuration', 'ListingType', 'ScheduleTime', 'ScheduleLocalTime'],
                                        sortInfo: {
                                             field: 'Id',
                                             direction: 'ASC'
                                        },
                                        remoteSort: true,
                                        url: 'service.php?action=getWaitingUploadItem',
                                        listeners: {
                                             load: function(t, r){
                                                  Ext.getCmp('waiting-to').setTitle('Waiting To Upload ('+t.totalLength+')');
                                             }
                                        }
                                   })
            
                                   var wait_grid_editor = new Ext.ux.grid.RowEditor({
                                        saveText: 'Update',
                                        listeners: {
                                             afteredit : function(a, b, c, d){
                                                  Ext.Ajax.request({  
                                                       waitMsg: 'Please Wait',
                                                       url: 'service.php?action=updateFields', 
                                                       params: { 
                                                            id: c.data.Id,
                                                            table: 'items',
                                                            Title: b.Title,
                                                            Quantity: b.Quantity,
                                                            Price: b.Price
                                                       }, 
                                                       success: function(response){
                                                           var result = eval(response.responseText);
                                                           if(result[0].success){
                                                                 Ext.MessageBox.alert('Success', result[0].msg);
                                                            }else{
                                                                 Ext.MessageBox.alert('Failure', result[0].msg);
                                                            }
                                                       },
                                                       failure: function(response){
                                                           var result=response.responseText;
                                                           Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                       }
                                                  });
                                             }
                                        }
                                   })
            
                                   var wait_grid = new Ext.grid.EditorGridPanel({
                                        title: 'Waiting To Upload List',
                                        store: wait_store,
                                        //plugins: [wait_grid_editor],
                                        //autoHeight: true,
                                        width: 1024,
                                        height: 460,
                                        selModel: new Ext.grid.RowSelectionModel({}),
                                        columns:[
                                             {header: "ID", width: 60, align: 'center', sortable: true, dataIndex: 'Id'},
                                             {header: "TID", width: 60, align: 'center', sortable: true, dataIndex: 'TemplateID'},
                                             {header: "Site", width: 30, align: 'center', sortable: true, dataIndex: 'Site', renderer: renderFlag},
                                             {header: "Sku", width: 80, align: 'center', sortable: true, dataIndex: 'SKU'},
                                             {header: "Title", width: 290, align: 'center', sortable: true, dataIndex: 'Title'/*,
                                                  editor: {
                                                       xtype: 'textfield',
                                                       allowBlank: false
                                                  }*/
                                             },
                                             {header: "ListingType", width: 70, align: 'center', sortable: true, dataIndex: 'ListingType'},
                                             {header: "Price", width: 50, align: 'center', sortable: true, dataIndex: 'Price'},
                                             {header: "Shipping Fee", width: 70, align: 'center', sortable: true, dataIndex: 'ShippingFee'},
                                             {header: "Qty", width: 30, align: 'center', sortable: true, dataIndex: 'Quantity'},
                                             {header: "Duration", width: 60, align: 'center', sortable: true, dataIndex: 'ListingDuration'},
                                             {header: "BeiJing Upload Time", width: 108, align: 'center', sortable: true, dataIndex: 'ScheduleTime'},
                                             {header: "Local Upload Time", width: 108, align: 'center', sortable: true, dataIndex: 'ScheduleLocalTime'}
                                        ],
                                        tbar: [/*{
                                                  text:'Copy',
                                                  icon: './images/page_copy.png',
                                                  tooltip:'Copy before uploading',
                                                  handler: function(){
                                                       var selections = wait_grid.selModel.getSelections();
                                                       if(wait_grid.selModel.getCount() == 0){
                                                            Ext.MessageBox.alert('Warning','Please select the you want to copy.');
                                                            return 0;
                                                       }
                                                       var ids = "";
                                                       for(var i = 0; i< wait_grid.selModel.getCount(); i++){
                                                            ids += selections[i].data.Id + ","
                                                       }
                                                       ids = ids.slice(0,-1);
                                                       Ext.Ajax.request({  
                                                            waitMsg: 'Please Wait',
                                                            url: 'service.php?action=copyItem&type=wait', 
                                                            params: { 
                                                                 ids: ids
                                                            }, 
                                                            success: function(response){
                                                                var result=eval(response.responseText);
                                                                switch(result){
                                                                   case 1:  // Success : simply reload
                                                                     wait_store.reload();
                                                                     break;
                                                                   default:
                                                                     Ext.MessageBox.alert('Warning','Copy failure, please notice admin.');
                                                                     break;
                                                                }
                                                            },
                                                            failure: function(response){
                                                                var result=response.responseText;
                                                                Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                            }
                                                       });
                                                       return 1;
                                                  }
                                             },'-',*/{
                                                       text: 'Select All',
                                                       tooltip:'Selects all rows',
                                                       handler: function(){
                                                            wait_grid.selModel.selectAll();
                                                       }
                                                  },'-',{
                                                       text: 'Clear Select',
                                                       tooltip:'Clears all selections',
                                                       handler: function(){
                                                            wait_grid.selModel.clearSelections();
                                                       }
                                                  },'-',{
                                                       text: 'Search',
                                                       icon: './images/magnifier.png',
                                                       handler: function(){
                                                            var  searchWindow = new Ext.Window({
                                                                      title: 'Search Waiting Item' ,
                                                                      closable:true,
                                                                      width: 300,
                                                                      height: 180,
                                                                      plain:true,
                                                                      layout: 'form',
                                                                      items: [/*{
                                                                                id:'interval-date',
                                                                                fieldLabel:'Date',
                                                                                xtype:'datefield',
                                                                                format:'Y-m-d',
                                                                                minValue: new Date(),
                                                                                selectOnFocus:true
                                                                           },*/{
                                                                                id:'TID',
                                                                                fieldLabel:'TID',
                                                                                xtype:'numberfield'
                                                                           },{
                                                                                id:'SKU',
                                                                                fieldLabel:'SKU',
                                                                                xtype:'textfield'
                                                                           },{
                                                                                id:'Title',
                                                                                fieldLabel:'Item Title',
                                                                                xtype:'textfield'
                                                                           },{
                                                                                id:'ListingDuration',
                                                                                fieldLabel:'Duration',
                                                                                xtype:"combo",
                                                                                store:['', 'Days_3', 'Days_5', 'Days_7', 'Days_10', 'Days_30', 'Days_60', 'Days_90'],
                                                                                triggerAction: 'all',
                                                                                editable: false,
                                                                                selectOnFocus:true,
                                                                                listWidth:100,
                                                                                width:100
                                                                           }
                                                                      ],
                                                                      buttons: [{
                                                                                     text: 'Submit',
                                                                                     handler: function(){
                                                                                          wait_store.baseParams = {
                                                                                               TID: Ext.getCmp("TID").getValue(),
                                                                                               SKU: Ext.getCmp("SKU").getValue(),
                                                                                               Title: Ext.getCmp("Title").getValue(),
                                                                                               ListingDuration: Ext.getCmp("ListingDuration").getValue()
                                                                                          };
                                                                                          wait_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                                                                                          searchWindow.close();
                                                                                     }
                                                                                },{
                                                                                     text: 'Close',
                                                                                     handler: function(){
                                                                                          searchWindow.close();
                                                                                     }
                                                                                }]
                                                                                
                                                                 })
                                                                 
                                                                 searchWindow.show();
                                                       }
                                                  },'-',{
                                                  text:'Edit',
                                                  icon: './images/page_edit.png',
                                                  tooltip:'Editing before uploading',
                                                  handler:function(){
                                                       var selections = wait_grid.selModel.getSelections();
                                                       if(wait_grid.selModel.getCount() == 0){
                                                            Ext.MessageBox.alert('Warning','Please select the template you want to edit.');
                                                            return 0;
                                                       }
                                                       var ids = "";
                                                       for(var i = 0; i< wait_grid.selModel.getCount(); i++){
                                                            ids += selections[i].data.Id + ",";
                                                       }
                                                       ids = ids.slice(0,-1);
                                                       if(wait_grid.selModel.getCount() > 1){
                                                            window.open(path + "mitem.php?id="+ids,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768"); 
                                                       }else{
                                                            window.open(path + "item.php?id="+ids,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768"); 
                                                       }
                                                       return 1;
                                                  }
                                             },'-',{
                                                  text:'Delete',
                                                  icon: './images/page_delete.png',
                                                  tooltip:'Delete before uploading',
                                                  handler:function(){
                                                       var selections = wait_grid.selModel.getSelections();
                                                       if(wait_grid.selModel.getCount() == 0){
                                                            Ext.MessageBox.alert('Warning','Please select the need to delete.');
                                                            return 0;
                                                       }
                                                       var ids = "";
                                                       for(var i = 0; i< wait_grid.selModel.getCount(); i++){
                                                            ids += selections[i].data.Id + ","
                                                       }
                                                       ids = ids.slice(0,-1);
                                                       
                                                       Ext.Msg.confirm('Confirm', 'Delete waiting to upload item ' + ids, function(a, b, c){
                                                            if (a == 'yes'){
                                   
                                                                 Ext.Ajax.request({  
                                                                      waitMsg: 'Please Wait',
                                                                      url: 'service.php?action=waitUploadItemDelete', 
                                                                      params: { 
                                                                             ids: ids
                                                                      }, 
                                                                      success: function(response){
                                                                          var result=eval(response.responseText);
                                                                          switch(result){
                                                                             case 1:  // Success : simply reload
                                                                               wait_store.reload();
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
                                                       })
                                                       return 1;
                                                  }
                                             },'-',{
                                             text:'Reset Time',
                                             icon: './images/clock_edit.png',
                                             tooltip:'Reset upload time',
                                             handler: function(){
                                                  var selections = wait_grid.selModel.getSelections();
                                                  if(wait_grid.selModel.getCount() == 0){
                                                       Ext.MessageBox.alert('Warning','Please select the need to reset.');
                                                       return 0;
                                                  }
                                                  var ids = "";
                                                  for(var i = 0; i< wait_grid.selModel.getCount(); i++){
                                                       ids += selections[i].data.Id + ","
                                                  }
                                                  ids = ids.slice(0,-1);
                                                  
                                                  var  resetTimeWindow = new Ext.Window({
                                                       title: 'Reset Upload Time' ,
                                                       closable:true,
                                                       width: 300,
                                                       height: 180,
                                                       plain:true,
                                                       layout: 'form',
                                                       items: [{
                                                                 id:'interval-date',
                                                                 fieldLabel:'Date',
                                                                 xtype:'datefield',
                                                                 format:'Y-m-d',
                                                                 minValue: new Date(),
                                                                 selectOnFocus:true
                                                            },{
                                                                 id:'interval-time',
                                                                 fieldLabel:'Time',
                                                                 xtype:'timefield',
                                                                 increment:1,
                                                                 triggerAction: 'all',
                                                                 editable: false,
                                                                 selectOnFocus:true,
                                                                 listWidth:80,
                                                                 width:80  
                                                            },{
                                                                 id:'interval-minute',
                                                                 fieldLabel:'Interval',
                                                                 xtype:"combo",
                                                                 store:[0,1,2,3,4,5,6,7,8,9,10],
                                                                 listWidth:60,
                                                                 width:60
                                                            }
                                                       ],
                                                       buttons: [{
                                                                      text: 'Ok',
                                                                      handler: function(){
                                                                           Ext.Ajax.request({  
                                                                                waitMsg: 'Please Wait',
                                                                                url: 'service.php?action=updateItemUploadTime', 
                                                                                params: {
                                                                                     ids: ids,
                                                                                     date: Ext.getCmp('interval-date').getValue(),
                                                                                     time: Ext.getCmp('interval-time').getValue(),
                                                                                     minute: Ext.getCmp('interval-minute').getValue()
                                                                                }, 
                                                                                success: function(response){
                                                                                     //console.log(response);
                                                                                     var result = eval(response.responseText);
                                                                                     //console.log(result);
                                                                                     if(result[0].success){
                                                                                          wait_store.reload();
                                                                                          resetTimeWindow.close();
                                                                                          Ext.MessageBox.alert('Success', result[0].msg);
                                                                                     }else{
                                                                                          Ext.MessageBox.alert('Warning', result[0].msg);
                                                                                     }
                                                                                },
                                                                                failure: function(response){
                                                                                    var result=response.responseText;
                                                                                    Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                                                }
                                                                           });
                                                                      }
                                                                 },{
                                                                      text: 'Close',
                                                                      handler: function(){
                                                                           resetTimeWindow.close();
                                                                      }
                                                                 }]
                                                                 
                                                  })
                                                  
                                                  resetTimeWindow.show();
                                                  return 1;
                                             }
                                        },'-',{
                                             text:'Add To Schedule',
                                             icon: './images/time.png',
                                             tooltip:'add to schedule list',
                                             handler: function(){
                                                  var selections = wait_grid.selModel.getSelections();
                                                  if(wait_grid.selModel.getCount() == 0){
                                                       Ext.MessageBox.alert('Warning','Please select the you want to schedule.');
                                                       return 0;
                                                  }
                                                  var ids = "";
                                                  for(var i = 0; i< wait_grid.selModel.getCount(); i++){
                                                       ids += selections[i].data.Id + ","
                                                  }
                                                  ids = ids.slice(0,-1);
                                                  Ext.Ajax.request({  
                                                       waitMsg: 'Please Wait',
                                                       url: 'service.php?action=addItemToSchedule', 
                                                       params: { 
                                                            ids: ids
                                                       }, 
                                                       success: function(response){
                                                           var result=eval(response.responseText);
                                                           switch(result){
                                                              case 1:  // Success : simply reload
                                                                wait_store.reload();
                                                                break;
                                                              default:
                                                                Ext.MessageBox.alert('Warning','failure, please notice admin.');
                                                                break;
                                                           }
                                                       },
                                                       failure: function(response){
                                                           var result=response.responseText;
                                                           Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                       }
                                                  });
                                                  return 1;
                                             }
                                        }],
                                        bbar: new Ext.PagingToolbar({
                                            pageSize: parseInt(getCookie("pagination")),
                                            store: wait_store,
                                            displayInfo: true
                                        })
                                   })
                                   
                                   if(tabPanel.isVisible('waiting-to-upload-tab'))
                                        tabPanel.remove('waiting-to-upload-tab');
                                   
                                   wait_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
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
                             
                              }
                         },{
                              xtype:'button',
                              text:'Relist',
                              icon:'images/arrow_refresh.png',
                              width: 160,
                              handler: function(){
                                   var wait_relist_store =new Ext.data.JsonStore({
                                        root: 'records',
                                        totalProperty: 'totalCount',
                                        idProperty: 'id',
                                        //autoLoad:true,
                                        fields: ['Id', 'TemplateID', 'Site', 'SKU', 'Title', 'Price', 'ShippingFee', 'Quantity', 'ListingDuration', 'ListingType'],
                                        sortInfo: {
                                             field: 'Id',
                                             direction: 'ASC'
                                        },
                                        remoteSort: true,
                                        url: 'service.php?action=getWaitingRelistItem',
                                        listeners: {
                                             load: function(t, r){
                                                  Ext.getCmp('waiting-to').setTitle('Waiting To Relist ('+t.totalLength+')');
                                             }
                                        }
                                   })
                                   
                                   var wait_relist_grid = new Ext.grid.GridPanel({
                                        title: 'Waiting To Relist List',
                                        store: wait_relist_store,
                                        //autoHeight: true,
                                        width: 1024,
                                        height: 460,
                                        selModel: new Ext.grid.RowSelectionModel({}),
                                        columns:[
                                             {header: "ID", width: 60, align: 'center', sortable: true, dataIndex: 'Id'},
                                             {header: "TID", width: 60, align: 'center', sortable: true, dataIndex: 'TemplateID'},
                                             {header: "Site", width: 30, align: 'center', sortable: true, dataIndex: 'Site', renderer: renderFlag},
                                             {header: "Sku", width: 80, align: 'center', sortable: true, dataIndex: 'SKU'},
                                             {header: "Title", width: 400, align: 'center', sortable: true, dataIndex: 'Title'},
                                             {header: "ListingType", width: 70, align: 'center', sortable: true, dataIndex: 'ListingType'},
                                             {header: "Price", width: 50, align: 'center', sortable: true, dataIndex: 'Price'},
                                             {header: "Shipping Fee", width: 70, align: 'center', sortable: true, dataIndex: 'ShippingFee'},
                                             {header: "Qty", width: 30, align: 'center', sortable: true, dataIndex: 'Quantity'},
                                             {header: "Duration", width: 60, align: 'center', sortable: true, dataIndex: 'ListingDuration'}
                                        ],
                                        tbar: [{
                                             text: 'Select All',
                                             tooltip:'Selects all rows',
                                             handler: function(){
                                                  wait_relist_grid.selModel.selectAll();
                                             }
                                        },'-',{
                                             text: 'Clear Select',
                                             tooltip:'Clears all selections',
                                             handler: function(){
                                                  wait_relist_grid.selModel.clearSelections();
                                             }
                                        },'-',{
                                             text: "Edit and Relist",
                                             icon: './images/page_edit.png',
                                             tooltip:'Edit Item',
                                             handler: function(){
                                                  var selections = wait_relist_grid.selModel.getSelections();
                                                  if(wait_relist_grid.selModel.getCount() == 0){
                                                       Ext.MessageBox.alert('Warning','Please select the item you want to relist.');
                                                       return 0;
                                                  }
                                                  var ids = "";
                                                  for(var i = 0; i< wait_relist_grid.selModel.getCount(); i++){
                                                       ids += selections[i].data.Id + ","
                                                  }
                                                  ids = ids.slice(0,-1);
                                                  //console.log(ids);
                                                  //return 0;
                                                  
                                                  if(wait_relist_grid.selModel.getCount() > 1){
                                                       //window.open(path + "mitem.php?id="+ids+"&Status=4","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                                                  }else{
                                                       window.open(path + "item.php?id="+ids+"&Status=4","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                                                  }
                                                  return 1;
                                             }
                                        },'-',{
                                             text: 'Relist',
                                             icon: 'images/arrow_refresh.png',
                                             handler: function(){
                                                  var selections = wait_relist_grid.selModel.getSelections();
                                                  if(wait_relist_grid.selModel.getCount() == 0){
                                                       Ext.MessageBox.alert('Warning','Please select the you want to relist.');
                                                       return 0;
                                                  }
                                                  var ids = "";
                                                  for(var i = 0; i< wait_relist_grid.selModel.getCount(); i++){
                                                       ids += selections[i].data.Id + ","
                                                  }
                                                  ids = ids.slice(0,-1);
                                                  Ext.Ajax.request({  
                                                       waitMsg: 'Please Wait',
                                                       url: 'service.php?action=addItemToRelist', 
                                                       params: { 
                                                            ids: ids
                                                       }, 
                                                       success: function(response){
                                                           var result=eval(response.responseText);
                                                           switch(result){
                                                              case 1:  // Success : simply reload
                                                                wait_relist_store.reload();
                                                                break;
                                                              default:
                                                                Ext.MessageBox.alert('Warning','failure, please notice admin.');
                                                                break;
                                                           }
                                                       },
                                                       failure: function(response){
                                                           var result=response.responseText;
                                                           Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                       }
                                                  });
                                                  return 1;
                                             }
                                        },'-',{
                                             text:'Delete',
                                             icon: 'images/cancel.png',
                                             handler: function(){
                                                  var selections = wait_relist_grid.selModel.getSelections();
                                                  if(wait_relist_grid.selModel.getCount() == 0){
                                                       Ext.MessageBox.alert('Warning','Please select template.');
                                                       return 0;
                                                  }
                                                  var ids = "";
                                                  for(var i = 0; i< wait_relist_grid.selModel.getCount(); i++){
                                                       ids += selections[i].data.Id + ","
                                                  }
                                                  ids = ids.slice(0,-1);
                                                  //console.log(ids);
                                                  Ext.Msg.confirm('Confirm', 'Delete ' + ids, function(a, b, c){
                                                       if (a == 'yes'){
                                                            Ext.Ajax.request({  
                                                                 waitMsg: 'Please Wait',
                                                                 url: 'service.php?action=deleteItem', 
                                                                 params: { 
                                                                        ids: ids
                                                                 }, 
                                                                 success: function(response){
                                                                     var result=eval(response.responseText);
                                                                     switch(result){
                                                                        case 1:  // Success : simply reload
                                                                          wait_relist_store.reload();
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
                                                  });
                                                  return 1;
                                             }
                                        }],
                                        bbar: new Ext.PagingToolbar({
                                            pageSize: parseInt(getCookie("pagination")),
                                            store: wait_relist_store,
                                            displayInfo: true
                                        })
                                   });
                                   
                                   if(tabPanel.isVisible('waiting-to-relist-tab'))
                                        tabPanel.remove('waiting-to-relist-tab');
                                   
                                   wait_relist_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                                   tabPanel.add({
                                        id:'waiting-to-relist-tab',
                                        iconCls: 'waiting-to-relist',
                                        title: "Waiting To Relist",
                                        items: wait_relist_grid,
                                        closable: true,
                                        autoScroll:true
                                   })
                                   tabPanel.doLayout();
                                   tabPanel.activate('waiting-to-relist-tab');
                              }
                         }]
                   },{
                         id:'schedule',
                         title:'Schedule',
                         html:'xxx',
                         border:false,
                         iconCls:'schedule',
                         listeners:{
                              expand: function(p){
                                   var schedule_search =new Ext.FormPanel({
                                        width: 1040,
                                        title: 'Search',
                                        buttonAlign: 'left',
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
                                                       schedule_store.baseParams = {
                                                            SKU:   schedule_search.getForm().findField("SKU").getValue(),
                                                            Title: schedule_search.getForm().findField("Title").getValue()
                                                       };
                                                       schedule_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                                                  }
                                        }]
                                   })
                                   
                                   var schedule_store = new Ext.data.JsonStore({
                                        root: 'records',
                                        totalProperty: 'totalCount',
                                        idProperty: 'id',
                                        //autoLoad:true,
                                        fields: ['Id', 'TemplateID', 'Site', 'SKU', 'Title', 'Price', 'ShippingFee', 'Quantity', 'ListingDuration', 'ListingType', 'ScheduleTime', 'ScheduleLocalTime'],
                                        sortInfo: {
                                             field: 'Id',
                                             direction: 'ASC'
                                        },
                                        remoteSort: true,
                                        url: 'service.php?action=geScheduleItem',
                                        listeners: {
                                             load: function(t, r){
                                                  Ext.getCmp('schedule').setTitle('Schedule ('+t.totalLength+')');
                                             }
                                        }
                                   })
                 
                                   var schedule_grid_editor = new Ext.ux.grid.RowEditor({
                                        saveText: 'Update',
                                        listeners: {
                                             afteredit : function(a, b, c, d){
                                                  Ext.Ajax.request({  
                                                       waitMsg: 'Please Wait',
                                                       url: 'service.php?action=updateFields', 
                                                       params: { 
                                                            id: c.data.Id,
                                                            table: 'items',
                                                            Title: b.Title,
                                                            Quantity: b.Quantity,
                                                            Price: b.Price
                                                       }, 
                                                       success: function(response){
                                                           var result = eval(response.responseText);
                                                           if(result[0].success){
                                                                 Ext.MessageBox.alert('Success', result[0].msg);
                                                            }else{
                                                                 Ext.MessageBox.alert('Failure', result[0].msg);
                                                            }
                                                       },
                                                       failure: function(response){
                                                           var result=response.responseText;
                                                           Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                       }
                                                  });
                                             }
                                        }
                                   })
                                   
                                   var schedule_grid = new Ext.grid.EditorGridPanel({
                                        title: 'Schedule List',
                                        store: schedule_store,
                                        //plugins: [schedule_grid_editor],
                                        //autoHeight: true,
                                        width: 1024,
                                        height: 460,
                                        selModel: new Ext.grid.RowSelectionModel({}),
                                        columns:[
                                             {header: "ID", width: 60, align: 'center', sortable: true, dataIndex: 'Id'},
                                             {header: "TID", width: 60, align: 'center', sortable: true, dataIndex: 'TemplateID'},
                                             {header: "Site", width: 30, align: 'center', sortable: true, dataIndex: 'Site', renderer: renderFlag},
                                             {header: "Sku", width: 70, align: 'center', sortable: true, dataIndex: 'SKU'},
                                             {header: "Title", width: 280, align: 'center', sortable: true, dataIndex: 'Title', 
                                                  editor: {
                                                  xtype: 'textfield',
                                                  allowBlank: false
                                                  }
                                             },
                                             {header: "ListingType", width: 70, align: 'center', sortable: true, dataIndex: 'ListingType'},
                                             {header: "Price", width: 50, align: 'center', sortable: true, dataIndex: 'Price'},
                                             {header: "Shipping Fee", width: 70, align: 'center', sortable: true, dataIndex: 'ShippingFee'},
                                             {header: "Qty", width: 30, align: 'center', sortable: true, dataIndex: 'Quantity'},
                                             {header: "Duration", width: 60, align: 'center', sortable: true, dataIndex: 'ListingDuration'},
                                             {header: "BeiJing Upload Time", width: 108, align: 'center', sortable: true, dataIndex: 'ScheduleTime'},
                                             {header: "Local Upload Time", width: 108, align: 'center', sortable: true, dataIndex: 'ScheduleLocalTime'}
                                        ],
                                        tbar: [/*{
                                                  text:'Copy',
                                                  icon: './images/page_copy.png',
                                                  tooltip:'Copy before uploading',
                                                  handler: function(){
                                                       var selections = schedule_grid.selModel.getSelections();
                                                       if(schedule_grid.selModel.getCount() == 0){
                                                            Ext.MessageBox.alert('Warning','Please select the you want to copy.');
                                                            return 0;
                                                       }
                                                       var ids = "";
                                                       for(var i = 0; i< schedule_grid.selModel.getCount(); i++){
                                                            ids += selections[i].data.Id + ","
                                                       }
                                                       ids = ids.slice(0,-1);
                                                       Ext.Ajax.request({  
                                                            waitMsg: 'Please Wait',
                                                            url: 'service.php?action=copyItem&type=schedule', 
                                                            params: { 
                                                                 ids: ids
                                                            }, 
                                                            success: function(response){
                                                                var result=eval(response.responseText);
                                                                switch(result){
                                                                   case 1:  // Success : simply reload
                                                                     schedule_store.reload();
                                                                     break;
                                                                   default:
                                                                     Ext.MessageBox.alert('Warning','Copy failure, please notice admin.');
                                                                     break;
                                                                }
                                                            },
                                                            failure: function(response){
                                                                var result=response.responseText;
                                                                Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                            }
                                                       });
                                                       return 1;
                                                  }
                                             },'-',*/{
                                                  text: 'Select All',
                                                  tooltip:'Selects all rows',
                                                  handler: function(){
                                                       schedule_grid.selModel.selectAll();
                                                  }
                                             },'-',{
                                                  text: 'Clear Select',
                                                  tooltip:'Clears all selections',
                                                  handler: function(){
                                                       schedule_grid.selModel.clearSelections();
                                                  }
                                             },'-',{
                                                  text: 'Search',
                                                  icon: './images/magnifier.png',
                                                  handler: function(){
                                                       var  searchWindow = new Ext.Window({
                                                                 title: 'Search Schedule Item' ,
                                                                 closable:true,
                                                                 width: 300,
                                                                 height: 180,
                                                                 plain:true,
                                                                 layout: 'form',
                                                                 items: [/*{
                                                                           id:'interval-date',
                                                                           fieldLabel:'Date',
                                                                           xtype:'datefield',
                                                                           format:'Y-m-d',
                                                                           minValue: new Date(),
                                                                           selectOnFocus:true
                                                                      },*/{
                                                                           id:'TID',
                                                                           fieldLabel:'TID',
                                                                           xtype:'numberfield'
                                                                      },{
                                                                           id:'SKU',
                                                                           fieldLabel:'SKU',
                                                                           xtype:'textfield'
                                                                      },{
                                                                           id:'Title',
                                                                           fieldLabel:'Item Title',
                                                                           xtype:'textfield'
                                                                      },{
                                                                           id:'ListingDuration',
                                                                           fieldLabel:'Duration',
                                                                           xtype:"combo",
                                                                           store:['', 'Days_3', 'Days_5', 'Days_7', 'Days_10', 'Days_30', 'Days_60', 'Days_90'],
                                                                           triggerAction: 'all',
                                                                           editable: false,
                                                                           selectOnFocus:true,
                                                                           listWidth:100,
                                                                           width:100
                                                                      }
                                                                 ],
                                                                 buttons: [{
                                                                                text: 'Submit',
                                                                                handler: function(){
                                                                                     schedule_store.baseParams = {
                                                                                          TID: Ext.getCmp("TID").getValue(),
                                                                                          SKU: Ext.getCmp("SKU").getValue(),
                                                                                          Title: Ext.getCmp("Title").getValue(),
                                                                                          ListingDuration: Ext.getCmp("ListingDuration").getValue()
                                                                                     };
                                                                                     schedule_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                                                                                     searchWindow.close();
                                                                                }
                                                                           },{
                                                                                text: 'Close',
                                                                                handler: function(){
                                                                                     searchWindow.close();
                                                                                }
                                                                           }]
                                                                           
                                                            })
                                                            
                                                            searchWindow.show();
                                                  }
                                             }/*,'-',{
                                                  text:'Edit',
                                                  icon: './images/page_edit.png',
                                                  tooltip:'Editing before uploading',
                                                  handler:function(){
                                                       var selections = schedule_grid.selModel.getSelections();
                                                       if(schedule_grid.selModel.getCount() == 0){
                                                            Ext.MessageBox.alert('Warning','Please select the template you want to edit.');
                                                            return 0;
                                                       }
                                                       var ids = "";
                                                       for(var i = 0; i< schedule_grid.selModel.getCount(); i++){
                                                            ids += selections[i].data.Id + ",";
                                                       }
                                                       ids = ids.slice(0,-1);
                                                       if(schedule_grid.selModel.getCount() > 1){
                                                            window.open(path + "mitem.php?id="+ids,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768"); 
                                                       }else{
                                                            window.open(path + "item.php?id="+ids,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768"); 
                                                       }
                                                       return 1;
                                                  }
                                             }*/,'-',{
                                                  text:'Delete',
                                                  icon: './images/page_delete.png',
                                                  tooltip:'Delete before uploading',
                                                  handler:function(){
                                                       var selections = schedule_grid.selModel.getSelections();
                                                       if(schedule_grid.selModel.getCount() == 0){
                                                            Ext.MessageBox.alert('Warning','Please select the need to delete.');
                                                            return 0;
                                                       }
                                                       var ids = "";
                                                       for(var i = 0; i< schedule_grid.selModel.getCount(); i++){
                                                            ids += selections[i].data.Id + ","
                                                       }
                                                       ids = ids.slice(0,-1);
                                                       
                                                       Ext.Msg.confirm('Confirm', 'Delete waiting to upload item ' + ids, function(a, b, c){
                                                            if (a == 'yes'){
                                   
                                                                 Ext.Ajax.request({  
                                                                      waitMsg: 'Please Wait',
                                                                      url: 'service.php?action=waitUploadItemDelete', 
                                                                      params: { 
                                                                             ids: ids
                                                                      }, 
                                                                      success: function(response){
                                                                          var result=eval(response.responseText);
                                                                          switch(result){
                                                                             case 1:  // Success : simply reload
                                                                               schedule_store.reload();
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
                                                       })
                                                       return 1;
                                                  }
                                             },'-',{
                                             text:'Reset Time',
                                             icon: './images/clock_edit.png',
                                             tooltip:'Reset upload time',
                                             handler: function(){
                                                  var selections = schedule_grid.selModel.getSelections();
                                                  if(schedule_grid.selModel.getCount() == 0){
                                                       Ext.MessageBox.alert('Warning','Please select the need to reset.');
                                                       return 0;
                                                  }
                                                  var ids = "";
                                                  for(var i = 0; i< schedule_grid.selModel.getCount(); i++){
                                                       ids += selections[i].data.Id + ","
                                                  }
                                                  ids = ids.slice(0,-1);
                                                  
                                                  var  resetTimeWindow = new Ext.Window({
                                                       title: 'Reset Upload Time' ,
                                                       closable:true,
                                                       width: 300,
                                                       height: 180,
                                                       plain:true,
                                                       layout: 'form',
                                                       items: [{
                                                                 id:'interval-date',
                                                                 fieldLabel:'Date',
                                                                 xtype:'datefield',
                                                                 format:'Y-m-d',
                                                                 minValue: new Date(),
                                                                 selectOnFocus:true
                                                            },{
                                                                 id:'interval-time',
                                                                 fieldLabel:'Time',
                                                                 xtype:'timefield',
                                                                 increment:1,
                                                                 triggerAction: 'all',
                                                                 editable: false,
                                                                 selectOnFocus:true,
                                                                 listWidth:80,
                                                                 width:80  
                                                            },{
                                                                 id:'interval-minute',
                                                                 fieldLabel:'Interval',
                                                                 xtype:"combo",
                                                                 store:[0,1,2,3,4,5,6,7,8,9,10],
                                                                 listWidth:60,
                                                                 width:60
                                                            }
                                                       ],
                                                       buttons: [{
                                                                      text: 'Ok',
                                                                      handler: function(){
                                                                           Ext.Ajax.request({  
                                                                                waitMsg: 'Please Wait',
                                                                                url: 'service.php?action=updateItemUploadTime', 
                                                                                params: {
                                                                                     ids: ids,
                                                                                     date: Ext.getCmp('interval-date').getValue(),
                                                                                     time: Ext.getCmp('interval-time').getValue(),
                                                                                     minute: Ext.getCmp('interval-minute').getValue()
                                                                                }, 
                                                                                success: function(response){
                                                                                     //console.log(response);
                                                                                     var result = eval(response.responseText);
                                                                                     //console.log(result);
                                                                                     if(result[0].success){
                                                                                          schedule_store.reload();
                                                                                          resetTimeWindow.close();
                                                                                          Ext.MessageBox.alert('Success', result[0].msg);
                                                                                     }else{
                                                                                          Ext.MessageBox.alert('Warning', result[0].msg);
                                                                                     }
                                                                                },
                                                                                failure: function(response){
                                                                                    var result=response.responseText;
                                                                                    Ext.MessageBox.alert('error','could not connect to the database. retry later');      
                                                                                }
                                                                           });
                                                                      }
                                                                 },{
                                                                      text: 'Close',
                                                                      handler: function(){
                                                                           resetTimeWindow.close();
                                                                      }
                                                                 }]
                                                                 
                                                  })
                                                  
                                                  resetTimeWindow.show();
                                                  return 1;
                                             }
                                        }],
                                        bbar: new Ext.PagingToolbar({
                                            pageSize: parseInt(getCookie("pagination")),
                                            store: schedule_store,
                                            displayInfo: true
                                        })
                                   })
                                   
                                   if(tabPanel.isVisible('schedule-tab'))
                                        tabPanel.remove('schedule-tab');
                                        
                                   //if(waitOpen == true){
                                        //tabPanel.activate('waiting-to-upload-tab');
                                   //}else{
                                        schedule_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                                        tabPanel.add({
                                             id:'schedule-tab',
                                             iconCls: 'schedule',
                                             title: "Schedule",
                                             items: schedule_grid,
                                             closable: true,
                                             autoScroll:true
                                        })
                                        tabPanel.doLayout();
                                        tabPanel.activate('schedule-tab');
                                   //}   
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
                    },/*{
                         title:'Processing',
                         //items: processing,
                         border:false,
                         iconCls:'processing',
                         listeners:{
                              expand: function(p){
                                   
                                   
                              }
                         }
                    },*/{
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
                                             fields: ['id', 'name', 'password', 'accountLocation', 'token', 'tokenExpiry', 'status'],
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
                                                            fieldLabel:'Location',
                                                            xtype:"combo",
                                                            store:['US', 'UK', 'Australia', 'Germany'],
                                                            triggerAction: 'all',
                                                            editable: false,
                                                            selectOnFocus:true,
                                                            listWidth:100,
                                                            width:100,
                                                            name:"accountLocation"
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
                                                                 accountLocation: ebayManageForm.form.findField('accountLocation').getValue(),
                                                                 token: ebayManageForm.form.findField('token').getValue(),
                                                                 tokenExpiry: ebayManageForm.form.findField('tokenExpiry').getValue(),
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
                              }/*,{
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
                              }*/,{
                                   text: 'Switch Account',
                                   icon: 'images/group_link.png',
                                   handler: function(){
                                        var x = new Ext.form.ComboBox({
                                             fieldLabel:"Account",
                                             mode: 'local',
                                             store: new Ext.data.JsonStore({
                                                 autoLoad: true,
                                                 fields: ['id', 'name'],
                                                 url: "service.php?action=getAllAccount"
                                             }),
                                             valueField:'id',
                                             displayField:'name',
                                             triggerAction: 'all',
                                             editable: false,
                                             selectOnFocus:true,
                                             name: 'account_id',
                                             hiddenName:'account_id'
                                        })
                              
                                        var  switchAccountWindow = new Ext.Window({
                                             title: 'Switch Account' ,
                                             closable:true,
                                             width: 320,
                                             height: 150,
                                             plain:true,
                                             layout: 'fit',
                                             items: [{
                                                  xtype:'form',
                                                  id:'aie-form',
                                                  fileUpload: true,
                                                  frame: true,
                                                  autoHeight: true,
                                                  bodyStyle: 'padding: 10px 10px 0 10px;',
                                                  labelWidth: 80,
                                                  defaults: {
                                                      anchor: '95%'
                                                      //allowBlank: false
                                                  },
                                                  items:[x,{
                                                       xtype: 'button',
                                                       text: 'Switch',
                                                       handler: function(){
                                                            Ext.Ajax.request({
                                                                 url: 'service.php?action=switchAccount',
                                                                 params: { 
                                                                      id: x.getValue()
                                                                 }, 
                                                                 success: function(a, b, c){
                                                                      //console.log([a, b, c]);
                                                                      switchAccountWindow.close();
                                                                 }
                                                            });
                                                       }
                                                  }]
                                             }],                                           
                                             buttons: [{
                                                            text: 'Close',
                                                            handler: function(){
                                                                 switchAccountWindow.close();
                                                            }
                                                       }]
                                                       
                                        })
                                        switchAccountWindow.show();   
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
                                   text: 'Template Log',
                                   icon: 'images/plugin.png',
                                   handler: function(){
                                        //console.log("test");
                                        var template_store = new Ext.data.JsonStore({
                                             root: 'records',
                                             totalProperty: 'totalCount',
                                             idProperty: 'id',
                                             //autoLoad:true,
                                             fields: ['id', 'level', 'content', 'time'],
                                             url: 'service.php?action=getUploadLog&type=template'
                                        })
                                        //console.log("test1");
                                        var template_grid = new Ext.grid.GridPanel({
                                             title: 'Template Log',
                                             store: template_store,
                                             autoHeight: true,
                                             selModel: new Ext.grid.RowSelectionModel({}),
                                             columns:[
                                                  {header: "Level", width: 80, align: 'center', sortable: true, dataIndex: 'level'},
                                                  {header: "Content", width: 800, align: 'center', sortable: true, dataIndex: 'content'},
                                                  {header: "Time", width: 110, align: 'center', sortable: true, dataIndex: 'time'}
                                             ],
                                             bbar: new Ext.PagingToolbar({
                                                 pageSize: parseInt(getCookie("pagination")),
                                                 store: template_store,
                                                 displayInfo: true
                                             })
                                        })
                                        //console.log(tabPanel);
                                        if(tabPanel.isVisible('template-log-tab'))
                                             tabPanel.remove('template-log-tab');
                                             
     
                                        template_store.load();
                                        tabPanel.add({
                                             id:'template-log-tab',
                                             iconCls: 'template-log',
                                             title: "Template Log",
                                             items: template_grid,
                                             closable: true,
                                             autoScroll:true
                                        })
                                        tabPanel.doLayout();
                                        tabPanel.activate('template-log-tab');
                                   }
                              },{
                                   text: 'Item   Log',
                                   icon: 'images/table.png',
                                   handler: function(){
                                        //console.log("test");
                                        var item_store = new Ext.data.JsonStore({
                                             root: 'records',
                                             totalProperty: 'totalCount',
                                             idProperty: 'id',
                                             //autoLoad:true,
                                             fields: ['id', 'level', 'content', 'time'],
                                             url: 'service.php?action=getUploadLog&type=item'
                                        })
                                        //console.log("test1");
                                        var item_grid = new Ext.grid.GridPanel({
                                             title: 'Item Log',
                                             store: item_store,
                                             autoHeight: true,
                                             selModel: new Ext.grid.RowSelectionModel({}),
                                             columns:[
                                                  {header: "Level", width: 80, align: 'center', sortable: true, dataIndex: 'level'},
                                                  {header: "Content", width: 800, align: 'center', sortable: true, dataIndex: 'content'},
                                                  {header: "Time", width: 110, align: 'center', sortable: true, dataIndex: 'time'}
                                             ],
                                             bbar: new Ext.PagingToolbar({
                                                 pageSize: parseInt(getCookie("pagination")),
                                                 store: item_store,
                                                 displayInfo: true
                                             })
                                        })
                                        //console.log(tabPanel);
                                        if(tabPanel.isVisible('item-log-tab'))
                                             tabPanel.remove('item-log-tab');
                                             
     
                                        item_store.load();
                                        tabPanel.add({
                                             id:'item-log-tab',
                                             iconCls: 'item-log',
                                             title: "Item Log",
                                             items: item_grid,
                                             closable: true,
                                             autoScroll:true
                                        })
                                        tabPanel.doLayout();
                                        tabPanel.activate('item-log-tab');
                                   }
                              },{
                                   text: 'Upload Log',
                                   icon: 'images/table_go.png',
                                   handler: function(){
                                        //console.log("test");
                                        var upload_store = new Ext.data.JsonStore({
                                             root: 'records',
                                             totalProperty: 'totalCount',
                                             idProperty: 'id',
                                             //autoLoad:true,
                                             fields: ['id', 'account', 'level', 'content', 'time'],
                                             url: 'service.php?action=getUploadLog&type=upload'
                                        })
                                        //console.log("test1");
                                        var upload_grid = new Ext.grid.GridPanel({
                                             title: 'Upload Log',
                                             store: upload_store,
                                             autoHeight: true,
                                             selModel: new Ext.grid.RowSelectionModel({}),
                                             columns:[
                                                  {header: "Account", width: 80, align: 'center', sortable: true, dataIndex: 'account'},
                                                  {header: "Level", width: 80, align: 'center', sortable: true, dataIndex: 'level'},
                                                  {header: "Content", width: 700, align: 'center', sortable: true, dataIndex: 'content'},
                                                  {header: "Time", width: 110, align: 'center', sortable: true, dataIndex: 'time'}
                                             ],
                                             tbar: [{
                                                  text: 'Search',
                                                  icon: './images/magnifier.png',
                                                  handler: function(){
                                                       var  searchWindow = new Ext.Window({
                                                                 title: 'Upload Item Search' ,
                                                                 closable:true,
                                                                 width: 300,
                                                                 height: 180,
                                                                 plain:true,
                                                                 layout: 'form',
                                                                 items: [{
                                                                           id:'id',
                                                                           fieldLabel:'ItemId',
                                                                           xtype:'textfield'
                                                                      },{
                                                                           id:'startDate',
                                                                           fieldLabel:'Start Date',
                                                                           xtype:'datefield',
                                                                           format:'Y-m-d',
                                                                           selectOnFocus:true
                                                                      },{
                                                                           id:'endDate',
                                                                           fieldLabel:'End Date',
                                                                           xtype:'datefield',
                                                                           format:'Y-m-d',
                                                                           selectOnFocus:true
                                                                      },{
                                                                           id:'level',
                                                                           fieldLabel:'Level',
                                                                           xtype:"combo",
                                                                           store:['', 'error', 'warn', 'normal'],
                                                                           triggerAction: 'all',
                                                                           editable: false,
                                                                           selectOnFocus:true,
                                                                           listWidth:100,
                                                                           width:100
                                                                      }
                                                                 ],
                                                                 buttons: [{
                                                                                text: 'Submit',
                                                                                handler: function(){
                                                                                     upload_store.baseParams = {
                                                                                          id: Ext.getCmp("id").getValue(),
                                                                                          startDate: Ext.getCmp("startDate").getValue().format("Y-m-d"),
                                                                                          endDate: Ext.getCmp("endDate").getValue().format("Y-m-d"),
                                                                                          level: Ext.getCmp("level").getValue()
                                                                                     };
                                                                                     upload_store.load({params:{start:0, limit:parseInt(getCookie("pagination"))}});
                                                                                     searchWindow.close();
                                                                                }
                                                                           },{
                                                                                text: 'Close',
                                                                                handler: function(){
                                                                                     searchWindow.close();
                                                                                }
                                                                           }]
                                                                           
                                                            })
                                                       searchWindow.show();
                                                  }
                                             }],
                                             bbar: new Ext.PagingToolbar({
                                                 pageSize: parseInt(getCookie("pagination")),
                                                 store: upload_store,
                                                 displayInfo: true
                                             })
                                        })
                                        //console.log(tabPanel);
                                        if(tabPanel.isVisible('upload-log-tab'))
                                             tabPanel.remove('upload-log-tab');
                                             
     
                                        upload_store.load();
                                        tabPanel.add({
                                             id:'upload-log-tab',
                                             iconCls: 'upload-log',
                                             title: "Upload Log",
                                             items: upload_grid,
                                             closable: true,
                                             autoScroll:true
                                        })
                                        tabPanel.doLayout();
                                        tabPanel.activate('upload-log-tab');
                                   }
                              },{
                                   text: 'Revise Log',
                                   icon: 'images/table_edit.png',
                                   handler: function(){
                                        //console.log("test");
                                        var revise_store = new Ext.data.JsonStore({
                                             root: 'records',
                                             totalProperty: 'totalCount',
                                             idProperty: 'id',
                                             //autoLoad:true,
                                             fields: ['id', 'level', 'content', 'time'],
                                             url: 'service.php?action=getUploadLog&type=revise'
                                        })
                                        //console.log("test1");
                                        var revise_grid = new Ext.grid.GridPanel({
                                             title: 'Upload Log',
                                             store: revise_store,
                                             autoHeight: true,
                                             selModel: new Ext.grid.RowSelectionModel({}),
                                             columns:[
                                                  {header: "Level", width: 80, align: 'center', sortable: true, dataIndex: 'level'},
                                                  {header: "Content", width: 800, align: 'center', sortable: true, dataIndex: 'content'},
                                                  {header: "Time", width: 110, align: 'center', sortable: true, dataIndex: 'time'}
                                             ],
                                             bbar: new Ext.PagingToolbar({
                                                 pageSize: parseInt(getCookie("pagination")),
                                                 store: revise_store,
                                                 displayInfo: true
                                             })
                                        })
                                        //console.log(tabPanel);
                                        if(tabPanel.isVisible('revise-log-tab'))
                                             tabPanel.remove('revise-log-tab');
                                             
     
                                        revise_store.load();
                                        tabPanel.add({
                                             id:'revise-log-tab',
                                             iconCls: 'revise-log',
                                             title: "Revise Log",
                                             items: revise_grid,
                                             closable: true,
                                             autoScroll:true
                                        })
                                        tabPanel.doLayout();
                                        tabPanel.activate('revise-log-tab');
                                   }
                              },{
                                   text: 'Relist Log',
                                   icon: 'images/table_refresh.png',
                                   handler: function(){
                                        //console.log("test");
                                        var relist_store = new Ext.data.JsonStore({
                                             root: 'records',
                                             totalProperty: 'totalCount',
                                             idProperty: 'id',
                                             //autoLoad:true,
                                             fields: ['id', 'level', 'content', 'time'],
                                             url: 'service.php?action=getUploadLog&type=relist'
                                        })
                                        //console.log("test1");
                                        var relist_grid = new Ext.grid.GridPanel({
                                             title: 'Relist Log',
                                             store: relist_store,
                                             autoHeight: true,
                                             selModel: new Ext.grid.RowSelectionModel({}),
                                             columns:[
                                                  {header: "Level", width: 80, align: 'center', sortable: true, dataIndex: 'level'},
                                                  {header: "Content", width: 800, align: 'center', sortable: true, dataIndex: 'content'},
                                                  {header: "Time", width: 110, align: 'center', sortable: true, dataIndex: 'time'}
                                             ],
                                             bbar: new Ext.PagingToolbar({
                                                 pageSize: parseInt(getCookie("pagination")),
                                                 store: relist_store,
                                                 displayInfo: true
                                             })
                                        })
                                        //console.log(tabPanel);
                                        if(tabPanel.isVisible('relist-log-tab'))
                                             tabPanel.remove('relist-log-tab');
                                             
     
                                        relist_store.load();
                                        tabPanel.add({
                                             id:'relist-log-tab',
                                             iconCls: 'revise-log',
                                             title: "Relist Log",
                                             items: relist_grid,
                                             closable: true,
                                             autoScroll:true
                                        })
                                        tabPanel.doLayout();
                                        tabPanel.activate('relist-log-tab');
                                   }
                              }]                      
                         },
                         border:false,
                         iconCls:'log',
                         listeners:{
                              expand: function(p){
                                   
                              }
                         }
                    }/*,{
                         title:'Sales Report',
                         border:false,
                         iconCls:'sales-report',
                         listeners:{
                              expand: function(p){
                                   Ext.chart.Chart.CHART_URL = '../../ext-3.0.0/resources/charts.swf';
                                   Ext.Ajax.request({
                                        url: 'service.php?action=skuSaleStatistics',
                                        success: function(a, b, c){
                                             //console.log([a, b, c]);
                                             eval(a.responseText);
                                             
                                             //console.log(chart);
                                             if(tabPanel.isVisible('sales-report'))
                                                  tabPanel.remove('sales-report');
                                                  
                                             
                                             tabPanel.add({
                                                  id:'sales-report',
                                                  title: "Sold Time",
                                                  items: chart,
                                                  closable: true,
                                                  autoScroll:true
                                             })
                                             tabPanel.doLayout();
                                             tabPanel.activate('sales-report');
                                        }
                                   });
                              }
                         }
                   }*/]
               },tabPanel
            ]
     });
     
     /*
     Ext.Ajax.request({
          url: 'service.php?action=logComet',
          success: function(a, b, c){
              //console.log("success");
              //console.log([a, b, c]);
              Ext.getCmp("log-watch").body.dom.innerHTML = "11";//a.responseText;
              //Ext.getCmp("log-watch").doLayout();
          },
          failure: function(a, b, c){
              console.log("failure");
              console.log([a, b, c]);
          },
          timeout:20000
     });
    */
});