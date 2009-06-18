Ext.onReady(function(){
    Ext.BLANK_IMAGE_URL = "../../Ext/2.2/resources/images/default/s.gif";
    
    var cp = new Ext.state.CookieProvider({
       //path: "/eBayBO/analyze/",
       expires: new Date(new Date().getTime()+(1000*60*60*24*365)) //365 days
       //domain: "127.0.0.1"
    });
    Ext.state.Manager.setProvider(cp);
   
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
            
            var grid = new Ext.grid.GridPanel({
                title: 'eBay Item Analyze (<font color="red">Seller:you want to specify multiple values, use a comma.</font>)',
                autoHeight: true,
                store: store,
                autoScroll: true,
                //width: 600,
                selModel: new Ext.grid.RowSelectionModel({}),
                columns:[
                    {header: "Image", width: 100, align: 'center', sortable: true, dataIndex: 'galleryURL', renderer: renderImage},
                    {header: "Seller ID", width: 80, align: 'center', sortable: true, dataIndex: 'userID'},
                    {header: "Title", width: 320, align: 'center', sortable: true, dataIndex: 'title'},
                    {header: "Buy It Now", width: 70, align: 'center', sortable: true, dataIndex: 'buyItNowAvailable'},
                    {header: "Price", width: 55, align: 'center', sortable: true, dataIndex: 'currentPrice'},
                    {header: "shipping Cost", width: 100, align: 'center', sortable: true, dataIndex: 'shippingServiceCost', renderer: renderShippingServiceCost},
                    {header: "Quantity", width: 60, align: 'center', sortable: true, dataIndex: 'quantity'},
                    {header: "Sold Quantity", width: 80, align: 'center', sortable: true, dataIndex: 'quantitySold'},
                    {header: "Start Time", width: 130, align: 'center', sortable: true, dataIndex: 'startTime'},
                    {header: "End Time", width: 130, align: 'center', sortable: true, dataIndex: 'endTime'},
                    {header: "Type", width: 60, align: 'center', sortable: true, dataIndex: 'listingType'},
                    {header: "Status", width: 60, align: 'center', sortable: true, dataIndex: 'listingStatus'}
                ],
                tbar:[{
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
                        width: 300
                    }/*,{ text: 'Manage',
                        handler: function(){

                            var sellerForm = Ext.FormPanel({
                                items:[{
                                    xtype: 'textfield' 
                                }]
                            })
                            
                            
                            var sellerWindow = Ext.Window({
                                title: 'Manage Seller',
                                width: 400,
                                height: 300,
                                items: sellerForm,
                                plain:true,
                                layout: 'fit',
                                buttons: [{
                                    text:'Close',
                                    handler: function (){
                                       sellerWindow.close(); 
                                    }
                                }]
                            })
                            
                            sellerWindow.show();
                        }
                    }*/,'-',{
                        xtype: 'tbtext',
                        text: 'Keyword:'
                    },{
                        id: 'keyword',
                        name: 'keyword',
                        xtype: 'textfield',
                        width: 150
                    },'-',{
			xtype: 'tbtext',
                        text: 'End time from:'
		    },{
			id: 'from',
			name: 'from',
			xtype: 'datefield',
			format : 'Y-m-d'
		    },'-',{
			xtype: 'tbtext',
                        text: 'End time to:'
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
                                    }
                                }
                                
                                //---------------------------------  getSingleItemFailure  -----------------------------------------------------
                                var getSingleItemFailure = function(errors){
                                    //console.log(errors);
                                    Ext.Msg.alert('Warn', errors.longMessage);
                                }
                        
                                if(Ext.isArray(data.searchResult[0].itemArray.item)){
                                    itemTotalNum = data.searchResult[0].itemArray.item.length;
                                    //console.log("item is array, count " + itemTotalNum);
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
                                //console.log(errors);
                                Ext.Msg.alert(errors[0].severityCode.value, errors[0].longMessage);
                            }
                            
                            //console.log({QueryKeywords: Ext.getCmp('keyword').getValue(), SellerID: Ext.getCmp('seller').getValue(), MaxEntries: 10, EndTimeFrom: Ext.getCmp('from').getValue().format('Y-m-d'), EndTimeTo: Ext.getCmp('to').getValue().format('Y-m-d')});
                            var config = new com.ebay.shoppingservice.ShoppingConfig({appId: 'eBayAPID-73f4-45f2-b9a3-c8f6388b38d8'});
                            var shopping = new com.ebay.shoppingservice.Shopping(config);
                            var request = new com.ebay.shoppingservice.FindItemsAdvancedRequestType({QueryKeywords: Ext.getCmp('keyword').getValue(), StoreName: Ext.getCmp('storeName').getValue(), SellerID: Ext.getCmp('seller').getValue(), MaxEntries: 10, EndTimeFrom: Ext.isEmpty(Ext.getCmp('from').getValue())?null:Ext.getCmp('from').getValue().format('Y-m-d'), EndTimeTo: Ext.isEmpty(Ext.getCmp('to').getValue())?null:Ext.getCmp('to').getValue().format('Y-m-d')});
                            var callback = new com.ebay.shoppingservice.ShoppingCallback({success: findItemsAdvancedSuccess, failure: findItemsAdvancedFailure});
                            shopping.findItemsAdvanced(request, callback);
                                
                        }
                    }]
            })
            grid.render(document.body);
           
        }
    });
    
})