<?php
class QoAttention {
	
	private $os;

	public function __construct($os){
		$this->os = $os;
	}
	
	public function getUnmapTransaction(){
		if(empty($_POST['start']) || empty($_POST['limit'])){
			$_POST['start'] = 0;
			$_POST['limit'] = 20;
		}
		$now = date("Y-m-d H:i:s");
		if(!empty($_POST['payeeId'])){
			$sql = "select count(*) as num from qo_transactions where payeeId='".$_POST['payeeId']."' and createdOn between '".$_POST['start_date']."' and '".$_POST['end_date']."' and id not in (select transactionsId from qo_orders_transactions where createdOn between '".$_POST['start_date']."' and '".$now."')";
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			
			$sql_1 = "select * from qo_transactions where payeeId='".$_POST['payeeId']."' and createdOn between '".$_POST['start_date']."' and '".$_POST['end_date']."' and id not in (select transactionsId from qo_orders_transactions where createdOn between '".$_POST['start_date']."' and '".$now."') order by id desc limit ".$_POST['start'].",".$_POST['limit'];
			$result_1 = mysql_query($sql_1);
			$array = array();
			while($row_1 = mysql_fetch_assoc($result_1)){
				$array[] = $row_1;
			}
		}else{
			$sql = "select count(*) as num from qo_transactions where createdOn between '".$_POST['start_date']."' and '".$_POST['end_date']."' and id not in (select transactionsId from qo_orders_transactions where createdOn between '".$_POST['start_date']."' and '".$now."')";
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			
			$sql_1 = "select * from qo_transactions where createdOn between '".$_POST['start_date']."' and '".$_POST['end_date']."' and id not in (select transactionsId from qo_orders_transactions where createdOn between '".$_POST['start_date']."' and '".$now."') order by id desc limit ".$_POST['start'].",".$_POST['limit'];
			$result_1 = mysql_query($sql_1);
			$array = array();
			while($row_1 = mysql_fetch_assoc($result_1)){
				$array[] = $row_1;
			}
		}
		echo json_encode(array('totalCount'=>$row['num'], 'records'=>$array));
		mysql_free_result($result);
	}
	
	public function getWaitingAuditRefund(){
		if(empty($_POST['start']) || empty($_POST['limit'])){
			$_POST['start'] = 0;
			$_POST['limit'] = 20;
		}
		$now = date("Y-m-d H:i:s");
		if(!empty($_POST['payeeId'])){
			$sql = "select count(*) as num from qo_transactions where payeeId='".$_POST['payeeId']."' and createdOn between '".$_POST['start_date']."' and '".$_POST['end_date']."' and status = 'A'";
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			
			$sql_1 = "select * from qo_transactions where payeeId='".$_POST['payeeId']."' and createdOn between '".$_POST['start_date']."' and '".$_POST['end_date']."' and status = 'A' order by id desc limit ".$_POST['start'].",".$_POST['limit'];
			$result_1 = mysql_query($sql_1);
			$array = array();
			while($row_1 = mysql_fetch_assoc($result_1)){
				$array[] = $row_1;
			}
		}else{
			$sql = "select count(*) as num from qo_transactions where createdOn between '".$_POST['start_date']."' and '".$_POST['end_date']."' and status = 'A'";
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			
			$sql_1 = "select * from qo_transactions where createdOn between '".$_POST['start_date']."' and '".$_POST['end_date']."' and status = 'A' order by id desc limit ".$_POST['start'].",".$_POST['limit'];
			$result_1 = mysql_query($sql_1);
			$array = array();
			while($row_1 = mysql_fetch_assoc($result_1)){
				$array[] = $row_1;
			}
		}
		echo json_encode(array('totalCount'=>$row['num'], 'records'=>$array));
		mysql_free_result($result);	
	}
	
	public function getWaitDealShipment(){
		if(empty($_POST['start']) || empty($_POST['limit'])){
			$_POST['start'] = 0;
			$_POST['limit'] = 20;
		}
		
		
	}
	
}