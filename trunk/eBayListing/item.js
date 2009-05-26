Ext.onReady(function(){
    //var categoryPath = "";
    var itemForm = new Ext.form.FormPanel({
        labelAlign:"top",
        autoScroll:true,
        height: 600,
        items:[{
            layout:"column",
            items:[{
                columnWidth:0.7,
                layout:"form",
                items:[{
                    xtype:"panel",
                    title:"Title and Category",
                    layout:"form",
                    defaults : {
                        width: 600,
                        listWidth: 600
                    },
                    items:[{
                        xtype:"textfield",
                        fieldLabel:"Title",
                        name:"textvalue"
                      },{
                        xtype:"textfield",
                        fieldLabel:"Subtitle",
                        name:"textvalue"
                      },{
                        layout:"column",
                        border: false,
                        defaults:{
                            border:false
                        },
                        width:680,
                        items:[{
                            columnWidth:0.9,
                            layout:"form",
                            items:[{
                                id:"category",
                                xtype:"combo",
                                fieldLabel:"Category",
                                name:"combovalue",
                                hiddenName:"combovalue",
                                width: 600,
                                listWidth: 600
                            }]
                          },{
                            columnWidth:0.1,
                            layout:"form",
                            items:[{
                                xtype:"button",
                                text:"Select",
                                style:"padding-top:18px;",
                                handler: function(){
                                    
                                    var categoryTree = new Ext.tree.TreePanel({
                                        useArrows:true,
                                        autoScroll:true,
                                        animate:true,
                                        //containerScroll:true,
                                        height:600,
                                        width:300,
                                        // auto create TreeLoader
                                        dataUrl: 'service.php?action=getCategoriesTree',
                                
                                        root: {
                                            nodeType: 'async',
                                            draggable:false,
                                            id: "0"
                                        },
                                        rootVisible: false,
                                        listeners:{
                                            click: function(n, e){
                                                if(n.leaf){
                                                    //console.log(n);
                                                    var categoryPath = "";
                                                    var categoryPath = n.text;
                                                    var parentNode = n.parentNode;
                                                    while(parentNode.id != "0"){
                                                        //console.log(parentNode);
                                                        categoryPath = parentNode.text + " --> " + categoryPath;
                                                        parentNode = parentNode.parentNode;
                                                    }
                                                    
                                                    itemForm.getForm().findField("category").setValue(categoryPath);
                                                    selectCategoryWindow.close();
                                                }
                                                //else{
                                                //    categoryPath = categoryPath + " --> " + n.text;
                                                //}
                                                //console.log(n);
                                            },
                                            expandnode: function(n){
                                                //console.log(n);
                                            }
                                        }
                                    })
                                    
                                    var selectCategoryWindow = new Ext.Window({
                                        title:"Select Category",
                                        items: [{
                                            xtype:"label",
                                            text:"Select a category for you item."
                                        },categoryTree]
                                    })
                                    
                                    selectCategoryWindow.show();
                                }
                            }]
                        }]
                      },{
                        layout:"column",
                        border: false,
                        defaults:{
                            border:false
                        },
                        width:680,
                        items:[{
                            columnWidth:0.9,
                            layout:"form",
                            items:[{
                                id:"SCategory",
                                xtype:"combo",
                                fieldLabel:"2nd Category",
                                name:"combovalue",
                                hiddenName:"combovalue",
                                width: 600,
                                listWidth: 600
                            }]
                          },{
                            columnWidth:0.1,
                            layout:"form",
                            items:[{
                                xtype:"button",
                                text:"Select",
                                style:"padding-top:18px;",
                                handler: function(){
                                    
                                    var categoryTree = new Ext.tree.TreePanel({
                                        useArrows:true,
                                        autoScroll:true,
                                        animate:true,
                                        //containerScroll:true,
                                        height:600,
                                        width:300,
                                        // auto create TreeLoader
                                        dataUrl: 'service.php?action=getCategoriesTree',
                                
                                        root: {
                                            nodeType: 'async',
                                            draggable:false,
                                            id: "0"
                                        },
                                        rootVisible: false,
                                        listeners:{
                                            click: function(n, e){
                                                if(n.leaf){
                                                    //console.log(n);
                                                    var categoryPath = "";
                                                    var categoryPath = n.text;
                                                    var parentNode = n.parentNode;
                                                    while(parentNode.id != "0"){
                                                        //console.log(parentNode);
                                                        categoryPath = parentNode.text + " --> " + categoryPath;
                                                        parentNode = parentNode.parentNode;
                                                    }
                                                    
                                                    itemForm.getForm().findField("SCategory").setValue(categoryPath);
                                                    selectCategoryWindow.close();
                                                }
                                                //else{
                                                //    categoryPath = categoryPath + " --> " + n.text;
                                                //}
                                                //console.log(n);
                                            },
                                            expandnode: function(n){
                                                //console.log(n);
                                            }
                                        }
                                    })
                                    
                                    var selectCategoryWindow = new Ext.Window({
                                        title:"Select Category",
                                        items: [{
                                            xtype:"label",
                                            text:"Select a category for you item."
                                        },categoryTree]
                                    })
                                    
                                    selectCategoryWindow.show();
                                }
                            }]
                        }]
                      },{
                        layout:"column",
                        border: false,
                        defaults:{
                            border:false
                        },
                        width:680,
                        items:[{
                            columnWidth:0.9,
                            layout:"form",
                            items:[{
                                id:"storeCategory",
                                xtype:"combo",
                                fieldLabel:"Store Category",
                                name:"combovalue",
                                hiddenName:"combovalue",
                                width: 600,
                                listWidth: 600
                            }]
                          },{
                            columnWidth:0.1,
                            layout:"form",
                            items:[{
                                xtype:"button",
                                text:"Select",
                                style:"padding-top:18px;",
                                handler: function(){
                                    
                                    var storeCategoriesTree = new Ext.tree.TreePanel({
                                        useArrows:true,
                                        autoScroll:true,
                                        animate:true,
                                        //containerScroll:true,
                                        height:600,
                                        width:300,
                                        // auto create TreeLoader
                                        dataUrl: 'service.php?action=getStoreCategoriesTree',
                                
                                        root: {
                                            nodeType: 'async',
                                            draggable:false,
                                            id: "0"
                                        },
                                        rootVisible: false,
                                        listeners:{
                                            click: function(n, e){
                                                if(n.leaf){
                                                    //console.log(n);
                                                    var categoryPath = "";
                                                    var categoryPath = n.text;
                                                    var parentNode = n.parentNode;
                                                    while(parentNode.id != "0"){
                                                        //console.log(parentNode);
                                                        categoryPath = parentNode.text + " --> " + categoryPath;
                                                        parentNode = parentNode.parentNode;
                                                    }
                                                    
                                                    itemForm.getForm().findField("storeCategory").setValue(categoryPath);
                                                    selectStoreCategoryWindow.close();
                                                }
                                                //else{
                                                //    categoryPath = categoryPath + " --> " + n.text;
                                                //}
                                                //console.log(n);
                                            },
                                            expandnode: function(n){
                                                //console.log(n);
                                            }
                                        }
                                    })
                                    
                                    var selectStoreCategoryWindow = new Ext.Window({
                                        title:"Select Store Category",
                                        items: [{
                                            xtype:"label",
                                            text:"Select a store category for you item."
                                        },storeCategoriesTree]
                                    })
                                    
                                    selectStoreCategoryWindow.show();
                                }
                            }]
                          }]
                      },{
                        layout:"column",
                        border: false,
                        defaults:{
                            border:false
                        },
                        width:680,
                        items:[{
                            columnWidth:0.9,
                            layout:"form",
                            items:[{
                                id:"SStoreCategory",
                                xtype:"combo",
                                fieldLabel:"2nd Store Category",
                                name:"combovalue",
                                hiddenName:"combovalue",
                                width: 600,
                                listWidth: 600
                            }]
                          },{
                            columnWidth:0.1,
                            layout:"form",
                            items:[{
                                xtype:"button",
                                text:"Select",
                                style:"padding-top:18px;",
                                handler: function(){
                                    var storeCategoriesTree = new Ext.tree.TreePanel({
                                        useArrows:true,
                                        autoScroll:true,
                                        animate:true,
                                        //containerScroll:true,
                                        height:600,
                                        width:300,
                                        // auto create TreeLoader
                                        dataUrl: 'service.php?action=getStoreCategoriesTree',
                                
                                        root: {
                                            nodeType: 'async',
                                            draggable:false,
                                            id: "0"
                                        },
                                        rootVisible: false,
                                        listeners:{
                                            click: function(n, e){
                                                if(n.leaf){
                                                    //console.log(n);
                                                    var categoryPath = "";
                                                    var categoryPath = n.text;
                                                    var parentNode = n.parentNode;
                                                    while(parentNode.id != "0"){
                                                        //console.log(parentNode);
                                                        categoryPath = parentNode.text + " --> " + categoryPath;
                                                        parentNode = parentNode.parentNode;
                                                    }
                                                    
                                                    itemForm.getForm().findField("SStoreCategory").setValue(categoryPath);
                                                    selectStoreCategoryWindow.close();
                                                }
                                                //else{
                                                //    categoryPath = categoryPath + " --> " + n.text;
                                                //}
                                                //console.log(n);
                                            },
                                            expandnode: function(n){
                                                //console.log(n);
                                            }
                                        }
                                    })
                                    
                                    var selectStoreCategoryWindow = new Ext.Window({
                                        title:"Select Store Category",
                                        items: [{
                                            xtype:"label",
                                            text:"Select a store category for you item."
                                        },storeCategoriesTree]
                                    })
                                    
                                    selectStoreCategoryWindow.show();
                                }
                            }]
                        }]
                      }]
                  },{
                    xtype:"panel",
                    title:"Pictures and Description",
                    layout:"form",
                    items:[{
                        layout:"column",
                        title:"Picture",
                        items:[{
                            columnWidth:0.1,
                            items:[{
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60,
                                listeners: {
                                    click: function(){
                                        console.log("test");
                                        var selectPictureWindow = new Ext.Window({
                                            title:"Insert Picture URLs - Self Hosted",
                                            items: [{
                                                xtype:"label",
                                                text:"Please enter URLs for you pictures.(e.g. http://www.yourdomain.com/picture.gjf)<br>\
                                                Optimal image size for use with layouts is 400x300pixels."
                                            }]
                                        })
                                        
                                        selectPictureWindow.show();
                                    }
                                }
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60
                            }]
                          }]
                    },{
                        //autoScroll: true,
                        width: 600,
                        xtype:"htmleditor",
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
                    title:"Selling Format",
                    layout:"form",
                    labelAlign:"top",
                    items:[{
                        xtype:"fieldset",
                        title:" ",
                        autoHeight:true,
                        items:[{
                            layout:"column",
                            border:false,
                            items:[{
                                columnWidth:0.5,
                                layout:"form",
                                defaults:{
                                    width: 100,
                                    listWidth: 100
                                },
                                border:false,
                                items:[{
                                    xtype:"numberfield",
                                    fieldLabel:"Start Price",
                                    name:"textvalue"
                                  },{
                                    xtype:"numberfield",
                                    fieldLabel:"Buy It Now Price",
                                    name:"textvalue"
                                  },{
                                    xtype:"numberfield",
                                    fieldLabel:"Quantity",
                                    name:"textvalue"
                                  }]
                              },{
                                columnWidth:0.5,
                                border:false,
                                layout:"form",
                                defaults:{
                                    width: 100,
                                    listWidth: 100
                                },
                                items:[{
                                    xtype:"numberfield",
                                    fieldLabel:"Reserve Price",
                                    name:"textvalue"
                                  },{
                                    xtype:"textfield",
                                    fieldLabel:"Text",
                                    name:"textvalue"
                                  },{
                                    xtype:"combo",
                                    fieldLabel:"Duration",
                                    name:"combovalue",
                                    hiddenName:"combovalue"
                                  }]
                              }]
                        }],
                        cls: 'my-fieldset',
                        listeners: {
                            render: function(c){
                                var combo = new Ext.form.ComboBox({
                                        mode: 'local',
                                        store: new Ext.data.JsonStore({
                                            autoLoad: true,
                                            fields: ['id', 'name'],
                                            url: "service.php?action=getListingDurationType"
                                        }),
                                        valueField:'id',
                                        displayField:'name',
                                        triggerAction: 'all',
                                        editable: false,
                                        selectOnFocus:true,
                                        //name: 'payeeId',
                                        //hiddenName:'payeeId',
                                        width: 150
                                });
                                combo.render(c.header, 1);
                                c.on('destroy', function(){
                                        combo.destroy();
                                }, c, {single: true});
                            }
                        }
                    }]
                  },{
                    xtype:"panel",
                    title:"Shipping Options",
                    layout:"form",
                    labelAlign:"top",
                    items:[{
                        xtype:"label",
                        text:"Domestic Shipping"
                    },{
                        xtype:"fieldset",
                        title:" ",
                        autoHeight:true,
                        bodyStyle:"padding:0px;",
                        items:[{
                            layout:"table",
                            layoutConfig:{
                              columns:3
                            },
                            defaults:{
                              bodyStyle:"padding:0px;",
                              style:"margin:0px;"
                              //width:60
                            },
                            border:false,
                            items:[{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"combo",
                                    fieldLabel:"Domestic Services",
                                    name:"combovalue",
                                    hiddenName:"combovalue",
                                    width:120,
                                    listWidth:120
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"numberfield",
                                    fieldLabel:"Cost",
                                    name:"numbervalue",
                                    width:60
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                bodyStyle:'padding-left:10px;',
                                items:[{
                                    xtype:"checkbox",
                                    labelWidth: 0,
                                    labelSeparator: '',
                                    fieldLabel:"",
                                    boxLabel:"Free",
                                    name:"checkbox",
                                    inputValue:"cbvalue"
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"combo",
                                    labelWidth: 0,
                                    labelSeparator: '',
                                    labelStyle:'height:0px;padding:0px;',
                                    fieldLabel:"",
                                    name:"combovalue",
                                    hiddenName:"combovalue",
                                    width:120,
                                    listWidth:120
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"numberfield",
                                    labelWidth: 0,
                                    labelSeparator: '',
                                    labelStyle:'height:0px;padding:0px;',
                                    fieldLabel:"",
                                    name:"numbervalue",
                                    width:60
                                  }]
                              },{
                                border:false
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"combo",
                                    labelWidth: 0,
                                    labelSeparator: '',
                                    labelStyle:'height:0px;padding:0px;',
                                    fieldLabel:"",
                                    name:"combovalue",
                                    hiddenName:"combovalue",
                                    width:120,
                                    listWidth:120
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"numberfield",
                                    labelWidth: 0,
                                    labelSeparator: '',
                                    labelStyle:'height:0px;padding:0px;',
                                    fieldLabel:"",
                                    name:"numbervalue",
                                    width:60
                                  }]
                              },{
                                border:false
                              }]
                        }],
                        cls: 'my-fieldset',
                        listeners: {
                            render: function(c){
                                var combo = new Ext.form.ComboBox({
                                        store: ['One', 'Two', 'Three'],
                                        triggerAction: 'all',
                                        editable: false,
                                        width: 200
                                });
                                combo.render(c.header, 1);
                                c.on('destroy', function(){
                                        combo.destroy();
                                }, c, {single: true});
                            }
                        }
                    },{
                        xtype:"label",
                        text:"International Shipping"
                    },{
                        xtype:"fieldset",
                        title:" ",
                        autoHeight:true,
                        bodyStyle:"padding:0px;",
                        items:[{
                            layout:"table",
                            layoutConfig:{
                              columns:3
                            },
                            defaults:{
                              bodyStyle:"padding:0px;",
                              style:"margin:0px;"
                              //width:60
                            },
                            border:false,
                            items:[{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"combo",
                                    fieldLabel:"International Services",
                                    name:"combovalue",
                                    hiddenName:"combovalue",
                                    width:120,
                                    listWidth:120
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"numberfield",
                                    fieldLabel:"Cost",
                                    name:"numbervalue",
                                    width:60
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                bodyStyle:'padding-left:10px;',
                                items:[{
                                    xtype:"checkbox",
                                    labelWidth: 0,
                                    labelSeparator: '',
                                    fieldLabel:"",
                                    boxLabel:"Free",
                                    name:"checkbox",
                                    inputValue:"cbvalue"
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"combo",
                                    labelWidth: 0,
                                    labelSeparator: '',
                                    labelStyle:'height:0px;padding:0px;',
                                    fieldLabel:"",
                                    name:"combovalue",
                                    hiddenName:"combovalue",
                                    width:120,
                                    listWidth:120
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"numberfield",
                                    labelWidth: 0,
                                    labelSeparator: '',
                                    labelStyle:'height:0px;padding:0px;',
                                    fieldLabel:"",
                                    name:"numbervalue",
                                    width:60
                                  }]
                              },{
                                border:false
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"combo",
                                    labelWidth: 0,
                                    labelSeparator: '',
                                    labelStyle:'height:0px;padding:0px;',
                                    fieldLabel:"",
                                    name:"combovalue",
                                    hiddenName:"combovalue",
                                    width:120,
                                    listWidth:120
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"numberfield",
                                    labelWidth: 0,
                                    labelSeparator: '',
                                    labelStyle:'height:0px;padding:0px;',
                                    fieldLabel:"",
                                    name:"numbervalue",
                                    width:60
                                  }]
                              },{
                                border:false
                              }]
                        }],
                        cls: 'my-fieldset',
                        listeners: {
                            render: function(c){
                                var combo = new Ext.form.ComboBox({
                                        store: ['One', 'Two', 'Three'],
                                        triggerAction: 'all',
                                        editable: false,
                                        width: 200
                                });
                                combo.render(c.header, 1);
                                c.on('destroy', function(){
                                        combo.destroy();
                                }, c, {single: true});
                            }
                        }
                    }]
                  },{
                    xtype:"panel",
                    title:"Payment Method",
                    layout:"form",
                    items:[{
                        xtype:"fieldset",
                        title:"PayPal",
                        autoHeight:true,
                        style: 'padding:0px',
                        items:[{
                            xtype:"checkbox",
                            labelSeparator: '',
                            labelStyle: 'height:0px;padding:0px',
                            fieldLabel:"",
                            boxLabel:"Credit crads via PayPal",
                            name:"checkbox",
                            inputValue:"cbvalue"
                          },{
                            xtype:"textfield",
                            fieldLabel:"PayPal Account Email",
                            name:"textvalue",
                            width: 250
                          }]
                      },{
                        xtype:"checkbox",
                        labelSeparator: '',
                        labelStyle: 'height:0px;padding:0px',
                        fieldLabel:"",
                        boxLabel:"Merchant credit card: Visa/MasterCard",
                        name:"checkbox",
                        inputValue:"cbvalue"
                      },{
                        xtype:"checkbox",
                        labelSeparator: '',
                        labelStyle: 'height:0px;padding:0px',
                        fieldLabel:"",
                        boxLabel:"Box label",
                        name:"checkbox",
                        inputValue:"cbvalue"
                      },{
                        xtype:"checkbox",
                        labelSeparator: '',
                        labelStyle: 'height:0px;padding:0px',
                        fieldLabel:"",
                        boxLabel:"Merchant credit card: Discover",
                        name:"checkbox",
                        inputValue:"cbvalue"
                      },{
                        xtype:"checkbox",
                        labelSeparator: '',
                        labelStyle: 'height:0px;padding:0px',
                        fieldLabel:"",
                        boxLabel:"Merchant credit card: American Express",
                        name:"checkbox",
                        inputValue:"cbvalue"
                      }]
                    }]
                }]
            }]
    })
    
    var itemPanel = new Ext.Panel({
        autoScroll: true,
        items: itemForm
    })
    
    itemPanel.render(document.body);
})

