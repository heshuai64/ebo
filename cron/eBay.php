<?php
require_once '/export/eBayBO/cron/eBaySOAP.php';

class eBayPlatformNotificationListener extends eBayPlatformNotifications {
	protected $NotificationSignature;

	// Dispatch method to ensure signature validation
	public function __call($method, $args) {
		$s = "Called with $method";
		$this->carp($s);

		if ($this->ValidateSignature($args[0])) {
			// strip off trailing "Request"
			$method = substr($method, 0, -8);
			if (method_exists($this, $method)) {
				return call_user_func_array(array($this, $method), $args);
			}
		}
		
		// Today is a good day to die.
		die("Death");
	}

	// Extract Signature for validation later
	// Can't validate here because we don't have access to the Timestamp
	public function RequesterCredentials($RequesterCredentials) {
		$this->NotificationSignature = $RequesterCredentials->NotificationSignature;
	}

	protected function ValidateSignature($Timestamp) {
		// Check for Signature Match
		$CalculatedSignature = $this->CalculateSignature($Timestamp);
		$NotificationSignature = $this->NotificationSignature;

		if ($CalculatedSignature != $NotificationSignature) {
			$this->carp("Sig Mismatch: Calc: $CalculatedSignature, Note: $NotificationSignature");
			return false;
		} else {
			$this->carp("Sig Match: $NotificationSignature");
		}

		// Check that Timestamp is within 10 minutes of now
		$tz = date_default_timezone_get();
		date_default_timezone_set('UTC');
		$then = strtotime($Timestamp);
		$now = time();
		date_default_timezone_set($tz);

		$drift = $now - $then;
		$ten_minutes = 60 * 10;
		if ($drift > $ten_minutes) {
			$this->carp("Time Drift is too large: $drift seconds");
			return false;
		} else {
			$this->carp("Time Drift is okay: $drift seconds");
		}

		return true;
	}

	// Arg order is brittle, assumes constant return ordering from eBay
	public function GetMemberMessages($Timestamp, $Ack, $CorrelationID,
						$Version, $Build, $NotificationEventName, 
						$RecipientUserID, $MemberMessage, 
						$PaginationResult, $HasMoreItems) {

		// Extract some data to prove this is working
		$UserID = $MemberMessage->MemberMessageExchange->Item->Seller->UserID;
		$this->carp($UserID);
		return $UserID;
	}

	public function GetItem($Timestamp, $Ack, $CorrelationID,
				$Version, $Build, $NotificationEventName, 
				$RecipientUserID, $Item) {

	       $ItemID = $Item->ItemID;
	       return "OutBid: $ItemID";
	}
        
        public function GetItemTransactions($PaginationResult, $HasMoreTransactions, $TransactionsPerPage, $PageNumber, 
                                            $ReturnedTransactionCountActual, $Item, $TransactionArray, $PayPalPreferred){
            file_put_contents('GetItemTransactions.log', print_r($TransactionArray, true));
        }

}

class eBay{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaybo';
    const GATEWAY_SOAP = 'https://api.sandbox.ebay.com/wsapi';
    const LOG_DIR = '/export/eBayBO/log/';
    
    private $startTime;
    private $endTime;
    
    public function __construct(){
        eBay::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

        if (!eBay::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(eBay::$database_connect);
            exit;
        }
	
        mysql_query("SET NAMES 'UTF8'", eBay::$database_connect);
	
        if (!mysql_select_db(self::DATABASE_NAME, eBay::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(eBay::$database_connect);
            exit;
        }
       
    }
    
    public function api_error_log($file_name, $content){
	file_put_contents("/export/eBayBO/log/ApiError/".$file_name."-".date("Y-m-d").".log", date("Y-m-d H:i:s")."   ".$content."\n", FILE_APPEND);
    }
    
    public function setStartTime($startTime){
	$this->startTime = $startTime;
    }
    
    public function setEndTime($endTime){
	$this->endTime = $endTime;
    }
    
    private function configEbay($dev='', $app='', $cert='', $token='', $proxy_host='', $proxy_port=''){
    	
	// Load developer-specific configuration data from ini file
	$config = parse_ini_file('/export/eBayBO/cron/ebay.ini', true);
	$site = $config['settings']['site'];
	//$compatibilityLevel = $config['settings']['compatibilityLevel'];
	
	$dev = (empty($dev)?$config[$site]['devId']:$dev);
	$app = (empty($app)?$config[$site]['appId']:$app);
	$cert = (empty($cert)?$config[$site]['cert']:$cert);
	$token = (empty($token)?$config[$site]['authToken']:$token);
	$location = $config[$site]['gatewaySOAP'];
	//$location = self::GATEWAY_SOAP;
	
	// Create and configure session
	$session = new eBaySession($dev, $app, $cert, $proxy_host, $proxy_port);
	$session->token = $token;
	$session->site = 0; // 0 = US;
	$session->location = $location;
	
	return $session;
    }
    
    private function saveFetchData($account_name, $file_name, $data){
	if(!file_exists(self::LOG_DIR.$account_name)){
            mkdir(self::LOG_DIR.$account_name, 0777);
        }
	
	if(!file_exists(self::LOG_DIR.$account_name."/".date("Ymd"))){
            mkdir(self::LOG_DIR.$account_name."/".date("Ymd"), 0777);
        }
	
	file_put_contents(self::LOG_DIR.$account_name."/".date("Ymd")."/".$file_name, $data);
	//file_put_contents("/export/eBayBO/log/".$file_name, $data);
    }
    
    private function GetSellerTransactions($ModTimeFrom, $ModTimeTo, $sellerId, $dev, $app, $cert, $token, $proxy_host, $proxy_port){
        /*
	$sql = "select token from qo_ebay_seller where id = '".$sellerId."'";
        $result = mysql_query($sql, eBay::$database_connect);
        $row = mysql_fetch_assoc($result);
        $token = $row['token'];
        */
        $session = $this->configEbay($dev, $app, $cert, $token, $proxy_host, $proxy_port);
        try {
                $client = new eBaySOAP($session);
                 
                //$ModTimeFrom = "2009-03-25 00:00:00";
                //$ModTimeTo   = "2009-03-25 02:00:00";
                $EntriesPerPage = 200;
                $Version = '607';
                $DetailLevel = "ReturnAll";
                $IncludeContainingOrder = true;
                $IncludeFinalValueFee = true;
                $Pagination = array('EntriesPerPage'=> $EntriesPerPage, 'PageNumber'=> 1);
             
                $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel, 'Pagination' => $Pagination, 'IncludeContainingOrder' => $IncludeContainingOrder, 'IncludeFinalValueFee' => $IncludeFinalValueFee, 'ModTimeFrom' => $ModTimeFrom, 'ModTimeTo' => $ModTimeTo);
                $results = $client->GetSellerTransactions($params);
                //----------   debug --------------------------------
                //print "Request: \n".$client->__getLastRequest() ."\n";
                //print "Response: \n".$client->__getLastResponse()."\n";
		//$this->saveFetchData("/GetSellerTransactions/".$sellerId."-Request-GetSellerTransactions-".date("Y-m-d H:i:s").".xml", $client->__getLastRequest());
		//$this->saveFetchData("/GetSellerTransactions/".$sellerId."-Response-GetSellerTransactions-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
                $this->saveFetchData($sellerId, "GetSellerTransactions-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		return $results;
                
        } catch (SOAPFault $f) {
		$this->api_error_log("GetSellerTransactions", print_r($f, true));
        }
        
    }
    
    private function GetOrders($CreateTimeFrom, $CreateTimeTo){
        $session = $this->configEbay($token);
        try {
            $client = new eBaySOAP($session);
            //$CreateTimeFrom = "2008-04-07 00:40:00";
            //$CreateTimeTo   = "2008-04-07 08:40:00";
            $OrderRole = 'Seller';
            $OrderStatus = 'Completed';
                        
            $params = array('Version' => '607', 'CreateTimeFrom' => $CreateTimeFrom, 'CreateTimeTo' => $CreateTimeTo, 'OrderRole' => $OrderRole, 'OrderStatus' => $OrderStatus,);
            $results = $client->GetOrders($params);
            print_r($results);
            
        } catch (SOAPFault $f) {
            print $f; // error handling
        }
    }
    
    
    private function checkEbayTransactionExist($itemId, $ebayTransactionId){
            $sql = "select o.id as ordersId from qo_orders as o left join qo_orders_detail as od on o.id=od.ordersId where od.itemId = '".$itemId."' and od.ebayTranctionId = '".$ebayTransactionId."'";
            $result = mysql_query($sql, eBay::$database_connect);
            //echo "<br>\n<font color='green'>".$sql."</font><br>\n";
            $row = mysql_fetch_assoc($result);
            return $row['ordersId'];
    }
    
    private function getOrderId(){
        $type = 'ORD';
        $today = date("Ym");
        $sql = "select curType,curId from sequence where curDate='$today' and type='$type'";
        $result = mysql_query($sql, eBay::$database_connect);
        $row = mysql_fetch_assoc($result);
       
        if($row["curId"] >=9999){
            // A-Z 66-91
            $curType = chr(ord($row["curType"]) + 1);
            $sql = "update  sequence  set curId = 1,curType='$curType' where curDate='$today' and type='$type'";
            mysql_query($sql, eBay::$database_connect);
        }elseif($row["curId"] < 1 || $row["curId"] == null) {
              $sql = "insert into sequence (type,curType,curDate,curId) value ('$type','A','$today',1)";
              mysql_query($sql, eBay::$database_connect);
        }else {   
            $sql = "update sequence set curId = curId + 1 where curDate='$today' and type='$type'";
            $result = mysql_query($sql, eBay::$database_connect);
        }
       
        $sql = "select curType,curId from sequence where curDate='$today' and type='$type'";
        $result = mysql_query($sql, eBay::$database_connect);
        $row = mysql_fetch_assoc($result);
        $orderId = $type.$today.$row["curType"].str_repeat("0",(4-strlen($row["curId"]))).$row["curId"];   
        return $orderId;
    }
    
    private function checkSameBuyExist($sellerId, $transaction){
        //$sql = "select id from qo_orders where sellerId= '".$sellerId."' and buyerId = '".$transaction->Buyer->UserID."' and 
        //status = 'W' and grandTotalCurrency = '".$transaction->AmountPaid->currencyID."'";
        //ebay order don't add
        $sql = "select o.id from qo_orders as o left join qo_orders_detail as od on o.id =od.ordersId where o.sellerId= '".$sellerId."' and o.buyerId = '".$transaction->Buyer->UserID."' and 
        o.status = 'W' and o.grandTotalCurrency = '".$transaction->AmountPaid->currencyID."' and od.ebayOrderId = ''";
        
        $result = mysql_query($sql, eBay::$database_connect);
        $row = mysql_fetch_assoc($result);
        return $row['id'];
    }
    
    private function createOrderFromEbayTransaction($sellerId, $transaction){
        $sameBuyOrderId = $this->checkSameBuyExist($sellerId, $transaction);
        if(!empty($sameBuyOrderId)){
            $this->AddOrderDetailBySameBuy($transaction, $sameBuyOrderId);
	    $this->mapEbayTransaction($sameBuyOrderId, $transaction);
        }else{
            $id = $this->getOrderId();
            $status = "W";
            $shippingMethod = $transaction->ShippingServiceSelected->ShippingService;
            $paymentMethod = "PayPal";
            $sellerId = $sellerId;
            $buyerId = $transaction->Buyer->UserID;
            $shippingFeeCurrency = $transaction->ShippingServiceSelected->ShippingServiceCost->currencyID;
            $shippingFeeValue = $transaction->ShippingServiceSelected->ShippingServiceCost->_;
            $insuranceCurrency = $transaction->ShippingDetails->InsuranceFee->currencyID;
            $insuranceValue = $transaction->ShippingDetails->InsuranceFee->_;
            $grandTotalCurrency = $transaction->AmountPaid->currencyID;
            $grandTotalValue = $transaction->AmountPaid->_;
            $ebayName = $transaction->Buyer->BuyerInfo->ShippingAddress->Name;
            $ebayEmail = $transaction->Buyer->Email;
            $ebayAddress1 = $transaction->Buyer->BuyerInfo->ShippingAddress->Street1;
            $ebayAddress2 = $transaction->Buyer->BuyerInfo->ShippingAddress->Street2;
            $ebayCity = $transaction->Buyer->BuyerInfo->ShippingAddress->CityName;
            $ebayStateOrProvince = $transaction->Buyer->BuyerInfo->ShippingAddress->StateOrProvince;
            $ebayPostalCode = $transaction->Buyer->BuyerInfo->ShippingAddress->PostalCode;
            $ebayCountry = $transaction->Buyer->BuyerInfo->ShippingAddress->CountryName;
            $ebayPhone = $transaction->Buyer->BuyerInfo->ShippingAddress->Phone;
            $createdBy = "eBay";
            //$createdOn = date("Y-m-d H:i:s",strtotime(substr($transaction->CreatedDate, 0 ,-5)) + (8 * 60 * 60));
            $createdOn = date("Y-m-d H:i:s",strtotime($transaction->CreatedDate));
	    
            $sql = "insert into qo_orders (id,status,shippingMethod,paymentMethod,sellerId,buyerId,shippingFeeCurrency,shippingFeeValue,
            insuranceCurrency,insuranceValue,grandTotalCurrency,grandTotalValue,ebayName,ebayEmail,ebayAddress1,ebayAddress2,
            ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone,createdBy,createdOn) values
            ('".$id."','".$status."','".$shippingMethod."','".$paymentMethod."','".$sellerId."','".$buyerId."','".$shippingFeeCurrency."','".$shippingFeeValue."',
            '".$insuranceCurrency."','".$insuranceValue."','".$grandTotalCurrency."','".$grandTotalValue."','".mysql_real_escape_string($ebayName)."',
            '".$ebayEmail."','".mysql_real_escape_string($ebayAddress1)."','".mysql_real_escape_string($ebayAddress2)."',
            '".mysql_real_escape_string($ebayCity)."','".mysql_real_escape_string($ebayStateOrProvince)."','".$ebayPostalCode."',
            '".mysql_real_escape_string($ebayCountry)."','".$ebayPhone."','".$createdBy."','".$createdOn."')";
            $result = mysql_query($sql, eBay::$database_connect);
            if (!$result) {
                $this->errorLog("createOrderFromEbayTransaction: query error ($sql) from DB: " . mysql_error(eBay::$database_connect));
            }else{
		$this->createOrderDetailFromEbayTransaction($id, $transaction);
	    }
            echo $sql;
            echo "<br>\n<font color='red'>createOrderFromEbayTransaction from ".$transaction->TransactionID.", ".$transaction->Item->ItemID."</font><br>\n<br>\n";
	    $this->mapEbayTransaction($id, $transaction);
        }
    }
    
    private function createOrderDetailFromEbayTransaction($orderId, $transaction){
        /*
        switch($item->ListingType){
            case "FixedPriceItem":
                $unitPriceCurrency = $item->StartPrice->currencyID;
                $unitPriceValue = $item->StartPrice->_;
                break;
            
            case "Chinese":
                
                break;
        }
        */
        $unitPriceCurrency = $transaction->Item->SellingStatus->CurrentPrice->currencyID;
        //$unitPriceValue = $transaction->Item->SellingStatus->CurrentPrice->_ / $transaction->Item->SellingStatus->QuantitySold;
	$unitPriceValue = $transaction->Item->SellingStatus->CurrentPrice->_;
        $quantity = $transaction->QuantityPurchased;
        
        $sql = "insert into qo_orders_detail (ordersId,skuId,itemId,itemTitle,quantity,unitPriceCurrency,unitPriceValue,ebayTranctionId,finalValueFee) values 
        ('".$orderId."','".$transaction->Item->SKU."','".$transaction->Item->ItemID."','".mysql_real_escape_string($transaction->Item->Title)."','".$quantity."','".$unitPriceCurrency."','".$unitPriceValue."','".$transaction->TransactionID."','".$transaction->FinalValueFee->_."')";
        $result = mysql_query($sql, eBay::$database_connect);
        if (!$result) {
            $this->errorLog("createOrderDetailFromEbayTransaction: sql error ($sql) from DB: " . mysql_error(eBay::$database_connect));
        }
	echo $sql;
	echo "<br>\n<font color='green'>createOrderDetailFromEbayTransaction from ".$orderId."</font><br>\n<br>\n";
	
	//$sql = "update qo_orders set shippingFeeValue = shippingFeeValue + ".$transaction->ShippingServiceSelected->ShippingServiceCost->_." where id = '".$orderId."'";
        //$result = mysql_query($sql, eBay::$database_connect);
    }
    
    private function AddOrderDetailBySameBuy($transaction, $orderId){
        $unitPriceCurrency = $transaction->Item->SellingStatus->CurrentPrice->currencyID;
        //$unitPriceValue = $transaction->Item->SellingStatus->CurrentPrice->_ / $transaction->Item->SellingStatus->QuantitySold;
	$unitPriceValue = $transaction->Item->SellingStatus->CurrentPrice->_;
        $quantity = $transaction->QuantityPurchased;
        
        //consider order status
        if(empty($transaction->ContainingOrder)){
            $sql = "insert into qo_orders_detail (ordersId,skuId,itemId,itemTitle,quantity,unitPriceCurrency,unitPriceValue,ebayTranctionId) values 
            ('".$orderId."','".$transaction->Item->SKU."','".$transaction->Item->ItemID."','".mysql_real_escape_string($transaction->Item->Title)."','".$quantity."','".$unitPriceCurrency."','".$unitPriceValue."','".$transaction->TransactionID."')";
        }else{
            $sql = "insert into qo_orders_detail (ordersId,skuId,itemId,itemTitle,quantity,unitPriceCurrency,unitPriceValue,ebayTranctionId,ebayOrderId) values 
            ('".$orderId."','".$transaction->Item->SKU."','".$transaction->Item->ItemID."','".mysql_real_escape_string($transaction->Item->Title)."','".$quantity."','".$unitPriceCurrency."','".$unitPriceValue."','".$transaction->TransactionID."','".$transaction->ContainingOrder->OrderID."')";
        }
        
        $result = mysql_query($sql, eBay::$database_connect);
        if (!$result) {
            $this->errorLog("AddOrderDetailBySameBuy: sql error ($sql) from DB: " . mysql_error(eBay::$database_connect));
        }
        
        if(empty($transaction->ShippingServiceSelected->ShippingServiceCost->_)){
            $transaction->ShippingServiceSelected->ShippingServiceCost->_ = 0;
        }
        $sql = "update qo_orders set grandTotalValue = grandTotalValue + ".$transaction->AmountPaid->_.",shippingFeeValue = shippingFeeValue + ".$transaction->ShippingServiceSelected->ShippingServiceCost->_." where id = '".$orderId."'";
        
	echo $sql;
	echo "<br>\n<font color='green'>AddOrderDetailBySameBuy from ".$transaction->Item->ItemID."</font><br>\n<br>\n";
        
        $result = mysql_query($sql, eBay::$database_connect);
        if (!$result) {
            $this->errorLog("AddOrderDetailBySameBuy: sql error ($sql) from DB: " . mysql_error(eBay::$database_connect));
        }
    }
    
    private function createOrderDetailFromEbayOrder($orderId, $transaction){
        $unitPriceCurrency = $transaction->Item->SellingStatus->CurrentPrice->currencyID;
        //$unitPriceValue = $transaction->Item->SellingStatus->CurrentPrice->_ / $transaction->Item->SellingStatus->QuantitySold;
	$unitPriceValue = $transaction->Item->SellingStatus->CurrentPrice->_;
        $quantity = $transaction->QuantityPurchased;
        
        $sql = "insert into qo_orders_detail (ordersId,skuId,itemId,itemTitle,quantity,unitPriceCurrency,unitPriceValue,ebayTranctionId,ebayOrderId,finalValueFee) values 
        ('".$orderId."','".$transaction->Item->SKU."','".$transaction->Item->ItemID."','".$transaction->Item->Title."','".$quantity."','".$unitPriceCurrency."','".$unitPriceValue."','".$transaction->TransactionID."','".$transaction->ContainingOrder->OrderID."','".$transaction->FinalValueFee->_."')";
        $result = mysql_query($sql, eBay::$database_connect);
        if (!$result) {
            $this->errorLog("createOrderDetailFromEbayOrder: sql error ($sql) from DB: " . mysql_error(eBay::$database_connect));
        }
	echo $sql;
	echo "<br>\n<font color='green'>createOrderDetailFromEbayOrder from ".$orderId."</font><br>\n<br>\n";
        //$sql = "update qo_orders set shippingFeeValue = shippingFeeValue + ".$transaction->ShippingServiceSelected->ShippingServiceCost->_." where id = '".$orderId."'";
        //$result = mysql_query($sql, eBay::$database_connect);
    }
    
    private function creteOrderFromEbayOrder($sellerId, $transaction){
        $sql = "select ordersId from qo_orders_detail where ebayOrderId = '".$transaction->ContainingOrder->OrderID."'";
        $result = mysql_query($sql, eBay::$database_connect);
        $row = mysql_fetch_assoc($result);
        if(empty($row['ordersId'])){
            $id = $this->getOrderId();
            $status = "W";
            $shippingMethod = $transaction->ShippingServiceSelected->ShippingService;
            $paymentMethod = "PayPal";
            $sellerId = $sellerId;
            $buyerId = $transaction->Buyer->UserID;
            $shippingFeeCurrency = $transaction->ShippingServiceSelected->ShippingServiceCost->currencyID;
            $shippingFeeValue = $transaction->ShippingServiceSelected->ShippingServiceCost->_;
            $insuranceCurrency = $transaction->ShippingDetails->InsuranceFee->currencyID;
            $insuranceValue = $transaction->ShippingDetails->InsuranceFee->_;
            $grandTotalCurrency = $transaction->AmountPaid->currencyID;
            $grandTotalValue = $transaction->AmountPaid->_;
            $ebayName = $transaction->Buyer->BuyerInfo->ShippingAddress->Name;
            $ebayEmail = $transaction->Buyer->Email;
            $ebayAddress1 = $transaction->Buyer->BuyerInfo->ShippingAddress->Street1;
            $ebayAddress2 = $transaction->Buyer->BuyerInfo->ShippingAddress->Street2;
            $ebayCity = $transaction->Buyer->BuyerInfo->ShippingAddress->CityName;
            $ebayStateOrProvince = $transaction->Buyer->BuyerInfo->ShippingAddress->StateOrProvince;
            $ebayPostalCode = $transaction->Buyer->BuyerInfo->ShippingAddress->PostalCode;
            $ebayCountry = $transaction->Buyer->BuyerInfo->ShippingAddress->CountryName;
            $ebayPhone = $transaction->Buyer->BuyerInfo->ShippingAddress->Phone;
            $createdBy = "eBay";
            //$createdOn = date("Y-m-d H:i:s",strtotime(substr($transaction->CreatedDate, 0 ,-5)) + (8 * 60 * 60));
            $createdOn = date("Y-m-d H:i:s",strtotime($transaction->CreatedDate));
	    
            $sql = "insert into qo_orders (id,status,shippingMethod,paymentMethod,sellerId,buyerId,shippingFeeCurrency,shippingFeeValue,
            insuranceCurrency,insuranceValue,grandTotalCurrency,grandTotalValue,ebayName,ebayEmail,ebayAddress1,ebayAddress2,
            ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone,createdBy,createdOn) values
            ('".$id."','".$status."','".$shippingMethod."','".$paymentMethod."','".$sellerId."','".$buyerId."','".$shippingFeeCurrency."','".$shippingFeeValue."',
            '".$insuranceCurrency."','".$insuranceValue."','".$grandTotalCurrency."','".$grandTotalValue."','".mysql_real_escape_string($ebayName)."',
            '".$ebayEmail."','".mysql_real_escape_string($ebayAddress1)."','".mysql_real_escape_string($ebayAddress2)."',
            '".mysql_real_escape_string($ebayCity)."','".mysql_real_escape_string($ebayStateOrProvince)."','".$ebayPostalCode."',
            '".mysql_real_escape_string($ebayCountry)."','".$ebayPhone."','".$createdBy."','".$createdOn."')";
            $result = mysql_query($sql, eBay::$database_connect);
            if (!$result) {
                $this->errorLog("creteOrderFromEbayOrder: query error ($sql) from DB: " . mysql_error(eBay::$database_connect));
            }else{
		$this->createOrderDetailFromEbayOrder($id, $transaction);
	    }
            echo $sql;
            echo "<br>\n<font color='red'>creteOrderFromEbayOrder from ".$transaction->TransactionID.", ".$transaction->Item->ItemID."</font><br>\n<br>\n";
            $this->mapEbayTransaction($id, $transaction);
        }else{
            $this->createOrderDetailFromEbayOrder($row['ordersId'], $transaction);
	    $this->mapEbayTransaction($row['ordersId'], $transaction);
        }
    }
    
    private function updateOrderFromEbay($ordersId, $sellerId, $transaction){
	$sql = "select status from qo_orders where id = '".$ordersId."'";
	$result = mysql_query($sql, eBay::$database_connect);
	$row = mysql_fetch_assoc($result);
	if($row["status"] == "W"){
		$shippingMethod = $transaction->ShippingServiceSelected->ShippingService;
		$paymentMethod = "PayPal";
		$sellerId = $sellerId;
		$buyerId = $transaction->Buyer->UserID;
		$shippingFeeCurrency = $transaction->ShippingServiceSelected->ShippingServiceCost->currencyID;
		$shippingFeeValue = $transaction->ShippingServiceSelected->ShippingServiceCost->_;
		$insuranceCurrency = $transaction->ShippingDetails->InsuranceFee->currencyID;
		$insuranceValue = $transaction->ShippingDetails->InsuranceFee->_;
		$grandTotalCurrency = $transaction->AmountPaid->currencyID;
		$grandTotalValue = $transaction->AmountPaid->_;
		$ebayName = $transaction->Buyer->BuyerInfo->ShippingAddress->Name;
		$ebayEmail = $transaction->Buyer->Email;
		$ebayAddress1 = $transaction->Buyer->BuyerInfo->ShippingAddress->Street1;
		$ebayAddress2 = $transaction->Buyer->BuyerInfo->ShippingAddress->Street2;
		$ebayCity = $transaction->Buyer->BuyerInfo->ShippingAddress->CityName;
		$ebayStateOrProvince = $transaction->Buyer->BuyerInfo->ShippingAddress->StateOrProvince;
		$ebayPostalCode = $transaction->Buyer->BuyerInfo->ShippingAddress->PostalCode;
		$ebayCountry = $transaction->Buyer->BuyerInfo->ShippingAddress->CountryName;
		$ebayPhone = $transaction->Buyer->BuyerInfo->ShippingAddress->Phone;
		$createdBy = "eBay";
		//$modifiedOn = date("Y-m-d H:i:s",strtotime(substr($transaction->CreatedDate, 0 ,-5)) + (8 * 60 * 60));
		$modifiedOn = date("Y-m-d H:i:s",strtotime($transaction->CreatedDate));
		
		//shippingFeeCurrency='".$shippingFeeCurrency."',
		//shippingFeeValue='".$shippingFeeValue."',insuranceCurrency='".$insuranceCurrency."',insuranceValue='".$insuranceValue."',
		//grandTotalCurrency='".$grandTotalCurrency."',grandTotalValue='".$grandTotalValue."',
		
		$sql = "update qo_orders set 
		paymentMethod='".$paymentMethod."',sellerId='".$sellerId."',buyerId='".$buyerId."',ebayName='".mysql_real_escape_string($ebayName)."',
		ebayEmail='".$ebayEmail."',ebayAddress1='".mysql_real_escape_string($ebayAddress1)."',ebayAddress2='".mysql_real_escape_string($ebayAddress2)."',
		ebayCity='".mysql_real_escape_string($ebayCity)."',ebayStateOrProvince='".mysql_real_escape_string($ebayStateOrProvince)."',
		ebayPostalCode='".$ebayPostalCode."',ebayCountry='".mysql_real_escape_string($ebayCountry)."',ebayPhone='".$ebayPhone."',
		modifiedBy='".$createdBy."',modifiedOn='".$modifiedOn."' where id = '".$ordersId."'";
		$result = mysql_query($sql, eBay::$database_connect);
		if (!$result) {
		    $this->errorLog("updateOrderFromEbayOrder: query error ($sql) from DB: " . mysql_error(eBay::$database_connect));
		}
		echo $sql;
		echo "<br>\n<font color='green'>updateOrderFromEbay from ".$transaction->TransactionID.", ".$transaction->Item->ItemID."</font><br>\n<br>\n";
		$this->mapEbayTransaction($ordersId, $transaction);
	}
    }
    
    private function updateOrderStatus($ordersId, $grandTotalValue, $amountValue){
	if($amountValue > ($grandTotalValue + ($grandTotalValue * 0.02))){
		$sql = "update qo_orders set status = 'C',modifiedBy='eBay',modifiedOn='".date("Y-m-d H:i:s")."' where id = '".$ordersId."'";
	}elseif($amountValue < ($grandTotalValue - ($grandTotalValue * 0.02))){
		$sql = "update qo_orders set status = 'S',modifiedBy='eBay',modifiedOn='".date("Y-m-d H:i:s")."' where id = '".$ordersId."'";
	}else{
		$sql = "update qo_orders set status = 'P',modifiedBy='eBay',modifiedOn='".date("Y-m-d H:i:s")."' where id = '".$ordersId."'";
		//$this->createShipmentFromEbay($ordersId);
	}
	echo $sql;
	echo "<br>\n<font color='green'>updateOrderStatus in ".$ordersId."</font><br>\n<br>\n";
	$result = mysql_query($sql, eBay::$database_connect);
    }
    
    private function updateOrderPayPalAddress($ordersId, $transactionsId){
	$sql = "select payerName,payerEmail,payerAddressLine1,payerAddressLine2,payerCity,payerStateOrProvince,payerPostalCode,payerCountry,transactionTime from qo_transactions where id = '".$transactionsId."'";
	$result = mysql_query($sql, eBay::$database_connect);
	$row = mysql_fetch_assoc($result);
	$sql = "update qo_orders set paypalName='".mysql_real_escape_string($row['payerName'])."',paypalEmail='".$row['payerEmail']."',paypalAddress1='".mysql_real_escape_string($row['payerAddressLine1'])."',paypalAddress2='".mysql_real_escape_string($row['payerAddressLine2'])."',
	paypalCity='".mysql_real_escape_string($row['payerCity'])."',paypalStateOrProvince='".mysql_real_escape_string($row['payerStateOrProvince'])."',paypalPostalCode='".$row['payerPostalCode']."',paypalCountry='".$row['payerCountry']."',modifiedBy='eBay',modifiedOn='".date("Y-m-d H:i:s")."' where id = '".$ordersId."'";
	echo $sql;
	echo "<br>\n<font color='green'>updateOrderPayPalAddress ".$ordersId." and ".$transactionsId."</font><br>\n<br>\n";
	$result = mysql_query($sql, eBay::$database_connect);
    }
    
    private function getShipmentId(){
	$type = 'SHI';
        $today = date("Ym");
        $sql = "select curType,curId from sequence where curDate='$today' and type='$type'";
        $result = mysql_query($sql, eBay::$database_connect);
        $row = mysql_fetch_assoc($result);
       
        if($row["curId"] >=9999){
            // A-Z 66-91
            $curType = chr(ord($row["curType"]) + 1);
            $sql = "update  sequence  set curId = 1,curType='$curType' where curDate='$today' and type='$type'";
            mysql_query($sql, eBay::$database_connect);
        }elseif($row["curId"] < 1 || $row["curId"] == null) {
              $sql = "insert into sequence (type,curType,curDate,curId) value ('$type','A','$today',1)";
              mysql_query($sql, eBay::$database_connect);
        }else {   
            $sql = "update sequence set curId = curId + 1 where curDate='$today' and type='$type'";
            $result = mysql_query($sql, eBay::$database_connect);
        }
       
        $sql = "select curType,curId from sequence where curDate='$today' and type='$type'";
        $result = mysql_query($sql, eBay::$database_connect);
        $row = mysql_fetch_assoc($result);
        $shipmentId = $type.$today.$row["curType"].str_repeat("0",(4-strlen($row["curId"]))).$row["curId"];   
        return $shipmentId;
    }
    
    private function createShipmentFromEbay($ordersId){
	$shipmentsId = $this->getShipmentId();
	$sql = "insert into qo_shipments (id,ordersId,status,shipmentMethod,remarks,shippingFeeCurrency,shippingFeeValue,shipToName,
	shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,shipToCountry,
	shipToPhoneNo,createdBy,createdOn,modifiedBy,modifiedOn) select '".$shipmentsId."','".$ordersId."','N',shippingMethod,remarks,
	shippingFeeCurrency,shippingFeeValue,ebayName,ebayEmail,ebayAddress1,ebayAddress2,ebayCity,
	ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone,'eBay','".date("Y-m-d H:i:s")."','eBay','".date("Y-m-d H:i:s")."' from qo_orders where id = '".$ordersId."'";
	echo $sql."<br>\n";
	echo "<br>\n<font color='green'>createShipmentFromEbay create shipment from ".$ordersId."</font><br>\n<br>\n";
	$result = mysql_query($sql, eBay::$database_connect);
	if($result){
		$sql = "insert into qo_shipments_detail (shipmentsId,skuId,skuTitle,itemId,itemTitle,quantity,barCode) 
		select '".$shipmentsId."',skuId,skuTitle,itemId,itemTitle,quantity,barCode from qo_orders_detail where ordersId='".$ordersId."'";
		$result = mysql_query($sql, eBay::$database_connect);
		echo $sql."<br>\n";
		echo "<br>\n<font color='green'>createShipmentFromEbay add shipment detail from ".$ordersId."</font><br>\n<br>\n";
	}
    }
    
    private function mapEbayTransaction($ordersId, $transaction){
	$sevenDayAgo = date("Y-m-d H:i:s", time() - (7 * 24 * 60 * 60));
	
	$sql = "select id,itemId,amountValue,status from qo_transactions where payerId = '".$transaction->Buyer->UserID."' and createdOn > '".$sevenDayAgo."' order by transactionTime desc";
	$result = mysql_query($sql, eBay::$database_connect);
	while($row = mysql_fetch_assoc($result)){
		//$itemNumber = explode(" ", $row['itemId']);
		$itemNumber = "hs".$row['itemId'];
		$num = 0;
		$success_num = 0;
		$sql_1 = "select itemId from qo_orders_detail where ordersId = '".$ordersId."'";
		$result_1 = mysql_query($sql_1, eBay::$database_connect);
		while($row_1 = mysql_fetch_assoc($result_1)){
			if(strpos($itemNumber, $row_1['itemId'])){
				$success_num++;
			}
			$num++;
		}
		if($success_num == $num){
			$sql_4 = "select count(*) as num from qo_orders_transactions where transactionsId = '".$row['id']."'";
			$result_4 = mysql_query($sql_4, eBay::$database_connect);
			$row_4 = mysql_fetch_assoc($result_4);
			if($row_4['num'] == 0){
				$sql_2 = "select count(*) as num from qo_orders_transactions where ordersId = '".$ordersId."' and transactionsId = '".$row['id']."'";
				$result_2 = mysql_query($sql_2, eBay::$database_connect);
				$row_2 = mysql_fetch_assoc($result_2);
				if($row_2['num'] == 0){
					$sql_3 = "insert into qo_orders_transactions (ordersId,transactionsId,status,amountPayCurrency,
					amountPayValue,createdBy,createdOn,modifiedBy,modifiedOn) values
					('".$ordersId."','".$row['id']."','A','".$transaction->AmountPaid->currencyID."',
					'".$transaction->AmountPaid->_."','eBay','".date("Y-m-d H:i:s")."','eBay','".date("Y-m-d H:i:s")."')";
					echo "map ebay transaction: ",$sql_3."<br>\n";
					$result_3 = mysql_query($sql_3, eBay::$database_connect);
					$this->updateOrderPayPalAddress($ordersId, $row['id']);
					if($row['status'] == 'P'){
						$this->updateOrderStatus($ordersId, $transaction->AmountPaid->_, $row['amountValue']);
					}
				}else{
					if($row['status'] == 'P'){
						$this->updateOrderStatus($ordersId, $transaction->AmountPaid->_, $row['amountValue']);
					}
				}
			}
			break;	
		}
	}
    }
    
    public function createOrderFromEbay($ModTimeFrom, $ModTimeTo, $sellerId, $dev, $app, $cert, $token, $proxy_host, $proxy_port){
        $result = $this->GetSellerTransactions($ModTimeFrom, $ModTimeTo, $sellerId, $dev, $app, $cert, $token, $proxy_host, $proxy_port);
        //print_r($result);
        $TotalNumberOfPages = $result->PaginationResult->TotalNumberOfPages;
	$TotalNumberOfEntries = $result->PaginationResult->TotalNumberOfEntries;
        echo "<br>\n<br>\n<br>\n<br>\n".date("Y-m-d H:i:s")."------------------------------------------- ".$sellerId." Start ----------------------------------------------------------";
	echo "<br>\n<br>\n";
	echo date("Y-m-d H:i:s")."  createOrderFromEbay from ".$sellerId.", total number: ".$TotalNumberOfEntries.", total page: ".$TotalNumberOfPages."<br>\n<br>\n";
	
	if($TotalNumberOfEntries == 0){
		return 0;
	}
	
        if(is_array($result->TransactionArray->Transaction)){
		foreach ($result->TransactionArray->Transaction as $transaction){
			//CompleteStatus  Incomplete > Complete
			//CheckoutStatus CheckoutIncomplete > CheckoutComplete
			//BuyerSelectedShipping  false/true
			//if($transaction->Status->CompleteStatus == "Complete"){
			if($transaction->Status->CheckoutStatus != "SellerResponded"){
				$ordersId = $this->checkEbayTransactionExist($transaction->Item->ItemID, $transaction->TransactionID);
				//var_dump($ordersId);
				if(empty($ordersId)){
					//Create 
					if(empty($transaction->ContainingOrder)){
					    $this->createOrderFromEbayTransaction($result->Seller->UserID, $transaction);
					}else{
					    $this->creteOrderFromEbayOrder($result->Seller->UserID, $transaction);
					}
				}else{
					//Update
					$this->updateOrderFromEbay($ordersId, $result->Seller->UserID, $transaction);
				}
			}
			//else{
			//	echo "TransactionID: ".$transaction->TransactionID . ", ItemID: " .$transaction->Item->ItemID.", PaymentMethodUsed: ".$transaction->Status->PaymentMethodUsed."<br>\n\n";
			//}
		}
        }else{
		$ordersId = $this->checkEbayTransactionExist($result->TransactionArray->Transaction->Item->ItemID, $result->TransactionArray->Transaction->TransactionID);
		if(empty($ordersId)){
			//Incomplete --> Complete
			//if($result->TransactionArray->Transaction->Status->CompleteStatus == "Complete"){
			if($result->TransactionArray->Transaction->Status->CheckoutStatus != "SellerResponded"){
			    if(empty($result->TransactionArray->Transaction->ContainingOrder)){
				$this->createOrderFromEbayTransaction($result->Seller->UserID, $result->TransactionArray->Transaction);
			    }else{
				$this->creteOrderFromEbayOrder($result->Seller->UserID, $result->TransactionArray->Transaction);
			    }
			}
			//else{
			//	echo "TransactionID: ".$result->TransactionArray->Transaction->TransactionID . ", ItemID: " .$result->TransactionArray->Transaction->Item->ItemID.", PaymentMethodUsed: ".$result->TransactionArray->Transaction->Status->PaymentMethodUsed. "<br>\n\n";
			//}
		}else{
			$this->updateOrderFromEbay($ordersId, $result->Seller->UserID, $result->TransactionArray->Transaction);
		}
        }
	echo "<br>\n<br>\n<br>\n<br>\n".date("Y-m-d H:i:s")."------------------------------------------- ".$sellerId." End ----------------------------------------------------------";
    }
    
    public function getAllEbayTransaction($id=""){
		if(empty($id)){	
			$sql = "select es.id,es.devId,es.appId,es.cert,es.token,ep.proxy_host,ep.proxy_port from qo_ebay_seller as es left join qo_ebay_proxy as ep on es.id=ep.ebay_seller_id where es.status='A'";
		}else{
			$sql = "select es.id,es.devId,es.appId,es.cert,es.token,ep.proxy_host,ep.proxy_port from qo_ebay_seller as es left join qo_ebay_proxy as ep on es.id=ep.ebay_seller_id where es.status='A' and es.id='".$id."'";
		}
		$result = mysql_query($sql, eBay::$database_connect);
		while ($row = mysql_fetch_assoc($result)){
			//authToken   devId  appId  cert  gatewaySOAP
			$this->createOrderFromEbay($this->startTime, $this->endTime, $row['id'], $row['devId'], $row['appId'], $row['cert'], $row['token'], $row['proxy_host'], $row['proxy_port']);
			
		}
    }
    
    private function checkEbayItemExist($id){
	$sql = "select count(id) as num from qo_items where id = '".$id."'";
	$result = mysql_query($sql, eBay::$database_connect);
        $row = mysql_fetch_assoc($result);
	return $row['num'];
    }
    
    private function insertEbayItem($item, $sellerId){
	$sql = "insert into qo_items (id,skuId,site,title,quantity,quantitySold,sellerId,ListingType,StartTime,EndTime,GalleryURL) values 
	('".$item->ItemID."','".mysql_real_escape_string($item->SKU)."','".$item->Site."','".mysql_real_escape_string($item->Title)."','".$item->Quantity."','".$item->SellingStatus->QuantitySold."',
	'".$sellerId."','".$item->ListingType."','".date("Y-m-d H:i:s",strtotime($item->ListingDetails->StartTime))."','".date("Y-m-d H:i:s",strtotime($item->ListingDetails->EndTime))."','".$item->PictureDetails->GalleryURL."')";
	echo $sql."<br>\n";
	$result = mysql_query($sql, eBay::$database_connect);
    }
    
    private function updateEbayItem($item, $sellerId){
	$sql = "update qo_items set skuId='".mysql_real_escape_string($item->SKU)."',site='".$item->Site."',title='".mysql_real_escape_string($item->Title)."',
	quantity='".$item->Quantity."',quantitySold='".$item->SellingStatus->QuantitySold."',sellerId='".$sellerId."',
	ListingType='".$item->ListingType."',StartTime='".date("Y-m-d H:i:s",strtotime($item->ListingDetails->StartTime))."',EndTime='".date("Y-m-d H:i:s",strtotime($item->ListingDetails->EndTime))."',GalleryURL='".$item->PictureDetails->GalleryURL."' 
	where id = '".$item->ItemID."'";
	echo $sql."<br>\n";
	$result = mysql_query($sql, eBay::$database_connect);
    }
    
    private function getSellerList($StartTimeFrom, $StartTimeTo, $sellerId, $dev, $app, $cert, $token, $proxy_host, $proxy_port){
	$session = $this->configEbay($dev, $app, $cert, $token, $proxy_host, $proxy_port);
        try {
                $client = new eBaySOAP($session);
                $GranularityLevel = "Fine";
		$EntriesPerPage = 200;
		$Pagination = array('EntriesPerPage'=> $EntriesPerPage, 'PageNumber'=> 1);
		$Sort = 1;
		$Version = "607";
		$UserID = $sellerId;
		$DetailLevel = "ReturnAll";
		//$EndTimeFrom = "2008-04-05 16:00:00";
		//$EndTimeTo   = "2008-04-10 00:00:00";
		//$UserID = "aqualuna0001";
		$params = array('Version' => $Version, 'GranularityLevel' =>$GranularityLevel, 'Pagination' => $Pagination, 'Sort' => $Sort, 'StartTimeFrom' => $StartTimeFrom, 'StartTimeTo' => $StartTimeTo, 'UserID' => $UserID, 'DetailLevel' => $DetailLevel);
		$results = $client->GetSellerList($params);
		
		$TotalNumberOfPages = $results->PaginationResult->TotalNumberOfPages;
		$TotalNumberOfEntries = $results->PaginationResult->TotalNumberOfEntries;
		echo "<br>\n<br>\n";
		echo "-------------------------------------------------------------- ".$sellerId." start -----------------------------------------------------------------";
		echo "<br>\n<br>\n";
		echo date("Y-m-d H:i:s")."  getSellerList from ".$sellerId.", total number: ".$TotalNumberOfEntries.", total page: ".$TotalNumberOfPages."<br>\n<br>\n";
	
		
		//----------   debug --------------------------------
		//print_r($results);
                //print "Request: \n".$client->__getLastRequest() ."\n";
                //print "Response: \n".$client->__getLastResponse()."\n";
		//$this->saveFetchData("/GetSellerList/".$sellerId.'-Request-'.date("Y-m-d H:i:s").".xml", $client->__getLastRequest());
		//$this->saveFetchData("/GetSellerList/".$sellerId.'-Response-'.date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		$this->saveFetchData($sellerId, "GetSellerList-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		
		if($results->PaginationResult->TotalNumberOfPages == 0)
			return 0;
		
		if(is_array($results->ItemArray->Item)){
			foreach ($results->ItemArray->Item as $item){
				if($this->checkEbayItemExist($item->ItemID) == 0){
					$this->insertEbayItem($item, $UserID);
				}else{
					$this->updateEbayItem($item, $UserID);
				}
			}
		}else{
			if($this->checkEbayItemExist($results->ItemArray->Item->ItemID) == 0){
				$this->insertEbayItem($results->ItemArray->Item, $UserID);
			}else{
				$this->updateEbayItem($results->ItemArray->Item, $UserID);
			}
		}
		
		$TotalNumberOfPages = $results->PaginationResult->TotalNumberOfPages;  //listing total pages
		if($TotalNumberOfPages > 1){
			for($i=2; $i <= $TotalNumberOfPages; $i++){
				$Pagination = array('EntriesPerPage'=> $EntriesPerPage,'PageNumber'=> $i);
				$params = array('Version' => $Version, 'GranularityLevel' =>$GranularityLevel, 'Pagination' => $Pagination, 'Sort' => $Sort, 'StartTimeFrom' => $StartTimeFrom, 'StartTimeTo' => $StartTimeTo, 'UserID' => $UserID, 'DetailLevel' => $DetailLevel);
			    	$results = $client->GetSellerList($params);
				
				if(is_array($results->ItemArray->Item)){
					foreach ($results->ItemArray->Item as $item){
						if($this->checkEbayItemExist($item->ItemID) == 0){
							$this->insertEbayItem($item, $UserID);
						}else{
							$this->updateEbayItem($item, $UserID);
						}
				    	}
				}else{
					if($this->checkEbayItemExist($results->ItemArray->Item->ItemID) == 0){
						$this->insertEbayItem($results->ItemArray->Item, $UserID);
					}else{
						$this->updateEbayItem($results->ItemArray->Item, $UserID);
					}
				}
				$this->saveFetchData($sellerId, "GetSellerList-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
				sleep(1);
			}	
		}
		
		echo "<br>\n<br>\n";
		echo "-------------------------------------------------------------- ".$sellerId." end -----------------------------------------------------------------";
                echo "<br>\n<br>\n";
        } catch (SOAPFault $f) {
		$this->api_error_log("getSellerList", print_r($f, true));
                //print $f; // error handling
        }
    }
    
    public function getAllSellerList($id=""){
		if(empty($id)){	
			$sql = "select es.id,es.devId,es.appId,es.cert,es.token,ep.proxy_host,ep.proxy_port from qo_ebay_seller as es left join qo_ebay_proxy as ep on es.id=ep.ebay_seller_id where es.status='A'";
		}else{
			$sql = "select es.id,es.devId,es.appId,es.cert,es.token,ep.proxy_host,ep.proxy_port from qo_ebay_seller as es left join qo_ebay_proxy as ep on es.id=ep.ebay_seller_id where es.status='A' and es.id='".$id."'";
		}
		$result = mysql_query($sql, eBay::$database_connect);
		while ($row = mysql_fetch_assoc($result)){
			//authToken   devId  appId  cert  gatewaySOAP
			$this->getSellerList($this->startTime, $this->endTime, $row['id'], $row['devId'], $row['appId'], $row['cert'], $row['token'], $row['proxy_host'], $row['proxy_port']);
		}
    }
    
    public function getToken(){
	$session = $this->configEbay();
        try {
		$session->token = NULL;
		//print_r($session);
		//exit;
                $client = new eBaySOAP($session);
                
		$Version = "607";
		$RuName = "Creasion-Creasion-1ca1-4-vldylhxcb";
                $params = array('Version' => $Version, 'RuName' => $RuName);
                $results = $client->GetSessionID($params);
		//$results->SessionID
		//echo "https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=Creasion-Creasion-1ca1-4-vldylhxcb&&sid=$results->SessionID";
		header("Location: https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=".$RuName."&sid=".$results->SessionID);
		//var_dump("https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=Creasion-Creasion-1ca1-4-vldylhxcb&&sid=$results->SessionID");
                //----------   debug --------------------------------
                //print "Request: \n".$client->__getLastRequest() ."\n";
                //print "Response: \n".$client->__getLastResponse()."\n";
        
                //return $results;
                
        } catch (SOAPFault $f) {
                print $f; // error handling
        }
    }
    
    public function saveToken(){
	$sql = "insert into qo_ebay_seller (id,token,tokenExpiry) values ('".$_GET['username']."','".$_GET['ebaytkn']."','".$_GET['tknexp']."')";
	echo $sql;
	//$result = mysql_query($sql, eBay::$database_connect);
	if($result){
		echo "<h1>Thank you, Success!</h1>";
	}else{
		echo "<h1>Failure!</h1>";
	}
    }
    
    private function errorLog($text){
        $sql = "insert into qo_error_log (text,timestamp) values ('".mysql_real_escape_string($text)."','".date("Y-m-d H:i:s")."')";
        echo "<br>\n<font color='red'>".$sql."</font><br>\n";
        $result = mysql_query($sql, eBay::$database_connect);
    }
    
    public function __destruct(){
        mysql_close(eBay::$database_connect);
    }
}


if(!empty($GLOBALS['HTTP_RAW_POST_DATA'])){
	$config = parse_ini_file('ebay.ini', true);
	$site = $config['settings']['site'];
	$dev = $config[$site]['devId'];
	$app = $config[$site]['appId'];
	$cert = $config[$site]['cert'];
    
	$session = new eBaySession($dev, $app, $cert);
	
	error_log(serialize(apache_request_headers()));
	
	//error_log("trying to listen");
	
	$stdin = $GLOBALS['HTTP_RAW_POST_DATA'];
	file_put_contents('GetItemRequest.xml', $stdin);
	error_log($stdin);
	
	
	$server = new SOAPServer(null, array('uri'=>'urn:ebay:apis:eBLBaseComponents'));
	$server->setClass('eBayPlatformNotificationListener', $session, true);
	$server->handle();
	
}else{
	$action = (!empty($_GET['action'])?$_GET['action']:$argv[1]);

	switch($action){
		case "getToken":
			$eBay = new eBay();
			$eBay->getToken();
		break;
		
		case "saveToken":
			$eBay = new eBay();
			$eBay->saveToken();
		break;
	
		case "getAllSellerList":
			$eBay = new eBay();
			$id = (!empty($_GET['id'])?$_GET['id']:$argv[4]);
			if(!empty($argv[2]) && !empty($argv[3])){
				$eBay->setStartTime($argv[2]);
				$eBay->setEndTime($argv[3]);
			}elseif(!empty($_GET['start']) && !empty($_GET['end'])){
				$eBay->setStartTime($_GET['start']);
				$eBay->setEndTime($_GET['end']);
			}else{
				$eBay->setStartTime(date("Y-m-d H:i:s", time() - ((10 * 60 * 60))));
				$eBay->setEndTime(date("Y-m-d H:i:s", time() - ((8 * 60 * 60))));
			}
			$eBay->getAllSellerList($id);
			
		break;
	
		case "getAllEbayTransaction":
			$eBay = new eBay();
			$id = (!empty($_GET['id'])?$_GET['id']:$argv[4]);
			if(!empty($argv[2]) && !empty($argv[3])){
				$eBay->setStartTime($argv[2]);
				$eBay->setEndTime($argv[3]);
			}elseif(!empty($_GET['start']) && !empty($_GET['end'])){
				$eBay->setStartTime($_GET['start']);
				$eBay->setEndTime($_GET['end']);
			}else{
				$eBay->setStartTime(date("Y-m-d H:i:s", time() - (12 * 60 * 60)));
				$eBay->setEndTime(date("Y-m-d H:i:s", time() - (8 * 60 * 60)));
			}
			$eBay->getAllEbayTransaction($id);
			//$test->GetSellerTransactions('','','AgAAAA**AQAAAA**aAAAAA**FmQISA**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wHlIOnCZaBpAWdj6x9nY+seQ**Q2AAAA**AAMAAA**K5e4SqBc83jVbhFXLTi50I3ptbripiwsQUS3jIeIcDvpmXzELIv5yXhhUp8jB/r9sdMAKZP2GxQB8g85Eq09tNqTWhmSLMGmFRXq3gBeof7enC9Ch4L0JLBf4rSDdyGWkwa8zpnVueMbOwQbExhx1UjkGZXsDfIxO8vU1FaeGW2tLjVkOffg/0NkzrhwQnoR63SeZ/aPkns9sBbaqkGH7VsoYSik0C/pkO8V9gfxJIuIDjRNuOQ6Stx0UWRNnTPyZRZpWDShtsh9horFcmRsB34ZRxxAkaxx3UmskFoqwxNviz1vYrjEqZlbV2KkQsF+iCOT5lu2YdFTeTZ2uv3/PY1zw+J7sdvK3tI4ucKNKTNLbrBIco0XW/ImHhRoNsun4AizgcHP4HQOwzzuwXnc53Z1QqehYQZsOvMCx+cU+Z2zlA/MP6z7NgdCuHdaYRJbYgINxfDxAuxKCnjzyozpgV6Smk/o7dOBAaZKclEEClNAg3xIpjnyamBh4EBUzk0/tYePv5K2PA6nClMu58PWd7HcGcP/X4FCDnxiDbu5ndxcntPfec6ztdC5f2FHDJJ7ACY9PjRdIYWUQBsgwhV6yZs3t0N1SfR5yuy0tW+fOX4Uw4RkPcMbrgHk9H8m5JEae8YaQMfNkuk3TCKwjjEE+25LDFgpbiTAEu4sYs7FxGhBQBr4RbhoLR6TTdnu0xhpvO2vC4lPb6FQmb9vGRaTv3uxdh2xgMJgD7bhAqt+1vnET+xKvGDrvIFp1XxJ7ij2');
			//$test = new eBay();
			//$test->createOrderFromEbay();
		break;
	}
}

//0 */1 * * * root php -q /export/eBayBO/cron/eBay.php getAllSellerList >> /tmp/getAllSellerList.log
//30 */2 * * * root php -q /export/eBayBO/cron/eBay.php getAllEbayTransaction >> /tmp/getAllEbayTransaction.log
//http://heshuai64.3322.org/eBayBO/cron/eBay.php?action=getAllEbayTransaction&start=2009-04-18 00:00:00&end=2009-04-19 00:00:00
//http://heshuai64.3322.org/eBayBO/cron/eBay.php?action=getAllSellerList&start=2009-04-17 00:00:00&end=2009-04-19 00:00:00
//php -q /export/eBayBO/cron/eBay.php getAllSellerList 2009-05-05 2009-05-06 bestnbestonline



?>