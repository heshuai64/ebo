QoDesk.QoAdminMyGroups = Ext.extend(Ext.app.Module, {

	  moduleType 			: 'system'
	, moduleId 				: 'qo-admin-my-groups'
	, phpFile 				: 'qo-admin-my-groups.php'
	, moduleAuthor			: 'Paul Simmons'
	, moduleVersion			: '2.0.0'
	, moduleWindowID		: 'qo-admin-my-groups-win'
	, moduleTitle			: 'My Groups'
	, moduleToolTip			: 'My Groups'
	, moduleIconClass		: 'qoa-group'
	, moduleShortcutClass 	: 'qoa-group-shortcut'
	//===========================================================================================
	// 2.0.0 Initial Creation
	//===========================================================================================
	
	, init : function() {
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
			
			// Store Definitions
			
			// for Grid data
			function storeMyGroups (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "read"
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
			storeGridMyGroups = storeMyGroups (this);
			storeGridMyGroups.loadData;

			function storeMemberNames (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "readMemberNames"
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
			storeGridMyGroupsLUMemberNames = storeMemberNames (this);
			storeGridMyGroupsLUMemberNames.loadData;
			storeGridMyGroupsLUMemberNames.load();
			storeFormMyGroupLUMemberNames = storeMemberNames (this);
			storeFormMyGroupLUMemberNames.loadData;
			storeFormMyGroupLUMemberNames.load();

			function storeGroupNames (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.Store ({
					proxy: new Ext.data.HttpProxy ({ 
						url: f_scope.app.connection
						, scope: f_scope
					})
					, baseParams: {
						task: "readGroupNames"
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
			storeGridMyGroupsLUGroupNames = storeGroupNames (this);
			storeGridMyGroupsLUGroupNames.loadData;
			storeGridMyGroupsLUGroupNames.load();
			storeFormMyGroupLUGroupNames = storeGroupNames (this);
			storeFormMyGroupLUGroupNames.loadData;
			storeFormMyGroupLUGroupNames.load();

			function storeTrueFalse (scope) {
				f_scope = scope || this.obj;
				return new Ext.data.SimpleStore({
					fields: [ 'value' ]
					, data: [ [ 'true' ], [ 'false' ] ]
				});
			};
			storeGridMyGroupsLUActive = storeTrueFalse (this);
			storeGridMyGroupsLUAdmin = storeTrueFalse (this);
			storeFormMyGroupLUActive = storeTrueFalse (this);
			storeFormMyGroupLUAdmin = storeTrueFalse (this);

			// End Store Definitions

			// Define column Model
			var cmGridMyGroups = new Ext.grid.ColumnModel([
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
						, store: storeGridMyGroupsLUMemberNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
					})
					, renderer: function(data) {
						record = storeGridMyGroupsLUMemberNames.getById(data);
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
						, store: storeGridMyGroupsLUGroupNames
						, displayField: 'DisplayField'
						, valueField: 'KeyField'
					})
					, renderer: function(data) {
						record = storeGridMyGroupsLUGroupNames.getById(data);
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
						, store: storeGridMyGroupsLUActive
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
						, store: storeGridMyGroupsLUAdmin
						, displayField: 'value'
						, valueField: 'value'
					})
				}
			]);
			
			// by default columns are sortable
			cmGridMyGroups.defaultSortable = true;
	
			function handleDeleteMyGroups() {
				//returns record objects for selected rows (all info for row)
				var selectedRows = gridMyGroups.selModel.selections.items;
				
				//returns array of selected rows ids only
				var selectedKeys = gridMyGroups.selModel.selections.keys; 

				//note we already did an if(selectedKeys) to get here

				//encode array into json
				var encoded_keys = Ext.encode(selectedKeys);
				//submit to server
				Ext.Ajax.request({
					//specify options (note success/failure below that receives these same options)
					waitMsg: 'Saving changes...'
					, url: this.app.connection
					, params: { 
						task: "delete" //pass task to do to the server script
						, moduleId: this.moduleId
						, fileName: this.phpFile
						, deleteKeys: encoded_keys
						, key: 'id'//pass to server same 'id' that the reader used
					}
					, callback: function (options, success, response) {
						if (success) { //success will be true if the request succeeded
							Ext.MessageBox.alert('OK',response.responseText);//you won't see this alert if the next one pops up fast
							var json = Ext.util.JSON.decode(response.responseText);
							Ext.MessageBox.alert('OK',json.del_count + ' record(s) deleted.');
						} else {
							Ext.MessageBox.alert('Sorry, please try again. [Q304]',response.responseText);
						}
					}
					, failure:function(response,options){
						Ext.MessageBox.alert('Warning','Oops...');
					}                                      
					, success:function(response,options){
						storeGridMyGroups.reload();
					}
					, scope: this
				});
			};
	
			function saveEditMyGroups (oGrid_Event) {
            
				//submit to server
				Ext.Ajax.request({
					waitMsg: 'Saving changes...'
					, url: this.app.connection
					, params: { 
						task: "edit" //pass task to do to the server script
						, moduleId: this.moduleId
						, fileName: this.phpFile
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
						storeGridMyGroups.commitChanges();
					}      
					, scope: this
				});
			};
		
			// Grid definition
			var gridMyGroups = new Ext.grid.EditorGridPanel({
				store: storeGridMyGroups
				, cm: cmGridMyGroups
				, height: 150
				, title:'Edit My Groups'
				, frame: false
				, border: false
				, clicksToEdit: 2
				, selModel: new Ext.grid.RowSelectionModel({singleSelect:false})
				, tbar: [
					{
						text: 'Delete'
						, tooltip: 'Select row to delete'
						, handler: handleDeleteMyGroups
						, scope: this
					}
					, {
						text: 'Refresh'
						, tooltip: 'Refresh grid'
						, handler: function () {
							storeGridMyGroups.reload()
						}
					}
				]
			});
			gridMyGroups.addListener('afteredit', saveEditMyGroups, this);

			// trigger the data store load
			storeGridMyGroups.load();

			var formMyGroup = new Ext.FormPanel ({
				title: 'New Group Member'
				, labelWidth: 85
				, url: this.app.connection
				, frame: false
				, border: false
				, bodyStyle:'padding:5px 5px 0'
				, width: 250
				, items: [
					new Ext.form.Hidden ({ name: 'task', value: 'new' })
					, new Ext.form.Hidden ({ name: 'moduleId', value: this.moduleId })
					, new Ext.form.Hidden ({ name: 'fileName', value: this.phpFile })
					, new Ext.form.ComboBox({
						typeAhead: false
						, triggerAction: 'all'
						, lazyRender: false
						, store: storeFormMyGroupLUMemberNames
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
						, store: storeFormMyGroupLUGroupNames
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
						, store: storeFormMyGroupLUActive
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
						, store: storeFormMyGroupLUAdmin
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
							formMyGroup.getForm().submit({
								waitMsg: 'Saving...'
								, success: function (response,options) {
									formMyGroup.getForm().reset();
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
							formMyGroup.getForm().reset();
						}
					}
				]
				});

			var tabMyGroups = new Ext.TabPanel ({
				activeTab: 0
				, frame: true
				, layoutOnTabChange: true
				, items: [ 
					formMyGroup
				]
				, tbar: [
					{
						text: 'Exit'
						, handler: function () {
							win.close();
						}
					}
					, '->'
					, {
						text: 'About'
						, handler: function () {
							Ext.Msg.show ({
							
								title: 'About'
								, msg: '<b>QO Admin</b><br>' + f_scope.moduleTitle + "<br>Version: "+ f_scope.moduleVersion + "<br>Author: " + f_scope.moduleAuthor
								, button: Ext.Msg.OK
							});
						}
					}
				]
			});
			new_tab = tabMyGroups.add(gridMyGroups);
			new_tab.show();
			new_tab.addListener (
				'activate'
				, function () {
					storeGridMyGroups.reload();
				}
				, this
			);

			win = desktop.createWindow({
                id: this.moduleWindowID
                , title: this.moduleTitle
                , width:435
                , height:400
                , iconCls: this.moduleIconClass
				, shortcutIconCls: this.moduleShortcutClass
                , shim:false
                , animCollapse:false
                , constrainHeader:true
				, layout: 'fit'
                , items: tabMyGroups
                , taskbuttonTooltip: this.moduleToolTip
            });
		}
		win.show();
	}
});

