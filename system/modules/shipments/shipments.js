
QoDesk.Shipments = Ext.extend(Ext.app.Module, {
	moduleType : 'shipments',
        moduleId : 'qo-shipments',
        menuPath : 'StartMenu',
	launcher : {
            iconCls: 'shipments-icon',
            shortcutIconCls: 'shipments-shortcut',
            text: '货运管理',
            tooltip: '<b>货运管理,</b><br/>查询、修改、删除货运'
        }
});