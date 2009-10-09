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
session_start();
//echo str_replace("\\", "", $_SESSION[$_GET['type']][$_GET['id']]['description']);

/*
if(!empty($_SESSION[$_GET['type']][$_GET['id']]['description']) || $_GET['type'] == "sku"){
    echo str_replace("\\", "", $_SESSION[$_GET['type']][$_GET['id']]['description']);
}else{
    $sql = "select Description from ".$_GET['type']." where Id = ".$_GET['id'];
    $result = mysql_query($sql, $link);
    $row = mysql_fetch_assoc($result);
    echo $row['Description'];
}
*/

if($_GET['u'] == "true"){
    $sql = "select footer from account_footer where accountId = '".$_COOKIE['account_id']."'";
    $result = mysql_query($sql, $link);
    $row = mysql_fetch_assoc($result);
    //echo $row['footer'];
    echo str_replace("%description%", str_replace("\\", "", $_SESSION[$_GET['type']][$_GET['id']]['description']), $row['footer']);
    mysql_close($link);
}else{
    echo str_replace("\\", "", $_SESSION[$_GET['type']][$_GET['id']]['description']);
}
?>