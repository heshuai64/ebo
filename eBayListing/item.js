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
    
    var standardStyleTemplateStore = new Ext.data.JsonStore({
        autoLoad :true,
        fields: ['id', 'name'],
        url:'service.php?action=getStandardStyleTemplate'
    })
    
    var standardStyleTemplate = new Ext.form.ComboBox({
        fieldLabel:"Standard Style Template",
        mode: 'local',
        store: standardStyleTemplateStore,
        valueField:'id',
        displayField:'name',
        triggerAction: 'all',
        selectOnFocus:true,
        name: 'StandardStyleTemplateId',
        hiddenName:'StandardStyleTemplateId'
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
        //triggerAction: 'all',
        editable: false,
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
        selectOnFocus:true,
        //name: 'ListingTypeCombo',
        //hiddenName:'ListingTypeCombo',
        width: 150,
        allowBlank:false,
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
    
    /*
    var schedule = new Ext.Panel({                              
        //title:"Schedule",
        layout:"table",
        layoutConfig:{
          columns:25
        },
        defaults:{
          width:26
        }
        //width:600,
    })
    
    var day = 0;
    var time = 0;
    
    var day_array = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
    var day_id_array = [["Mon-am-12-panel", "Mon-am-1-panel", "Mon-am-2-panel", "Mon-am-3-panel", "Mon-am-4-panel", "Mon-am-5-panel", "Mon-am-6-panel", "Mon-am-7-panel", "Mon-am-8-panel", "Mon-am-9-panel", "Mon-am-10-panel", "Mon-am-11-panel",
                         "Mon-pm-12-panel", "Mon-pm-1-panel", "Mon-pm-2-panel", "Mon-pm-3-panel", "Mon-pm-4-panel", "Mon-pm-5-panel", "Mon-pm-6-panel", "Mon-pm-7-panel", "Mon-pm-8-panel", "Mon-pm-9-panel", "Mon-pm-10-panel", "Mon-pm-11-panel"],
                         
                         ["Tue-am-12-panel", "Tue-am-1-panel", "Tue-am-2-panel", "Tue-am-3-panel", "Tue-am-4-panel", "Tue-am-5-panel", "Tue-am-6-panel", "Tue-am-7-panel", "Tue-am-8-panel", "Tue-am-9-panel", "Tue-am-10-panel", "Tue-am-11-panel",
                          "Tue-pm-12-panel", "Tue-pm-1-panel", "Tue-pm-2-panel", "Tue-pm-3-panel", "Tue-pm-4-panel", "Tue-pm-5-panel", "Tue-pm-6-panel", "Tue-pm-7-panel", "Tue-pm-8-panel", "Tue-pm-9-panel", "Tue-pm-10-panel", "Tue-pm-11-panel"],
                         
                         ["Wed-am-12-panel", "Wed-am-1-panel", "Wed-am-2-panel", "Wed-am-3-panel", "Wed-am-4-panel", "Wed-am-5-panel", "Wed-am-6-panel", "Wed-am-7-panel", "Wed-am-8-panel", "Wed-am-9-panel", "Wed-am-10-panel", "Wed-am-11-panel",
                          "Wed-pm-12-panel", "Wed-pm-1-panel", "Wed-pm-2-panel", "Wed-pm-3-panel", "Wed-pm-4-panel", "Wed-pm-5-panel", "Wed-pm-6-panel", "Wed-pm-7-panel", "Wed-pm-8-panel", "Wed-pm-9-panel", "Wed-pm-10-panel", "Wed-pm-11-panel"],
                         
                         ["Thu-am-12-panel", "Thu-am-1-panel", "Thu-am-2-panel", "Thu-am-3-panel", "Thu-am-4-panel", "Thu-am-5-panel", "Thu-am-6-panel", "Thu-am-7-panel", "Thu-am-8-panel", "Thu-am-9-panel", "Thu-am-10-panel", "Thu-am-11-panel",
                          "Thu-pm-12-panel", "Thu-pm-1-panel", "Thu-pm-2-panel", "Thu-pm-3-panel", "Thu-pm-4-panel", "Thu-pm-5-panel", "Thu-pm-6-panel", "Thu-pm-7-panel", "Thu-pm-8-panel", "Thu-pm-9-panel", "Thu-pm-10-panel", "Thu-pm-11-panel"],
                         
                         ["Fri-am-12-panel", "Fri-am-1-panel", "Fri-am-2-panel", "Fri-am-3-panel", "Fri-am-4-panel", "Fri-am-5-panel", "Fri-am-6-panel", "Fri-am-7-panel", "Fri-am-8-panel", "Fri-am-9-panel", "Fri-am-10-panel", "Fri-am-11-panel",
                          "Fri-pm-12-panel", "Fri-pm-1-panel", "Fri-pm-2-panel", "Fri-pm-3-panel", "Fri-pm-4-panel", "Fri-pm-5-panel", "Fri-pm-6-panel", "Fri-pm-7-panel", "Fri-pm-8-panel", "Fri-pm-9-panel", "Fri-pm-10-panel", "Fri-pm-11-panel"],
                         
                         ["Sat-am-12-panel", "Sat-am-1-panel", "Sat-am-2-panel", "Sat-am-3-panel", "Sat-am-4-panel", "Sat-am-5-panel", "Sat-am-6-panel", "Sat-am-7-panel", "Sat-am-8-panel", "Sat-am-9-panel", "Sat-am-10-panel", "Sat-am-11-panel",
                          "Sat-pm-12-panel", "Sat-pm-1-panel", "Sat-pm-2-panel", "Sat-pm-3-panel", "Sat-pm-4-panel", "Sat-pm-5-panel", "Sat-pm-6-panel", "Sat-pm-7-panel", "Sat-pm-8-panel", "Sat-pm-9-panel", "Sat-pm-10-panel", "Sat-pm-11-panel"],
                         
                         ["Sun-am-12-panel", "Sun-am-1-panel", "Sun-am-2-panel", "Sun-am-3-panel", "Sun-am-4-panel", "Sun-am-5-panel", "Sun-am-6-panel", "Sun-am-7-panel", "Sun-am-8-panel", "Sun-am-9-panel", "Sun-am-10-panel", "Sun-am-11-panel",
                          "Sun-pm-12-panel", "Sun-pm-1-panel", "Sun-pm-2-panel", "Sun-pm-3-panel", "Sun-pm-4-panel", "Sun-pm-5-panel", "Sun-pm-6-panel", "Sun-pm-7-panel", "Sun-pm-8-panel", "Sun-pm-9-panel", "Sun-pm-10-panel", "Sun-pm-11-panel"]
                        ];
    
    var time_array = ["12 <br> am", "1 <br> am", "2 <br> am", "3 <br> am", "4 <br> am", "5 <br> am", "6 <br> am", "7 <br> am", "8 <br> am", "9 <br> am", "10 <br> am", "11 <br> am", "12 <br> pm",
                "1 <br> pm", "2 <br> pm", "3 <br> pm", "4 <br> pm", "5 <br> pm", "6 <br> pm", "7 <br> pm", "8 <br> pm", "9 <br> pm", "10 <br> pm", "11 <br> pm"];
    var time_id_array = [["Mon-am-12", "Mon-am-1", "Mon-am-2", "Mon-am-3", "Mon-am-4", "Mon-am-5", "Mon-am-6", "Mon-am-7", "Mon-am-8", "Mon-am-9", "Mon-am-10", "Mon-am-11",
                          "Mon-pm-12", "Mon-pm-1", "Mon-pm-2", "Mon-pm-3", "Mon-pm-4", "Mon-pm-5", "Mon-pm-6", "Mon-pm-7", "Mon-pm-8", "Mon-pm-9", "Mon-pm-10", "Mon-pm-11"],
                         
                         ["Tue-am-12", "Tue-am-1", "Tue-am-2", "Tue-am-3", "Tue-am-4", "Tue-am-5", "Tue-am-6", "Tue-am-7", "Tue-am-8", "Tue-am-9", "Tue-am-10", "Tue-am-11",
                          "Tue-pm-12", "Tue-pm-1", "Tue-pm-2", "Tue-pm-3", "Tue-pm-4", "Tue-pm-5", "Tue-pm-6", "Tue-pm-7", "Tue-pm-8", "Tue-pm-9", "Tue-pm-10", "Tue-pm-11"],
                         
                         ["Wed-am-12", "Wed-am-1", "Wed-am-2", "Wed-am-3", "Wed-am-4", "Wed-am-5", "Wed-am-6", "Wed-am-7", "Wed-am-8", "Wed-am-9", "Wed-am-10", "Wed-am-11",
                          "Wed-pm-12", "Wed-pm-1", "Wed-pm-2", "Wed-pm-3", "Wed-pm-4", "Wed-pm-5", "Wed-pm-6", "Wed-pm-7", "Wed-pm-8", "Wed-pm-9", "Wed-pm-10", "Wed-pm-11"],
                         
                         ["Thu-am-12", "Thu-am-1", "Thu-am-2", "Thu-am-3", "Thu-am-4", "Thu-am-5", "Thu-am-6", "Thu-am-7", "Thu-am-8", "Thu-am-9", "Thu-am-10", "Thu-am-11",
                          "Thu-pm-12", "Thu-pm-1", "Thu-pm-2", "Thu-pm-3", "Thu-pm-4", "Thu-pm-5", "Thu-pm-6", "Thu-pm-7", "Thu-pm-8", "Thu-pm-9", "Thu-pm-10", "Thu-pm-11"],
                         
                         ["Fri-am-12", "Fri-am-1", "Fri-am-2", "Fri-am-3", "Fri-am-4", "Fri-am-5", "Fri-am-6", "Fri-am-7", "Fri-am-8", "Fri-am-9", "Fri-am-10", "Fri-am-11",
                          "Fri-pm-12", "Fri-pm-1", "Fri-pm-2", "Fri-pm-3", "Fri-pm-4", "Fri-pm-5", "Fri-pm-6", "Fri-pm-7", "Fri-pm-8", "Fri-pm-9", "Fri-pm-10", "Fri-pm-11"],
                         
                         ["Sat-am-12", "Sat-am-1", "Sat-am-2", "Sat-am-3", "Sat-am-4", "Sat-am-5", "Sat-am-6", "Sat-am-7", "Sat-am-8", "Sat-am-9", "Sat-am-10", "Sat-am-11",
                          "Sat-pm-12", "Sat-pm-1", "Sat-pm-2", "Sat-pm-3", "Sat-pm-4", "Sat-pm-5", "Sat-pm-6", "Sat-pm-7", "Sat-pm-8", "Sat-pm-9", "Sat-pm-10", "Sat-pm-11"],
                         
                         ["Sun-am-12", "Sun-am-1", "Sun-am-2", "Sun-am-3", "Sun-am-4", "Sun-am-5", "Sun-am-6", "Sun-am-7", "Sun-am-8", "Sun-am-9", "Sun-am-10", "Sun-am-11",
                          "Sun-pm-12", "Sun-pm-1", "Sun-pm-2", "Sun-pm-3", "Sun-pm-4", "Sun-pm-5", "Sun-pm-6", "Sun-pm-7", "Sun-pm-8", "Sun-pm-9", "Sun-pm-10", "Sun-pm-11"]
                        ];
    
    for(var i = 1; i <= 175; i++){
        if(i == (25 * day) + 1){
            schedule.add({
                width: 60,
                html: day_array[day],
                border: false
            });
        }else{ 
            schedule.add({
                html: time_array[time],
                id: day_id_array[day][time],
                bodyCssClass:"schedule-time",
                layout:"form",
                items:[{
                    xtype:"hidden",
                    id:time_id_array[day][time],
                    name:time_id_array[day][time],
                    value: 0
                }]
            });
            
            time++;
            if(time == 24){
                time = 0;
            }
        }
        
        if(i % 25 == 0){
            day++;
        }
    }
    */
    
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
        reader:new Ext.data.JsonReader({
            }, ['Id','AutoPay','BuyItNowPrice','CategoryMappingAllowed','Country','Currency','Description','DispatchTimeMax','ListingDuration','ListingType','Location','PaymentMethods','PayPalEmailAddress',
                'PostalCode','PrimaryCategoryCategoryID','PrimaryCategoryCategoryName','Quantity','ReturnPolicyDescription','ReturnPolicyReturnsAcceptedOption','ReturnPolicyReturnsAcceptedOption','ReturnPolicyReturnsWithinOption',
                'ReturnPolicyShippingCostPaidByOption','ReservePrice','CurrentPrice','ListingStatus','ScheduleTime','SecondaryCategoryCategoryID','SecondaryCategoryCategoryName','Site','Motors','SiteID','SKU','StartPrice',
                'StoreCategory2ID','StoreCategory2Name','StoreCategoryID','StoreCategoryName','SubTitle','Title','UserID','BoldTitle','Border','Featured','Highlight','HomePageFeatured','GalleryTypeFeatured','GalleryTypeGallery','GalleryTypePlus','GalleryURL',
                'picture_1','picture_2','picture_3','picture_4','picture_5','picture_6','picture_7','picture_8',
                'picture_9','picture_10','PhotoDisplay',
                'ShippingServiceOptionsType','InsuranceOption','InsuranceFee',
                'InternationalShippingServiceOptionType','InternationalInsurance','InternationalInsuranceFee',
                'ShippingService_1','ShippingServiceCost_1','ShippingServiceFree_1',
                'ShippingService_2','ShippingServiceCost_2','ShippingServiceFree_2',
                'ShippingService_3','ShippingServiceCost_3','ShippingServiceFree_3',
                'InternationalShippingService_1','InternationalShippingServiceCost_1',
                'InternationalShippingService_2','InternationalShippingServiceCost_2',
                'InternationalShippingService_3','InternationalShippingServiceCost_3',
                'InternationalShippingToLocations_1','InternationalShippingToLocations_2','InternationalShippingToLocations_3',
                'Americas_1','US_1','Europe_1','Asia_1','CA_1','GB_1','AU_1','MX_1','DE_1','JP_1',
                'Americas_2','US_2','Europe_2','Asia_2','CA_2','GB_2','AU_2','MX_2','DE_2','JP_2',
                'Americas_3','US_3','Europe_3','Asia_3','CA_3','GB_3','AU_3','MX_3','DE_3','JP_3',
                'accountId','StandardStyleTemplateId','UseStandardFooter'
        ]),
        items:[{
                layout:"column",
                border:false,
                width: 400,
                items:[{
                    columnWidth:0.3,
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
                        //allowBlank: false,
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
                    columnWidth:0.3,
                    layout:"form",
                    defaults:{
                        width: 80,
                        listWidth: 80
                    },
                    border:false,
                    items: currencyCombo
                },{
                    columnWidth:0.3,
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
                                                        if(temp.Attribute[i].fieldLabel != "Brand"){
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
                                                        }
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
                        name:"Title",
                        maxLength: 55,
                        allowBlank:false,
                        enableKeyEvents:true,
                        listeners: {
                            keydown: function(t, e){
                                if(e.getKey() == 32 && TitleBlankMark == true ){
                                    //console.log("mark");
                                    e.stopEvent();
                                }
                                
                                if(e.getKey() == 32){
                                    TitleBlankMark = true;
                                }else{
                                    TitleBlankMark = false;
                                }
                            },
                            blur : function(t){
                                if(TitleBlankMark == true){
                                    t.setValue(t.getValue().substring(0, t.getValue().length - 1));
                                    TitleBlankMark = false;
                                }
                            }
                        }
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
                                //editable:false,
                                name:"PrimaryCategoryCategoryName",
                                hiddenName:"PrimaryCategoryCategoryName",
                                width: 600,
                                listWidth: 600,
                                allowBlank:false,
                                
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
                                editable:false,
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
                                editable:false,
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
                    },'-',pictureManage],
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
                        title:"standard Style Template",
                        items:[/*{
                            width: 180,
                            border:false,
                            items:[{
                                xtype:"button",
                                text:"Copy Description From Inventory",
                                handler: function(){
                                    if(Ext.isEmpty(Ext.getCmp("SiteID").getValue())){
                                        Ext.Msg.alert('Warn', 'Please choice Site.');
                                    }else{
                                        Ext.Ajax.request({
                                            url: inventory_service + '?action=getSkuDescription&site=' + Ext.getCmp("SiteID").getValue() + '&sku=' + sku,
                                            success: function(a, b){
                                                //console.log(a);
                                                //document.getElementById("Description").value = a.responseText;
                                                tinyMCE.get("Description").setContent(a.responseText);
                                            }
                                        })
                                    }
                                }
                            }]
                        },*/standardStyleTemplate,{
                            width: 110,
                            border:false,
                            items:[{
                                xtype:"button",
                                text:"Add Standard Style",
                                handler: function(){
                                    window.open(path + "style.php?id=0","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                                }
                            }]
                        },{
                            width: 110,
                            border:false,
                            items:[{
                                xtype:"button",
                                text:"Edit Standard Style",
                                handler: function(){
                                    if(!Ext.isEmpty(standardStyleTemplate.getValue())){
                                        window.open(path + "style.php?id="+standardStyleTemplate.getValue(),"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                                    }else{
                                        Ext.Msg.alert('Warn', 'Please first select standard style template.');
                                    }
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
                                            window.open(path + "preview.php?type=items&u="+Ext.getCmp("UseStandardFooter").getValue()+"&id="+item_id+"&sku="+Ext.getCmp("SKU").getValue()+"&standardStyleTemplateId="+standardStyleTemplate.getValue(),"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                                        }
                                    })
                                }
                            }]
                        }]
                    }/*{
                        //autoScroll: true,
                        id:"Description",
                        width: 700,
                        height: 500,
                        xtype:"htmleditor",
                        fieldLabel:"Descritpion",
                        name:"Description",
                        allowBlank:false,
                        listeners: {
                            sync : function(t, h){
                                //console.log(h);
                                Ext.Ajax.request({
                                    url: 'service.php?action=saveTempDescription&type=items&id='+item_id,
                                    params: { description: h}
                                })
                            }
                        }
                    },{
                        layout:"column",
                        border:false,
                        items:[{
                            columnWidth:0.2,
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
                                boxLabel:"Use Standard Footer",
                                name:"UseStandardFooter",
                                inputValue:1
                            }]
                        },{
                            columnWidth:0.2,
                            border:false,
                            items:[{
                                xtype:"button",
                                text:"Edit Standard Footer",
                                handler: function(){
                                    window.open(path + "footer.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                                }
                            }]
                        },{
                            columnWidth:0.2,
                            border:false,
                            items:[{
                                xtype:"button",
                                text:"Preview",
                                handler: function(){
                                    window.open(path + "preview.php?type=items&u="+Ext.getCmp("UseStandardFooter").getValue()+"&id="+item_id,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1024, height=768");
                                }
                            }]
                        }]
                    }*/]
                  },/*{
                    xtype:"panel",
                    title:"Schedule",
                    layout:"form",
                    //labelAlign:"left",
                    //labelPad:0,
                    items:[{
                            layout:"column",
                            border:false,
                            items:[{
                                columnWidth:0.5,
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
                                columnWidth:0.5,
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
                            }]
                    },{
                            xtype:"panel",
                            items: schedule
                    }]
                  },*/{
                        xtype:"textfield",
                        fieldLabel:"<font color='red'>SKU</font>",
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
                                    name:"StartPrice"
                                  },{
                                    xtype:"numberfield",
                                    fieldLabel:"Buy It Now Price",
                                    id:"BuyItNowPrice",
                                    name:"BuyItNowPrice"
                                  },{
                                    xtype:"numberfield",
                                    fieldLabel:"Quantity",
                                    id:"Quantity",
                                    name:"Quantity",
                                    allowBlank:false,
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
                                    //allowBlank: false,
                                    hiddenName:'ListingDuration',
                                    allowBlank:false
                                  }/*,{
                                    xtype:"numberfield",
                                    fieldLabel:"Lowest Price",
                                    disabled:true,
                                    id:'LowPrice',
                                    name: 'LowPrice'
                                }*/]
                              }]
                        }],
                        cls: 'my-fieldset',
                        style: 'margin: 10px;',
                        listeners: {
                            render: function(c){
                                /*
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
                                    name: 'ListingType',
                                    hiddenName:'ListingType',
                                    width: 150,
                                    allowBlank:false,
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
                                            
                                            //document.getElementById("ListingType").value = r.data.name;
                                            listingDurationStore.load({params: {id: r.data.id}});
                                        }
                                    }
                                });
                                */
                                
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
                                    boxLabel:"Returns accepted",
                                    id:"ReturnPolicyReturnsAcceptedOption1",
                                    name:"ReturnPolicyReturnsAcceptedOption"
                                    //value:"ReturnsAccepted"
                                },{
                                    xtype:"form",
                                    id:"ReturnPolicyReturns",
                                    style:"padding-left:10px;",
                                    labelAlign:"top",
                                    reader:new Ext.data.JsonReader({
                                        }, ['ReturnPolicyReturnsAcceptedOption','ReturnPolicyReturnsWithinOption',
                                            'ReturnPolicyRefundOption','ReturnPolicyShippingCostPaidByOption',
                                            'ReturnPolicyDescription'
                                    ]),
                                    items:[{
                                            //id:"ReturnPolicyReturnsWithinOption",
                                            xtype:"combo",
                                            fieldLabel:"Item must be returned within",
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
                            
                            Ext.getCmp('ReturnPolicyReturns').getForm().load({
                                url:'service.php?action=loadReturnPolicyReturns', 
                                method:'GET', 
                                params: {item_id: item_id}, 
                                waitMsg:'Please wait...',
                                success: function(f, a){
                                    if(a.result.data.ReturnPolicyReturnsAcceptedOption == "ReturnsAccepted"){
                                        Ext.getCmp("ReturnPolicyReturnsAcceptedOption1").setValue(true);
                                    }else if(a.result.data.ReturnPolicyReturnsAcceptedOption == "ReturnsNotAccepted"){
                                        Ext.getCmp("ReturnPolicyReturnsAcceptedOption2").setValue(true);
                                    }
                                }
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
                                    name:"InsuranceFee",
                                    width:60
                                  }]
                            }]    
                        },{
                            xtype:"combo",
                            fieldLabel:"Domestic Handling Time",
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
                            listWidth:150,
                            allowBlank:false
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
                        url: 'service.php?action=updateItem&item_id='+item_id+'&status='+status,
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
    
    itemForm.getForm().load({
            url:'service.php?action=getItem', 
            method:'GET', 
            params: {id: item_id}, 
            waitMsg:'Please wait...',
            success: function(f, a){
                //console.log(a.result.data);
                switch(a.result.data.Site){
                    case "US":
                       categoryStore.setBaseParam('SiteID', 0);
                    break;
                
                    case "UK":
                      categoryStore.setBaseParam('SiteID', 3);
                    break;
                
                    case "Australia":
                        categoryStore.setBaseParam('SiteID', 15);
                    break;
                
                    case "France":
                        categoryStore.setBaseParam('SiteID', 71);
                    break;
                
                    case "Germany":
                        categoryStore.setBaseParam('SiteID', 77);
                        Ext.getCmp("Motors").show();
                    break;
                }
                
                document.getElementById("Description").value  = a.result.data.Description;
                
                listTypeCombo.setValue(a.result.data.ListingType);
                ShippingServiceOptionsTypeCombo.setValue(a.result.data.ShippingServiceOptionsType);
                shippingServiceStore.load({params: {serviceType: a.result.data.ShippingServiceOptionsType, SiteID: Ext.getCmp("SiteID").getValue()}});
                
                InternationalShippingServiceOptionTypeCombo.setValue(a.result.data.InternationalShippingServiceOptionType);
                internationalShippingServiceStore.load({params: {serviceType: a.result.data.InternationalShippingServiceOptionType, SiteID: Ext.getCmp("SiteID").getValue()}});
                
                //console.log(a.result.data.InternationalShippingService_1);
                if(!Ext.isEmpty(a.result.data.InternationalShippingService_1)){
                    Ext.getCmp("InternationalShippingTo_1").show();
                    if(a.result.data.InternationalShippingToLocations_1 == "Custom Locations"){
                        Ext.getCmp("InternationalShippingCustom_1").show();
                    }
                }
                
                if(!Ext.isEmpty(a.result.data.InternationalShippingService_2)){
                    Ext.getCmp("InternationalShippingTo_2").show();
                    if(a.result.data.InternationalShippingToLocations_2 == "Custom Locations"){
                        Ext.getCmp("InternationalShippingCustom_2").show();
                    }
                }
                
                if(!Ext.isEmpty(a.result.data.InternationalShippingService_3)){
                    Ext.getCmp("InternationalShippingTo_3").show();
                    if(a.result.data.InternationalShippingToLocations_3 == "Custom Locations"){
                        Ext.getCmp("InternationalShippingCustom_3").show();
                    }
                }
                
                for(var i = 1; i <= 10; i++){
                    //console.log(document.getElementById("picture_"+i).value);
                    if(!Ext.isEmpty(document.getElementById("picture_"+i).value)){
                        Ext.getCmp("picture_panel_"+i).body.dom.innerHTML = '<img width="60" height="60" src="' + document.getElementById("picture_"+i).value + '"/>';
                    }
                }
                
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
            }
        }
    );
    
    /*
    //Schedule Time  --------------------------------------------------------------------------------
    Ext.select(".schedule-time").on("click",function(e, el){
        var tempArray = el.childNodes[0].id.split("-");
        
        var timeStore = new Ext.data.JsonStore({
            //root:"timeList",
            autoLoad:true,
            url:'service.php?action=getScheduleTime&item_id='+item_id+'&dayTime='+el.childNodes[0].id,
            fields: [{name:'time', type:'string'}],
            sortInfo: {
                field: 'time',
                direction: 'ASC' // or 'DESC' (case sensitive for local sorting)
            },
            listeners: {"load": function(t, r){
                    //console.log(r);
                }
            }
        });


        var timeList = new Ext.ListView({
            store: timeStore,
            multiSelect: true,
            //emptyText: 'No time to display',
            //height:150,
            reserveScrollOffset: true,
            //autoHeight:true,
            columns: [{
                //width:150,
                //width:.8,
                header: ' ',
                dataIndex: 'time'
            }]
        });

        var timeWindow = new Ext.Window({
            title:String.format('{0} {1}:00:00 {2} - {1}:59:59 {2} schedule', tempArray[0], tempArray[2], tempArray[1]),
            width:400,
            height: 213,
            items:[{
                layout:"column",
                //height:150,
                items:[{
                    columnWidth:0.5,
                    title:"List of Start Times",
                    height:150,
                    autoScroll:true,
                    items:timeList
                    },{
                    columnWidth:0.5,
                    title:"New start time",
                    items:[{
                        id:"nowStartTime",
                        xtype:"timefield",
                        increment:1,
                        triggerAction: 'all',
                        editable: false,
                        selectOnFocus:true,
                        minValue: tempArray[2] + ":00 " + tempArray[1].toUpperCase(),
                        maxValue: tempArray[2] + ":59 " + tempArray[1].toUpperCase()
                    },{
                        xtype:"button",
                        text:"Add",
                        handler: function(){
                            if(Ext.getCmp("nowStartTime").getValue != ""){
                                Ext.Ajax.request({
                                    url: 'service.php?action=addScheduleTime',
                                    success: function(){
                                            timeStore.reload();
                                        },
                                    failure: function(){},
                                    params: {
                                            dayTime: el.childNodes[0].id,
                                            item_id: item_id,
                                            time: Ext.getCmp("nowStartTime").getValue()
                                        }
                                });
                            }
                        }
                    }]
                }]
            },{
                layout:"column",
                items:[{
                    columnWidth:0.25,
                    border:false,
                    items:[{
                        xtype:"button",
                        text:"Delete",
                        handler: function(){
                            if(timeList.getSelectionCount() > 0){
                                var id = "";
                                var selectedIndexes = timeList.getSelectedIndexes();
                                for(var i in selectedIndexes){
                                    if(!Ext.isFunction(selectedIndexes[i])){
                                        id = id + selectedIndexes[i] + ",";
                                    }
                                }
                                //console.log(id);
                                Ext.Ajax.request({
                                    url: 'service.php?action=deleteScheduleTime',
                                    success: function(){
                                            timeStore.reload();
                                        },
                                    failure: function(){},
                                    params: {
                                            dayTime: el.childNodes[0].id,
                                            item_id: item_id,
                                            id: id
                                        }
                                });
                            }
                            //console.log(timeList.getSelectedIndexes());
                            //timeStore.reload();
                        }
                    }]
                },{
                    columnWidth:0.25,
                    border:false,
                    items:[{
                        xtype:"button",
                        text:"Delete All",
                        handler: function(){
                            Ext.Ajax.request({
                                url: 'service.php?action=deleteAllScheduleTime',
                                success: function(){
                                        timeStore.reload();
                                    },
                                failure: function(){},
                                params: {
                                        dayTime: el.childNodes[0].id,
                                        item_id: item_id
                                    }
                            });
                        }
                    }]
                },{
                    columnWidth:0.25,
                    border:false,
                    items:[{
                        xtype:"button",
                        text:"Ok",
                        handler: function(){
                            Ext.Ajax.request({
                                url: 'service.php?action=saveScheduleTime',
                                success: function(){
                                        if(timeStore.getCount() > 0){
                                            Ext.getCmp(el.childNodes[0].id + "-panel").body.applyStyles("background-color:red;");
                                            Ext.getCmp(el.childNodes[0].id).setValue(1)
                                        }else{
                                            Ext.getCmp(el.childNodes[0].id + "-panel").body.applyStyles("background-color:white;");
                                            Ext.getCmp(el.childNodes[0].id).setValue(0)
                                        }
                                        timeWindow.close();
                                    },
                                failure: function(){},
                                params: {
                                        dayTime: el.childNodes[0].id,
                                        item_id: item_id
                                    }
                            });
                        }
                    }]
                },{
                    columnWidth:0.25,
                    border:false,
                    items:[{
                        xtype:"button",
                        text:"Canel",
                        handler: function(){
                            timeWindow.close();
                        }
                    }]
                }]
            }]
        })
        
        //console.log(tempArray[2] + ":59:59 AM");
        timeWindow.show();
    })
    */
})

