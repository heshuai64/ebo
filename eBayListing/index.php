<?php
if(empty($_COOKIE['account_id'])){
    header("Location: login.html");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <!--<title>eBay Listing</title>-->
    <link rel="stylesheet" type="text/css" href="../ext-3.0.0/resources/css/ext-all.css" />
    <link rel="stylesheet" type="text/css" href="ux/css/RowEditor.css" />
    <script src="../ext-3.0.0/adapter/ext/ext-base.js"></script>
    <script src="../ext-3.0.0/ext-all.js"></script>
    <script src="FileUploadField.js"></script>
    <script src="ux/RowEditor.js"></script>
    <script src="ebay-listing.js"></script>
    <link rel="stylesheet" type="text/css" href="fileuploadfield.css"/>
    <style type="text/css">
	html, body {
            font:normal 12px verdana;
            margin:0;
            padding:0;
            border:0 none;
            overflow:hidden;
            height:100%;
        }
	p {
	    margin:5px;
	}

	.inventory {
	    background-image:url(./images/package.png);
	}
	.template {
	    background-image:url(./images/plugin.png);
	}
	.template-status {
	    background-image:url(./images/cog.png);
	}
	.share-template {
	    background-image:url(./images/group.png);
	}
	.waiting-to{
	    background-image:url(./images/bomb.png);
	}
	.waiting-to-upload {
            background-image:url(./images/arrow_up.png);
        }
	.waiting-to-relist {
            background-image:url(./images/arrow_refresh.png);
        }
	.schedule{
	    background-image:url(./images/time.png);
	}
	.listing-activity {
	    background-image:url(./images/money_dollar.png);
	}
	.active-item-tab{
	    background-image:url(./images/hourglass.png);
	}
	.sold-item-tab{
	    background-image:url(./images/money_add.png);
	}
	.unsold-item-tab{
	    background-image:url(./images/money_delete.png);
	}
	.upload-icon{
	    background: url(./images/database.png) no-repeat 0 0 !important;
	}
	.interval-upload{
	    background-image:url(./images/clock_add.png) !important;
	}
	.manage{
	    background-image:url(./images/wrench.png) !important;
	}
	.user{
	    background-image:url(./images/user.png) !important;
	}
	.proxy{
	    background-image:url(./images/vector.png) !important;
	}
	.log{
	    background-image:url(./images/application_view_list.png) !important;
	}
	.upload-log{
	    background-image:url(./images/table_go.png) !important;
	}
	.template-log{
	    background-image:url(./images/table_edit.png) !important;
	}
	
	.import-turbo-lister{
	    background-image:url(./images/tb.png) !important;
	}
	.import-spoonfeeder{
	    background-image:url(./images/spoon.png) !important;
	}
    </style>
</head>
<body>
    <!--
    <div id="north">
        <p>north - generally for menus, toolbars and/or advertisements</p>
    </div>
    <div id="props-panel" style="width:200px;height:200px;overflow:hidden;">
    </div>
    -->
</body>
</html>
