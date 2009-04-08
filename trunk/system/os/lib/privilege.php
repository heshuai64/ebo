<?php
/*
 * qWikiOffice Desktop 0.8.1
 * Copyright(c) 2007-2008, Integrated Technologies, Inc.
 * licensing@qwikioffice.com
 * 
 * http://www.qwikioffice.com/license
 */

class privilege {
	
    private $os;
    
    public function __construct($os){
        $this->os = $os;
    }
    
    /** init() Initial page load or refresh has occured 
	  **/
	public function init(){
		if(isset($_SESSION['privileges'])){
	        unset($_SESSION['privileges']);
	    }
	}
    
    /** get_all() Will return all the privileges associated with a member for the current session.
	  *
	  * @access public
	  * @param {integer} $member_id The member id
	  * @param {integer} $group_id The group id
	  **/
	public function get_all($member_id, $group_id){
		$privileges = array();
		
		$member_id = $this->os->session->get_member_id();
		$group_id = $this->os->session->get_group_id();
		
		if($member_id != "" && $group_id != ""){
			
			unset($_SESSION['privileges']);
			/*
			$sql = "SELECT
				is_allowed,
				P.is_singular AS is_privilege_singular,
				A.name AS action,
				D.is_singular AS is_domain_singular,
				M.id AS module_id,
				M.module_id AS moduleId,
				G.importance
				FROM qo_groups_has_domain_privileges AS GDP
					-- Privileges Joins --
					INNER JOIN qo_privileges AS P ON P.id = GDP.qo_privileges_id 
					INNER JOIN qo_privileges_has_module_actions AS PA ON PA.qo_privileges_id = P.id
					INNER JOIN qo_modules_actions AS A ON A.id = PA.qo_modules_actions_id
					-- Domain Joins --
					INNER JOIN qo_domains AS D ON D.id = GDP.qo_domains_id
					INNER JOIN qo_domains_has_modules AS DM ON DM.qo_domains_id = D.id
					INNER JOIN qo_modules AS M ON M.id = DM.qo_modules_id
					-- Groups to member Joins --
					INNER JOIN qo_groups AS G ON G.id = GDP.qo_groups_id
					INNER JOIN qo_groups_has_members AS MG ON MG.qo_groups_id = G.id
				WHERE
				qo_members_id = ".$member_id."
				AND
				G.id = ".$group_id."
				ORDER BY
				A.name, G.importance DESC";
			
			$result = mysql_query($sql);
			
			// Initialise variables.
			$weight = -1; // Used to find out which privileges take precedence.
			$is_allowed = 0; // FALSE, initialise
			$prev_importance = '';
			$prev_action= '';
			$prev_module = '';
			$prev_is_allowed= '';
			$count = 0;
			$arr_data = array(); // Store temporary data
			
			// Loop through all matches
			while($row = mysql_fetch_assoc($result)){
				$action = $row["action"];
				$module_id = $row["module_id"]; // MySQL table id
				$moduleId = $row["moduleId"]; // moduleId property of the module
				$importance = $row["importance"];
				$is_allowed = (int) $row["is_allowed"];
				
				// We are only interested in the groups with the most importance (i.e. Some groups may have the same importance.)
				
				if ($count > 0 && $action === $prev_action && $module_id === $prev_module){
					if ($importance < $prev_importance || $prev_is_allowed === 0){
						continue;
					}
				}
				
				$new_weight = (int) $row["is_privilege_singular"] + (int) $row["is_domain_singular"];
				
				if ($new_weight > $weight){
					
					$weight = $new_weight;
				}
				else if ($new_weight == $weight && (int) $is_allowed === 1 && $is_allowed === 0){
					
					// We always give more weight to denials.
					$weight = $new_weight;
				}
				
				//echo "Group: ".$row["group"]."<br /> weight: ".$new_weight."<br />is_allowed: ".$row["is_allowed"]."<br>";
				
				$prev_importance = $importance;
				$prev_module = $module_id;
				$prev_action = $action;
				$prev_is_allowed = $is_allowed;
				
				$count++;
				
				// store value in sessions for next time
				// note: module id here referes to the MySQL record id
				$_SESSION['privileges'][$action][$moduleId] = $is_allowed;
				
				// store value in an array to return
				if($is_allowed){
					// note: moduleId here referes to the javascript moduleId
					$privileges[$action][] = $moduleId;
				}
				//$privileges[$action][$module_id] = $is_allowed;
			}
			*/

			$sql = "SELECT is_allowed,A.name AS action,M.id AS module_id,M.module_id AS moduleId
			FROM qo_groups_has_domain_privileges as GDP 
			INNER JOIN qo_privileges AS P on GDP.qo_privileges_id = P.id 
			INNER JOIN qo_privileges_has_module_actions AS PA ON PA.qo_privileges_id = P.id 
			INNER JOIN qo_groups AS G ON G.id = GDP.qo_groups_id 
			INNER JOIN qo_modules_actions AS A ON A.id = PA.qo_modules_actions_id 
			INNER JOIN qo_modules AS M ON M.id = A.qo_modules_id 
			WHERE G.id = ".$group_id;
			
			$result = mysql_query($sql);
			$is_allowed = 0;
			
			while($row = mysql_fetch_assoc($result)){
				$action = $row["action"];
				$module_id = $row["module_id"]; // MySQL table id
				$moduleId = $row["moduleId"]; // moduleId property of the module
				$is_allowed = (int) $row["is_allowed"];
				$_SESSION['privileges'][$action][$moduleId] = $is_allowed;
				if($is_allowed){
					// note: moduleId here referes to the javascript moduleId
					$privileges[$action][] = $moduleId;
				}
				
			}
			
		}
		
		return json_encode($privileges);
	} // end get_all()
	
	/** is_allowed() checks whether a member (in group) is allowed
	  * an action on a module.
	  *
	  * @param {string} $action The action name
	  * @param {integer} $module_id The module id
	  * @param {integer} $member_id The member id
	  * @param {integer} $group_id The group id
	  * @return {boolean}
	  **/
	public function is_allowed($action, $moduleId, $member_id, $group_id){
		
		if($member_id != "" && $group_id != "" && $action != "" && $moduleId != ""){
			
			// check if answer is already in sessions
			if(isset($_SESSION['privileges'][$action][$moduleId])){
				if($_SESSION['privileges'][$action][$moduleId]){
					return TRUE;
				}else{
					return FALSE;
				}
			}
			/*
			$sql = "SELECT
				is_allowed,
				P.is_singular AS is_privilege_singular,
				D.is_singular AS is_domain_singular,
				G.importance
				FROM
				qo_groups_has_domain_privileges AS GDP
					-- Privileges Joins --
					INNER JOIN qo_privileges AS P ON P.id = GDP.qo_privileges_id 
					INNER JOIN qo_privileges_has_module_actions AS PA ON PA.qo_privileges_id = P.id
					INNER JOIN qo_modules_actions AS A ON A.id = PA.qo_modules_actions_id
					-- Domain Joins --
					INNER JOIN qo_domains AS D ON D.id = GDP.qo_domains_id
					INNER JOIN qo_domains_has_modules AS DM ON DM.qo_domains_id = D.id
					INNER JOIN qo_modules AS M ON M.id = DM.qo_modules_id
					-- Groups to members Joins --
					INNER JOIN qo_groups AS G ON G.id = GDP.qo_groups_id
					INNER JOIN qo_groups_has_members AS MG ON MG.qo_groups_id = G.id
				WHERE
				qo_members_id = ".$member_id."
				AND
				G.id = ".$group_id."
				AND
				A.name = '".$action."'
				AND
				M.module_id = '".$moduleId."'
				ORDER BY
				G.importance DESC, G.name";
			
			$result = mysql_query($sql);
			
			// Initialise variables.
			$weight = -1; // Used to find out which privileges take precedence.
			$is_allowed = 0; // FALSE, initialise
			$prev_importance = '';
			$count = 0;
			
			while($row = mysql_fetch_assoc($result)){
				$importance = $row["importance"];
				$is_allowed = (int) $row["is_allowed"];
				
				// Only interested in the groups with the most importance (i.e. Some groups may have the same importance.)
				if ($count > 0 && $importance !== $prev_importance){
					break;
				}
				
				$new_weight = (int) $row["is_privilege_singular"] + (int) $row["is_domain_singular"];
				
				if ($new_weight > $weight){
					$weight = $new_weight;
				}else if($new_weight == $weight && (int) $is_allowed === 1 && (int) $is_allowed === 0){
					// Give more weight to denials.
					$weight = $new_weight;
				}
				
				$prev_importance = $importance;
				$count++;
				
			}
			*/
			$sql = "select qo_privileges_id from qo_groups_has_domain_privileges where qo_groups_id=".$group_id;
			$result = mysql_query($sql);
			while($row = mysql_fetch_assoc($result)){
				$sql_1 = "select count(*) as num from qo_privileges_has_module_actions as pma left join qo_modules_actions as ma on pma.qo_modules_actions_id=ma.id 
				where qo_privileges_id=".$row['qo_privileges_id']." and ma.name='".$action."'";
				//echo "\n".$sql_1."\n";
				$result_1 = mysql_query($sql_1);
				$row_1 = mysql_fetch_assoc($result_1);
				if($row_1['num'] > 0){
					$is_allowed = 1;
					break;
				}
			}
						
		}
		
		// Store value in sessions for next time
		$_SESSION['privileges'][$action][$moduleId] = $is_allowed;
		
		// Return answer
		if ($is_allowed){
			return true;
		}else{
			return false;
		}
	} // end is_allowed()
	
	
	
	public function get_group_domain_privilege(){
	    $group_domain_privilege = array();
	    /*$sql = "select g.id as group_id,g.name as group_name,d.id as domain_id,d.name as domain_name,p.id as privilege_id,p.name as privilege_name from qo_groups_has_domain_privileges as gdp
	    inner join qo_groups as g on gdp.qo_groups_id=g.id
	    inner join qo_domains as d on gdp.qo_domains_id=d.id
	    inner join qo_privileges as p on gdp.qo_privileges_id=p.id order by group_name";
	    
	    $result = mysql_query($sql);
	    while($row = mysql_fetch_assoc($result)){
		$group_domain_privilege[] = $row;
	    }
	    */
	    
	    /*
	    $domain_array = array();
	    $sql = "select * from qo_domains";
	    $result = mysql_query($sql);
	    while($row = mysql_fetch_assoc($result)){
		$domain_array[] = $row;
	    }
	    */
	    
	    $group_array = array();
	    $sql = "select * from qo_groups";
	    $result = mysql_query($sql);
	    while($row = mysql_fetch_assoc($result)){
		$group_array[] = $row;
	    }
	    //var_dump($group_array);
	    
	    $privilege_array = array();
	    $sql = "select * from qo_privileges";
	    $result = mysql_query($sql);
	    while($row = mysql_fetch_assoc($result)){
		$privilege_array[] = $row;
	    }
	    //var_dump($privilege_array);
	    
	    $group_privilege_array = array();
	    $sql = "select qo_groups_id,qo_privileges_id,is_allowed from qo_groups_has_domain_privileges order by qo_groups_id";
	    $result = mysql_query($sql);
	    while($row = mysql_fetch_assoc($result)){
		$group_privilege_array[] = $row;
	    }
	    //var_dump($group_privilege_array);
	    $group_domain_privilege = array('group'=>$group_array, 'privilege'=>$privilege_array, 'group_domain_privilege'=>$group_privilege_array);
	    //print_r($group_domain_privilege);
	    return '['.json_encode($group_domain_privilege).']';
	}
}
?>