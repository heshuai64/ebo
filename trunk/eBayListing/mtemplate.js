Ext.onReady(function(){
    Ext.BLANK_IMAGE_URL = "../ext-3.0.0/resources/images/default/s.gif";
    //var categoryPath = "";
    var today = new Date();
    //var today = new Date();
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
                emptyText: "Multi Value",
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
                emptyText: "Multi Value",
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
                emptyText: "Multi Value",
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
                emptyText: "Multi Value",
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
                emptyText: "Multi Value",
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
                emptyText: "Multi Value",
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
                emptyText: "Multi Value",
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
                emptyText: "Multi Value",
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
                emptyText: "Multi Value",
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
                emptyText: "Multi Value",
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
    
    var scheduleTemplateStore = new Ext.data.JsonStore({
        autoLoad :true,
        fields: ['id', 'name'],
        url:'service.php?action=getScheduleTemplate'
    })
    
    var schedule_template = new Ext.form.ComboBox({
        fieldLabel:"Schedule Template",
        mode: 'local',
        store: scheduleTemplateStore,
        valueField:'id',
        displayField:'name',
        triggerAction: 'all',
        selectOnFocus:true,
        name: 'scheduleTemplateName',
        hiddenName:'scheduleTemplateName'
    })
    
    var shippingTemplateStore = new Ext.data.JsonStore({
        autoLoad :true,
        fields: ['id', 'name'],
        url:'service.php?action=getShippingTemplate'
    })
    
    var shippingTemplateCombo = new Ext.form.ComboBox({
        fieldLabel:"Shipping Template",
        mode: 'local',
        store: shippingTemplateStore,
        valueField:'id',
        displayField:'name',
        triggerAction: 'all',
        selectOnFocus:true,
        name: 'shippingTemplateName',
        hiddenName:'shippingTemplateName'
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
        selectOnFocus:true,
        emptyText: "Multi Value",
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
        emptyText: "Multi Value",
        displayField:'name',
        triggerAction: 'all',
        editable: false,
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
                        //Ext.getCmp("Quantity").setDisabled(1);
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
        id: 'ShippingServiceOptionsType',
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
        id: 'InternationalShippingServiceOptionType',
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
                        emptyText: "Multi Value",
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
                                
                                    case "Germany":
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
                                                        url: 'service.php?action=saveSpecifics&template_id='+template_id,
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
                                            url: 'service.php?action=loadSpecifics&AttributeSetID='+temp.CharacteristicsSetId+'&template_id='+template_id,
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
                                        url: 'service.php?action=saveTempDescription&type=template&id='+template_id,
                                        params: {
                                            title: Ext.getCmp("Title").getValue(),
                                            description: tinyMCE.get("Description").getContent(),
                                            sku: Ext.getCmp("SKU").getValue()
                                        },
                                        success: function(a, b){
                                            window.open(path + "preview.php?type=template&u="+Ext.getCmp("UseStandardFooter").getValue()+"&id="+template_id+"&sku="+Ext.getCmp("SKU").getValue(),"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                                        }
                                    })
                                }
                            }]
                        }]
                    }]
                  },{
                    xtype:"panel",
                    title:"Schedule",
                    layout:"form",
                    //labelAlign:"left",
                    //labelPad:0,
                    items:[{
                            layout:"column",
                            border:false,
                            items:[{
                                columnWidth:0.15,
                                layout:"form",
                                defaults:{
                                    width: 90,
                                    listWidth: 90
                                },
                                border:false,
                                items:[{xtype:"datefield",
                                        fieldLabel:"Start Date",
                                        name:"ScheduleStartDate",
                                        minValue: today.format("Y-m-d"),
                                        triggerAction: 'all',
                                        editable: false,
                                        selectOnFocus:true,
                                        format : 'Y-m-d'
                                        //allowBlank:false
                                    }]
                            },{
                                columnWidth:0.15,
                                layout:"form",
                                defaults:{
                                    width: 90,
                                    listWidth: 90
                                },
                                border:false,
                                items:[{ xtype:"datefield",
                                        fieldLabel:"End Date",
                                        name:"ScheduleEndDate",
                                        minValue: today.format("Y-m-d"),
                                        triggerAction: 'all',
                                        editable: false,
                                        selectOnFocus:true,
                                        format : 'Y-m-d'
                                        //allowBlank:false
                                    }]
                            },{
                                columnWidth:0.2,
                                layout:"form",
                                defaults:{
                                    width: 120,
                                    listWidth: 120
                                },
                                border:false,
                                items: schedule_template
                            },{
                                columnWidth:0.25,
                                layout:"form",
                                defaults:{
                                    width: 120,
                                    listWidth: 120
                                },
                                border:false,
                                items:[{
                                        xtype:"button",
                                        text:"Add Schedule Template",
                                        icon:"images/date_add.png",
                                        style:"margin-top: 15px;",
                                        handler: function(){
                                            window.open(path + "scheduleTemplate.php?name=heshuai-temp","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=700, height=300");
                                        }
                                    }]
                            },{
                                columnWidth:0.25,
                                layout:"form",
                                defaults:{
                                    width: 120,
                                    listWidth: 120
                                },
                                border:false,
                                items:[{
                                        xtype:"button",
                                        text:"Edit Select Schedule Template",
                                        icon:"images/date_edit.png",
                                        style:"margin-top: 15px;",
                                        handler: function(){
                                            if(!Ext.isEmpty(schedule_template.getValue())){
                                                window.open(path + "scheduleTemplate.php?name="+schedule_template.getValue(),"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=700, height=300");
                                            }else{
                                                Ext.Msg.alert('Warn', 'Please first select schedule template.');
                                            }
                                        }
                                    }]
                            }]
                    }]
                  },{
                        xtype:"textfield",
                        fieldLabel:"<font color='red'>SKU</font>",
                        emptyText: "Multi Value",
                        id:"SKU",
                        name:"SKU"
                    },{
                        xtype: 'combo',
                        fieldLabel:"Template Category",
                        emptyText: "Multi Value",
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
                                    xtype:"numberfield",
                                    fieldLabel:"Quantity",
                                    emptyText: "Multi Value",
                                    id:"Quantity",
                                    name:"Quantity",
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
                    title:"Shipping Template",
                    layout:"form",
                    //labelAlign:"top",
                    items:[{
                                items: shippingTemplateCombo
                            },{
                                xtype:"button",
                                text:"Add Shipping Template",
                                icon:"images/lorry_add.png",
                                style:"margin-top: 15px;",
                                handler: function(){
                                    window.open(path + "shippingTemplate.php?name=heshuai-temp","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=600, height=400");
                                }
                                    
                            },{
                                xtype:"button",
                                text:"Edit Select Shipping Template",
                                icon:"images/lorry_link.png",
                                style:"margin-top: 15px;",
                                handler: function(){
                                    if(!Ext.isEmpty(Ext.getCmp("SiteID").getValue())){
                                        if(!Ext.isEmpty(shippingTemplateCombo.getValue())){
                                            window.open(path + "shippingTemplate.php?name="+shippingTemplateCombo.getValue()+"&site="+Ext.getCmp("SiteID").getValue(),"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=600, height=400");
                                        }else{
                                            Ext.Msg.alert('Warn', 'Please first select a shipping template.');
                                        }
                                    }else{
                                        Ext.Msg.alert('Warn', 'Please first select Site.');
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
                text: "Update Template",
                handler: function(){
                    document.getElementById("Description").value = tinyMCE.get("Description").getContent();
                    itemForm.getForm().submit({
                        clientValidation: true,
                        url: 'service.php?action=updateMultiTemplate&template_id='+template_id,
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

