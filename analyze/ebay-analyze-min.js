Ext.onReady(function(){Ext.BLANK_IMAGE_URL="../../Ext/2.2/resources/images/default/s.gif";com.ebay.widgets.needs({baseUrl:"http://w-1.ebay.com/js/607/min/",files:["FindItemsAdvanced.js","GetSingleItem.js"],resources:[].concat(com.ebay.shoppingservice.Shopping.getSingleItem,com.ebay.shoppingservice.Shopping.findItemsAdvanced),callback:function(){var a=new Ext.data.ArrayReader({},[{name:"itemID"},{name:"userID"},{name:"title"},{name:"buyItNowAvailable"},{name:"currentPrice"},{name:"shippingServiceCost"},{name:"quantity"},{name:"quantitySold"},{name:"galleryURL"},{name:"startTime"},{name:"endTime",type:"date",dateFormat:"y-m-d H:i:s"},{name:"listingType"},{name:"listingStatus"}]);var c=new Ext.data.Store({reader:a});function e(f,h,g){return String.format('<img src="{0}"/>',f)}function b(f,h,g){if(f.substr(3)=="0"||f.substr(3)==0){return String.format('<font color="red">{0}</font>',f)}else{return String.format('<font color="green">{0}</font>',f)}}var d=new Ext.grid.GridPanel({title:"eBay Item Analyze",autoHeight:true,store:c,autoScroll:true,selModel:new Ext.grid.RowSelectionModel({}),columns:[{header:"Image",width:100,align:"center",sortable:true,dataIndex:"galleryURL",renderer:e},{header:"Seller ID",width:80,align:"center",sortable:true,dataIndex:"userID"},{header:"Title",width:320,align:"center",sortable:true,dataIndex:"title"},{header:"Buy It Now",width:70,align:"center",sortable:true,dataIndex:"buyItNowAvailable"},{header:"Price",width:55,align:"center",sortable:true,dataIndex:"currentPrice"},{header:"shipping Cost",width:100,align:"center",sortable:true,dataIndex:"shippingServiceCost",renderer:b},{header:"Quantity",width:60,align:"center",sortable:true,dataIndex:"quantity"},{header:"Sold Quantity",width:80,align:"center",sortable:true,dataIndex:"quantitySold"},{header:"Start Time",width:130,align:"center",sortable:true,dataIndex:"startTime"},{header:"End Time",width:130,align:"center",sortable:true,dataIndex:"endTime"},{header:"Type",width:60,align:"center",sortable:true,dataIndex:"listingType"},{header:"Status",width:60,align:"center",sortable:true,dataIndex:"listingStatus"}],tbar:[{xtype:"tbtext",text:"Seller:"},{id:"seller",name:"seller",xtype:"textfield",width:300},"-",{xtype:"tbtext",text:"Keyword:"},{id:"keyword",name:"keyword",xtype:"textfield",width:200},"-",{xtype:"tbtext",text:"End time from:"},{id:"from",name:"from",xtype:"datefield",format:"Y-m-d"},"-",{xtype:"tbtext",text:"End time to:"},{id:"to",name:"to",xtype:"datefield",format:"Y-m-d"},{text:"Submit",handler:function(){function h(p){var q=0;var m=new Array();var s=0;var r=function(x){var w=new Array();w[0]=x.item.itemID;w[1]=x.item.seller.userID;w[2]=x.item.title;w[3]=x.item.buyItNowAvailable;w[4]=x.item.currentPrice.currencyID+x.item.currentPrice.value;w[5]=x.item.shippingCostSummary.shippingServiceCost.currencyID+x.item.shippingCostSummary.shippingServiceCost.value;w[6]=x.item.quantity;w[7]=x.item.quantitySold;w[8]=x.item.galleryURL;w[9]=x.item.startTime;w[10]=x.item.endTime;w[11]=x.item.listingType.value;w[12]=x.item.listingStatus.value;m.push(w);q++;if(q==s){c.loadData(m)}};var v=function(w){console.log(w)};if(Ext.isArray(p.searchResult[0].itemArray.item)){s=p.searchResult[0].itemArray.item.length;for(j in p.searchResult[0].itemArray.item){if(!Ext.isEmpty(p.searchResult[0].itemArray.item[j].itemID)){var n=new com.ebay.shoppingservice.ShoppingConfig({appId:"eBayAPID-73f4-45f2-b9a3-c8f6388b38d8"});var t=new com.ebay.shoppingservice.Shopping(n);var o=new com.ebay.shoppingservice.GetSingleItemRequestType({ItemID:p.searchResult[0].itemArray.item[j].itemID,IncludeSelector:"Details,ShippingCosts"});var u=new com.ebay.shoppingservice.ShoppingCallback({success:r,failure:v});t.getSingleItem(o,u)}}}else{}}function f(m){console.log(m)}var g=new com.ebay.shoppingservice.ShoppingConfig({appId:"eBayAPID-73f4-45f2-b9a3-c8f6388b38d8"});var k=new com.ebay.shoppingservice.Shopping(g);var i=new com.ebay.shoppingservice.FindItemsAdvancedRequestType({QueryKeywords:Ext.getCmp("keyword").getValue(),SellerID:Ext.getCmp("seller").getValue(),MaxEntries:10,EndTimeFrom:Date.parseDate(Ext.getCmp("from").getValue(),"Y-m-d"),EndTimeTo:Date.parseDate(Ext.getCmp("to").getValue(),"Y-m-d")});var l=new com.ebay.shoppingservice.ShoppingCallback({success:h,failure:f});k.findItemsAdvanced(i,l)}}]});d.render(document.body)}})});