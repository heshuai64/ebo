Ext.onReady(function(){
    Ext.BLANK_IMAGE_URL = "../../ext-3.0-rc2/resources/images/default/s.gif";
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
    
    var siteStore = new Ext.data.JsonStore({
        autoLoad :true,
        fields: ['id', 'name'],
        url:'service.php?action=getSite'
    })
    
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
    var day_id_array = [["Mon-am-0-panel-panel", "Mon-am-1-panel", "Mon-am-2-panel", "Mon-am-3-panel", "Mon-am-4-panel", "Mon-am-5-panel", "Mon-am-6-panel", "Mon-am-7-panel", "Mon-am-8-panel", "Mon-am-9-panel", "Mon-am-10-panel", "Mon-am-11-panel", "Mon-am-12-panel",
                          "Mon-pm-1-panel", "Mon-pm-2-panel", "Mon-pm-3-panel", "Mon-pm-4-panel", "Mon-pm-5-panel", "Mon-pm-6-panel", "Mon-pm-7-panel", "Mon-pm-8-panel", "Mon-pm-9-panel", "Mon-pm-10-panel", "Mon-pm-11-panel"],
                         
                         ["Tue-am-0-panel", "Tue-am-1-panel", "Tue-am-2-panel", "Tue-am-3-panel", "Tue-am-4-panel", "Tue-am-5-panel", "Tue-am-6-panel", "Tue-am-7-panel", "Tue-am-8-panel", "Tue-am-9-panel", "Tue-am-10-panel", "Tue-am-11-panel", "Tue-am-12-panel",
                          "Tue-pm-1-panel", "Tue-pm-2-panel", "Tue-pm-3-panel", "Tue-pm-4-panel", "Tue-pm-5-panel", "Tue-pm-6-panel", "Tue-pm-7-panel", "Tue-pm-8-panel", "Tue-pm-9-panel", "Tue-pm-10-panel", "Tue-pm-11-panel"],
                         
                         ["Wed-am-0-panel", "Wed-am-1-panel", "Wed-am-2-panel", "Wed-am-3-panel", "Wed-am-4-panel", "Wed-am-5-panel", "Wed-am-6-panel", "Wed-am-7-panel", "Wed-am-8-panel", "Wed-am-9-panel", "Wed-am-10-panel", "Wed-am-11-panel", "Wed-am-12-panel",
                          "Wed-pm-1-panel", "Wed-pm-2-panel", "Wed-pm-3-panel", "Wed-pm-4-panel", "Wed-pm-5-panel", "Wed-pm-6-panel", "Wed-pm-7-panel", "Wed-pm-8-panel", "Wed-pm-9-panel", "Wed-pm-10-panel", "Wed-pm-11-panel"],
                         
                         ["Thu-am-0-panel", "Thu-am-1-panel", "Thu-am-2-panel", "Thu-am-3-panel", "Thu-am-4-panel", "Thu-am-5-panel", "Thu-am-6-panel", "Thu-am-7-panel", "Thu-am-8-panel", "Thu-am-9-panel", "Thu-am-10-panel", "Thu-am-11-panel", "Thu-am-12-panel",
                          "Thu-pm-1-panel", "Thu-pm-2-panel", "Thu-pm-3-panel", "Thu-pm-4-panel", "Thu-pm-5-panel", "Thu-pm-6-panel", "Thu-pm-7-panel", "Thu-pm-8-panel", "Thu-pm-9-panel", "Thu-pm-10-panel", "Thu-pm-11-panel"],
                         
                         ["Fri-am-0-panel", "Fri-am-1-panel", "Fri-am-2-panel", "Fri-am-3-panel", "Fri-am-4-panel", "Fri-am-5-panel", "Fri-am-6-panel", "Fri-am-7-panel", "Fri-am-8-panel", "Fri-am-9-panel", "Fri-am-10-panel", "Fri-am-11-panel", "Fri-am-12-panel",
                          "Fri-pm-1-panel", "Fri-pm-2-panel", "Fri-pm-3-panel", "Fri-pm-4-panel", "Fri-pm-5-panel", "Fri-pm-6-panel", "Fri-pm-7-panel", "Fri-pm-8-panel", "Fri-pm-9-panel", "Fri-pm-10-panel", "Fri-pm-11-panel"],
                         
                         ["Sat-am-0-panel", "Sat-am-1-panel", "Sat-am-2-panel", "Sat-am-3-panel", "Sat-am-4-panel", "Sat-am-5-panel", "Sat-am-6-panel", "Sat-am-7-panel", "Sat-am-8-panel", "Sat-am-9-panel", "Sat-am-10-panel", "Sat-am-11-panel", "Sat-am-12-panel",
                          "Sat-pm-1-panel", "Sat-pm-2-panel", "Sat-pm-3-panel", "Sat-pm-4-panel", "Sat-pm-5-panel", "Sat-pm-6-panel", "Sat-pm-7-panel", "Sat-pm-8-panel", "Sat-pm-9-panel", "Sat-pm-10-panel", "Sat-pm-11-panel"],
                         
                         ["Sun-am-0-panel", "Sun-am-1-panel", "Sun-am-2-panel", "Sun-am-3-panel", "Sun-am-4-panel", "Sun-am-5-panel", "Sun-am-6-panel", "Sun-am-7-panel", "Sun-am-8-panel", "Sun-am-9-panel", "Sun-am-10-panel", "Sun-am-11-panel", "Sun-am-12-panel",
                          "Sun-pm-1-panel", "Sun-pm-2-panel", "Sun-pm-3-panel", "Sun-pm-4-panel", "Sun-pm-5-panel", "Sun-pm-6-panel", "Sun-pm-7-panel", "Sun-pm-8-panel", "Sun-pm-9-panel", "Sun-pm-10-panel", "Sun-pm-11-panel"]
                        ];
    
    var time_array = ["0 <br> am", "1 <br> am", "2 <br> am", "3 <br> am", "4 <br> am", "5 <br> am", "6 <br> am", "7 <br> am", "8 <br> am", "9 <br> am", "10 <br> am", "11 <br> am", "12 <br> am",
                "1 <br> pm", "2 <br> pm", "3 <br> pm", "4 <br> pm", "5 <br> pm", "6 <br> pm", "7 <br> pm", "8 <br> pm", "9 <br> pm", "10 <br> pm", "11 <br> pm"];
    var time_id_array = [["Mon-am-0", "Mon-am-1", "Mon-am-2", "Mon-am-3", "Mon-am-4", "Mon-am-5", "Mon-am-6", "Mon-am-7", "Mon-am-8", "Mon-am-9", "Mon-am-10", "Mon-am-11", "Mon-am-12",
                          "Mon-pm-1", "Mon-pm-2", "Mon-pm-3", "Mon-pm-4", "Mon-pm-5", "Mon-pm-6", "Mon-pm-7", "Mon-pm-8", "Mon-pm-9", "Mon-pm-10", "Mon-pm-11"],
                         
                         ["Tue-am-0", "Tue-am-1", "Tue-am-2", "Tue-am-3", "Tue-am-4", "Tue-am-5", "Tue-am-6", "Tue-am-7", "Tue-am-8", "Tue-am-9", "Tue-am-10", "Tue-am-11", "Tue-am-12",
                          "Tue-pm-1", "Tue-pm-2", "Tue-pm-3", "Tue-pm-4", "Tue-pm-5", "Tue-pm-6", "Tue-pm-7", "Tue-pm-8", "Tue-pm-9", "Tue-pm-10", "Tue-pm-11"],
                         
                         ["Wed-am-0", "Wed-am-1", "Wed-am-2", "Wed-am-3", "Wed-am-4", "Wed-am-5", "Wed-am-6", "Wed-am-7", "Wed-am-8", "Wed-am-9", "Wed-am-10", "Wed-am-11", "Wed-am-12",
                          "Wed-pm-1", "Wed-pm-2", "Wed-pm-3", "Wed-pm-4", "Wed-pm-5", "Wed-pm-6", "Wed-pm-7", "Wed-pm-8", "Wed-pm-9", "Wed-pm-10", "Wed-pm-11"],
                         
                         ["Thu-am-0", "Thu-am-1", "Thu-am-2", "Thu-am-3", "Thu-am-4", "Thu-am-5", "Thu-am-6", "Thu-am-7", "Thu-am-8", "Thu-am-9", "Thu-am-10", "Thu-am-11", "Thu-am-12",
                          "Thu-pm-1", "Thu-pm-2", "Thu-pm-3", "Thu-pm-4", "Thu-pm-5", "Thu-pm-6", "Thu-pm-7", "Thu-pm-8", "Thu-pm-9", "Thu-pm-10", "Thu-pm-11"],
                         
                         ["Fri-am-0", "Fri-am-1", "Fri-am-2", "Fri-am-3", "Fri-am-4", "Fri-am-5", "Fri-am-6", "Fri-am-7", "Fri-am-8", "Fri-am-9", "Fri-am-10", "Fri-am-11", "Fri-am-12",
                          "Fri-pm-1", "Fri-pm-2", "Fri-pm-3", "Fri-pm-4", "Fri-pm-5", "Fri-pm-6", "Fri-pm-7", "Fri-pm-8", "Fri-pm-9", "Fri-pm-10", "Fri-pm-11"],
                         
                         ["Sat-am-0", "Sat-am-1", "Sat-am-2", "Sat-am-3", "Sat-am-4", "Sat-am-5", "Sat-am-6", "Sat-am-7", "Sat-am-8", "Sat-am-9", "Sat-am-10", "Sat-am-11", "Sat-am-12",
                          "Sat-pm-1", "Sat-pm-2", "Sat-pm-3", "Sat-pm-4", "Sat-pm-5", "Sat-pm-6", "Sat-pm-7", "Sat-pm-8", "Sat-pm-9", "Sat-pm-10", "Sat-pm-11"],
                         
                         ["Sun-am-0", "Sun-am-1", "Sun-am-2", "Sun-am-3", "Sun-am-4", "Sun-am-5", "Sun-am-6", "Sun-am-7", "Sun-am-8", "Sun-am-9", "Sun-am-10", "Sun-am-11", "Sun-am-12",
                          "Sun-pm-1", "Sun-pm-2", "Sun-pm-3", "Sun-pm-4", "Sun-pm-5", "Sun-pm-6", "Sun-pm-7", "Sun-pm-8", "Sun-pm-9", "Sun-pm-10", "Sun-pm-11"]
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
    //---------------------------------------------------------------------------------------------------------
    var itemForm = new Ext.form.FormPanel({
        labelAlign:"top",
        //height: 600,
        buttonAlign:"center",
        items:[{
                xtype:"combo",
                labelAlign:"left",
                fieldLabel:"Site",
                mode: 'local',
                store: siteStore,
                valueField:'id',
                displayField:'name',
                triggerAction: 'all',
                editable: false,
                selectOnFocus:true,
                //listWidth: 156,
                //width: 156,
                name: 'Site',
                //allowBlank: false,
                hiddenName:'Site' 
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
                                //name:"combovalue",
                                //hiddenName:"combovalue",
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
                                //name:"combovalue",
                                //hiddenName:"combovalue",
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
                                //name:"combovalue",
                                //hiddenName:"combovalue",
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
                                //name:"combovalue",
                                //hiddenName:"combovalue",
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
                                    }]
                            }]
                    },{
                            xtype:"panel",
                            items: schedule
                        }]
                  },{
                    xtype:"textfield",
                    fieldLabel:"<font color='red'>SKU</font>",
                    name:"SKU",
                    value: sku,
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
                        style: 'margin: 10px;',
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
                        style: 'margin: 10px;',
                        listeners: {
                            render: function(c){
                                var combo = new Ext.form.ComboBox({
                                        store: ['Flat', 'Calculated'],
                                        triggerAction: 'all',
                                        editable: false,
                                        width: 150,
                                        listWidth: 150,
                                        name: 'shippingType',
                                        listeners: {
                                            "select": function(c, r, i){
                                                //console.log(c);
                                                shippingServiceStore.load({params: {InternationalService: 0, serviceType: c.value}});
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
                        style: 'margin: 10px;',
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
                                                shippingServiceStore.load({params: {InternationalService: 1, serviceType: c.value}});
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
                        style: 'margin: 10px;',
                        items:[{
                            xtype:"checkbox",
                            labelSeparator: '',
                            labelStyle: 'height:0px;padding:0px',
                            fieldLabel:"",
                            boxLabel:"Credit crads via PayPal",
                            name:"PayPalPayment",
                            inputValue:"PayPalPayment"
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
                        url: 'service.php?action=saveItem',
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
                text: "Close",
                handler: function(){
                    
                }
            }]
    })
    
    var itemPanel = new Ext.Panel({
        autoScroll: true,
        //height:750,
        items: itemForm
    })
    
    itemPanel.render(document.body);
    
    Ext.select(".schedule-time").on("click",function(e, el){
        //console.log(el);
        if(Ext.getCmp(el.childNodes[0].id).getValue() == 0){
            Ext.getCmp(el.childNodes[0].id + "-panel").body.applyStyles("background-color:red;");
            Ext.getCmp(el.childNodes[0].id).setValue(1)
        }else{
            Ext.getCmp(el.childNodes[0].id + "-panel").body.applyStyles("background-color:white;");
            Ext.getCmp(el.childNodes[0].id).setValue(0)
        }
        //el.addClass("schedule-time-y");
        //console.log(el.childNodes[0].id);
    })
})

