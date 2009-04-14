
QoDesk.Reports = Ext.extend(Ext.app.Module, {
	moduleType : 'reports',
        moduleId : 'qo-reports',
        menuPath : 'StartMenu',
	launcher : {
            iconCls: 'reports-icon',
            shortcutIconCls: 'reports-shortcut',
            text: 'Statistical Reports',
            tooltip: '<b>Statistical Reports</b><br/>A variety of statistical reports'
        }
});