
QoDesk.Transactions = Ext.extend(Ext.app.Module, {
	moduleType : 'transactions',
        moduleId : 'qo-transactions',
        menuPath : 'StartMenu',
	launcher : {
            iconCls: 'transactions-icon',
            shortcutIconCls: 'transactions-shortcut',
            text: 'Transactions Management',
            tooltip: '<b>Transactions Management,</b><br/>Search, Update, Delete Transactions'
        }
});