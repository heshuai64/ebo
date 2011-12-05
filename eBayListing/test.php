<?php
$link = mysql_connect('localhost', 'root', '5333533');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
if (!mysql_select_db('ebaylisting')) {
    die('Could not select database: ' . mysql_error());
}
/*
if (($handle = fopen("Inactive.csv", "r")) !== FALSE) {
    $status = 5;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $sql = "update template set status = ".$status." where SKU = '".$data[0]."'";
        echo $sql."\n";
        $result = mysql_query($sql);
    }
}

$status = 5;
$sql = "update template set status = ".$status." where SKU in (".file_get_contents("../inventory/temp1.txt").")";
echo $sql."\n";
$result = mysql_query($sql);
mysql_close($link);
*/

echo mysql_real_escape_string(html_entity_decode("&amp;"));

exit;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="../../ext-3.0.0/resources/css/ext-all.css" />
    <script src="../../ext-3.0.0/adapter/ext/ext-base.js"></script>
    <script src="../../ext-3.0.0/ext-all.js"></script>
    <title>Login</title>
    <script>
        Ext.onReady(function(){
            Ext.Ajax.request({
                url: 'service.php?action=testComet',
                success: function(a, b, c){
                    console.log("success");
                    console.log([a, b, c]);
                },
                failure: function(a, b, c){
                    console.log("failure");
                    console.log([a, b, c]);
                },
                timeout:20000,
                params: { foo: 'bar' }
            });
        })
    </script>
</head>
<body>
    <div>
        
    </div>
</body>
</html>
