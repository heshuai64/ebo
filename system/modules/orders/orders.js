
QoDesk.Orders = Ext.extend(Ext.app.Module, {
	moduleType : 'orders',
        moduleId : 'qo-orders',
        menuPath : 'StartMenu',
	launcher : {
            iconCls: 'orders-icon',
            shortcutIconCls: 'orders-shortcut',
            text: 'Orders Management',
            tooltip: '<b>Orders Management</b><br/>Search、Update、Delete Orders'
        }
});