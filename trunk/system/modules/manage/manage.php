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
        
}

?>