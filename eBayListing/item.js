Ext.onReady(function(){
    Ext.BLANK_IMAGE_URL = "../../Ext/2.2/resources/images/default/s.gif";
    //var categoryPath = "";
    var today = new Date();
    var pictureForm = new Ext.form.FormPanel({
            labelAlign:"top",
            border: false,
            defaults:{
                width:400
            },
            items:[{
                id:"picture_value_1",
                xtype:"textfield",
                fieldLabel:"Picture 1  (used for Gallery)",
                name:"picture_value_1"
              },{
                id:"picture_value_2",
                xtype:"textfield",
                fieldLabel:"Picture 2",
                name:"picture_value_2"
              },{
                id:"picture_value_3",
                xtype:"textfield",
                fieldLabel:"Picture 3",
                name:"picture_value_3"
              },{
                id:"picture_value_4",
                xtype:"textfield",
                fieldLabel:"Picture 4",
                name:"picture_value_4"
              },{
                id:"picture_value_5",
                xtype:"textfield",
                fieldLabel:"Picture 5",
                name:"picture_value_5"
              },{
                id:"picture_value_6",
                xtype:"textfield",
                fieldLabel:"Picture 6",
                name:"picture_value_6"
              },{
                id:"picture_value_7",
                xtype:"textfield",
                fieldLabel:"Picture 7",
                name:"picture_value_7"
              },{
                id:"picture_value_8",
                xtype:"textfield",
                fieldLabel:"Picture 8",
                name:"picture_value_8"
              },{
                id:"picture_value_9",
                xtype:"textfield",
                fieldLabel:"Picture 9",
                name:"picture_value_9"
              },{
                id:"picture_value_10",
                xtype:"textfield",
                fieldLabel:"Picture 10",
                name:"picture_value_10"
            }],
            buttons:[{
                text: 'OK',
                handler: function(){
                    for(var i=1; i<=10;i++){
                        if(document.getElementById("picture_value_"+i).value != ""){
                            Ext.getCmp("picture_panel_"+i).body.dom.innerHTML = '<img width="60" height="60" src="' + document.getElementById("picture_value_"+i).value + '"/>';
                            Ext.getCmp("picture_panel_"+i).doLayout();
                            document.getElementById("picture_"+i).value = document.getElementById("picture_value_"+i).value;
                        }
                    }
                    selectPictureWindow.hide();
                }
                
            },{
                text: 'Cancel',
                handler: function(){
                    selectPictureWindow.hide();
                }
            }]
    })
    
    var selectPictureWindow = new Ext.Window({
        title:"Insert Picture URLs - Self Hosted",
        width:450,
        items: [{
            xtype:"panel",
            border: false,
            html:"<font color='green'>Please enter URLs for you pictures.(e.g. http://www.yourdomain.com/picture.gjf)<br>\
            Optimal image size for use with layouts is 400x300pixels.</font><br>"
        },pictureForm]
    })
                
    var listingDurationStore =  new Ext.data.JsonStore({
        //root: 'records',
        //totalProperty: 'totalCount',
        //idProperty: 'id',
        fields: ['id', 'name'],
        url:'service.php?action=getListingDuration'
    })

    var shippingServiceStore = new Ext.data.JsonStore({
        //root: 'records',
        //totalProperty: 'totalCount',
        //idProperty: 'id',
        fields: ['id', 'name'],
        url:'service.php?action=getShippingService'
    })
    
    
    
    var itemForm = new Ext.form.FormPanel({
        labelAlign:"top",
        //height: 600,
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
                        id:"Title",
                        xtype:"textfield",
                        fieldLabel:"Title",
                        name:"Title"
                      },{
                        id:"SubTitle",
                        xtype:"textfield",
                        fieldLabel:"Subtitle",
                        name:"SubTitle"
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
                                                    document.getElementById("PrimaryCategory").value = n.id;
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
                        },{
                            xtype:"hidden",
                            id:"PrimaryCategory",
                            name:"PrimaryCategory"
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
                                                    document.getElementById("SecondaryCategory").value = n.id;
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
                        },{
                            xtype:"hidden",
                            id:"SecondaryCategory",
                            name:"SecondaryCategory"
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
                                                    document.getElementById("StoreCategoryID").value = n.id;
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
                          },{
                            xtype:"hidden",
                            id:"StoreCategoryID",
                            name:"StoreCategoryID"
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
                                                    document.getElementById("StoreCategory2ID").value = n.id;
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
                        },{
                            xtype:"hidden",
                            id:"StoreCategory2ID",
                            name:"StoreCategory2ID"
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
                                id: "picture_panel_1",
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60,
                                listeners: {
                                    render: function(t){
                                        t.body.on('click', function(){
                                            selectPictureWindow.show();
                                        })
                                    }
                                }
                            },{
                                xtype:"hidden",
                                id:"picture_1",
                                name:"picture_1"
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                id: "picture_panel_2",
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60,
                                listeners: {
                                    render: function(t){
                                        t.body.on('click', function(){
                                            selectPictureWindow.show();
                                        })
                                    }
                                }
                            },{
                                xtype:"hidden",
                                id:"picture_2",
                                name:"picture_2"
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                id: "picture_panel_3",
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60,
                                listeners: {
                                    render: function(t){
                                        t.body.on('click', function(){
                                            selectPictureWindow.show();
                                        })
                                    }
                                }
                            },{
                                xtype:"hidden",
                                id:"picture_3",
                                name:"picture_3"
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                id: "picture_panel_4",
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60,
                                listeners: {
                                    render: function(t){
                                        t.body.on('click', function(){
                                            selectPictureWindow.show();
                                        })
                                    }
                                }
                            },{
                                xtype:"hidden",
                                id:"picture_4",
                                name:"picture_4"
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                id: "picture_panel_5",
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60,
                                listeners: {
                                    render: function(t){
                                        t.body.on('click', function(){
                                            selectPictureWindow.show();
                                        })
                                    }
                                }
                            },{
                                xtype:"hidden",
                                id:"picture_5",
                                name:"picture_5"
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                id: "picture_panel_6",
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60,
                                listeners: {
                                    render: function(t){
                                        t.body.on('click', function(){
                                            selectPictureWindow.show();
                                        })
                                    }
                                }
                            },{
                                xtype:"hidden",
                                id:"picture_6",
                                name:"picture_6"
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                id: "picture_panel_7",
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60,
                                listeners: {
                                    render: function(t){
                                        t.body.on('click', function(){
                                            selectPictureWindow.show();
                                        })
                                    }
                                }
                            },{
                                xtype:"hidden",
                                id:"picture_7",
                                name:"picture_7"
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                id: "picture_panel_8",
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60,
                                listeners: {
                                    render: function(t){
                                        t.body.on('click', function(){
                                            selectPictureWindow.show();
                                        })
                                    }
                                }
                            },{
                                xtype:"hidden",
                                id:"picture_8",
                                name:"picture_8"
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                id: "picture_panel_9",
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60,
                                listeners: {
                                    render: function(t){
                                        t.body.on('click', function(){
                                            selectPictureWindow.show();
                                        })
                                    }
                                }
                            },{
                                xtype:"hidden",
                                id:"picture_9",
                                name:"picture_9"
                            }]
                          },{
                            columnWidth:0.1,
                            items:[{
                                id: "picture_panel_10",
                                xtype:"panel",
                                style:"font-size:10px;",
                                bodyStyle:"padding:6px;cursor:pointer;",
                                html: "Click to insert into a picture",
                                width:60,
                                height:60,
                                listeners: {
                                    render: function(t){
                                        t.body.on('click', function(){
                                            selectPictureWindow.show();
                                        })
                                    }
                                }
                            },{
                                xtype:"hidden",
                                id:"picture_10",
                                name:"picture_10"
                            }]
                          }]
                    },{
                        //autoScroll: true,
                        id:"Description",
                        width: 600,
                        xtype:"htmleditor",
                        fieldLabel:"Descritpion",
                        name:"Description"
                      }]
                  },{
                    xtype:"panel",
                    //title:"Inventory Information",
                    layout:"form",
                    items:[{
                            title: "Schedule",
                            layout:"column",
                            items:[{
                                columnWidth:0.2,
                                layout:"form",
                                border: false,
                                items:[{
                                    xtype:"datefield",
                                    fieldLabel:"Date",
                                    name:"ScheduleDate",
                                    minValue: today.format("Y-m-d"),
                                    format : 'Y-m-d'
                                }]
                            },{
                                columnWidth:0.8,
                                layout:"form",
                                border: false,
                                items:[{
                                    xtype:"timefield",
                                    fieldLabel:"Time",
                                    name:"ScheduleTime",
                                    width: 120,
                                    listWidth: 120,
                                    triggerAction: "all",
                                    selectOnFocus: true,
                                    increment: 60,
                                    editable: false,
                                    format: 'H:i'
                                }]
                            }]
                        },{
                            xtype:"textfield",
                            fieldLabel:"<font color='red'>SKU</font>",
                            name:"SKU",
                            value: sku,
                            readOnly: true
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
                                    name:"StartPrice"
                                  },{
                                    xtype:"numberfield",
                                    fieldLabel:"Buy It Now Price",
                                    name:"BuyItNowPrice"
                                  },{
                                    xtype:"numberfield",
                                    fieldLabel:"Quantity",
                                    name:"Quantity"
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
                                    mode: 'local',
                                    store: listingDurationStore,
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    //listWidth: 156,
                                    //width: 156,
                                    name: 'ListingDuration',
                                    //allowBlank: false,
                                    hiddenName:'ListingDuration' 
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
                                        //name: 'ListingTypeCombo',
                                        //hiddenName:'ListingTypeCombo',
                                        width: 150,
                                        listeners: {
                                            "select": function(c, r, i){
                                                document.getElementById("ListingType").value = r.data.name;
                                                listingDurationStore.load({params: {id: r.data.id}});
                                            }
                                        }
                                });
                                combo.render(c.header, 1);
                                c.on('destroy', function(){
                                        combo.destroy();
                                }, c, {single: true});
                            }
                        }
                    },{
                        xtype:"hidden",
                        id:"ListingType",
                        name:"ListingType"
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
                                    mode: 'local',
                                    store: shippingServiceStore,
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    //listWidth: 156,
                                    //width: 156,
                                    //name: 'ListingDuration',
                                    //allowBlank: false,
                                    //hiddenName:'ListingDuration',
                                    width:150,
                                    listWidth:260
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
                                    //name:"combovalue",
                                    //hiddenName:"combovalue",
                                    mode: 'local',
                                    store: shippingServiceStore,
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    width:150,
                                    listWidth:260
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
                                    //name:"combovalue",
                                    //hiddenName:"combovalue",
                                    mode: 'local',
                                    store: shippingServiceStore,
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    width:150,
                                    listWidth:260
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
                                        store: ['Flat', 'Calculated'],
                                        triggerAction: 'all',
                                        editable: false,
                                        width: 150,
                                        listWidth: 150,
                                        listeners: {
                                            "select": function(c, r, i){
                                                //console.log(r);
                                                shippingServiceStore.load({params: {InternationalService: 0, serviceType: r.data.text}});
                                            }
                                        }
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
                                    //name:"combovalue",
                                    //hiddenName:"combovalue",
                                    mode: 'local',
                                    store: shippingServiceStore,
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    width:150,
                                    listWidth:260
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
                                    //name:"combovalue",
                                    //hiddenName:"combovalue",
                                    mode: 'local',
                                    store: shippingServiceStore,
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    width:150,
                                    listWidth:260
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
                                    //name:"combovalue",
                                    //hiddenName:"combovalue",
                                    mode: 'local',
                                    store: shippingServiceStore,
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    width:150,
                                    listWidth:260
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
                                        store: ['Flat', 'Calculated'],
                                        triggerAction: 'all',
                                        editable: false,
                                        width: 150,
                                        listWidth: 150,
                                        listeners: {
                                            "select": function(c, r, i){
                                                //console.log(r);
                                                shippingServiceStore.load({params: {InternationalService: 1, serviceType: r.data.text}});
                                            }
                                        }
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
                            name:"PayPalEmailAddress",
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
            }],
            buttons: [{
                text: "Save",
                handler: function(){
                    itemForm.getForm().submit({
                        clientValidation: true,
                        url: 'service.php',
                        success: function(form, action) {
                            Ext.Msg.alert("Success", action.result.msg);
                        },
                        failure: function(form, action) {
                            switch (action.failureType) {
                                case Ext.form.Action.CLIENT_INVALID:
                                    Ext.Msg.alert("Failure", "Form fields may not be submitted with invalid values");
                                    break;
                                case Ext.form.Action.CONNECT_FAILURE:
                                    Ext.Msg.alert("Failure", "Ajax communication failed");
                                    break;
                                case Ext.form.Action.SERVER_INVALID:
                                    Ext.Msg.alert("Failure", action.result.msg);
                            }
                        }

                    })
                }
            },{
                text: "Canel",
                handler: function(){
                    
                }
            }]
    })
    
    var itemPanel = new Ext.Panel({
        autoScroll: true,
        height:750,
        items: itemForm
    })
    
    itemPanel.render(document.body);
})

