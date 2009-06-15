Ext.onReady(function(){
    var salesReportStore = new Ext.data.JsonStore({
        root: 'records',
        totalProperty: 'totalCount',
        idProperty: 'sku_id',
        autoLoad:true,
        fields: ['item_title', 'sku_id', '1_Mon_quantity', '1_Tue_quantity', '1_Wed_quantity', '1_Thu_quantity', '1_Fri_quantity', '1_Sat_quantity', '1_Sun_quantity', '1_total_num', '1_growth_rate', '2_Mon_quantity', '2_Tue_quantity', '2_Wed_quantity', '2_Thu_quantity', '2_Fri_quantity', '2_Sat_quantity', '2_Sun_quantity', '2_total_num', '2_growth_rate', '3_Mon_quantity', '3_Tue_quantity', '3_Wed_quantity', '3_Thu_quantity', '3_Fri_quantity', '3_Sat_quantity', '3_Sun_quantity', '3_total_num', '3_growth_rate', '4_Mon_quantity', '4_Tue_quantity', '4_Wed_quantity', '4_Thu_quantity', '4_Fri_quantity', '4_Sat_quantity', '4_Sun_quantity', '4_total_num', '4_growth_rate', '5_Mon_quantity', '5_Tue_quantity', '5_Wed_quantity', '5_Thu_quantity', '5_Fri_quantity', '5_Sat_quantity', '5_Sun_quantity', '5_total_num', '5_growth_rate', '6_Mon_quantity', '6_Tue_quantity', '6_Wed_quantity', '6_Thu_quantity', '6_Fri_quantity', '6_Sat_quantity', '6_Sun_quantity', '6_total_num'],
        url:'reports.php?type=salesReport&sellerId=' + sellerId
    });
    
    var renderStar = function(){
        return "<font color='red'>*</font>";
    }
    
    var renderVerticalLine = function(){
        return "<font color='blue'>|</font>";
    }
    
    var renderGrowthRate1 = function(v, m, r){
        if(Ext.isEmpty(r.data['0_total_num'])){
            return String.format('<font color="green">{0}%</font>', r.data['1_total_num'] * 100);
        }
        
        if(Ext.isEmpty(r.data['1_total_num'])){
            return String.format('<font color="red">-{0}%</font>', r.data['0_total_num'] * 100);
        }
        
        var grow_rate = (((r.data['1_total_num'] - r.data['0_total_num']) / r.data['0_total_num']) * 100).toFixed(2);
        if(grow_rate > 0){
            return String.format('<font color="green">{0}%</font>', grow_rate);
        }else{
            return String.format('<font color="red">{0}%</font>', grow_rate);
        }
    }
    
    var renderGrowthRate2 = function(v, m, r){
        if(Ext.isEmpty(r.data['1_total_num'])){
            return String.format('<font color="green">{0}%</font>', r.data['2_total_num'] * 100);
        }
        
        if(Ext.isEmpty(r.data['2_total_num'])){
            return String.format('<font color="red">-{0}%</font>', r.data['1_total_num'] * 100);
        }
        
        var grow_rate = (((r.data['2_total_num'] - r.data['1_total_num']) / r.data['1_total_num']) * 100).toFixed(2);
        if(grow_rate > 0){
            return String.format('<font color="green">{0}%</font>', grow_rate);
        }else{
            return String.format('<font color="red">{0}%</font>', grow_rate);
        }
    }
    
    var renderGrowthRate3 = function(v, m, r){
        if(Ext.isEmpty(r.data['2_total_num'])){
            return String.format('<font color="green">{0}%</font>', r.data['3_total_num'] * 100);
        }
        
        if(Ext.isEmpty(r.data['3_total_num'])){
            return String.format('<font color="red">-{0}%</font>', r.data['2_total_num'] * 100);
        }
        
        var grow_rate = (((r.data['3_total_num'] - r.data['2_total_num']) / r.data['2_total_num']) * 100).toFixed(2);
        
        if(grow_rate > 0){
            return String.format('<font color="green">{0}%</font>', grow_rate);
        }else{
            return String.format('<font color="red">{0}%</font>', grow_rate);
        }
    }
    
    var renderGrowthRate4 = function(v, m, r){
        if(Ext.isEmpty(r.data['3_total_num'])){
            return String.format('<font color="green">{0}%</font>', r.data['4_total_num'] * 100);
        }
        
        if(Ext.isEmpty(r.data['4_total_num'])){
            return String.format('<font color="red">-{0}%</font>', r.data['3_total_num'] * 100);
        }
        
        var grow_rate = (((r.data['4_total_num'] - r.data['3_total_num']) / r.data['3_total_num']) * 100).toFixed(2);
        
        if(grow_rate > 0){
            return String.format('<font color="green">{0}%</font>', grow_rate);
        }else{
            return String.format('<font color="red">{0}%</font>', grow_rate);
        }
    }
    
    var renderGrowthRate5 = function(v, m, r){
        if(Ext.isEmpty(r.data['5_total_num'])){
            return String.format('<font color="green">{0}%</font>', r.data['6_total_num'] * 100);
        }
        
        if(Ext.isEmpty(r.data['6_total_num'])){
            return String.format('<font color="red">-{0}%</font>', r.data['5_total_num'] * 100);
        }
        
        var grow_rate = (((r.data['6_total_num'] - r.data['5_total_num']) / r.data['5_total_num']) * 100).toFixed(2);
        
        if(grow_rate > 0){
            return String.format('<font color="green">{0}%</font>', grow_rate);
        }else{
            return String.format('<font color="red">{0}%</font>', grow_rate);
        }
    }
    
    var renderGrowthRate = function(v, m, r){
        if(v > 0){
            return String.format('<font color="green">{0}%</font>', v);
        }else{
            return String.format('<font color="red">{0}%</font>', v);
        }
    }
    
    switch(week){
        case "1":
            var windowTitle = "1st Week Sales Report";
            var colModel = new Ext.grid.ColumnModel([
                //{header: "Title", width: 335, align: 'center', sortable: true, dataIndex: 'item_title'},
                {header: "SKU", width: 110, align: 'center', sortable: true, dataIndex: 'sku_id'},
                
                {header: "Mon", width: 45, align: 'center', sortable: true, dataIndex: '0_Mon_quantity'},
                {header: "Tue", width: 45, align: 'center', sortable: true, dataIndex: '0_Tue_quantity'},
                {header: "Wed", width: 45, align: 'center', sortable: true, dataIndex: '0_Wed_quantity'},
                {header: "Thu", width: 45, align: 'center', sortable: true, dataIndex: '0_Thu_quantity'},
                {header: "Fri", width: 45, align: 'center', sortable: true, dataIndex: '0_Fri_quantity'},
                {header: "Sat", width: 45, align: 'center', sortable: true, dataIndex: '0_Sat_quantity'},
                {header: "Sun", width: 45, align: 'center', sortable: true, dataIndex: '0_Sun_quantity'},
                {header: "Last Total", width: 70, align: 'center', sortable: true, dataIndex: '0_total_num'},
                
                {header: "|", width: 10, dataIndex: '0_total_num', renderer: renderVerticalLine},
                
                {header: "Mon", width: 45, align: 'center', sortable: true, dataIndex: '1_Mon_quantity'},
                {header: "Tue", width: 45, align: 'center', sortable: true, dataIndex: '1_Tue_quantity'},
                {header: "Wed", width: 45, align: 'center', sortable: true, dataIndex: '1_Wed_quantity'},
                {header: "Thu", width: 45, align: 'center', sortable: true, dataIndex: '1_Thu_quantity'},
                {header: "Fri", width: 45, align: 'center', sortable: true, dataIndex: '1_Fri_quantity'},
                {header: "Sat", width: 45, align: 'center', sortable: true, dataIndex: '1_Sat_quantity'},
                {header: "Sun", width: 45, align: 'center', sortable: true, dataIndex: '1_Sun_quantity'},
                {header: "This Total", width: 65, align: 'center', sortable: true, dataIndex: '1_total_num'},
                {header: "This Growth", width: 75, align: 'center', sortable: true, dataIndex: '1_growth_rate', renderer: renderGrowthRate}
            ]);
        break;
    
        case "2":
            var windowTitle = "2nd Week Sales Report";
            var colModel = new Ext.grid.ColumnModel([
                //{header: "Title", width: 335, align: 'center', sortable: true, dataIndex: 'item_title'},
                {header: "SKU", width: 110, align: 'center', sortable: true, dataIndex: 'sku_id'},
                
                {header: "Mon", width: 45, align: 'center', sortable: true, dataIndex: '1_Mon_quantity'},
                {header: "Tue", width: 45, align: 'center', sortable: true, dataIndex: '1_Tue_quantity'},
                {header: "Wed", width: 45, align: 'center', sortable: true, dataIndex: '1_Wed_quantity'},
                {header: "Thu", width: 45, align: 'center', sortable: true, dataIndex: '1_Thu_quantity'},
                {header: "Fri", width: 45, align: 'center', sortable: true, dataIndex: '1_Fri_quantity'},
                {header: "Sat", width: 45, align: 'center', sortable: true, dataIndex: '1_Sat_quantity'},
                {header: "Sun", width: 45, align: 'center', sortable: true, dataIndex: '1_Sun_quantity'},
                {header: "Last Total", width: 65, align: 'center', sortable: true, dataIndex: '1_total_num'},
                {header: "Last Growth", width: 70, align: 'center', sortable: true, dataIndex: '1_growth_rate', renderer: renderGrowthRate},
                
                {header: "|", width: 10, align: 'center', dataIndex: '1_total_num', renderer: renderVerticalLine},
                
                {header: "Mon", width: 45, align: 'center', sortable: true, dataIndex: '2_Mon_quantity'},
                {header: "Tue", width: 45, align: 'center', sortable: true, dataIndex: '2_Tue_quantity'},
                {header: "Wed", width: 45, align: 'center', sortable: true, dataIndex: '2_Wed_quantity'},
                {header: "Thu", width: 45, align: 'center', sortable: true, dataIndex: '2_Thu_quantity'},
                {header: "Fri", width: 45, align: 'center', sortable: true, dataIndex: '2_Fri_quantity'},
                {header: "Sat", width: 45, align: 'center', sortable: true, dataIndex: '2_Sat_quantity'},
                {header: "Sun", width: 45, align: 'center', sortable: true, dataIndex: '2_Sun_quantity'},
                {header: "This Total", width: 65, align: 'center', sortable: true, dataIndex: '2_total_num'},
                {header: "This Growth", width: 70, align: 'center', sortable: true, dataIndex: '2_growth_rate', renderer: renderGrowthRate}
            ]);
        break;
    
        case "3":
            var windowTitle = "3rd Week Sales Report";
            var colModel = new Ext.grid.ColumnModel([
                //{header: "Title", width: 335, align: 'center', sortable: true, dataIndex: 'item_title'},
                {header: "SKU", width: 110, align: 'center', sortable: true, dataIndex: 'sku_id'},
                
                {header: "Mon", width: 45, align: 'center', sortable: true, dataIndex: '2_Mon_quantity'},
                {header: "Tue", width: 45, align: 'center', sortable: true, dataIndex: '2_Tue_quantity'},
                {header: "Wed", width: 45, align: 'center', sortable: true, dataIndex: '2_Wed_quantity'},
                {header: "Thu", width: 45, align: 'center', sortable: true, dataIndex: '2_Thu_quantity'},
                {header: "Fri", width: 45, align: 'center', sortable: true, dataIndex: '2_Fri_quantity'},
                {header: "Sat", width: 45, align: 'center', sortable: true, dataIndex: '2_Sat_quantity'},
                {header: "Sun", width: 45, align: 'center', sortable: true, dataIndex: '2_Sun_quantity'},
                {header: "Last Total", width: 65, align: 'center', sortable: true, dataIndex: '2_total_num'},
                {header: "Last Growth", width: 70, align: 'center', sortable: true, dataIndex: '2_growth_rate', renderer: renderGrowthRate},
                
                {header: "|", width: 10, align: 'center', dataIndex: '2_total_num', renderer: renderVerticalLine},
                
                {header: "Mon", width: 45, align: 'center', sortable: true, dataIndex: '3_Mon_quantity'},
                {header: "Tue", width: 45, align: 'center', sortable: true, dataIndex: '3_Tue_quantity'},
                {header: "Wed", width: 45, align: 'center', sortable: true, dataIndex: '3_Wed_quantity'},
                {header: "Thu", width: 45, align: 'center', sortable: true, dataIndex: '3_Thu_quantity'},
                {header: "Fri", width: 45, align: 'center', sortable: true, dataIndex: '3_Fri_quantity'},
                {header: "Sat", width: 45, align: 'center', sortable: true, dataIndex: '3_Sat_quantity'},
                {header: "Sun", width: 45, align: 'center', sortable: true, dataIndex: '3_Sun_quantity'},
                {header: "This Total", width: 65, align: 'center', sortable: true, dataIndex: '3_total_num'},
                {header: "This Growth", width: 70, align: 'center', sortable: true, dataIndex: '3_growth_rate', renderer: renderGrowthRate}
            ]);
        break;
    
        case "4":
            var windowTitle = "4th Week Sales Report";
            var colModel = new Ext.grid.ColumnModel([
                //{header: "Title", width: 335, align: 'center', sortable: true, dataIndex: 'item_title'},
                {header: "SKU", width: 110, align: 'center', sortable: true, dataIndex: 'sku_id'},
                
                {header: "Mon", width: 45, align: 'center', sortable: true, dataIndex: '3_Mon_quantity'},
                {header: "Tue", width: 45, align: 'center', sortable: true, dataIndex: '3_Tue_quantity'},
                {header: "Wed", width: 45, align: 'center', sortable: true, dataIndex: '3_Wed_quantity'},
                {header: "Thu", width: 45, align: 'center', sortable: true, dataIndex: '3_Thu_quantity'},
                {header: "Fri", width: 45, align: 'center', sortable: true, dataIndex: '3_Fri_quantity'},
                {header: "Sat", width: 45, align: 'center', sortable: true, dataIndex: '3_Sat_quantity'},
                {header: "Sun", width: 45, align: 'center', sortable: true, dataIndex: '3_Sun_quantity'},
                {header: "Last Total", width: 65, align: 'center', sortable: true, dataIndex: '3_total_num'},
                {header: "Last Growth", width: 70, align: 'center', sortable: true, dataIndex: '3_growth_rate', renderer: renderGrowthRate},
                
                {header: "|", width: 10, align: 'center', dataIndex: '3_total_num', renderer: renderVerticalLine},
                
                {header: "Mon", width: 45, align: 'center', sortable: true, dataIndex: '4_Mon_quantity'},
                {header: "Tue", width: 45, align: 'center', sortable: true, dataIndex: '4_Tue_quantity'},
                {header: "Wed", width: 45, align: 'center', sortable: true, dataIndex: '4_Wed_quantity'},
                {header: "Thu", width: 45, align: 'center', sortable: true, dataIndex: '4_Thu_quantity'},
                {header: "Fri", width: 45, align: 'center', sortable: true, dataIndex: '4_Fri_quantity'},
                {header: "Sat", width: 45, align: 'center', sortable: true, dataIndex: '4_Sat_quantity'},
                {header: "Sun", width: 45, align: 'center', sortable: true, dataIndex: '4_Sun_quantity'},
                {header: "This Total", width: 65, align: 'center', sortable: true, dataIndex: '4_total_num'},
                {header: "This Growth", width: 70, align: 'center', sortable: true, dataIndex: '4_growth_rate', renderer: renderGrowthRate}
            ]);
        break;
    
        case "5":
            var windowTitle = "Month";
            var colModel = new Ext.grid.ColumnModel([
                //{header: "Title", width: 335, align: 'center', sortable: true, dataIndex: 'item_title'},
                {header: "SKU", width: 110, align: 'center', sortable: true, dataIndex: 'sku_id'},
                
                {header: "Mon", width: 45, align: 'center', sortable: true, dataIndex: '5_Mon_quantity'},
                {header: "Tue", width: 45, align: 'center', sortable: true, dataIndex: '5_Tue_quantity'},
                {header: "Wed", width: 45, align: 'center', sortable: true, dataIndex: '5_Wed_quantity'},
                {header: "Thu", width: 45, align: 'center', sortable: true, dataIndex: '5_Thu_quantity'},
                {header: "Fri", width: 45, align: 'center', sortable: true, dataIndex: '5_Fri_quantity'},
                {header: "Sat", width: 45, align: 'center', sortable: true, dataIndex: '5_Sat_quantity'},
                {header: "Sun", width: 45, align: 'center', sortable: true, dataIndex: '5_Sun_quantity'},
                {header: "Last Month Total", width: 90, align: 'center', sortable: true, dataIndex: '5_total_num'},
                
                {header: "|", width: 10, align: 'center', dataIndex: '5_total_num', renderer: renderVerticalLine},
                
                {header: "Mon", width: 45, align: 'center', sortable: true, dataIndex: '6_Mon_quantity'},
                {header: "Tue", width: 45, align: 'center', sortable: true, dataIndex: '6_Tue_quantity'},
                {header: "Wed", width: 45, align: 'center', sortable: true, dataIndex: '6_Wed_quantity'},
                {header: "Thu", width: 45, align: 'center', sortable: true, dataIndex: '6_Thu_quantity'},
                {header: "Fri", width: 45, align: 'center', sortable: true, dataIndex: '6_Fri_quantity'},
                {header: "Sat", width: 45, align: 'center', sortable: true, dataIndex: '6_Sat_quantity'},
                {header: "Sun", width: 45, align: 'center', sortable: true, dataIndex: '6_Sun_quantity'},
                {header: "This Month Total", width: 90, align: 'center', sortable: true, dataIndex: '6_total_num'},
                {header: "This Month Growth", width: 100, align: 'center', sortable: true, dataIndex: '5_growth_rate', renderer: renderGrowthRate}
            ]);
        break;
    }
    
    var salesReportGrid = new Ext.grid.GridPanel({
        //id:'button-grid',
        store: salesReportStore,
        //autoHeight: true,
        width: 990,
        height: 600,
        frame:true,
        //autoScroll: true,
        selModel: new Ext.grid.RowSelectionModel({}),
        colModel: colModel
    })
    
    /*
    var salesReportGrid = new Ext.grid.GridPanel({
        //id:'button-grid',
        store: salesReportStore,
        //autoHeight: true,
        width: 980,
        height: 620,
        frame:true,
        //autoScroll: true,
        selModel: new Ext.grid.RowSelectionModel({}),
        columns:[
            {header: "Title", width: 380, align: 'center', sortable: true, dataIndex: 'item_title'},
            {header: "SKU", width: 150, align: 'center', sortable: true, dataIndex: 'sku_id'},
            
            {header: "1st Mon", width: 80, align: 'center', sortable: true, dataIndex: '1_Mon_quantity'},
            {header: "1st Tue", width: 80, align: 'center', sortable: true, dataIndex: '1_Tue_quantity'},
            {header: "1st Wed", width: 80, align: 'center', sortable: true, dataIndex: '1_Wed_quantity'},
            {header: "1st Thu", width: 80, align: 'center', sortable: true, dataIndex: '1_Thu_quantity'},
            {header: "1st Fri", width: 80, align: 'center', sortable: true, dataIndex: '1_Fri_quantity'},
            {header: "1st Sat", width: 80, align: 'center', sortable: true, dataIndex: '1_Sat_quantity'},
            {header: "1st Sun", width: 80, align: 'center', sortable: true, dataIndex: '1_Sun_quantity'},
            {header: "1st Week Total", width: 100, align: 'center', sortable: true, dataIndex: '1_total_num'},
            {header: "1st End", width: 50, align: 'center', sortable: true, dataIndex: 'sku_id', renderer: renderStar},
              
            {header: "2nd Mon", width: 80, align: 'center', sortable: true, dataIndex: '2_Mon_quantity'},
            {header: "2nd Tue", width: 80, align: 'center', sortable: true, dataIndex: '2_Tue_quantity'},
            {header: "2nd Wed", width: 80, align: 'center', sortable: true, dataIndex: '2_Wed_quantity'},
            {header: "2nd Thu", width: 80, align: 'center', sortable: true, dataIndex: '2_Thu_quantity'},
            {header: "2nd Fri", width: 80, align: 'center', sortable: true, dataIndex: '2_Fri_quantity'},
            {header: "2nd Sat", width: 80, align: 'center', sortable: true, dataIndex: '2_Sat_quantity'},
            {header: "2nd Sun", width: 80, align: 'center', sortable: true, dataIndex: '2_Sun_quantity'},
            {header: "2nd Week Total", width: 100, align: 'center', sortable: true, dataIndex: '2_total_num'},
            {header: "Growth Rate", width: 90, align: 'center', sortable: true, dataIndex: 'sku_id', renderer: renderGrowthRate2},
            {header: "2nd End", width: 50, align: 'center', sortable: true, dataIndex: 'sku_id', renderer: renderStar},
            
            {header: "3rd Mon", width: 80, align: 'center', sortable: true, dataIndex: '3_Mon_quantity'},
            {header: "3rd Tue", width: 80, align: 'center', sortable: true, dataIndex: '3_Tue_quantity'},
            {header: "3rd Wed", width: 80, align: 'center', sortable: true, dataIndex: '3_Wed_quantity'},
            {header: "3rd Thu", width: 80, align: 'center', sortable: true, dataIndex: '3_Thu_quantity'},
            {header: "3rd Fri", width: 80, align: 'center', sortable: true, dataIndex: '3_Fri_quantity'},
            {header: "3rd Sat", width: 80, align: 'center', sortable: true, dataIndex: '3_Sat_quantity'},
            {header: "3rd Sun", width: 80, align: 'center', sortable: true, dataIndex: '3_Sun_quantity'},
            {header: "3rd Week Total", width: 100, align: 'center', sortable: true, dataIndex: '3_total_num'},
            {header: "Growth Rate", width: 90, align: 'center', sortable: true, dataIndex: 'sku_id', renderer: renderGrowthRate3},
            {header: "3rd End", width: 50, align: 'center', sortable: true, dataIndex: 'sku_id', renderer: renderStar},
            
            {header: "4th Mon", width: 80, align: 'center', sortable: true, dataIndex: '4_Mon_quantity'},
            {header: "4th Tue", width: 80, align: 'center', sortable: true, dataIndex: '4_Tue_quantity'},
            {header: "4th Wed", width: 80, align: 'center', sortable: true, dataIndex: '4_Wed_quantity'},
            {header: "4th Thu", width: 80, align: 'center', sortable: true, dataIndex: '4_Thu_quantity'},
            {header: "4th Fri", width: 80, align: 'center', sortable: true, dataIndex: '4_Fri_quantity'},
            {header: "4th Sat", width: 80, align: 'center', sortable: true, dataIndex: '4_Sat_quantity'},
            {header: "4th Sun", width: 80, align: 'center', sortable: true, dataIndex: '4_Sun_quantity'},
            {header: "4th Week Total", width: 100, align: 'center', sortable: true, dataIndex: '4_total_num'},
            {header: "Growth Rate", width: 90, align: 'center', sortable: true, dataIndex: 'sku_id', renderer: renderGrowthRate4}
        ]
    })
    
    */
    //salesReportGrid.render();
    /*
    var p = Ext.Panel({
        autoScroll: true,
        title: "Sales Report",
        autoHeight: true,
        width: 600,
        html: 'test',
        items: salesReportGrid
    })
    
    p.render(document.body);
    */
    
    var viewport = new Ext.Viewport({
        layout:'border',
        items:[{
                xtype: 'panel',
                title: windowTitle,
                region:'center',
                autoScroll: true,
                //width: 800,
                items:salesReportGrid
        }]
    })
    
})