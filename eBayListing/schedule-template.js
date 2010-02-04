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
    
    var schedule = new Ext.Panel({                              
        //title:"Schedule",
        layout:"table",
        layoutConfig:{
          columns:25
        },
        defaults:{
          width:26
        },
        width:688
    })
    
    var scheduleForm = new Ext.form.FormPanel({
        title:"Schedule",
        //layout:"form",
        //labelAlign:"left",
        //labelPad:0,
        reader:new Ext.data.JsonReader({
            }, ['Schedule']
        ),
        items:[schedule,(template_id == "heshuai")?{
            xtype: 'textfield',
            fieldLabel:"Schedule Template Name",
            labelStyle: "width: 160px; left: 180px;",
            id:'scheduleTemplateName',
            name: 'scheduleTemplateName',
            style: 'float: left; position: relative; left: 200px;'
        }:{},{
            xtype: 'button',
            style: 'float: left; position: relative; left: 250px;',
            text: 'OK',
            handler: function(){
                if(!Ext.isEmpty(Ext.getCmp("scheduleTemplateName"))){
                    template_id = Ext.getCmp("scheduleTemplateName").getValue();
                }
                
                Ext.Ajax.request({
                    url: 'service.php?action=saveScheduleTemplate',
                    success: function(){
                        window.close();
                    },
                    failure: function(){},
                    params: {
                        name: template_id
                    }
                });
            }
        },{
            xtype: 'button',
            style: 'float: left; position: relative; left: 350px;',
            text: 'Close',
            handler: function(){
                window.close();
            }
        }],
        //bodyStyle: 'text-align: center;',
        width:688
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
    
    scheduleForm.render(document.body);
    
    //Schedule time edit windows --------------------------------------------------------------------------------
    Ext.select(".schedule-time").on("click",function(e, el){
        var tempArray = el.childNodes[0].id.split("-");
        
        var timeStore = new Ext.data.JsonStore({
            //root:"timeList",
            autoLoad:true,
            url:'service.php?action=getTemplateScheduleTime&template_id='+template_id+'&dayTime='+el.childNodes[0].id,
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
                                    url: 'service.php?action=addTemplateScheduleTime',
                                    success: function(){
                                            timeStore.reload();
                                        },
                                    failure: function(){},
                                    params: {
                                            dayTime: el.childNodes[0].id,
                                            template_id: template_id,
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
                                    url: 'service.php?action=deleteTemplateScheduleTime',
                                    success: function(){
                                            timeStore.reload();
                                        },
                                    failure: function(){},
                                    params: {
                                            dayTime: el.childNodes[0].id,
                                            template_id: template_id,
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
                                url: 'service.php?action=deleteAllTemplateScheduleTime',
                                success: function(){
                                        timeStore.reload();
                                    },
                                failure: function(){},
                                params: {
                                        dayTime: el.childNodes[0].id,
                                        template_id: template_id
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
                                url: 'service.php?action=updateTemplateScheduleTime',
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
                                waitMsg:'updating, please wait.',
                                failure: function(){},
                                params: {
                                        dayTime: el.childNodes[0].id,
                                        template_id: template_id
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
        /*
        if(Ext.getCmp(el.childNodes[0].id).getValue() == 0){
            Ext.getCmp(el.childNodes[0].id + "-panel").body.applyStyles("background-color:red;");
            Ext.getCmp(el.childNodes[0].id).setValue(1)
        }else{
            Ext.getCmp(el.childNodes[0].id + "-panel").body.applyStyles("background-color:white;");
            Ext.getCmp(el.childNodes[0].id).setValue(0)
        }
        //el.addClass("schedule-time-y");
        //console.log(el.childNodes[0].id);
        */
    })
    
    scheduleForm.getForm().load({
            url:'service.php?action=loadScheduleTemplate', 
            method:'GET', 
            params: {name: template_id},
            success: function(f, a){
                if(!Ext.isEmpty(a.result.data.Schedule)){
                    var Schedule = a.result.data.Schedule.split(',');
                    for(var i in Schedule){
                        if(!Ext.isFunction(Schedule[i])){
                            Ext.getCmp(Schedule[i]).body.applyStyles("background-color:red;");
                        }
                    }
                }
            }
    })
})