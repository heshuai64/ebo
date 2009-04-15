
QoDesk.Attention = Ext.extend(Ext.app.Module, {
	moduleType : 'attention',
        moduleId : 'qo-attention',
        menuPath : 'StartMenu',
	launcher : {
            iconCls: 'attention-icon',
            shortcutIconCls: 'attention-shortcut',
            text: 'Attention',
            tooltip: '<b>Attention</b><br/>System to remind'
        }
});