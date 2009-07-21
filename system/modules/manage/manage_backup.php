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
            $sql = "insert into qo_members (email_address,password,active) values
            ('".$_POST['email_address']."','".$_POST['password']."','".$_POST['active']."')";
            $result = mysql_query($sql);
            $members_id = mysql_insert_id();
            if($result){
                $sql = "insert into qo_groups_has_members (qo_groups_id,qo_members_id,active,admin) values
                ('".$_POST['group_id']."','".$members_id."','1','1')";
                $result = mysql_query($sql);
                echo $result;
            }else{
                echo $result;
            } 
        }
        
        public function updateMember(){
            print $this->os->member->update();
        }
        
        public function deleteMember(){
            $sql = "delete from qo_members where id='".$_POST['id']."'";
            $result = mysql_query($sql);
            if($result){
                $sql = "delete from qo_groups_has_members where qo_members_id='".$_POST['id']."'";
                $result = mysql_query($sql);
                echo $result;
            }else{
                echo $result;
            }
        }
        
        public function getAllGroup(){
            print $this->os->group->get_all();
        }
        
        public function addGroup(){
            $sql = "insert into qo_groups (name,description,active) values
            ('".$_POST['name']."','".$_POST['description']."','".$_POST['active']."')";
            $result = mysql_query($sql);
            $group_id = mysql_insert_id();
            
            $sql = "select id from qo_privileges";
            $result = mysql_query($sql);
            while($row = mysql_fetch_assoc($result)){
                $sql_1 = "insert into qo_groups_has_domain_privileges (qo_groups_id,qo_privileges_id) values ('$group_id','".$row['id']."')";
                $result_1 = mysql_query($sql_1);
            }  
            
            echo "1";
        }
        
        public function updateGroup(){
            $sql = "update qo_groups set name='".$_POST['name']."',description='".$_POST['description']."',
            active='".$_POST['active']."' where id='".$_POST['id']."'";
            $result = mysql_query($sql);
            echo $result;
        }
        
        public function deleteGroup(){
            $sql = "delete from qo_groups where id = '".$_POST['id']."'";
            $result = mysql_query($sql);
            
            if($result){
                $sql = "delete from qo_groups_has_domain_privileges where qo_groups_id = '".$_POST['id']."'";
                $result = mysql_query($sql);
            }
            echo $result;
        }
        
        public function getGroupDomainPrivilege(){
            print $this->os->privilege->get_group_domain_privilege();
        }
        
        public function updateGroupPrivilege(){
            //echo $_GET['data'];
            $gpa = explode(",", $_GET['data']);
            foreach($gpa as $gp){
                //echo $gp."\n";
                $a = explode("=", $gp);
                $b = explode("_", $a[0]);
                $sql = "update qo_groups_has_domain_privileges set is_allowed = '".$a[1]."' where
                qo_groups_id = '".$b[0]."' and qo_privileges_id = '".$b[1]."'";
                //echo $sql;
                $result = mysql_query($sql);
            }
            echo "1";
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
            
        public function addEbaySeller(){
            $sql = "select count(*) as num from qo_ebay_seller where id = '".$_POST['id']."'";
            $result = mysql_query($sql);
            $row = mysql_fetch_assoc($result);
            if($row['num'] ==0 ){
                $sql = "insert into qo_ebay_seller (id,email,status,devId,appId,cert,token,tokenExpiry,currency,site) values
                ('".$_POST['id']."','".$_POST['email']."','".$_POST['status']."','".$_POST['devId']."','".$_POST['appId']."',
                '".$_POST['cert']."','".$_POST['token']."','".$_POST['tokenExpiry']."','".$_POST['currency']."','".$_POST['site']."')";
                $result = mysql_query($sql);
                echo $result;
            }else{
                echo 0;
            }
        }
        
        public function updateEbaySeller(){
            $sql = "update qo_ebay_seller set email='".$_POST['email']."',emailPassword='".$_POST['emailPassword']."',status='".$_POST['status']."',
            devId='".$_POST['devId']."',appId='".$_POST['appId']."',cert='".$_POST['cert']."',
            token='".$_POST['token']."',tokenExpiry='".$_POST['tokenExpiry']."',currency='".$_POST['currency']."',
            site='".$_POST['site']."' where id = '".$_POST['id']."'";
            $result = mysql_query($sql);
            //echo $sql;
            echo $result;
        }
        
        public function deleteEbaySeller(){
            $sql = "delete from qo_ebay_seller where id = '".$_POST['id']."'";
            $result = mysql_query($sql);
            echo $result;
        }
	
        public function getAllEbayProxy(){
            $sql = "select id as ebay_seller_id,id as ebay_seller_name from qo_ebay_seller";
            $result = mysql_query($sql);
            $seller_array = array();
            while($row = mysql_fetch_assoc($result)){
                    $seller_array[] = $row;
            }
            
            $sql = "select * from qo_ebay_proxy";
            $result = mysql_query($sql);
            $proxy_array = array();
            while($row = mysql_fetch_assoc($result)){
                    $proxy_array[] = $row;
            }
            echo json_encode(array('result'=>array('seller'=>$seller_array,'proxy'=>$proxy_array)));
            mysql_free_result($result);
        }
        
        public function addEbayProxy(){
            $sql = "select count(*) as num from qo_ebay_proxy where ebay_seller_id = '".$_POST['ebay_seller_id']."'";
            //echo $sql;
            $result = mysql_query($sql);
            $row = mysql_fetch_assoc($result);
            if($row['num'] ==0 ){
                $sql = "insert into qo_ebay_proxy (ebay_seller_id,proxy_host,proxy_port) values ('".$_POST['ebay_seller_id']."','".$_POST['proxy_host']."','".$_POST['proxy_port']."')";
                //echo $sql;
                $result = mysql_query($sql);
                echo $result;
            }else{
                echo 0;
            }
        }
        
        public function updateEbayProxy(){
            $sql = "update qo_ebay_proxy set ebay_seller_id='".$_POST['ebay_seller_id']."',proxy_host='".$_POST['proxy_host']."',proxy_port='".$_POST['proxy_port']."' where id = '".$_POST['id']."'";
            $result = mysql_query($sql);
            echo $result;
        }
        
        public function deleteEbayProxy(){
            $sql = "delete from qo_ebay_proxy where id = '".$_POST['id']."'";
            $result = mysql_query($sql);
            echo $result;
        }
				
}

?>