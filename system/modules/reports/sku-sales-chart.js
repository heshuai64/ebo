Ext.onReady(function(){
    Ext.BLANK_IMAGE_URL = "../ext-3.0-rc2/resources/images/default/s.gif";
    var salesReportStore = new Ext.data.JsonStore({
        autoLoad:true,
        fields: ['date', '1_name', '1_quantity', '1_growth', '2_name', '2_quantity'],
        url:'reports.php?type=skuSalesChart&skuId='+skuId+'&week=' + week + '&sellerId=' + sellerId
    })
    
    
    //salesReportStore.load();
    
    new Ext.Panel({
        iconCls:'chart',
        title: skuId + ' Sales Chart',
        frame:true,
        renderTo: 'chart_div',
        width:500,
        height:300,
        layout:'fit',

        items: {
            xtype: 'columnchart',
            store: salesReportStore,
            url:'../ext-3.0-rc2/resources/charts.swf',
            xField: 'date',
            yAxis: new Ext.chart.NumericAxis({
                displayName: 'Visits',
                labelRenderer : Ext.util.Format.numberRenderer('0,0')
            }),
            chartStyle: {
                padding: 10,
                animationEnabled: true,
                font: {
                    name: 'Tahoma',
                    color: 0x444444,
                    size: 11
                },
                dataTip: {
                    padding: 5,
                    border: {
                        color: 0x99bbe8,
                        size:1
                    },
                    background: {
                        color: 0xDAE7F6,
                        alpha: .9
                    },
                    font: {
                        name: 'Tahoma',
                        color: 0x15428B,
                        size: 10,
                        bold: true
                    }
                },
                xAxis: {
                    color: 0x69aBc8,
                    majorTicks: {color: 0x69aBc8, length: 4},
                    minorTicks: {color: 0x69aBc8, length: 2},
                    majorGridLines: {size: 1, color: 0xeeeeee}
                },
                yAxis: {
                    color: 0x69aBc8,
                    majorTicks: {color: 0x69aBc8, length: 4},
                    minorTicks: {color: 0x69aBc8, length: 2},
                    majorGridLines: {size: 1, color: 0xdfe8f6}
                }
            },
            series: [{
                type: 'line',
                displayName: 'This Week',
                yField: '1_quantity',
                style: {
                    color: '99CC00'
                }
            },{
                type:'line',
                displayName: 'Last Week',
                yField: '2_quantity',
                style: {
                    color: 'FF0000'
                }
            }]
        }
    });
})