Ext.onReady(function(){
    Ext.BLANK_IMAGE_URL = "../ext-3.0.0/resources/images/default/s.gif";
    //var categoryPath = "";
    var today = new Date();
    var today = new Date();
    //var path = "/eBayBO/eBayListing/";
    //var path = "/eBayListing/";
    
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
                name:"picture_value_1",
                listeners: {
                    render: function(t){
                       t.setValue(Ext.getCmp("picture_1").getValue());
                    }
                }
              },{
                id:"picture_value_2",
                xtype:"textfield",
                fieldLabel:"Picture 2",
                name:"picture_value_2",
                listeners: {
                    render: function(t){
                       t.setValue(Ext.getCmp("picture_2").getValue());
                    }
                }
              },{
                id:"picture_value_3",
                xtype:"textfield",
                fieldLabel:"Picture 3",
                name:"picture_value_3",
                listeners: {
                    render: function(t){
                       t.setValue(Ext.getCmp("picture_3").getValue());
                    }
                }
              },{
                id:"picture_value_4",
                xtype:"textfield",
                fieldLabel:"Picture 4",
                name:"picture_value_4",
                listeners: {
                    render: function(t){
                       t.setValue(Ext.getCmp("picture_4").getValue());
                    }
                }
              },{
                id:"picture_value_5",
                xtype:"textfield",
                fieldLabel:"Picture 5",
                name:"picture_value_5",
                listeners: {
                    render: function(t){
                       t.setValue(Ext.getCmp("picture_5").getValue());
                    }
                }
              },{
                id:"picture_value_6",
                xtype:"textfield",
                fieldLabel:"Picture 6",
                name:"picture_value_6",
                listeners: {
                    render: function(t){
                       t.setValue(Ext.getCmp("picture_6").getValue());
                    }
                }
              },{
                id:"picture_value_7",
                xtype:"textfield",
                fieldLabel:"Picture 7",
                name:"picture_value_7",
                listeners: {
                    render: function(t){
                       t.setValue(Ext.getCmp("picture_7").getValue());
                    }
                }
              },{
                id:"picture_value_8",
                xtype:"textfield",
                fieldLabel:"Picture 8",
                name:"picture_value_8",
                listeners: {
                    render: function(t){
                       t.setValue(Ext.getCmp("picture_8").getValue());
                    }
                }
              },{
                id:"picture_value_9",
                xtype:"textfield",
                fieldLabel:"Picture 9",
                name:"picture_value_9",
                listeners: {
                    render: function(t){
                       t.setValue(Ext.getCmp("picture_9").getValue());
                    }
                }
              },{
                id:"picture_value_10",
                xtype:"textfield",
                fieldLabel:"Picture 10",
                name:"picture_value_10",
                listeners: {
                    render: function(t){
                       t.setValue(Ext.getCmp("picture_10").getValue());
                    }
                }
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
        closeAction:"hide",
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
    
    var internationalShippingServiceStore = new Ext.data.JsonStore({
        //root: 'records',
        //totalProperty: 'totalCount',
        //idProperty: 'id',
        fields: ['id', 'name'],
        url:'service.php?action=getInternationalShippingService'
    })
    
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
    
    var currencyCombo = new Ext.form.ComboBox({
        readOnly:true,
        labelAlign:"left",
        fieldLabel:"Currency",
        mode: 'local',
        store: ['USD', 'GBP', 'AUD', 'EUR'],
        triggerAction: 'all',
        editable: false,
        emptyText: "Multi Value",
        selectOnFocus:true,
        //listWidth: 156,
        //width: 156,
        name:'Currency',
        hiddenName:'Currency'
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
        emptyText: "Multi Value",
        selectOnFocus:true,
        //name: 'ListingTypeCombo',
        //hiddenName:'ListingTypeCombo',
        width: 150,
        listeners: {
            "select": function(c, r, i){
                switch(r.data.name){
                    case "Chinese":
                        Ext.getCmp("StartPrice").setDisabled(0);
                        Ext.getCmp("ReservePrice").setDisabled(0);
                        Ext.getCmp("Quantity").setValue(1);
                        Ext.getCmp("Quantity").setDisabled(1);
                    break;
                
                    case "FixedPriceItem":
                        Ext.getCmp("Quantity").setDisabled(0);
                        Ext.getCmp("StartPrice").setDisabled(1);
                        Ext.getCmp("ReservePrice").setDisabled(1);
                    break;
                
                    case "StoresFixedPrice":
                        Ext.getCmp("Quantity").setDisabled(0);
                        Ext.getCmp("StartPrice").setDisabled(1);
                        Ext.getCmp("ReservePrice").setDisabled(1);
                    break;
                }
                
                document.getElementById("ListingType").value = r.data.name;
                listingDurationStore.load({params: {id: r.data.id}});
            }
        }
    });
    
    var ShippingServiceOptionsTypeCombo = new Ext.form.ComboBox({
            store: ['Flat', 'Calculated'],
            triggerAction: 'all',
            editable: false,
            emptyText: "Multi Value",
            width: 150,
            listWidth: 150,
            listeners: {
                "select": function(c, r, i){
                    //console.log(c);
                    if(Ext.isEmpty(Ext.getCmp("SiteID").getValue())){
                        Ext.Msg.alert('Warn', 'Please choice Site.');
                    }else{
                        shippingServiceStore.load({params: {serviceType: c.value, SiteID: Ext.getCmp("SiteID").getValue()}});
                    }
                }
            }
    });
    
    var InternationalShippingServiceOptionTypeCombo = new Ext.form.ComboBox({
            store: ['Flat', 'Calculated'],
            triggerAction: 'all',
            editable: false,
            emptyText: "Multi Value",
            width: 150,
            listWidth: 150,
            listeners: {
                "select": function(c, r, i){
                    //console.log(r);
                    if(Ext.isEmpty(Ext.getCmp("SiteID").getValue())){
                        Ext.Msg.alert('Warn', 'Please choice Site.');
                    }else{
                        internationalShippingServiceStore.load({params: {serviceType: c.value, SiteID: Ext.getCmp("SiteID").getValue()}});
                    }
                }
            }
    });
    
    //---------------------------------------------------------------------------------------------------------
    var resultCategoryTpl = new Ext.XTemplate(
        '<tpl for="."><div class="search-item">',
            '<h3>{name} ({id})</h3>',
        '</div></tpl>'
    )
    
    var categoryStore = new Ext.data.JsonStore({
        //autoLoad :true,
        fields: ['id', 'name'],
        url:'service.php?action=getCategoryById'
    })
    
    var itemForm = new Ext.form.FormPanel({
        labelAlign:"top",
        //height: 600,
        buttonAlign:"center",
        items:[{
                layout:"column",
                border:false,
                width: 300,
                items:[{
                    columnWidth:0.5,
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
                        emptyText: "Multi Value",
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
                        //allowBlank: false,
                        hiddenName:'Site',
                        listeners: {
                            select: function(c, r, i){
                                categoryStore.setBaseParam('SiteID', r.data.id);
                                //console.log(r);
                                switch(r.data.name){
                                    case "US":
                                       currencyCombo.setValue("USD");
                                    break;
                                
                                    case "UK":
                                       currencyCombo.setValue("GBP");
                                    break;
                                
                                    case "Australia":
                                        currencyCombo.setValue("AUD");
                                    break;
                                
                                    case "France":
                                        currencyCombo.setValue("EUR");
                                    break;
                                }
                                Ext.getCmp("SiteID").setValue(r.data.name);
                            }
                        }
                    }]
                },{
                    columnWidth:0.5,
                    layout:"form",
                    defaults:{
                        width: 80,
                        listWidth: 80
                    },
                    border:false,
                    items: currencyCombo
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
                    tbar:[{
                        text:"Item Specifics",
                        iconCls :"item-specifics",
                        handler:function(){
                            if(Ext.isEmpty(Ext.getCmp("SiteID").getValue()) || Ext.isEmpty(Ext.getCmp("PrimaryCategoryCategoryID").getValue())){
                                Ext.Msg.alert('Warn', 'Please choice Site/Category.');
                            }else{
                                Ext.Ajax.request({
                                    url: 'service.php?action=getAttributes&SiteID='+Ext.getCmp("SiteID").getValue()+'&CategoryID='+Ext.getCmp("PrimaryCategoryCategoryID").getValue(),
                                    success: function(a, b){
                                        var temp = Ext.decode(a.responseText);
                                        /*
                                        {"CharacteristicsSetId":"2919","Attribute":[{"AttributeId":"10244","Label":"Condition","Type":"dropdown"
                                        
                                        ,"ValueList":[{"id":"-10","name":"-"},{"id":"10425","name":"New"},{"id":"10426","name":"Used"}]},{"AttributeId"
                                        
                                        :"3801","Label":"SIFFTAS Group Pseudo Attribute","Type":""}]}
                                        */
                                        
                                        //console.log(temp);
                                        
                                        var tempArray = new Array();
                                        for(var t in temp.Attribute){
                                            /*
                                            if(temp.Attribute[t].xtype == 'checkboxgroup'){
                                                //tempArray.push(temp.Attribute[t].id);
                                                var temp1 = Ext.decode(temp.Attribute[t].items);
                                                //console.log(temp.Attribute[t].items);
                                                for(var e in temp1){
                                                    //console.log(temp1[e]);
                                                    if(!Ext.isFunction(temp1[e])){
                                                        //tempArray.push(temp1[e].name);
                                                        Ext.getCmp(temp1[e].name).setValue(1);
                                                    }
                                                }
                                            }else{
                                            */
                                                if(!Ext.isFunction(temp.Attribute[t]) && temp.Attribute[t].xtype != 'checkboxgroup'){
                                                    tempArray.push(temp.Attribute[t].id);
                                                }
                                            //}
                                        }
                                        
                                        //console.log(tempArray);
                                        
                                        var itemSpecificsForm = new Ext.FormPanel({
                                            autoScroll:true,
                                            reader:new Ext.data.JsonReader({
                                            },tempArray)
                                        });
                                        
                                        itemSpecificsForm.add({
                                            xtype: "hidden",
                                            name: "CharacteristicsSetId",
                                            value: temp.CharacteristicsSetId
                                        });
                                        
                                        for(var i in temp.Attribute){
                                            if(!Ext.isFunction(temp.Attribute[i])){
                                                //console.log(Ext.decode(temp.Attribute[i].store))
                                                switch(temp.Attribute[i].xtype){
                                                    case "checkboxgroup":
                                                        itemSpecificsForm.add({
                                                            xtype: temp.Attribute[i].xtype,
                                                            fieldLabel: temp.Attribute[i].fieldLabel,
                                                            columns: 2,
                                                            items: Ext.decode(temp.Attribute[i].items)
                                                        });
                                                    break;
                                                
                                                    case "combo":
                                                        itemSpecificsForm.add({
                                                            //id: temp.Attribute[i].id,
                                                            xtype: temp.Attribute[i].xtype,
                                                            fieldLabel: temp.Attribute[i].fieldLabel,
                                                            name: temp.Attribute[i].name,
                                                            hiddenName: temp.Attribute[i].hiddenName,
                                                            mode: 'local',
                                                            triggerAction: 'all',
                                                            editable: false,
                                                            selectOnFocus:true,
                                                            valueField: 'id',
                                                            displayField: 'name',
                                                            store: Ext.decode(temp.Attribute[i].store)
                                                        });
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        var itemSpecificsWindow = new Ext.Window({
                                            title:"Item Specifics",
                                            height:300,
                                            width: 400,
                                            autoScroll:true,
                                            items: itemSpecificsForm,
                                            buttons:[{
                                                text:"OK",
                                                handler:function(){
                                                    itemSpecificsForm.getForm().submit({
                                                        clientValidation: true,
                                                        url: 'service.php?action=saveSpecifics&item_id='+item_id,
                                                        success: function(form, action) {
                                                            itemSpecificsWindow.close();
                                                            //console.log(action);
                                                            //Ext.Msg.alert("Success", action.result.msg);
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
                                                text:"Close",
                                                handler:function(){
                                                    itemSpecificsWindow.close();
                                                }
                                            }]
                                        })
                                        itemSpecificsWindow.show();
                                        
                                        itemSpecificsForm.getForm().load({
                                            url: 'service.php?action=loadSpecifics&AttributeSetID='+temp.CharacteristicsSetId+'&item_id='+item_id,
                                            waitMsg:'Please wait...',
                                            success: function(form, action){
                                                //console.log(action);
                                                var temp = Ext.decode(action.response.responseText);
                                                //console.log(temp);
                                                for(var i in temp[0]){
                                                    //console.log(i);
                                                    if(temp[0][i].indexOf("on") != -1){
                                                        Ext.getCmp(i).setValue(1);
                                                    }
                                                }
                                            }
                                        });
                                        
                                    },
                                    failure: function(){
                                        
                                    }
                                });
                            }
                        }
                    }],
                    items:[{
                        id:"Title",
                        xtype:"textfield",
                        fieldLabel:"Title",
                        emptyText: "Multi Value",
                        name:"Title"
                      },{
                        id:"SubTitle",
                        xtype:"textfield",
                        fieldLabel:"Subtitle",
                        emptyText: "Multi Value",
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
                                emptyText: "Multi Value",
                                //editable:false,
                                name:"PrimaryCategoryCategoryName",
                                hiddenName:"PrimaryCategoryCategoryName",
                                width: 600,
                                listWidth: 600,
                                store: categoryStore,
                                displayField:'name',
                                //typeAhead: false,
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
                                                    
                                                    itemForm.getForm().findField("category").setValue(categoryPath);
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
                                id:"SCategory",
                                xtype:"combo",
                                fieldLabel:"2nd Category",
                                emptyText: "Multi Value",
                                //editable:false,
                                name:"SecondaryCategoryCategoryName",
                                hiddenName:"SecondaryCategoryCategoryName",
                                width: 600,
                                listWidth: 600,
                                store: categoryStore,
                                displayField:'name',
                                //typeAhead: false,
                                loadingText: 'Searching...',
                                pageSize:20,
                                listeners:{
                                    select: function(c, r, i){
                                        //console.log([c, r, i]);
                                        //itemForm.getForm().findField("category").setValue(r.data.name);
                                        document.getElementById("SecondaryCategoryCategoryID").value = r.data.id;
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
                                                    
                                                    itemForm.getForm().findField("SCategory").setValue(categoryPath);
                                                    document.getElementById("SecondaryCategoryCategoryID").value = n.id;
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
                            id:"SecondaryCategoryCategoryID",
                            name:"SecondaryCategoryCategoryID"
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
                                emptyText: "Multi Value",
                                name:"StoreCategoryName",
                                hiddenName:"StoreCategoryName",
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
                                emptyText: "Multi Value",
                                name:"StoreCategory2Name",
                                hiddenName:"StoreCategory2Name",
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
                        xtype:"hidden",
                        id:"GalleryURL",
                        name:"GalleryURL"
                    },{
                    xtype:"panel",
                    title:"Pictures and Description",
                    layout:"form",
                    tbar:[{
                        text:"Gallery thumbnail",
                        handler:function(){
                            var window = new Ext.Window({
                                title:"Add gallery thumbnail picture",
                                closeAction:"hide",
                                width:450,
                                layout:"form",
                                items: [{
                                    xtype:"textfield",
                                    fieldLabel:"url",
                                    emptyText: "Multi Value",
                                    labelStyle:"width:50px;",
                                    id:"gallery-url",
                                    style:"padding-left:0px;",
                                    width:300,
                                    listeners: {
                                        "render": function(t){
                                            //console.log(Ext.getCmp("GalleryURL").getValue());
                                            t.setValue(Ext.getCmp("GalleryURL").getValue());
                                        }
                                    }
                                }],
                                buttons:[{
                                    text:"OK",
                                    handler:function(){
                                        Ext.getCmp("GalleryURL").setValue(Ext.getCmp("gallery-url").getValue());
                                        window.close();
                                    }
                                },{
                                    text:"Cancel",
                                    handler:function(){
                                        window.close();
                                    }
                                }]
                            })
                            Ext.getCmp("gallery-url").setValue(Ext.getCmp("GalleryURL").getValue());
                            window.show();
                        },
                        iconCls :"gallery-url"
                    }],
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
                        xtype:"panel",
                        width: 690,
                        height: 500,
                        title:"Description",
                        html:'<textarea id="Description" name="Description" style="height:450px; width:100%;">'
                    },{
                        layout:"column",
                        border:false,
                        items:[{
                            width: 120,
                            border:false,
                            items:[{
                                xtype:"button",
                                text:"Edit Standard Style",
                                handler: function(){
                                    window.open(path + "style.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                                }
                            }]
                        },{
                            width: 150,
                            layout:"form",
                            border:false,
                            style:"padding:0px;",
                            items:[{
                                id:"UseStandardFooter",
                                xtype:"checkbox",
                                labelWidth: 0,
                                labelSeparator: '',
                                fieldLabel:"",
                                labelStyle: 'height:0px;padding:0px;',
                                style:"padding:0px;",
                                boxLabel:"Use Standard Style",
                                name:"UseStandardFooter",
                                inputValue:1
                            }]
                        },{
                            width: 150,
                            border:false,
                            items:[{
                                xtype:"button",
                                text:"Preview Description",
                                handler: function(){
                                    Ext.Ajax.request({
                                        url: 'service.php?action=saveTempDescription&type=items&id='+item_id,
                                        params: {
                                            title: Ext.getCmp("Title").getValue(),
                                            description: tinyMCE.get("Description").getContent(),
                                            sku: Ext.getCmp("SKU").getValue()
                                        },
                                        success: function(a, b){
                                            window.open(path + "preview.php?type=items&u="+Ext.getCmp("UseStandardFooter").getValue()+"&id="+item_id+"&sku="+Ext.getCmp("SKU").getValue(),"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                                        }
                                    })    
                                }
                            }]
                        }]
                    }]
                  },{
                        xtype:"textfield",
                        fieldLabel:"<font color='red'>SKU</font>",
                        emptyText: "Multi Value",
                        id:"SKU",
                        name:"SKU",
                        readOnly: true
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
                                    emptyText: "Multi Value",
                                    id:"StartPrice",
                                    name:"StartPrice"
                                  },{
                                    xtype:"numberfield",
                                    fieldLabel:"Buy It Now Price",
                                    emptyText: "Multi Value",
                                    id:"BuyItNowPrice",
                                    name:"BuyItNowPrice"
                                  },{
                                    id:"Quantity",
                                    xtype:"numberfield",
                                    fieldLabel:"Quantity",
                                    emptyText: "Multi Value",
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
                                    emptyText: "Multi Value",
                                    id:"ReservePrice",
                                    name:"ReservePrice"
                                  },{
                                    xtype:"combo",
                                    fieldLabel:"Duration",
                                    emptyText: "Multi Value",
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
                    }]
                  },{
                    xtype:"panel",
                    title:"Listing Upgrades",
                    layout:"column",
                    items:[{
                        columnWidth:0.5,
                        border:false,
                        items:[{
                            xtype:"checkbox",
                            labelWidth: 0,
                            labelSeparator: '',
                            fieldLabel:"",
                            boxLabel:"Gallery Plus",
                            id:"GalleryTypePlus",
                            name:"GalleryTypePlus",
                            inputValue:"1",
                            disabled:true
                        },{
                            xtype:"checkbox",
                            labelWidth: 0,
                            labelSeparator: '',
                            fieldLabel:"",
                            boxLabel:"BoldTitle",
                            name:"BoldTitle",
                            inputValue:"1",
                            disabled:true
                        },{
                            xtype:"checkbox",
                            labelWidth: 0,
                            labelSeparator: '',
                            fieldLabel:"",
                            boxLabel:"Border",
                            name:"Border",
                            inputValue:"1"
                        },{
                            xtype:"checkbox",
                            labelWidth: 0,
                            labelSeparator: '',
                            fieldLabel:"",
                            boxLabel:"Highlight",
                            name:"Highlight",
                            inputValue:"1",
                            disabled:true
                        }]
                    },{
                        columnWidth:0.5,
                        border:false,
                        items:[{
                            xtype:"checkbox",
                            labelWidth: 0,
                            labelSeparator: '',
                            fieldLabel:"",
                            boxLabel:"Featured Plus",
                            name:"Featured",
                            inputValue:"1",
                            disabled:true
                        },{
                            xtype:"checkbox",
                            labelWidth: 0,
                            labelSeparator: '',
                            fieldLabel:"",
                            boxLabel:"Featured First",
                            name:"GalleryTypeFeatured",
                            inputValue:"1",
                            listeners: {"check": function(t, c){
                                    if(c == true){
                                        Ext.getCmp("GalleryTypePlus").setValue(1);
                                        Ext.getCmp("GalleryTypePlus").setDisabled(1);
                                    }else{
                                        Ext.getCmp("GalleryTypePlus").setValue(0);
                                        Ext.getCmp("GalleryTypePlus").setDisabled(0);
                                    }
                                }
                            },
                            disabled:true
                        },{
                            xtype:"checkbox",
                            labelWidth: 0,
                            labelSeparator: '',
                            fieldLabel:"",
                            boxLabel:"HomePageFeatured",
                            name:"HomePageFeatured",
                            inputValue:"1",
                            disabled:true
                        }]
                    }]
                  },{
                    xtype:"panel",
                    title:"Shipping Options",
                    layout:"form",
                    labelAlign:"top",
                    tbar:[{
                        text:"Return Policy",
                        iconCls:"return-policy",
                        handler:function(){
                            
                            var window = new Ext.Window({
                                title:"Please specify a return policy",
                                closeAction:"hide",
                                width:450,
                                layout:"form",
                                labelAlign:"top",
                                items: [{
                                    xtype:"radio",
                                    labelWidth: 0,
                                    labelSeparator: '',
                                    fieldLabel:"",
                                    emptyText: "Multi Value",
                                    boxLabel:"Returns accepted",
                                    id:"ReturnPolicyReturnsAcceptedOption1",
                                    name:"ReturnPolicyReturnsAcceptedOption"
                                    //value:"ReturnsAccepted"
                                },{
                                    xtype:"form",
                                    id:"ReturnPolicyReturns",
                                    style:"padding-left:10px;",
                                    labelAlign:"top",
                                    items:[{
                                            //id:"ReturnPolicyReturnsWithinOption",
                                            xtype:"combo",
                                            fieldLabel:"Item must be returned within",
                                            emptyText: "Multi Value",
                                            store: new Ext.data.SimpleStore({
                                                fields: ["id","name"],
                                                data: [["Days_3", "3 Days"],["Days_7", "7 Days"], ["Days_10", "10 Days"], ["Days_14", "14 Days"], ["Days_30", "30 Days"], ["Days_60", "60 Days"]]
                                            }),
                                            mode: 'local',
                                            valueField: 'id',
                                            displayField: 'name',
                                            triggerAction: 'all',
                                            editable: false,
                                            name: 'ReturnPolicyReturnsWithinOption',
                                            hiddenName:'ReturnPolicyReturnsWithinOption',
                                            listeners:{select : function(c, r, i){
                                                    Ext.getCmp("ReturnPolicyReturnsAcceptedOption1").setValue(1);
                                                }
                                            }
                                        },/*{
                                            xtype:"label",
                                            text:"After the buyer receives the item, it can be returned within the time frame selected."
                                        },*/{
                                            //id:"ReturnPolicyRefundOption",
                                            xtype:"combo",
                                            fieldLabel:"Refund will be given as",
                                            emptyText: "Multi Value",
                                            store: new Ext.data.SimpleStore({
                                                fields: ["id","name"],
                                                data: [["Exchange", "Exchange"],["MerchandiseCredit", "Merchandise Credit"], ["MoneyBack", "Money Back"]]
                                            }),
                                            mode: 'local',
                                            valueField: 'id',
                                            displayField: 'name',
                                            triggerAction: 'all',
                                            editable: false,
                                            name: 'ReturnPolicyRefundOption',
                                            hiddenName:'ReturnPolicyRefundOption'
                                        },{
                                            id:"ReturnPolicyShippingCostPaidByOption1",
                                            xtype:"radio",
                                            fieldLabel: 'Return shipping will be paid by',
                                            boxLabel: 'Buyer',
                                            name: 'ReturnPolicyShippingCostPaidByOption',
                                            inputValue: 'Buyer'    
                                        },{
                                            id:"ReturnPolicyShippingCostPaidByOption2",
                                            xtype:"radio",
                                            fieldLabel: '',
                                            labelSeparator: '',
                                            labelStyle: 'height:0px;padding:0px;',
                                            boxLabel: 'Seller',
                                            name: 'ReturnPolicyShippingCostPaidByOption',
                                            inputValue: 'Seller'    
                                        },{
                                            id:"ReturnPolicyDescription",
                                            xtype:"textarea",
                                            fieldLabel: 'Additional return policy details',
                                            emptyText: "Multi Value",
                                            name: 'ReturnPolicyDescription',
                                            width:400
                                        }]
                                },{
                                    xtype:"radio",
                                    labelWidth: 0,
                                    labelSeparator: '',
                                    fieldLabel:"",
                                    boxLabel:"Returns not accepted",
                                    id:"ReturnPolicyReturnsAcceptedOption2",
                                    name:"ReturnPolicyReturnsAcceptedOption",
                                    //value:"ReturnsNotAccepted",
                                    listeners:{"check":function(t, c){
                                            if(c){
                                                Ext.getCmp("ReturnPolicyReturns").setDisabled(1);
                                            }else{
                                                Ext.getCmp("ReturnPolicyReturns").setDisabled(0);
                                            }
                                        }
                                    }
                                },{
                                    xtype:"label",
                                    text:"Sellers may be required to accept a return if eBay determines that the item is significantly different from what was description in listing."
                                }],
                                buttons:[{
                                    text:"OK",
                                    handler:function(){
                                        Ext.getCmp('ReturnPolicyReturns').getForm().submit({
                                            params: {
                                                ReturnPolicyReturnsAcceptedOption1: Ext.getCmp("ReturnPolicyReturnsAcceptedOption1").getValue(),
                                                ReturnPolicyReturnsAcceptedOption2: Ext.getCmp("ReturnPolicyReturnsAcceptedOption2").getValue()
                                            },
                                            url: 'service.php?action=saveReturnPolicyReturns&item_id='+item_id,
                                            success: function(form, action) {
                                                //Ext.Msg.alert("Success", action.result.msg);
                                                window.close();
                                            },
                                            failure: function(form, action) {
                                                switch (action.failureType) {
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
                                    text:"Cancel",
                                    handler:function(){
                                        window.close();
                                    }
                                }]
                            })
                            
                            window.show();
                        }
                    }],
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
                                    emptyText: "Multi Value",
                                    mode: 'local',
                                    store: shippingServiceStore,
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    //listWidth: 156,
                                    //width: 156,
                                    title:'Select a Shipping Service',
                                    name: 'ShippingService_1',
                                    hiddenName:'ShippingService_1',
                                    //allowBlank: false,
                                    width:150,
                                    listWidth:300
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"numberfield",
                                    fieldLabel:"Cost",
                                    emptyText: "Multi Value",
                                    id:"ShippingServiceCost_1",
                                    name:"ShippingServiceCost_1",
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
                                    name:"ShippingServiceFree_1",
                                    inputValue:"1",
                                    listeners: {
                                        check: function(t, c){
                                            if(c){
                                                Ext.getCmp("ShippingServiceCost_1").disable();
                                            }else{
                                                Ext.getCmp("ShippingServiceCost_1").enable();
                                            }
                                        }
                                    }
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
                                    emptyText: "Multi Value",
                                    title:'Select a Shipping Service',
                                    name:"ShippingService_2",
                                    hiddenName:"ShippingService_2",
                                    mode: 'local',
                                    store: shippingServiceStore,
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    width:150,
                                    listWidth:300
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
                                    emptyText: "Multi Value",
                                    id:"ShippingServiceCost_2",
                                    name:"ShippingServiceCost_2",
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
                                    name:"ShippingServiceFree_2",
                                    inputValue:"1",
                                    listeners: {
                                        check: function(t, c){
                                            if(c){
                                                Ext.getCmp("ShippingServiceCost_2").disable();
                                            }else{
                                                Ext.getCmp("ShippingServiceCost_2").enable();
                                            }
                                        }
                                    }
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
                                    emptyText: "Multi Value",
                                    title:'Select a Shipping Service',
                                    name:"ShippingService_3",
                                    hiddenName:"ShippingService_3",
                                    mode: 'local',
                                    store: shippingServiceStore,
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    width:150,
                                    listWidth:300
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
                                    emptyText: "Multi Value",
                                    id:"ShippingServiceCost_3",
                                    name:"ShippingServiceCost_3",
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
                                    name:"ShippingServiceFree_3",
                                    inputValue:"1",
                                    listeners: {
                                        check: function(t, c){
                                            if(c){
                                                Ext.getCmp("ShippingServiceCost_3").disable();
                                            }else{
                                                Ext.getCmp("ShippingServiceCost_3").enable();
                                            }
                                        }
                                    }
                                  }]
                              }]
                        },{
                            layout:"table",
                            layoutConfig:{
                            columns:2
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
                                    fieldLabel:"Domestic Insurance",
                                    emptyText: "Multi Value",
                                    mode: 'local',
                                    store: ["", "IncludedInShippingHandling", "NotOffered", "Optional", "Required"],
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    name: 'InsuranceOption',
                                    hiddenName:'InsuranceOption',
                                    //allowBlank: false,
                                    width:150,
                                    listWidth:180
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"numberfield",
                                    fieldLabel:"Cost",
                                    emptyText: "Multi Value",
                                    name:"InsuranceFee",
                                    width:60
                                  }]
                            }]    
                        },{
                            xtype:"combo",
                            fieldLabel:"Domestic Handling Time",
                            emptyText: "Multi Value",
                            title:'Select a time Period',
                            name:"DispatchTimeMax",
                            hiddenName:"DispatchTimeMax",
                            mode: 'local',
                            store: ['1','2','3','4','5','10','15','20'],
                            valueField:'id',
                            displayField:'name',
                            triggerAction: 'all',
                            editable: false,
                            selectOnFocus:true,
                            width:150,
                            listWidth:150
                        }],
                        cls: 'my-fieldset',
                        style: 'margin: 10px;',
                        listeners: {
                            render: function(c){
                                ShippingServiceOptionsTypeCombo.render(c.header, 1);
                                c.on('destroy', function(){
                                        ShippingServiceOptionsTypeCombo.destroy();
                                }, c, {single: true});
                            }
                        }
                    },{
                        xtype:"label",
                        text:"International Shipping"
                    },{
                        xtype:"fieldset",
                        title:" ",
                        //autoHeight:true,
                        bodyStyle:"padding:0px;",
                        items:[{
                            layout:"table",
                            layoutConfig:{
                              columns:2
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
                                    emptyText: "Multi Value",
                                    title:'Select a Shipping Service',
                                    name:"InternationalShippingService_1",
                                    hiddenName:"InternationalShippingService_1",
                                    mode: 'local',
                                    store: internationalShippingServiceStore,
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    width:220,
                                    listWidth:300,
                                    listeners: {
                                                "select": function(c, r, i){
                                                    Ext.getCmp("InternationalShippingTo_1").show();
                                                }
                                    }
                                }]
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"numberfield",
                                    fieldLabel:"Cost",
                                    emptyText: "Multi Value",
                                    name:"InternationalShippingServiceCost_1",
                                    width:60
                                  }]
                              },{
                                id:"InternationalShippingTo_1",
                                hidden:true,
                                layout:"form",
                                colspan: 2,
                                items:[{
                                    xtype:"fieldset",
                                    title: 'To',
                                    style: 'margin: 5px;',
                                    items:[{
                                            xtype:"combo",
                                            labelWidth: 0,
                                            labelSeparator: '',
                                            labelStyle:'height:0px;padding:0px;',
                                            fieldLabel:"",
                                            store: ['Custom Locations', 'Worldwide'],
                                            triggerAction: 'all',
                                            editable: false,
                                            selectOnFocus:true,
                                            width: 150,
                                            listWidth: 150,
                                            name:'InternationalShippingToLocations_1',
                                            hiddenName:"InternationalShippingToLocations_1",
                                            listeners: {
                                                "select": function(c, r, i){
                                                    //console.log(c);
                                                    if(c.value == "Custom Locations"){
                                                        Ext.getCmp("InternationalShippingCustom_1").show();
                                                        /*
                                                        Ext.Ajax.request({
                                                            url: 'service.php?action=getShippingLocation&SiteID='+Ext.getCmp("SiteID").getValue(),
                                                            success: function(a, b){
                                                                var temp = Ext.decode(a.responseText);
                                                                //console.log(temp);
                                                                var items = new Array();
                                                                for(var i in temp){
                                                                    if(!Ext.isFunction(temp[i])){
                                                                        items.push(temp[i]);
                                                                    }
                                                                }
                                                                
                                                                var checkboxGroup = new Ext.form.CheckboxGroup({
                                                                    id: "InternationalShippingCustomCheckboxGroup-1",
                                                                    items: items,
                                                                    columns: 3
                                                                })
                                                                
                                                                //console.log(checkboxGroup);
                                                                Ext.getCmp("InternationalShippingCustomForm-1").add(checkboxGroup);
                                                                Ext.getCmp("InternationalShippingCustomForm-1").doLayout();
                                                            },
                                                            failure: function(){
                                                                
                                                            }
                                                        })
                                                        */
                                                    }else{
                                                        Ext.getCmp("InternationalShippingCustom_1").hide();
                                                    }
                                                    
                                                }
                                            }
                                        },{
                                            id:"InternationalShippingCustom_1",
                                            hidden:true,
                                            border:false,
                                            layout:"column",
                                            items:[{
                                                columnWidth:0.3,
                                                layout:"form",
                                                defaults:{
                                                    
                                                },
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Americas",
                                                    name:"Americas_1",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.4,
                                                layout:"form",
                                                defaults:{
                                                    
                                                },
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Europe",
                                                    name:"Europe_1",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                defaults:{
                                                    
                                                },
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Asia",
                                                    name:"Asia_1",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    boxLabel:"Canada",
                                                    name:"CA_1",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.4,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"UK",
                                                    name:"GB_1",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"AU",
                                                    name:"AU_1",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Mexico",
                                                    name:"MX_1",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.4,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Germany",
                                                    name:"DE_1",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Japan",
                                                    name:"JP_1",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"US",
                                                    name:"US_1",
                                                    inputValue:1
                                                }]
                                            }]
                                        }]
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
                                    emptyText: "Multi Value",
                                    title:'Select a Shipping Service',
                                    name:"InternationalShippingService_2",
                                    hiddenName:"InternationalShippingService_2",
                                    mode: 'local',
                                    store: internationalShippingServiceStore,
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    width:220,
                                    listWidth:300,
                                    listeners: {
                                                "select": function(c, r, i){
                                                    Ext.getCmp("InternationalShippingTo_2").show();
                                                }
                                    }
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
                                    emptyText: "Multi Value",
                                    name:"InternationalShippingServiceCost_2",
                                    width:60
                                  }]
                              },{
                                id:"InternationalShippingTo_2",
                                hidden:true,
                                layout:"form",
                                colspan: 2,
                                items:[{
                                    xtype:"fieldset",
                                    title: 'To',
                                    style: 'margin: 5px;',
                                    items:[{
                                            xtype:"combo",
                                            labelWidth: 0,
                                            labelSeparator: '',
                                            labelStyle:'height:0px;padding:0px;',
                                            fieldLabel:"",
                                            store: ['Custom Locations', 'Worldwide'],
                                            triggerAction: 'all',
                                            editable: false,
                                            selectOnFocus:true,
                                            width: 150,
                                            listWidth: 150,
                                            name:'InternationalShippingToLocations_2',
                                            hiddenName:"InternationalShippingToLocations_2",
                                            listeners: {
                                                "select": function(c, r, i){
                                                    //console.log(c);
                                                    if(c.value == "Custom Locations"){
                                                        Ext.getCmp("InternationalShippingCustom_2").show();
                                                    }else{
                                                        Ext.getCmp("InternationalShippingCustom_2").hide();
                                                    }
                                                    
                                                }
                                            }
                                        },{
                                            id:"InternationalShippingCustom_2",
                                            hidden:true,
                                            border:false,
                                            layout:"column",
                                            items:[{
                                                columnWidth:0.3,
                                                layout:"form",
                                                defaults:{
                                                    
                                                },
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Americas",
                                                    name:"Americas_2",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.4,
                                                layout:"form",
                                                defaults:{
                                                    
                                                },
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Europe",
                                                    name:"Europe_2",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                defaults:{
                                                    
                                                },
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Asia",
                                                    name:"Asia_2",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    boxLabel:"Canada",
                                                    name:"CA_2",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.4,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"UK",
                                                    name:"GB_2",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"AU",
                                                    name:"AU_2",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Mexico",
                                                    name:"MX_2",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.4,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Germany",
                                                    name:"DE_2",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Japan",
                                                    name:"JP_2",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"US",
                                                    name:"US_2",
                                                    inputValue:1
                                                }]
                                            }]
                                        }]
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
                                    emptyText: "Multi Value",
                                    title:'Select a Shipping Service',
                                    name:"InternationalShippingService_3",
                                    hiddenName:"InternationalShippingService_3",
                                    mode: 'local',
                                    store: internationalShippingServiceStore,
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    width:220,
                                    listWidth:300,
                                    listeners: {
                                                "select": function(c, r, i){
                                                    Ext.getCmp("InternationalShippingTo_3").show();
                                                }
                                    }
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
                                    emptyText: "Multi Value",
                                    name:"InternationalShippingServiceCost_3",
                                    width:60
                                  }]
                              },{
                                id:"InternationalShippingTo_3",
                                hidden:true,
                                layout:"form",
                                colspan: 2,
                                items:[{
                                    xtype:"fieldset",
                                    title: 'To',
                                    style: 'margin: 5px;',
                                    items:[{
                                            xtype:"combo",
                                            labelWidth: 0,
                                            labelSeparator: '',
                                            labelStyle:'height:0px;padding:0px;',
                                            fieldLabel:"",
                                            store: ['Custom Locations', 'Worldwide'],
                                            triggerAction: 'all',
                                            editable: false,
                                            selectOnFocus:true,
                                            width: 150,
                                            listWidth: 150,
                                            name:'InternationalShippingToLocations_3',
                                            hiddenName:"InternationalShippingToLocations_3",
                                            listeners: {
                                                "select": function(c, r, i){
                                                    //console.log(c);
                                                    if(c.value == "Custom Locations"){
                                                        Ext.getCmp("InternationalShippingCustom_3").show();
                                                    }else{
                                                        Ext.getCmp("InternationalShippingCustom_3").hide();
                                                    }
                                                }
                                            }
                                        },{
                                            id:"InternationalShippingCustom_3",
                                            hidden:true,
                                            border:false,
                                            layout:"column",
                                            items:[{
                                                columnWidth:0.3,
                                                layout:"form",
                                                defaults:{
                                                    
                                                },
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Americas",
                                                    name:"Americas_3",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.4,
                                                layout:"form",
                                                defaults:{
                                                    
                                                },
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Europe",
                                                    name:"Europe_3",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                defaults:{
                                                    
                                                },
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Asia",
                                                    name:"Asia_3",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    boxLabel:"Canada",
                                                    name:"CA_3",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.4,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"UK",
                                                    name:"UK_3",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"AU",
                                                    name:"AU_3",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Mexico",
                                                    name:"MX_3",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.4,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Germany",
                                                    name:"DE_3",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"Japan",
                                                    name:"JP_3",
                                                    inputValue:1
                                                }]
                                            },{
                                                columnWidth:0.3,
                                                layout:"form",
                                                style:"padding-left:8px;",
                                                border:false,
                                                items:[{
                                                    xtype:"checkbox",
                                                    labelWidth: 0,
                                                    labelSeparator: '',
                                                    fieldLabel:"",
                                                    labelStyle: 'height:0px;padding:0px;',
                                                    style:"padding:0px;",
                                                    boxLabel:"US",
                                                    name:"US_3",
                                                    inputValue:1
                                                }]
                                            }]
                                        }]
                                }]
                              }]
                        },{
                            layout:"table",
                            layoutConfig:{
                            columns:2
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
                                    fieldLabel:"International Insurance",
                                    emptyText: "Multi Value",
                                    mode: 'local',
                                    store: ["", "IncludedInShippingHandling", "NotOffered", "Optional", "Required"],
                                    valueField:'id',
                                    displayField:'name',
                                    triggerAction: 'all',
                                    editable: false,
                                    selectOnFocus:true,
                                    name: 'InternationalInsurance',
                                    hiddenName:'InternationalInsurance',
                                    //allowBlank: false,
                                    width:150,
                                    listWidth:180
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"numberfield",
                                    fieldLabel:"Cost",
                                    emptyText: "Multi Value",
                                    name:"InternationalInsuranceFee",
                                    width:60
                                  }]
                            }]    
                        }],
                        cls: 'my-fieldset',
                        style: 'margin: 10px;',
                        listeners: {
                            render: function(c){
                                InternationalShippingServiceOptionTypeCombo.render(c.header, 1);
                                c.on('destroy', function(){
                                        InternationalShippingServiceOptionTypeCombo.destroy();
                                }, c, {single: true});
                            }
                        }
                    },{
                        xtype:"fieldset",
                        title: 'Locations',
                        items:[{
                            xtype:"combo",
                            fieldLabel:"Country",
                            emptyText: "Multi Value",
                            name:"Location",
                            hiddenName:"Location",
                            mode: 'local',
                            store: countriesStore,
                            valueField:'id',
                            displayField:'name',
                            triggerAction: 'all',
                            editable: false,
                            selectOnFocus:true,
                            width:200,
                            listWidth:200
                        },{
                            xtype:"numberfield",
                            fieldLabel:"ZIP Code",
                            emptyText: "Multi Value",
                            name:"PostalCode",
                            width:60
                            
                        }]
                    }]
                  },{
                    xtype:"panel",
                    title:"Payment Method",
                    layout:"form",
                    items:[{
                        xtype:"fieldset",
                        title:"PayPal",
                        autoHeight:true,
                        style: 'margin: 10px;',
                        items:[{
                            xtype:"checkbox",
                            labelSeparator: '',
                            labelStyle: 'height:0px;padding:0px',
                            fieldLabel:"",
                            boxLabel:"Credit crads via PayPal",
                            name:"PayPalPayment",
                            inputValue:1,
                            checked:true,
                            disabled:true
                          },{
                            xtype:"textfield",
                            fieldLabel:"PayPal Account Email",
                            emptyText: "Multi Value",
                            name:"PayPalEmailAddress",
                            width: 250
                          }]
                      }/*,{
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
                      }*/]
                    }]
                }]
            }],
            buttons: [{
                text: "Update Item",
                handler: function(){
                    document.getElementById("Description").value = tinyMCE.get("Description").getContent();
                    itemForm.getForm().submit({
                        clientValidation: true,
                        url: 'service.php?action=updateMultiItem&item_id='+item_id,
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
    
    var itemPanel = new Ext.Panel({
        autoScroll: true,
        //height:750,
        items: itemForm
    })
    
    itemPanel.render(document.body);
    
    tinyMCE.init({
        // General options
        mode : "textareas",
        theme : "advanced",
        plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        //theme_advanced_resizing : true,

        // Example content CSS (should be your site CSS)
        //content_css : "css/content.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "lists/template_list.js"
        //external_link_list_url : "lists/link_list.js",
        //external_image_list_url : "lists/image_list.js",
        //media_external_list_url : "lists/media_list.js",

        // Replace values for the template plugin
        /*
        template_replace_values : {
                username : "Some User",
                staffid : "991234"
        }
        */
    })
})

