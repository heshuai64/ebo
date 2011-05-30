<?php
define ('__DOCROOT__', '/export/eBayBO');
define ('FETCH_HOUR' , 6);

ini_set("memory_limit","256M");
set_time_limit(600);

    class PayPal{
        private static $database_connect;
        //const IPN_VALIDATE_HOST = 'ssl://www.sandbox.paypal.com';
        const IPN_VALIDATE_HOST = 'ssl://www.paypal.com';
        const NVPAPI_HOST = 'https://api-3t.paypal.com/nvp';
        private $start_time;
	private $end_time;
	private $config;
	private $account;
	
        public function __construct(){
	    $this->config = parse_ini_file(__DOCROOT__ . '/config.ini', true);
	    
            PayPal::$database_connect = mysql_connect($this->config['database']['host'], $this->config['database']['user'], $this->config['database']['password']);

            if (!PayPal::$database_connect) {
                echo "Unable to connect to DB: " . mysql_error(PayPal::$database_connect);
                exit;
            }
            
	    mysql_query("SET NAMES 'UTF8'", PayPal::$database_connect);
	    
            if (!mysql_select_db($this->config['database']['name'], PayPal::$database_connect)) {
                echo "Unable to select mydbname: " . mysql_error(PayPal::$database_connect);
                exit;
            }
        }
        
        private function log($file_name, $content, $type="log"){
	    if(!file_exists($this->config['log']['paypal'].date("Ymd"))){
		mkdir($this->config['log']['paypal'].date("Ymd"), 0777);
	    }
	    
	    if(!file_exists($this->config['log']['paypal'].date("Ymd")."/".$this->account)){
		mkdir($this->config['log']['paypal'].date("Ymd")."/".$this->account, 0777);
	    }

            file_put_contents($this->config['log']['paypal'].date("Ymd")."/".$this->account."/".$file_name."-".date("Y-m-d").".".$type, date("Y-m-d H:i:s")."   ".$content."\n", FILE_APPEND);
        }
        
        private function getTransactionId(){
            $type = 'TRC';
            $today = date("Ym");
            $sql = "select curType,curId from sequence where curDate='$today' and type='$type'";
            $result = mysql_query($sql, PayPal::$database_connect);
            $row = mysql_fetch_assoc($result);
           
            if($row["curId"] >=9999){
                // A-Z 66-91
                $curType = chr(ord($row["curType"]) + 1);
                $sql = "update  sequence  set curId = 1,curType='$curType' where curDate='$today' and type='$type'";
                mysql_query($sql, PayPal::$database_connect);
            }elseif($row["curId"] < 1 || $row["curId"] == null) {
                  $sql = "insert into sequence (type,curType,curDate,curId) value ('$type','A','$today',1)";
                  mysql_query($sql, PayPal::$database_connect);
            }else {   
                $sql = "update sequence set curId = curId + 1 where curDate='$today' and type='$type'";
                $result = mysql_query($sql, PayPal::$database_connect);
            }
           
            $sql = "select curType,curId from sequence where curDate='$today' and type='$type'";
            $result = mysql_query($sql, PayPal::$database_connect);
            $row = mysql_fetch_assoc($result);
            $transactionId = $type.$today.$row["curType"].str_repeat("0",(4-strlen($row["curId"]))).$row["curId"];   
            return $transactionId;
        }
    
        private function getEbayOrderId($buyerId, $item_number_string){
	    $sevenDayAgo = date("Y-m-d H:i:s", time() - (7 * 24 * 60 * 60));
	    $itemNumber = $item_number_string;
	    $oitemNumber = $item_number_string;
                
            $sql = "select id from qo_orders where buyerId='".$buyerId."' and status = 'W' and createdOn > '".$sevenDayAgo."' order by createdOn desc";
            $this->log('ipn_deal', "getEbayOrderId id: ".$sql);
            $result = mysql_query($sql, PayPal::$database_connect);
            while ($row = mysql_fetch_assoc($result)) {
                $sql_1 = "select itemId from qo_orders_detail where ordersId='".$row['id']."'";
                $this->log('ipn_deal',"getEbayOrderId itemId: ".$sql_1);
                $result_1 = mysql_query($sql_1, PayPal::$database_connect);
                $itemId_array = array();
                while ($row_1 = mysql_fetch_assoc($result_1)) {
                    $itemId_array[] = $row_1['itemId'];
                }
                
                if($itemId_array[0] == $itemNumber && count($itemId_array) == 1){
                    $this->log('ipn_deal',"getEbayOrderId (S) itemNumber ~ itemId: ".$itemNumber." ~ ".$itemId_array[0]);
                    return $row['id'];
                }
                
                $success = false;
                $itemNumber = "hs".$itemNumber;
                $success_num = 0;
                foreach ($itemId_array as $itemId){
                    if(strpos($itemNumber, $itemId)){
                        $success = true;
                        $success_num++;
                    }else{
                        $success = false;
                        break;
                    }
                }
                
                if($success == true && count($itemId_array) == $success_num){
                    $this->log('ipn_deal',"getEbayOrderId (M) itemNumber ~ itemId_array: ".$itemNumber." ~ ".print_r($itemId_array, true));
                    return $row['id'];
                }
                
                $itemNumber = $oitemNumber;
                $this->log('ipn_deal', "getEbayOrderId ~end one loop~");
            }
            return "";
        }
        
        private function getEbayOrderIdFromTxnId($txnId){
	    if($txnId != ""){
                $sql = "select id from qo_transactions where txnId = '$txnId'";
                $this->log('ipn_deal',"getEbayOrderIdFromTxnId: transactionId ".$sql);
                $result = mysql_query($sql, PayPal::$database_connect);
                $row = mysql_fetch_assoc($result);
                
                $sql = "select ordersId from qo_orders_transactions where transactionsId = '".$row['id']."'";
                $this->log('ipn_deal',"getEbayOrderIdFromTxnId: ordersId ".$sql);
                $result = mysql_query($sql, PayPal::$database_connect);
                $row = mysql_fetch_assoc($result);
                return $row['ordersId'];
	    }else{
	    	return "";
	    }
	    
	}
        
        private function getOrderIdFromTxnId($txnId){
            if($txnId != ""){
                $sql = "select id from qo_transactions where txnId = '$txnId'";
                $this->log('paypal_api', "getOrderIdFromTxnId: transactionId ".$sql."<br>", "html");
                $result = mysql_query($sql, PayPal::$database_connect);
                $row = mysql_fetch_assoc($result);
                
                $sql = "select ordersId from qo_orders_transactions where transactionsId = '".$row['id']."'";
                $this->log('paypal_api', "getOrderIdFromTxnId: ordersId ".$sql."<br>", "html");
                $result = mysql_query($sql, PayPal::$database_connect);
                $row = mysql_fetch_assoc($result);
                return $row['ordersId'];
	    }else{
	    	return "";
	    }
        }
        
        private function updateEbayOrderAddressInfo($ordersId, $ipn_data){
            $paypalName = $ipn_data['address_name'];
            $paypalEmail  = $ipn_data['payer_email'];
            $address = split("\n",$ipn_data['address_street']);
            $paypalAddress1 = $address[0];
            $paypalAddress2 = $address[1];
            $paypalCity = $ipn_data['address_city'];
            $paypalStateOrProvince = $ipn_data['address_state'];
            $paypalPostalCode = $ipn_data['address_zip'];
            $paypalCountry = $ipn_data['address_country'];
            
            $sql = "update qo_orders set paypalName='".mysql_real_escape_string($paypalName)."',paypalEmail='".$paypalEmail."',paypalAddress1='".mysql_real_escape_string($paypalAddress1)."',
            paypalAddress2='".mysql_real_escape_string($paypalAddress2)."',paypalCity='".mysql_real_escape_string($paypalCity)."',paypalStateOrProvince='".mysql_real_escape_string($paypalStateOrProvince)."',
            paypalPostalCode='".mysql_real_escape_string($paypalPostalCode)."',paypalCountry='".$paypalCountry."' where id='".$ordersId."'";
            $this->log('ipn_deal',"updateEbayOrderAddressInfo: ".$sql);
            $result = mysql_query($sql, PayPal::$database_connect);
        }
        
        private function getEbayOrderPay($ordersId){
            $sql = "select grandTotalValue from qo_orders where id='$ordersId'";
            $this->log('ipn_deal',"getEbayOrderPay: ".$sql);
            $result = mysql_query($sql, PayPal::$database_connect);
            $row = mysql_fetch_assoc($result);
	    return $row['grandTotalValue'];
        }
        
        private function updateEbayOrderStatus($ordersId, $status){
            $modifiedBy = "Paypal";
            $modifiedOn= date("Y-m-d H:i:s");
            $sql = "update qo_orders set status='$status',modifiedBy='$modifiedBy',modifiedOn='$modifiedOn' where id='$ordersId'";
            $this->log('ipn_deal',"updateEbayOrderStatus: ".$sql);
            $result = mysql_query($sql, PayPal::$database_connect);
        }
        
        private function getShipmentStatus($ordersId){
            $sql = "select status from qo_shipments where ordersId='$ordersId'";
            $this->log('ipn_deal',"getShipmentStatus: ".$sql);
            $result = mysql_query($sql, PayPal::$database_connect);
            $row = mysql_fetch_assoc($result);
	    return $row['status'];
	}
	
	private function updateShipmentStatus($ordersId, $status){
            $modifiedBy = "Paypal";
            $modifiedOn= date("Y-m-d H:i:s");
            $sql = "update qo_shipments set status='$status',modifiedBy='$modifiedBy',modifiedOn='$modifiedOn' where ordersId='$ordersId'";
            $this->log('ipn_deal',"updateShipmentStatus: ".$sql);
            $result = mysql_query($sql, PayPal::$database_connect);
	}
        
	private function getShipmentId(){
            $type = 'SHI';
            $today = date("Ym");
            $sql = "select curType,curId from sequence where curDate='$today' and type='$type'";
            $result = mysql_query($sql, PayPal::$database_connect);
            $row = mysql_fetch_assoc($result);
           
            if($row["curId"] >=9999){
                // A-Z 66-91
                $curType = chr(ord($row["curType"]) + 1);
                $sql = "update  sequence  set curId = 1,curType='$curType' where curDate='$today' and type='$type'";
                mysql_query($sql, PayPal::$database_connect);
            }elseif($row["curId"] < 1 || $row["curId"] == null) {
                  $sql = "insert into sequence (type,curType,curDate,curId) value ('$type','A','$today',1)";
                  mysql_query($sql, PayPal::$database_connect);
            }else {   
                $sql = "update sequence set curId = curId + 1 where curDate='$today' and type='$type'";
                $result = mysql_query($sql, PayPal::$database_connect);
            }
           
            $sql = "select curType,curId from sequence where curDate='$today' and type='$type'";
            $result = mysql_query($sql, PayPal::$database_connect);
            $row = mysql_fetch_assoc($result);
            $shipmentId = $type.$today.$row["curType"].str_repeat("0",(4-strlen($row["curId"]))).$row["curId"];   
            return $shipmentId;
        }
	
	private function createShipmentFromPayPal($ordersId){
	    $sql = "select count(*) as num from qo_shipments where ordersId = '$ordersId'";
	    $result = mysql_query($sql, PayPal::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    if($row['num'] == 0){
                /*
		$sql = "select shippingMethod,ebayName,ebayEmail,ebayAddress1,ebayAddress2,ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone,shippingFeeCurrency,shippingFeeValue from qo_orders where id = '$ordersId'";
		$result = mysql_query($sql, PayPal::$database_connect);
		$row = mysql_fetch_assoc($result);
		
		$shipmentId = $this->getShipmentId();
		
		$sql = "insert into qo_shipments (id,ordersId,status,shippingFeeCurrency,shippingFeeValue,shipToName,
		shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,
		shipToCountry,shipToPhoneNo,createdBy,createdOn,modifiedBy,modifiedOn) values ('".$shipmentId."','".$ordersId."',
		'N','".$row['shippingFeeCurrency']."','".$row['shippingFeeValue']."','".$row['ebayName']."',
		'".$row['ebayEmail']."','".$row['ebayAddress1']."','".$row['ebayAddress2']."','".$row['ebayCity']."',
		'".$row['ebayStateOrProvince']."','".$row['ebayPostalCode']."','".$row['ebayCountry']."','".$row['ebayPhone']."',
		'PayPal','".date("Y-m-d H:i:s")."','PayPal','".date("Y-m-d H:i:s")."')";
		
		$this->log('ipn_deal',"createShipment: ".$sql);
		$result = mysql_query($sql, PayPal::$database_connect);
		
		$sql = "select skuId,skuTitle,itemId,itemTitle,quantity,barCode from qo_orders_detail where ordersId = '$ordersId'";
		$result = mysql_query($sql, PayPal::$database_connect);
		while($row = mysql_fetch_assoc($result)){
		    $sql_1 = "insert into qo_shipments_detail (shipmentsId,skuId,skuTitle,itemId,itemTitle,quantity,barCode) values
		    ('".$shipmentId."','".$row['skuId']."','".$row['skuTitle']."','".$row['itemId']."','".$row['itemTitle']."',
		    '".$row['quantity']."','".$row['barCode']."')";
		    $this->log('ipn_deal',"createShipmentDetail: ".$sql_1);
		    $result_1 = mysql_query($sql_1, PayPal::$database_connect);
		}
                */
                $shipmentsId = $this->getShipmentId();
                $sql = "insert into qo_shipments (id,ordersId,status,shipmentMethod,remarks,shippingFeeCurrency,shippingFeeValue,shipToName,
                shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,shipToCountry,
                shipToPhoneNo,createdBy,createdOn,modifiedBy,modifiedOn) select '".$shipmentsId."','".$ordersId."','N',shippingMethod,remarks,
                shippingFeeCurrency,shippingFeeValue,ebayName,ebayEmail,ebayAddress1,ebayAddress2,ebayCity,
                ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone,'eBay','".date("Y-m-d H:i:s")."','eBay','".date("Y-m-d H:i:s")."' from qo_orders where id = '".$ordersId."'";
                $this->log('ipn_deal',"createShipmentFromPayPal: insert shipment ".$sql);
                $result = mysql_query($sql, eBay::$database_connect);
                if($result){
                        $sql = "insert into qo_shipments_detail (shipmentsId,skuId,skuTitle,itemId,itemTitle,quantity,barCode) 
                        select '".$shipmentsId."',skuId,skuTitle,itemId,itemTitle,quantity,barCode from qo_orders_detail where ordersId='".$ordersId."'";
                        $result = mysql_query($sql, eBay::$database_connect);
                        $this->log('ipn_deal',"createShipmentFromPayPal: add shipment detail ".$sql);
                }
	    }
	}
	
        private function updateEbayOrderAndShipment($ordersId, $ipn_data){
            switch($ipn_data['payment_status']){	
                case "Completed":
                    $pay = $this->getEbayOrderPay($ordersId);
                    if($ipn_data['mc_gross'] > ($pay + ($pay * 0.02))){
                            $this->updateEbayOrderStatus($ordersId, "C");
                    }elseif($ipn_data['mc_gross'] < ($pay - ($pay * 0.02))){
                            $this->updateEbayOrderStatus($ordersId, "S");
                    }else{
                            $this->updateEbayOrderStatus($ordersId, "P");
                            //$this->createShipmentFromPayPal($ordersId);
                    }
                break;
                        
                case "Refunded":
                    /*
                    $pay = $this->getEbayOrderPay($ordersId);
                    if($ipn_data['mcGross'] >= (0 - ($pay + ($pay * 0.02))) && $ipn_data['mcGross'] <= (0 - ($pay - ($pay * 0.02)))){
                            $this->updateEbayOrderStatus($ordersId, "X");
                            $shipment_status = $this->getShipmentStatus($ordersId);
                            if($shipment_status == "N" || $shipment_status == "K" ){
                                    $this->updateShipmentStatus($ordersId, 'X');
                                    if($shipment_status == "K"){
                                            $this->send_mail($ordersId." Refunded, Shipment Packed!");
                                    }
                            }
                    }else{
                            $shipment_status = $this->getShipmentStatus($ordersId);
                            if($shipment_status == "N" || $shipment_status == "K" ){
                                    $this->updateShipmentStatus($ordersId, 'H');
                                    if($shipment_status == "K"){
                                            $this->send_mail($ordersId." Refunded, Shipment Packed!");
                                    }
                            }
                    }
                    */
                break;
           
                case "Reversed":
                    $this->updateEbayOrderStatus($ordersId, "V");
                    $shipment_status = $this->getShipmentStatus($ordersId);
                    if($shipment_status == "N" ){
                        $this->updateShipmentStatus($ordersId, 'H');
                    }
                break;
                        
                case "Canceled_Reversal":
                    $this->updateEbayOrderStatus($ordersId, "P");
                    //$this->createShipmentFromPayPal($ordersId);
                break;	   		
            }

	}
               
        private function matchEbayOrder($ipn_data, $item_number_string, $transactionId){
            $ordersId = $this->getEbayOrderId($ipn_data['auction_buyer_id'], $item_number_string);
            $ordersId = ($ordersId!='')?$ordersId:($this->getEbayOrderIdFromTxnId($ipn_data['parent_txn_id']));
            if($ordersId !=''){
                $status = "A";
                $amountPaidCurrency = $ipn_data['mc_currency'];
                $amountPaidValue = $ipn_data['mc_gross'];
                $createdBy = "Paypal";
                $createdOn = date("Y-m-d H:i:s");
                $modifiedBy = "Paypal";
                $modifiedOn = date("Y-m-d H:i:s");
                $sql = "insert into qo_orders_transactions (ordersId,transactionsId,status,amountPayCurrency,amountPayValue,createdBy,
                createdOn,modifiedBy,modifiedOn) values ('$ordersId','$transactionId','$status','$amountPaidCurrency','$amountPaidValue',
                '$createdBy','$createdOn','$modifiedBy','$modifiedOn')";
                $this->log('ipn_deal',"orderstransactions: ".$sql);
                $result = mysql_query($sql, PayPal::$database_connect);
                $this->updateEbayOrderAddressInfo($ordersId, $ipn_data);
                $this->updateEbayOrderAndShipment($ordersId, $ipn_data);
            }else{
                $this->log('ipn_deal',"mapEbayOrder failure, transactionId: ".$transactionId);
            }
        }
        
	private function getPayeeIdFromEmail($business){
	    $sql = "select id from qo_ebay_seller where email='".$business."'";
	    $result = mysql_query($sql);
	    $row = mysql_fetch_assoc($result);
	    return $row['id'];
	}
	
        private function addEbayTransaction($ipn_data){
            switch($ipn_data['payment_status']){
		
		case "Pending":
		    $status = "N";
		break;
		
                case "Completed":
                    $status = "P";
                break;
            
                case "Reversed":
                    $status = "V";
                break;
            
                case "Canceled_Reversal":
                    $status = "C";
                break;
            
                case "Refunded":
                    $status = "R";
                break;
            }
            $i = 1;
            //$item_number_string = $ipn_data['item_number'];
	    $item_number_string = "";
            while(!empty($ipn_data['item_number'.$i])){
                $item_number_string .= ",".$ipn_data['item_number'.$i];
                $i++;
            }
	    
	    //if items only one, cut off ","
	    //if($i == 2){
		$item_number_string = substr($item_number_string, 1);
	    //}
	    
            $address = split("\n",$ipn_data['address_street']);
  	    $payerAddressLine1 = $address[0];
  	    $payerAddressLine2 = $address[1];
            
	    if($status == "V"){
		$sql = "select count(*) as num from qo_transactions where txnId='".$ipn_data['txn_id']."' and status = 'V'";
		$result = mysql_query($sql, PayPal::$database_connect);
		$row = mysql_fetch_assoc($result);
	    }elseif($status == "C"){
		$sql = "select count(*) as num from qo_transactions where txnId='".$ipn_data['txn_id']."' and status = 'C'";
		$result = mysql_query($sql, PayPal::$database_connect);
		$row = mysql_fetch_assoc($result);
	    }else{
		$sql = "select count(*) as num from qo_transactions where txnId='".$ipn_data['txn_id']."'";
		$result = mysql_query($sql, PayPal::$database_connect);
		$row = mysql_fetch_assoc($result);
	    }
	    
	    if($row['num'] == 0){
                $transactionId = $this->getTransactionId();
		$payeeId = $this->getPayeeIdFromEmail($_POST['business']);
		
                $sql = "insert into qo_transactions (id,txnId,transactionTime,amountCurrency,amountValue,status,remarks,createdBy,createdOn,payeeId,
                payerId,payerName,payerEmail,payerAddressLine1,payerAddressLine2,payerCity,payerStateOrProvince,
                payerPostalCode,payerCountry,itemId) values ('".$transactionId."','".$_POST['txn_id']."','".date("Y-m-d H:i:s",strtotime($_POST['payment_date']))."',
                '".$_POST['mc_currency']."','".$_POST['mc_gross']."','".$status."','".mysql_real_escape_string($_POST['memo'])."','PayPal','".date("Y-m-d H:i:s")."','".mysql_real_escape_string($payeeId)."',
                '".mysql_real_escape_string($_POST['auction_buyer_id'])."','".mysql_real_escape_string($_POST['address_name'])."',
                '".$_POST['payer_email']."','".mysql_real_escape_string($payerAddressLine1)."','".mysql_real_escape_string($payerAddressLine2)."',
                '".mysql_real_escape_string($_POST['address_city'])."','".mysql_real_escape_string($_POST['address_state'])."',
                '".$_POST['address_zip']."','".$_POST['address_country']."','".$item_number_string."')";
                
		$e = 0;
                $result = mysql_query($sql, PayPal::$database_connect);
                while($result == false){
		    if($e > 5){
			$this->log('ipn_deal', "<font color='red'>addEbayTransaction: Insert Trasaction while Error!</font>");
			return false;
		    }
                    sleep(rand(0,10));
                    $transactionId = $this->getTransactionId();
                    $sql = "insert into qo_transactions (id,txnId,transactionTime,amountCurrency,amountValue,status,remarks,createdBy,createdOn,payeeId,
                    payerId,payerName,payerEmail,payerAddressLine1,payerAddressLine2,payerCity,payerStateOrProvince,
                    payerPostalCode,payerCountry,itemId) values ('".$transactionId."','".$_POST['txn_id']."','".date("Y-m-d H:i:s",strtotime($_POST['payment_date']))."',
                    '".$_POST['mc_currency']."','".$_POST['mc_gross']."','".$status."','".mysql_real_escape_string($_POST['memo'])."','PayPal','".date("Y-m-d H:i:s")."','".mysql_real_escape_string($_POST['business'])."',
                    '".mysql_real_escape_string($_POST['auction_buyer_id'])."','".mysql_real_escape_string($_POST['address_name'])."',
                    '".$_POST['payer_email']."','".mysql_real_escape_string($payerAddressLine1)."','".mysql_real_escape_string($payerAddressLine2)."',
                    '".mysql_real_escape_string($_POST['address_city'])."','".mysql_real_escape_string($_POST['address_state'])."',
                    '".$_POST['address_zip']."','".$_POST['address_country']."','".$item_number_string."')";
                
                    $result = mysql_query($sql, PayPal::$database_connect);
		    $e++;
		}
                $this->log('ipn_deal', "addEbayTransaction: ".$sql);
            }
            $this->matchEbayOrder($ipn_data, $item_number_string, $transactionId);
            
        }
        
        public function ipn(){
            if(empty($_POST['payment_status'])){
                return 0;
            }
	    
	    $this->log("ipn_data", $_POST['txn_id'] ."\n");
	    
            // read the post from PayPal system and add 'cmd'
            $req = 'cmd=_notify-validate';
            
            foreach ($_POST as $key => $value) {
                $value = urlencode(stripslashes($value));
                $req .= "&$key=$value";
            }
            
            $this->log("ipn_data", "IPN:\n" . print_r($_POST, true));
            // post back to PayPal system to validate
            $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
            $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
            $fp = fsockopen (self::IPN_VALIDATE_HOST, 443, $errno, $errstr, 30);

	    $this->log("ipn_data", "Send Back PayPal:\n". $header . $req);
	    
            if (!$fp) {
            // HTTP ERROR
            } else {
                fputs ($fp, $header . $req);
                while (!feof($fp)) {
                    $res = fgets ($fp, 1024);
                    if (strcmp ($res, "VERIFIED") == 0) {
                        $this->log("ipn_data", $_POST['txn_id']."   VERIFIED");
                        //eBay IPN
                        //if(!empty($_POST['auction_buyer_id']) && !empty($_POST['auction_closing_date'])){
                            $this->addEbayTransaction($_POST);
                        //}else{
                            
                        //}
                        // check the payment_status is Completed
                        // check that txn_id has not been previously processed
                        // check that receiver_email is your Primary PayPal email
                        // check that payment_amount/payment_currency are correct
                        // process payment
                    }
                    else if (strcmp ($res, "INVALID") == 0) {
                        // log for manual investigation
                        $this->log("ipn_data", $_POST['txn_id']."   INVALID");
                    }
                }
                fclose ($fp);
            }
	    $this->log("ipn_data", "\n\n\n********************************************************************************\n\n\n");
        }
    
        
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	private function getOrderPay($ordersId){
            $sql = "select grandTotalValue from qo_orders where id='$ordersId'";
            $this->log('paypal_api',"getOrderPay: ".$sql."<br>", "html");
            $result = mysql_query($sql, PayPal::$database_connect);
            $row = mysql_fetch_assoc($result);
	    return $row['grandTotalValue'];
        }
	
        private function getOrderId($buyerId, $item_number_string){
	    $sevenDayAgo = date("Y-m-d H:i:s", time() - (7 * 24 * 60 * 60));
	    $itemNumber = $item_number_string;
	    $oitemNumber = $item_number_string;
                
            $sql = "select id from qo_orders where buyerId='".$buyerId."' and status = 'W' and createdOn > '".$sevenDayAgo."' order by createdOn desc";
            $this->log('paypal_api', "getOrderId id: ".$sql."<br>", "html");
            $result = mysql_query($sql, PayPal::$database_connect);
            while ($row = mysql_fetch_assoc($result)) {
                $sql_1 = "select itemId from qo_orders_detail where ordersId='".$row['id']."'";
                $this->log('paypal_api',"getOrderId itemId: ".$sql_1."<br>", "html");
                $result_1 = mysql_query($sql_1, PayPal::$database_connect);
                $itemId_array = array();
                while ($row_1 = mysql_fetch_assoc($result_1)) {
                    $itemId_array[] = $row_1['itemId'];
                }
                
                if($itemId_array[0] == $itemNumber && count($itemId_array) == 1){
                    $this->log('paypal_api',"getOrderId (S) itemNumber ~ itemId: ".$itemNumber." ~ ".$itemId_array[0]."<br>", "html");
                    return $row['id'];
                }
                
                $success = false;
                $itemNumber = "hs".$itemNumber;
                $success_num = 0;
                foreach ($itemId_array as $itemId){
                    if(strpos($itemNumber, $itemId)){
                        $success = true;
                        $success_num++;
                    }else{
                        $success = false;
                        break;
                    }
                }
                
                if($success == true && count($itemId_array) == $success_num){
                    $this->log('paypal_api',"getOrderId (M) itemNumber ~ itemId_array: ".$itemNumber." ~ ".print_r($itemId_array, true)."<br>", "html");
                    return $row['id'];
                }
                
                $itemNumber = $oitemNumber;
                $this->log('paypal_api', "getOrderId ~end one loop~<br>", "html");
            }
            return "";
        }
        
	private function updateOrderAddressInfo($ordersId, $api_data){
            $paypalName = $api_data['SHIPTONAME'];
            $paypalEmail  = $api_data['EMAIL'];

            $paypalAddress1 = $api_data['SHIPTOSTREET'];
            $paypalAddress2 = $api_data['SHIPTOSTREET2'];
            $paypalCity = $api_data['SHIPTOCITY'];
            $paypalStateOrProvince = $api_data['SHIPTOSTATE'];
            $paypalPostalCode = $api_data['SHIPTOZIP'];
            $paypalCountry = $api_data['SHIPTOCOUNTRYNAME'];
            
            $sql = "update qo_orders set paypalName='".mysql_real_escape_string($paypalName)."',paypalEmail='".$paypalEmail."',paypalAddress1='".mysql_real_escape_string($paypalAddress1)."',
            paypalAddress2='".mysql_real_escape_string($paypalAddress2)."',paypalCity='".mysql_real_escape_string($paypalCity)."',paypalStateOrProvince='".mysql_real_escape_string($paypalStateOrProvince)."',
            paypalPostalCode='".mysql_real_escape_string($paypalPostalCode)."',paypalCountry='".$paypalCountry."' where id='".$ordersId."'";
            $this->log('paypal_api',"updateOrderAddressInfo: ".$sql."<br>", "html");
            $result = mysql_query($sql, PayPal::$database_connect);
        }
	
	private function updateOrderStatus($ordersId, $status){
            $modifiedBy = "Paypal";
            $modifiedOn= date("Y-m-d H:i:s");
            $sql = "update qo_orders set status='$status',modifiedBy='$modifiedBy',modifiedOn='$modifiedOn' where id='$ordersId'";
            $this->log('paypal_api',"updateOrderStatus: ".$sql."<br>", "html");
            $result = mysql_query($sql, PayPal::$database_connect);
        }
	
	private function updateOrderAndShipment($ordersId, $api_data){
            switch($api_data['PAYMENTSTATUS']){	
                case "Completed":
                    $pay = $this->getOrderPay($ordersId);
                    if($api_data['AMT'] > ($pay + ($pay * 0.02))){
                            $this->updateOrderStatus($ordersId, "C");
                    }elseif($api_data['AMT'] < ($pay - ($pay * 0.02))){
                            $this->updateOrderStatus($ordersId, "S");
                    }else{
                            $this->updateOrderStatus($ordersId, "P");
                            //$this->createShipmentFromPayPal($ordersId);
                    }
                break;
                        
		case "Pending":
		    $this->updateOrderStatus($ordersId, "E");
		break;
	    
                case "Refunded":
                    /*
                    $pay = $this->getOrderPay($ordersId);
                    if($ipn_data['mcGross'] >= (0 - ($pay + ($pay * 0.02))) && $ipn_data['mcGross'] <= (0 - ($pay - ($pay * 0.02)))){
                            $this->updateOrderStatus($ordersId, "X");
                            $shipment_status = $this->getShipmentStatus($ordersId);
                            if($shipment_status == "N" || $shipment_status == "K" ){
                                    $this->updateShipmentStatus($ordersId, 'X');
                                    if($shipment_status == "K"){
                                            $this->send_mail($ordersId." Refunded, Shipment Packed!");
                                    }
                            }
                    }else{
                            $shipment_status = $this->getShipmentStatus($ordersId);
                            if($shipment_status == "N" || $shipment_status == "K" ){
                                    $this->updateShipmentStatus($ordersId, 'H');
                                    if($shipment_status == "K"){
                                            $this->send_mail($ordersId." Refunded, Shipment Packed!");
                                    }
                            }
                    }
                    */
                break;
           
                case "Reversed":
                    $this->updateOrderStatus($ordersId, "V");
                    $shipment_status = $this->getShipmentStatus($ordersId);
                    if($shipment_status == "N" ){
                        $this->updateShipmentStatus($ordersId, 'H');
                    }
                break;
                        
                case "Canceled_Reversal":
                    $this->updateOrderStatus($ordersId, "P");
                    //$this->createShipmentFromPayPal($ordersId);
                break;	   		
            }

	}
	
	private function matchOrder($api_data, $item_number_string, $transactionId){
            $ordersId = $this->getOrderId($api_data['BUYERID'], $item_number_string);
            $ordersId = ($ordersId!='')?$ordersId:($this->getOrderIdFromTxnId($api_data['PARENTTRANSACTIONID']));
            if($ordersId !=''){
                $status = "A";
                $amountPaidCurrency = $api_data['CURRENCYCODE'];
                $amountPaidValue = $api_data['AMT'];
                $createdBy = "Paypal";
                $createdOn = date("Y-m-d H:i:s");
                $modifiedBy = "Paypal";
                $modifiedOn = date("Y-m-d H:i:s");
                $sql = "insert into qo_orders_transactions (ordersId,transactionsId,status,amountPayCurrency,amountPayValue,createdBy,
                createdOn,modifiedBy,modifiedOn) values ('$ordersId','$transactionId','$status','$amountPaidCurrency','$amountPaidValue',
                '$createdBy','$createdOn','$modifiedBy','$modifiedOn')";
                $this->log('paypal_api',"orderstransactions: ".$sql."<br>", "html");
                $result = mysql_query($sql, PayPal::$database_connect);
                $this->updateOrderAddressInfo($ordersId, $api_data);
                $this->updateOrderAndShipment($ordersId, $api_data);
            }else{
                $this->log('paypal_api', "<font color='red'>mapOrder failure, transactionId: ".$transactionId."</font><br>", "html");
            }
        }
	
        private function PPHttpPost($userName, $password, $sgnature, $methodName_, $nvpStr_) {
            $API_Endpoint = PayPal::NVPAPI_HOST;
            // Set up your API credentials, PayPal end point, and API version.
            $API_UserName = urlencode($userName);
            $API_Password = urlencode($password);
            $API_Signature = urlencode($sgnature);

            $version = urlencode('51.0');
    
            // Set the curl parameters.
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
    
            // Turn off the server and peer verification (TrustManager Concept).
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
	    //curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	    
            // Set the API operation, version, and API signature in the request.
            $nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";
            echo $nvpreq."\n";
            // Set the request as a POST FIELD for curl.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
    
            // Get response from the server.
            $httpResponse = curl_exec($ch);
    
            if(!$httpResponse) {
                    exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
            }
    
            // Extract the response details.
            $httpResponseAr = explode("&", $httpResponse);
    
            $httpParsedResponseAr = array();
            foreach ($httpResponseAr as $i => $value) {
                    $tmpAr = explode("=", $value);
                    if(sizeof($tmpAr) > 1) {
                            $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
                    }
            }
    
            if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
                    exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
            }
    
            return $httpParsedResponseAr;
        }

        public function TransactionSearch($userName, $password, $sgnature, $start_time, $end_time){
            
            $nvpStr .= "&STARTDATE=$start_time";
            $nvpStr .= "&ENDDATE=$end_time";
            $httpParsedResponseAr = $this->PPHttpPost($userName, $password, $sgnature, 'TransactionSearch', $nvpStr);
            
	    foreach($httpParsedResponseAr as $key=>$value){
		$httpParsedResponseAr[$key] = urldecode($value);
                //echo $key.": ".urldecode($value);
                //echo "<br>";
            }
	    
	    $this->log($userName."-TransactionSearch", print_r($httpParsedResponseAr, true)."<br><br><br>", "html");
            
            
            $i = 0;
            while(!empty($httpParsedResponseAr['L_TRANSACTIONID'.$i])){
                if(in_array($httpParsedResponseAr['L_TYPE'.$i], array("Payment", "Refund"))){
                    $this->GetTransactionDetails($userName, $password, $sgnature, $httpParsedResponseAr['L_TRANSACTIONID'.$i]);
                }
                $i++;
            }
            
            if("Success" == $httpParsedResponseAr["ACK"]) {
                //exit('TransactionSearch Completed Successfully: '.print_r($httpParsedResponseAr, true));
            } else  {
                $this->log($userName."-TransactionSearch", '<font color="red">TransactionSearch failed: ' . print_r($httpParsedResponseAr, true).'</font><br><br><br>', "html");
            }
        }
        
        private function addTransactionFromAPI($api_date){
	    /*
            foreach($api_date as $key=>$value){
                $api_date[$key] = urldecode($value);
            }
	    */
            switch($api_date['PAYMENTSTATUS']){
		
		case "Pending":
		    $status = "N";
		break;
		
                case "Completed":
                    $status = "P";
                break;
            
                case "Reversed":
                    $status = "V";
                break;
            
                case "Canceled_Reversal":
                    $status = "C";
                break;
            
                case "Refunded":
                    $status = "R";
                break;
            }
            $i = 0;
            //$item_number_string = $ipn_data['item_number'];
	    $item_number_string = "";
            while(!empty($api_date['L_NUMBER'.$i])){
                $item_number_string .= ",".$api_date['L_NUMBER'.$i];
                $i++;
            }
	    
	    //if items only one, cut off ","
	    //if($i == 2){
		$item_number_string = substr($item_number_string, 1);
	    //}
	    
  	    $payerAddressLine1 = $api_date['SHIPTOSTREET'];
  	    $payerAddressLine2 = $api_date['SHIPTOSTREET2'];
            
	    if($status == "V"){
		$sql = "select id from qo_transactions where txnId='".$api_date['TRANSACTIONID']."' and status = 'V'";
		$result = mysql_query($sql, PayPal::$database_connect);
		$row = mysql_fetch_assoc($result);
	    }elseif($status == "C"){
		$sql = "select id from qo_transactions where txnId='".$api_date['TRANSACTIONID']."' and status = 'C'";
		$result = mysql_query($sql, PayPal::$database_connect);
		$row = mysql_fetch_assoc($result);
	    }else{
		$sql = "select id from qo_transactions where txnId='".$api_date['TRANSACTIONID']."'";
		$result = mysql_query($sql, PayPal::$database_connect);
		$row = mysql_fetch_assoc($result);
	    }
	    $this->log('paypal_api', "addTransactionFromAPI: ".$sql."<br>", "html");
	    
	    if(empty($row['id'])){
                $transactionId = $this->getTransactionId();
		$payeeId = $this->getPayeeIdFromEmail($api_date['RECEIVEREMAIL']);
		
                $sql = "insert into qo_transactions (id,txnId,transactionTime,amountCurrency,amountValue,status,remarks,createdBy,createdOn,payeeId,
                payerId,payerName,payerEmail,payerAddressLine1,payerAddressLine2,payerCity,payerStateOrProvince,
                payerPostalCode,payerCountry,itemId) values ('".$transactionId."','".$api_date['TRANSACTIONID']."','".date("Y-m-d H:i:s",strtotime($api_date['ORDERTIME']))."',
                '".$api_date['CURRENCYCODE']."','".$api_date['AMT']."','".$status."','".mysql_real_escape_string($api_date['NOTE'])."','PayPal','".date("Y-m-d H:i:s",strtotime($api_date['ORDERTIME']))."','".mysql_real_escape_string($payeeId)."',
                '".mysql_real_escape_string($api_date['BUYERID'])."','".mysql_real_escape_string($api_date['SHIPTONAME'])."',
                '".$api_date['EMAIL']."','".mysql_real_escape_string($payerAddressLine1)."','".mysql_real_escape_string($payerAddressLine2)."',
                '".mysql_real_escape_string($api_date['SHIPTOCITY'])."','".mysql_real_escape_string($api_date['SHIPTOSTATE'])."',
                '".$api_date['SHIPTOZIP']."','".$api_date['SHIPTOCOUNTRYNAME']."','".$item_number_string."')";
                
		$this->log('paypal_api', "addTransactionFromAPI: ".$sql."<br>", "html");
                $result = mysql_query($sql, PayPal::$database_connect);
		
		$e = 0;
                while($result == false){
		    if($e > 5){
			$this->log('paypal_api', "<font color='red'>addTransactionFromAPI: Insert trasaction while error!</font>");
			return false;
		    }
                    sleep(rand(0,10));
                    $transactionId = $this->getTransactionId();
                    $sql = "insert into qo_transactions (id,txnId,transactionTime,amountCurrency,amountValue,status,remarks,createdBy,createdOn,payeeId,
                    payerId,payerName,payerEmail,payerAddressLine1,payerAddressLine2,payerCity,payerStateOrProvince,
                    payerPostalCode,payerCountry,itemId) values ('".$transactionId."','".$api_date['TRANSACTIONID']."','".date("Y-m-d H:i:s",strtotime($api_date['ORDERTIME']))."',
                    '".$api_date['CURRENCYCODE']."','".$api_date['AMT']."','".$status."','".mysql_real_escape_string($api_date['NOTE'])."','PayPal','".date("Y-m-d H:i:s",strtotime($api_date['ORDERTIME']))."','".mysql_real_escape_string($payeeId)."',
                    '".mysql_real_escape_string($api_date['BUYERID'])."','".mysql_real_escape_string($api_date['SHIPTONAME'])."',
                    '".$api_date['EMAIL']."','".mysql_real_escape_string($payerAddressLine1)."','".mysql_real_escape_string($payerAddressLine2)."',
                    '".mysql_real_escape_string($api_date['SHIPTOCITY'])."','".mysql_real_escape_string($api_date['SHIPTOSTATE'])."',
                    '".$api_date['SHIPTOZIP']."','".$api_date['SHIPTOCOUNTRYNAME']."','".$item_number_string."')";
                
		    $this->log('paypal_api', "<font color='red'>addTransactionFromAPI failure, Repeat: ".$sql."</font><br>", "html");
                    $result = mysql_query($sql, PayPal::$database_connect);
		    $e++;
		}
                
		$this->matchOrder($api_date, $item_number_string, $transactionId);
            }else{
		$sql_1 = "select o.id from qo_orders as o left join qo_orders_transactions as ot on o.id = ot.ordersId where o.buyerId='".$api_date['BUYERID']."' and o.status = 'W' and o.createdOn > '".date("Y-m-d H:i:s", time() - (7 * 24 * 60 * 60))."' and ot.transactionsId = '".$row['id']."'";
		$this->log('paypal_api', "getOrderId: ".$sql_1."<br>", "html");
		$result_1 = mysql_query($sql_1, PayPal::$database_connect);
		$row_1 = mysql_fetch_assoc($result_1);
		if(!empty($row_1['ordersId'])){
		    $this->log('paypal_api', '<font color="red"> '. $row_1['ordersId'] . '|'. $row['id'] . '|'. $api_date['TRANSACTIONID']. ' Exist.</font><br>', "html");
		    $this->updateOrderAndShipment($row_1['ordersId'], $api_date);
		}
		
		/*
		$this->log('paypal_api', '<font color="red">TRANSACTIONID: '. $row['id'] . '|'. $api_date['TRANSACTIONID']. ' Exist.</font><br>', "html");
		$ordersId = $this->getOrderId($api_date['BUYERID'], $item_number_string);
		if(!empty($ordersId)){
		    $this->updateOrderAndShipment($ordersId, $api_date);
		}
		*/
            }
	    
	    $this->log('paypal_api', "<br><br>+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br><br>", "html");
        }
        
        public function GetTransactionDetails($userName, $password, $sgnature, $transactionID){
            // Add request-specific fields to the request string.
            $nvpStr = "&TRANSACTIONID=$transactionID";
            
            // Execute the API operation; see the PPHttpPost function above.
            $httpParsedResponseAr = $this->PPHttpPost($userName, $password, $sgnature, 'GetTransactionDetails', $nvpStr);

            foreach($httpParsedResponseAr as $key=>$value){
		$httpParsedResponseAr[$key] = urldecode($value);
                //echo $key.": ".urldecode($value);
                //echo "<br>";
            }
	    
            $this->log($userName."-GetTransactionDetails", print_r($httpParsedResponseAr, true)."<br><br><br>", "html");
	    
            $this->addTransactionFromAPI($httpParsedResponseAr);
            
            if("Success" == $httpParsedResponseAr["ACK"]) {
                    //exit('GetTransactionDetails Completed Successfully: '.print_r($httpParsedResponseAr, true));
            } else  {
		    $this->log($userName."-GetTransactionDetails", '<font color="red">GetTransactionDetails failed: ' . print_r($httpParsedResponseAr, true).'</font>', "html");
            }
        }
        
	public function setAPITime($start_time, $end_time){
	    if(strlen($start_time) == 10 && $end_time == 10){
		$this->start_time = $start_time . " 00:00:00";
		$this->end_time = $end_time . " 00:00:00";
	    }else{
		$this->start_time = $start_time;
		$this->end_time = $end_time;
	    }
	}
	
        public function getAllSellerTransactions(){
            //$this->TransactionSearch("paintings.suppliersz_api1.gmail.com", "BQ3G47PGEUPFJYUW", "AFAonZoEN5Tlf1AdMI6LHryIRiuXAZmyV1n8z4H3aK3CTTmVXIajebfk", "2009-04-18 00:00:00", "2009-04-20 00:00:00");
	    $api_acount = parse_ini_file(__DOCROOT__ . '/paypal.ini', true);
	    
	    if(empty($this->start_time) && empty($this->end_time)){
		$this->start_time = date("Y-m-d H:i:s", time() - (13 * 60 * 60));
		$this->end_time   = date("Y-m-d H:i:s", time() - (9 * 60 * 60));
		
		
		foreach($api_acount as $acount){
		    echo $acount['Username']."\n";
		    echo $this->start_time."\n";
		    echo $this->end_time."\n";
		    echo "\n";
		    
		    //continue;
		    $this->account = $acount['Username'];
		    $this->TransactionSearch($acount['Username'], $acount['Password'], $acount['Signature'], $this->start_time, $this->end_time);
		}
		
	    }else{
		$start_timestamp = strtotime($this->start_time);
		$end_timestamp = strtotime($this->end_time);
		
		for($i = $start_timestamp; $i < $end_timestamp; $i += FETCH_HOUR * 60 *  60){
		    foreach($api_acount as $acount){
			echo $acount['Username']."\n";
			echo date("Y-m-d H:i:s", $i)."\n";
			echo date("Y-m-d H:i:s", $i + FETCH_HOUR * 60 *  60)."\n";
			echo "\n";
			//sleep(5);
			
			//continue;
			$this->account = $acount['Username'];
			$this->TransactionSearch($acount['Username'], $acount['Password'], $acount['Signature'], date("Y-m-d H:i:s", $i), date("Y-m-d H:i:s", $i + FETCH_HOUR * 60 *  60));
		    }
		    
		}
	    }
	}
        
	public function getSellerTransactions($user_name){
	    //$this->start_time .= " 00:00:00";
	    //$this->end_time .= " 00:00:00";
	    
	    $start_timestamp = strtotime($this->start_time);
	    //echo $start_timestamp."\n";
	    //echo date("Y-m-d H:i:s", $start_timestamp)."\n";
	    $end_timestamp = strtotime($this->end_time);
	    //echo $end_timestamp."\n";
	    //echo date("Y-m-d H:i:s", $end_timestamp)."\n";
	    
	    $api_acount = parse_ini_file(__DOCROOT__ . '/paypal.ini', true);
	    for($i = $start_timestamp; $i < $end_timestamp; $i += FETCH_HOUR * 60 *  60){
		foreach($api_acount as $acount){
		    if($user_name == $acount['Username']){
			echo date("Y-m-d\TH:i:s\Z", $i)."\n";
			echo date("Y-m-d\TH:i:s\Z", $i + FETCH_HOUR * 60 *  60)."\n";
			echo "\n";
			sleep(5);
			$this->account = $acount['Username'];
			$this->TransactionSearch($acount['Username'], $acount['Password'], $acount['Signature'], date("Y-m-d\TH:i:s\Z", $i), date("Y-m-d\TH:i:s\Z", $i + FETCH_HOUR * 60 *  60));
		    }
		}
		
	    }
	}
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	public function getSellerTransactionsFromCSV(){
	    $row = 0;
	    if (($handle = fopen("test.csv", "r")) !== FALSE) {
		while (($data = fgetcsv($handle)) !== FALSE) {
		    if($row == 0){
			$row++;
			continue;
		    }
		    $i = 0;
		    $transaction_array[$row]['Date'] = $data[$i++];
		    $transaction_array[$row]['Time'] = $data[$i++];
		    $transaction_array[$row]['Time_Zone'] = $data[$i++];
		    $transaction_array[$row]['Name'] = $data[$i++];
		    $transaction_array[$row]['Type'] = $data[$i++];
		    $transaction_array[$row]['Status'] = $data[$i++];
		    $transaction_array[$row]['Gross'] = $data[$i++];
		    $transaction_array[$row]['Fee'] = $data[$i++];
		    $transaction_array[$row]['Net'] = $data[$i++];
		    $transaction_array[$row]['From_Email_Address'] = $data[$i++];
		    $transaction_array[$row]['To_Email_Address'] = $data[$i++];
		    $transaction_array[$row]['Transaction_ID'] = $data[$i++];
		    $transaction_array[$row]['Counterparty_Status'] = $data[$i++];
		    $transaction_array[$row]['Address_Status'] = $data[$i++];
		    $transaction_array[$row]['Item_Title'] = $data[$i++];
		    $transaction_array[$row]['Item_ID'] = $data[$i++];
		    $transaction_array[$row]['Shipping_and_Handling_Amount'] = $data[$i++];
		    $transaction_array[$row]['Insurance_Amount'] = $data[$i++];
		    $transaction_array[$row]['Sales_Tax'] = $data[$i++];
		    $transaction_array[$row]['Option_1_Name'] = $data[$i++];
		    $transaction_array[$row]['Option_1_Value'] = $data[$i++];
		    $transaction_array[$row]['Option_2_Name'] = $data[$i++];
		    $transaction_array[$row]['Option_2_Value'] = $data[$i++];
		    $transaction_array[$row]['Auction_Site'] = $data[$i++];
		    $transaction_array[$row]['Buyer_ID'] = $data[$i++];
		    $transaction_array[$row]['Item_URL'] = $data[$i++];
		    $transaction_array[$row]['Closing_Date'] = $data[$i++];
		    $transaction_array[$row]['Escrow_Id'] = $data[$i++];
		    $transaction_array[$row]['Invoice_Id'] = $data[$i++];
		    $transaction_array[$row]['Reference_Txn_ID'] = $data[$i++];
		    $transaction_array[$row]['Invoice_Number'] = $data[$i++];
		    $transaction_array[$row]['Custom_Number'] = $data[$i++];
		    $transaction_array[$row]['Receipt_ID'] = $data[$i++];
		    $transaction_array[$row]['Balance'] = $data[$i++];
		    $transaction_array[$row]['Address_Line_1'] = $data[$i++];
		    $transaction_array[$row]['Address_Line_2/District/Neighborhood'] = $data[$i++];
		    $transaction_array[$row]['Town/City'] = $data[$i++];
		    $transaction_array[$row]['State/Province/Region/County/Territory/Prefecture/Republic'] = $data[$i++];
		    $transaction_array[$row]['Zip/Postal_Code'] = $data[$i++];
		    $transaction_array[$row]['Country'] = $data[$i++];
		    $transaction_array[$row]['Contact_Phone_Number'] = $data[$i++];
		    $row++;
		}
		fclose($handle);
	    }    
	}
	
        public function __destruct(){
            mysql_close(PayPal::$database_connect);
        }
    }
    
    
if(!empty($_POST)){
    $PayPal = new PayPal();
    $PayPal->ipn();
}

/*
if(!empty($_GET['action'])){
    $PayPal = new PayPal();
    switch($_GET['action']){
        case "TransactionSearch":
            $PayPal->TransactionSearch("paintings.suppliersz_api1.gmail.com", "BQ3G47PGEUPFJYUW", "AFAonZoEN5Tlf1AdMI6LHryIRiuXAZmyV1n8z4H3aK3CTTmVXIajebfk", "2009-07-04 00:00:00", "2009-07-05 00:00:00");
        break;
    
        case "GetTransactionDetails":
            $PayPal->GetTransactionDetails("paintings.suppliersz_api1.gmail.com", "BQ3G47PGEUPFJYUW", "AFAonZoEN5Tlf1AdMI6LHryIRiuXAZmyV1n8z4H3aK3CTTmVXIajebfk", "9YE0607179577100D");
        break;
    }
}
*/
if(!empty($argv[1]) && $argv[1] == "API"){
    $PayPal = new PayPal();
    
    if(!empty($argv[2]) && $argv[2] == "Today"){
	$PayPal->setAPITime(date("Y-m-d H:i:s", time() - (32 * 60 * 60)), date("Y-m-d H:i:s", time() - (8 * 60 * 60)));
	$PayPal->getAllSellerTransactions();
    }else{
	if(!empty($argv[2]) && !empty($argv[3])){
	    $PayPal->setAPITime($argv[2], $argv[3]);
	}
	//$PayPal->setAPITime("2010-09-23 00:00:00", "2010-09-23 14:30:00");
	if(!empty($argv[4])){
	    $PayPal->getSellerTransactions($argv[4]);
	}else{
	    $PayPal->getAllSellerTransactions();
	}
    }
}
/*
paintings.suppliersz_api1.gmail.com
BQ3G47PGEUPFJYUW
AFAonZoEN5Tlf1AdMI6LHryIRiuXAZmyV1n8z4H3aK3CTTmVXIajebfk


bestnbestonlinesz_api1.gmail.com
8QRU8M9WXG7N3953
ATFJ04PNS0BGkSpwDZ9jfBhWpU8DAenN3dU8O9ap3NNjIK46BR-KRcos


libra.studio.gd_api1.gmail.com
9SX9GJJX38PGPDBZ
AFcWxV21C7fd0v3bYYYRCpSSRl31A5TLxC.ZzYePJo53IDKsil6q0V3o
*/
?>