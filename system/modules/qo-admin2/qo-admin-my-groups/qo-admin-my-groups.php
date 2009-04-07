<?php

require (dirname(__FILE__)."/../common/qo-admin.php");

$success = "{success: false}";

$task = ($_POST['task']) ? ($_POST['task']) : null;

if($os->is_member_logged_in()) {
	$fMemberId = $os->get_member_id();
	switch($task) {
		case "read":
			$success = getGridDataMyGroups($fMemberId);
			break;
		case "delete":
			$success = deleteGridDataQOMemberGroups($fMemberId);
			break;
		case "edit":
			$success = updateGridDataQOMemberGroup($fMemberId);
			break;
		case "new":
			$success = saveFormDataNewQOMemberGroup($fMemberId);
			break;
		case "readMemberNames":
			$success = getLookupQOMemberNames($fMemberId);
			break;
		case "readGroupNames":
			$success = getLookupMyGroupNames($fMemberId);
			break;
		default:
			break;
	}//end switch
}

print $success;

?>