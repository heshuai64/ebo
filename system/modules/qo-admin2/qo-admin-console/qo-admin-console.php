<?php

require (dirname(__FILE__)."/../common/qo-admin.php");

$success = "{success: false}";

$task = ($_POST['task']) ? ($_POST['task']) : null;
if($os->is_member_logged_in()) {
	$fMemberId = $os->get_member_id();
	switch($task) {
		// qo_members
		case "readQOMembers":
			$success = getGridDataQOMembers($fMemberId);
			break;
		case "updateQOMember":
			$success = updateGridDataQOMember($fMemberId);
			break;
		case "deleteQOMembers":
			$success = deleteGridDataQOMembers($fMemberId);
			break;
		case "newQOMember":
			$success = saveFormDataNewQOMember($fMemberId);
			break;
		case "lookupQOMemberNames":
			$success = getLookupQOMemberNames($fMemberId);
			break;
		case "lookupQOMemberNamesPlus":
			$success = getLookupQOMemberNamesPlus($fMemberId);
			break;
		// qo_groups
		case "readQOGroups":
			$success = getGridDataQOGroups($fMemberId);
			break;
		case "updateQOGroup":
			$success = updateGridDataQOGroup($fMemberId);
			break;
		case "deleteQOGroups":
			$success = deleteGridDataQOGroups($fMemberId);
			break;
		case "newQOGroup":
			$success = saveFormDataNewQOGroup($fMemberId);
			break;
		case "lookupQOGroupNames":
			$success = getLookupQOGroupNames($fMemberId);
			break;
		case "lookupQOGroupNamesPlus":
			$success = getLookupQOGroupNamesPlus($fMemberId);
			break;
		// qo_members_has_groups
		case "readQOMemberGroups":
			$success = getGridDataQOMemberGroups($fMemberId);
			break;
		case "updateQOMemberGroup":
			$success = updateGridDataQOMemberGroup($fMemberId);
			break;
		case "deleteQOMemberGroups":
			$success = deleteGridDataQOMemberGroups($fMemberId);
			break;
		case "newQOMemberGroup":
			$success = saveFormDataNewQOMemberGroup($fMemberId);
			break;
		// qo_modules
		case "readQOModules":
			$success = getGridDataQOModules($fMemberId);
			break;
		case "updateQOModule":
			$success = updateGridDataQOModule($fMemberId);
			break;
		case "deleteQOModules":
			$success = deleteGridDataQOModules($fMemberId);
			break;
		case "newQOModule":
			$success = saveFormDataNewQOModule($fMemberId);
			break;
		case "lookupQOModuleNames":
			$success = getLookupQOModuleNames($fMemberId);
			break;
		// qo_launchers
		case "lookupQOLauncherNames":
			$success = getLookupQOLauncherNames($fMemberId);
			break;
		// qo_modules_has_files
		case "readQOModuleFiles":
			$success = getGridDataQOModuleFiles($fMemberId);
			break;
		case "updateQOModuleFile":
			$success = updateGridDataQOModuleFile($fMemberId);
			break;
		case "deleteQOModuleFiles":
			$success = deleteGridDataQOModuleFiles($fMemberId);
			break;
		case "newQOModuleFile":
			$success = saveFormDataNewQOModuleFile($fMemberId);
			break;		
		// qo_modules_has_launchers
		case "readQOModuleLaunchers":
			$success = getGridDataQOModuleLaunchers($fMemberId);
			break;
		case "updateQOModuleLauncher":
			$success = updateGridDataQOModuleLauncher($fMemberId);
			break;
		case "deleteQOModuleLaunchers":
			$success = deleteGridDataQOModuleLaunchers($fMemberId);
			break;
		case "newQOModuleLauncher":
			$success = saveFormDataNewQOModuleLauncher($fMemberId);
			break;		
		// qo_groups_has_modules
		case "readQOGroupModules":
			$success = getGridDataQOGroupModules($fMemberId);
			break;
		case "updateQOGroupModule":
			$success = updateGridDataQOGroupModule($fMemberId);
			break;
		case "deleteQOGroupModules":
			$success = deleteGridDataQOGroupModules($fMemberId);
			break;
		case "newQOGroupModule":
			$success = saveFormDataNewQOGroupModule($fMemberId);
			break;		
		// qo_sessions
		case "readQOSessions":
			$success = getGridDataQOSessions($fMemberId);
			break;
		case "deleteQOSessions":
			$success = deleteGridDataQOSessions($fMemberId);
			break;
		//qo_files
		case "readQOFiles":
			$success = getGridDataQOFiles($fMemberId);
			break;
		case "updateQOFile":
			$success = updateGridDataQOFile($fMemberId);
			break;
		case "deleteQOFiles":
			$success = deleteGridDataQOFiles($fMemberId);
			break;
		case "newQOFile":
			$success = saveFormDataNewQOFile($fMemberId);
			break;		
		// Tree Data
		case "readTreeData":
			$success = getTreeData($fMemberId);
			break;
		// Audit table
		case "readQOAdminAudit":
			$success = getGridDataQOAdminAudit ($fMemberId);
			break;
		// DEFULT
		default:
			break;
	}//end switch
}

print $success;

?>