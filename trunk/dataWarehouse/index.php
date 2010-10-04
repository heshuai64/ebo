<?php
session_start();
if(empty($_SESSION['modules'])){
    header('Location: /eBayBO/login.html');
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Data Warehouse</title>

    <!-- ** CSS ** -->
    <!-- base library -->
    <link rel="stylesheet" type="text/css" href="../../ext-3.2.1/resources/css/ext-all.css" />

    <!-- overrides to base library -->
    <link rel="stylesheet" type="text/css" href="ux/css/Portal.css" />

    <!-- page specific -->
    <link rel="stylesheet" type="text/css" href="sample.css" />
    <style type="text/css">

    </style>

    <!-- ** Javascript ** -->
    <!-- ExtJS library: base/adapter -->
    <script type="text/javascript" src="../../ext-3.2.1/adapter/ext/ext-base.js"></script>

    <!-- ExtJS library: all widgets -->
    <script type="text/javascript" src="../../ext-3.2.1/ext-all.js"></script>

    <!-- overrides to base library -->

    <!-- extensions -->
    <script type="text/javascript" src="ux/Portal.js"></script>
    <script type="text/javascript" src="ux/PortalColumn.js"></script>
    <script type="text/javascript" src="ux/Portlet.js"></script>

    <!-- page specific -->
    <script type="text/javascript" src="sample-grid.js"></script>
    <script type="text/javascript" src="examples.js"></script>
    <script type="text/javascript" src="portal.js"></script>

</head>
<body></body>
</html>