<?php
class QoManage {
	
	private $os;

	public function __construct($os){
		$this->os = $os;
	}
        
        public function getAllMember(){
            print $this->os->member->get_all();
        }
        
        public function addMember(){
            
        }
        
        public function updateMember(){
            print $this->os->member->update();
        }
        
        public function deleteMember(){
            
        }
        
        public function getAllGroup(){
            print $this->os->group->get_all();
        }
        
        public function getGroupDomainPrivilege(){
            print $this->os->privilege->get_group_domain_privilege();
        }
        
        public function getPrivilegeInfo(){
        	$sql = "select id,name from qo_privileges";
			$result = mysql_query($sql);
			$privilege_array = array();
			while($row = mysql_fetch_assoc($result)){
				$sql_1 = "select pma.qo_privileges_id,pma.qo_modules_actions_id,ma.description from 
				qo_privileges_has_module_actions as pma left join qo_modules_actions as ma on 
				ma.id = pma.qo_modules_actions_id where qo_privileges_id=".$row['id'];
				$result_1 = mysql_query($sql_1);
				while($row_1 = mysql_fetch_assoc($result_1)){
					
				}
			}
        }
        
        /*
         INSERT INTO `ebaybo`.`qo_modules_actions` (
        `id` ,
        `qo_modules_id` ,
        `name` ,
        `description`
        )
        VALUES (
        NULL , '9', 'getAllEbaySeller', '获取所有eBay账户信息'
        );
        
        INSERT INTO `ebaybo`.`qo_privileges_has_module_actions` (
        `id` ,
        `qo_privileges_id` ,
        `qo_modules_actions_id`
        )
        VALUES (
        NULL , '3', '36'
        );
        */
        public function getAllEbaySeller(){
            $sql = "select * from qo_ebay_seller";
		$result = mysql_query($sql);
		$array = array();
		while($row = mysql_fetch_assoc($result)){
			$array[] = $row;
		}
		echo json_encode(array('result'=>$array));
		mysql_free_result($result);
        }
        /*
         INSERT INTO `ebaybo`.`qo_modules_actions` (
        `id` ,
        `qo_modules_id` ,
        `name` ,
        `description`
        )
        VALUES (
        NULL , '9', 'saveEbaySeller', '保存eBay账户信息'
        ), (
        NULL , '9', 'deleteEbaySeller', '删除eBay账户'
        );
        
        INSERT INTO `ebaybo`.`qo_privileges_has_module_actions` (
        `id` ,
        `qo_privileges_id` ,
        `qo_modules_actions_id`
        )
        VALUES (
        NULL , '3', '37'
        ), (
        NULL , '3', '38'
        );
        
        */
        public function saveEbaySeller(){
            
        }
        
        public function deleteEbaySeller(){
            
        }
}

?>