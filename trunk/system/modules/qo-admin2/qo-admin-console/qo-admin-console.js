QoDesk.QoAdminConsole = Ext.extend(Ext.app.Module, {

	  moduleType 		  : 'system'
	, moduleId 			  : 'qo-admin-console'
	, phpFile 			  : 'qo-admin-console.php'
	, moduleAuthor		  : 'Paul Simmons'
	, moduleVersion		  : '2.0.0'
	, moduleWindowID	  : 'qo-admin-console-win'
	, moduleTitle		  : 'Admin Console'
	, moduleToolTip		  : 'Admin Console'
	, moduleIconClass	  : 'qoa-console'
	, moduleShortcutClass : 'qoa-console-shortcut'
	//===========================================================================================
	// 2.0.0 Initial Creation
	// This is a complete re-write from the ground up of the QO Admin module suite
	//===========================================================================================
	
	, init : function(){
        this.launcher = {
            handler : this.createWindow
			, iconCls: this.moduleIconClass
			, shortcutIconCls: this.moduleShortcutClass
			, text: this.moduleTitle
			, tooltip: this.moduleToolTip
			, scope: this
        }
    }
	
	, createWindow : function(){
	
	    var desktop = this.app.getDesktop();
        var win = desktop.getWindow(this.moduleWindowID);

		if (!win) {

			// --
			Ext.QuickTips.init();

			// turn on validation errors beside the field globally
			Ext.form.Field.prototype.msgTarget = 'side';

			//===========================================================================================
			// Functions 
			
			//-------------------------------------------------------------------------------------------
			// Delete Functions
			
			// Generic Delete Function
			function handleDelete(store, gridmodel, task_name, scope) {
			
				f_scope = scope || this.obj;
			
				//returns record objects for selected rows (all info for row)
				var selectedRows = gridmodel.selModel.selections.items;
				
				//returns array of selected rows ids only
				var selectedKeys = gridmodel.selModel.selections.keys; 

				//note we already did an if(selectedKeys) to get here

				//encode array into json
				var encoded_keys = Ext.encode(selectedKeys);
				//submit to server
				Ext.Ajax.request({
					//specify options (note success/failure below that receives these same options)
					waitMsg: 'Deleting...'
					, url: f_scope.app.connection
					, params: { 
						task: task_name //pass task to do to the server script
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
						, deleteKeys: encoded_keys
						, key: 'id'//pass to server same 'id' that the reader used
					}
					, callback: function (options, success, response) {
						if (success) { //success will be true if the request succeeded
							Ext.MessageBox.alert('OK',response.responseText);//you won't see this alert if the next one pops up fast
							var json = Ext.util.JSON.decode(response.responseText);
							Ext.MessageBox.alert('OK',json.del_count + ' record(s) deleted.');
						} else {
							Ext.MessageBox.alert('Sorry, please try again.',response.responseText);
						}
					}
					, failure:function(response,options){
						Ext.MessageBox.alert('Warning','Oops...');
					}                                      
					, success:function(response,options){
						store.reload();
					}
					, scope: f_scope
				});
			};

			// wrapper delete functions
			// these are called by the individual delete for grid rows
			function handleDeleteQOMembers() {
				handleDelete (storeGridQOMembers, gridQOMembers, "deleteQOMembers", this);
			};

			function handleDeleteQOGroups() {
				handleDelete (storeGridQOGroups, gridQOGroups, "deleteQOGroups", this);
			};

			function handleDeleteQOMemberGroups() {
				handleDelete (storeGridQOMemberGroups, gridQOMemberGroups, "deleteQOMemberGroups", this);
			};

			function handleDeleteQOModules() {
				handleDelete (storeGridQOModules, gridQOModules, "deleteQOModules", this);
			};

			function handleDeleteQOModuleFiles() {
				handleDelete (storeGridQOModuleFiles, gridQOModuleFiles, "deleteQOModuleFiles", this);
			};

			function handleDeleteQOModuleLaunchers() {
				handleDelete (storeGridQOModuleLaunchers, gridQOModuleLaunchers, "deleteQOModuleLaunchers", this);
			};

			function handleDeleteQOGroupModules() {
				handleDelete (storeGridQOGroupModules, gridQOGroupModules, "deleteQOGroupModules", this);
			};

			function handleDeleteQOSessions() {
				handleDelete (storeGridQOSessions, gridQOSessions, "deleteQOSessions", this);
			};

			function handleDeleteQOFiles() {
				handleDelete (storeGridQOFiles, gridQOFiles, "deleteQOFiles", this);
			};

			//-------------------------------------------------------------------------------------------
			// Save Functions

			// Generic Save function
			function updateRecord (store, oGrid_Event, task_name, scope) {
            
				f_scope = scope || this.obj;
				
				//submit to server
				Ext.Ajax.request({
					waitMsg: 'Saving changes...'
					, url: f_scope.app.connection
					, params: { 
						task: task_name //pass task to do to the server script
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
						, key: 'id' //pass to server same 'id' that the reader used
						, keyID: oGrid_Event.record.data.id
						, field: oGrid_Event.field //the column name
						, value: oGrid_Event.value //the updated value
						, originalValue: oGrid_Event.record.modified
					}
					, failure:function(response,options){
						Ext.MessageBox.alert('Warning','Oops...');
					}                            
					, success:function(response,options){
						store.commitChanges();
					}      
					, scope: f_scope
				});
			}; 

			// Wrapper Save edit functions
			// these are called by individual edit grid listeners
			
			function updateRecordQOMembers (oGrid_Event) {
				updateRecord (storeGridQOMembers, oGrid_Event, "updateQOMember", this);
			}; 

			function updateRecordQOGroups (oGrid_Event) {
				updateRecord (storeGridQOGroups, oGrid_Event, "updateQOGroup", this);
			}; 

			function updateRecordQOMemberGroups (oGrid_Event) {
				updateRecord (storeGridQOMemberGroups, oGrid_Event, "updateQOMemberGroup", this);
			}; 

			function updateRecordQOModules (oGrid_Event) {
				updateRecord (storeGridQOModules, oGrid_Event, "updateQOModule", this);
			}; 

			function updateRecordQOModuleFiles (oGrid_Event) {
				updateRecord (storeGridQOModuleFiles, oGrid_Event, "updateQOModuleFile", this);
			}; 

			function updateRecordQOModuleLaunchers (oGrid_Event) {
				updateRecord (storeGridQOModuleLaunchers, oGrid_Event, "updateQOModuleLauncher", this);
			}; 

			function updateRecordQOGroupModules (oGrid_Event) {
				updateRecord (storeGridQOGroupModules, oGrid_Event, "updateQOGroupModule", this);
			}; 

			function updateRecordQOFiles (oGrid_Event) {
				updateRecord (storeGridQOFiles, oGrid_Event, "updateQOFile", this);
			}; 

			// End Functions 
			//===========================================================================================

			//===========================================================================================
			// Menu Definitions
			
			// Defining Menu items as functions to allow for duplicity of definitions within other menus

			function miExit () {
				return new Ext.menu.Item({
					text: 'Exit'
					, handler: function () {
						win.close();
					}
				});
			}

			function miHelp () {
				return new Ext.menu.Item({
					text: 'Help'
					, iconCls: 'qoa-help'
					, handler: function () {
						new_tab = myTabs.add(panelHelp); 
						new_tab.show();
					}
				});
			}

			function miAbout (scope) {
				f_scope = scope || this.obj;
				f_message = '<b>QO Admin</b><br>' + f_scope.moduleTitle + "<br>Version: "+ f_scope.moduleVersion + "<br>Author: " + f_scope.moduleAuthor;
				return new Ext.menu.Item({
					text: 'About'
					, handler: function () {
						Ext.Msg.show ({
							title: 'About'
							, msg: f_message
							, button: Ext.Msg.OK
						});
					}
				});
			}

			function miNewModule (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-module-register'
					, handler: function () { 
						new_tab = myTabs.add(formQOModule); 
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeFormQOModuleLUGroupNames.reload();
							}
							, this
						);
						
					}
				});
			}
			
			function miEditModules (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-module-edit'
					, handler: function () { 
						storeGridQOModules.load();
						new_tab = myTabs.add(gridQOModules); 
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeGridQOModules.reload();
							}
							, this
						);
					}
				});
			}
			
			function miNewModuleFile (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-module-file-add'
					, handler: function () { 
						new_tab = myTabs.add(formQOModuleFile); 
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeFormQOModuleFileLUModuleNames.reload();
							}
							, this
						);
					}
				});
			}
			
			function miEditModuleFiles (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-module-file-edit'
					, handler: function () { 
						storeGridQOModuleFilesLUModuleNames.load();
						storeGridQOModuleFiles.load();
						new_tab = myTabs.add(gridQOModuleFiles); 
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeGridQOModuleFilesLUModuleNames.reload();
								storeGridQOModuleFiles.reload();
							}
							, this
						);
					}
				});
			}
			
			function miNewModuleLauncher (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-module-launcher-add'
					, handler: function () { 
						new_tab = myTabs.add(formQOModuleLauncher);
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeFormQOModuleLauncherLUModuleNames.reload();
								storeFormQOModuleLauncherLUMemberNamesPlus.reload();
								storeFormQOModuleLauncherLUGroupNamesPlus.reload();
							}
							, this
						);
					}
				});
			}
			
			function miEditModuleLaunchers (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-module-launcher-edit'
					, handler: function () { 
						storeGridQOModuleLaunchersLUModuleNames.load();
						storeGridQOModuleLaunchersLULauncherNames.load();
						storeGridQOModuleLaunchersLUMemberNamesPlus.load();
						storeGridQOModuleLaunchersLUGroupNamesPlus.load();
						storeGridQOModuleLaunchers.load();
						new_tab = myTabs.add(gridQOModuleLaunchers); 
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeGridQOModuleLaunchersLUModuleNames.reload();
								storeGridQOModuleLaunchersLUMemberNamesPlus.reload();
								storeGridQOModuleLaunchersLUGroupNamesPlus.reload();
								storeGridQOModuleLaunchers.reload();
							}
							, this
						);
					}
				});
			}
			
			function miNewMember (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-member-add'
					, handler: function () { 
						new_tab = myTabs.add(formQOMember);
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeFormQOMemberLUGroupNames.reload();
							}
							, this
						);
					}
				});
			}

			function miEditMembers (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-member-edit'
					, handler: function () { 
						storeGridQOMembers.load();
						new_tab = myTabs.add(gridQOMembers); 
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeGridQOMembers.reload();
							}
							, this
						);
					}
				});
			}

			function miNewGroup (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-group-add'
					, handler: function () { 
						myTabs.add(formQOGroup).show(); 
					}
				});
			}

			function miEditGroups (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-group-edit'
					, handler: function () { 
						storeGridQOGroups.load();
						new_tab = myTabs.add(gridQOGroups); 
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeGridQOGroups.reload();
							}
							, this
						);
					}
				});
			}

			function miNewMemberGroup (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-permission-member-group'
					, handler: function () {
						new_tab = myTabs.add(formQOMemberGroup);
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeFormQOMemberGroupLUMemberNames.reload();
								storeFormQOMemberGroupLUGroupNames.reload();
							}
							, this
						);
					}
				});
			}

			function miEditMemberGroups (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-permission-member-group'
					, handler: function () {
						storeGridQOMemberGroupsLUMemberNames.load();
						storeGridQOMemberGroupsLUGroupNames.load();
						storeGridQOMemberGroups.load();
						new_tab = myTabs.add(gridQOMemberGroups);
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeGridQOMemberGroupsLUMemberNames.reload();
								storeGridQOMemberGroupsLUGroupNames.reload();
								storeGridQOMemberGroups.reload();
							}
							, this
						);
					}
				});
			}

			function miNewGroupModule (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-permission-group-module'
					, handler: function () {
						new_tab = myTabs.add(formQOGroupModule);
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeFormQOGroupModuleLUModuleNames.reload();
								storeFormQOGroupModuleLUGroupNames.reload();
							}
							, this
						);
					}
				});
			}

			function miEditGroupModules (menuText) {
				return new Ext.menu.Item({
					text: menuText
					, iconCls: 'qoa-permission-group-module'
					, handler: function () {
						storeGridQOGroupModulesLUModuleNames.load();
						storeGridQOGroupModulesLUGroupNames.load();
						storeGridQOGroupModules.load();
						new_tab = myTabs.add(gridQOGroupModules);
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeGridQOGroupModulesLUModuleNames.reload();
								storeGridQOGroupModulesLUGroupNames.reload();
								storeGridQOGroupModules.reload();
							}
							, this
						);
					}
				});
			}

			function miNewPluginFile (menuText) {
				return new Ext.menu.Item ({
					text: menuText
					, iconCls: 'qoa-plugin-file-add'
					, handler: function () {
						new_tab = myTabs.add(formQOFile);
						new_tab.show();
					}
				});
			}

			function miEditPluginFiles (menuText) {
				return new Ext.menu.Item ({
					text: menuText
					, iconCls: 'qoa-plugin-file-edit'
					, handler: function () {
						storeGridQOFiles.reload();
						new_tab = myTabs.add(gridQOFiles);
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeGridQOFiles.reload();
							}
							, this
						);
					}
				});
			}

			// Window tbar Menues
			var menuWindowFile = new Ext.Action ({
				text: "File"
				, menu: [
					miExit ()
				]
			});
			
			var menuWindowNewModule = new Ext.Action ({
				text: 'Module'
				, iconCls: 'qoa-module'
				, menu: [
					miNewModule ('Module')
					, miNewModuleFile ('File')
					, miNewModuleLauncher ('Launcher')
				]
			});
			
			var menuWindowNew = new Ext.Action ({
				text: "New"
				, menu: [
					miNewMember ("Member")
					, miNewGroup ("Group")
					, menuWindowNewModule
					, miNewPluginFile ("Plugin File")
				]
			});
			
			var menuWindowEditModules = new Ext.Action ({
				text: "Module"
				, iconCls: 'qoa-module'
				, menu: [
					miEditModules ("Info")
					, miEditModuleFiles ("Files")
					, miEditModuleLaunchers ("Launchers")
				]
			});
			
			var menuWindowEdit = new Ext.Action ({
				text: "Edit"
				, menu: [
					miEditMembers ("Members")
					, miEditGroups ("Groups")
					, menuWindowEditModules
					, miEditPluginFiles ("Plugin Files")
				]
			});
				
			var menuWindowPermissions = new Ext.Action ({
				text: "Permissions"
				, menu: [
					miNewMemberGroup ("Assign Member/Group")
					, miEditMemberGroups ("Edit Member/Group")
					, miNewGroupModule ("Assign Group/Module")
					, miEditGroupModules ("Edit Group/Module")					
				]
			});
				
			var menuWindowHelp = new Ext.Action ({
				text: "Help"
				, menu: [
					miHelp ()
					, '-'
					, miAbout(this)
				]
			});
			
			// context menu for the navigation panel

			var menuContNewModule = new Ext.Action ({
				text: 'Module'
				, iconCls: 'qoa-module'
				, menu: [
					miNewModule ('Module')
					, miNewModuleFile ('File')
					, miNewModuleLauncher ('Launcher')
				]
			});

			var navContNew = new Ext.Action ({
				text: "New"
				, menu: [
					miNewMember ("Member")
					, miNewGroup ("Group")
					, menuContNewModule
					, miNewPluginFile ("Plugin File")
				]
			});
			
			var navContEditModule = new Ext.Action ({
				text: "Module"
				, iconCls: 'qoa-module'
				, menu: [
					miEditModules ("Info")
					, miEditModuleFiles ("Files")
					, miEditModuleLaunchers ("Launchers")
				]
			});
			
			var navContEdit = new Ext.Action ({
				text: "Edit"
				, menu: [
					miEditMembers ("Members")
					, miEditGroups ("Groups")
					, navContEditModule
					, miEditPluginFiles ("Plugin Files")
				]
			});
				
			var navContPermissions = new Ext.Action ({
				text: "Permissions"
				, menu: [
					miNewMemberGroup ("Assign Member/Group")
					, miEditMemberGroups ("Edit Member/Group")
					, miNewGroupModule ("Assign Group/Module")
					, miEditGroupModules ("Edit Group/Module")					
				]
			});
				

			var navContMenu = new Ext.menu.Menu ({
				items: [
					navContNew
					, navContEdit
					, navContPermissions
				]
			});

			// End Menu Functions 
			//===========================================================================================
			
			//===========================================================================================
			// Store Definitions
			
			// Dynamic Stores
			function storeQOMembers (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "readQOMembers"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_members'
						, id: 'id'
						, fields: [
							{name: 'id'}
							, {name: 'first_name'}
							, {name: 'last_name'}
							, {name: 'email_address'}
							, {name: 'password'}
							, {name: 'active'}
						]
					})
				});
			};
			storeGridQOMembers = storeQOMembers (this);
			storeGridQOMembers.loadData;

			function storeQOMemberNames (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "lookupQOMemberNames"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_members'
						, id: 'KeyField'
						, fields: [
							{name: 'KeyField'}
							, {name: 'DisplayField'}
						]
					})
				});
			};
			storeGridQOMemberGroupsLUMemberNames = storeQOMemberNames (this);
			storeGridQOMemberGroupsLUMemberNames.loadData;
			storeFormQOMemberGroupLUMemberNames = storeQOMemberNames (this);
			storeFormQOMemberGroupLUMemberNames.loadData;
			storeGridQOSessionsLUMemberNames = storeQOMemberNames (this);
			storeGridQOSessionsLUMemberNames.loadData;
			storeGridQOAdminAuditLUMemberNames = storeQOMemberNames (this);
			storeGridQOAdminAuditLUMemberNames.loadData;

			function storeQOMemberNamesPlus (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "lookupQOMemberNamesPlus"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_members'
						, id: 'KeyField'
						, fields: [
							{name: 'KeyField'}
							, {name: 'DisplayField'}
						]
					})
				});
			};
			storeGridQOModuleLaunchersLUMemberNamesPlus = storeQOMemberNamesPlus (this);
			storeGridQOModuleLaunchersLUMemberNamesPlus.loadData;
			storeFormQOModuleLauncherLUMemberNamesPlus = storeQOMemberNamesPlus (this);
			storeFormQOModuleLauncherLUMemberNamesPlus.loadData;

			function storeQOMemberGroups (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "readQOMemberGroups"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_members_has_groups'
						, id: 'id'
						, fields: [
							{name: 'id'}
							, {name: 'qo_members_id'}
							, {name: 'qo_groups_id'}
							, {name: 'active'}
							, {name: 'admin_flag'}
						]
					})
				});
			};
			storeGridQOMemberGroups = storeQOMemberGroups (this);
			storeGridQOMemberGroups.loadData;

			function storeQOGroups (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "readQOGroups"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_groups'
						, id: 'id'
						, fields: [
							{name: 'id'}
							, {name: 'name'}
							, {name: 'description'}
							, {name: 'active'}
						]
					})
				});
			};
			storeGridQOGroups = storeQOGroups (this);
			storeGridQOGroups.loadData;
			
			function storeQOGroupNames (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "lookupQOGroupNames"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_groups'
						, id: 'KeyField'
						, fields: [
							{name: 'KeyField'}
							, {name: 'DisplayField'}
						]
					})
				});
			};
			storeFormQOMemberLUGroupNames = storeQOGroupNames (this);
			storeFormQOMemberLUGroupNames.loadData;
			storeGridQOMemberGroupsLUGroupNames = storeQOGroupNames (this);
			storeGridQOMemberGroupsLUGroupNames.loadData;
			storeFormQOMemberGroupLUGroupNames = storeQOGroupNames (this);
			storeFormQOMemberGroupLUGroupNames.loadData;
			storeFormQOModuleLUGroupNames = storeQOGroupNames (this);
			storeFormQOModuleLUGroupNames.loadData;
			storeGridQOGroupModulesLUGroupNames = storeQOGroupNames (this);
			storeGridQOGroupModulesLUGroupNames.loadData;
			storeFormQOGroupModuleLUGroupNames = storeQOGroupNames (this);
			storeFormQOGroupModuleLUGroupNames.loadData;

			function storeQOGroupNamesPlus (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "lookupQOGroupNamesPlus"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_groups'
						, id: 'KeyField'
						, fields: [
							{name: 'KeyField'}
							, {name: 'DisplayField'}
						]
					})
				});
			};
			storeGridQOModuleLaunchersLUGroupNamesPlus = storeQOGroupNamesPlus (this);
			storeGridQOModuleLaunchersLUGroupNamesPlus.loadData;
			storeFormQOModuleLauncherLUGroupNamesPlus = storeQOGroupNamesPlus (this);
			storeFormQOModuleLauncherLUGroupNamesPlus.loadData;

			function storeQOModules (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "readQOModules"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_modules'
						, id: 'id'
						, fields: [
							{name: 'id'}
							, {name: 'moduleName'}
							, {name: 'moduleType'}
							, {name: 'fmoduleId'}
							, {name: 'version'}
							, {name: 'author'}
							, {name: 'description'}
							, {name: 'path'}
							, {name: 'active'}
						]
					})
				});
			};
			storeGridQOModules = storeQOModules (this);
			storeGridQOModules.loadData;
			
			function storeQOModuleNames (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "lookupQOModuleNames"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_modules'
						, id: 'KeyField'
						, fields: [
							{name: 'KeyField'}
							, {name: 'DisplayField'}
						]
					})
				});
			};
			storeGridQOModuleFilesLUModuleNames = storeQOModuleNames (this);
			storeGridQOModuleFilesLUModuleNames.loadData;
			storeFormQOModuleFileLUModuleNames = storeQOModuleNames (this);
			storeFormQOModuleFileLUModuleNames.loadData;
			storeGridQOModuleLaunchersLUModuleNames = storeQOModuleNames (this);
			storeGridQOModuleLaunchersLUModuleNames.loadData;
			storeFormQOModuleLauncherLUModuleNames = storeQOModuleNames (this);
			storeFormQOModuleLauncherLUModuleNames.loadData;
			storeGridQOGroupModulesLUModuleNames = storeQOModuleNames (this);
			storeGridQOGroupModulesLUModuleNames.loadData;
			storeFormQOGroupModuleLUModuleNames = storeQOModuleNames (this);
			storeFormQOGroupModuleLUModuleNames.loadData;

			function storeQOModuleFiles (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "readQOModuleFiles"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_modules_has_files'
						, id: 'id'
						, fields: [
							{name: 'id'}
							, {name: 'qo_modules_id'}
							, {name: 'name'}
							, {name: 'type'}
						]
					})
				});
			};
			storeGridQOModuleFiles = storeQOModuleFiles (this);
			storeGridQOModuleFiles.loadData;

			function storeQOModuleLaunchers (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "readQOModuleLaunchers"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_modules_has_launchers'
						, id: 'id'
						, fields: [
							{name: 'id'}
							, {name: 'qo_members_id'}
							, {name: 'qo_groups_id'}
							, {name: 'qo_modules_id'}
							, {name: 'qo_launchers_id'}
							, {name: 'sort_order'}
						]
					})
				});
			};
			storeGridQOModuleLaunchers = storeQOModuleLaunchers (this);
			storeGridQOModuleLaunchers.loadData;
			
			function storeQOLauncherNames (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "lookupQOLauncherNames"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_launchers'
						, id: 'KeyField'
						, fields: [
							{name: 'KeyField'}
							, {name: 'DisplayField'}
						]
					})
				});
			};
			storeFormQOModuleLULauncherNames = storeQOLauncherNames (this);
			storeFormQOModuleLULauncherNames.loadData;
			storeGridQOModuleLaunchersLULauncherNames = storeQOLauncherNames (this);
			storeGridQOModuleLaunchersLULauncherNames.loadData;
			storeFormQOModuleLauncherLULauncherNames = storeQOLauncherNames (this);
			storeFormQOModuleLauncherLULauncherNames.loadData;

			function storeQOGroupModules (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "readQOGroupModules"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_groups_has_modules'
						, id: 'id'
						, fields: [
							{name: 'id'}
							, {name: 'qo_groups_id'}
							, {name: 'qo_modules_id'}
							, {name: 'active'}
						]
					})
				});
			};
			storeGridQOGroupModules = storeQOGroupModules (this);
			storeGridQOGroupModules.loadData;
			
			function storeQOSessions (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "readQOSessions"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_sessions'
						, id: 'id'
						, fields: [
							{name: 'id'}
							, {name: 'qo_members_id'}
							, {name: 'ip'}
							, {name: 'date'}
						]
					})
				});
			};
			storeGridQOSessions = storeQOSessions (this);
			storeGridQOSessions.loadData;

			function storeQOFiles (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "readQOFiles"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_files'
						, id: 'id'
						, fields: [
							{name: 'id'}
							, {name: 'name'}
							, {name: 'path'}
							, {name: 'type'}
						]
					})
				});
			};
			storeGridQOFiles = storeQOFiles (this);
			storeGridQOFiles.loadData;

			function storeQOAdminAudit (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "readQOAdminAudit"
						, moduleId: f_scope.moduleId
						, fileName: f_scope.phpFile
					}
					, reader: new Ext.data.JsonReader ({
						root: 'qo_admin_audit'
						, id: 'id'
						, fields: [
							{name: 'id'}
							, {name: 'qo_members_id'}
							, {name: 'audit_date'}
							, {name: 'audit_state'}
							, {name: 'audit_text'}
						]
					})
				});
			};
			storeGridQOAdminAudit = storeQOAdminAudit (this);
			storeGridQOAdminAudit.loadData;

			// Static Stores
			function storeTrueFalse (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.SimpleStore({
					fields: [ 'value' ]
					, data: [ [ 'true' ], [ 'false' ] ]
				});
			};
			
			storeGridQOMembersLUActive = storeTrueFalse (this);
			storeFormQOMemberLUActive = storeTrueFalse (this);
			storeGridQOGroupsLUActive = storeTrueFalse (this);
			storeFormQOGroupLUActive = storeTrueFalse (this);
			storeGridQOMemberGroupsLUAdmin = storeTrueFalse (this);
			storeGridQOMemberGroupsLUActive = storeTrueFalse (this);
			storeFormQOMemberGroupLUAdmin = storeTrueFalse (this);
			storeFormQOMemberGroupLUActive = storeTrueFalse (this);
			storeGridQOModulesLUActive = storeTrueFalse (this);
			storeFormQOModuleLUActive = storeTrueFalse (this);
			storeGridQOGroupModulesLUActive = storeTrueFalse (this);
			storeFormQOGroupModuleLUActive = storeTrueFalse (this);
			
			function storeModuleFileType (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.SimpleStore ({
					fields: [ 'value' ]
					, data: [ [ 'javascript' ], [ 'css' ], [ 'php' ] ]
				});
			};
			storeGridQOModuleFilesLUFileType = storeModuleFileType (this);
			storeFormQOModuleFileLUFileType = storeModuleFileType (this);
			
			function storeFileFileType (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.SimpleStore ({
					fields: [ 'value' ]
					, data: [ [ 'javascript' ], [ 'css' ] ]
				});
			};
			storeGridQOFilesLUFileType = storeFileFileType (this);
			storeFormQOFileLUFileType = storeFileFileType (this);

			// End Store Definitions
			//===========================================================================================

			//===========================================================================================
			// Column Model Definitions
			
			var cmGridQOMembers = new Ext.grid.ColumnModel([
				{
					id:'id'
					, header: 'ID'
					, dataIndex: 'id'
					, align: 'right'
					, hidden: true
				}
				, {	
					header: 'First Name'
					, dataIndex: 'first_name'
					, width: 100
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'Last Name'
					, dataIndex: 'last_name'
					, width: 100
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'email'
					, dataIndex: 'email_address'
					, vtype: 'email'
					, width: 200
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'Password'
					, dataIndex: 'password'
					, width: 75
					, renderer: function() {
						return '**********';
					}
					, editor: new Ext.form.TextField({
						allowBlank: false
					, inputType: 'password'
					})
				}
				, {	
					header: 'Active'
					, dataIndex: 'active'
					, width: 50
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, mode: 'local'
						, store: storeGridQOMembersLUActive
						, displayField: 'value'
						, valueField: 'value'
					})
				}
			]);
			cmGridQOMembers.defaultSortable = true; // by default columns are sortable
			
			var cmGridQOGroups = new Ext.grid.ColumnModel([
				{
					id:'id'
					, header: 'ID'
					, dataIndex: 'id'
					, align: 'right'
					, hidden: true
				}
				, {	
					header: 'Group Name'
					, dataIndex: 'name'
					, width: 100
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'Description'
					, dataIndex: 'description'
					, width: 200
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'Active'
					, dataIndex: 'active'
					, width: 50
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, mode: 'local'
						, store: storeGridQOGroupsLUActive
						, displayField: 'value'
						, valueField: 'value'
					})
				}
			]);
			cmGridQOGroups.defaultSortable = true; // by default columns are sortable

			var cmGridQOMemberGroups = new Ext.grid.ColumnModel([
				{
					id:'id'
					, header: 'ID'
					, dataIndex: 'id'
					, align: 'right'
					, hidden: true
				}
				, {	
					header: 'Member Name'
					, dataIndex: 'qo_members_id'
					, width: 150
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, store: storeGridQOMemberGroupsLUMemberNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
					})
					, renderer: function(data) {
						record = storeGridQOMemberGroupsLUMemberNames.getById(data);
						if(record) {
							return record.data.DisplayField;
						} else {
							return 'missing data';
						}
					}
				}
				, {	
					header: 'Group Name'
					, dataIndex: 'qo_groups_id'
					, width: 150
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, store: storeGridQOMemberGroupsLUGroupNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
					})
					, renderer: function(data) {
						record = storeGridQOMemberGroupsLUGroupNames.getById(data);
						if(record) {
							return record.data.DisplayField;
						} else {
							return 'missing data';
						}
					}
				}
				, {	
					header: 'Active'
					, dataIndex: 'active'
					, width: 50
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, mode: 'local'
						, store: storeGridQOMemberGroupsLUActive
						, displayField: 'value'
						, valueField: 'value'
					})
				}
				, {	
					header: 'Admin'
					, dataIndex: 'admin_flag'
					, width: 50
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, mode: 'local'
						, store: storeGridQOMemberGroupsLUAdmin
						, displayField: 'value'
						, valueField: 'value'
					})
				}
			]);
			cmGridQOMemberGroups.defaultSortable = true;
			
			var cmGridQOModules = new Ext.grid.ColumnModel([
				{
					id:'id'
					, header: 'ID'
					, dataIndex: 'id'
					, align: 'right'
					, hidden: true
				}
				, {	
					header: 'Module Name'
					, dataIndex: 'moduleName'
					, width: 200
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'Module Type'
					, dataIndex: 'moduleType'
					, width: 75
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'Module ID'
					, dataIndex: 'fmoduleId'
					, width: 100
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'Version'
					, dataIndex: 'version'
					, width: 65
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'Author'
					, dataIndex: 'author'
					, width: 100
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'Description'
					, dataIndex: 'description'
					, width: 300
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'Path'
					, dataIndex: 'path'
					, width: 300
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'Active'
					, dataIndex: 'active'
					, width: 50
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, mode: 'local'
						, store: storeGridQOModulesLUActive
						, displayField: 'value'
						, valueField: 'value'
					})
				}
			]);
			cmGridQOModules.defaultSortable = true;

			var cmGridQOModuleFiles = new Ext.grid.ColumnModel([
				{
					id:'id'
					, header: 'ID'
					, dataIndex: 'id'
					, align: 'right'
					, hidden: true
				}
				, {	
					header: 'Module Name'
					, dataIndex: 'qo_modules_id'
					, width: 200
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, store: storeGridQOModuleFilesLUModuleNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
					})
					, renderer: function(data) {
						record = storeGridQOModuleFilesLUModuleNames.getById(data);
						if(record) {
							return record.data.DisplayField;
						} else {
							return 'missing data';
						}
					}
				}
				, {	
					header: 'File Name'
					, dataIndex: 'name'
					, width: 150
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'File Type'
					, dataIndex: 'type'
					, width: 75
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, mode: 'local'
						, store: storeGridQOModuleFilesLUFileType
						, displayField: 'value'
						, valueField: 'value'
					})
				}
			]);
			cmGridQOModuleFiles.defaultSortable = true;

			var cmGridQOModuleLaunchers = new Ext.grid.ColumnModel([
				{
					id:'id'
					, header: 'ID'
					, dataIndex: 'id'
					, align: 'right'
					, hidden: true
				}
				, {	
					header: 'Member Name'
					, dataIndex: 'qo_members_id'
					, width: 150
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, store: storeGridQOModuleLaunchersLUMemberNamesPlus
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
					})
					, renderer: function(data) {
						record = storeGridQOModuleLaunchersLUMemberNamesPlus.getById(data);
						if(record) {
							return record.data.DisplayField;
						} else {
							return 'missing data';
						}
					}
				}
				, {	
					header: 'Group Name'
					, dataIndex: 'qo_groups_id'
					, width: 150
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, store: storeGridQOModuleLaunchersLUGroupNamesPlus
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
					})
					, renderer: function(data) {
						record = storeGridQOModuleLaunchersLUGroupNamesPlus.getById(data);
						if(record) {
							return record.data.DisplayField;
						} else {
							return 'missing data';
						}
					}
				}
				, {	
					header: 'Module Name'
					, dataIndex: 'qo_modules_id'
					, width: 150
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, store: storeGridQOModuleLaunchersLUModuleNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
					})
					, renderer: function(data) {
						record = storeGridQOModuleLaunchersLUModuleNames.getById(data);
						if(record) {
							return record.data.DisplayField;
						} else {
							return 'missing data';
						}
					}
				}
				, {	
					header: 'Launcher Name'
					, dataIndex: 'qo_launchers_id'
					, width: 150
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, store: storeGridQOModuleLaunchersLULauncherNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
					})
					, renderer: function(data) {
						record = storeGridQOModuleLaunchersLULauncherNames.getById(data);
						if(record) {
							return record.data.DisplayField;
						} else {
							return 'missing data';
						}
					}
				}
				, {	
					header: 'Sort Order'
					, dataIndex: 'sort_order'
					, width: 75
					, editor: new Ext.form.NumberField({
						allowBlank: false
					})
				}
			]);
			cmGridQOModuleLaunchers.defaultSortable = true;

			var cmGridQOGroupModules = new Ext.grid.ColumnModel([
				{
					id:'id'
					, header: 'ID'
					, dataIndex: 'id'
					, align: 'right'
					, hidden: true
				}
				, {	
					header: 'Group Name'
					, dataIndex: 'qo_groups_id'
					, width: 100
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, store: storeGridQOGroupModulesLUGroupNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
					})
					, renderer: function(data) {
						record = storeGridQOGroupModulesLUGroupNames.getById(data);
						if(record) {
							return record.data.DisplayField;
						} else {
							return 'missing data';
						}
					}
				}
				, {	
					header: 'Module Name'
					, dataIndex: 'qo_modules_id'
					, width: 200
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, store: storeGridQOGroupModulesLUModuleNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
					})
					, renderer: function(data) {
						record = storeGridQOGroupModulesLUModuleNames.getById(data);
						if(record) {
							return record.data.DisplayField;
						} else {
							return 'missing data';
						}
					}
				}
				, {	
					header: 'Active'
					, dataIndex: 'active'
					, width: 50
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, mode: 'local'
						, store: storeGridQOGroupModulesLUActive
						, displayField: 'value'
						, valueField: 'value'
					})
				}
			]);
			cmGridQOGroupModules.defaultSortable = true;
	
			var cmGridQOSessions = new Ext.grid.ColumnModel([
				{
					id:'id'
					, header: 'ID'
					, dataIndex: 'id'
					, align: 'right'
					, width: 200
				}
				, {	
					header: 'Member'
					, dataIndex: 'qo_members_id'
					, width: 200
					, renderer: function(data) {
						record = storeGridQOSessionsLUMemberNames.getById(data);
						if(record) {
							return record.data.DisplayField;
						} else {
							return 'missing data';
						}
					}
				}
				, {	
					header: 'IP Address'
					, dataIndex: 'ip'
					, width: 100
				}
				, {	
					header: 'Date'
					, dataIndex: 'date'
					, width: 120
				}
			]);
			cmGridQOSessions.defaultSortable = true;
	
			var cmGridQOFiles = new Ext.grid.ColumnModel([
				{
					id:'id'
					, header: 'ID'
					, dataIndex: 'id'
					, align: 'right'
					, width: 200
					, hidden: true
				}
				, {	
					header: 'Name'
					, dataIndex: 'name'
					, width: 200
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'Path'
					, dataIndex: 'path'
					, width: 300
					, editor: new Ext.form.TextField({
						allowBlank: false
					})
				}
				, {	
					header: 'File Type'
					, dataIndex: 'type'
					, width: 50
					, editor: new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: true
						, mode: 'local'
						, store: storeGridQOFilesLUFileType
						, displayField: 'value'
						, valueField: 'value'
					})
				}
			]);
			cmGridQOFiles.defaultSortable = true;

			var cmGridQOAdminAudit = new Ext.grid.ColumnModel([
				{
					id:'id'
					, header: 'ID'
					, dataIndex: 'id'
					, align: 'right'
					, width: 200
					, hidden: true
				}
				, {	
					header: 'Time Stamp'
					, dataIndex: 'audit_date'
					, width: 120
				}
				, {	
					header: 'Member'
					, dataIndex: 'qo_members_id'
					, width: 200
					, renderer: function(data) {
						record = storeGridQOAdminAuditLUMemberNames.getById(data);
						if(record) {
							return record.data.DisplayField;
						} else {
							return 'missing data';
						}
					}
				}
				, {	
					header: 'Status'
					, dataIndex: 'audit_state'
					, width: 100
				}
				, {	
					header: 'Text'
					, dataIndex: 'audit_text'
					, width: 800
					, renderer: function (data, metadata) {
						//metadata.css = 'qoa-wrap-cell-data';
						return data;
					}
				}
			]);
			cmGridQOAdminAudit.defaultSortable = true;

			// End Column Model Definitions
			//===========================================================================================

			//===========================================================================================
			// Grid Definitions

			// Edit Members definition
			var gridQOMembers = new Ext.grid.EditorGridPanel({
				store: storeGridQOMembers
				, cm: cmGridQOMembers
				, height: 150
				, title:'Edit Members'
				, frame: false
				, border : false
				, iconCls: 'qoa-member-edit'
				, clicksToEdit: 2
				, closable: true
				, selModel: new Ext.grid.RowSelectionModel({singleSelect:false}) // False allows multiple row selection
				, tbar: [
					{
						text: 'Delete'
						, tooltip: 'Select row(s) to delete'
						, handler: handleDeleteQOMembers
						, scope: this
					}
					, {
						text: 'Refresh'
						, tooltip: 'Refresh grid'
						, handler: function () {
							storeGridQOMembers.reload()
						}
					}
				]
			});
			gridQOMembers.addListener('afteredit', updateRecordQOMembers, this);
			
			var gridQOGroups = new Ext.grid.EditorGridPanel({
				store: storeGridQOGroups
				, cm: cmGridQOGroups
				, height: 150
				, title:'Edit Groups'
				, frame: false
				, border : false
				, iconCls: 'qoa-group-edit'
				, clicksToEdit: 2
				, closable: true
				, selModel: new Ext.grid.RowSelectionModel({singleSelect:false}) // False allows multiple row selection
				, tbar: [
					{
						text: 'Delete'
						, tooltip: 'Select row(s) to delete'
						, handler: handleDeleteQOGroups
						, scope: this
					}
					, {
						text: 'Refresh'
						, tooltip: 'Refresh grid'
						, handler: function () {
							storeGridQOGroups.reload()
						}
					}
				]
			});
			gridQOGroups.addListener('afteredit', updateRecordQOGroups, this);

			var gridQOMemberGroups = new Ext.grid.EditorGridPanel({
				store: storeGridQOMemberGroups
				, cm: cmGridQOMemberGroups
				, height: 150
				, title:'Edit Member Groups'
				, frame: false
				, border: false
				, iconCls: 'qoa-permission-member-group'
				, clicksToEdit: 2
				, closable: true
				, selModel: new Ext.grid.RowSelectionModel({singleSelect:false})
				, tbar: [
					{
						text: 'Delete'
						, tooltip: 'Select row to delete'
						, handler: handleDeleteQOMemberGroups
						, scope: this
					}
					, {
						text: 'Refresh'
						, tooltip: 'Refresh grid'
						, handler: function () {
							storeGridQOMemberGroups.reload()
						}
					}
				]
			});
			gridQOMemberGroups.addListener('afteredit', updateRecordQOMemberGroups, this);

			var gridQOModules = new Ext.grid.EditorGridPanel({
				store: storeGridQOModules
				, cm: cmGridQOModules
				, height: 150
				, title:'Edit Modules'
				, frame: false
				, border : false
				, iconCls: 'qoa-module-edit'
				, clicksToEdit: 2
				, closable: true
				, selModel: new Ext.grid.RowSelectionModel({singleSelect:false}) // False allows multiple row selection
				, tbar: [
					{
						text: 'Delete'
						, tooltip: 'Select row(s) to delete'
						, handler: handleDeleteQOModules
						, scope: this
					}
					, {
						text: 'Refresh'
						, tooltip: 'Refresh grid'
						, handler: function () {
							storeGridQOModules.reload()
						}
					}
				]
			});
			gridQOModules.addListener('afteredit', updateRecordQOModules, this);

			var gridQOModuleFiles = new Ext.grid.EditorGridPanel({
				store: storeGridQOModuleFiles
				, cm: cmGridQOModuleFiles
				, height: 150
				, title:'Edit Module Files'
				, frame: false
				, border : false
				, iconCls: 'qoa-module-file-edit'
				, clicksToEdit: 2
				, closable: true
				, selModel: new Ext.grid.RowSelectionModel({singleSelect:false})
				, tbar: [
					{
						text: 'Delete'
						, tooltip: 'Select row to delete'
						, handler: handleDeleteQOModuleFiles
						, scope: this
					}
					, {
						text: 'Refresh'
						, tooltip: 'Refresh grid'
						, handler: function () {
							storeGridQOModuleFiles.reload()
						}
					}
				]
			});
			gridQOModuleFiles.addListener('afteredit', updateRecordQOModuleFiles, this);

			var gridQOModuleLaunchers = new Ext.grid.EditorGridPanel({
				store: storeGridQOModuleLaunchers
				, cm: cmGridQOModuleLaunchers
				, height: 150
				, title:'Edit Module Launchers'
				, frame: false
				, border : false
				, iconCls: 'qoa-module-launcher-edit'
				, clicksToEdit: 2
				, closable: true
				, selModel: new Ext.grid.RowSelectionModel({singleSelect:false})
				, tbar: [
					{
						text: 'Delete'
						, tooltip: 'Select row to delete'
						, handler: handleDeleteQOModuleLaunchers
						, scope: this
					}
					, {
						text: 'Refresh'
						, tooltip: 'Refresh grid'
						, handler: function () {
							storeGridQOModuleLaunchers.reload()
						}
					}
				]
			});
			gridQOModuleLaunchers.addListener('afteredit', updateRecordQOModuleLaunchers, this);

			var gridQOGroupModules = new Ext.grid.EditorGridPanel({
				store: storeGridQOGroupModules
				, cm: cmGridQOGroupModules
				, height: 150
				, title:'Edit Group Modules'
				, frame: false
				, border : false
				, iconCls: 'qoa-permission-group-module'
				, clicksToEdit: 2
				, closable: true
				, selModel: new Ext.grid.RowSelectionModel({singleSelect:false})
				, tbar: [
					{
						text: 'Delete'
						, tooltip: 'Select row to delete'
						, handler: handleDeleteQOGroupModules
						, scope: this
					}
					, {
						text: 'Refresh'
						, tooltip: 'Refresh grid'
						, handler: function () {
							storeGridQOGroupModules.reload()
						}
					}
				]
			});
			gridQOGroupModules.addListener('afteredit', updateRecordQOGroupModules, this);

			var gridQOSessions = new Ext.grid.EditorGridPanel({
				id: 'qo-admin-console-sessions'
				, store: storeGridQOSessions
				, cm: cmGridQOSessions
				, height: 150
				, title:'Session'
				, frame: false
				, border : false
				, iconCls: 'qoa-session'
				, clicksToEdit: 0
				, closable: true
				, selModel: new Ext.grid.RowSelectionModel({singleSelect:false})
				, tbar: [
					{
						text: 'Delete'
						, tooltip: 'Select row to delete'
						, handler: handleDeleteQOSessions
						, scope: this
					}
					, {
						text: 'Refresh'
						, tooltip: 'Refresh grid'
						, handler: function () {
							storeGridQOSessions.reload()
						}
					}
				]
			});

			var gridQOFiles = new Ext.grid.EditorGridPanel({
				id: 'qo-admin-console-files'
				, store: storeGridQOFiles
				, cm: cmGridQOFiles
				, height: 150
				, title:'Edit Plugin Files'
				, frame: false
				, border : false
				, iconCls: 'qoa-plugin-file-edit'
				, clicksToEdit: 2
				, closable: true
				, selModel: new Ext.grid.RowSelectionModel({singleSelect:false})
				, tbar: [
					{
						text: 'Delete'
						, tooltip: 'Select row to delete'
						, handler: handleDeleteQOFiles
						, scope: this
					}
					, {
						text: 'Refresh'
						, tooltip: 'Refresh grid'
						, handler: function () {
							storeGridQOFiles.reload()
						}
					}
				]
			});

			var gridQOAdminAudit = new Ext.grid.GridPanel({
				id: 'qo-admin-audit-trail'
				, store: storeGridQOAdminAudit
				, cm: cmGridQOAdminAudit
				, height: 150
				, title:'Audit Trail'
				, frame: false
				, border : false
				, iconCls: 'qoa-admin-audit'
				, closable: true
				, tbar: [
					{
						text: 'Refresh'
						, tooltip: 'Refresh grid'
						, handler: function () {
							storeGridQOAdminAudit.reload()
						}
					}
				]
			});

			// End Grid Definitions
			//===========================================================================================

			//===========================================================================================
			// Form Definitions

			// Form Members Definition
			var formQOMember = new Ext.FormPanel ({
				title: 'New Member'
				, labelWidth: 75
				, url: this.app.connection
				, bodyStyle:'padding:5px 5px 0'
				, frame: false
				, border : false
				, iconCls: 'qoa-member-add'
				, closable: true
				, items: [
					new Ext.form.Hidden ({ name: 'task', value: 'newQOMember' })
					, new Ext.form.Hidden ({ name: 'moduleId', value: this.moduleId })
					, new Ext.form.Hidden ({ name: 'fileName', value: this.phpFile })
					, {
						fieldLabel: 'First Name'
						, name: 'first_name'
						, allowBlank: false
						, xtype: 'textfield'
						, width: 200
					}
					, {
						fieldLabel: 'Last Name'
						, name: 'last_name'
						, allowBlank:false
						, xtype:'textfield'
						, width: 200
					}
					, {
						fieldLabel: 'Email'
						, name: 'email_address'
						, vtype: 'email'
						, allowBlank:false
						, xtype:'textfield'
						, width: 200
					}
					, {
						fieldLabel: 'Password'
						, name: 'password'
						, allowBlank:false
						, xtype:'textfield'
						, width: 100
					}
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, mode: 'local'
						, store: storeFormQOMemberLUActive
						, displayField: 'value'
						, valueField: 'value'
						, hiddenName: 'active'
						, fieldLabel: 'Active'
						, value: 'true'
						, width: 75
					})
					, {
						xtype: 'fieldset'
						, title: 'Initial Group'
						, autoHeight:true
						, items: [
							new Ext.form.ComboBox({
								typeAhead: false
								, triggerAction: 'all'
								, lazyRender: false
								, store: storeFormQOMemberLUGroupNames
								, displayField: 'DisplayField'
								, valueField: 'KeyField'
								, hiddenName: 'qo_groups_id'
								, fieldLabel: 'Group Name'
								, width: 200 
							})
						]
					}
				]
				, tbar: [
					{
						text: 'Save'
						, type: 'submit'
						, handler: function () {
							formQOMember.getForm().submit({
								waitMsg: 'Saving...'
								, success: function (form, action) {
									Ext.MessageBox.alert('OK',action.result.save_message);
									formQOMember.getForm().reset();
								}
								, failure: function (response,options) {
									Ext.MessageBox.alert('Error','Unable to save record');
								}
							});
						}
					}
					, {
						text: 'Reset'
						, type: 'reset'
						, handler: function () {
							formQOMember.getForm().reset();
						}
					}
				]
			});
			
			// form New Group Definition
			var formQOGroup = new Ext.FormPanel ({
				title: 'New Group'
				, labelWidth: 75
				, url: this.app.connection
				, bodyStyle:'padding:5px 5px 0'
				, frame: false
				, border : false
				, iconCls: 'qoa-group-add'
				, closable: true
				, items: [
					new Ext.form.Hidden ({ name: 'task', value: 'newQOGroup' })
					, new Ext.form.Hidden ({ name: 'moduleId', value: this.moduleId })
					, new Ext.form.Hidden ({ name: 'fileName', value: this.phpFile })
					, {
						fieldLabel: 'Group Name'
						, name: 'name'
						, allowBlank:false
						, xtype:'textfield'
						, width: 200
					}
					, {
						fieldLabel: 'Description'
						, name: 'description'
						, allowBlank:false
						, xtype:'textfield'
						, width: 200
					}
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, mode: 'local'
						, store: storeFormQOGroupLUActive
						, displayField: 'value'
						, valueField: 'value'
						, hiddenName: 'active'
						, fieldLabel: 'Active'
						, width: 75
					})
				]
				, tbar: [
					{
						text: 'Save'
						, type: 'submit'
						, handler: function () {
							formQOGroup.getForm().submit({
								waitMsg: 'Saving...'
								, success: function (response,options) {
									formQOGroup.getForm().reset();
								}
								, failure: function (response,options) {
									Ext.MessageBox.alert('Error','Unable to save record');
								}
							});
						}
					}
					, {
						text: 'Reset'
						, type: 'reset'
						, handler: function () {
							formQOGroup.getForm().reset();
						}
					}
				]
			});

			var formQOMemberGroup = new Ext.FormPanel ({
				title: 'New Group Member'
				, labelWidth: 85
				, url: this.app.connection
				, bodyStyle:'padding:5px 5px 0'
				, frame: false
				, border : false
				, iconCls: 'qoa-permission-member-group'
				, closable: true
				, items: [
					new Ext.form.Hidden ({ name: 'task', value: 'newQOMemberGroup' })
					, new Ext.form.Hidden ({ name: 'moduleId', value: this.moduleId })
					, new Ext.form.Hidden ({ name: 'fileName', value: this.phpFile })
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, store: storeFormQOMemberGroupLUMemberNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
						, hiddenName: 'qo_members_id'
						, fieldLabel: 'Member Name'
						, width: 200 
					})
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, store: storeFormQOMemberGroupLUGroupNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
						, hiddenName: 'qo_groups_id'
						, fieldLabel: 'Group Name'
						, width: 200 
					})
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, mode: 'local'
						, store: storeFormQOMemberGroupLUActive
						, displayField: 'value'
						, valueField: 'value'
						, hiddenName: 'active'
						, fieldLabel: 'Active'
						, width: 75
						, value: 'true'
					})
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, mode: 'local'
						, store: storeFormQOMemberGroupLUAdmin
						, displayField: 'value'
						, valueField: 'value'
						, hiddenName: 'admin_flag'
						, fieldLabel: 'Admin'
						, width: 75
						, value: 'false'
					})
				]
				, tbar: [
					{
						text: 'Save'
						, type: 'submit'
						, handler: function () {
							formQOMemberGroup.getForm().submit({
								waitMsg: 'Saving...'
								, success: function (response,options) {
									formQOMemberGroup.getForm().reset();
								}
								, failure: function (response,options) {
									Ext.MessageBox.alert('Error','Unable to save record');
								}
							});
						}
					}
					, {
						text: 'Reset'
						, type: 'reset'
						, handler: function () {
							formQOMemberGroup.getForm().reset();
						}
					}
				]
			});

			var formQOModule = new Ext.FormPanel ({
				title: 'New Module'
				, labelWidth: 85
				, url: this.app.connection
				, bodyStyle:'padding:5px 5px 0'
				, frame: false
				, border : false
				, iconCls: 'qoa-module-register'
				, closable: true
				, items: [
					new Ext.form.Hidden ({ name: 'task', value: 'newQOModule' })
					, new Ext.form.Hidden ({ name: 'moduleId', value: this.moduleId })
					, new Ext.form.Hidden ({ name: 'fileName', value: this.phpFile })
					, {
						layout: 'column'
						, border: false
						, items: [
							{
								columnWidth: 0.5
								, layout: 'form'
								, border: false
								, items: [
									{
										fieldLabel: 'Module Name'
										, name: 'moduleName'
										, allowBlank:false
										, width: 200
										, xtype:'textfield'
										, value: 'QoDesk.'
									}
									, {
										fieldLabel: 'Module Type'
										, name: 'moduleType'
										, allowBlank:false
										, width: 200
										, xtype:'textfield'
									}
									, {
										fieldLabel: 'Module ID'
										, name: 'fmoduleId'
										, allowBlank:false
										, width: 200
										, xtype:'textfield'
									}
								]
							}
							, {
								columnWidth: 0.5
								, layout: 'form'
								, border: false
								, items: [
									{
										fieldLabel: 'Version'
										, name: 'version'
										, allowBlank:false
										, width: 200
										, xtype:'textfield'
									}
									, {
										fieldLabel: 'Author'
										, name: 'author'
										, allowBlank:false
										, width: 200
										, xtype:'textfield'
									}
								]
							}
						]
					}
					, {
						fieldLabel: 'Description'
						, name: 'description'
						, allowBlank:false
						, width: 400
						, xtype:'textfield'
					}
					, {
						fieldLabel: 'Path'
						, name: 'path'
						, value: 'system/modules/'
						, allowBlank:false
						, width: 400
						, xtype:'textfield'
					}
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, mode: 'local'
						, store: storeFormQOModuleLUActive
						, displayField: 'value'
						, valueField: 'value'
						, hiddenName: 'active'
						, fieldLabel: 'Active'
						, value: 'true'
						, width: 100 
					})
					, {
						layout: 'column'
						, border: false
						, items: [
							{
								columnWidth: 0.5
								, style : 'margin-right: 5px'
								, layout: 'form'
								, border: false
								, items: [
									{
										xtype: 'fieldset'
										, title: 'Files'
										, autoHeight: true
										, items: [
											{
												fieldLabel: 'Javascript'
												, name: 'file_js'
												, allowBlank: true
												, width: 200
												, xtype:'textfield'
											}
											, {
												fieldLabel: 'PHP'
												, name: 'file_php'
												, allowBlank: true
												, width: 200
												, xtype:'textfield'
											}
											, {
												fieldLabel: 'CSS'
												, name: 'file_css'
												, allowBlank:true
												, width: 200
												, xtype:'textfield'
											}
										]
									}
								]
							}
							, {
								columnWidth: 0.5
								, style : 'margin-left: 5px'
								, layout: 'form'
								, border: false
								, items: [
									{
										xtype: 'fieldset'
										, title: 'Base Group'
										, autoHeight: true
										, items : [
											new Ext.form.ComboBox({
												typeAhead: false
												, triggerAction: 'all'
												, lazyRender: false
												, store: storeFormQOModuleLUGroupNames
												, displayField: 'DisplayField'
												, valueField: 'KeyField'
												, hiddenName: 'qo_groups_id'
												, fieldLabel: 'Group'
												, width: 200 
											})
										]
									}
									, {
										xtype: 'fieldset'
										, title: 'Base Launcher'
										, autoHeight: true
										, items : [
											new Ext.form.ComboBox({
												typeAhead: false
												, triggerAction: 'all'
												, lazyRender: false
												, store: storeFormQOModuleLULauncherNames
												, displayField: 'DisplayField'
												, valueField: 'KeyField'
												, hiddenName: 'qo_launchers_id'
												, fieldLabel: 'Launcher'
												, width: 200 
											})
										]
									}
								]
							}
						]
					}
					, new Ext.form.Checkbox ({
						fieldLabel: 'DB install'
						, value: 'true'
						, boxLabel: ' Will execute the file "db_install.sql" against the database, if in doubt leave this unchecked'
						, name: 'has_db_script'
					})
				]
				, tbar: [
					{
						text: 'Save'
						, type: 'submit'
						, handler: function () {
							formQOModule.getForm().submit({
								waitMsg: 'Saving...'
								, success: function (form, action) {
									Ext.MessageBox.alert('OK',action.result.save_message);
									formQOModule.getForm().reset();
								}
								, failure: function (response,options) {
									Ext.MessageBox.alert('Error','Unable to save record');
								}
							});
						}
					}
					, {
						text: 'Reset'
						, type: 'reset'
						, handler: function () {
							formQOModule.getForm().reset();
						}
					}
				]
			});

			var formQOModuleFile = new Ext.FormPanel ({
				title: 'New Module File'
				, labelWidth: 85
				, url: this.app.connection
				, bodyStyle:'padding:5px 5px 0'
				, frame: false
				, border : false
				, iconCls: 'qoa-module-file-add'
				, closable: true
				, items: [
					new Ext.form.Hidden ({ name: 'task', value: 'newQOModuleFile' })
					, new Ext.form.Hidden ({ name: 'moduleId', value: this.moduleId })
					, new Ext.form.Hidden ({ name: 'fileName', value: this.phpFile })
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, store: storeFormQOModuleFileLUModuleNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
						, hiddenName: 'qo_modules_id'
						, fieldLabel: 'Module Name'
						, width: 200 
					})
					, {
						fieldLabel: 'File Name'
						, name: 'name'
						, allowBlank:false
						, width: 200
						, xtype:'textfield'
					}
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, mode: 'local'
						, store: storeFormQOModuleFileLUFileType
						, displayField: 'value'
						, valueField: 'value'
						, hiddenName: 'type'
						, fieldLabel: 'File Type'
						, width: 75
					})
				]
				, tbar: [
					{
						text: 'Save'
						, type: 'submit'
						, handler: function () {
							formQOModuleFile.getForm().submit({
								waitMsg: 'Saving...'
								, success: function (response,options) {
									formQOModuleFile.getForm().reset();
								}
								, failure: function (response,options) {
									Ext.MessageBox.alert('Error','Unable to save record');
								}
							});
						}
					}
					, {
						text: 'Reset'
						, type: 'reset'
						, handler: function () {
							formQOModuleFile.getForm().reset();
						}
					}
				]
			});

			var formQOModuleLauncher = new Ext.FormPanel ({
				title: 'New Module Launcher'
				, labelWidth: 95
				, url: this.app.connection
				, bodyStyle:'padding:5px 5px 0'
				, frame: false
				, border : false
				, iconCls: 'qoa-module-launcher-add'
				, closable: true
				, items: [
					new Ext.form.Hidden ({ name: 'task', value: 'newQOModuleLauncher' })
					, new Ext.form.Hidden ({ name: 'moduleId', value: this.moduleId })
					, new Ext.form.Hidden ({ name: 'fileName', value: this.phpFile })
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, store: storeFormQOModuleLauncherLUMemberNamesPlus
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
						, hiddenName: 'qo_members_id'
						, fieldLabel: 'Member Name'
						, width: 200 
					})
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, store: storeFormQOModuleLauncherLUGroupNamesPlus
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
						, hiddenName: 'qo_groups_id'
						, fieldLabel: 'Group Name'
						, width: 200 
					})
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, store: storeFormQOModuleLauncherLUModuleNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
						, hiddenName: 'qo_modules_id'
						, fieldLabel: 'Module Name'
						, width: 200 
					})
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, store: storeFormQOModuleLauncherLULauncherNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
						, hiddenName: 'qo_launchers_id'
						, fieldLabel: 'Launcher Name'
						, width: 200 
					})
					, {
						fieldLabel: 'Sort Order'
						, name: 'sort_order'
						, allowBlank:false
						, width: 75
						, xtype:'numberfield'
						, value: 20
					}
				]
				, tbar: [
					{
						text: 'Save'
						, type: 'submit'
						, handler: function () {
							formQOModuleLauncher.getForm().submit({
								waitMsg: 'Saving...'
								, success: function (response,options) {
									formQOModuleLauncher.getForm().reset();
								}
								, failure: function (response,options) {
									Ext.MessageBox.alert('Error','Unable to save record');
								}
							});
						}
					}
					, {
						text: 'Reset'
						, type: 'reset'
						, handler: function () {
							formQOModuleLauncher.getForm().reset();
						}
					}
				]
			});

			var formQOGroupModule = new Ext.FormPanel ({
				title: 'New Group Module'
				, labelWidth: 85
				, url: this.app.connection
				, bodyStyle:'padding:5px 5px 0'
				, closable: true
				, frame: false
				, border : false
				, iconCls: 'qoa-permission-group-module'
				, items: [
					new Ext.form.Hidden ({ name: 'task', value: 'newQOGroupModule' })
					, new Ext.form.Hidden ({ name: 'moduleId', value: this.moduleId })
					, new Ext.form.Hidden ({ name: 'fileName', value: this.phpFile })
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, store: storeFormQOGroupModuleLUGroupNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
						, hiddenName: 'qo_groups_id'
						, fieldLabel: 'Group Name'
						, width: 200 
					})
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, store: storeFormQOGroupModuleLUModuleNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
						, hiddenName: 'qo_modules_id'
						, fieldLabel: 'Module Name'
						, width: 200 
					})
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, mode: 'local'
						, store: storeFormQOGroupModuleLUActive
						, displayField: 'value'
						, valueField: 'value'
						, hiddenName: 'active'
						, fieldLabel: 'Active'
						, width: 75
						, value: 'true'
					})
				]
				, tbar: [
					{
						text: 'Save'
						, type: 'submit'
						, handler: function () {
							formQOGroupModule.getForm().submit({
								waitMsg: 'Saving...'
								, success: function (response,options) {
									formQOGroupModule.getForm().reset();
								}
								, failure: function (response,options) {
									Ext.MessageBox.alert('Error','Unable to save record');
								}
							});
						}
					}
					, {
						text: 'Reset'
						, type: 'reset'
						, handler: function () {
							formQOGroupModule.getForm().reset();
						}
					}
				]
			});

			// form New Plugin File Definition
			var formQOFile = new Ext.FormPanel ({
				title: 'New Plugin File'
				, labelWidth: 75
				, url: this.app.connection
				, bodyStyle:'padding:5px 5px 0'
				, frame: false
				, border : false
				, iconCls: 'qoa-plugin-file-add'
				, closable: true
				, items: [
					new Ext.form.Hidden ({ name: 'task', value: 'newQOFile' })
					, new Ext.form.Hidden ({ name: 'moduleId', value: this.moduleId })
					, new Ext.form.Hidden ({ name: 'fileName', value: this.phpFile })
					, {
						fieldLabel: 'File Name'
						, name: 'name'
						, allowBlank:false
						, xtype:'textfield'
						, width: 200
					}
					, {
						fieldLabel: 'Path'
						, name: 'path'
						, value: 'system/modules/'
						, allowBlank:false
						, xtype:'textfield'
						, width: 200
					}
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, mode: 'local'
						, store: storeFormQOFileLUFileType
						, displayField: 'value'
						, valueField: 'value'
						, hiddenName: 'type'
						, fieldLabel: 'File Type'
						, width: 75
					})
				]
				, tbar: [
					{
						text: 'Save'
						, type: 'submit'
						, handler: function () {
							formQOFile.getForm().submit({
								waitMsg: 'Saving...'
								, success: function (response,options) {
									formQOFile.getForm().reset();
								}
								, failure: function (response,options) {
									Ext.MessageBox.alert('Error','Unable to save record');
								}
							});
						}
					}
					, {
						text: 'Reset'
						, type: 'reset'
						, handler: function () {
							formQOFile.getForm().reset();
						}
					}
				]
			});

			// End Form Definitions
			//===========================================================================================

			//===========================================================================================
			// Help Tab Definition
			
			panelHelp = {
				title: 'Help'
				, frame: false
				, border : false
				, iconCls: 'qoa-help'
				, closable: true
				, autoScroll: true
				, html:
					  '<h1>New vs Edit</h1>'
					+ '<p>New allows for the creation of new records in the qWikiOffice database.'
					+ '  Filling in all fields and clicking on the <b>SAVE</b> button will save the record'
					+ ', clicking on the <b>RESET</b> will clear the form to its default values.'
					+ '  It should be assumed that all fields are mandetory unless otherwise specified.'
					+ '<p>Edit allows for the updation and deletion of new records.'
					+ '  To edit a field double-click on the field of choice, change the value.'
					+ '  Changing focus off the field will automatically save the altered value.'
					+ '  One or more rows may be selected at a time and the <b>DELETE</b> button selected and they will be removed from the database.'
					+ '  Pressing the <b>REFRESH</b> button will refresh the data from the database.'
					+ '</p>'
					+ '<h1>New Members</h1>'
					+ '<p>Allows the creation of new members with in the qWikiOffice environment.  Specifying a group will designate the user to the group.'
					+ '</p>'
					+ '<h1>New Group</h1>'
					+ '<p>Creates a new group members may become part of.'
					+ '</p>'
					+ '<h1>Edit Group</h1>'
					+ '<p>Edits existing groups members may be assigned to.  Please note that the <b>ADMINSTRATOR</b> group should never me modified.'
					+ '</p>'
					+ '<h1>New Module</h1>'
					+ '<p>Registers a new module for use in qWikiOffice.'
					+ '</p>'
					+ '<p>Checking the field <b>Has DB install script</b> will expect a file called <i>\'db_install.sql\'</i> to exist in the module directory.  Likewise there should also be a file <i>\'db_uninstall.sql\'</i> that will undo the db changes.'
					+ 'This file will be executed against the database.'
					+ '</p>'
					+ '<p>There are three additional sub fields.'
					+ '</p>'
					+ '<p>Files - defines the three files that may be associated to a module.  Files will be assumed to reside in the module path defined.'
					+ '<br />Base Group - The initial member group a module may be assigned.'
					+ '<br />Base Launcher - The launcher that the module should be assigned so a member may activate it'
					+ '</p>'
					+ '<h1>Edit Module</h1>'
					+ '<p>Allows the base information of a module to be edited.  This will not allow the editing of files, groups or launchers associated to modules.'
					+ '</p>'
					+ '<h1>New Module File</h1>'
					+ '<p>Defines a new module file, the file is assumed to reside in the path defined by the module.'
					+ '    Please note that while more than one file of any type may be created, only one <b>JAVASCRIPT</b> and <b>PHP</b> should ever be associated with a module.'
					+ '</p>'
					+ '<h1>Module Launchers</h1>'
					+ '<p>The assigning of modules to launchers has some specific requirements.'
					+ '  If assigning a module to any of the following then the user should be set to <b>ALL MEMBERS</b> and group <b>ALL GROUPS</b>.'
					+ '</p>'
					+ '<p>- startmenu'
					+ '<br />- startmenutool'
					+ '<br />- contextmenu'
					+ '</p>'
					+ '<p>All other launcher definitions should be defined with a specific member and group.'
					+ '<h1>Plugin Files</h1>'
					+ '<p>Plugin files are files that are loaded at the time of desktop creation.  It can be useful to add files in this section for new generic style sheets,'
					+ 'or extjs plugins that can be used my modules without including them in module definitions.'
					+ '</p>'
					+ '<h1>Sessions</h1>'
					+ '<p>Lists the sessions logged into qWikiOffice.  Deleting a session will not log a session out automatically.'
					+ '  However it will cause the session to prompt for login again if the user refreshes their page.'
					+ '<h1>Permissions</h1>'
					+ '<p>Permissions are separated into two broad sections.  Members and groups, and groups and modules.'
					+ '  A member is assigned to one or more groups, and a group is assigned one or more modules.'
					+ '  This combination derives all modules a member is permited to access, and what launcher that they are displayed from.'
			};

			// End Help Definition
			//===========================================================================================

			//===========================================================================================
			// Task Definitions
			
			var taskQOSessionsReload = {
				run: function() {
					storeGridQOSessions.reload();
				}
				, interval: 3000000 //5 Minutes
//				, interval: 5000 //5 Seconds
			};

			var taskRunner = new Ext.util.TaskRunner();

			// End Task Definitions
			//===========================================================================================

			//===========================================================================================
			// Tree Definitions

			// Members list for tree panel
			
			var navTree = new Ext.tree.TreePanel ({
				loader: new Ext.tree.TreeLoader ()
//				loader: new Ext.tree.TreeLoader ({
//					url: this.app.connection
//					, baseParams: {
//						task: 'readTreeData'
//						, moduleId: f_scope.moduleId
//						, fileName: f_scope.phpFile
//					}
//				})
				, frame: false
				, border: false
			});
			// add listeners
			navTree.on ('click', function (node, e) {
				// We load the store data just before the tab is added.  This reduces
				// Module load time as it does not need to got to the database until required
				// Listener placed on activate so that any time the tab is activated the stores 
				// are refreshed.  This is to ensure data is up to date at time of viewing.
				switch (node.id) {
					case "MembersGridTab":
						storeGridQOMembers.load ();
						new_tab = myTabs.add(gridQOMembers);
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeGridQOMembers.reload();
							}
							, this
						);
						break;
					case "GroupsGridTab":
						storeGridQOGroups.load();
						new_tab = myTabs.add(gridQOGroups);
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeGridQOGroups.reload();
							}
							, this
						);
						break;
					case "ModulesGridTab":
						storeGridQOModules.load();
						new_tab = myTabs.add(gridQOModules)
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeGridQOModules.reload();
							}
							, this
						);
						break;
					case "SessionsGridTab":
						storeGridQOSessionsLUMemberNames.load();
						storeGridQOSessions.load();
						new_tab = myTabs.add(gridQOSessions)
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeGridQOSessionsLUMemberNames.reload();
								storeGridQOSessions.reload();
							}
							, this
						);
						taskRunner.start (taskQOSessionsReload);
						break;
					case "AuditTrailGridTab":
						storeGridQOAdminAuditLUMemberNames.load();
						storeGridQOAdminAudit.load();
						new_tab = myTabs.add(gridQOAdminAudit)
						new_tab.show();
						new_tab.addListener (
							'activate'
							, function () {
								storeGridQOAdminAuditLUMemberNames.reload();
								storeGridQOAdminAudit.reload();
							}
							, this
						);
						break;
					default:
						break;
				}		
			});
			navTree.on ('contextmenu'
				, function (node, e) {
					e.stopEvent();
					var xy = e.getXY();
					navContMenu.showAt(xy);
				}
			);
			// define Tree root
			var navRoot = new Ext.tree.TreeNode ({
				text: '<i>Root</i>'
				, draggable: false
				, id: 'source'
			});
			navTree.setRootNode (navRoot);
			// Static content for Navigation Tree
			var navContent = [
				{
					"text" : "Members"
					, "id" : "MembersGridTab"
					, "leaf" : true
				}
				, {
					"text" : "Groups"
					, "id" : "GroupsGridTab"
					, "leaf" : true
				}
				, {
					"text" : "Modules"
					, "id" : "ModulesGridTab"
					, "leaf" : true
				}
				, {
					"text" : "Sessions"
					, "id" : "SessionsGridTab"
					, "leaf" : true
				}
				, {
					"text" : "Audit Trail"
					, "id" : "AuditTrailGridTab"
					, "leaf" : true
				}
			];
			// populate the tree
			for(var i = 0, len = navContent.length; i < len; i++) {
				navRoot.appendChild(navTree.getLoader().createNode(navContent[i]));
			}
			
			// End Tree Definitions
			//===========================================================================================

			//===========================================================================================
			// Panel Definitions

			// Tab Panel for all forms and grids
			var myTabs = new Ext.TabPanel ({
				activeTab: 0
				, xtype: 'tabpanel'
				, colapsable: false
				, deferredRender: false
				, layoutOnTabChange: true
				, enableTabScroll: true
				, autoDestroy: false
				, items: [
					{
						title: 'Home'
						, html: 'Welcome to qWikiOffice Admin Console'
					}
				]
			});
			// add listeners
			myTabs.on('remove', function(tp, c) {
				c.hide();
				c.purgeListeners(); // purge listeners on hide hasListener ('activate') does not appear to work
				if (c.getId() == 'qo-admin-console-sessions') {
					taskRunner.stop(taskQOSessionsReload);
				}
			});
			
			// Region Panels for the border layout
			// all other panels are childs of the region panels, this is to 
			// ensure we do not lose scope of tabs when adding and removing them
			// it has been observed that this may also be best practice in the
			// ExtJS community

			// Center Panel
			var regionPanelCenter = new Ext.Panel ({
				id: 'region-panel-center'
				, layout: 'fit'
				, region: 'center'
				, items: myTabs
			});
			
			// West Panel
			var regionPanelWest = new Ext.Panel ({
				id: 'region-panel-west'
				, layout: 'fit'
				, region: 'west'
				, width: 150
				, items: navTree
			});
			
			// Final window creation
			win = desktop.createWindow({
                id: this.moduleWindowID
                , title: this.moduleTitle
                , iconCls: this.moduleIconClass
				, shortcutIconCls: this.moduleShortcutClass
                , taskbuttonTooltip: this.moduleToolTip
                , width: 850
                , height: 550
                , shim: false
                , animCollapse: false
                , constrainHeader: true
				, layout: 'border'
                , items: [ 
					regionPanelWest
					, regionPanelCenter
				]
				, tbar: [
					menuWindowFile
					, menuWindowNew
					, menuWindowEdit
					, menuWindowPermissions
					, '->'
					, menuWindowHelp
				]
            });
		}
		win.show();
		navRoot.expand();
	}
});

