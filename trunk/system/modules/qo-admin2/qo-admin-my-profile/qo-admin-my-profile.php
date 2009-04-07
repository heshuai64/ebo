<?php

require (dirname(__FILE__)."/../common/qo-admin.php");

$success = "{'success': false}";

$task = ($_POST['task']) ? ($_POST['task']) : null;

if($os->is_member_logged_in()) {
	$fMemberId = $os->get_member_id();
	switch($task) {
		case "read":
			$success = getFormDataMyProfile($fMemberId);
			break;
		case "save":
			$success = saveFormDataMyProfile($fMemberId);
			break;
		default:
			break;
	}//end switch
}

print $success;

?>