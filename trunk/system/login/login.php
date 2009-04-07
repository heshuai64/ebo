<?php
// get the os
require_once("../os/os.php");
if(class_exists('os')){
	$os = new os();
	
	$module = $_POST['module'];
	
	if($module == ""){
		die("success: false");
	}
	
	if($module == 'login'){
		print $os->session->login($_POST['user'], $_POST['pass'], $_POST['group']);
	}else if($module == 'signup'){
		//print $os->signup($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['email_verify'], $_POST['comments']);
	}else if($module == 'forgotPassword'){
		//print $os->forgot_password($_POST['user']);
	}else{
		print "{success: false}";
	}
}
?>