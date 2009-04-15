<?php
require_once 'eBaySOAP.php';

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
    const DATABASE_PASSWORD = '';
    const DATABASE_NAME = 'ebaybo';
    const GATEWAY_SOAP = 'https://api.sandbox.ebay.com/wsapi';
    private $startTime;
    private $endTime;
    
    public function __construct(){
        eBay::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

        if (!eBay::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(eBay::$database_connect);
            exit;
        }
          
        if (!mysql_select_db(self::DATABASE_NAME, eBay::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(eBay::$database_connect);
            exit;
        }
       
    }
    
    public function setStartTime($startTime){
	$this->startTime = $startTime;
    }
    
    public function setEndTime($endTime){
	$this->endTime = $endTime;
    }
    
    private function configEbay($dev, $app, $cert, $token, $proxy_host, $proxy_port){
    	
	// Load developer-specific configuration data from ini file
	$config = parse_ini_file('ebay.ini', true);
	$site = $config['settings']['site'];
	//$compatibilityLevel = $config['settings']['compatibilityLevel'];
	
	$dev = $config[$site]['devId'];
	$app = $config[$site]['appId'];
	$cert = $config[$site]['cert'];
	//$token = $config[$site]['authToken'];
	$location = $config[$site]['gatewaySOAP'];
	
	    
	// Create and configure session
	$session = new eBaySession($dev, $app, $cert, $proxy_host, $proxy_port);
	$session->token = $token;
	$session->site = 0; // 0 = US;
	$session->location = $location;
	//$session->location = self::GATEWAY_SOAP;
	
	return $session;
    }
    
    private function GetSellerTransactions($ModTimeFrom, $ModTimeTo, $sellerId, $dev, $app, $cert, $token, $proxy_host, $proxy_port){
        $sql = "select token from qo_ebay_seller where id = '".$sellerId."'";
        $result = mysql_query($sql, eBay::$database_connect);
        $row = mysql_fetch_assoc($result);
        $token = $row['token'];
        
        $session = $this->configEbay($dev, $app, $cert, $token, $proxy_host, $proxy_port);
        try {
                $client = new eBaySOAP($session);
                 
                //$ModTimeFrom = "2009-03-25 00:00:00";
                //$ModTimeTo   = "2009-03-25 02:00:00";
                $EntriesPerPage = 100;
                $Version = '607';
                $DetailLevel = "ReturnAll";
                $IncludeContainingOrder = true;
                $IncludeFinalValueFee = true;
                $Pagination = array('EntriesPerPage'=> $EntriesPerPage, 'PageNumber'=> 1);
             
                $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel, 'Pagination' => $Pagination, 'IncludeContainingOrder' => $IncludeContainingOrder, 'IncludeFinalValueFee' => $IncludeFinalValueFee, 'ModTimeFrom' => $ModTimeFrom, 'ModTimeTo' => $ModTimeTo);
                $results = $client->GetSellerTransactions($params);
                //----------   debug --------------------------------
                print "Request: \n".$client->__getLastRequest() ."\n";
                print "Response: \n".$client->__getLastResponse()."\n";
        
                return $results;
                
        } catch (SOAPFault $f) {
                print $f; // error handling
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
            $sql = "select count(*) as num from qo_orders_detail where itemId = '".$itemId."' and ebayTranctionId = '".$ebayTransactionId."'";
            $result = mysql_query($sql, eBay::$database_connect);
            //echo "<br><font color='green'>".$sql."</font><br>";
            $row = mysql_fetch_assoc($result);
            return $row['num'];
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
            $createdOn = date("Y-m-d H:i:s",strtotime(substr($transaction->CreatedDate, 0 ,-5)) + (8 * 60 * 60));
            
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
            echo "<br><font color='green'>".$transaction->Item->ItemID." create ebay transaction!</font><br><br>";
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
        $unitPriceValue = $transaction->Item->SellingStatus->CurrentPrice->_;
        $quantity = $transaction->Item->SellingStatus->QuantitySold;
        
        $sql = "insert into qo_orders_detail (ordersId,itemId,itemTitle,quantity,unitPriceCurrency,unitPriceValue,ebayTranctionId) values 
        ('".$orderId."','".$transaction->Item->ItemID."','".$transaction->Item->Title."','".$quantity."','".$unitPriceCurrency."','".$unitPriceValue."','".$transaction->TransactionID."')";
        $result = mysql_query($sql, eBay::$database_connect);
        if (!$result) {
            $this->errorLog("createOrderDetailFromEbayTransaction: sql error ($sql) from DB: " . mysql_error(eBay::$database_connect));
        }
    
    }
    
    private function AddOrderDetailBySameBuy($transaction, $orderId){
        $unitPriceCurrency = $transaction->Item->SellingStatus->CurrentPrice->currencyID;
        $unitPriceValue = $transaction->Item->SellingStatus->CurrentPrice->_;
        $quantity = $transaction->Item->SellingStatus->QuantitySold;
        
        //consider order status
        if(empty($transaction->ContainingOrder)){
            $sql = "insert into qo_orders_detail (ordersId,itemId,itemTitle,quantity,unitPriceCurrency,unitPriceValue,ebayTranctionId) values 
            ('".$orderId."','".$transaction->Item->ItemID."','".$transaction->Item->Title."','".$quantity."','".$unitPriceCurrency."','".$unitPriceValue."','".$transaction->TransactionID."')";
        }else{
            $sql = "insert into qo_orders_detail (ordersId,itemId,itemTitle,quantity,unitPriceCurrency,unitPriceValue,ebayTranctionId,ebayOrderId) values 
            ('".$orderId."','".$transaction->Item->ItemID."','".$transaction->Item->Title."','".$quantity."','".$unitPriceCurrency."','".$unitPriceValue."','".$transaction->TransactionID."','".$transaction->ContainingOrder->OrderID."')";
        }
        
        $result = mysql_query($sql, eBay::$database_connect);
        if (!$result) {
            $this->errorLog("AddOrderDetailBySameBuy: sql error ($sql) from DB: " . mysql_error(eBay::$database_connect));
        }
        
        if(empty($transaction->ShippingServiceSelected->ShippingServiceCost->_)){
            $transaction->ShippingServiceSelected->ShippingServiceCost->_ = 0;
        }
        $sql = "update qo_orders set grandTotalValue = grandTotalValue + ".$transaction->AmountPaid->_.",shippingFeeValue = shippingFeeValue + ".$transaction->ShippingServiceSelected->ShippingServiceCost->_." where id = '".$orderId."'";
        echo "AddOrderDetailBySameBuy: ".$transaction->Item->ItemID."<br>";
        echo $sql."<br>";
        $result = mysql_query($sql, eBay::$database_connect);
        if (!$result) {
            $this->errorLog("AddOrderDetailBySameBuy: sql error ($sql) from DB: " . mysql_error(eBay::$database_connect));
        }
    }
    
    private function createOrderDetailFromEbayOrder($orderId, $transaction){
        $unitPriceCurrency = $transaction->Item->SellingStatus->CurrentPrice->currencyID;
        $unitPriceValue = $transaction->Item->SellingStatus->CurrentPrice->_;
        $quantity = $transaction->Item->SellingStatus->QuantitySold;
        
        $sql = "insert into qo_orders_detail (ordersId,itemId,itemTitle,quantity,unitPriceCurrency,unitPriceValue,ebayTranctionId,ebayOrderId) values 
        ('".$orderId."','".$transaction->Item->ItemID."','".$transaction->Item->Title."','".$quantity."','".$unitPriceCurrency."','".$unitPriceValue."','".$transaction->TransactionID."','".$transaction->ContainingOrder->OrderID."')";
        $result = mysql_query($sql, eBay::$database_connect);
        if (!$result) {
            $this->errorLog("createOrderDetailFromEbayOrder: sql error ($sql) from DB: " . mysql_error(eBay::$database_connect));
        }
    
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
            $createdOn = date("Y-m-d H:i:s",strtotime(substr($transaction->CreatedDate, 0 ,-5)) + (8 * 60 * 60));
            
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
            echo "<br><font color='green'>".$transaction->Item->ItemID." create ebay order!</font><br><br>";
            $this->mapEbayTransaction($id, $transaction);
        }else{
            $this->AddOrderDetailBySameBuy($transaction, $row['ordersId']);
        }
    }
    
    private function mapEbayTransaction($ordersId, $transaction){
	$sql = "select id,itemId from qo_transactions where payerId ='".$transaction->Buyer->UserID."' order by transactionTime desc";
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
			$sql_2 = "select count(*) as num from qo_orders_transactions where ordersId = '".$ordersId."' and transactionsId = '".$row['id']."'";
			$result_2 = mysql_query($sql_2, eBay::$database_connect);
			$row_2 = mysql_fetch_assoc($result_2);
			if($row_2['num'] == 0){
				$sql_3 = "insert into qo_orders_transactions (ordersId,transactionsId,status,amountPayCurrency,
				amountPayValue,createdBy,createdOn,modifiedBy,modifiedOn) values
				('".$ordersId."','".$row['id']."','A','".$transaction->AmountPaid->currencyID."',
				'".$transaction->AmountPaid->_."','eBay','".date("Y-m-d H:i:s")."','eBay','".date("Y-m-d H:i:s")."')";
				echo "map ebay transaction: ",$sql_3."<br>";
				$result_3 = mysql_query($sql_3, eBay::$database_connect);
			}
			break;	
		}
	}
    }
    
    public function createOrderFromEbay($ModTimeFrom, $ModTimeTo, $sellerId, $dev, $app, $cert, $token, $proxy_host, $proxy_port){
        $result = $this->GetSellerTransactions($ModTimeFrom, $ModTimeTo, $sellerId, $dev, $app, $cert, $token, $proxy_host, $proxy_port);
        print_r($result);
        $TotalNumberOfPages = $result->PaginationResult->TotalNumberOfPages;
        echo "total number: ".$result->PaginationResult->TotalNumberOfEntries."<br>";
        echo "total page: ".$TotalNumberOfPages."<br>";
        
        if(is_array($result->TransactionArray->Transaction)){
            foreach ($result->TransactionArray->Transaction as $transaction){
                //Incomplete --> Complete
                //if($transaction->Status->CompleteStatus == "Complete"){
                if(1 == 1){
                    if(!$this->checkEbayTransactionExist($transaction->Item->ItemID, $transaction->TransactionID)){
                        if(empty($transaction->ContainingOrder)){
                            $this->createOrderFromEbayTransaction($result->Seller->UserID, $transaction);
                        }else{
                            $this->creteOrderFromEbayOrder($result->Seller->UserID, $transaction);
                        }
                    }
                }else{
                    var_dump($transaction->Status->CompleteStatus);
                    echo "TransactionID: ".$transaction->TransactionID . ", ItemID: " .$transaction->Item->ItemID. "<br>";
                }
            }
        }else{
            if(!$this->checkEbayTransactionExist($result->TransactionArray->Transaction->Item->ItemID, $result->TransactionArray->Transaction->TransactionID)){
                //Incomplete --> Complete
                if($result->TransactionArray->Transaction->Status->CompleteStatus == "Complete"){
                    if(empty($result->TransactionArray->Transaction->ContainingOrder)){
                        $this->createOrderFromEbayTransaction($result->Seller->UserID, $result->TransactionArray->Transaction);
                    }else{
                        $this->creteOrderFromEbayOrder($result->Seller->UserID, $result->TransactionArray->Transaction);
                    }
                }
            }
        }
    }
    
    public function getAllEbayTransaction(){    
		$sql = "select es.id,es.devId,es.appId,es.cert,es.token,ep.proxy_host,ep.proxy_port from qo_ebay_seller as es left join qo_ebay_proxy as ep on es.id=ep.ebay_seller_id";
		$result = mysql_query($sql, eBay::$database_connect);
		while ($row = mysql_fetch_assoc($result)){
			//authToken   devId  appId  cert  gatewaySOAP
			$this->createOrderFromEbay($this->startTime, $this->endTime, $row['id'], $row['devId'], $row['appId'], $row['cert'], $row['token'], $row['proxy_host'], $row['proxy_port']);
		}
    }
    
    private function errorLog($text){
        $sql = "insert into qo_error_log (text,timestamp) values ('".mysql_real_escape_string($text)."','".date("Y-m-d H:i:s")."')";
        echo "<br><font color='red'>".$sql."</font><br>";
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
    $test = new eBay();
    $test->setStartTime = "2009-03-16 00:00:00";
    $test->setEndTime = "2009-03-28 09:30:00";
    $test->getAllEbayTransaction();
    //$test->GetSellerTransactions('','','AgAAAA**AQAAAA**aAAAAA**FmQISA**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wHlIOnCZaBpAWdj6x9nY+seQ**Q2AAAA**AAMAAA**K5e4SqBc83jVbhFXLTi50I3ptbripiwsQUS3jIeIcDvpmXzELIv5yXhhUp8jB/r9sdMAKZP2GxQB8g85Eq09tNqTWhmSLMGmFRXq3gBeof7enC9Ch4L0JLBf4rSDdyGWkwa8zpnVueMbOwQbExhx1UjkGZXsDfIxO8vU1FaeGW2tLjVkOffg/0NkzrhwQnoR63SeZ/aPkns9sBbaqkGH7VsoYSik0C/pkO8V9gfxJIuIDjRNuOQ6Stx0UWRNnTPyZRZpWDShtsh9horFcmRsB34ZRxxAkaxx3UmskFoqwxNviz1vYrjEqZlbV2KkQsF+iCOT5lu2YdFTeTZ2uv3/PY1zw+J7sdvK3tI4ucKNKTNLbrBIco0XW/ImHhRoNsun4AizgcHP4HQOwzzuwXnc53Z1QqehYQZsOvMCx+cU+Z2zlA/MP6z7NgdCuHdaYRJbYgINxfDxAuxKCnjzyozpgV6Smk/o7dOBAaZKclEEClNAg3xIpjnyamBh4EBUzk0/tYePv5K2PA6nClMu58PWd7HcGcP/X4FCDnxiDbu5ndxcntPfec6ztdC5f2FHDJJ7ACY9PjRdIYWUQBsgwhV6yZs3t0N1SfR5yuy0tW+fOX4Uw4RkPcMbrgHk9H8m5JEae8YaQMfNkuk3TCKwjjEE+25LDFgpbiTAEu4sYs7FxGhBQBr4RbhoLR6TTdnu0xhpvO2vC4lPb6FQmb9vGRaTv3uxdh2xgMJgD7bhAqt+1vnET+xKvGDrvIFp1XxJ7ij2');
    //$test = new eBay();
    //$test->createOrderFromEbay();
}
?>