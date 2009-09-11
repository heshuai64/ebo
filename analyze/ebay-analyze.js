Ext.onReady(function(){
    Ext.BLANK_IMAGE_URL = "../../Ext/2.2/resources/images/default/s.gif";
    
    var cp = new Ext.state.CookieProvider({
       path: "/eBayBO/analyze/",
       expires: new Date(new Date().getTime()+(1000*60*60*24*365)) //365 days
       //domain: "127.0.0.1"
    });
    Ext.state.Manager.setProvider(cp);
   
    var myMask = new Ext.LoadMask(Ext.getBody(), {msg:"Please wait..."});
    //http://open.api.ebay.com/shopping?callname=FindItems&callbackname=com.ebay.shoppingservice.Shopping.findItemsClosure0&responseencoding=JSON&callback=true&version=607&appId=eBayAPID-73f4-45f2-b9a3-c8f6388b38d8&QueryKeywords=ipod&MaxEntries=1&client=js
    //http://open.api.ebay.com/shopping?callname=GetSingleItem&callbackname=com.ebay.shoppingservice.Shopping.getSingleItemClosure1&responseencoding=JSON&callback=true&version=607&appId=eBayAPID-73f4-45f2-b9a3-c8f6388b38d8&ItemID=390059143734&IncludeSelector=Details%2CShippingCosts&client=js
    //http://open.api.ebay.com/shopping?callname=FindItemsAdvanced&callbackname=com.ebay.shoppingservice.Shopping.findItemsAdvancedClosure0&responseencoding=JSON&callback=true&version=607&appId=eBayAPID-73f4-45f2-b9a3-c8f6388b38d8&QueryKeywords=Battery&ItemSort=CurrentBid&SellerID(0)=%20libra.studio&SellerID(1)=easybattery&MaxEntries=10&client=js
    com.ebay.widgets.needs({
        baseUrl: 'http://w-1.ebay.com/js/607/min/',
        files: ['FindItemsAdvanced.js', 'GetSingleItem.js'],
	resources: [].concat(com.ebay.shoppingservice.Shopping.getSingleItem, com.ebay.shoppingservice.Shopping.findItemsAdvanced),
        callback: function() {
       
            //console.log("render grid");
            var reader = new Ext.data.ArrayReader({}, [
                {name: 'itemID'},
                {name: 'userID'},
                {name: 'title'},
                {name: 'buyItNowAvailable'},
                {name: 'currentPrice'},
                {name: 'shippingServiceCost'},
                {name: 'quantity'},
                {name: 'quantitySold'},
                {name: 'galleryURL'},
                {name: 'startTime'},
                {name: 'endTime', type: 'date', dateFormat: 'y-m-d H:i:s'},
                {name: 'listingType'},
                {name: 'listingStatus'}
            ]);
                   
            var store = new Ext.data.Store({
                    reader: reader
                    //data: itemArray
            })
            
            function renderImage(v, p, r){
                return String.format('<img src="{0}"/>', v);
            }
            
            function renderShippingServiceCost(v, p, r){
		//console.log(v.substr(3));
                if(v.substr(3) == "0" || v.substr(3) == 0){
                    return String.format('<font color="red">{0}</font>', v);
                }else{
                    return String.format('<font color="green">{0}</font>', v);
                }
            }
            
            function renderListingDays(v, p, r){
                //console.log(r);
                //console.log(r.data.endTime.getElapsed(r.data.startTime));
                return r.data.endTime.getElapsed(r.data.startTime) / (24 * 60 * 60 *1000);
            }
            
            var countryCombo = new Ext.form.ComboBox({
                mode: 'local',
                store: ['US','GB','AU','FR'],
                triggerAction: 'all',
                editable: false,
                selectOnFocus:true,
                name:'country',
                hiddenName:'country',
                listWidth: 50,
                width:50
            })
            
            var grid = new Ext.grid.GridPanel({
                title: 'eBay Item Analyze (<font color="red">you want to specify multiple Seller, use a comma.</font>)',
                autoHeight: true,
                store: store,
                autoScroll: true,
                width: 1300,
		//height: 768,
                selModel: new Ext.grid.RowSelectionModel({}),
                columns:[
                    {header: "Image", width: 100, align: 'center', sortable: true, dataIndex: 'galleryURL', renderer: renderImage},
                    {header: "Seller ID", width: 80, align: 'center', sortable: true, dataIndex: 'userID'},
                    {header: "Title", width: 320, align: 'center', sortable: true, dataIndex: 'title'},
                    {header: "Buy It Now", width: 70, align: 'center', sortable: true, dataIndex: 'buyItNowAvailable'},
                    {header: "Price", width: 55, align: 'center', sortable: true, dataIndex: 'currentPrice'},
                    {header: "Shipping Cost", width: 100, align: 'center', sortable: true, dataIndex: 'shippingServiceCost', renderer: renderShippingServiceCost},
                    {header: "Listing Q", width: 60, align: 'center', sortable: true, dataIndex: 'quantity'},
                    {header: "Sold Q", width: 60, align: 'center', sortable: true, dataIndex: 'quantitySold'},
                    {header: "Start Time", width: 130, align: 'center', sortable: true, dataIndex: 'startTime'},
                    {header: "End Time", width: 130, align: 'center', sortable: true, dataIndex: 'endTime'},
                    {header: "Listing Days", width: 80, align: 'center', sortable: true, dataIndex: 'startTime', renderer: renderListingDays},
                    {header: "Type", width: 60, align: 'center', sortable: true, dataIndex: 'listingType'},
                    {header: "Status", width: 60, align: 'center', sortable: true, dataIndex: 'listingStatus'}
                ],
                tbar:[{
                        xtype: 'tbtext',
                        text: 'Country:'
                    },countryCombo,{
                        xtype: 'tbtext',
                        text: 'Store Name:'
                    },{
                        id: 'storeName',
                        name: 'storeName',
                        xtype: 'textfield',
                        width: 120,
                        listeners:{change: function(t, n, o){
                                        //console.log(t);
                                        if(n.length > 0){
                                            Ext.getCmp('seller').disable();
                                        }else{
                                            Ext.getCmp('seller').enable();
                                        }
                                    }
                        }
                    },'-',{
                        xtype: 'tbtext',
                        text: 'Seller:'
                    },{
                        id: 'seller',
                        name: 'seller',
                        xtype: 'textfield',
                        stateful: true,
                        width: 200
                    },{ text: 'Select',
                        handler: function(){
                            
                            
                            var sellerForm = new Ext.FormPanel({
                                width:280,
                                defaultType: 'checkbox',
                                autoHeight: true
                            })
                            
                            //var a = ['easybattery','libra.studio'];
                            //cp.set("sellerArray", a);
                            //console.log(cp.get("sellerArray"));
                            
                            var sellerArray = cp.get("sellerArray");
                            //console.log(sellerArray);

                            for(var i in sellerArray){
                                if(Ext.type(sellerArray[i]) == "string"){
                                    var c = new Ext.form.Checkbox({
                                        fieldLabel: (i==0)?'Seller':'',
                                        labelSeparator: (i==0)?':':'',
                                        boxLabel: sellerArray[i],
                                        name: sellerArray[i]
                                    })
                                    sellerForm.add(c);
                                }
                            }
                            
                            
                            var sellerWindow = new Ext.Window({
                                autoScroll: true,
                                title: 'Select Seller',
                                buttonAlign: 'center',
                                width: 400,
                                height: 500,
                                items: sellerForm,
                                plain:true,
                                layout: 'fit',
                                buttons: [{
                                    text:'OK',
                                    handler: function (){
                                        var selectSeller = "";
                                        for(var i in sellerForm.items.items){
                                            if(!Ext.isEmpty(sellerForm.items.items[i].name)){
                                                if(sellerForm.items.items[i].checked == true){
                                                    selectSeller = selectSeller + sellerForm.items.items[i].name + ",";
                                                }
                                                //console.log([sellerForm.items.items[i].name, sellerForm.items.items[i].checked]);
                                            }
                                        }
                                        //console.log(selectSeller);
                                        selectSeller = selectSeller.substr(0, selectSeller.length-1);
                                        Ext.getCmp("seller").setValue(selectSeller);
                                        sellerWindow.close();
                                    }
                                },{
                                    text:'Add Seller',
                                    handler: function (){
                                        var addSellerForm = new Ext.FormPanel({
                                            autoHeight: true,
                                            items: [{
                                                xtype: 'textfield',
                                                fieldLabel: 'Seller',
                                                name: 'seller'
                                            }]
                                        })
                                        
                                        var addSellerWindow = new Ext.Window({
                                            title: 'Add Seller',
                                            width: 300,
                                            height: 100,
                                            items: addSellerForm,
                                            plain:true,
                                            layout: 'fit',
                                            buttons: [{
                                                text:'OK',
                                                handler: function (){
                                                    var seller = addSellerForm.getForm().findField('seller').getValue();
                                                    var c = new Ext.form.Checkbox({
                                                        fieldLabel: '',
                                                        labelSeparator: '',
                                                        boxLabel: seller,
                                                        name: seller
                                                    })
                                                    sellerForm.add(c);
              
                                                    var sellerArray = cp.get("sellerArray");
                                                    if(Ext.isEmpty(sellerArray)){
                                                        sellerArray = new Array();
                                                        sellerArray.push(seller);
                                                        cp.set("sellerArray", sellerArray);
                                                    }else{
                                                        sellerArray.push(seller);
                                                        cp.set("sellerArray", sellerArray);
                                                    }
                                                    sellerWindow.doLayout();
                                                    addSellerWindow.close();
                                                }
                                            },{
                                                text:'Close',
                                                handler: function (){
                                                   addSellerWindow.close();
                                                }
                                            }]
                                        })
                                        
                                        addSellerWindow.show();
                                    }
                                },{
                                    text:'Delete Selected',
                                    handler: function (){
                                        for(var i in sellerForm.items.items){
                                            if(!Ext.isEmpty(sellerForm.items.items[i].name)){
                                                if(sellerForm.items.items[i].checked == true){
                                                    var sellerArray = cp.get("sellerArray");
                                                    for(var j in sellerArray){
                                                        console.log([sellerForm.items.items[i].name, sellerArray[j]]);
                                                        if(sellerForm.items.items[i].name == sellerArray[j]){
                                                            sellerArray[j] = sellerArray[0];
                                                            sellerArray.shift();
                                                            sellerForm.items.items[i].destroy();
                                                        }
                                                    }
                                                }
                                                //console.log([sellerForm.items.items[i].name, sellerForm.items.items[i].checked]);
                                            }
                                        }
                                        console.log(sellerArray);
                                        cp.set("sellerArray", sellerArray);
                                        sellerForm.doLayout();
                                        sellerWindow.doLayout();
                                    }    
                                },{
                                    text:'Close',
                                    handler: function (){
                                       sellerWindow.close(); 
                                    }
                                }]
                            })
                            
                            sellerWindow.show();
                        }
                    },'-',{
                        xtype: 'tbtext',
                        text: 'Keyword:'
                    },{
                        id: 'keyword',
                        name: 'keyword',
                        xtype: 'textfield',
                        width: 150
                    },{ text: 'Select',
                        handler: function(){
                            
                            var keywordForm = new Ext.FormPanel({
                                width:280,
                                defaultType: 'checkbox',
                                autoHeight: true
                            })
                    
                            
                            var keywordArray = cp.get("keywordArray");
                            //console.log(keywordArray);

                            for(var i in keywordArray){
                                if(Ext.type(keywordArray[i]) == "string"){
                                    var c = new Ext.form.Checkbox({
                                        fieldLabel: (i==0)?'KeyWord':'',
                                        labelSeparator: (i==0)?':':'',
                                        boxLabel: keywordArray[i],
                                        name: keywordArray[i]
                                    })
                                    keywordForm.add(c);
                                }
                            }
                            
                            
                            var keywordWindow = new Ext.Window({
                                autoScroll: true,
                                title: 'Select Keyword',
                                buttonAlign: 'center',
                                width: 400,
                                height: 500,
                                items: keywordForm,
                                plain:true,
                                layout: 'fit',
                                buttons: [{
                                    text:'OK',
                                    handler: function (){
                                        var selectSeller = "";
                                        for(var i in keywordForm.items.items){
                                            if(!Ext.isEmpty(keywordForm.items.items[i].name)){
                                                if(keywordForm.items.items[i].checked == true){
                                                    selectSeller = selectSeller + keywordForm.items.items[i].name + "%20";
                                                }
                                                //console.log([keywordForm.items.items[i].name, keywordForm.items.items[i].checked]);
                                            }
                                        }
                                        //console.log(selectSeller);
                                        selectSeller = selectSeller.substr(0, selectSeller.length-3);
                                        Ext.getCmp("keyword").setValue(selectSeller);
                                        keywordWindow.close();
                                    }
                                },{
                                    text:'Add Keyword',
                                    handler: function (){
                                        var addSellerForm = new Ext.FormPanel({
                                            autoHeight: true,
                                            items: [{
                                                xtype: 'textfield',
                                                fieldLabel: 'Keyword',
                                                name: 'keyword'
                                            }]
                                        })
                                        
                                        var addSellerWindow = new Ext.Window({
                                            title: 'Add Keyword',
                                            width: 300,
                                            height: 100,
                                            items: addSellerForm,
                                            plain:true,
                                            layout: 'fit',
                                            buttons: [{
                                                text:'OK',
                                                handler: function (){
                                                    var keyword = addSellerForm.getForm().findField('keyword').getValue();
                                                    var c = new Ext.form.Checkbox({
                                                        fieldLabel: '',
                                                        labelSeparator: '',
                                                        boxLabel: keyword,
                                                        name: keyword
                                                    })
                                                    keywordForm.add(c);
              
                                                    var keywordArray = cp.get("keywordArray");
                                                    if(Ext.isEmpty(keywordArray)){
                                                        keywordArray = new Array();
                                                        keywordArray.push(keyword);
                                                        cp.set("keywordArray", keywordArray);
                                                    }else{
                                                        keywordArray.push(keyword);
                                                        cp.set("keywordArray", keywordArray);
                                                    }
                                                    keywordWindow.doLayout();
                                                    addSellerWindow.close();
                                                }
                                            },{
                                                text:'Close',
                                                handler: function (){
                                                   addSellerWindow.close();
                                                }
                                            }]
                                        })
                                        
                                        addSellerWindow.show();
                                    }
                                },{
                                    text:'Delete Selected',
                                    handler: function (){
                                        for(var i in keywordForm.items.items){
                                            if(!Ext.isEmpty(keywordForm.items.items[i].name)){
                                                if(keywordForm.items.items[i].checked == true){
                                                    var keywordArray = cp.get("keywordArray");
                                                    for(var j in keywordArray){
                                                        console.log([keywordForm.items.items[i].name, keywordArray[j]]);
                                                        if(keywordForm.items.items[i].name == keywordArray[j]){
                                                            keywordArray[j] = keywordArray[0];
                                                            keywordArray.shift();
                                                            keywordForm.items.items[i].destroy();
                                                        }
                                                    }
                                                }
                                                //console.log([keywordForm.items.items[i].name, keywordForm.items.items[i].checked]);
                                            }
                                        }
                                        console.log(keywordArray);
                                        cp.set("keywordArray", keywordArray);
                                        keywordForm.doLayout();
                                        keywordWindow.doLayout();
                                    }    
                                },{
                                    text:'Close',
                                    handler: function (){
                                       keywordWindow.close(); 
                                    }
                                }]
                            })
                            
                            keywordWindow.show();
                        }
                    },'-',{
			xtype: 'tbtext',
                        text: 'End date from:'
		    },{
			id: 'from',
			name: 'from',
			xtype: 'datefield',
			format : 'Y-m-d'
		    },'-',{
			xtype: 'tbtext',
                        text: 'End date to:'
		    },{
			id: 'to',
			name: 'to',
			xtype: 'datefield',
			format : 'Y-m-d'
		    },{
                        text: 'Submit',
                        handler: function(){
                            
                            function findItemsAdvancedSuccess(data) {
                                //console.log("findItemsAdvancedSuccess");
                                //console.log(data);
                                
                                var i = 0;
                                var itemArray = new Array();
                                var itemTotalNum = 0;
                                
                                //---------------------------------  getSingleItemSuccess  -----------------------------------------------------
                                var getSingleItemSuccess = function(data){
                                    //console.log("getSingleItemSuccess (" + i + ")");
                                    //console.log(data);
                                    var item = new Array();
                                    item[0] = data.item.itemID
                                    item[1] = data.item.seller.userID;
                                    item[2] = data.item.title;
                                    item[3] = data.item.buyItNowAvailable;
                                    item[4] = data.item.currentPrice.currencyID + data.item.currentPrice.value;
                                    item[5] = data.item.shippingCostSummary.shippingServiceCost.currencyID + data.item.shippingCostSummary.shippingServiceCost.value;
                                    item[6] = data.item.quantity;
                                    item[7] = data.item.quantitySold;
                                    item[8] = data.item.galleryURL;
                                    item[9] = data.item.startTime;
                                    item[10] = data.item.endTime;
                                    item[11] = data.item.listingType.value;
                                    item[12] = data.item.listingStatus.value
                                    
                                    itemArray.push(item);
                                    /*
                                    data.item.buyItNowAvailable
                                    data.item.buyItNowPrice
                                    data.item.currentPrice.currencyID
                                    data.item.currentPrice.value
                                    data.item.endTime
                                    data.item.galleryURL
                                    data.item.itemID
                                    data.item.listingStatus.value
                                    data.item.listingType.value
                                    data.item.primaryCategoryName
                                    data.item.quantity
                                    data.item.quantitySold
                                    data.item.seller.userID
                                    data.item.shippingCostSummary.shippingServiceCost.currencyID
                                    data.item.shippingCostSummary.shippingServiceCost.value
                                    data.item.startTime
                                    data.item.timeLeft
                                    data.item.title
                                    console.log(data);
                                    */
                                    i++;
                                    
                                    if(i == itemTotalNum){
					//console.log(itemArray);
                                        //console.log("store load data.");
                                        store.loadData(itemArray);
                                        myMask.hide();
                                    }
                                }
                                
                                //---------------------------------  getSingleItemFailure  -----------------------------------------------------
                                var getSingleItemFailure = function(errors){
                                    myMask.hide();
                                    //console.log(errors);
                                    Ext.Msg.alert('Warn', errors.longMessage);
                                }
                        
                                if(!Ext.isEmpty(data.searchResult) && Ext.isArray(data.searchResult[0].itemArray.item)){
                                    itemTotalNum = data.searchResult[0].itemArray.item.length;
                                    //console.log("item is array, count " + itemTotalNum);
                                    myMask.show();
                                    for(j in data.searchResult[0].itemArray.item){
                                        if(!Ext.isEmpty(data.searchResult[0].itemArray.item[j].itemID)){
                                            var config = new com.ebay.shoppingservice.ShoppingConfig({appId: 'eBayAPID-73f4-45f2-b9a3-c8f6388b38d8'});
                                            var shopping = new com.ebay.shoppingservice.Shopping(config);
                                            var request = new com.ebay.shoppingservice.GetSingleItemRequestType({ItemID: data.searchResult[0].itemArray.item[j].itemID, IncludeSelector: 'Details,ShippingCosts'});
                                            var callback = new com.ebay.shoppingservice.ShoppingCallback({success: getSingleItemSuccess, failure: getSingleItemFailure});
                                            shopping.getSingleItem(request, callback);
                                        }
                                    }
                                    
                                }else{
                                    Ext.Msg.alert('Warn', 'No Result!');
                                    store.removeAll();
                                    /*
                                    console.log("item is single");
                                    itemTotalNum = 1;
                                    //console.log("4");
                                    var config = new com.ebay.shoppingservice.ShoppingConfig({appId: 'eBayAPID-73f4-45f2-b9a3-c8f6388b38d8'});
                                    var shopping = new com.ebay.shoppingservice.Shopping(config);
                                    var request = new com.ebay.shoppingservice.GetSingleItemRequestType({ItemID: data.item.itemID, IncludeSelector: 'Details,ShippingCosts'});
                                    var callback = new com.ebay.shoppingservice.ShoppingCallback({success: getSingleItemSuccess, failure: getSingleItemFailure});
                                    shopping.getSingleItem(request, callback);
                                    */
                                }
                                
                            }
                        
                            function findItemsAdvancedFailure(errors) {
                                myMask.hide();
                                //console.log(errors);
                                Ext.Msg.alert(errors[0].severityCode.value, errors[0].longMessage);
                            }
                            
                            //console.log({QueryKeywords: Ext.getCmp('keyword').getValue(), SellerID: Ext.getCmp('seller').getValue(), MaxEntries: 10, EndTimeFrom: Ext.getCmp('from').getValue().format('Y-m-d'), EndTimeTo: Ext.getCmp('to').getValue().format('Y-m-d')});
                            var config = new com.ebay.shoppingservice.ShoppingConfig({appId: 'eBayAPID-73f4-45f2-b9a3-c8f6388b38d8'});
                            var shopping = new com.ebay.shoppingservice.Shopping(config);
                            var request = new com.ebay.shoppingservice.FindItemsAdvancedRequestType({ItemsAvailableTo: countryCombo.getValue(), QueryKeywords: Ext.getCmp('keyword').getValue(), StoreName: Ext.getCmp('storeName').getValue(), SellerID: Ext.getCmp('seller').getValue(), MaxEntries: 10, EndTimeFrom: Ext.isEmpty(Ext.getCmp('from').getValue())?null:Ext.getCmp('from').getValue().format('Y-m-d'), EndTimeTo: Ext.isEmpty(Ext.getCmp('to').getValue())?null:Ext.getCmp('to').getValue().format('Y-m-d')});
                            var callback = new com.ebay.shoppingservice.ShoppingCallback({success: findItemsAdvancedSuccess, failure: findItemsAdvancedFailure});
                            shopping.findItemsAdvanced(request, callback);
                                
                        }
                    }]
            })
            grid.render("analyze-grid");
           
        }
    });
    
})