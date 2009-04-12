
QoDesk.Shipments = Ext.extend(Ext.app.Module, {
	moduleType : 'shipments',
        moduleId : 'qo-shipments',
        menuPath : 'StartMenu',
	launcher : {
            iconCls: 'shipments-icon',
            shortcutIconCls: 'shipments-shortcut',
            text: 'Shipments Management',
            tooltip: '<b>Shipments Management</b><br/>Search, Updage, Delete Shipments'
        }
});