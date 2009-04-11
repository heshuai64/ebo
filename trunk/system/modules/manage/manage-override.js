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
	    var active_data = [['1','是'],['0','否']];
	    var userManageWin = desktop.getWindow('user-manage-win');
	    var store = new Ext.data.JsonStore({
		autoLoad: true,
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
		
		var showActive = function(val){
		    if(val == "1"){
			return "是";
		    }else{
			return "否";
		    }
		}
	    
		var userForm = new Ext.FormPanel({
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
					{id:'id', header: "id", width: 0, sortable: true, hidden:true, dataIndex: 'id'},
					{header: "用户名", width: 75, sortable: true,  dataIndex: 'email_address'},
					{header: "密码", width: 75, sortable: true, dataIndex: 'password'},
					{header: "激活", width: 75, sortable: true, dataIndex: 'active', renderer: showActive},
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
			    xtype:'hidden',
			    fieldLabel: 'Id',
			    name: 'id'
			},{
			    fieldLabel: '用户名',
			    name: 'email_address'
			},{
			    fieldLabel: '密码',
			    name: 'password'
			},{
			    xtype:"combo",
			    fieldLabel: '激活',
			    mode: 'local',
			    store: new Ext.data.SimpleStore({
				fields: ['active_id', 'active_name'],
				data : active_data
			    }),
			    listWith:100,
			    width:100,
			    valueField:'active_id',
			    displayField:'active_name',
			    triggerAction: 'all',
			    editable: false,
			    selectOnFocus:true,
			    name: 'active',
			    hiddenName:'active'
			},{
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
			text: '添加用户',
			handler: function(){
			    var addUserForm = new Ext.FormPanel({
				frame: true,
				labelAlign: 'left',
				bodyStyle:'padding:5px',
				labelWidth:50,
				defaultType: 'textfield',
				items:[{
					fieldLabel: '用户名',
					name: 'email_address'
				    },{
					fieldLabel: '密码',
					name: 'password'
				    },{
					xtype:"combo",
					fieldLabel: '激活',
					mode: 'local',
					store: new Ext.data.SimpleStore({
					    fields: ['active_id', 'active_name'],
					    data : active_data
					}),
					listWith:100,
					width:100,
					valueField:'active_id',
					displayField:'active_name',
					triggerAction: 'all',
					editable: false,
					selectOnFocus:true,
					name: 'active',
					hiddenName:'active'
				    },{
					xtype: 'combo',
					fieldLabel: '用户组',
					mode: 'local',
					store: new Ext.data.JsonStore({
					    fields: ['group_id', 'group_name'],
					    data : group
					}),
					listWith:100,
					width:100,
					valueField:'group_id',
					displayField:'group_name',
					triggerAction: 'all',
					editable: false,
					selectOnFocus:true,
					name: 'group_id',
					hiddenName:'group_id'
				    }],
				buttons: [{
				    text: '保存',
				    handler: function(){
					Ext.Ajax.request({
					    waitMsg: 'Please wait...',
					    url: 'connect.php?moduleId=qo-manage&action=addMember',
					    params: {
						    email_address: addUserForm.form.findField('email_address').getValue(),
						    password: addUserForm.form.findField('password').getValue(),
						    active: addUserForm.form.findField('active').getValue(),
						    group_id: addUserForm.form.findField('group_id').getValue()
					    },
					    success: function(response){
						var result = eval(response.responseText);
						switch (result) {
						    case 1:
							store.reload();
							addUserWin.close();
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
				}]
			    });
			    
			    addUserWin = desktop.createWindow({
				title:'添加用户',
				width:260,
				height:200,
				iconCls: 'user-manage-icon',
				shim:false,
				animCollapse:false,
				constrainHeader:true,
				layout: 'fit',
				items:addUserForm,
				taskbuttonTooltip: '<b>添加用户</b><br />添加一个用户'
			    });
			    addUserWin.show();
			}
		    },{
			text: '保存选中的用户',
			handler: function(){
			    var notifyWin = desktop.showNotification({
				    html: '保存用户信息中...'
				    , title: '请等待'
			    });
			    var memberForm = Ext.getCmp("user-manage-form").getForm();
			    
			    Ext.Ajax.request({
				url: "connect.php?moduleId=qo-manage&action=updateMember",
				params:{
				    id: userForm.form.findField('id').getValue(),
				    email_address: userForm.form.findField('email_address').getValue(),
				    password: userForm.form.findField('password').getValue(),
				    active: userForm.form.findField('active').getValue()
				},
				success: function(o){
				    store.reload();
				    saveComplete(notifyWin, '完成', '保存用户信息成功.');
				},
				failure: function(){
				    saveComplete(notifyWin, 'Error', 'Lost connection to server.');
				},
				scope: this
			    });
			}
		    },{
			text: '删除选中用户',
			handler: function(){
			    Ext.Ajax.request({
				waitMsg: 'Please wait...',
				url: 'connect.php?moduleId=qo-manage&action=deleteMember',
				params: {
				    id: userForm.form.findField('id').getValue()	
				},
				success: function(response){
				    var result = eval(response.responseText);
				    switch (result) {
					case 1:
					    store.reload();
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
			text: '关闭',
			handler: function(){
			    userManageWin.close();
			}
		    }]
		});
		if(!userManageWin){		
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
			items: userForm,
			taskbuttonTooltip: '<b>用户管理</b><br />添加、修改、删除用户'
		    });
				
		    userManageWin.show();
		}else{
		    userManageWin.show();
		}
	    }
	    store.on('load', loadMemberInfComplete);	
	   
	}
	
	var groupManage = function(){
	    var active_data = [['1','是'],['0','否']];
	    var groupManageWin = desktop.getWindow('group-manage-win');
	    var store = new Ext.data.JsonStore({
		autoLoad: true,
		fields: ['id','name','description','active'],
		url:'connect.php?moduleId=qo-manage&action=getAllGroup'
	    });
	    
	    var showActive = function(val){
		if(val == "1"){
		    return "是";
		}else{
		    return "否";
		}
	    }
	    
	    var grid = new Ext.grid.GridPanel({
		store: store,
		columns:[{
		    id: 'id', // id assigned so we can apply custom css (e.g. .x-grid-col-topic b { color:#333 })
		    header: "Id",
		    dataIndex: 'id',
		    width: 0,
		    hidden: true,
		    sortable: true
		},{
		    header: "组名",
		    dataIndex: 'name',
		    width: 90,
		    sortable: true
		},{
		    header: "描述",
		    dataIndex: 'description',
		    width: 200,
		    sortable: true
		},{
		    header: "激活",
		    dataIndex: 'active',
		    width: 50,
		    renderer: showActive,
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
			xtype: 'hidden',
			fieldLabel: 'Id',
			name: 'id'
		    },{
			fieldLabel: '组名',
			name: 'name'
		    },{
			fieldLabel: '描述',
			name: 'description'
		    },{
			xtype:"combo",
			fieldLabel: '激活',
			mode: 'local',
			store: new Ext.data.SimpleStore({
			    fields: ['active_id', 'active_name'],
			    data : active_data
			}),
			listWith:100,
			width:100,
			valueField:'active_id',
			displayField:'active_name',
			triggerAction: 'all',
			editable: false,
			selectOnFocus:true,
			name: 'active',
			hiddenName:'active'
		    }]
		}],
		buttons: [{
		    text: '添加用户组',
		    handler: function(){
			var addGroupForm = new Ext.FormPanel({
			    frame: true,
			    labelAlign: 'left',
			    bodyStyle:'padding:5px',
			    labelWidth:50,
			    items:[{
				xtype:"textfield",
				fieldLabel: '组名',
				name: 'name'
			    },{
				xtype:"textfield",
				fieldLabel: '描述',
				name: 'description'
			    },{
				xtype:"combo",
				fieldLabel: '激活',
				mode: 'local',
				store: new Ext.data.SimpleStore({
				    fields: ['active_id', 'active_name'],
				    data : active_data
				}),
				listWith:100,
				width:100,
				valueField:'active_id',
				displayField:'active_name',
				triggerAction: 'all',
				editable: false,
				selectOnFocus:true,
				name: 'active',
				hiddenName:'active'
			    }],
			    buttons: [{
				text: '保存',
				handler: function(){
				     Ext.Ajax.request({
					waitMsg: 'Please wait...',
					url: 'connect.php?moduleId=qo-manage&action=addGroup',
					params: {
						name: addGroupForm.form.findField('name').getValue(),
						description: addGroupForm.form.findField('description').getValue(),
						active: addGroupForm.form.findField('active').getValue()
					},
					success: function(response){
					    var result = eval(response.responseText);
					    switch (result) {
						case 1:
						    store.reload();
						    addGroupWin.close();
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
			    }]
			});
			addGroupWin = desktop.createWindow({
			    title:'添加用户组',
			    width:260,
			    height:200,
			    iconCls: 'group-manage-icon',
			    shim:false,
			    animCollapse:false,
			    constrainHeader:true,
			    layout: 'fit',
			    items:addGroupForm,
			    taskbuttonTooltip: '<b>添加用户组</b><br />添加一个用户组'
			});
			addGroupWin.show();
		    }
		},{
		    text: '保存选中的组',
		    handler: function(){
			var notifyWin = desktop.showNotification({
				html: '保存信息中...',
				title: '请等待'
			});
			var form = Ext.getCmp("group-manage-form").getForm();
			Ext.Ajax.request({
			    url: "connect.php?moduleId=qo-manage&action=updateGroup",
			    params: {
				id: form.findField('id').getValue(),
				name: form.findField('name').getValue(),
				description: form.findField('description').getValue(),
				active: form.findField('active').getValue()
			    },
			    success: function(o){
				store.reload();
				saveComplete(notifyWin, '完成', '保存用户组成功.');
			    },
			    failure: function(){
				saveComplete(notifyWin, 'Error', 'Lost connection to server.');
			    },
			    scope: this
			});
		    }
		},{
		    text: '删除选中的组',
		    handler: function(){
			var notifyWin = desktop.showNotification({
				html: '删除中...',
				title: '请等待'
			});
			var form = Ext.getCmp("group-manage-form").getForm();
			Ext.Ajax.request({
			    url: "connect.php?moduleId=qo-manage&action=deleteGroup",
			    params: {
				id: form.findField('id').getValue()
			    },
			    success: function(o){
				store.reload();
				saveComplete(notifyWin, '完成', '删除用户组成功.');
			    },
			    failure: function(){
				saveComplete(notifyWin, 'Error', 'Lost connection to server.');
			    },
			    scope: this
			});
		    }
		},{
		    text: '关闭',
		    handler: function(){
			groupManageWin.close();
		    }
		}]
	    });
	    
	    var notifyWin = desktop.showNotification({
		html: '加载用户组信息中...',
		title: '请等待'
	    });
	    
	    var loadGroupInfoComplete = function(){
		if(!groupManageWin){
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
		}else{
		    groupManageWin.show();
		}
	    }
	    store.on('load', loadGroupInfoComplete);
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
		    fields: ['id','email','status','devId','appId','cert','token','tokenExpiry','currency','site'],
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
					    {id:'id', header: "id", width: 200, sortable: true, locked:false, dataIndex: 'id'}
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
				xtype:"textfield",
				fieldLabel:"Dev Id",
				width:350,
				name:"devId"
			    },{
				xtype:"textfield",
				fieldLabel:"App Id",
				width:350,
				name:"appId"
			    },{
				xtype:"textfield",
				fieldLabel:"Cert",
				width:350,
				name:"cert"
			    },{
				xtype:"textarea",
				fieldLabel:"Token",
				height:200,
				width:350,
				name:"token"
			    }]
		    }],
			buttons: [{
			    text: '保存选中的eBay账户',
			    handler: function(){
				Ext.Ajax.request({
				    waitMsg: 'Please wait...',
				    url: 'connect.php?moduleId=qo-manage&action=updateEbaySeller',
				    params: {
					    id: ebayManageForm.form.findField('id').getValue(),
					    email: ebayManageForm.form.findField('email').getValue(),
					    status: ebayManageForm.form.findField('status').getValue(),
					    tokenExpiry: ebayManageForm.form.findField('tokenExpiry').getValue(),
					    site: ebayManageForm.form.findField('site').getValue(),
					    currency: ebayManageForm.form.findField('currency').getValue(),
					    devId: ebayManageForm.form.findField('devId').getValue(),
					    appId: ebayManageForm.form.findField('appId').getValue(),
					    cert: ebayManageForm.form.findField('cert').getValue(),
					    token: ebayManageForm.form.findField('token').getValue()
				    },
				    success: function(response){
					    var result = eval(response.responseText);
					    switch (result) {
						    case 1:
							store.reload();
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
			    text: '添加eBay账户',
			    handler: function(){
				var addEbaySellerForm = new Ext.FormPanel({
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
					xtype:"textfield",
					fieldLabel:"Dev Id",
					width:350,
					name:"devId"
				    },{
					xtype:"textfield",
					fieldLabel:"App Id",
					width:350,
					name:"appId"
				    },{
					xtype:"textfield",
					fieldLabel:"Cert",
					width:350,
					name:"cert"
				    },{
					xtype:"textarea",
					fieldLabel:"Token",
					height:200,
					width:350,
					name:"token"
				    }],
				    buttons: [{
					text: '保存',
					handler: function(){
					     Ext.Ajax.request({
						waitMsg: 'Please wait...',
						url: 'connect.php?moduleId=qo-manage&action=addEbaySeller',
						params: {
							id: addEbaySellerForm.form.findField('id').getValue(),
							email: addEbaySellerForm.form.findField('email').getValue(),
							status: addEbaySellerForm.form.findField('status').getValue(),
							tokenExpiry: addEbaySellerForm.form.findField('tokenExpiry').getValue(),
							site: addEbaySellerForm.form.findField('site').getValue(),
							currency: addEbaySellerForm.form.findField('currency').getValue(),
							devId: addEbaySellerForm.form.findField('devId').getValue(),
							appId: addEbaySellerForm.form.findField('appId').getValue(),
							cert: addEbaySellerForm.form.findField('cert').getValue(),
							token: addEbaySellerForm.form.findField('token').getValue()
						},
						success: function(response){
						    var result = eval(response.responseText);
						    switch (result) {
							case 1:
							    store.reload();
							    addEbaySellerWin.close();
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
				    }]
				});
				addEbaySellerWin = desktop.createWindow({
				    title:'添加eBay账户',
				    width:600,
				    height:500,
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
				//console.log(Ext.getCmp("ebay-manage-grid").getSelectionModel().getSelected());
				Ext.Ajax.request({
				    waitMsg: 'Please wait...',
				    url: 'connect.php?moduleId=qo-manage&action=deleteEbaySeller',
				    params: {
					id: Ext.getCmp("ebay-manage-grid").getSelectionModel().getSelected().data.id
				    },
				    success: function(response){
					var result = eval(response.responseText);
					switch (result) {
					    case 1:
						store.reload();
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
			    text: '关闭',
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
	    
	   
	    var store = new Ext.data.JsonStore({
		root: 'result.proxy',
		autoLoad: true,
		fields: ['id','ebay_seller_id','proxy_host','proxy_port'],
		url:'connect.php?moduleId=qo-manage&action=getAllEbayProxy'
	    });
	    
	    var notifyWin = desktop.showNotification({
				   html: '加载信息中...', 
				   title: '请等待'
			       });
	     
	    var loadComplete = function(S, r){
		if(!ebayProxyManageWin){
		    var seller = S.reader.jsonData.result.seller;
		    saveComplete(notifyWin, '完成', '加载eBay代理信息成功.');
		    
		    
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
					    {header: "id", width: 0, sortable: true,  dataIndex: 'id', hidden:true},
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
			    labelWidth: 55,
			    //title:'用户信息',
			    defaults: {width: 150},	// Default config options for child items
			    defaultType: 'textfield',
			    autoHeight: true,
			    bodyStyle: Ext.isIE ? 'padding:0 0 5px 15px;' : 'padding:10px 15px;',
			    border: false,
			    style: {
				"margin-left": "10px", // when you add custom margin in IE 6...
				"margin-right": Ext.isIE6 ? (Ext.isStrict ? "-10px" : "-13px") : "0"  // you have to adjust for it somewhere else
			    },
			    items: [{
				xtype: 'hidden',
				name:'id'
				},{
				xtype: 'combo',
				fieldLabel: 'eBay帐号',
				mode: 'local',
				store: new Ext.data.JsonStore({
				    fields: ['ebay_seller_id', 'ebay_seller_name'],
				    data : seller
				}),
				valueField:'ebay_seller_id',
				displayField:'ebay_seller_name',
				triggerAction: 'all',
				editable: false,
				selectOnFocus:true,
				name: 'ebay_seller_id',
				hiddenName:'ebay_seller_id'
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
				var add_ebay_proxy_form =  form = new Ext.FormPanel({
				    labelAlign: 'top',
				    bodyStyle:'padding:5px',
				    defaultType: 'textfield',
				    items: [{
					    xtype: 'combo',
					    fieldLabel: 'eBay帐号',
					    mode: 'local',
					    store: new Ext.data.JsonStore({
						fields: ['ebay_seller_id', 'ebay_seller_name'],
						data : seller
					    }),
					    valueField:'ebay_seller_id',
					    displayField:'ebay_seller_name',
					    triggerAction: 'all',
					    editable: false,
					    selectOnFocus:true,
					    name: 'ebay_seller_id',
					    hiddenName:'ebay_seller_id'
					},{
					    fieldLabel: '代理主机',
					    name: 'proxy_host'
					},{
					    fieldLabel: '代理端口',
					    name: 'proxy_port'
					}]
				})
				
				var addeBayProxyWindow = desktop.createWindow({
				    id: 'add_ebay_proxy_win',
				    title: '添加eBay代理' ,
				    closable:true,
				    width: 400,
				    height: 300,
				    iconCls: 'ebay-proxy-icon',
				    plain:true,
				    layout: 'fit',
				    items: add_ebay_proxy_form,
				    taskbuttonTooltip: '<b>添加eBay代理</b><br />添加代理并绑定到相应的eBay账户',
				    buttons: [{
					text: '保存',
					handler: function(){
					    Ext.Ajax.request({
						waitMsg: 'Please wait...',
						url: 'connect.php?moduleId=qo-manage&action=addEbayProxy',
						params: {
							ebay_seller_id: add_ebay_proxy_form.form.findField('ebay_seller_id').getValue(),
							proxy_host: add_ebay_proxy_form.form.findField('proxy_host').getValue(),
							proxy_port: add_ebay_proxy_form.form.findField('proxy_port').getValue()
						},
						success: function(response){
						    var result = eval(response.responseText);
						    switch (result) {
							case 1:
							    store.reload();
							    addeBayProxyWindow.close();
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
					text: '关闭',
					handler: function(){
					      addeBayProxyWindow.close();
					}
				    }]
				});
				addeBayProxyWindow.show();
				console.log(addeBayProxyWindow);
			    }
			},{
			    text: '保存选中的eBay代理',
			    handler: function(){
				var notifyWin = desktop.showNotification({
					html: '保存信息中...',
					title: '请等待'
				});
				var form = Ext.getCmp("ebay-proxy-manage-form").getForm();
				Ext.Ajax.request({
				    url: "connect.php?moduleId=qo-manage&action=updateEbayProxy",
				    params: {
					id: form.findField('id').getValue(),
					ebay_seller_id: form.findField('ebay_seller_id').getValue(),
					proxy_host: form.findField('proxy_host').getValue(),
					proxy_port: form.findField('proxy_port').getValue()
				    },
				    success: function(o){
					store.reload();
					saveComplete(notifyWin, '完成', '保存eBay代理成功.');
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
				var notifyWin = desktop.showNotification({
					html: '删除中...',
					title: '请等待'
				});
				var form = Ext.getCmp("ebay-proxy-manage-form").getForm();
				Ext.Ajax.request({
				    url: "connect.php?moduleId=qo-manage&action=deleteEbayProxy",
				    params: {
					id: form.findField('id').getValue()
				    },
				    success: function(o){
					store.reload();
					saveComplete(notifyWin, '完成', '删除eBay代理成功.');
				    },
				    failure: function(){
					saveComplete(notifyWin, 'Error', 'Lost connection to server.');
				    },
				    scope: this
				});
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
	    store.on('load', loadComplete);
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