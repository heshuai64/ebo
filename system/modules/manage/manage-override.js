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
	    var active_data = [['1', lang.yes],['0', lang.no]];
	    var userManageWin = desktop.getWindow('user-manage-win');
	    var store = new Ext.data.JsonStore({
		autoLoad: true,
		root: 'result.member_info',
		fields: ['id','email_address','password','active','group_id','group_name'],
		url:'connect.php?moduleId=qo-manage&action=getAllMember'
	    });
	    
	    var notifyWin = desktop.showNotification({
		html: lang.loading, 
		title: lang.waiting
	    });
	    
	    var loadMemberInfComplete = function(S, r){
		var group = S.reader.jsonData.result.group_info;
		saveComplete(notifyWin, lang.complete, lang.load_user_info_complete);
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
			return lang.yes;
		    }else{
			return lang.no;
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
					{header: lang.user_name, width: 75, sortable: true,  dataIndex: 'email_address'},
					{header: lang.password, width: 75, sortable: true, dataIndex: 'password'},
					{header: lang.active, width: 75, sortable: true, dataIndex: 'active', renderer: showActive},
					{header: lang.user_group, width: 75, sortable: true, dataIndex: 'group_id', renderer: showGroupName}
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
				title: lang.user_list,
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
			    fieldLabel: lang.user_name,
			    name: 'email_address'
			},{
			    fieldLabel: lang.password,
			    name: 'password'
			},{
			    xtype:"combo",
			    fieldLabel: lang.active,
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
			    fieldLabel: lang.user_group,
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
			text: lang.add_user,
			handler: function(){
			    var addUserForm = new Ext.FormPanel({
				frame: true,
				labelAlign: 'left',
				bodyStyle:'padding:5px',
				labelWidth:50,
				defaultType: 'textfield',
				items:[{
					fieldLabel: lang.user_name,
					name: 'email_address'
				    },{
					fieldLabel: lang.password,
					name: 'password'
				    },{
					xtype:"combo",
					fieldLabel: lang.active,
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
					fieldLabel: lang.user_group,
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
				    text: lang.save,
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
				title: lang.add_user,
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
			text: lang.save_selected_user_info,
			handler: function(){
			    var notifyWin = desktop.showNotification({
				    html: lang.saving,
				    title: lang.waiting
			    });
			    var memberForm = Ext.getCmp("user-manage-form").getForm();
			    
			    Ext.Ajax.request({
				url: "connect.php?moduleId=qo-manage&action=updateMember",
				params:{
				    id: userForm.form.findField('id').getValue(),
				    email_address: userForm.form.findField('email_address').getValue(),
				    password: userForm.form.findField('password').getValue(),
				    active: userForm.form.findField('active').getValue(),
				    group_id: userForm.form.findField('group_id').getValue()
				},
				success: function(o){
				    store.reload();
				    saveComplete(notifyWin, lang.complete, lang.save_user_inf_success);
				},
				failure: function(){
				    saveComplete(notifyWin, lang.error, lang.connect_lost);
				},
				scope: this
			    });
			}
		    },{
			text: lang.delete_selected_user,
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
			text: lang.close,
			handler: function(){
			    userManageWin.close();
			}
		    }]
		});
		if(!userManageWin){		
		    userManageWin = desktop.createWindow({
			id: 'user-manage-win',
			title: lang.user_manager,
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
	    var active_data = [['1', lang.yes], ['0', lang.no]];
	    var groupManageWin = desktop.getWindow('group-manage-win');
	    var store = new Ext.data.JsonStore({
		autoLoad: true,
		fields: ['id','name','description','active'],
		url:'connect.php?moduleId=qo-manage&action=getAllGroup'
	    });
	    
	    var showActive = function(val){
		if(val == "1"){
		    return lang.yes;
		}else{
		    return lang.no;
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
		    header: lang.user_group_name,
		    dataIndex: 'name',
		    width: 90,
		    sortable: true
		},{
		    header: lang.user_group_des,
		    dataIndex: 'description',
		    width: 200,
		    sortable: true
		},{
		    header: lang.active,
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
		title: lang.user_group_list,
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
			fieldLabel: lang.user_group_name,
			name: 'name'
		    },{
			fieldLabel: lang.user_group_des,
			name: 'description'
		    },{
			xtype:"combo",
			fieldLabel: lang.active,
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
		    text: lang.add_user_group,
		    handler: function(){
			var addGroupForm = new Ext.FormPanel({
			    frame: true,
			    labelAlign: 'left',
			    bodyStyle:'padding:5px',
			    labelWidth:50,
			    items:[{
				xtype:"textfield",
				fieldLabel: lang.user_group_name,
				name: 'name'
			    },{
				xtype:"textfield",
				fieldLabel: lang.user_group_des,
				name: 'description'
			    },{
				xtype:"combo",
				fieldLabel: lang.active,
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
				text: lang.save,
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
			    title: lang.add_user_group,
			    width:260,
			    height:200,
			    iconCls: 'group-manage-icon',
			    shim:false,
			    animCollapse:false,
			    constrainHeader:true,
			    layout: 'fit',
			    items:addGroupForm,
			    taskbuttonTooltip: lang.add_user_group_tooltip
			});
			addGroupWin.show();
		    }
		},{
		    text: lang.save_selected_user_group_info,
		    handler: function(){
			var notifyWin = desktop.showNotification({
				html: lang.saving,
				title: lang.waiting
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
				saveComplete(notifyWin, lang.save, lang.save_user_group_info_success);
			    },
			    failure: function(){
				saveComplete(notifyWin, lang.error, lang.connect_lost);
			    },
			    scope: this
			});
		    }
		},{
		    text: lang.delete_selected_group,
		    handler: function(){
			var notifyWin = desktop.showNotification({
				html: lang.deleting,
				title: lang.waiting
			});
			var form = Ext.getCmp("group-manage-form").getForm();
			Ext.Ajax.request({
			    url: "connect.php?moduleId=qo-manage&action=deleteGroup",
			    params: {
				id: form.findField('id').getValue()
			    },
			    success: function(o){
				store.reload();
				saveComplete(notifyWin, lang.complete, lang.delete_selected_group_success);
			    },
			    failure: function(){
				saveComplete(notifyWin, lang.error, lang.connect_lost);
			    },
			    scope: this
			});
		    }
		},{
		    text: lang.close,
		    handler: function(){
			groupManageWin.close();
		    }
		}]
	    });
	    
	    var notifyWin = desktop.showNotification({
		html: lang.loading,
		title: lang.waiting
	    });
	    
	    var loadGroupInfoComplete = function(){
		if(!groupManageWin){
		    saveComplete(notifyWin, lang.complete, load_user_group_complete);
		    groupManageWin = desktop.createWindow({
			id: 'group-manage-win',
			title: lang.user_group_manager,
			width:600,
			height:500,
			iconCls: 'group-manage-icon',
			shim:false,
			animCollapse:false,
			constrainHeader:true,
			layout: 'fit',
			items: gridForm,
			taskbuttonTooltip: lang.user_group_manager_tooltip
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
			html: lang.loading,
			title: lang.waiting
		});
		
		Ext.Ajax.request({
			url: 'connect.php',
			params: 'moduleId=qo-manage&action=getPrivilegeInfo',
			success: function(o){
				if(o && o.responseText){
				    saveComplete(notifyWin, lang.complete, lang.load_privilege_info_success);
				    var data = eval(o.responseText);
				    var groupHtml = "";
				    var checked = "";
				    
				    var formHtml = '<form id="privilegeForm">'+groupHtml+'</form>';
				    
				    privilegeManageWin = desktop.createWindow({
					id: 'privilege-manage-win',
					title: lang.privilege_manager,
					width:600,
					height:500,
					iconCls: 'privilege-manage-icon',
					shim:false,
					autoScroll: true,
					animCollapse:false,
					constrainHeader:true,
					layout: 'fit',
					html: formHtml,
					taskbuttonTooltip: lang.privilege_manager_tooltip
				    });
				    privilegeManageWin.show(); 
				}else{
				    saveComplete(notifyWin, lang.failure, lang.load_privilege_info_failure);
				}
			},
			failure: function(){
				saveComplete(notifyWin, lang.error, lang.connect_lost);
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
			html: lang.loading,
			title: lang.waiting
		});
				
		Ext.Ajax.request({
			url: 'connect.php',
			params: 'moduleId=qo-manage&action=getGroupDomainPrivilege',
			success: function(o){
				if(o && o.responseText){
				    
				    groupPrivilegeDetailManage = function(t, gpi){
					var temp = gpi.split("_");
					var group_id = temp[0];
					var privilege_id = temp[1];
					
					if(t.checked == true){
					    var groupPrivilegeDetailManageWin = desktop.getWindow('group-privilege-detail-manage-win-'+gpi);
					    if(!groupPrivilegeDetailManageWin){
						var notifyWin = desktop.showNotification({
							html: lang.loading,
							title: lang.waiting
						});
						
						Ext.Ajax.request({
						    url: 'connect.php',
						    params: 'moduleId=qo-manage&action=getGroupPrivilegeDetail&data='+gpi,
						    success: function(o){
							saveComplete(notifyWin, lang.complete, lang.load_group_privilege_detail_success);
							
							var data = eval(o.responseText);
							var actionHtml = "";
							for(i in data){
							    if(Ext.isEmpty(data[i].description)){
								continue;
							    }
							    checked = '';
							    if(data[i].active == 1){
								checked = 'checked="checked"';
							    }
							    actionHtml += '<div style="float: left; width: 180px;"><input class="privilege-action" id="'+data[i].id+'" '+checked+' type="checkbox" />' + data[i].description + '<img width="12px" src="resources/images/default/s.gif"/></div>';
							    /*
							    j = i + 1;
							    if(j % 3 == 0){
								actionHtml += "<br>";
							    }
							    */
							}
							
							var actionFormHtml = '<form id="group-privilege-detail-form">'+actionHtml+'\
							<div style="clear: left; text-align:center;">\
							    <button id="active-select-all" type="button">'+lang.select_all+'</button>\
							    <button id="active-unselect-all" type="button">'+lang.unselect_all+'</button>\
							    <button id="active-save" type="button">'+lang.save+'</button>\
							    <button id="active-close" type="button">'+lang.close+'</button>\
							</div></form>';
							
							groupPrivilegeDetailManageWin = desktop.createWindow({
							    id: 'group-privilege-detail-manage-win-'+gpi,
							    title: lang.group_privilege_detail_manager,
							    width:560,
							    height:250,
							    iconCls: 'group-privilege-manage-icon',
							    shim:false,
							    autoScroll: true,
							    animCollapse:false,
							    constrainHeader:true,
							    layout: 'fit',
							    html: actionFormHtml,
							    taskbuttonTooltip: lang.group_privilege_detail_tooltip
							});
							groupPrivilegeDetailManageWin.show();
							
							var saveGroupPrivilegeDetailInfo = function(){
							    var p = "";
							    var test = function(p1){
								if(p1.checked){
								    p += p1.id + "=1,";
								}else{
								    p += p1.id + "=0,";
								}
							    }
							   
							    Ext.each(Ext.query("input[class=privilege-action]"), test);
							    //console.log(p);
							    
							    Ext.Ajax.request({
								waitMsg: 'Please wait...',
								url: 'connect.php?moduleId=qo-manage&action=updateGroupPrivilegeDetail&group_id='+group_id+'&privilege_id='+privilege_id+'&data='+p,
								success: function(response){
								    var result = eval(response.responseText);
								    switch (result) {
									case 1:
									    //Ext.MessageBox.alert('Success', 'Save Success!');
									    groupPrivilegeDetailManageWin.close();
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
							
							var closeGroupPrivilegeDetailWindow = function(){
							    groupPrivilegeDetailManageWin.close();
							}
							
							var selectAllPrivilegeDetailInfo = function(){
							    var test1 = function(p1){
								p1.checked = "checked";
							    }
							    Ext.each(Ext.query("input[class=privilege-action]"), test1);
							}
							
							var unselectAllPrivilegeDetailInfo = function(){
							    var test1 = function(p1){
								p1.checked = "";
							    }
							    Ext.each(Ext.query("input[class=privilege-action]"), test1);
							}
							
							Ext.EventManager.addListener("active-select-all", "click", selectAllPrivilegeDetailInfo);
							Ext.EventManager.addListener("active-unselect-all", "click", unselectAllPrivilegeDetailInfo);
							Ext.EventManager.addListener("active-save", "click", saveGroupPrivilegeDetailInfo);
							Ext.EventManager.addListener("active-close", "click", closeGroupPrivilegeDetailWindow);
						    },
						    failure: function(){
							saveComplete(notifyWin, lang.error, lang.connect_lost);
						    },
						    scope: this
						})
						
					    }else{
						groupPrivilegeDetailManageWin.show();
					    }
					}else{
					    
					}
				    }
				    
				    saveComplete(notifyWin, lang.complete, lang.load_group_privilege_detail_success);
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
								    groupHtml += '<input class="group-privilege" id="'+data[0].group[i].id+"_"+data[0].privilege[j].id+'" '+checked+' type="checkbox" onclick="groupPrivilegeDetailManage(this, \''+data[0].group[i].id+"_"+data[0].privilege[j].id+'\')" />      ' + data[0].privilege[j].name + '<img width="12px" src="resources/images/default/s.gif"/>'; 
							    }
							}
						    }
						}
					    }
					    groupHtml += "</fieldset></div>";
					}
				    }
				    var formHtml = '<form id="group-privilege-form">'+groupHtml+'\
						    <div style="text-align:center;"><button id="save" type="button">'+lang.save+'</button>\
						         <button id="close" type="button">'+lang.close+'</button>\
						    </div></form>';
				    groupPrivilegeManageWin = desktop.createWindow({
					id: 'group-privilege-manage-win',
					title: lang.group_privilege_manager,
					width:600,
					height:500,
					iconCls: 'group-privilege-manage-icon',
					shim:false,
					autoScroll: true,
					animCollapse:false,
					constrainHeader:true,
					layout: 'fit',
					html: formHtml,
					taskbuttonTooltip: lang.group_privilege_manager_tooltip
				    });
				    groupPrivilegeManageWin.show();
				    
				    var saveGroupPrivilegeInfo = function(){
					    var p = "";
					    var test = function(p1){
						if(p1.checked){
						    p += p1.id + "=1,";
						}else{
						    p += p1.id + "=0,";
						}
					    }
					   
					    Ext.each(Ext.query("input[class=group-privilege]"), test);
					    //console.log(p);
					    
					    Ext.Ajax.request({
						waitMsg: 'Please wait...',
						url: 'connect.php?moduleId=qo-manage&action=updateGroupPrivilege&data='+p,
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
				    saveComplete(notifyWin, lang.failure, lang.load_group_privilege_info_failure);
				}
			},
			failure: function(){
				saveComplete(notifyWin, lang.error, lang.connect_lost);
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
		    fields: ['id','email','emailPassword','status','devId','appId','cert','token','tokenExpiry','currency','site'],
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
				    title: lang.ebay_account_list,
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
				      }/*,{
					xtype:"textfield",
					fieldLabel:"Email",
					name:"email"
				      }*/,{
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
				      }/*,{
					xtype:"textfield",
					fieldLabel:"Site",
					name:"site"
				      }*/,{
					xtype:"combo",
					fieldLabel:"Currency",
					name:"currency",
					width:80,
					hiddenName:"currency"
				      }]
				  }]
			      },{
				xtype:"textfield",
				fieldLabel:"Email",
				width:350,
				name:"email"
			    },{
				xtype:"textfield",
				fieldLabel:"Email Password",
				width:350,
				name:"emailPassword"
			    }/*,{
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
			    }*/,{
				xtype:"textarea",
				fieldLabel:"Token",
				height:200,
				width:350,
				name:"token"
			    }]
		    }],
			buttons: [{
			    text: lang.save_selected_ebay_account_info,
			    handler: function(){
				Ext.Ajax.request({
				    waitMsg: 'Please wait...',
				    url: 'connect.php?moduleId=qo-manage&action=updateEbaySeller',
				    params: {
					    id: ebayManageForm.form.findField('id').getValue(),
					    email: ebayManageForm.form.findField('email').getValue(),
					    emailPassword: ebayManageForm.form.findField('emailPassword').getValue(),
					    status: ebayManageForm.form.findField('status').getValue(),
					    tokenExpiry: ebayManageForm.form.findField('tokenExpiry').getValue(),
					    //site: ebayManageForm.form.findField('site').getValue(),
					    currency: ebayManageForm.form.findField('currency').getValue(),
					    //devId: ebayManageForm.form.findField('devId').getValue(),
					    //appId: ebayManageForm.form.findField('appId').getValue(),
					    //cert: ebayManageForm.form.findField('cert').getValue(),
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
			    text: lang.add_ebay_account,
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
				    }/*,{
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
				    }*/,{
					xtype:"textarea",
					fieldLabel:"Token",
					height:200,
					width:350,
					name:"token"
				    }],
				    buttons: [{
					text: lang.save,
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
				    title: lang.add_ebay_account,
				    width:600,
				    height:500,
				    iconCls: 'ebay-manage-icon',
				    shim:false,
				    animCollapse:false,
				    constrainHeader:true,
				    layout: 'fit',
				    items:addEbaySellerForm,
				    taskbuttonTooltip: lang.add_ebay_account_tooltip
				});
				addEbaySellerWin.show();
			    }
			},{
			    text: lang.delete_selected_ebay_account,
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
			    text: lang.close,
			    handler: function(){
				ebayManageWin.close();
			    }
			}]
		});
				
		ebayManageWin = desktop.createWindow({
		    id: 'ebay-manage-win',
		    title: lang.ebay_account_manager,
		    width:720,
		    height:500,
		    iconCls: 'ebay-manage-icon',
		    shim:false,
		    animCollapse:false,
		    constrainHeader:true,
		    layout: 'fit',
		    items:ebayManageForm,
		    taskbuttonTooltip: lang.ebay_account_manager_tooltip
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
				   html: lang.loading, 
				   title: lang.waiting
			       });
	     
	    var loadComplete = function(S, r){
		if(!ebayProxyManageWin){
		    var seller = S.reader.jsonData.result.seller;
		    saveComplete(notifyWin, lang.complete, lang.load_ebay_account_proxy_service_success);
		    
		    
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
					    {header: lang.ebay_account, width: 135, sortable: true,  dataIndex: 'ebay_seller_id'},
					    {header: lang.proxy_host, width: 125, sortable: true, dataIndex: 'proxy_host'},
					    {header: lang.proxy_port, width: 40, sortable: true, dataIndex: 'proxy_port'}
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
				fieldLabel: lang.ebay_account,
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
				fieldLabel: lang.proxy_host,
				name: 'proxy_host'
			    },{
				fieldLabel: lang.proxy_port,
				name: 'proxy_port'
			    }]
			}],
			buttons: [{
			    text: lang.add_proxy_service,
			    handler: function(){
				var add_ebay_proxy_form =  form = new Ext.FormPanel({
				    labelAlign: 'top',
				    bodyStyle:'padding:5px',
				    defaultType: 'textfield',
				    items: [{
					    xtype: 'combo',
					    fieldLabel: lang.ebay_account,
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
					    fieldLabel: lang.proxy_host,
					    name: 'proxy_host'
					},{
					    fieldLabel: lang.proxy_port,
					    name: 'proxy_port'
					}]
				})
				
				var addeBayProxyWindow = desktop.createWindow({
				    id: 'add_ebay_proxy_win',
				    title: lang.add_proxy_service,
				    closable:true,
				    width: 400,
				    height: 300,
				    iconCls: 'ebay-proxy-icon',
				    plain:true,
				    layout: 'fit',
				    items: add_ebay_proxy_form,
				    taskbuttonTooltip: lang.add_ebay_proxy_tooltip,
				    buttons: [{
					text: lang.save,
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
					text: lang.close,
					handler: function(){
					      addeBayProxyWindow.close();
					}
				    }]
				});
				addeBayProxyWindow.show();
				console.log(addeBayProxyWindow);
			    }
			},{
			    text: lang.save_selected_ebay_proxy,
			    handler: function(){
				var notifyWin = desktop.showNotification({
					html: lang.saving,
					title: lang.waiting
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
					saveComplete(notifyWin, lang.complete, lang.save_selected_ebay_proxy_success);
				    },
				    failure: function(){
					saveComplete(notifyWin, lang.error, lang.connect_lost);
				    },
				    scope: this
				});
			    }
			},{
			    text: lang.delete_selected_ebay_proxy,
			    handler: function(){
				var notifyWin = desktop.showNotification({
					html: lang.deleting,
					title: lang.waiting
				});
				var form = Ext.getCmp("ebay-proxy-manage-form").getForm();
				Ext.Ajax.request({
				    url: "connect.php?moduleId=qo-manage&action=deleteEbayProxy",
				    params: {
					id: form.findField('id').getValue()
				    },
				    success: function(o){
					store.reload();
					saveComplete(notifyWin, lang.complete, lang.delete_selected_ebay_proxy_success);
				    },
				    failure: function(){
					saveComplete(notifyWin, lang.error, lang.connect_lost);
				    },
				    scope: this
				});
			    }
			},{
			    text: lang.close,
			    handler: function(){
				ebayProxyManageWin.close();
			    }
			}]
		    });
		    ebayProxyManageWin = desktop.createWindow({
				    id: 'ebay-proxy-manage-win',
				    title: lang.ebay_proxy_manager,
				    width:600,
				    height:500,
				    iconCls: 'ebay-proxy-icon',
				    shim:false,
				    animCollapse:false,
				    constrainHeader:true,
				    layout: 'fit',
				    items: gridForm,
				    taskbuttonTooltip: lang.ebay_proxy_manager_tooltip
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
                title: lang.system_manager,
                width:340,
                height:200,
                iconCls: 'manage-icon',
                shim:false,
                animCollapse:false,
                constrainHeader:true,
		layout: 'fit',
                html: '<div class="manage-button"><div class="user-manage"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">'+lang.user_manager+'</div></div>\
		       <div class="manage-button"><div class="group-manage"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">'+lang.user_group_manager+'</div></div>\
		       <div class="manage-button"><div class="group-privilege-manage"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">'+lang.group_privilege_manager+'</div></div>\
		       <div class="manage-button"><div class="ebay-manage"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">'+lang.ebay_account_manager+'</div></div>\
		       <div class="manage-button"><div class="ebay-proxy"><img src="resources/images/default/s.gif"/></div><div class="manage-button-des">'+lang.ebay_proxy_manager+'</div></div>',
                taskbuttonTooltip: lang.system_manager_tooltip
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