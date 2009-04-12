<?php
class QoTransactions {
	
	private $os;

	public function __construct($os){
		$this->os = $os;
	}
        
        public function searchTransaction(){
		$where = " 1 = 1 ";
                
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
                
		if(!empty($_POST['ordersId'])){
			$sql = "select t.id,t.txnId,t.transactionTime,t.status,t.amountCurrency,t.amountValue,t.payerId from
			qo_transactions as t where ".$where;
		}else{
			$sql = "select t.id,t.txnId,t.transactionTime,t.status,t.amountCurrency,t.amountValue,t.payerId from 
			qo_transactions as t left join qo_orders_transactions as ot on t.id = ot.transactionsId where ".$where."
			group by t.id";
		}
                $result = mysql_query($sql);
		$i = 0;
		$transaction_array = array();
		while($row = mysql_fetch_assoc($result)){
                    $transaction_array[] = $row;
                    $i++;
		}
		//var_dump($order_array);
		echo json_encode(array('totalCount'=>$i, 'records'=>$transaction_array));
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
	
	public function saveTransactionInfo(){
		
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
		$sql = "insert into qo_orders_transactions (ordersId,transactionsId,status,amountPayCurrency,amountPayValue,
			createdBy,createdOn) values ('".$_POST['ordersId']."','".$_POST['transactionsId']."',
			'A','".$_POST['amountCurrency']."','".$_POST['amountValue']."',
			'','".date("Y-m-d H:i:s")."')";
		$result = mysql_query($sql);
		echo $result;
	}
	
	public function getSeller(){
		$sql = "select id as id, id as name from qo_ebay_seller";
		$result = mysql_query($sql);
		$seller_array = array();
		while($row = mysql_fetch_assoc($result)){
			$seller_array[] = $row;
		}
		echo json_encode($seller_array);
		mysql_free_result($result);
	}
	
	public function getCountries(){
		$sql = "select countries_name as id, countries_name as name from qo_countries";
		$result = mysql_query($sql);
		$countries_array = array();
		while($row = mysql_fetch_assoc($result)){
			$countries_array[] = $row;
		}
		echo json_encode($countries_array);
		mysql_free_result($result);
	}
}