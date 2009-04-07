<?php
// Author: Paul Simmons
// Version
// 2.0.0

// ============================================================================
// Generic Functions
// ============================================================================

function getJasonData ($sql_query, $order_column=2) {
	$success = "[]";
	
	if($result = mysql_query($sql_query . ' order by ' . $order_column))
	{
		$items = array();
		while($row = mysql_fetch_assoc($result))
		{
			$items[] = $row;
		}
		$success = json_encode($items);
	}
	return $success;
}

function getStoreData ($json_key, $sql_query, $order_column=2) {
	$success = "{'" . $json_key . "': []}";
	
	if($result = mysql_query($sql_query . ' order by ' . $order_column))
	{
		$items = array();
		while($row = mysql_fetch_assoc($result))
		{
			$items[] = $row;
		}
		$success = "{'" . $json_key . "':" . json_encode($items) . "}";
	}
	return $success;
}

function deleteData ($sqlTable, $whereClause='1=1') {
	$sql = 'DELETE FROM '.$sqlTable.' WHERE '.$whereClause;
	return (mysql_query ($sql));
}

function dbSqlExecute($fMemberId, $sqlFileToExecute) {
	// Provided by Mark Floyd (aka Cobnet)
	
	$success = false;

	$sqlErrorText = '';
	$sqlErrorCode = 0;
	$sqlStmt      = '';

	// Load and explode the sql file
	if (file_exists($sqlFileToExecute)) {
		writeAudit ($fMemberId, 'EXECUTE', 'Executing db file: '.$sqlFileToExecute);
		$f = fopen($sqlFileToExecute,"r");
		$sqlFile = fread($f,filesize($sqlFileToExecute));
		$sqlArray = explode(';',$sqlFile);
           
		//Process the sql file by statements
		foreach ($sqlArray as $stmt) {
			if (strlen($stmt)>3){
				$result = mysql_query($stmt);
				if (!$result){
					$sqlErrorCode = mysql_errno();
					$sqlErrorText = mysql_error();
					$sqlStmt      = $stmt;
					break;
				}
			}
		}

		if ($sqlErrorCode == 0){
			$success = true;
		} else {
			$success = false;
		}

	} else {
		$success = false;
	}

	return $success;
}

function writeAudit ($auditMemberId, $auditState, $auditText) {
	$sql = 'INSERT INTO `qo_admin_audit` (`qo_members_id`, `audit_state`, `audit_text`) VALUES ('.$auditMemberId.',\''.$auditState.'\',\''.mysql_real_escape_string($auditText).'\')';
	mysql_query ($sql);
}

function getNameById ($field, $tableName, $id) {
	$sql = 'SELECT '.$field.' as name FROM '.$tableName.' WHERE id = '.$id;
	$result = mysql_query ($sql);
	if ($result) {
		$row = mysql_fetch_assoc($result);
		return $row['name'];
	} else {
		return 'no data found or error';
	}
}

function getMemberNameById ($memberId=0) {
	return getNameById ('email_address', 'qo_members', $memberId);
}

function getGroupNameById ($groupId=0) {
	return getNameById ('name', 'qo_groups', $groupId);
}

function getModuleNameById ($moduleId=0) {
	return getNameById ('moduleName', 'qo_modules', $moduleId);
}

function getLauncherNameById ($launcherId=0) {
	return getNameById ('name', 'qo_launchers', $launcherId);
}

function getMemberNameBySessionId ($sessionId='x') {
	$memberId = getNameById ('qo_members_id', 'qo_sessions', $sessionId);
	return getMemberNameById ($memberId);
}

function getPluginFileById ($fileId=0) {
	return getNameById ('name', 'qo_files', $fileId);
}

// ============================================================================
// Get Grid Data Functions
// ============================================================================

function getTreeDataQOMembers () {

	$sql = 'SELECT CONCAT("U-",`id`) as id, `email_address` as text, "true" as leaf from qo_members';

	return getJasonData ($sql);
}

function getTreeDataQOGroups () {

	$sql = 'SELECT CONCAT("G-",`id`) as id, `name` as text, "true" as leaf from qo_members';

	return getJasonData ($sql);
}

function getTreeDataQOModules () {

	$sql = 'SELECT CONCAT("M-",`id`) as id, `moduleName` as text, "true" as leaf from qo_members';

	return getJasonData ($sql);
}

function getTreeData($fMemberId) {
	$json_data = '{
					"text" : "Members"
					, "id" : "MembersGridTab"
					, "leaf" : false
					, "children": '. getTreeDataQOMembers() . '
				}
				, {
					"text" : "Groups"
					, "id" : "GroupsGridTab"
					, "leaf" : false
					, "children" : ' . getTreeDataQOGroups() . '
				}
				, {
					"text" : "Modules"
					, "id" : "ModulesGridTab"
					, "leaf" : false
					, "children" : ' . getTreeDataQOModules() . '
				}';
	return $json_data;
}

function getGridDataQOGroupModules ($fMemberId) {

	// qo_groups_has_modules a composite primary key so we need to create an id for the grid
	$sql = 'SELECT concat(`qo_groups_id`,"^",`qo_modules_id`) as "id", `qo_groups_id`, `qo_modules_id`, `active` 
	        FROM `qo_groups_has_modules`';

	return getStoreData ("qo_groups_has_modules", $sql, '2,3');
	
}

function getGridDataQOGroups ($fMemberId) {

	$sql = 'select `id`, `name`, `description`, `active` from `qo_groups`';
	
	return getStoreData ("qo_groups", $sql);
}

function getGridDataQOMemberGroups ($fMemberId) {

	$sql = 'SELECT concat(`qo_members_id`,"^",`qo_groups_id`) as "id", `qo_members_id`, `qo_groups_id`, `active`, `admin_flag` 
	        FROM `qo_members_has_groups`';
	
	return getStoreData ("qo_members_has_groups", $sql, '2,3');
	
}

function getGridDataQOMembers ($fMemberId) {

	$sql = 'select `id`, `first_name`, `last_name`, `email_address`, `password`, `active` from `qo_members`';
	
	return getStoreData ("qo_members", $sql, 4);
	
}

function getGridDataQOModuleFiles ($fMemberId) {

	$sql = 'SELECT concat(`qo_modules_id`,"^",`name`) as "id", `qo_modules_id`, `name`, `type` 
	        FROM `qo_modules_has_files`';
	
	return getStoreData ("qo_modules_has_files", $sql, '2,3');
	
}

function getGridDataQOModuleLaunchers ($fMemberId) {

	$sql = 'SELECT concat(`qo_members_id`,"^",`qo_groups_id`,"^",`qo_modules_id`,"^",`qo_launchers_id`) as "id", `qo_members_id`, `qo_groups_id`, `qo_modules_id`, `qo_launchers_id`, `sort_order` 
	        FROM `qo_modules_has_launchers`';
	
	return getStoreData ("qo_modules_has_launchers", $sql, '2,3,4,5');
	
}

function getGridDataQOModules ($fMemberId) {

	$success = "{'qo_modules': []}";
	$sql = 'select `id`, `moduleName`, `moduleType`, `moduleId` as fmoduleId, `version`, `author`, `description`, `path`, `active` from `qo_modules`';
	
	return getStoreData ("qo_modules", $sql);
	
}

function getGridDataQOSessions ($fMemberId) {

	$success = "{'qo_sessions': []}";
	$sql = 'select `id`, `qo_members_id`, `ip`, `date` from `qo_sessions`';
	
	return getStoreData ("qo_sessions", $sql);
	
}

function getGridDataQOFiles ($fMemberId) {

	$success = "{'qo_files': []}";
	$sql = 'select `id`, `name`, `path`, `type` from `qo_files` where default_file = \'FALSE\'';
	
	return getStoreData ("qo_files", $sql);
	
}

function getGridDataMyGroups ($fMemberId) {

	$sql = 'SELECT concat(`qo_members_id`,"^",`qo_groups_id`) as "id", `qo_members_id`, `qo_groups_id`, `active`, `admin_flag`
			FROM `qo_members_has_groups`
			where `qo_groups_id` in 
				(select a.`qo_groups_id`
				 from `qo_members_has_groups` a
				 where a.`qo_members_id` = ' . $fMemberId . '
				 and a.`admin_flag` = "true")';
	
	return getStoreData ("qo_members_has_groups", $sql, '2,3');
	
}

function getGridDataQOAdminAudit ($fMemberId) {
	$sql = 'SELECT `id`, `qo_members_id`, `audit_date`, `audit_state`, `audit_text`
			FROM `qo_admin_audit`';
	
	return getStoreData ("qo_admin_audit", $sql, '3 desc');
}

// ============================================================================
// Delete Grid Data Functions
// ============================================================================

function deleteGridDataQOGroupModules ($fMemberId) {

    $arr = $_POST['deleteKeys'];
    $count = 0;
	$selectedRows = json_decode(stripslashes($arr));//decode the data from json format
	
    //should validate and clean data prior to posting to the database
    foreach($selectedRows as $row_id)
    {
		// we need to strip out the composite key to its individual parts
		list ($id1, $id2) = explode ('^', $row_id, 2);
        $id1 = (integer) $id1;
        $id2 = (integer) $id2;
        if (deleteData ('`qo_groups_has_modules`', '`qo_groups_id` = '.$id1.' and `qo_modules_id` = '.$id2)) {
			$count++;
			$auditState = 'SUCCESS';
		} else {
			$auditState = 'FAILURE';
		}
		writeAudit ($fMemberId, $auditState, 'Delete permission (group_id: '.$id1.', module_id: '.$id2.')');
    }
	
    if ($count) { //only checks if the last record was deleted, others may have failed

        /* If using ScriptTagProxy:  In order for the browser to process the returned
           data, the server must wrap te data object with a call to a callback function,
           the name of which is passed as a parameter by the ScriptTagProxy. (default = "stcCallback1001")
           If using HttpProxy no callback reference is to be specified*/
        $cb = isset($_GET['callback']) ? $_GET['callback'] : '';
        $response = array('success'=>$count, 'del_count'=>$count);
        $json_response = json_encode($response);
        return $cb . $json_response;
    } else {
        return '{failure: true}';
    }
}

function deleteGridDataQOGroups ($fMemberId) {

	$key = $_POST['key'];
    $arr = $_POST['deleteKeys'];
    $count = 0;
	
	$selectedRows = json_decode(stripslashes($arr));//decode the data from json format
	
    //should validate and clean data prior to posting to the database
    foreach($selectedRows as $row_id)
    {
        $id = (integer) $row_id;
		// Delete rows from other tables
		deleteData ('`qo_groups_has_modules`', '`qo_groups_id` = '.$id);
        deleteData ('`qo_members_has_groups`', '`qo_groups_id` = '.$id);
        deleteData ('`qo_modules_has_launchers`', '`qo_groups_id` = '.$id);

		// Delete group
		$groupName = getGroupNameById ($id);
        if (deleteData ('`qo_groups`', '`'.$key.'` = '.$id)) {
			$count++;
			$auditState = 'SUCCESS';
		} else {
			$auditState = 'FAILURE';
		}
		writeAudit ($fMemberId, $auditState, 'Delete group ('.$groupName.')');
		
    }
	
    if ($count) { //only checks if the last record was deleted, others may have failed

        /* If using ScriptTagProxy:  In order for the browser to process the returned
           data, the server must wrap te data object with a call to a callback function,
           the name of which is passed as a parameter by the ScriptTagProxy. (default = "stcCallback1001")
           If using HttpProxy no callback reference is to be specified*/
        $cb = isset($_GET['callback']) ? $_GET['callback'] : '';
        $response = array('success'=>$count, 'del_count'=>$count);
        $json_response = json_encode($response);
        return $cb . $json_response;
    } else {
        return '{failure: true}';
    }
}

function deleteGridDataQOMemberGroups ($fMemberId) {

    $arr = $_POST['deleteKeys'];
    $count = 0;
	$selectedRows = json_decode(stripslashes($arr));//decode the data from json format
	
    //should validate and clean data prior to posting to the database
    foreach($selectedRows as $row_id)
    {
		list ($id1, $id2) = explode ('^', $row_id, 2);
        $id1 = (integer) $id1;
        $id2 = (integer) $id2;
		$memberName = getMemberNameById ($id1);
		$groupName = getGroupNameById ($id2);
        if (deleteData ('`qo_members_has_groups`', '`qo_members_id` = '.$id1.' and `qo_groups_id` = '.$id2)) {
			$count++;
			$auditState = 'SUCCESS';
		} else {
			$auditState = 'FAILURE';
		}
		writeAudit ($fMemberId, $auditState, 'Delete group permission (Member: '.$memberName.', Group: '.$groupName.')');
    }
	
    if ($count) { //only checks if the last record was deleted, others may have failed

        /* If using ScriptTagProxy:  In order for the browser to process the returned
           data, the server must wrap te data object with a call to a callback function,
           the name of which is passed as a parameter by the ScriptTagProxy. (default = "stcCallback1001")
           If using HttpProxy no callback reference is to be specified*/
        $cb = isset($_GET['callback']) ? $_GET['callback'] : '';
        $response = array('success'=>$count, 'del_count'=>$count);
        $json_response = json_encode($response);
        return $cb . $json_response;
    } else {
        return '{failure: true}';
    }
}

function deleteGridDataQOMembers ($fMemberId) {

	$key = $_POST['key'];
    $arr = $_POST['deleteKeys'];
    $count = 0;
	
	$selectedRows = json_decode(stripslashes($arr));//decode the data from json format
	
    //should validate and clean data prior to posting to the database
    foreach($selectedRows as $row_id)
    {
        $id = (integer) $row_id;
		// Delete details from other tables
        deleteData ('`qo_members_has_groups`', '`qo_members_id` = '.$id);
        deleteData ('`qo_modules_has_launghers`', '`qo_members_id` = '.$id);

		// remove member from member table.
		$memberLogin = getMemberNameById ($id);
        if (deleteData ('`qo_members`', '`'.$key.'` = '.$id)) {
			$count++;
			$auditState = 'SUCCESS';
		} else {
			$auditState = 'FAILURE';
		}
		writeAudit ($fMemberId, $auditState, 'Delete member ('.$memberLogin.')');
    }
	
    if ($count) { //only checks if the last record was deleted, others may have failed

        /* If using ScriptTagProxy:  In order for the browser to process the returned
           data, the server must wrap te data object with a call to a callback function,
           the name of which is passed as a parameter by the ScriptTagProxy. (default = "stcCallback1001")
           If using HttpProxy no callback reference is to be specified*/
        $cb = isset($_GET['callback']) ? $_GET['callback'] : '';
        $response = array('success'=>$count, 'del_count'=>$count);
        $json_response = json_encode($response);
        return $cb . $json_response;
    } else {
        return '{failure: true}';
    }
}

function deleteGridDataQOModuleFiles ($fMemberId) {

    $arr = $_POST['deleteKeys'];
    $count = 0;
	$selectedRows = json_decode(stripslashes($arr));//decode the data from json format
	
    //should validate and clean data prior to posting to the database
    foreach($selectedRows as $row_id)
    {
		list ($id1, $id2) = explode ('^', $row_id, 2);
        $id1 = (integer) $id1;
		$moduleName = getModuleNameById ($id1);
        if (deleteData ('qo_modules_has_files', 'qo_modules_id` = '.$id1.' and `name` = \''.$id2.'\'')) {
			$count++;
			$auditState = 'SUCCESS';
		} else {
			$auditState = 'FAILURE';
		}
		writeAudit ($fMemberId, $auditState, 'Delete module file (Module: '.$moduleName.', File: '.$id2.')');
    }
	
    if ($count) { //only checks if the last record was deleted, others may have failed

        /* If using ScriptTagProxy:  In order for the browser to process the returned
           data, the server must wrap te data object with a call to a callback function,
           the name of which is passed as a parameter by the ScriptTagProxy. (default = "stcCallback1001")
           If using HttpProxy no callback reference is to be specified*/
        $cb = isset($_GET['callback']) ? $_GET['callback'] : '';
        $response = array('success'=>$count, 'del_count'=>$count);
        $json_response = json_encode($response);
        return $cb . $json_response;
    } else {
        return '{failure: true}';
    }
}

function deleteGridDataQOModuleLaunchers ($fMemberId) {

    $arr = $_POST['deleteKeys'];
    $count = 0;
	$selectedRows = json_decode(stripslashes($arr));//decode the data from json format
	
    //should validate and clean data prior to posting to the database
    foreach($selectedRows as $row_id)
    {
		list ($id1, $id2, $id3, $id4) = explode ('^', $row_id, 4);
        $id1 = (integer) $id1;
        $id2 = (integer) $id2;
        $id3 = (integer) $id3;
        $id4 = (integer) $id4;
		$memberName = ($id1 == 0) ? 'All Members' : getMemberNameById ($id1);
		$groupName = ($id2 == 0) ? 'All groups' : getGroupNameById ($id2);
		$moduleName = getModuleNameById ($id3);
		$launcherName = getLauncherNameById ($id4);
        if (deleteData ('`qo_modules_has_launchers`', '`qo_members_id` = '.$id1.' AND `qo_groups_id` = '.$id2.' AND `qo_modules_id` = '.$id3.' AND `qo_launchers_id` = '.$id4)) {
			$count++;
			$auditState = 'SUCCESS';
		} else {
			$auditState = 'FAILURE';
		}
		writeAudit ($fMemberId, $auditState, 'Delete module launcher (Member: '.$memberName.', Group: '.$groupName.', Module: '.$moduleName.', Launcher: '.$launcherName.')');
    }
	
    if ($count) { //only checks if the last record was deleted, others may have failed

        /* If using ScriptTagProxy:  In order for the browser to process the returned
           data, the server must wrap te data object with a call to a callback function,
           the name of which is passed as a parameter by the ScriptTagProxy. (default = "stcCallback1001")
           If using HttpProxy no callback reference is to be specified*/
        $cb = isset($_GET['callback']) ? $_GET['callback'] : '';
        $response = array('success'=>$count, 'del_count'=>$count);
        $json_response = json_encode($response);
        return $cb . $json_response;
    } else {
        return '{failure: true}';
    }
}

function deleteGridDataQOModules ($fMemberId) {

	$key = $_POST['key'];
    $arr = $_POST['deleteKeys'];
    $count = 0;
	$selectedRows = json_decode(stripslashes($arr));//decode the data from json format
	
    //should validate and clean data prior to posting to the database
    foreach($selectedRows as $row_id)
    {
        $id = (integer) $row_id;
		
		// Delete rows from other tables
		deleteData ('`qo_modules_has_files`', '`qo_modules_id` = '.$id);
		deleteData ('`qo_modules_has_launchers`', '`qo_modules_id` = '.$id);
		deleteData ('`qo_groups_has_modules`', '`qo_modules_id` = '.$id);

		// Execute uninstall db script
		$sql = 'SELECT `path` FROM `qo_modules` WHERE `'.$key.'` = '.$id;
		if ($result = mysql_query($sql)) {
			$row = mysql_fetch_assoc($result);
			$fDBFile = str_replace('\\','/',getcwd()).'/'.str_replace('\'','',$row['path']).'db_uninstall.sql';
			dbSqlExecute ($fMemberId, $fDBFile);
		}
		// Delete module from db table
		$moduleName = getModuleNameById ($id);
        if (deleteData ('`qo_modules`', '`'.$key.'` = '.$id)) {
			$count++;
			$auditState = 'SUCCESS';
		} else {
			$auditState = 'FAILURE';
		}
		writeAudit ($fMemberId, $auditState, 'Delete module ('.$moduleName.')');
    }
	
    if ($count) { //only checks if the last record was deleted, others may have failed

        /* If using ScriptTagProxy:  In order for the browser to process the returned
           data, the server must wrap te data object with a call to a callback function,
           the name of which is passed as a parameter by the ScriptTagProxy. (default = "stcCallback1001")
           If using HttpProxy no callback reference is to be specified*/
        $cb = isset($_GET['callback']) ? $_GET['callback'] : '';
        $response = array('success'=>$count, 'del_count'=>$count);
        $json_response = json_encode($response);
        return $cb . $json_response;
    } else {
        return '{failure: true}';
    }
}

function deleteGridDataQOSessions ($fMemberId) {

	$key = $_POST['key'];
    $arr = $_POST['deleteKeys'];
    $count = 0;
	$selectedRows = json_decode(stripslashes($arr));//decode the data from json format
	
    //should validate and clean data prior to posting to the database
    foreach($selectedRows as $row_id)
    {
        $id = (integer) $row_id;
		$sessionMember = getMemberNameBySessionId ($id);
        if (deleteData ('`qo_sessions`', '`'.$key.'` = '.$id)) {
			$count++;
			$auditState = 'SUCCESS';
		} else {
			$auditState = 'FAILURE';
		}
		writeAudit ($fMemberId, $auditState, 'Delete session ('.$sessionMember.')');
    }
	
    if ($count) { //only checks if the last record was deleted, others may have failed

        /* If using ScriptTagProxy:  In order for the browser to process the returned
           data, the server must wrap te data object with a call to a callback function,
           the name of which is passed as a parameter by the ScriptTagProxy. (default = "stcCallback1001")
           If using HttpProxy no callback reference is to be specified*/
        $cb = isset($_GET['callback']) ? $_GET['callback'] : '';
        $response = array('success'=>$count, 'del_count'=>$count);
        $json_response = json_encode($response);
        return $cb . $json_response;
    } else {
        return '{failure: true}';
    }
}

function deleteGridDataQOFiles ($fMemberId) {

	$key = $_POST['key'];
    $arr = $_POST['deleteKeys'];
    $count = 0;
	$selectedRows = json_decode(stripslashes($arr));//decode the data from json format
	
    //should validate and clean data prior to posting to the database
    foreach($selectedRows as $row_id)
    {
        $id = (integer) $row_id;
		$fileName = getPluginFileById ($id);
        if (deleteData ('`qo_files`', '`'.$key.'` = '.$id)) {
			$count++;
			$auditState = 'SUCCESS';
		} else {
			$auditState = 'FAILURE';
		}
		writeAudit ($fMemberId, $auditState, 'Delete plugin file ('.$fileName.')');
    }
	
    if ($count) { //only checks if the last record was deleted, others may have failed

        /* If using ScriptTagProxy:  In order for the browser to process the returned
           data, the server must wrap te data object with a call to a callback function,
           the name of which is passed as a parameter by the ScriptTagProxy. (default = "stcCallback1001")
           If using HttpProxy no callback reference is to be specified*/
        $cb = isset($_GET['callback']) ? $_GET['callback'] : '';
        $response = array('success'=>$count, 'del_count'=>$count);
        $json_response = json_encode($response);
        return $cb . $json_response;
    } else {
        return '{failure: true}';
    }
}

// ============================================================================
// Edit Grid Data Functions
// ============================================================================

function updateGridDataQOGroup($fMemberId) {
    /*
     * $key:   db primary key label
     * $id:    db primary key value
     * $field: column or field name that is being updated (see data.Record mapping)
     * $value: the new value of $field
     */ 

    $key = $_POST['key'];
    $id    = (integer) mysql_real_escape_string($_POST['keyID']);
    $field = mysql_real_escape_string($_POST['field']);
    $value = mysql_real_escape_string ($_POST['value']);
	$memberName = getMemberNameById($id);
   
    $sql = 'UPDATE `qo_groups` SET `'.$field.'` = \''.$value.'\' WHERE `'.$key.'` = '.$id;
	
    if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'Update to group (member: '.$memberName.'), '.$field.' set to '.$value.'.');
	return $returnState;
}

function updateGridDataQOGroupModule($fMemberId) {
    /*
     * $idX:   db primary key value
     * $field: column or field name that is being updated (see data.Record mapping)
     * $value: the new value of $field
     */ 

	// we need to strip out the composite key to its individual parts
	list ($id1, $id2) = explode ('^', mysql_real_escape_string($_POST['keyID']), 2);
	$id1 = (integer) $id1;
    $field = mysql_real_escape_string($_POST['field']);
    $value = mysql_real_escape_string ($_POST['value']);
	$groupName = getGroupNameById($id1);
	$moduleName = getModuleNameById($id2);
	
    $sql = 'UPDATE `qo_groups_has_modules`
	        SET `'.$field.'` = \''.$value.'\' 
			WHERE `qo_groups_id` = '.$id1.' and `qo_modules_id` = \''.$id2.'\'';
	
    if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'Update to group module permission (group: '.$groupName.', module: '.$moduleName.'), '.$field.' set to '.$value.'.');
	return $returnState;
}

function updateGridDataQOMemberGroup($fMemberId) {
    /*
     * $idX:   db primary key value
     * $field: column or field name that is being updated (see data.Record mapping)
     * $value: the new value of $field
     */ 

	list ($id1, $id2) = explode ('^', mysql_real_escape_string($_POST['keyID']), 2);
	$id1 = (integer) $id1;
    $field = mysql_real_escape_string($_POST['field']);
    $value = mysql_real_escape_string ($_POST['value']);
	$memberName = getMemberNameById ($id1);
	$groupName = getGroupNameById ($id2);
	
    $sql = 'UPDATE `qo_members_has_groups`
	        SET `'.$field.'` = \''.$value.'\' 
			WHERE `qo_members_id` = '.$id1.' and `qo_groups_id` = \''.$id2.'\'';
    if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'Update to member group permissions (member: '.$memberName.', group: '.$groupName.'), '.$field.' set to '.$value.'.');
	return $returnState;
}

function updateGridDataQOMember($fMemberId) {
    /*
     * $key:   db primary key label
     * $id:    db primary key value
     * $field: column or field name that is being updated (see data.Record mapping)
     * $value: the new value of $field
     */ 

    $key = $_POST['key'];
    $id    = (integer) mysql_real_escape_string($_POST['keyID']);
    $field = mysql_real_escape_string($_POST['field']);
    $value = mysql_real_escape_string ($_POST['value']);
	$memberName = getMemberNameById ($id);

    $sql = 'UPDATE `qo_members` SET `'.$field.'` = \''.$value.'\' WHERE `'.$key.'` = '.$id;
	
    if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'Update to member ('.$memberName.'), '.$field.' set to '.$value.'.');
	return $returnState;
}

function updateGridDataQOModuleFile($fMemberId) {
    /*
     * $idX:   db primary key value
     * $field: column or field name that is being updated (see data.Record mapping)
     * $value: the new value of $field
     */ 

	list ($id1, $id2) = explode ('^', mysql_real_escape_string($_POST['keyID']), 2);
	$id1 = (integer) $id1;
    $field = mysql_real_escape_string($_POST['field']);
    $value = mysql_real_escape_string ($_POST['value']);
	$moduleName = getModuleNameById ($id1);

    $sql = 'UPDATE `qo_modules_has_files`
	        SET `'.$field.'` = \''.$value.'\' 
			WHERE `qo_modules_id` = '.$id1.' and `name` = \''.$id2.'\'';

			if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'Update to module file (module: '.$moduleName.', name: '.$id2.'), '.$field.' set to '.$value.'.');
	return $returnState;
}

function updateGridDataQOModuleLauncher($fMemberId) {
    /*
     * $idX:   db primary key value
     * $field: column or field name that is being updated (see data.Record mapping)
     * $value: the new value of $field
     */ 


	list ($id1, $id2, $id3, $id4) = explode ('^', mysql_real_escape_string($_POST['keyID']), 4);
    $id1 = (integer) $id1;
    $id2 = (integer) $id2;
    $id3 = (integer) $id3;
    $id4 = (integer) $id4;
    $field = mysql_real_escape_string($_POST['field']);
    $value = mysql_real_escape_string ($_POST['value']);
	$memberName = ($id1 == 0) ? "All Members" : getMemberNameById ($id1);
	$groupName = ($id2 == 0) ? "All Groups" : getGroupNameById ($id2);
	$moduleName = getModuleNameById ($id3);
	$launcherName = getLauncherNameById ($id4);
	
    $sql = 'UPDATE `qo_modules_has_launchers`
	        SET `'.$field.'` = '.$value.' 
	        WHERE `qo_members_id` = '.$id1.' 
			  AND `qo_groups_id` = '.$id2.'
			  AND `qo_modules_id` = '.$id3.'
			  AND `qo_launchers_id` = '.$id4;
	
    if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'Update to module launcher (member: '.$memberName.', group: '.$groupName.', module: '.$moduleName.', launcher: '.$launcherName.'), '.$field.' set to '.$value.'.');
	return $returnState;
}

function updateGridDataQOFile($fMemberId) {
    /*
     * $key:   db primary key label
     * $id:    db primary key value
     * $field: column or field name that is being updated (see data.Record mapping)
     * $value: the new value of $field
     */ 

    $key = $_POST['key'];
    $id    = (integer) mysql_real_escape_string($_POST['keyID']);
    $field = mysql_real_escape_string($_POST['field']);
    $value = mysql_real_escape_string ($_POST['value']);
	// this is because moduleId is used by this.app.connect for alternate purpose
	$field = ($field == 'fmoduleId') ? 'moduleId' : $field;
	$fileName = getPluginFileById ($id);
   
    //should validate and clean data prior to posting to the database

    $sql = 'UPDATE `qo_files` SET `'.$field.'` = \''.$value.'\' WHERE `'.$key.'` = '.$id;
	
    if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'Update to plugin file ('.$fileName.'), '.$field.' set to '.$value.'.');
	return $returnState;
}

function updateGridDataMyGroup ($fMemberId) {
	return updateGridDataMemberGroup($fMemberId);
}

// ============================================================================
// Get Form Data Functions
// ============================================================================

function getFormDataMyProfile ($fMemberId) {

	$success = "{success: false, data: []}";
	
	$sql = 'select `first_name`, `last_name`, `email_address`, `password` 
			from `qo_members`
			where id = '.$fMemberId;
	
	if($result = mysql_query($sql))
	{
		$row = mysql_fetch_assoc($result);
		
		$success = '{success: true, "data":'.json_encode($row).'}';
	}
	
	return $success;
	
}

// ============================================================================
// Save Form Data Functions
// ============================================================================

function saveFormDataMyProfile($fMemberId) {

	// make all the strings safe
	$fFirstName 	= '\''.mysql_real_escape_string($_POST['first_name']).'\'';
	$fLastName 		= '\''.mysql_real_escape_string($_POST['last_name']).'\'';
	$fEmailAddress 	= '\''.mysql_real_escape_string($_POST['email_address']).'\'';
	$fPassword 		= '\''.mysql_real_escape_string($_POST['password']).'\'';
	
	$sql = 'UPDATE `qo_members`
			SET `first_name` = '.$fFirstName.'
			  , `last_name` = '.$fLastName.'
			  , `email_address` = '.$fEmailAddress.'
			  , `password` = '.$fPassword.'
			WHERE id = '.$fMemberId;
	
    if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'Update to My Profile');
	return $returnState;
}

function saveFormDataNewQOGroupModule($fMemberId) {

	// make all the strings safe
	$fQoGroupsId	= (integer) mysql_real_escape_string($_POST['qo_groups_id']);
	$fQoModulesId	= (integer) mysql_real_escape_string($_POST['qo_modules_id']);
	$fActive		= '\''.mysql_real_escape_string($_POST['active']).'\'';
	$groupName = getGroupNameById ($fQoGroupsId);
	$moduleName = getModuleNameById ($fQoModulesId);
	
	$sql = 'INSERT INTO `qo_groups_has_modules` (`qo_groups_id`, `qo_modules_id`, `active`)
            VALUES ('.$fQoGroupsId.','.$fQoModulesId.','.$fActive.')';
    if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'New group module permission (group: '.$groupName.', module: '.$moduleName.')');
	return $returnState;
}

function saveFormDataNewQOGroup($fMemberId) {

	// make all the strings safe
	$fName 			= '\''.mysql_real_escape_string($_POST['name']).'\'';
	$fDescription	= '\''.mysql_real_escape_string($_POST['description']).'\'';
	$fActive 		= '\''.mysql_real_escape_string($_POST['active']).'\'';
	
	$sql = 'INSERT INTO `qo_groups` (`name`, `description`, `active`)
		VALUES ('.$fName.','.$fDescription.','.$fActive.')';
	
    if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'New group ('.$fName.')');
	return $returnState;
}

function saveFormDataNewQOMemberGroup($fMemberId) {

	// make all the strings safe
	$fQoMembersId	= (integer) mysql_real_escape_string($_POST['qo_members_id']);
	$fQoGroupsId	= (integer) mysql_real_escape_string($_POST['qo_groups_id']);
	$fActive		= '\''.mysql_real_escape_string($_POST['active']).'\'';
	$fAdminFlag		= '\''.mysql_real_escape_string($_POST['admin_flag']).'\'';
	$memberName = getMemberNameById ($fQoMembersId);
	$groupName = getGroupNameById ($fQoGroupId);
	
	$sql = 'INSERT INTO `qo_members_has_groups` (`qo_members_id`, `qo_groups_id`, `active`, `admin_flag`)
            VALUES ('.$fQoMembersId.','.$fQoGroupsId.','.$fActive.','.$fAdminFlag.')';

    if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'New member group permission (member: '.$memberName.', group: '.$groupName.', is admin: '.$fAdminFlag.')');
	return $returnState;
}

function saveFormDataNewQOMember($fMemberId) {

	// make all the strings safe
	$fFirstName 	= '\''.mysql_real_escape_string($_POST['first_name']).'\'';
	$fLastName 		= '\''.mysql_real_escape_string($_POST['last_name']).'\'';
	$fEmailAddress 	= '\''.mysql_real_escape_string($_POST['email_address']).'\'';
	$fPassword 		= '\''.mysql_real_escape_string($_POST['password']).'\'';
	$fActive 		= '\''.mysql_real_escape_string($_POST['active']).'\'';
	$fGroup			= (integer) mysql_real_escape_string($_POST['qo_groups_id']);
	
	$sql = 'INSERT INTO `qo_members` (`first_name`, `last_name`, `email_address`, `password`, `active`)
		VALUES ('.$fFirstName.','.$fLastName.','.$fEmailAddress.','.$fPassword.','.$fActive.')';
	
	if (mysql_query($sql)) {
		if ($fGroup > 0) {
			// We now insert into the qo_members_has_groups table also
			$sql = 'INSERT INTO `qo_members_has_groups` (`qo_members_id`, `qo_groups_id`, `active`, `admin_flag`)
				SELECT m.id, '.$fGroup.', \'true\', \'false\'
				FROM qo_members m WHERE email_address = '.$fEmailAddress;
       
			if (mysql_query($sql)) {
				$response = array('success'=>'true', 'save_message'=>'All Records Saved');
			} else {
				$response = array('success'=>'true', 'save_message'=>'Member Saved, group assignment failed.');
			}
		} else {
			$response = array('success'=>'true', 'save_message'=>'Member Saved.');
		}
		$auditState = 'SUCCESS';
    } else {
        $response = array ('success'=>'false');
		$auditState = 'FAILURE';
    }
	$json_response = json_encode($response);
	writeAudit ($fMemberId, $auditState, 'New member ('.$fEmailAddress.')');
	return $returnState;
	
}

function saveFormDataNewQOModuleFile($fMemberId) {

	// make all the strings safe
	$fQoModulesId	= (integer) mysql_real_escape_string($_POST['qo_modules_id']);
	$fName 			= '\''.mysql_real_escape_string($_POST['name']).'\'';
	$fType 			= '\''.mysql_real_escape_string($_POST['type']).'\'';
	$moduleName = getModuleNameById ($fQoModulesId);
	
	$sql = 'INSERT INTO `qo_modules_has_files` (`qo_modules_id`, `name`, `type`)
            VALUES ('.$fQoModulesId.','.$fName.','.$fType.')';

    if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'New module file (module: '.$moduleName.', file: '.$fName.')');
	return $returnState;
}

function saveFormDataNewQOModuleLauncher($fMemberId) {

	// make all the strings safe
	$fQoMembersId	= (integer) mysql_real_escape_string($_POST['qo_members_id']);
	$fQoGroupsId	= (integer) mysql_real_escape_string($_POST['qo_groups_id']);
	$fQoModulesId	= (integer) mysql_real_escape_string($_POST['qo_modules_id']);
	$fQoLaunchersId	= (integer) mysql_real_escape_string($_POST['qo_launchers_id']);
	$fSortOrder		= (integer) mysql_real_escape_string($_POST['sort_order']);
	$memberName = ($fQoMembersId == 0) ? "All Members" : getMemberNameById ($fQoMembersId);
	$groupName = ($fQoGroupsId == 0) ? "All Groups" : getGroupNameById ($fQoGroupsId);
	$moduleName = getModuleNameById ($fQoModulesId);
	$launcherName = getLauncherNameById ($fQoLaunchersId);

	$sql = 'INSERT INTO `qo_modules_has_launchers` (`qo_members_id`, `qo_groups_id`, `qo_modules_id`, `qo_launchers_id`, `sort_order`)
            VALUES ('.$fQoMembersId.','.$fQoGroupsId.','.$fQoModulesId.','.$fQoLaunchersId.','.$fSortOrder.')';

    if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'New module launcher (member: '.$memberName.', group: '.$groupName.', module: '.$moduleName.', launcher: '.$launcherName.'), '.$field.' set to '.$value.'.');
	return $returnState;
}

function saveFormDataNewQOModule($fMemberId) {
	// make all the strings safe
	$fModuleName 	= '\''.mysql_real_escape_string($_POST['moduleName']).'\'';
	$fModuleType 	= '\''.mysql_real_escape_string($_POST['moduleType']).'\'';
	$fModuleId 		= '\''.mysql_real_escape_string($_POST['fmoduleId']).'\'';
	$fVersion 		= '\''.mysql_real_escape_string($_POST['version']).'\'';
	$fAuthor 		= '\''.mysql_real_escape_string($_POST['author']).'\'';
	$fDescription 	= '\''.mysql_real_escape_string($_POST['description']).'\'';
	// ensure path ends with a '/'
	if (substr($_POST['path'], -1) != '/') {
		$fPath		= '\''.mysql_real_escape_string($_POST['path'].'/').'\'';
	} else {
		$fPath 		= '\''.mysql_real_escape_string($_POST['path']).'\'';
	}
	$fActive 		= '\''.mysql_real_escape_string($_POST['active']).'\'';
	$fFileJS		= mysql_real_escape_string($_POST['file_js']);
	$fFilePHP		= mysql_real_escape_string($_POST['file_php']);
	$fFileCSS		= mysql_real_escape_string($_POST['file_css']);
	$fGroup			=  (integer) mysql_real_escape_string($_POST['qo_groups_id']);
	$fLauncher		=  (integer) mysql_real_escape_string($_POST['qo_launchers_id']);
	$fInstallDB		= mysql_real_escape_string($_POST['has_db_script']);
	
	$sql = 'INSERT INTO `qo_modules` (`moduleName`, `moduleType`, `moduleId`, `version`, `author`, `description`, `path`, `active`)
		VALUES ('.$fModuleName.','.$fModuleType.','.$fModuleId.','.$fVersion.','.$fAuthor.','.$fDescription.','.$fPath.','.$fActive.')';

	if (mysql_query($sql)) {
		$save_message = 'Saved Module';
		$failed_message = '';
		// now we perform our other inserts
		// Insert our files into qo_modules_has_files
		if ($fFileJS != '') {
			$sql = 'INSERT INTO `qo_modules_has_files` (`qo_modules_id`, `name`, `type`)
				SELECT id, \''.$fFileJS.'\', \'javascript\'
				FROM qo_modules
				WHERE moduleId = '.$fModuleId;
			if (mysql_query($sql)) {
				$save_message .= ', javascript';
			} else {
				$failed_message .= 'javascript';
			}
		}
		if (trim($fFilePHP) != '') {
			$sql = 'INSERT INTO `qo_modules_has_files` (`qo_modules_id`, `name`, `type`)
				SELECT id, \''.$fFilePHP.'\', \'php\'
				FROM qo_modules
				WHERE moduleId = '.$fModuleId;
			if (mysql_query($sql)) {
				$save_message .= ', php';
			} else {
				$failed_message .= ' php';
			}
		}
		if (trim($fFileCSS) != '') {
			$sql = 'INSERT INTO `qo_modules_has_files` (`qo_modules_id`, `name`, `type`)
				SELECT id, \''.$fFileCSS.'\', \'css\'
				FROM qo_modules
				WHERE moduleId = '.$fModuleId;
			if (mysql_query($sql)) {
				$save_message .= ', css';
			} else {
				$failed_message .= ' css';
			}
		}
		if ($fGroup != ''){
			$sql = 'INSERT INTO `qo_groups_has_modules` (`qo_groups_id`, `qo_modules_id`, `active`)
				SELECT '.$fGroup.', id, \'true\'
				FROM qo_modules
				WHERE moduleId = '.$fModuleId;
			if (mysql_query($sql)) {
				$save_message .= ', group';
			} else {
				$failed_message .= ' group';
			}
		}
		if ($fLauncher != ''){
			$sql = 'INSERT INTO `qo_modules_has_launchers` (`qo_members_id`, `qo_groups_id`, `qo_modules_id`, `qo_launchers_id`, `sort_order`)
				SELECT 0, 0, id, '.$fLauncher.', 20
				FROM qo_modules
				WHERE moduleId = '.$fModuleId;
			if (mysql_query($sql)) {
				$save_message .= ', launcher';
			} else {
				$failed_message .= ' launcher';
			}
		}
		if ($fInstallDB == 'on') {
			$fDBFile = str_replace('\\','/',getcwd()).'/'.str_replace('\'','',$fPath).'db_install.sql';
			if (dbSqlExecute ($fMemberId, $fDBFile)) {
				$save_message .= ', DB-file';
			} else {
				$failed_message .= ' DB-file';
			}
		}
		if ($failed_message != '') {
			$failed_message = str_replace (' ', ', ', ltrim($failed_message));
			$save_message .= ', FAILED on '.$failed_message;
		}
		
		$response = array('success'=>'true', 'save_message'=>$save_message);
		$auditState = 'SUCCESS';
    } else {
        $response = array('success'=>'false');
		$auditState = 'FAILURE';
    }
	$json_response = json_encode($response);
	writeAudit ($fMemberId, $auditState, 'New module ('.$fModuleName.')');
	return $returnState;

}

function saveFormDataNewQOFile($fMemberId) {

	// make all the strings safe
	$fName 	= '\''.mysql_real_escape_string($_POST['name']).'\'';
	if (substr($_POST['path'], -1) != '/') {
		$fPath		= '\''.mysql_real_escape_string($_POST['path'].'/').'\'';
	} else {
		$fPath 		= '\''.mysql_real_escape_string($_POST['path']).'\'';
	}
	$fType 	= '\''.mysql_real_escape_string($_POST['type']).'\'';
	
	$sql = 'INSERT INTO `qo_files` (`name`, `path`, `type`, `default_file`)
		VALUES ('.$fName.','.$fPath.','.$fType.',\'FALSE\')';
	
    if (mysql_query($sql)) {
		$returnState = "{success:true}";
		$auditState = 'SUCCESS';
    } else {
        $returnState = "{success:false}";
		$auditState = 'FAILURE';
    }
	writeAudit ($fMemberId, $auditState, 'New plugin File (file: '.$fName.', path: '.$fPath.')');
	return $returnState;
}

// ============================================================================
// Get Lookup Data Functions
// ============================================================================

function getLookupQOGroupNames ($fMemberId) {

	$sql = 'select `id` as "KeyField", `name` as "DisplayField" from `qo_groups`';
	
	return getStoreData ("qo_groups", $sql);
	
}

function getLookupQOGroupNamesPlus ($fMemberId) {

	$sql = 'select 0 as "KeyField", "All Groups" as "DisplayField"
			union
			select `id` as "KeyField", `name` as "DisplayField" from `qo_groups`';
	
	return getStoreData ("qo_groups", $sql);
	
}

function getLookupQOMemberNames ($fMemberId) {

	$sql = 'select `id` as "KeyField", `email_address` as "DisplayField" from `qo_members`';
	
	return getStoreData ("qo_members", $sql);
	
}

function getLookupQOMemberNamesPlus ($fMemberId) {

	$sql = 'select 0 as "KeyField", "All Members" as "DisplayField"
			union
			select `id` as "KeyField", `email_address` as "DisplayField" from `qo_members`';
	
	return getStoreData ("qo_members", $sql);
	
}

function getLookupQOModuleNames ($fMemberId) {

	$sql = 'select `id` as "KeyField", `moduleName` as "DisplayField" from `qo_modules`';
	
	return getStoreData ("qo_modules", $sql);
	
}

function getLookupMyGroupNames ($fMemberId) {

	$sql = 'select `id` as "KeyField", `name` as "DisplayField" 
			from `qo_groups`
			where `id` in 
				(select a.`qo_groups_id`
				 from `qo_members_has_groups` a
				 where a.`qo_members_id` = ' . $fMemberId . '
				 and a.`admin_flag` = "true")';
	
	return getStoreData ("qo_groups", $sql);
	
}

function getLookupQOLauncherNames ($fMemberId) {

	$sql = 'select `id` as "KeyField", `name` as "DisplayField" from `qo_launchers`';
	
	return getStoreData ("qo_launchers", $sql);
	
}

?>