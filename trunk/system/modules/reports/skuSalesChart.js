Ext.onReady(function(){
    Ext.BLANK_IMAGE_URL = "../Ext/2.2/resources/images/default/s.gif";
    var salesReportStore = new Ext.data.JsonStore({
        root: 'records',
        totalProperty: 'totalCount',
        idProperty: 'sku_id',
        autoLoad:true,
        fields: ['sku_id'],
        url:'reports.php?type=skuSalesChart&week=' + week + '&sellerId=' + sellerId
    })
    
    
    google.load('visualization', '1', {packages:['imagelinechart']});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Day');
        data.addColumn('number', 'Last Week');
        data.addColumn('number', 'This Week');
        data.addRows(7);
        
        data.setValue(0, 0, 'Mon');
        data.setValue(0, 1, record.data['3_Mon_quantity']);
        data.setValue(0, 2, record.data['4_Mon_quantity']);
        
        data.setValue(1, 0, 'Tue');
        data.setValue(1, 1, record.data['3_Tue_quantity']);
        data.setValue(1, 2, record.data['4_Tue_quantity']);
        
        data.setValue(2, 0, 'Wed');
        data.setValue(2, 1, record.data['3_Wed_quantity']);
        data.setValue(2, 2, record.data['4_Wed_quantity']);
        
        data.setValue(3, 0, 'Thu');
        data.setValue(3, 1, record.data['3_Thu_quantity']);
        data.setValue(3, 2, record.data['4_Thu_quantity']);
        
        data.setValue(4, 0, 'Fri');
        data.setValue(4, 1, record.data['3_Fri_quantity']);
        data.setValue(4, 2, record.data['4_Fri_quantity']);
        
        data.setValue(5, 0, 'Sat');
        data.setValue(5, 1, record.data['3_Sat_quantity']);
        data.setValue(5, 2, record.data['4_Sat_quantity']);
        
        data.setValue(6, 0, 'Sun');
        data.setValue(6, 1, record.data['3_Sun_quantity']);
        data.setValue(6, 2, record.data['4_Sun_quantity']);


        var chart = new google.visualization.ImageLineChart(document.getElementById('chart_div'));
        chart.draw(data, {width: 400, height: 240, min: 0});
    }
})          