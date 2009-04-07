<?php
class QoOrders {
	
	private $os;

	public function __construct($os){
		$this->os = $os;
	}
        
	private function getOrderId(){
            $type = 'ORD';
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
            $orderId = $type.$today.$row["curType"].str_repeat("0",(4-strlen($row["curId"]))).$row["curId"];   
            return $orderId;
        }
	
	private function getTransactionId(){
            $type = 'TRA';
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
            return $TransactionId;
        }
	
	public function searchOrder(){
		$where = " 1 = 1 ";
		
		if(!empty($_POST['id'])){
			$where .= " and o.id like '%".$_POST['id']."%'";
		}
		
		if(!empty($_POST['status'])){
			$where .= " and o.status = '".$_POST['status']."'";
		}
		
		if(!empty($_POST['remarks'])){
			$where .= " and o.remarks like '%".$_POST['remarks']."%'";
		}
		
		if(!empty($_POST['sellerId '])){
			$where .= " and o.remarks = '".$_POST['sellerId']."'";
		}
		
		if(!empty($_POST['buyerId'])){
			$where .= " and o.buyerId like '%".$_POST['buyerId']."%'";
		}
		
		if(!empty($_POST['buyerName'])){
			$where .= " and (o.ebayName like '%".$_POST['buyerName']."%' or o.paypalName like '%".$_POST['buyerName']."%')";
		}
		
		if(!empty($_POST['buyerEmail'])){
			$where .= " and (o.ebayEmail like '%".$_POST['buyerEmail']."%' or o.paypalEmail like '%".$_POST['buyerEmail']."%')";
		}
		
		if(!empty($_POST['buyerAddress'])){
			$where .= " and (o.ebayAddress1 like '%".$_POST['remarks']."%' or o.paypalAddress1 like '%".$_POST['remarks']."%')";
		}
		
		if(!empty($_POST['createdOnFrom'])){
			$where .= " and o.createdOn  > '".$_POST['createdOnFrom']."'";
		}
		
		if(!empty($_POST['createdOnTo'])){
			$where .= " and o.createdOn  < '".$_POST['createdOnTo']."'";
		}
		
		if(!empty($_POST['modifiedOnFrom'])){
			$where .= " and o.modifiedOn   > '".$_POST['modifiedOnFrom']."'";
		}
		
		if(!empty($_POST['modifiedOnTo'])){
			$where .= " and o.modifiedOn   < '".$_POST['modifiedOnTo']."'";
		}
		
		$sql = "select o.id,o.status,o.sellerId,o.buyerId,o.ebayName,o.ebayEmail,o.grandTotalCurrency,o.grandTotalValue,ot.amountPayCurrency,sum(ot.amountPayValue) as amountPayValue 
		from qo_orders as o left join qo_orders_transactions as ot on o.id=ot.ordersId where ".$where." 
		group by o.id";
		//echo $sql;
		$result = mysql_query($sql);
		$i = 0;
		$order_array = array();
		while($row = mysql_fetch_assoc($result)){
			$order_array[] = $row;
			$i++;
		}
		//var_dump($order_array);
		echo json_encode(array('totalCount'=>$i, 'records'=>$order_array));
		mysql_free_result($result);
	}
	
	
	public function getOrderInfo(){
		$sql = "select * from qo_orders where id = '".$_GET['id']."'";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		echo '['.json_encode($row).']';
		mysql_free_result($result);
	}
	
	public function saveOrderInfo(){
		$sql = "";
		$result = mysql_query($sql);
		//echo $sql;
		echo $result;
	}
	
	public function getOrderDetail(){
		$sql = "select * from qo_orders_detail where ordersId='".$_GET['id']."'";
		$order_detail_array = array();
		$result = mysql_query($sql);
		while($row = mysql_fetch_assoc($result)){
			$order_detail_array[] = $row;
			$i++;
		}
		echo json_encode(array('totalCount'=>$i, 'records'=>$order_detail_array));
		mysql_free_result($result);
	}
	
	public function addOrderDetail(){
		$sql = "insert qo_orders_detail (ordersId,skuId,skuTitle,itemId,itemTitle,quantity,unitPriceCurrency,unitPriceValue) values
		('".$_POST['ordersId']."','".$_POST['skuId']."','".$_POST['skuTitle']."','".$_POST['itemId']."','".$_POST['itemTitle']."',
		'".$_POST['quantity']."','".$_POST['unitPriceCurrency']."','".$_POST['unitPriceValue']."')";
		$result = mysql_query($sql);
		//echo $sql;
		echo $result;
	}
	
	public function deleteOrderDetail(){
		$sql = "delete from qo_orders_detail where id in (".$_POST['ids'].")";
		$result = mysql_query($sql);
		//echo $sql;
		echo $result;
	}
	
	public function addOrderTransaction(){
		$transactionId = $this->getTransactionId();
		$sql = "insert into qo_transactions (id,txnId,transactionTime,amountCurrency,amountValue,status,remarks,
		createdBy,createdOn,payeeId,payerId,payerName,payerEmail,payerAddressLine1,payerAddressLine2,payerCity,
		payerStateOrProvince,payerPostalCode,payerCountry) values ('".$transactionId."','".$_POST['txnId']."',
		'".$_POST['transactionTime']."','".$_POST['amountCurrency']."','".$_POST['amountValue']."','".$_POST['status']."',
		'".$_POST['remarks']."','','".date("Y-m-d H:i:s")."','".$_POST['payeeId']."','".$_POST['payerId']."',
		'".$_POST['payerName']."','".$_POST['payerEmail']."','".$_POST['payerAddressLine1']."','".$_POST['payerAddressLine2']."',
		'".$_POST['payerCity']."','".$_POST['payerStateOrProvince']."','".$_POST['payerPostalCode']."','".$_POST['payerCountry']."')";
		$result = mysql_query($sql);
		if($result){
			$sql = "insert into qo_orders_transactions (ordersId,transactionsId,status,amountPayCurrency,amountPayValue,
			createdBy,createdOn) values ('".$_POST['ordersId']."','".$transactionId."',
			'A','".$_POST['amountCurrency']."','".$_POST['amountValue']."',
			'','".date("Y-m-d H:i:s")."')";
			$result = mysql_query($sql);
			echo $result;
		}else{
			echo $result;
		}
	}
	
	public function deleteOrderTransaction(){
		$transaction_id_array = explode(",",$_POST['ids']);
		$where = "";
		foreach($transaction_id_array as $transaction_id){
			$where .= "'".$transaction_id."',";
		}
		
		$sql = "delete from qo_orders_transactions where transactionsId in (".substr($where, 0, -1).")";
		$result = mysql_query($sql);
		echo $result;
		/*	
		$sql = "delete from qo_transactions where id in (".substr($where, 0, -1).")";
		$result = mysql_query($sql);
		if($result){
			$sql = "delete from qo_orders_transactions where transactionsId in (".substr($where, 0, -1).")";
			$result = mysql_query($sql);
			echo $result;
		}else{
			echo $result;
		}
		*/
	}
	
	public function readMapOrderTransaction(){
		if(!empty($_POST['transactionId'])){
			$sql = "select * from qo_transactions where id like '%".$_POST['transactionId']."%'";
			$result = mysql_query($sql);
			$order_transaction_array = array();
			while($row = mysql_fetch_assoc($result)){
				$order_transaction_array[] = $row;
				$i++;
			}
			echo json_encode(array('totalCount'=>$i, 'records'=>$order_transaction_array));
			mysql_free_result($result);
		}else{
			$sql = "select sellerId,buyerId from qo_orders where id = '".$_GET['id']."'";
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			
			$sql = "select id,payerName,payerEmail,amountCurrency,amountValue,transactionTime,itemId from qo_transactions where payeeId = '".$row['sellerId']."' and payerId='".$row['buyerId']."'";
			$result = mysql_query($sql);
			$order_transaction_array = array();
			while($row = mysql_fetch_assoc($result)){
				$order_transaction_array[] = $row;
				$i++;
			}
			echo json_encode(array('totalCount'=>$i, 'records'=>$order_transaction_array));
			mysql_free_result($result);
		}
	}
	
	public function mapOrderTransaction(){
		$sql = "insert into qo_orders_transactions (ordersId,transactionsId,status,amountPayCurrency,amountPayValue,
			createdBy,createdOn) values ('".$_POST['ordersId']."','".$_POST['transactionId']."',
			'A','".$_POST['amountCurrency']."','".$_POST['amountValue']."',
			'','".date("Y-m-d H:i:s")."')";
		$result = mysql_query($sql);
		echo $result;
		
	}
	
	public function getOrderTransaction(){
		$sql = "select * from qo_transactions as t left join qo_orders_transactions as ot on t.id = ot.	transactionsId  where ot.ordersId ='".$_GET['id']."'";
		$result = mysql_query($sql);
		$order_transaction_array = array();
		while($row = mysql_fetch_assoc($result)){
			$order_transaction_array[] = $row;
			$i++;
		}
		echo json_encode(array('totalCount'=>$i, 'records'=>$order_transaction_array));
		mysql_free_result($result);
	}
	
	public function getOrderShipment(){
		$sql = "select * from qo_shipments where ordersId='".$_GET['id']."'";
		$result = mysql_query($sql);
		$order_shipment_array = array();
		while($row = mysql_fetch_assoc($result)){
			$order_shipment_array[] = $row;
			$i++;
		}
		echo json_encode(array('totalCount'=>$i, 'records'=>$order_shipment_array));
		mysql_free_result($result);
	}
	
	
}