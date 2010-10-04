<?php
require_once("system/os/os.php");
if(!class_exists('os')){
	header("Location: login.html");
}else{
	$os = new os();
	if(!$os->session->exists()){
		header("Location: login.html");
	}else{
		$os->init();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title>eBay BackOffice</title>

<!-- EXT JS LIBRARY -->
<link rel="stylesheet" type="text/css" href="../Ext/2.2/resources/css/ext-all.css" />
<script src="../Ext/2.2/adapter/ext/ext-base.js"></script>
<script src="../Ext/2.2/ext-all.js"></script> 
<script type="text/javascript">
	Ext.BLANK_IMAGE_URL = "../Ext/2.2/resources/images/default/s.gif";
</script>
<!-- DESKTOP CSS -->
<link rel="stylesheet" type="text/css" href="resources/css/desktop.css" />
<link rel="stylesheet" type="text/css" href="system/dialogs/colorpicker/colorpicker.css" />

<!-- THEME CSS -->
<?php print $os->theme->get(); ?>
<!-- MODULES CSS -->
<?php print $os->module->get_css(); ?>

<!-- SYSTEM DIALOGS AND CORE -->
<script src="system/dialogs/colorpicker/ColorPicker.js"></script>
<script src="system/core-min.js"></script>
<script src="system/login/cookies.js"></script>
<!-- QoDesk -->
<script src="QoDesk.php"></script>
</head>

<body scroll="no">

<div id="x-desktop"></div>

<div id="ux-taskbar">
	<div id="ux-taskbar-start"></div>
	<div id="ux-taskbar-panel-wrap">
		<div id="ux-quickstart-panel"></div>
		<div id="ux-taskbuttons-panel"></div>
		<div id="ux-systemtray-panel"></div>
	</div>
	<div class="x-clear"></div>
</div>

</body>
</html>
<?php }} ?>