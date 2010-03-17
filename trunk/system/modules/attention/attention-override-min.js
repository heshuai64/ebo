Ext.override(QoDesk.Attention,{createWindow:function(){var h=this.app.getDesktop();var e=h.getWindow("attention-win");var b=new Date();b.setDate(b.getDate()-7);var g=new Date();var c=new Ext.data.JsonStore({root:"records",totalProperty:"totalCount",idProperty:"id",baseParams:{start_date:b,end_date:g},autoLoad:true,fields:["id","txnId","transactionTime","status","amountCurrency","amountValue","payeeId","payerId"],url:"connect.php?moduleId=qo-attention&action=getUnmapTransaction"});function a(i,k,j){return lang.attention.transactions_status_json[i]}function d(j,l,k){if(j>0){var i="green"}else{var i="red"}return String.format('<font color="'+i+'">{0} {1}</font>',k.data.amountCurrency,j)}var f=new Ext.grid.GridPanel({autoHeight:true,store:c,columns:[{header:lang.attention.grid_transactions_payee_id,dataIndex:"payeeId",width:110,align:"center",sortable:true},{header:lang.attention.grid_transactions_id,dataIndex:"id",width:110,align:"center",sortable:true},{header:lang.attention.grid_payer_id,dataIndex:"payerId",width:120,align:"center",sortable:true},{header:lang.attention.grid_txn_id,dataIndex:"txnId",width:120,align:"center",sortable:true},{header:lang.attention.grid_transactions_time,dataIndex:"transactionTime",width:150,align:"center",sortable:true},{header:lang.attention.grid_amount,dataIndex:"amountValue",width:100,renderer:d,align:"center",sortable:true},{header:lang.attention.grid_status,dataIndex:"status",width:100,renderer:a,align:"center",sortable:true}],tbar:[{xtype:"tbtext",text:"Seller:"},{xtype:"textfield",id:"payeeId",name:"payeeId"},{xtype:"tbseparator"},{xtype:"tbtext",text:"Start Date:"},{xtype:"datefield",id:"start_date",name:"start_date",format:"Y-m-d",value:b},{xtype:"tbseparator"},{xtype:"tbtext",text:"End Date:"},{xtype:"datefield",id:"end_date",name:"end_date",format:"Y-m-d",value:g},{xtype:"tbbutton",text:"Submit",handler:function(){c.baseParams={payeeId:document.getElementById("payeeId").value,start_date:document.getElementById("start_date").value,end_date:document.getElementById("end_date").value};c.load({params:{start:0,limit:10}})}}],bbar:new Ext.PagingToolbar({pageSize:10,store:c,displayInfo:true})});f.on("rowdblclick",function(j){var i=j.getSelectionModel().getSelected();window.open("/eBayBO/transactions.php?id="+i.data.id,"_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=1000, height=800")});if(!e){e=h.createWindow({id:"attention-win",title:lang.attention.window_title,width:830,height:550,iconCls:"attention-icon",shim:false,animCollapse:false,constrainHeader:true,layout:"fit",items:[{xtype:"panel",title:lang.attention.un_map_transaction_grid_title,autoHeight:true,items:f}],taskbuttonTooltip:"<b>注意</b><br />系统提醒"})}e.show()}});