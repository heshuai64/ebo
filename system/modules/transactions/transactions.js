
QoDesk.Transactions = Ext.extend(Ext.app.Module, {
	moduleType : 'transactions',
        moduleId : 'qo-transactions',
        menuPath : 'StartMenu',
	launcher : {
            iconCls: 'transactions-icon',
            shortcutIconCls: 'transactions-shortcut',
            text: '付款管理',
            tooltip: '<b>付款管理,</b><br/>查询、修改、删除付款'
        }
});