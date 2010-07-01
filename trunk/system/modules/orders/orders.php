<?php
class QoOrders {
	private static $memcache_connect;
	const MEMCACHE_HOST = '127.0.0.1';
	const MEMCACHE_PORT = 11211;
	
	private $os;

	public function __construct($os){
		$this->os = $os;
		QoOrders::$memcache_connect = new Memcache;
		QoOrders::$memcache_connect->connect(self::MEMCACHE_HOST, self::MEMCACHE_PORT);
	}
        
	public function log($file_name, $content){
		file_put_contents("/export/eBayBO/log/System/".$file_name."-".date("Y-m-d").".html", date("Y-m-d H:i:s")."   ".$content."<br>", FILE_APPEND);
	}
	
	public function getOrderId(){
            $type = 'ORM';
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
	    //echo $orderId;
            return $orderId;
        }
	
	private function getTransactionId(){
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
            return $TransactionId;
        }
	
	private function getShipmentId($type = 'SHM'){
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
            $ShipmentId = $type.$today.$row["curType"].str_repeat("0",(4-strlen($row["curId"]))).$row["curId"];   
            return $ShipmentId;
        }
	
	public function searchOrder(){
		$where = " where 1 = 1 ";
		
		if(!empty($_POST['id'])){
			$where .= " and o.id like '%".$_POST['id']."%'";
		}
		
		if(!empty($_POST['status'])){
			$where .= " and o.status = '".$_POST['status']."'";
		}
		
		if(!empty($_POST['remarks'])){
			$where .= " and o.remarks like '%".$_POST['remarks']."%'";
		}
		
		if(!empty($_POST['sellerId'])){
			$where .= " and o.sellerId = '".$_POST['sellerId']."'";
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
		
		if(!empty($_POST['skuId'])){
			$where_sku = " and od.skuId like '%".$_POST['skuId']."%'";
		}
		
		if(!empty($_POST['skuTitle'])){
			$where_sku = " and od.skuTitle like '%".$_POST['skuTitle']."%'";
		}
		
		if(!empty($_POST['itemId'])){
			$where_item = " and od.itemId like '%".$_POST['itemId']."%'";
		}
		
		if(!empty($_POST['itemTitle'])){
			$where_item = " and od.itemTitle like '%".$_POST['itemTitle']."%'";
		}
		
		if(!empty($_POST['modifiedOnFrom'])){
			$where .= " and o.modifiedOn   > '".$_POST['modifiedOnFrom']."'";
		}
		
		if(!empty($_POST['modifiedOnTo'])){
			$where .= " and o.modifiedOn   < '".$_POST['modifiedOnTo']."'";
		}
		
		if(!empty($where_sku) && !empty($where_item)){
			$count_sql = "select count(*) as num from (select distinct od.ordersId from qo_orders as o left join qo_orders_detail as od on o.id=od.ordersId ".$where.$where_sku.$where_item.") as total";
			//distinct
			$data_sql = "select o.id,o.status,o.sellerId,o.buyerId,o.ebayName,o.ebayEmail,o.grandTotalCurrency,o.grandTotalValue,ot.amountPayCurrency,sum(ot.amountPayValue) as amountPayValue 
			from (qo_orders as o left join qo_orders_transactions as ot on o.id=ot.ordersId) left join qo_orders_detail as od on o.id=od.ordersId ".$where.$where_sku.$where_item." group by o.id order by o.id desc limit ".$_POST['start'].",".$_POST['limit'];
		}elseif(!empty($where_sku)){
			$count_sql = "select count(*) as num from (select distinct od.ordersId from qo_orders as o left join qo_orders_detail as od on o.id=od.ordersId ".$where.$where_sku.") as total";
			
			$data_sql = "select o.id,o.status,o.sellerId,o.buyerId,o.ebayName,o.ebayEmail,o.grandTotalCurrency,o.grandTotalValue,ot.amountPayCurrency,sum(ot.amountPayValue) as amountPayValue 
			from (qo_orders as o left join qo_orders_transactions as ot on o.id=ot.ordersId) left join qo_orders_detail as od on o.id=od.ordersId ".$where.$where_sku." group by o.id order by o.id desc limit ".$_POST['start'].",".$_POST['limit'];
		}elseif(!empty($where_item)){
			$count_sql = "select count(*) as num from (select distinct od.ordersId from qo_orders as o left join qo_orders_detail as od on o.id=od.ordersId ".$where.$where_item.") as total";
			
			$data_sql = "select o.id,o.status,o.sellerId,o.buyerId,o.ebayName,o.ebayEmail,o.grandTotalCurrency,o.grandTotalValue,ot.amountPayCurrency,sum(ot.amountPayValue) as amountPayValue 
			from (qo_orders as o left join qo_orders_transactions as ot on o.id=ot.ordersId) left join qo_orders_detail as od on o.id=od.ordersId ".$where.$where_item." group by o.id order by o.id desc limit ".$_POST['start'].",".$_POST['limit'];
		}else{
			$count_sql = "select count(*) as num from qo_orders as o ".$where;
			
			$data_sql = "select o.id,o.status,o.sellerId,o.buyerId,o.ebayName,o.ebayEmail,o.grandTotalCurrency,o.grandTotalValue,ot.amountPayCurrency,sum(ot.amountPayValue) as amountPayValue 
			from qo_orders as o left join qo_orders_transactions as ot on o.id=ot.ordersId ".$where." 
			group by o.id order by o.id desc limit ".$_POST['start'].",".$_POST['limit'];
		}
		
		//echo $count_sql;
		//echo "\n";
		//echo $data_sql;
		//exit;
		$count_result = mysql_query($count_sql);
		$count_row = mysql_fetch_assoc($count_result);
		$totalCount = $count_row['num'];
		
		$data_result = mysql_query($data_sql);
		$order_array = array();
		while($data_row = mysql_fetch_assoc($data_result)){
			$order_array[] = $data_row;
		}
		//var_dump($order_array);
		echo json_encode(array('totalCount'=>$totalCount, 'records'=>$order_array));
		mysql_free_result($result);
	}
	
	
	public function getOrderInfo(){
		$sql = "select * from qo_orders where id = '".$_GET['id']."'";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		echo '['.json_encode($row).']';
		mysql_free_result($result);
	}
	
	public function createOrder(){
		$sql = "insert into qo_orders (id,status,shippingMethod,remarks,sellerId,buyerId,
		shippingFeeCurrency,shippingFeeValue,insuranceCurrency,insuranceValue,
		discountCurrency,discountValue,grandTotalCurrency,grandTotalValue,
		ebayName,ebayEmail,ebayAddress1,ebayAddress2,ebayCity,ebayStateOrProvince,ebayPostalCode,
		ebayCountry,ebayPhone,paypalName,paypalEmail,paypalAddress1,paypalAddress2,paypalCity,paypalStateOrProvince,
		paypalPostalCode,paypalCountry,paypalPhone,createdBy,createdOn,modifiedBy,modifiedOn) values ('".$_POST['id']."','".$_POST['status']."',
		'".$_POST['shippingMethod']."','".$_POST['remarks']."','".$_POST['sellerId']."','".$_POST['buyerId']."',
		'".$_POST['shippingFeeCurrency']."','".$_POST['shippingFeeValue']."','".$_POST['insuranceCurrency']."','".$_POST['insuranceValue']."',
		'".$_POST['discountCurrency']."','".$_POST['discountValue']."','".$_POST['grandTotalCurrency']."','".$_POST['grandTotalValue']."',
		'".$_POST['ebayName']."','".$_POST['ebayEmail']."','".$_POST['ebayAddress1']."','".$_POST['ebayAddress2']."',
		'".$_POST['ebayCity']."','".$_POST['ebayStateOrProvince']."','".$_POST['ebayPostalCode']."','".$_POST['ebayCountry']."',
		'".$_POST['ebayPhone']."','".$_POST['paypalName']."','".$_POST['paypalEmail']."','".$_POST['paypalAddress1']."',
		'".$_POST['paypalAddress2']."','".$_POST['paypalCity']."','".$_POST['paypalStateOrProvince']."','".$_POST['paypalPostalCode']."',
		'".$_POST['paypalCountry']."','".$_POST['paypalPhone']."','".$this->os->session->get_member_name()."','".date("Y-m-d H:i:s")."','".$this->os->session->get_member_name()."','".date("Y-m-d H:i:s")."')";
		$result = mysql_query($sql);
		if($result){
			echo 	'{success: true}';
			
		}else{
			echo 	'{success: false,
				  errors: {message: "can\'t create."}
				}';
		}
		
	}
	
	public function updateOrder(){
		$sql = "update qo_orders set status='".$_POST['status']."',shippingMethod='".$_POST['shippingMethod']."',
		remarks='".$_POST['remarks']."',sellerId='".$_POST['sellerId']."',buyerId='".$_POST['buyerId']."',
		shippingFeeCurrency='".$_POST['shippingFeeCurrency']."',shippingFeeValue='".$_POST['shippingFeeValue']."',
		insuranceCurrency='".$_POST['insuranceCurrency']."',insuranceValue='".$_POST['insuranceValue']."',
		discountCurrency='".$_POST['discountCurrency']."',discountValue='".$_POST['discountValue']."',
		grandTotalCurrency='".$_POST['grandTotalCurrency']."',grandTotalValue='".$_POST['grandTotalValue']."',
		ebayName='".$_POST['ebayName']."',ebayEmail='".$_POST['ebayEmail']."',ebayAddress1='".$_POST['ebayAddress1']."',
		ebayAddress2='".$_POST['ebayAddress2']."',ebayCity='".$_POST['ebayCity']."',ebayStateOrProvince='".$_POST['ebayStateOrProvince']."',
		ebayPostalCode='".$_POST['ebayPostalCode']."',ebayCountry='".$_POST['ebayCountry']."',ebayPhone='".$_POST['ebayPhone']."',
		paypalName='".$_POST['paypalName']."',paypalEmail='".$_POST['paypalEmail']."',paypalAddress1='".$_POST['paypalAddress1']."',
		paypalAddress2='".$_POST['paypalAddress2']."',paypalCity='".$_POST['paypalCity']."',paypalStateOrProvince='".$_POST['paypalStateOrProvince']."',
		paypalPostalCode='".$_POST['paypalPostalCode']."',paypalCountry='".$_POST['paypalCountry']."',paypalPhone='".$_POST['paypalPhone']."',
		modifiedBy='".$this->os->session->get_member_name()."',modifiedOn='".date("Y-m-d H:i:s")."' 
		where id = '".$_POST['id']."'";
		$result = mysql_query($sql);
		//echo $sql;
		if($result){
			echo 	'{success: true}';
			
		}else{
			echo 	'{success: false,
				  errors: {message: "can\'t create."}
				}';
		}
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
	
        public function getOrderDetailInfo(){
		$sql = "select * from qo_orders_detail where id='".$_GET['id']."'";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		echo '['.json_encode($row).']';
		mysql_free_result($result);
	}
        
        public function updateOrderDetailInfo(){
		$sql = "update qo_orders_detail set skuId='".$_POST['skuId']."',skuTitle='".$_POST['skuTitle']."',
		itemId='".$_POST['itemId']."',itemTitle='".$_POST['itemTitle']."',quantity='".$_POST['quantity']."',
		unitPriceCurrency='".$_POST['unitPriceCurrency']."',unitPriceValue='".$_POST['unitPriceValue']."' 
		where id = '".$_POST['id']."'";
		$result = mysql_query($sql);
		//echo $sql;
		if($result){
			echo 	1;
			
		}else{
			echo 0;
		}
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
	
	public function splitOrderDetail(){
		$sql_1 = "select ordersId,unitPriceCurrency,unitPriceValue,quantity from qo_orders_detail where id in (".$_POST['ids'].")";
		$result_1 = mysql_query($sql_1);
		$sumPriceValue = 0;
		while($row_1 = mysql_fetch_assoc($result_1)){
			$o_ordersId = $row_1['ordersId'];
			$sumPriceValue += $row_1['unitPriceValue'] * $row_1['quantity'];
		}
		
		$ordersId = $this->getOrderId();
		$sql_2 = "insert into qo_orders (id,status,shippingMethod,paymentMethod,sellerId,buyerId,shippingFeeCurrency,shippingFeeValue,
		insuranceCurrency,insuranceValue,grandTotalCurrency,grandTotalValue,ebayName,ebayEmail,ebayAddress1,ebayAddress2,
		ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone,
		paypalName,paypalEmail,paypalAddress1,paypalAddress2,paypalCity,paypalStateOrProvince,paypalPostalCode,
		paypalCountry,paypalPhone,createdBy,createdOn) select '".$ordersId."','W',shippingMethod,paymentMethod,sellerId,buyerId,shippingFeeCurrency,shippingFeeValue,
		insuranceCurrency,insuranceValue,grandTotalCurrency,'".$sumPriceValue."',ebayName,ebayEmail,ebayAddress1,ebayAddress2,
		ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone,
		paypalName,paypalEmail,paypalAddress1,paypalAddress2,paypalCity,paypalStateOrProvince,paypalPostalCode,
		paypalCountry,paypalPhone,'".$this->os->session->get_member_name()."','".date("Y-m-d H:i:s")."' from qo_orders where id = '".$o_ordersId."'";
		//echo $sql_2;
		$result_2 = mysql_query($sql_2);
		
		if($result_2){
			$sql_3 = "update qo_orders_detail set ordersId = '".$ordersId."' where id in (".$_POST['ids'].")";
			//echo $sql_3;
			$result_3 = mysql_query($sql_3);
			
			$sql_4 = "update qo_orders set grandTotalValue = grandTotalValue - ".$sumPriceValue." where id = '".$o_ordersId."'";
			$result_4 = mysql_query($sql_4);
		}
		if($result_2 && $result_3 && $result_4){
			echo "split as " . $ordersId;
		}else{
			echo "split failure!";
		}
	}
	
	public function addOrderTransaction(){
		$transactionId = $this->getTransactionId();
		$sql = "insert into qo_transactions (id,txnId,transactionTime,amountCurrency,amountValue,status,remarks,
		createdBy,createdOn,payeeId,payerId,payerName,payerEmail,payerAddressLine1,payerAddressLine2,payerCity,
		payerStateOrProvince,payerPostalCode,payerCountry) values ('".$transactionId."','".$_POST['txnId']."',
		'".$_POST['transactionTime']."','".$_POST['amountCurrency']."','".$_POST['amountValue']."','".$_POST['status']."',
		'".$_POST['remarks']."','".$this->os->session->get_member_name()."','".date("Y-m-d H:i:s")."','".$_POST['payeeId']."','".$_POST['payerId']."',
		'".$_POST['payerName']."','".$_POST['payerEmail']."','".$_POST['payerAddressLine1']."','".$_POST['payerAddressLine2']."',
		'".$_POST['payerCity']."','".$_POST['payerStateOrProvince']."','".$_POST['payerPostalCode']."','".$_POST['payerCountry']."')";
		$result = mysql_query($sql);
		if($result){
			$sql = "insert into qo_orders_transactions (ordersId,transactionsId,status,amountPayCurrency,amountPayValue,
			createdBy,createdOn) values ('".$_POST['ordersId']."','".$transactionId."',
			'A','".$_POST['amountCurrency']."','".$_POST['amountValue']."',
			'".$this->os->session->get_member_name()."','".date("Y-m-d H:i:s")."')";
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
	
	public function addOrderShipment(){
		//print_r($_POST);
		//exit;
		
		$sql = "select id,shippingMethod,shippingFeeCurrency,shippingFeeValue,ebayName,ebayEmail,ebayAddress1,ebayAddress2,ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone from qo_orders where id='".$_POST['id']."'";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		
		if($row['shippingMethod'] == 'R'){
			$shipmentId = $this->getShipmentId('SHR');
		}else{
			$shipmentId = $this->getShipmentId();
		}
		$sql = "insert into qo_shipments (id,ordersId,shipmentMethod,shipmentReason,status,shippingFeeCurrency,shippingFeeValue,shipToName,
		shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,
		shipToCountry,shipToPhoneNo,createdBy,createdOn,modifiedBy,modifiedOn) values ('".$shipmentId."','".$row['id']."','".$row['shippingMethod']."','".$_POST['shipmentReason']."',
		'N','".mysql_escape_string($row['shippingFeeCurrency'])."','".$row['shippingFeeValue']."','".mysql_escape_string($row['ebayName'])."',
		'".$row['ebayEmail']."','".mysql_escape_string($row['ebayAddress1'])."','".mysql_escape_string($row['ebayAddress2'])."','".mysql_escape_string($row['ebayCity'])."',
		'".mysql_escape_string($row['ebayStateOrProvince'])."','".$row['ebayPostalCode']."','".$row['ebayCountry']."','".$row['ebayPhone']."',
		'".$this->os->session->get_member_name()."','".date("Y-m-d H:i:s")."','".$this->os->session->get_member_name()."','".date("Y-m-d H:i:s")."')";
		//echo $sql;
		//$this->log("addOrderShipment", "insert qo_shipments: ".$sql);
		$result = mysql_query($sql);
		
		if($result){
			$sql_1 = "select skuId,skuTitle,itemId,itemTitle,quantity,barCode from qo_orders_detail where ordersId = '".$_POST['id']."'";
			$result_1 = mysql_query($sql_1);
			while($row_1 = mysql_fetch_assoc($result_1)){
				$sql_1 = "insert into qo_shipments_detail (shipmentsId,skuId,skuTitle,itemId,itemTitle,quantity,barCode) values
				('".$shipmentId."','".$row_1['skuId']."','".mysql_escape_string($row_1['skuTitle'])."','".$row_1['itemId']."','".mysql_escape_string($row_1['itemTitle'])."',
				'".$row_1['quantity']."','".$row_1['barCode']."')";
				$result_1 = mysql_query($sql_1);
				//$this->log("addOrderShipment", "insert qo_shipments_detail: ".$sql_1);
			}
		}
		echo $result;
	}
	
	public function deleteOrderShipment(){
		$shipment_id_array = explode(",", $_POST['ids']);
		foreach($shipment_id_array as $shipment_id){
			$sql = "delete from qo_shipments where id = '".$shipment_id."'";
			$result = mysql_query($sql);
			
			if($result){
				$sql_1 = "delete from qo_shipments_detail where shipmentsId = '".$shipment_id."'";
				$result_1 = mysql_query($sql_1);
			}
		}
		echo $result;
		
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
			'".$this->os->session->get_member_name()."','".date("Y-m-d H:i:s")."')";
		$result = mysql_query($sql);
		echo $result;
		
	}
	
	public function getOrderTransaction(){
		$sql = "select t.id,t.txnId,t.transactionTime,t.amountCurrency,t.amountValue,t.status from qo_transactions as t left join qo_orders_transactions as ot on t.id = ot.	transactionsId  where ot.ordersId ='".$_GET['id']."'";
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
	
	public function getConfigure(){
		$data = QoOrders::$memcache_connect->get("ordersConfigure");
		if($data == false){
			$sql = "select countries_name as id,countries_name as name from qo_countries";
			$result = mysql_query($sql);
			$countries_array = array();
			while($row = mysql_fetch_assoc($result)){
				$countries_array[] = array($row['id'], $row['name']);
			}
			
			$sql = "select id as id,id as name from qo_ebay_seller";
			$result = mysql_query($sql);
			$seller_array = array();
			while($row = mysql_fetch_assoc($result)){
				$seller_array[] = array($row['id'], $row['name']);
			}
			$data = array('countries'=>$countries_array, 'seller'=>$seller_array);
			QoOrders::$memcache_connect->set("ordersConfigure", $data);
			mysql_free_result($result);
		}
		echo json_encode($data);
	}
	
	public function __destruct(){
		QoOrders::$memcache_connect->close();
	}
	
}