
QoDesk.Orders = Ext.extend(Ext.app.Module, {
	moduleType : 'orders',
        moduleId : 'qo-orders',
        menuPath : 'StartMenu',
	launcher : {
            iconCls: 'orders-icon',
            shortcutIconCls: 'orders-shortcut',
            text: '订单管理',
            tooltip: '<b>订单管理</b><br/>查询、修改、删除订单'
        }
});