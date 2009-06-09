<?php
    class PayPal{
        private static $database_connect;
        const DATABASE_HOST = 'localhost';
        const DATABASE_USER = 'root';
        const DATABASE_PASSWORD = '5333533';
        const DATABASE_NAME = 'ebaybo';
        //const IPN_VALIDATE_HOST = 'ssl://www.sandbox.paypal.com';
        const IPN_VALIDATE_HOST = 'ssl://www.paypal.com';
        const NVPAPI_HOST = 'https://api-3t.paypal.com/nvp';
        
        public function __construct(){
            PayPal::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

            if (!PayPal::$database_connect) {
                echo "Unable to connect to DB: " . mysql_error(PayPal::$database_connect);
                exit;
            }
            
	    mysql_query("SET NAMES 'UTF8'", PayPal::$database_connect);
	    
            if (!mysql_select_db(self::DATABASE_NAME, PayPal::$database_connect)) {
                echo "Unable to select mydbname: " . mysql_error(PayPal::$database_connect);
                exit;
            }
        }
        
        private function log($file_name, $content){
            file_put_contents("/export/eBayBO/log/Ipn/".$file_name."-".date("Y-m-d").".log", date("Y-m-d H:i:s")."   ".$content."\n", FILE_APPEND);
        }
        
        private function getTransactionId(){
            $type = 'TRA';
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
        
        private function getOrderIdFromTxnId($txnId){
	    if($txnId != ""){
                $sql = "select id from qo_transactions where txnId = '$txnId'";
                $this->log('ipn_deal',"getOrderIdFromTxnId: transactionId ".$sql);
                $result = mysql_query($sql, PayPal::$database_connect);
                $row = mysql_fetch_assoc($result);
                
                $sql = "select ordersId from qo_orders_transactions where transactionsId = '".$row['id']."'";
                $this->log('ipn_deal',"getOrderIdFromTxnId: ordersId ".$sql);
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
        
        private function getOrderPay($ordersId){
            $sql = "select grandTotalValue from qo_orders where id='$ordersId'";
            $this->log('ipn_deal',"getOrderPay: ".$sql);
            $result = mysql_query($sql, PayPal::$database_connect);
            $row = mysql_fetch_assoc($result);
	    return $row['grandTotalValue'];
        }
        
        private function updateOrderStatus($ordersId, $status){
            $modifiedBy = "Paypal";
            $modifiedOn= date("Y-m-d H:i:s");
            $sql = "update qo_orders set status='$status',modifiedBy='$modifiedBy',modifiedOn='$modifiedOn' where id='$ordersId'";
            $this->log('ipn_deal',"updateOrderStatus: ".$sql);
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
	
        private function updateOrderAndShipment($ordersId, $ipn_data){
            switch($ipn_data['payment_status']){	
                case "Completed":
                    $pay = $this->getOrderPay($ordersId);
                    if($ipn_data['mc_gross'] > ($pay + ($pay * 0.02))){
                            $this->updateOrderStatus($ordersId, "C");
                    }elseif($ipn_data['mc_gross'] < ($pay - ($pay * 0.02))){
                            $this->updateOrderStatus($ordersId, "S");
                    }else{
                            $this->updateOrderStatus($ordersId, "P");
                            //$this->createShipmentFromPayPal($ordersId);
                    }
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
                    //$shipment_status = $this->getShipmentStatus($ordersId);
                    //if($shipment_status == "N" ){
                    //    $this->updateShipmentStatus($ordersId, 'H');
                    //}
                break;
                        
                case "Canceled_Reversal":
                    $this->updateOrderStatus($ordersId, "P");
                    //$this->createShipmentFromPayPal($ordersId);
                break;	   		
            }

	}
        
        private function matchEbayOrder($ipn_data, $item_number_string, $transactionId){
            $ordersId = $this->getEbayOrderId($ipn_data['auction_buyer_id'], $item_number_string);
            $ordersId = ($ordersId!='')?$ordersId:($this->getOrderIdFromTxnId($ipn_data['parent_txn_id']));
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
                $this->updateOrderAndShipment($ordersId, $ipn_data);
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
                
                $result = mysql_query($sql, PayPal::$database_connect);
                while($result == false){
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
		}
                $this->log('ipn_deal', "addEbayTransaction: ".$sql);
            }
            $this->matchEbayOrder($ipn_data, $item_number_string, $transactionId);
            
        }
        
        public function ipn(){
            if(empty($_POST['payment_status'])){
                return 0;
            }
            // read the post from PayPal system and add 'cmd'
            $req = 'cmd=_notify-validate';
            
            foreach ($_POST as $key => $value) {
                $value = urlencode(stripslashes($value));
                $req .= "&$key=$value";
            }
            
            $this->log("ipn_data", $_POST['txn_id']. '   '. print_r($_POST, true));
            // post back to PayPal system to validate
            $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
            $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
            $fp = fsockopen (self::IPN_VALIDATE_HOST, 443, $errno, $errstr, 30);

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
    
            // Set the API operation, version, and API signature in the request.
            $nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";
            echo $nvpreq;
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

        private function TransactionSearch($userName, $password, $sgnature, $start_time, $end_time){
            
            $nvpStr .= "&STARTDATE=$start_time";
            $nvpStr .= "&ENDDATE=$end_time";
            $httpParsedResponseAr = $this->PPHttpPost($userName, $password, $sgnature, 'TransactionSearch', $nvpStr);

            if("Success" == $httpParsedResponseAr["ACK"]) {
                    exit('TransactionSearch Completed Successfully: '.print_r($httpParsedResponseAr, true));
            } else  {
                    exit('TransactionSearch failed: ' . print_r($httpParsedResponseAr, true));
            }
        }
        
        private function GetTransactionDetails($userName, $password, $sgnature, $transactionID){
            // Add request-specific fields to the request string.
            $nvpStr = "&TRANSACTIONID=$transactionID";
            
            // Execute the API operation; see the PPHttpPost function above.
            $httpParsedResponseAr = $this->PPHttpPost($userName, $password, $sgnature, 'GetTransactionDetails', $nvpStr);
            
            if("Success" == $httpParsedResponseAr["ACK"]) {
                    exit('GetTransactionDetails Completed Successfully: '.print_r($httpParsedResponseAr, true));
            } else  {
                    exit('GetTransactionDetails failed: ' . print_r($httpParsedResponseAr, true));
            }
        }
        
        public function getAllSellerTransactions(){
            $this->TransactionSearch("paintings.suppliersz_api1.gmail.com", "BQ3G47PGEUPFJYUW", "AFAonZoEN5Tlf1AdMI6LHryIRiuXAZmyV1n8z4H3aK3CTTmVXIajebfk", "2009-04-18 00:00:00", "2009-04-20 00:00:00");
        }
        
        public function __destruct(){
            mysql_close(PayPal::$database_connect);
        }
    }
    
    
if(!empty($_POST)){
    $PayPal = new PayPal();
    $PayPal->ipn();
}else{
    $PayPal = new PayPal();
    $PayPal->getAllSellerTransactions();
}
    
?>