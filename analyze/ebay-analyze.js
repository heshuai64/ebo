Ext.onReady(function(){
    Ext.BLANK_IMAGE_URL = "../../Ext/2.2/resources/images/default/s.gif";

    function findItemsSuccess(/*com.ebay.shoppingservice.FindItemsResponseType*/ data) {
        console.log("findItemsSuccess");
        console.log(data);
	
	var i = 0;
	var itemArray = new Array();
	var itemTotalNum = 0;
    
	//---------------------------------  getSingleItemSuccess  -----------------------------------------------------
        var getSingleItemSuccess = function(data){
            console.log("getSingleItemSuccess (" + i + ")");
	    itemArray[i][0] = data.item.itemID
	    itemArray[i][1] = data.item.seller.userID;
	    itemArray[i][2] = data.item.title;
	    itemArray[i][3] = data.item.buyItNowAvailable;
	    itemArray[i][4] = data.item.currentPrice.currencyID + data.item.currentPrice.value;
	    itemArray[i][5] = data.item.shippingCostSummary.shippingServiceCost.currencyID + data.item.shippingCostSummary.shippingServiceCost.value;
	    itemArray[i][6] = data.item.quantity;
	    itemArray[i][7] = data.item.quantitySold;
	    itemArray[i][8] = data.item.galleryURL;
	    itemArray[i][9] = data.item.startTime;
	    itemArray[i][10] = data.item.endTime;
	    itemArray[i][11] = data.item.listingType.value;
	    itemArray[i][12] = data.item.listingStatus.value
	    
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
	    console.log(i);
	    if(i == itemTotalNum){
		console.log("render grid");
		var reader = new Ext.data.ArrayReader({}, [
		    {name: 'itemID'},
		    {name: 'userID'},
		    {name: 'title'},
		    {name: 'buyItNowAvailable'},
		    {name: 'currentPrice', type: 'float'},
		    {name: 'shippingServiceCost', type: 'float'},
		    {name: 'quantity'},
		    {name: 'quantitySold'},
		    {name: 'galleryURL'},
		    {name: 'startTime', type: 'date'},
		    {name: 'endTime', type: 'date'},
		    {name: 'listingType'},
		    {name: 'listingStatus'}
		]);
			
		var grid = new Ext.grid.GridPanel({
		    //title: 'Waiting To Upload SKU List',
		    //autoHeight: true,
		    store: new Ext.data.Store({
			reader: reader,
			data: itemArray
		    }),
		    autoScroll: true,
		    //width: 600,
		    selModel: new Ext.grid.RowSelectionModel({}),
		    columns:[
			{header: "Seller ID", width: 80, align: 'center', sortable: true, dataIndex: 'UserID'},
			{header: "Title", width: 120, align: 'center', sortable: true, dataIndex: 'title'},
			{header: "BuyItNowAvailable", width: 50, align: 'center', sortable: true, dataIndex: 'buyItNowAvailable'},
			{header: "Price", width: 50, align: 'center', sortable: true, dataIndex: 'currentPrice'},
			{header: "shipping Cost", width: 50, align: 'center', sortable: true, dataIndex: 'shippingServiceCost'},
			{header: "Quantity", width: 50, align: 'center', sortable: true, dataIndex: 'quantity'},
			{header: "Sold Quantity", width: 50, align: 'center', sortable: true, dataIndex: 'quantitySold'},
			{header: "Image", width: 100, align: 'center', sortable: true, dataIndex: 'galleryURL'},
			{header: "Start Time", width: 60, align: 'center', sortable: true, dataIndex: 'startTime'},
			{header: "End Time", width: 60, align: 'center', sortable: true, dataIndex: 'endTime'},
			{header: "Type", width: 60, align: 'center', sortable: true, dataIndex: 'listingType'},
			{header: "Status", width: 60, align: 'center', sortable: true, dataIndex: 'listingStatus'}
		    ]
		})
		
		grid.render(document.body);
	    }
        }
	
        //---------------------------------  getSingleItemFailure  -----------------------------------------------------
        var getSingleItemFailure = function(errors){
            console.log(errors);
        }
        /*
        com.ebay.widgets.needs({
            baseUrl: 'http://w-1.ebay.com/js/607/min/',
            files: ['GetSingleItem.js'],
            resources: com.ebay.shoppingservice.Shopping.getSingleItem,
            callback: function() {
        */
	//http://open.api.ebay.com/shopping?callname=GetSingleItem&callbackname=com.ebay.shoppingservice.Shopping.getSingleItemClosure1&responseencoding=JSON&callback=true&version=607&appId=eBayAPID-73f4-45f2-b9a3-c8f6388b38d8&ItemID=390059143734&IncludeSelector=Details%2CShippingCosts&client=js
	//console.log("2");
	if(Ext.isArray(data.item)){
	    //console.log("3");
	    itemTotalNum = data.item.length;
	    console.log("item is array, count " + itemTotalNum);
	    for(j in data.item){
		if(!Ext.isEmpty(data.item[j].itemID)){
		    var config = new com.ebay.shoppingservice.ShoppingConfig({appId: 'eBayAPID-73f4-45f2-b9a3-c8f6388b38d8'});
		    var shopping = new com.ebay.shoppingservice.Shopping(config);
		    var request = new com.ebay.shoppingservice.GetSingleItemRequestType({ItemID: data.item[j].itemID, IncludeSelector: 'Details,ShippingCosts'});
		    var callback = new com.ebay.shoppingservice.ShoppingCallback({success: getSingleItemSuccess, failure: getSingleItemFailure});
		    shopping.getSingleItem(request, callback);
		}
	    }
	}else{
	    console.log("item is single");
	    itemTotalNum = 1;
	    //console.log("4");
	    var config = new com.ebay.shoppingservice.ShoppingConfig({appId: 'eBayAPID-73f4-45f2-b9a3-c8f6388b38d8'});
	    var shopping = new com.ebay.shoppingservice.Shopping(config);
	    var request = new com.ebay.shoppingservice.GetSingleItemRequestType({ItemID: data.item.itemID, IncludeSelector: 'Details,ShippingCosts'});
	    var callback = new com.ebay.shoppingservice.ShoppingCallback({success: getSingleItemSuccess, failure: getSingleItemFailure});
	    shopping.getSingleItem(request, callback);
	}
        /*
            }
        });
        */
    }

    function findItemsFailure(/*[com.ebay.shoppingservice.ErrorType]*/ errors) {
        console.log(errors);
    }
    
    //http://open.api.ebay.com/shopping?callname=FindItems&callbackname=com.ebay.shoppingservice.Shopping.findItemsClosure0&responseencoding=JSON&callback=true&version=607&appId=eBayAPID-73f4-45f2-b9a3-c8f6388b38d8&QueryKeywords=ipod&MaxEntries=1&client=js
    com.ebay.widgets.needs({
        baseUrl: 'http://w-1.ebay.com/js/607/min/',
        files: ['GetSingleItem.js', 'FindItems.js'],
	resources: [].concat(com.ebay.shoppingservice.Shopping.getSingleItem, com.ebay.shoppingservice.Shopping.findItems),
        callback: function() {
            var config = new com.ebay.shoppingservice.ShoppingConfig({appId: 'eBayAPID-73f4-45f2-b9a3-c8f6388b38d8'});
            var shopping = new com.ebay.shoppingservice.Shopping(config);
            var request = new com.ebay.shoppingservice.FindItemsRequestType({QueryKeywords: 'ipod', MaxEntries: 1});
            var callback = new com.ebay.shoppingservice.ShoppingCallback({success: findItemsSuccess, failure: findItemsFailure});
            shopping.findItems(request, callback);
        }
    });
    
    /*
    var getSingleItemSuccess = function(data){
        console.log(data);
    }
    
    var getSingleItemFailure = function(errors){
        console.log(errors);
    }
      
    com.ebay.widgets.needs({
        baseUrl: 'http://w-1.ebay.com/js/607/min/',
        files: ['GetSingleItem.js'],
        resources: com.ebay.shoppingservice.Shopping.getSingleItem,
        callback: function() {
            var config = new com.ebay.shoppingservice.ShoppingConfig({appId: 'eBayAPID-73f4-45f2-b9a3-c8f6388b38d8'});
            var shopping = new com.ebay.shoppingservice.Shopping(config);
            var request = new com.ebay.shoppingservice.GetSingleItemRequestType({ItemID: '220433866152', IncludeSelector: 'Details,ShippingCosts'});
            var callback = new com.ebay.shoppingservice.ShoppingCallback({success: getSingleItemSuccess, failure: getSingleItemFailure});
            shopping.getSingleItem(request, callback);
        }
    });
    */
})