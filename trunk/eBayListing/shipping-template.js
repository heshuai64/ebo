Ext.onReady(function(){
    Ext.BLANK_IMAGE_URL = "../ext-3.0.0/resources/images/default/s.gif";
    
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
    Ext.Ajax.on('requestexception', hideWait);
    
    var countriesStore = new Ext.data.JsonStore({
        autoLoad :true,
        fields: ['id', 'name'],
        url:'service.php?action=getAllCountries'
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
    
    var ShippingServiceOptionsTypeCombo = new Ext.form.ComboBox({
        id: 'ShippingServiceOptionsType',
        store: ['Flat', 'Calculated'],
        triggerAction: 'all',
        editable: false,
        width: 150,
        listWidth: 150,
        listeners: {
            "select": function(c, r, i){
                shippingServiceStore.load({params: {serviceType: c.value, SiteID: site}});
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
                internationalShippingServiceStore.load({params: {serviceType: c.value, SiteID: site}});
            }
        }
    });
    
    var shipping = new Ext.FormPanel({          
        title:"Shipping Template",
        reader:new Ext.data.JsonReader({
            }, ['Location','PostalCode','DispatchTimeMax','ShippingServiceOptionsType','InsuranceOption','InsuranceFee',
                'InternationalShippingServiceOptionType','InternationalInsurance','InternationalInsuranceFee',
                'ShippingService_1','ShippingServiceCost_1','ShippingServiceAdditionalCost_1','ShippingServiceFree_1',
                'ShippingService_2','ShippingServiceCost_2','ShippingServiceAdditionalCost_2','ShippingServiceFree_2',
                'ShippingService_3','ShippingServiceCost_3','ShippingServiceAdditionalCost_3','ShippingServiceFree_3',
                'InternationalShippingService_1','InternationalShippingServiceCost_1','InternationalShippingServiceAdditionalCost_1',
                'InternationalShippingService_2','InternationalShippingServiceCost_2','InternationalShippingServiceAdditionalCost_2',
                'InternationalShippingService_3','InternationalShippingServiceCost_3','InternationalShippingServiceAdditionalCost_3',
                'InternationalShippingToLocations_1','InternationalShippingToLocations_2','InternationalShippingToLocations_3',
                'Americas_1','US_1','Europe_1','Asia_1','CA_1','GB_1','AU_1','MX_1','DE_1','JP_1',
                'Americas_2','US_2','Europe_2','Asia_2','CA_2','GB_2','AU_2','MX_2','DE_2','JP_2',
                'Americas_3','US_3','Europe_3','Asia_3','CA_3','GB_3','AU_3','MX_3','DE_3','JP_3']
        ),
        labelAlign:"top",
        width: 780,
        tbar:[{
            text:"Return Policy",
            icon:"images/page_edit.png",
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
                                url: 'service.php?action=saveReturnPolicyReturns&template_id='+template_id,
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
                    params: {template_id: template_id}, 
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
            layout:"column",
            border:false,
            items:[{
                columnWidth:0.5,
                layout:"form",
                defaults:{
                },
                border:false,
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
                              columns:4
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
                                    width:60,
                                    listeners: {
                                        blur: function(t){
                                            Ext.getCmp("ShippingServiceAdditionalCost_1").setValue(t.getValue());
                                            Ext.getCmp("ShippingServiceCost_2").minValue = t.getValue();
                                            Ext.getCmp("ShippingServiceCost_3").minValue = t.getValue();
                                            Ext.getCmp("InternationalShippingServiceCost_1").minValue = t.getValue();
                                            Ext.getCmp("InternationalShippingServiceCost_2").minValue = t.getValue();
                                            Ext.getCmp("InternationalShippingServiceCost_3").minValue = t.getValue();
                                        }
                                    }
                                  }]
                              },{
                                layout:"form",
                                border:false,
                                items:[{
                                    xtype:"numberfield",
                                    fieldLabel:"E A I",
                                    id:"ShippingServiceAdditionalCost_1",
                                    name:"ShippingServiceAdditionalCost_1",
                                    width:60,
                                    validator: function(){
                                        if(Ext.isEmpty(Ext.getCmp("ShippingServiceCost_1").getValue())){
                                            return true;    
                                        }
                                        if(this.getValue() >= Ext.getCmp("ShippingServiceCost_1").getValue()){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }
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
                                    width:60,
                                    listeners: {
                                        blur: function(t){
                                            Ext.getCmp("ShippingServiceAdditionalCost_2").setValue(t.getValue());
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
                                    id:"ShippingServiceAdditionalCost_2",
                                    name:"ShippingServiceAdditionalCost_2",
                                    width:60,
                                    validator: function(){
                                        if(Ext.isEmpty(Ext.getCmp("ShippingServiceCost_2").getValue())){
                                            return true;    
                                        }
                                        if(this.getValue() >= Ext.getCmp("ShippingServiceCost_1").getValue()){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }
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
                                    width:60,
                                    listeners: {
                                        blur: function(t){
                                            Ext.getCmp("ShippingServiceAdditionalCost_3").setValue(t.getValue());
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
                                    id:"ShippingServiceAdditionalCost_3",
                                    name:"ShippingServiceAdditionalCost_3",
                                    width:60,
                                    validator: function(){
                                        if(Ext.isEmpty(Ext.getCmp("ShippingServiceCost_3").getValue())){
                                            return true;    
                                        }
                                    
                                        if(this.getValue() >= Ext.getCmp("ShippingServiceCost_1").getValue()){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }
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
                            //editable: false,
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
                columnWidth:0.5,
                layout:"form",
                defaults:{
                },
                border:false,
                items:[{
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
                                id:"InternationalShippingServiceCost_1",
                                name:"InternationalShippingServiceCost_1",
                                width:60,
                                listeners: {
                                    blur: function(t){
                                        Ext.getCmp("InternationalShippingServiceAdditionalCost_1").setValue(t.getValue());
                                    }
                                }
                              }]
                          },{
                            layout:"form",
                            border:false,
                            items:[{
                                xtype:"numberfield",
                                fieldLabel:"E A I",
                                id:"InternationalShippingServiceAdditionalCost_1",
                                name:"InternationalShippingServiceAdditionalCost_1",
                                width:60,
                                validator: function(){
                                    if(Ext.isEmpty(Ext.getCmp("InternationalShippingServiceCost_1").getValue())){
                                        return true;    
                                    }
                                    
                                    if(this.getValue() >= Ext.getCmp("ShippingServiceCost_1").getValue()){
                                        return true;
                                    }else{
                                        return false;
                                    }
                                }
                              }]
                          },{
                            id:"InternationalShippingTo_1",
                            hidden:true,
                            layout:"form",
                            colspan: 3,
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
                                id:"InternationalShippingServiceCost_2",
                                name:"InternationalShippingServiceCost_2",
                                width:60,
                                listeners: {
                                    blur: function(t){
                                        Ext.getCmp("InternationalShippingServiceAdditionalCost_2").setValue(t.getValue());
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
                                id:"InternationalShippingServiceAdditionalCost_2",
                                name:"InternationalShippingServiceAdditionalCost_2",
                                width:60,
                                validator: function(){
                                    if(Ext.isEmpty(Ext.getCmp("InternationalShippingServiceCost_2").getValue())){
                                        return true;    
                                    }
                                    
                                    if(this.getValue() >= Ext.getCmp("ShippingServiceCost_1").getValue()){
                                        return true;
                                    }else{
                                        return false;
                                    }
                                }
                              }]
                          },{
                            id:"InternationalShippingTo_2",
                            hidden:true,
                            layout:"form",
                            colspan: 3,
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
                                id:"InternationalShippingServiceCost_3",
                                name:"InternationalShippingServiceCost_3",
                                width:60,
                                listeners: {
                                    blur: function(t){
                                        Ext.getCmp("InternationalShippingServiceAdditionalCost_3").setValue(t.getValue());
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
                                id:"InternationalShippingServiceAdditionalCost_3",
                                name:"InternationalShippingServiceAdditionalCost_3",
                                width:60,
                                validator: function(){
                                    if(Ext.isEmpty(Ext.getCmp("InternationalShippingServiceCost_3").getValue())){
                                        return true;    
                                    }
                                    
                                    if(this.getValue() >= Ext.getCmp("ShippingServiceCost_1").getValue()){
                                        return true;
                                    }else{
                                        return false;
                                    }
                                }
                              }]
                          },{
                            id:"InternationalShippingTo_3",
                            hidden:true,
                            layout:"form",
                            colspan: 3,
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
                }]
            }]
        },(template_id == "heshuai-temp")?{
            xtype: 'textfield',
            fieldLabel:"Shipping Template Name",
            labelStyle: "left: 250px; position: relative;",
            id:'shippingTemplateName',
            name: 'shippingTemplateName',
            style: 'position: relative; left: 230px;',
            width: 200
        }:{},{
            xtype: 'button',
            style: 'float: left; position: relative; left: 250px;',
            text: 'OK',
            handler: function(){
                if(!Ext.isEmpty(Ext.getCmp("shippingTemplateName"))){
                    template_id = Ext.getCmp("shippingTemplateName").getValue();
                }
                
                shipping.getForm().submit({
                    clientValidation: true,
                    url: 'service.php?action=saveShippingTemplate&name='+template_id+'&Site='+site,
                    success: function(form, action) {
                        //console.log(action);
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
            xtype: 'button',
            style: 'float: left; position: relative; left: 350px;',
            text: 'Close',
            handler: function(){
                window.close();
            }
        }]
    })
    
    shipping.render(document.body);
    
    shipping.getForm().load({
            url:'service.php?action=loadShippingTemplate', 
            method:'GET', 
            params: {name: template_id, Site: site},
            success: function(f, a){
                
                ShippingServiceOptionsTypeCombo.setValue(a.result.data.ShippingServiceOptionsType);
                shippingServiceStore.load({params: {serviceType: a.result.data.ShippingServiceOptionsType, SiteID: site}});
                
                InternationalShippingServiceOptionTypeCombo.setValue(a.result.data.InternationalShippingServiceOptionType);
                internationalShippingServiceStore.load({params: {serviceType: a.result.data.InternationalShippingServiceOptionType, SiteID: site}});
                
                
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
            }
    })
})