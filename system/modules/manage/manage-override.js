/* Override the module code here.
 * This code will be Loaded on Demand.
 */

Ext.override(QoDesk.Manage, {

    createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('manage-win');
	var saveComplete = function(notifyWin, title, msg){
			notifyWin.setIconClass('x-icon-done');
			notifyWin.setTitle(title);
			notifyWin.setMessage(msg);
			desktop.hideNotification(notifyWin);
		}
		
	var userManage = function(){
	    
	    var userManageWin = desktop.getWindow('user-manage-win');
	    
	    if(!userManageWin){
		var store = new Ext.data.JsonStore({
		    root: 'result.member_info',
		    fields: ['id','email_address','password','active','group_id','group_name'],
		    url:'connect.php?moduleId=qo-manage&action=getAllMember'
		});
		
		var notifyWin = desktop.showNotification({
				    html: '加载用户信息中...', 
				    title: '请等待'
				});
		var loadMemberInfComplete = function(S, r){
		    var group = S.reader.jsonData.result.group_info;
		    saveComplete(notifyWin, '完成', '加载用户信息成功.');
		    //console.log(group);
		    
		    var showGroupName = function(val){
			for(i in group){
			    if(group[i].group_id == val){
				return group[i].group_name;
			    }
			}
		    }
		    
		    var gridForm = new Ext.FormPanel({
			id: 'user-manage-form',
			frame: true,
			labelAlign: 'left',
			//title: '用户管理',
			bodyStyle:'padding:5px',
			//width: 750,
			layout: 'column',	
			items: [{
			    columnWidth: 0.55,
			    layout: 'fit',
			    items: {
				    xtype: 'grid',
				    store: store,
				    columns:[
					    {id:'id', header: "id", width: 20, sortable: true, locked:false, dataIndex: 'id'},
					    {header: "用户名", width: 75, sortable: true,  dataIndex: 'email_address'},
					    {header: "密码", width: 75, sortable: true, dataIndex: 'password'},
					    {header: "激活", width: 75, sortable: true, dataIndex: 'active'},
					    {header: "组", width: 75, sortable: true, dataIndex: 'group_id', renderer: showGroupName}
					],
				    sm: new Ext.grid.RowSelectionModel({
					singleSelect: true,
					listeners: {
					    rowselect: function(sm, row, rec) {
						Ext.getCmp("user-manage-form").getForm().loadRecord(rec);
					    }
					}
				    }),
				    height: 350,
				    title:'用户列表',
				    border: true,
				    listeners: {
					    render: function(g) {
						    g.getSelectionModel().selectRow(0);
					    },
					    delay: 10 // Allow rows to be rendered.
				    }
				}
			},{
			    columnWidth: 0.45,
			    xtype: 'fieldset',
			    labelWidth: 45,
			    //title:'用户信息',
			    defaults: {width: 130},	// Default config options for child items
			    defaultType: 'textfield',
			    autoHeight: true,
			    bodyStyle: Ext.isIE ? 'padding:0 0 5px 15px;' : 'padding:10px 15px;',
			    border: false,
			    style: {
				"margin-left": "10px", // when you add custom margin in IE 6...
				"margin-right": Ext.isIE6 ? (Ext.isStrict ? "-10px" : "-13px") : "0"  // you have to adjust for it somewhere else
			    },
			    items: [{
				fieldLabel: 'Id',
				name: 'id'
			    },{
				fieldLabel: '用户名',
				name: 'email_address'
			    },{
				fieldLabel: '密码',
				name: 'password'
			    },{
				fieldLabel: '激活',
				name: 'active'
			    },{
				id:'group_list',
				xtype: 'combo',
				fieldLabel: '用户组',
				mode: 'local',
				store: new Ext.data.JsonStore({
				    fields: ['group_id', 'group_name'],
				    data : group
				}),
				valueField:'group_id',
				displayField:'group_name',
				triggerAction: 'all',
				editable: false,
				selectOnFocus:true,
				name: 'group_id',
				hiddenName:'group_id'
			    }
			    ]
			}],
			buttons: [{
			    text: '添加用户'
			},{
			    text: '保存选中的用户',
			    handler: function(){
				var notifyWin = desktop.showNotification({
					html: '保存用户信息中...'
					, title: '请等待'
				});
				var memberForm = Ext.getCmp("user-manage-form").getForm();
				
				Ext.Ajax.request({
				    url: "connect.php",
				    params: "moduleId=qo-manage&action=updateMember&id="+memberForm.items.items[0].value+"&email_address="+memberForm.items.items[1].value+"&password="+memberForm.items.items[2].value+"&active="+memberForm.items.items[3].value,
				    success: function(o){
					saveComplete(notifyWin, '完成', '保存用户信息成功.');
				    },
				    failure: function(){
						saveComplete(notifyWin, 'Error', 'Lost connection to server.');
					    },
					    scope: this
				});
			    }
			},{
			    text: '删除选中用户'
			},{
			    text: '关闭窗口',
			    handler: function(){
				userManageWin.close();
			    }
			}]
		    });
				
		    userManageWin = desktop.createWindow({
					id: 'user-manage-win',
					title:'用户管理',
					width:600,
					height:500,
					iconCls: 'user-manage-icon',
					shim:false,
					animCollapse:false,
					constrainHeader:true,
					layout: 'fit',
					items: gridForm,
					taskbuttonTooltip: '<b>用户管理</b><br />添加、修改、删除用户'
				    });
				    
		    userManageWin.show();
		}
		store.on('load', loadMemberInfComplete);
		store.load();
		
		
		
	    }else{
		userManageWin.show();
	    }
	}
	
	var groupManage = function(){
	    var groupManageWin = desktop.getWindow('group-manage-win');
	    if(!groupManageWin){
		var store = new Ext.data.JsonStore({
		    fields: ['id','name','description','active'],
		    url:'connect.php?moduleId=qo-manage&action=getAllGroup'
		});
		
		var grid = new Ext.grid.GridPanel({
				    store: store,
				    columns:[{
					id: 'id', // id assigned so we can apply custom css (e.g. .x-grid-col-topic b { color:#333 })
					header: "Id",
					dataIndex: 'id',
					width: 20,
					sortable: true
				    },{
					header: "组名",
					dataIndex: 'name',
					width: 90,
					sortable: true
				    },{
					header: "描述",
					dataIndex: 'description',
					width: 150,
					sortable: true
				    },{
					header: "激活",
					dataIndex: 'active',
					width: 50,
					sortable: true
				    }],
				    sm: new Ext.grid.RowSelectionModel({
					singleSelect: true,
					listeners: {
					    rowselect: function(sm, row, rec) {
						Ext.getCmp("group-manage-form").getForm().loadRecord(rec);
					    }
					}
				    }),
				    //autoExpandColumn: 'company',
				    height: 350,
				    title:'用户组列表',
				    border: true,
				    listeners: {
					    render: function(g) {
						    g.getSelectionModel().selectRow(0);
					    },
					    delay: 10 // Allow rows to be rendered.
				    }});
		
		var gridForm = new Ext.FormPanel({
			id: 'group-manage-form',
			frame: true,
			labelAlign: 'left',
			//title: '用户组管理',
			bodyStyle:'padding:5px',
			//width: 750,
			layout: 'column',	// Specifies that the items will now be arranged in columns
			items: [{
			    columnWidth: 0.55,
			    layout: 'fit',
			    items: grid
			},{
			    columnWidth: 0.45,
			    xtype: 'fieldset',
			    labelWidth: 45,
			    //title:'组信息',
			    defaults: {width: 160},	// Default config options for child items
			    defaultType: 'textfield',
			    autoHeight: true,
			    bodyStyle: Ext.isIE ? 'padding:0 0 5px 15px;' : 'padding:10px 15px;',
			    border: false,
			    style: {
				"margin-left": "10px", // when you add custom margin in IE 6...
				"margin-right": Ext.isIE6 ? (Ext.isStrict ? "-10px" : "-13px") : "0"  // you have to adjust for it somewhere else
			    },
			    items: [{
				fieldLabel: 'Id',
				name: 'id'
			    },{
				fieldLabel: '组名',
				name: 'name'
			    },{
				fieldLabel: '描述',
				name: 'description'
			    },{
				fieldLabel: '激活',
				name: 'active'
			    }
			    ]
			}],
			buttons: [{
			    text: '添加组'
			},{
			    text: '保存选中的组',
			    handler: function(){
			    }
			},{
			    text: '删除选中的组'
			},{
			    text: '关闭窗口',
			    handler: function(){
				groupManageWin.close();
			    }
			}]
		    });
		var notifyWin = desktop.showNotification({
					html: '加载用户组信息中...'
					, title: '请等待'
				});
		
		var loadGroupInfoComplete = function(){
		    saveComplete(notifyWin, '完成', '加载用户组信息成功.');
		    groupManageWin = desktop.createWindow({
			id: 'group-manage-win',
			title:'用户组管理',
			width:600,
			height:500,
			iconCls: 'group-manage-icon',
			shim:false,
			animCollapse:false,
			constrainHeader:true,
			layout: 'fit',
			items: gridForm,
			taskbuttonTooltip: '<b>用户组管理</b><br />添加、修改、删除用户组'
		    });
		    groupManageWin.show();
		}
		store.on('load', loadGroupInfoComplete);
		store.load();
		
		
	    }else{
		groupManageWin.show();
	    }
	}
	
	var privilegeManage = function(){
	    var privilegeManageWin = desktop.getWindow('privilege-manage-win');
	    if(!privilegeManageWin){
		var notifyWin = desktop.showNotification({
			html: '加载权限信息中...',
			title: '请等待'
		});
		
		Ext.Ajax.request({
			url: 'connect.php',
			params: 'moduleId=qo-manage&action=getPrivilegeInfo',
			success: function(o){
				if(o && o.responseText){
				    saveComplete(notifyWin, '完成', '加载权限信息成功.');
				    var data = eval(o.responseText);
				    var groupHtml = "";
				    var checked = "";
				    
				    var formHtml = '<form id="privilegeForm">'+groupHtml+'</form>';
				    
				    privilegeManageWin = desktop.createWindow({
					id: 'privilege-manage-win',
					title:'权限管理',
					width:600,
					height:500,
					iconCls: 'privilege-manage-icon',
					shim:false,
					autoScroll: true,
					animCollapse:false,
					constrainHeader:true,
					layout: 'fit',
					html: formHtml,
					taskbuttonTooltip: '<b>权限管理</b><br />设置权限于相应的功能'
				    });
				    privilegeManageWin.show(); 
				}else{
				    saveComplete(notifyWin, '失败', '加载组权限信息失败.');
				}
			},
			failure: function(){
				saveComplete(notifyWin, '失败', '不能连接服务器.');
			},
			scope: this
		});
		
	    }else{
		privilegeManage.show();
	    }
	}
	
	var groupPrivilegeManage = function(){
	    var groupPrivilegeManageWin = desktop.getWindow('group-privilege-manage-win');
	    if(!groupPrivilegeManageWin){
		var notifyWin = desktop.showNotification({
			html: '加载组权限信息中...',
			title: '请等待'
		});
				
		Ext.Ajax.request({
			url: 'connect.php',
			params: 'moduleId=qo-manage&action=getGroupDomainPrivilege',
			success: function(o){
				if(o && o.responseText){
				    saveComplete(notifyWin, '完成', '加载组权限信息成功.');
				    var data = eval(o.responseText);
				    var groupHtml = "";
				    //console.log(data[0].group_privilege);
				    for(i in data[0].group){
					if(typeof(data[0].group[i]) != 'function'){
					    if(i % 2 ==0){
						groupHtml += "<div class='dual-group privilege-group'><fieldset>"
					    }else{
						groupHtml += "<div class='singular-group privilege-group'><fieldset>"
					    }
					    groupHtml += "<legend>"+data[0].group[i].name+"</legend>";
					    /*
					    for(l in data[0].domain){
						if(typeof(data[0].domain[l]) != 'function'){
						    groupHtml += '<div class="domain">' + data[0].domain[l].name +':<img width="12px" src="resources/images/default/s.gif"/>';
						    for(j in data[0].privilege){
							if(typeof(data[0].privilege[j]) != 'function'){
							    for(k in data[0].group_domain_privilege){
								if(typeof(data[0].group_domain_privilege[k]) != 'function'){
								    if(data[0].group[i].id == data[0].group_domain_privilege[k].qo_groups_id &&
								       data[0].domain[l].id == data[0].group_domain_privilege[k].qo_domains_id &&
								       data[0].privilege[j].id == data[0].group_domain_privilege[k].qo_privileges_id){
									    if(data[0].group_domain_privilege[k].is_allowed == "1"){
										checked = 'checked="checked"';
									    }
									    groupHtml += '<input id="'+data[0].group[i].id+"_"+data[0].domain[l].id+"_"+data[0].privilege[j].id+'" '+checked+' type="checkbox"/>      ' + data[0].privilege[j].name + '<img width="12px" src="resources/images/default/s.gif"/>'; 
								    }
								}
							    }
							}
						    }
						    groupHtml += '</div>';
						}
					    }
					    */
					    for(j in data[0].privilege){
						if(typeof(data[0].privilege[j]) != 'function'){
						    for(k in data[0].group_domain_privilege){
							var checked = "";
							if(typeof(data[0].group_domain_privilege[k]) != 'function'){
							    if(data[0].group[i].id == data[0].group_domain_privilege[k].qo_groups_id &&
							       data[0].privilege[j].id == data[0].group_domain_privilege[k].qo_privileges_id){
								    //console.log(data[0].group_domain_privilege[k].is_allowed);
								    if(data[0].group_domain_privilege[k].is_allowed == "1"){
									checked = 'checked="checked"';
								    }
								    groupHtml += '<input id="'+data[0].group[i].id+"_"+data[0].privilege[j].id+'" '+checked+' type="checkbox"/>      ' + data[0].privilege[j].name + '<img width="12px" src="resources/images/default/s.gif"/>'; 
							    }
							}
						    }
						}
					    }
					    groupHtml += "</fieldset></div>";
					}
				    }
				    var formHtml = '<form id="group-privilege-form">'+groupHtml+'\
						    <div style="text-align:center;"><button id="save" type="button">保存</button>\
						         <button id="close" type="button">关闭</button>\
						    </div></form>';
				    groupPrivilegeManageWin = desktop.createWindow({
					id: 'group-privilege-manage-win',
					title:'组权限管理',
					width:600,
					height:500,
					iconCls: 'group-privilege-manage-icon',
					shim:false,
					autoScroll: true,
					animCollapse:false,
					constrainHeader:true,
					layout: 'fit',
					html: formHtml,
					taskbuttonTooltip: '<b>组权限管理</b><br />管理用户组的权限'
				    });
				    groupPrivilegeManageWin.show();
				    
				    var saveGroupPrivilegeInfo = function(){
					    var p = "";
					    var test = function(p1){
						if(p1.checked){
						    p += p1.id + "=1@";
						}else{
						    p += p1.id + "=0@";
						}
					    }
					   
					    Ext.each(Ext.query("input"), test);
					    console.log(p);
					    
					    Ext.Ajax.request({
						waitMsg: 'Please wait...',
						url: 'connect.php?moduleId=qo-manage&action=saveGroupPrivilege&data='+p,
						success: function(response){
						    var result = eval(response.responseText);
						    switch (result) {
							case 1:
							    
							    break;
							default:
							    Ext.MessageBox.alert('Uh uh...', 'We couldn\'t save him...');
							    break;
						    }
						},
						failure: function(response){
						    var result = response.responseText;
						    Ext.MessageBox.alert('error', 'could not connect to the database. retry later');
						}
					    });		
				    }
				    
				    var closeGroupPrivilegeWindow = function(){
					groupPrivilegeManageWin.close();
				    }
				    
				    Ext.EventManager.addListener("save", "click", saveGroupPrivilegeInfo);
				    Ext.EventManager.addListener("close", "click", closeGroupPrivilegeWindow);
				}else{
				    saveComplete(notifyWin, '失败', '加载组权限信息失败.');
				}
			},
			failure: function(){
				saveComplete(notifyWin, '失败', '不能连接服务器.');
			},
			scope: this
		});
			
	    }else{
		groupPrivilegeManageWin.show();
	    }
	}
	
	var ebayManage = function(){
	    var ebayManageWin = desktop.getWindow('ebay-manage-win');
	    if(!ebayManageWin){
		
		var store = new Ext.data.JsonStore({
		    root: 'result',
		    autoLoad: true,
		    fields: ['id','email','status','token','tokenExpiry','currency','site'],
		    url:'connect.php?moduleId=qo-manage&action=getAllEbaySeller'
		});
		
		var ebayManageForm = new Ext.FormPanel({
			id: 'ebay-manage-form',
			frame: true,
			labelAlign: 'left',
			bodyStyle:'padding:5px',
			labelWidth:75,
			//width: 750,
			layout:"column",
			items:[{
			    columnWidth: 0.3,
			    layout: 'fit',
			    items: {
				    id:'ebay-manage-grid',
				    xtype: 'grid',
				    store: store,
				    columns:[
					    {id:'id', header: "id", width: 120, sortable: true, locked:false, dataIndex: 'id'},
					    {header: "Email", width: 120, sortable: true,  dataIndex: 'email'}
					],
				    sm: new Ext.grid.RowSelectionModel({
					singleSelect: true,
					listeners: {
					    rowselect: function(sm, row, rec) {
						Ext.getCmp("ebay-manage-form").getForm().loadRecord(rec);
					    }
					}
				    }),
				    height: 350,
				    title:'eBay账户列表',
				    border: true,
				    listeners: {
					    render: function(g) {
						    g.getSelectionModel().selectRow(0);
					    },
					    delay: 10 // Allow rows to be rendered.
				    }
				}
			},{
			    columnWidth:0.7,
			    layout:"form",
			    items:[{
				layout:"column",
				items:[{
				    columnWidth:0.5,
				    layout:"form",
				    items:[{
					xtype:"textfield",
					fieldLabel:"ID",
					name:"id"
				      },{
					xtype:"textfield",
					fieldLabel:"Email",
					name:"email"
				      },{
					xtype:"combo",
					fieldLabel:"Status",
					name:"status",
					width:80,
					hiddenName:"status"
				      }]
				  },{
				    columnWidth:0.5,
				    layout:"form",
				    items:[{
					xtype:"textfield",
					fieldLabel:"Token Expiry",
					name:"tokenExpiry"
				      },{
					xtype:"textfield",
					fieldLabel:"Site",
					name:"site"
				      },{
					xtype:"combo",
					fieldLabel:"Currency",
					name:"currency",
					width:80,
					hiddenName:"currency"
				      }]
				  }]
			      },{
				xtype:"textarea",
				fieldLabel:"Token",
				height:200,
				width:350,
				name:"token"
			    }]
		    }],
			buttons: [{
			    text: '保存',
			    handler: function(){
				 Ext.Ajax.request({
				    waitMsg: 'Please wait...',
				    url: 'connect.php?moduleId=qo-orders&action=updateEbaySeller',
				    params: {
					    id: ebayManageForm.form.findField('id').getValue(),
					    email: ebayManageForm.form.findField('email').getValue(),
					    status: ebayManageForm.form.findField('status').getValue(),
					    tokenExpiry: ebayManageForm.form.findField('tokenExpiry').getValue(),
					    site: ebayManageForm.form.findField('site').getValue(),
					    currency: ebayManageForm.form.findField('currency').getValue(),
					    token: ebayManageForm.form.findField('token').getValue()
				    },
				    success: function(response){
					    var result = eval(response.responseText);
					    switch (result) {
						    case 1:
							
							break;
						    default:
							Ext.MessageBox.alert('Uh uh...', 'We couldn\'t save him...');
							break;
					    }
				    },
				    failure: function(response){
					    var result = response.responseText;
					    Ext.MessageBox.alert('error', 'could not connect to the database. retry later');
				    }
				});		
			    }
			},{
			    text: '添加账户',
			    handler: function(){
				var addEbaySellerForm = new Ext.FormPanel({
				    id: 'ebay-manage-form',
				    frame: true,
				    labelAlign: 'left',
				    bodyStyle:'padding:5px',
				    labelWidth:80,
				    items:[{
					layout:"column",
					items:[{
					    columnWidth:0.5,
					    layout:"form",
					    items:[{
						xtype:"textfield",
						fieldLabel:"ID",
						name:"id"
					      },{
						xtype:"textfield",
						fieldLabel:"Email",
						name:"email"
					      },{
						xtype:"combo",
						fieldLabel:"Status",
						name:"status",
						width:80,
						hiddenName:"status"
					      }]
					  },{
					    columnWidth:0.5,
					    layout:"form",
					    items:[{
						xtype:"textfield",
						fieldLabel:"Token Expiry",
						name:"tokenExpiry"
					      },{
						xtype:"textfield",
						fieldLabel:"Site",
						name:"site"
					      },{
						xtype:"combo",
						fieldLabel:"Currency",
						name:"currency",
						width:80,
						hiddenName:"currency"
					      }]
					  }]
				      },{
					xtype:"textarea",
					fieldLabel:"Token",
					height:200,
					width:350,
					name:"token"
				    }],
				    buttons: [{
					text: '添加',
					handler: function(){
					    
					}
				    }]
				});
				addEbaySellerWin = desktop.createWindow({
				    title:'添加eBay账户',
				    width:500,
				    height:400,
				    iconCls: 'ebay-manage-icon',
				    shim:false,
				    animCollapse:false,
				    constrainHeader:true,
				    layout: 'fit',
				    items:addEbaySellerForm,
				    taskbuttonTooltip: '<b>添加eBay账户</b><br />添加eBay账户'
				});
				addEbaySellerWin.show();
			    }
			},{
			    text: '删除账户',
			    handler: function(){
				console.log(Ext.getCmp("ebay-manage-grid").getSelectionModel().getSelected());
			    }
			},{
			    text: '关闭窗口',
			    handler: function(){
				ebayManageWin.close();
			    }
			}]
		});
				
		ebayManageWin = desktop.createWindow({
		    id: 'ebay-manage-win',
		    title:'eBay账户管理',
		    width:720,
		    height:500,
		    iconCls: 'ebay-manage-icon',
		    shim:false,
		    animCollapse:false,
		    constrainHeader:true,
		    layout: 'fit',
		    items:ebayManageForm,
		    taskbuttonTooltip: '<b>eBay账户管理</b><br />管理eBay帐号'
		});
	    }
	    ebayManageWin.show();
	}
	
	var ebayProxyManage = function(){
	    var ebayProxyManageWin = desktop.getWindow('ebay-proxy-manage-win');
	    
	    if(!ebayProxyManageWin){
		var store = new Ext.data.JsonStore({
		    root: 'result',
		    autoLoad: true,
		    fields: ['id','ebay_seller_id','proxy_host','proxy_port'],
		    url:'connect.php?moduleId=qo-manage&action=getAllEbayProxy'
		});
		
		var notifyWin = desktop.showNotification({
				    html: '加载用户信息中...', 
				    title: '请等待'
				});
		
		saveComplete(notifyWin, '完成', '加载用户信息成功.');
		
		
		var gridForm = new Ext.FormPanel({
		    id: 'ebay-proxy-manage-form',
		    frame: true,
		    labelAlign: 'left',
		    //title: '用户管理',
		    bodyStyle:'padding:5px',
		    //width: 750,
		    layout: 'column',	
		    items: [{
			columnWidth: 0.55,
			layout: 'fit',
			items: {
				xtype: 'grid',
				store: store,
				columns:[
					{header: "eBay帐号", width: 135, sortable: true,  dataIndex: 'ebay_seller_id'},
					{header: "代理主机", width: 125, sortable: true, dataIndex: 'proxy_host'},
					{header: "代理端口", width: 40, sortable: true, dataIndex: 'proxy_port'}
				    ],
				sm: new Ext.grid.RowSelectionModel({
				    singleSelect: true,
				    listeners: {
					rowselect: function(sm, row, rec) {
					    Ext.getCmp("ebay-proxy-manage-form").getForm().loadRecord(rec);
					}
				    }
				}),
				height: 350,
				title:'用户列表',
				border: true,
				listeners: {
					render: function(g) {
						g.getSelectionModel().selectRow(0);
					},
					delay: 10 // Allow rows to be rendered.
				}
			    }
		    },{
			columnWidth: 0.45,
			xtype: 'fieldset',
			labelWidth: 60,
			//title:'用户信息',
			defaults: {width: 130},	// Default config options for child items
			defaultType: 'textfield',
			autoHeight: true,
			bodyStyle: Ext.isIE ? 'padding:0 0 5px 15px;' : 'padding:10px 15px;',
			border: false,
			style: {
			    "margin-left": "10px", // when you add custom margin in IE 6...
			    "margin-right": Ext.isIE6 ? (Ext.isStrict ? "-10px" : "-13px") : "0"  // you have to adjust for it somewhere else
			},
			items: [{
			    fieldLabel: 'eBay帐号',
			    name: 'ebay_seller_id'
			},{
			    fieldLabel: '代理主机',
			    name: 'proxy_host'
			},{
			    fieldLabel: '代理端口',
			    name: 'proxy_port'
			}]
		    }],
		    buttons: [{
			text: '添加eBay代理',
			handler: function(){
			    
			}
		    },{
			text: '保存选中的eBay代理',
			handler: function(){
			    var notifyWin = desktop.showNotification({
				    html: '保存用户信息中...'
				    , title: '请等待'
			    });
			    var memberForm = Ext.getCmp("user-manage-form").getForm();
			    
			    Ext.Ajax.request({
				url: "connect.php",
				params: "moduleId=qo-manage&action=updateMember&id="+memberForm.items.items[0].value+"&email_address="+memberForm.items.items[1].value+"&password="+memberForm.items.items[2].value+"&active="+memberForm.items.items[3].value,
				success: function(o){
				    saveComplete(notifyWin, '完成', '保存用户信息成功.');
				},
				failure: function(){
					    saveComplete(notifyWin, 'Error', 'Lost connection to server.');
					},
					scope: this
			    });
			}
		    },{
			text: '删除选中的eBay代理',
			handler: function(){
			    
			}
		    },{
			text: '关闭窗口',
			handler: function(){
			    ebayProxyManageWin.close();
			}
		    }]
		});
			    
		ebayProxyManageWin = desktop.createWindow({
				    id: 'ebay-proxy-manage-win',
				    title:'eBay代理管理',
				    width:600,
				    height:500,
				    iconCls: 'ebay-proxy-icon',
				    shim:false,
				    animCollapse:false,
				    constrainHeader:true,
				    layout: 'fit',
				    items: gridForm,
				    taskbuttonTooltip: '<b>eBay代理管理</b><br />设置eBay帐户获取订单所经过的代理服务器'
				});
				
		ebayProxyManageWin.show();
	
	    }else{
		ebayProxyManageWin.show();
	    }
	}
	
        if(!win){        	
			
            win = desktop.createWindow({
                id: 'manage-win',
                title:'系统管理',
                width:340,
                height:200,
                iconCls: 'manage-icon',
                shim:false,
                animCollapse:false,
                constrainHeader:true,
		layout: 'fit',
                html: '<div class="manage-button"><div class="user-manage"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">用户管理</div></div>\
		       <div class="manage-button"><div class="group-manage"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">用户组管理</div></div>\
		       <div class="manage-button"><div class="group-privilege-manage"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">组权限管理</div></div>\
		       <div class="manage-button"><div class="ebay-manage"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">eBay帐号管理</div></div>\
		       <div class="manage-button"><div class="ebay-proxy"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">eBay代理管理</div></div>',
                taskbuttonTooltip: '<b>系统管理</b><br />用户、组、权限、eBay管理'
            });
	    Ext.EventManager.on(Ext.DomQuery.select("div[@class='user-manage']")[0], "click", userManage);
	    Ext.EventManager.on(Ext.DomQuery.select("div[@class='group-manage']")[0], "click", groupManage);
	    //<div class="manage-button"><div class="privilege-manage"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">权限管理</div></div>\
	    //Ext.EventManager.on(Ext.DomQuery.select("div[@class='privilege-manage']")[0], "click", privilegeManage);
	    Ext.EventManager.on(Ext.DomQuery.select("div[@class='group-privilege-manage']")[0], "click", groupPrivilegeManage);
	    Ext.EventManager.on(Ext.DomQuery.select("div[@class='ebay-manage']")[0], "click", ebayManage);
	    Ext.EventManager.on(Ext.DomQuery.select("div[@class='ebay-proxy']")[0], "click", ebayProxyManage);
        }
        
        win.show();
    }
});