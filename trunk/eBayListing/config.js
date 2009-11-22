var currency_symbol = {
    US: '$',
    UK: '£',
    Australia: 'AUD$',
    France: '€'
}
var path = "/eBayBO/eBayListing/"
//var path = "/eBayListing/"
var inventory_service = "/inventory/service.php";

function previewSkuPicture(index){                                   
    var window = new Ext.Window({
        title:"Preview SKU " + Ext.getCmp("SKU").getValue() + " Picture",
        width:550,
        height:610,
        layout:"vbox",
        items: [{
            html: '<div id="sku-picture-preview"><img src="' + Ext.getCmp("picture-" + index).getValue() + '"></div>',
            height: 530,
            width:530
        },{
            html: '<div id="sku-picture-index" style="text-align:center;">SKU ' + Ext.getCmp("SKU").getValue() + ' Picture ' + (index + 1) + '</div>',
            height: 20,
            width:530
        },{
            layout:"hbox",
            width:530,
            items:[{
                xtype:"button",
                text:"Back",
                width: 200,
                handler: function(){
                    //console.log(index);
                    if(index <= 0){
                        
                        return 0;
                    }
                    index--;
                    document.getElementById("sku-picture-index").innerHTML = 'SKU ' + Ext.getCmp("SKU").getValue() + ' Picture ' + (index + 1);
                    document.getElementById("sku-picture-preview").innerHTML = '<img src="' + Ext.getCmp("picture-" + index).getValue() + '">';
                    return 1;
                }
            },{
                xtype:"spacer",
                flex:1    
            },{
                xtype:"button",
                text:"Close",
                handler: function(){
                    window.close();
                }
            },{
                xtype:"spacer",
                flex:1    
            },{
                xtype:"button",
                text:"Next",
                width: 200,
                handler: function(){
                    //console.log(index);
                    if(index >= 4){
                        
                        return 0;
                    }
                    index++;
                    document.getElementById("sku-picture-index").innerHTML = 'SKU ' + Ext.getCmp("SKU").getValue() + ' Picture ' + (index + 1);
                    document.getElementById("sku-picture-preview").innerHTML = '<img src="' + Ext.getCmp("picture-" + index).getValue() + '">';
                    return 1;
                }
            }]
        }]
    })
    window.show();
}

var pictureManage = {
    text:'SKU Picture Manage',
    icon: './images/photos.png',
    tooltip:'Manage SKU Picture',
    handler: function(){
        Ext.Ajax.request({
            url: 'service.php?action=getSkuPicture',
            params: {
                sku: Ext.getCmp("SKU").getValue()
            },
            success: function(a, b){
                var result = eval(a.responseText);
                for(var i = 0; i <5; i++){
                    Ext.getCmp("picture-"+i).setValue(result[i]);
                }
            }
        })
        
        var window = new Ext.Window({
            title:"Manage SKU Picture",
            closeAction:"hide",
            width:550,
            layout:"form",
            items: [{
                layout:'column',
                items:[{
                    xtype:"textfield",
                    columnWidth: .8,
                    fieldLabel:"picture 1",
                    //labelStyle:"width:50px;",
                    id:"picture-0",
                    style:"padding-left:0px;",
                    width:400
                },{
                    xtype:"button",
                    columnWidth: .2,
                    text:"Preview",
                    handler: function(){
                        previewSkuPicture(0);
                    }
                }]
            },{
                layout:'column',
                items:[{
                    xtype:"textfield",
                    columnWidth: .8,
                    fieldLabel:"picture 1",
                    //labelStyle:"width:50px;",
                    id:"picture-1",
                    style:"padding-left:0px;",
                    width:400
                },{
                    xtype:"button",
                    columnWidth: .2,
                    text:"Preview",
                    handler: function(){
                        previewSkuPicture(1);
                    }
                }]
            },{
                layout:'column',
                items:[{
                    xtype:"textfield",
                    columnWidth: .8,
                    fieldLabel:"picture 1",
                    //labelStyle:"width:50px;",
                    id:"picture-2",
                    style:"padding-left:0px;",
                    width:400
                },{
                    xtype:"button",
                    columnWidth: .2,
                    text:"Preview",
                    handler: function(){
                        previewSkuPicture(2);
                    }
                }]
            },{
                layout:'column',
                items:[{
                    xtype:"textfield",
                    columnWidth: .8,
                    fieldLabel:"picture 1",
                    //labelStyle:"width:50px;",
                    id:"picture-3",
                    style:"padding-left:0px;",
                    width:400
                },{
                    xtype:"button",
                    columnWidth: .2,
                    text:"Preview",
                    handler: function(){
                        previewSkuPicture(3);
                    }
                }]
            },{
                layout:'column',
                items:[{
                    xtype:"textfield",
                    columnWidth: .8,
                    fieldLabel:"picture 1",
                    //labelStyle:"width:50px;",
                    id:"picture-4",
                    style:"padding-left:0px;",
                    width:400
                },{
                    xtype:"button",
                    columnWidth: .2,
                    text:"Preview",
                    handler: function(){
                        previewSkuPicture(4);
                    }
                }]
            }],
            buttons:[{
                text:"Save",
                handler:function(){
                    Ext.Ajax.request({
                        url: 'service.php?action=saveSkuPicture',
                        params: {
                            sku: Ext.getCmp("SKU").getValue(),
                            picture_1: Ext.getCmp("picture-0").getValue(),
                            picture_2: Ext.getCmp("picture-1").getValue(),
                            picture_3: Ext.getCmp("picture-2").getValue(),
                            picture_4: Ext.getCmp("picture-3").getValue(),
                            picture_5: Ext.getCmp("picture-4").getValue()
                        },
                        success: function(a, b){
                            window.close();
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
}

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