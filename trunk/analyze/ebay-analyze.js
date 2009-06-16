Ext.onReady(function(){
    Ext.BLANK_IMAGE_URL = "../../Ext/2.2/resources/images/default/s.gif";
    
    var inventory_store = new Ext.data.JsonStore({
        root: 'records',
        totalProperty: 'totalCount',
        idProperty: 'id',
        fields: ['ItemID', 'Country', 'SellerUserID', 'Title', 'BuyItNowAvailable', 'BuyItNowPrice', 'CurrentPrice', 'Quantity', 'QuantitySold']
    });
     
    function onSuccess(/*com.ebay.shoppingservice.FindItemsResponseType*/ data) {
        console.log("1");
        console.log(data.item);
        
        var getSingleItemSuccess = function(data){
            console.log("5");
            console.log(data);
        }
        
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
                console.log("2");
                if(Ext.isArray(data.item)){
                    console.log("3");
                    for(i in data.item){
                        if(!Ext.isEmpty(data.item[i].itemID)){
                            console.log(data.item[i].itemID);
                            var config = new com.ebay.shoppingservice.ShoppingConfig({appId: 'eBayAPID-73f4-45f2-b9a3-c8f6388b38d8'});
                            var shopping = new com.ebay.shoppingservice.Shopping(config);
                            var request = new com.ebay.shoppingservice.GetSingleItemRequestType({ItemID: data.item[i].itemID, IncludeSelector: 'Details,ShippingCosts'});
                            var callback = new com.ebay.shoppingservice.ShoppingCallback({success: getSingleItemSuccess, failure: getSingleItemFailure});
                            shopping.getSingleItem(request, callback);
                        }
                    }
                }else{
                    console.log("4");
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

    function onFailure(/*[com.ebay.shoppingservice.ErrorType]*/ errors) {
        console.log(errors);
    }
    
    
    com.ebay.widgets.needs({
        baseUrl: 'http://w-1.ebay.com/js/607/min/',
        files: ['GetSingleItem.js', 'FindItems.js'],
	resources: [].concat(com.ebay.shoppingservice.Shopping.getSingleItem, com.ebay.shoppingservice.Shopping.findItems),
        callback: function() {
            var config = new com.ebay.shoppingservice.ShoppingConfig({appId: 'eBayAPID-73f4-45f2-b9a3-c8f6388b38d8'});
            var shopping = new com.ebay.shoppingservice.Shopping(config);
            var request = new com.ebay.shoppingservice.FindItemsRequestType({QueryKeywords: 'ipod', MaxEntries: 1});
            var callback = new com.ebay.shoppingservice.ShoppingCallback({success: onSuccess, failure: onFailure});
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