<?php
class QoTransactions {
	private static $memcache_connect;
	const MEMCACHE_HOST = '127.0.0.1';
	const MEMCACHE_PORT = 11211;
    
	private $os;

	public function __construct($os){
		$this->os = $os;
		QoTransactions::$memcache_connect = new Memcache;
		QoTransactions::$memcache_connect->connect(self::MEMCACHE_HOST, self::MEMCACHE_PORT);
	}
        
	public function getTransactionId(){
		$type = 'TRM';
		$today = date("Ym");
		$sql = "select curType,curId from sequence where curDate='$today' and type='$type'";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
	       
		if($row["curId"] >=9999){
		    // A-Z 66-91
		    $curType = chr(ord($row["curType"]) + 1);
		    $sql = "update  sequence  set curId = 1,curType='$curType' where curDate='$today' and type='$type'";
		    mysql_query($sql);
		}elseif($row["curId"] < 1 || $row["curId"] == null) {
		      $sql = "insert into sequence (type,curType,curDate,curId) value ('$type','A','$today',1)";
		      mysql_query($sql);
		}else {   
		    $sql = "update sequence set curId = curId + 1 where curDate='$today' and type='$type'";
		    $result = mysql_query($sql);
		}
	       
		$sql = "select curType,curId from sequence where curDate='$today' and type='$type'";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$TransactionId = $type.$today.$row["curType"].str_repeat("0",(4-strlen($row["curId"]))).$row["curId"];   
		echo $TransactionId;
		return $TransactionId;
	}
	
        public function searchTransaction(){
		$where = " where 1 = 1 ";
                
                if(!empty($_POST['transactionsId'])){
			$where .= " and t.id like '%".$_POST['transactionsId']."%'";
		}
                
                if(!empty($_POST['ordersId'])){
			$where .= " and ot.ordersId like '%".$_POST['ordersId']."%'";
		}
                
                if(!empty($_POST['payeeId'])){
			$where .= " and t.payeeId = '".$_POST['payeeId']."'";
		}
                
                if(!empty($_POST['status'])){
			$where .= " and t.status = '".$_POST['status']."'";
		}
                
                if(!empty($_POST['txnId'])){
			$where .= " and t.txnId like '%".$_POST['txnId']."%'";
		}
                
                if(!empty($_POST['payer'])){
			$where .= " and (t.payerId like '%".$_POST['payer']."%' or t.payerName like '%".$_POST['payer']."%')";
		}
                
                if(!empty($_POST['payerEmail'])){
			$where .= " and t.payerEmail like '%".$_POST['payerEmail']."%'";
		}
                
                if(!empty($_POST['payerAddressLine'])){
			$where .= " and (t.payerAddressLine1 like '%".$_POST['payerAddressLine']."%' or t.payerAddressLine2 like '%".$_POST['payerAddressLine']."%')";
		}
                
                if(!empty($_POST['transactionTimeFrom'])){
			$where .= " and t.transactionTime > '".$_POST['transactionTimeFrom']."'";
		}
                
                if(!empty($_POST['transactionTimeTo'])){
			$where .= " and t.transactionTime < '".$_POST['transactionTimeTo']."'";
		}
                
                if(!empty($_POST['createdOnFrom'])){
			$where .= " and t.createdOn > '".$_POST['createdOnFrom']."'";
		}
                
                if(!empty($_POST['createdOnTo'])){
			$where .= " and t.createdOn < '".$_POST['createdOnTo']."'";
		}
                
                if(!empty($_POST['modifiedOnFrom'])){
			$where .= " and t.modifiedOn > '".$_POST['modifiedOnFrom']."'";
		}
                
                if(!empty($_POST['modifiedOnTo'])){
			$where .= " and t.modifiedOn < '".$_POST['modifiedOnTo']."'";
		}
                
		if(empty($_POST['ordersId'])){
			$count_sql = "select count(*) as num from qo_transactions as t ".$where;
			$data_sql = "select t.id,t.txnId,t.transactionTime,t.status,t.amountCurrency,t.amountValue,t.payerId from qo_transactions as t ".$where." order by t.id desc limit ".$_POST['start'].",".$_POST['limit'];
		}else{
			$count_sql = "select count(*) from qo_transactions as t left join qo_orders_transactions as ot on t.id = ot.transactionsId ".$where;
			$data_sql = "select t.id,t.txnId,t.transactionTime,t.status,t.amountCurrency,t.amountValue,t.payerId from 
			qo_transactions as t left join qo_orders_transactions as ot on t.id = ot.transactionsId ".$where." order by t.id desc limit ".$_POST['start'].",".$_POST['limit'];
		}
		//echo $count_sql;
		//echo "\n";
		//echo $data_sql;
		
		$count_result = mysql_query($count_sql);
		$count_row = mysql_fetch_assoc($count_result);
		$totalCount = $count_row['num'];
		
                $data_result = mysql_query($data_sql);
		$transaction_array = array();
		while($data_row = mysql_fetch_assoc($data_result)){
                    $transaction_array[] = $data_row;
		}
		//var_dump($order_array);
		echo json_encode(array('totalCount'=>$totalCount, 'records'=>$transaction_array));
		mysql_free_result($result);
        }
        
        public function getTransactionInfo(){
		$sql = "select * from qo_transactions where id = '".$_GET['id']."'";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		//var_dump($row);
		echo '['.json_encode($row).']';
		mysql_free_result($result);
        }
	
	public function createTransaction(){
		$sql = "insert into qo_transactions (id,txnId,transactionTime,amountCurrency,amountValue,status,
		remarks,createdBy,createdOn,payeeId,payerId,payerName,payerEmail,payerAddressLine1,payerAddressLine2,
		payerCity,payerStateOrProvince,payerPostalCode,payerCountry) values ('".$_POST['id']."',
		'".$_POST['txnId']."','".$_POST['transactionTime']."','".$_POST['amountCurrency']."','".$_POST['amountValue']."',
		'".$_POST['status']."','".$_POST['remarks']."','".$this->os->session->get_member_name()."','".date("Y-m-d H:i:s")."',
		'".$_POST['payeeId']."','".$_POST['payerId']."','".$_POST['payerName']."','".$_POST['payerEmail']."',
		'".$_POST['payerAddressLine1']."','".$_POST['payerAddressLine2']."','".$_POST['payerCity']."','".$_POST['payerStateOrProvince']."',
		'".$_POST['payerPostalCode']."','".$_POST['payerCountry']."')";
		$result = mysql_query($sql);
		if($result){
			echo 	'{success: true}';
			
		}else{
			echo 	'{success: false,
				  errors: {message: "can\'t create."}
				}';
		}
	}
	
	public function updateTransaction(){
		$sql = "update qo_transactions set txnId='".$_POST['txnId']."',transactionTime='".$_POST['transactionTime']."',
		amountCurrency='".$_POST['amountCurrency']."',amountValue='".$_POST['amountValue']."',status='".$_POST['status']."',remarks='".$_POST['remarks']."',
		modifiedBy='".$this->os->session->get_member_name()."',modifiedOn='".date("Y-m-d H:i:s")."',payeeId='".$_POST['payeeId']."',payerId='".$_POST['payerId']."',
		payerName='".$_POST['payerName']."',payerEmail='".$_POST['payerEmail']."',payerAddressLine1='".$_POST['payerAddressLine1']."',payerAddressLine2='".$_POST['payerAddressLine2']."',
		payerCity='".$_POST['payerCity']."',payerStateOrProvince='".$_POST['payerStateOrProvince']."',payerPostalCode='".$_POST['payerPostalCode']."',payerCountry='".$_POST['payerCountry']."'
		where id='".$_POST['id']."'";
		$result = mysql_query($sql);
		if($result){
			echo 	'{success: true}';
			
		}else{
			echo 	'{success: false,
				  errors: {message: "can\'t create."}
				}';
		}
	}
	
	public function getTransactionOrder(){
		$sql = "select o.id as ordersId,o.grandTotalCurrency,o.grandTotalValue,o.createdBy,o.createdOn,o.modifiedBy,o.modifiedOn,o.status from qo_orders as o left join qo_orders_transactions as ot on o.id=ot.ordersId where ot.transactionsId = '".$_GET['id']."'";
		$result = mysql_query($sql);
		$i = 0;
		$transaction_order_array = array();
		while($row = mysql_fetch_assoc($result)){
                    $transaction_order_array[] = $row;
                    $i++;
		}
		//var_dump($order_array);
		echo json_encode(array('totalCount'=>$i, 'records'=>$transaction_order_array));
		mysql_free_result($result);
	}
        
        public function readMapTransactionOrder(){
		if(!empty($_POST['orderId'])){
			$sql = "select * from qo_orders where id like '%".$_POST['orderId']."%'";
			$result = mysql_query($sql);
			$order_transaction_array = array();
			while($row = mysql_fetch_assoc($result)){
				$order_transaction_array[] = $row;
				$i++;
			}
			echo json_encode(array('totalCount'=>$i, 'records'=>$order_transaction_array));
			mysql_free_result($result);
		}else{
			$sql = "select payeeId,payerId from qo_transactions where id='".$_GET['id']."'";
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			
			$sql = "select * from qo_orders where sellerId='".$row['payeeId']."' and buyerId= '".$row['payerId']."'";			 
			$result = mysql_query($sql);
			$i = 0;
			$transaction_order_array = array();
			while($row = mysql_fetch_assoc($result)){
			    $transaction_order_array[] = $row;
			    $i++;
			}
			//var_dump($order_array);
			echo json_encode(array('totalCount'=>$i, 'records'=>$transaction_order_array));
			mysql_free_result($result);
		}
	}
	
	public function mapTransactionOrder(){
		$sql = "select amountCurrency,amountValue from qo_transactions where id = '".$_POST['transactionsId']."'";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		
		$sql = "insert into qo_orders_transactions (ordersId,transactionsId,status,amountPayCurrency,amountPayValue,
			createdBy,createdOn) values ('".$_POST['ordersId']."','".$_POST['transactionsId']."',
			'A','".$row['amountCurrency']."','".$row['amountValue']."',
			'".$this->os->session->get_member_name()."','".date("Y-m-d H:i:s")."')";
		$result = mysql_query($sql);
		echo $result;
	}
	
	public function getSeller(){
		$seller_array = QoTransactions::$memcache_connect->get("seller");
		if($seller_array == false){
			$sql = "select id as id, id as name from qo_ebay_seller";
			$result = mysql_query($sql);
			$seller_array = array();
			while($row = mysql_fetch_assoc($result)){
				$seller_array[] = $row;
			}
			QoTransactions::$memcache_connect->set("seller", $seller_array);
			mysql_free_result($result);
		}
		echo json_encode($seller_array);
	}
	
	public function getCountries(){
		$countries_array = QoTransactions::$memcache_connect->get("countries");
		if($countries_array == false){
			$sql = "select countries_name as id, countries_name as name from qo_countries";
			$result = mysql_query($sql);
			$countries_array = array();
			while($row = mysql_fetch_assoc($result)){
				$countries_array[] = $row;
			}
			mysql_free_result($result);
			QoTransactions::$memcache_connect->set("countries", $countries_array);
		}
		echo json_encode($countries_array);
	}
	
	
	public function __destruct(){
		QoTransactions::$memcache_connect->close();
	}
}