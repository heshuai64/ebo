<?php
$link = mysql_connect('localhost', 'root', '5333533');
if (!$link) {
    die('Could not connect: ' . mysql_error($link));
}

mysql_query("SET NAMES 'UTF8'", $link);

if (!mysql_select_db("ebaylisting", $link)) {
    echo "Unable to select ebaylisting: " . mysql_error($link);
    exit;
}

/*
switch($_GET['t']){
    case "s":
        echo $_GET['d'];
    break;
    
    case "t":
        $sql = "select Description from template where Id = '".$_GET['id']."' and accountId = '".$_COOKIE['account_id']."'";
        $result = mysql_query($sql, $link);
        $row = mysql_fetch_assoc($result);
        echo $row['Description'];
    break;

    case "i":
        $sql = "select Description from items where Id = '".$_GET['id']."' and accountId = '".$_COOKIE['account_id']."'";
        $result = mysql_query($sql, $link);
        $row = mysql_fetch_assoc($result);
        echo $row['Description'];
    break;
    
}
*/
echo $_GET['d'];

if($_GET['u'] == "true"){
    $sql = "select footer from account_footer where accountId = '".$_COOKIE['account_id']."'";
    $result = mysql_query($sql, $link);
    $row = mysql_fetch_assoc($result);
    echo $row['footer'];
    mysql_close($link);
}
?>