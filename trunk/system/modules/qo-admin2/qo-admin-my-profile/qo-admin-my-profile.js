QoDesk.QoAdminMyProfile = Ext.extend(Ext.app.Module, {

	  moduleType 			: 'system'
	, moduleId 				: 'qo-admin-my-profile'
	, phpFile 				: 'qo-admin-my-profile.php'
	, moduleAuthor			: 'Paul Simmons'
	, moduleVersion			: '2.0.0'
	, moduleWindowID		: 'qo-admin-my-profile-win'
	, moduleTitle			: 'My Profile'
	, moduleToolTip			: 'My Profile'
	, moduleIconClass		: 'qoa-member'
	, moduleShortcutClass	: 'qoa-member-shortcut'
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

			var menuWindowHelp = new Ext.Action ({
				text: "Help"
				, menu: [
					miAbout(this)
				]
			});

			var formMyProfile = new Ext.FormPanel ({
				labelWidth: 75
				, url: this.app.connection
				, frame: false
				, border: false
				, bodyStyle:'padding:5px 5px 0'
				, items: [
					new Ext.form.Hidden ({ name: 'task', value: 'save' })
					, new Ext.form.Hidden ({ name: 'moduleId', value: this.moduleId })
					, new Ext.form.Hidden ({ name: 'fileName', value: this.phpFile })
					, {
						fieldLabel: 'First Name'
						, name: 'first_name'
						, allowBlank:false
						, xtype:'textfield'
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
						, inputType: 'password'
						, renderer: function() {
							return '**********';
						}
						, width: 100
					}
				]
				, tbar: [
					{
						text: 'Exit'
						, handler: function () {
							win.close();
						}
					}
					, '-'
					, {
						text: 'Save'
						, type: 'submit'
						, handler: function () {
							formMyProfile.getForm().submit({
								waitMsg: 'Saving...'
								, failure: function (response,options) {
									Ext.MessageBox.alert('Error','Unable to save record');
								}
								, scope: this
							});
						}
					}
					, {
						text: 'Reset'
						, type: 'reset'
						, handler: function () {
							formMyProfile.getForm().load ({
								url: this.app.connection
								, params: {
									task: "read"
									, moduleId: this.moduleId
									, fileName: this.phpFile
								}
							});
						}
						, scope: this
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

			formMyProfile.getForm().load ({
				url: this.app.connection
				, params: {
					task: "read"
					, moduleId: this.moduleId
					, fileName: this.phpFile
				}
			});			

			win = desktop.createWindow({
                id: this.moduleWindowID
				, title: this.moduleTitle
				, width:360
				, height:200
				, iconCls: this.moduleIconClass
				, shortcutIconCls: this.moduleShortcutClass
                , shim:false
				, animCollapse:false
				, constrainHeader:true
				, layout: 'fit'
				, items: formMyProfile
				, taskbuttonTooltip: this.moduleToolTip
            });
		}
		win.show();
	}
});

