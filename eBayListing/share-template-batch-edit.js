Ext.onReady(function(){
    var siteStore = new Ext.data.JsonStore({
        autoLoad :true,
        fields: ['id', 'name'],
        url:'service.php?action=getAllSites'
    })
    
    var countriesStore = new Ext.data.JsonStore({
        autoLoad :true,
        fields: ['id', 'name'],
        url:'service.php?action=getAllCountries'
    })
    
    var conditionStore = new Ext.data.JsonStore({
        //autoLoad :true,
        fields: ['id', 'name'],
        url:'service.php?action=getCategoryCondition'
    })
    
    var currencyCombo = new Ext.form.ComboBox({
        readOnly:true,
        labelAlign:"left",
        fieldLabel:"Currency",
        mode: 'local',
        store: ['USD', 'GBP', 'AUD', 'EUR'],
        //triggerAction: 'all',
        editable: false,
        selectOnFocus:true,
        //listWidth: 156,
        //width: 156,
        name:'Currency',
        hiddenName:'Currency'
    })
    
    var categoryStore = new Ext.data.JsonStore({
        //autoLoad :true,
        fields: ['id', 'name'],
        url:'service.php?action=getCategoryById'
    })
    
    var listingDurationStore =  new Ext.data.JsonStore({
        //root: 'records',
        //totalProperty: 'totalCount',
        //idProperty: 'id',
        fields: ['id', 'name'],
        url:'service.php?action=getListingDuration'
    })
    
    var listTypeCombo = new Ext.form.ComboBox({
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
        //allowBlank:false,
        listeners: {
            "select": function(c, r, i){
                switch(r.data.name){
                    case "Chinese":
                        Ext.getCmp("StartPrice").setDisabled(0);
                        Ext.getCmp("ReservePrice").setDisabled(0);
                        Ext.getCmp("Quantity").setValue(1);
                        //Ext.getCmp("Quantity").setDisabled(1);
                    break;
                
                    case "Dutch":
                        Ext.getCmp("Quantity").setDisabled(0);
                        Ext.getCmp("StartPrice").setDisabled(0);
                        Ext.getCmp("ReservePrice").setDisabled(1);
                    break;
                
                    case "FixedPriceItem":
                        Ext.getCmp("Quantity").setDisabled(0);
                        Ext.getCmp("StartPrice").setDisabled(1);
                        Ext.getCmp("StartPrice").setValue(0);
                        Ext.getCmp("ReservePrice").setDisabled(1);
                    break;
                
                    case "StoresFixedPrice":
                        Ext.getCmp("Quantity").setDisabled(0);
                        Ext.getCmp("StartPrice").setDisabled(1);
                        Ext.getCmp("StartPrice").setValue(0);
                        Ext.getCmp("ReservePrice").setDisabled(1);
                    break;
                }
                
                document.getElementById("ListingType").value = r.data.name;
                listingDurationStore.load({params: {id: r.data.id}});
            }
        }
    });
    
    var shippingTemplateStore = new Ext.data.JsonStore({
        autoLoad :true,
        fields: ['id', 'name'],
        url:'service.php?action=getShareShippingTemplate'
    })
    
    var shippingTemplateCombo = new Ext.form.ComboBox({
        fieldLabel:"Shipping Template",
        mode: 'local',
        store: shippingTemplateStore,
        valueField:'id',
        displayField:'name',
        triggerAction: 'all',
        selectOnFocus:true,
        name: 'shippingTemplateId',
        hiddenName:'shippingTemplateId'
    })
    
    var form = new Ext.form.FormPanel({
        labelAlign:"top",
        //height: 600,
        buttonAlign:"center",
        items:[{
            layout:"column",
            border:false,
            width: 600,
            items:[{
                columnWidth:0.25,
                layout:"form",
                defaults:{
                    width: 100,
                    listWidth: 100
                },
                border:false,
                items:[{
                    xtype:"combo",
                    labelAlign:"left",
                    fieldLabel:"Site",
                    mode: 'local',
                    store: siteStore,
                    valueField:'name',
                    displayField:'name',
                    triggerAction: 'all',
                    editable: false,
                    selectOnFocus:true,
                    //listWidth: 156,
                    //width: 156,
                    name: 'Site',
                    hiddenName:'Site',
                    allowBlank:false,
                    listeners: {
                        select: function(c, r, i){
                            categoryStore.setBaseParam('SiteID', r.data.id);
                            //console.log(r);
                            switch(r.data.name){
                                case "US":
                                   currencyCombo.setValue("USD");
                                   Ext.getCmp("Motors").hide();
                                break;
                            
                                case "UK":
                                   currencyCombo.setValue("GBP");
                                   Ext.getCmp("Motors").hide();
                                break;
                            
                                case "Australia":
                                    currencyCombo.setValue("AUD");
                                    Ext.getCmp("Motors").hide();
                                break;
                            
                                case "France":
                                    currencyCombo.setValue("EUR");
                                    Ext.getCmp("Motors").hide();
                                break;
                            
                                case "Germany":
                                    currencyCombo.setValue("EUR");
                                    Ext.getCmp("Motors").show();
                                break;
                            
                                default:
                                    currencyCombo.setValue("USD");
                                    Ext.getCmp("Motors").hide();
                                break;
                            }
                            Ext.getCmp("SiteID").setValue(r.data.name);
                        }
                    }
                }]
            },{
                columnWidth:0.25,
                layout:"form",
                defaults:{
                    width: 80,
                    listWidth: 80
                },
                border:false,
                items: currencyCombo
            },{
                columnWidth:0.25,
                layout:"form",
                border:false,
                items: {
                    xtype:"combo",
                    labelAlign:"left",
                    fieldLabel:"Condition",
                    mode: 'local',
                    store: conditionStore,
                    valueField:'id',
                    displayField:'name',
                    triggerAction: 'all',
                    editable: false,
                    selectOnFocus:true,
                    listWidth: 120,
                    width: 120,
                    name: 'ConditionID',
                    hiddenName:'ConditionID',
                    listeners: {
                        focus: function(t){
                            var SiteID = Ext.getCmp("SiteID").getValue();
                            var PrimaryCategoryCategoryID = Ext.getCmp("PrimaryCategoryCategoryID").getValue();
                            if(Ext.isEmpty(SiteID) || Ext.isEmpty(PrimaryCategoryCategoryID)){
                                Ext.Msg.alert('Warn', 'Please first choice Site and Category.');
                            }else{
                                conditionStore.setBaseParam('site_id', SiteID);
                                conditionStore.setBaseParam('category_id', PrimaryCategoryCategoryID);
                                conditionStore.load();
                            }
                        }
                    }
                }
            },{
                columnWidth:0.25,
                layout:"form",
                border:false,
                items: {
                    xtype:"checkbox",
                    boxLabel:"Motors",
                    id:'Motors',
                    name:'Motors',
                    hidden: true
                }
            }]
        },{
            xtype:"hidden",
            id:'SiteID',
            name:'SiteID'
        },{
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
                        name:"Title",
                        maxLength: 80
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
                                //editable:false,
                                name:"PrimaryCategoryCategoryName",
                                hiddenName:"PrimaryCategoryCategoryName",
                                width: 600,
                                listWidth: 600,
                                store: categoryStore,
                                displayField:'name',
                                //typeAhead: false,
                                minChars: 3,
                                loadingText: 'Searching...',
                                pageSize:20,
                                listeners:{
                                    select: function(c, r, i){
                                        //console.log([c, r, i]);
                                        //itemForm.getForm().findField("category").setValue(r.data.name);
                                        document.getElementById("PrimaryCategoryCategoryID").value = r.data.id;
                                    }
                                }
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
                                        dataUrl: 'service.php?action=getCategoriesTree&SiteID='+Ext.getCmp('SiteID').getValue(),
                                
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
                                                    
                                                    form.getForm().findField("category").setValue(categoryPath);
                                                    document.getElementById("PrimaryCategoryCategoryID").value = n.id;
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
                            id:"PrimaryCategoryCategoryID",
                            name:"PrimaryCategoryCategoryID"
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
                                    //id:"USStoreCategoryName",
                                    xtype:"combo",
                                    fieldLabel:"Store Category(US)",
                                    editable:false,
                                    name:"USStoreCategoryName",
                                    hiddenName:"USStoreCategoryName",
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
                                            dataUrl: 'service.php?action=getUSStoreCategoriesTree',
                                    
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
                                                        
                                                        form.getForm().findField("USStoreCategoryName").setValue(categoryPath);
                                                        document.getElementById("USStoreCategoryID").value = n.id;
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
                                columnWidth:0.9,
                                layout:"form",
                                items:[{
                                    //id:"DEStoreCategoryName",
                                    xtype:"combo",
                                    fieldLabel:"Store Category(DE)",
                                    editable:false,
                                    name:"DEStoreCategoryName",
                                    hiddenName:"DEStoreCategoryName",
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
                                            dataUrl: 'service.php?action=getDEStoreCategoriesTree',
                                    
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
                                                        
                                                        form.getForm().findField("DEStoreCategoryName").setValue(categoryPath);
                                                        document.getElementById("DEStoreCategoryID").value = n.id;
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
                                id:"USStoreCategoryID",
                                name:"USStoreCategoryID"
                            },{
                                xtype:"hidden",
                                id:"DEStoreCategoryID",
                                name:"DEStoreCategoryID"    
                            }]
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
                            width:300,
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
                                    id:"StartPrice",
                                    name:"StartPrice",
                                    maxValue: 9.9/*,
                                    listeners: {
                                        blur: function(t){
                                            if(global_config.LP){
                                                Ext.Ajax.request({
                                                    url: 'service.php?action=getTemplateLowPrice&id=' + template_id + '&SKU=' + Ext.getCmp("SKU").getValue() + '&type=auction&Currency=' + currencyCombo.getValue() + '&price=' + t.getValue() + '&ShippingServiceCost=' + Ext.getCmp('ShippingServiceCost').getValue() + '&shippingTemplateId='+shippingTemplateCombo.getValue() + '&site=' + Ext.getCmp("SiteID").getValue(),
                                                    success: function(a, b){
                                                        Ext.getCmp("StartPrice").minValue = a.responseText;
                                                    }
                                                })
                                            }
                                        }
                                    }*/
                                  },{
                                    xtype:"numberfield",
                                    fieldLabel:"Buy It Now Price",
                                    id:"BuyItNowPrice",
                                    name:"BuyItNowPrice"/*,
                                    listeners: {
                                        blur: function(t){
                                            if(global_config.LP){
                                                Ext.Ajax.request({
                                                    url: 'service.php?action=getTemplateLowPrice&id=' + template_id + '&SKU=' + Ext.getCmp("SKU").getValue() + '&type=fix&Currency=' + currencyCombo.getValue() + '&price=' + t.getValue() + '&ShippingServiceCost=' + Ext.getCmp('ShippingServiceCost').getValue() + '&shippingTemplateId='+shippingTemplateCombo.getValue() + '&site=' + Ext.getCmp("SiteID").getValue(),
                                                    success: function(a, b){
                                                        Ext.getCmp("BuyItNowPrice").minValue = a.responseText;
                                                    }
                                                })
                                            }
                                        }
                                    }*/
                                  },{
                                    xtype:"numberfield",
                                    fieldLabel:"Quantity",
                                    name:"Quantity",
                                    id:"Quantity",
                                    validator: function(t){
                                        if(listTypeCombo.getValue() == "Chinese" && t != 1){
                                            return false;
                                        }else{
                                            return true;
                                        }
                                    }
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
                                    id:"ReservePrice",
                                    name:"ReservePrice"
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
                                    hiddenName:'ListingDuration'
                                  },{
                                    xtype:"numberfield",
                                    fieldLabel:"Lowest Price",
                                    disabled:true,
                                    id:'LowPrice',
                                    name: 'LowPrice'
                                }]
                            }]
                        }],
                        cls: 'my-fieldset',
                        style: 'margin: 10px;',
                        listeners: {
                            render: function(c){
                                listTypeCombo.render(c.header, 1);
                                c.on('destroy', function(){
                                        listTypeCombo.destroy();
                                }, c, {single: true});
                            }
                        }
                    },{
                        xtype:"hidden",
                        id:"ListingType",
                        name:"ListingType"
                    },shippingTemplateCombo,
                    {
                        layout:"column",
                        title:"Shipping Cost",
                        items:[{
                            columnWidth:0.5,
                            layout:"form",
                            labelAlign:"top",
                            border:false,
                            items:[{
                                xtype:"numberfield",
                                fieldLabel:"Cost",
                                id:"ShippingServiceCost",
                                name:"ShippingServiceCost"
                              }]
                          },{
                            columnWidth:0.5,
                            layout:"form",
                            labelAlign:"top",
                            border:false,
                            items:[{
                                xtype:"numberfield",
                                fieldLabel:"E A I",
                                id:"ShippingServiceAdditionalCost",
                                name:"ShippingServiceAdditionalCost"
                              }]
                          }]
                    },{
                        xtype:"timefield",
                        id:"ForeverListingTime",
                        fieldLabel:"Forever Listing Time",
                        name:"ForeverListingTime",
                        format:"H:i"
                    }]
                  }]
            }]
        }],
        buttons: [{
            text: "Batch Update Share Template",
            handler: function(){
                form.getForm().submit({
                    url: 'service.php?action=batchUpdateShareTemplate&ids='+ids,
                    success: function(form, action) {
                        //console.log(action);
                        Ext.Msg.alert("Success", action.result.msg);
                    },
                    waitMsg:'updating, please wait.',
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
            text: "Close Window",
            handler: function(){
                window.close();
            }
        }]
    })
    
    var panel = new Ext.Panel({
        autoScroll: true,
        //height:750,
        items: form
    })
    
    panel.render(document.body);  
})