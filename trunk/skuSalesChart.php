<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="PRAGMA" content="NO-CACHE">
        <meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
        <meta http-equiv="EXPIRES" content="-1">
        
        <title>Sku Sales Chart</title>
        
        <!-- EXT JS LIBRARY -->
        <link rel="stylesheet" type="text/css" href="../ext-3.0-rc2/resources/css/ext-all.css" />
        <script src="../ext-3.0-rc2/adapter/ext/ext-base.js"></script>
        <script src="../ext-3.0-rc2/ext-all.js"></script>
        <script src="system/modules/reports/sku-sales-chart.js"></script>
        <script type="text/javascript">
                var skuId = "<?=$_GET['skuId']?>";
                var week = "<?=$_GET['week']?>";
                var sellerId = "<?=$_GET['sellerId']?>";
        </script>
</head>

<body>
    <div id='chart_div'></div>
</body>
</html>