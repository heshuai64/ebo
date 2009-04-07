/* This code defines the module and will be loaded at start up.
 * 
 * When the user selects to open this module, the override code will
 * be loaded to provide the functionality.
 * 
 * Allows for 'Module on Demand'.
 */

QoDesk.Manage = Ext.extend(Ext.app.Module, {
	moduleType : 'system/manage',
        moduleId : 'qo-manage',
        menuPath : 'StartMenu',
	launcher : {
            iconCls: 'manage-icon',
            shortcutIconCls: 'manage-shortcut',
            text: '系统管理',
            tooltip: '<b>系统管理</b><br/>用户、组、权限管理'
        }
});