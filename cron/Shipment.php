<?php
define ('__DOCROOT__', '/export/eBayBO');
ini_set("memory_limit","256M");

class Shipment{
    private static $database_connect;
    private $startTime;
    private $endTime;
    private $complete_orders = array();
    private $config;
    
    public function __construct(){
        $this->config = parse_ini_file(__DOCROOT__ . '/config.ini', true);
        
        Shipment::$database_connect = mysql_connect($this->config['database']['host'], $this->config['database']['user'], $this->config['database']['password']);

        if (!Shipment::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(Shipment::$database_connect);
            exit;
        }
          
        mysql_query("SET NAMES 'UTF8'", Shipment::$database_connect);
        
        if (!mysql_select_db($this->config['database']['name'], Shipment::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(Shipment::$database_connect);
            exit;
        }
        //$this->startTime = date("Y-m-d 13:30:00",mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
        
    }
    
    public function setStartTime($startTime){
        $this->startTime = $startTime;
    }
    
    public function setEndTime($endTime){
        $this->endTime = $endTime;
    }
    
    public function general(){
        $this->startTime = date("Y-m-d 08:30:00",mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
        $this->endTime = date("Y-m-d 09:00:00");
        $this->createShipment();
    }
    
    public function morning(){
        $this->startTime = date("Y-m-d 17:00:00",mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
        $this->endTime   = date("Y-m-d 10:00:00");
        $this->createShipment();
    }
    
    public function afternoon(){
        $this->startTime = date("Y-m-d 10:00:00");
        $this->endTime   = date("Y-m-d 17:00:00");
        $this->createShipment();
    }
    
    public function temp(){
        global $argv;
        $this->startTime = $argv[2];
        $this->endTime   = $argv[3];
        $this->createShipment();
    }
    
    private function getTransactionRemarksByOrdersId($ordersId){
        $remarks = "";
        $sql = "select t.remarks from qo_transactions as t left join qo_orders_transactions as ot on t.id = ot.transactionsId where ot.ordersId = '".$ordersId."'";
        $result = mysql_query($sql, Shipment::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $remarks .= $row['remarks']."\n";
        }
        return substr($remarks, 0, -2);
    }
    
    private function getCompleteOrder(){
        $sql = "select id,shippingMethod,ebayName,ebayEmail,ebayAddress1,ebayAddress2,ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone from qo_orders 
        where status = 'P' and modifiedOn between '".$this->startTime."' and '".$this->endTime."'";
        $result = mysql_query($sql, Shipment::$database_connect);
        $i= 0;
        while($row = mysql_fetch_assoc($result)){
            $row['remarks'] = $this->getTransactionRemarksByOrdersId($row['id']);
            $this->complete_orders[$i] = $row;
            $sql_1 = "select skuId,skuTitle,itemId,itemTitle,quantity,barCode from qo_orders_detail where ordersId = '".$row['id']."'";
            $result_1 = mysql_query($sql_1, Shipment::$database_connect);
            $j = 0;
            while($row_1 = mysql_fetch_assoc($result_1)){
                $this->complete_orders[$i]['detail'][$j] = $row_1;
                $j++;
            }
            $i++;
        }
    }
    
    private function getShipmentId($type = 'SHA'){
        $today = date("Ym");
        $sql = "select curType,curId from sequence where curDate='$today' and type='$type'";
        $result = mysql_query($sql, Shipment::$database_connect);
        $row = mysql_fetch_assoc($result);
       
        if($row["curId"] >=9999){
            // A-Z 66-91
            $curType = chr(ord($row["curType"]) + 1);
            $sql = "update  sequence  set curId = 1,curType='$curType' where curDate='$today' and type='$type'";
            mysql_query($sql, Shipment::$database_connect);
        }elseif($row["curId"] < 1 || $row["curId"] == null) {
              $sql = "insert into sequence (type,curType,curDate,curId) value ('$type','A','$today',1)";
              mysql_query($sql, Shipment::$database_connect);
        }else {   
            $sql = "update sequence set curId = curId + 1 where curDate='$today' and type='$type'";
            $result = mysql_query($sql, Shipment::$database_connect);
        }
       
        $sql = "select curType,curId from sequence where curDate='$today' and type='$type'";
        $result = mysql_query($sql, Shipment::$database_connect);
        $row = mysql_fetch_assoc($result);
        $shipmentId = $type.$today.$row["curType"].str_repeat("0",(4-strlen($row["curId"]))).$row["curId"];   
        return $shipmentId;
    }
    
    private function log($content){
        file_put_contents($this->config['log']['shipments']."CreateShipment-".date("YmdH").".log", date("Y-m-d H:i:s")."   ".$content."\n", FILE_APPEND);
    }
    
    public function createShipment(){
        //echo $this->startTime;
        //echo "\n";
        //echo $this->endTime;
        //exit;
        $this->getCompleteOrder();
        //print_r($this->complete_orders);
        foreach($this->complete_orders as $orders){
            $sql_0 = "select count(*) as num from qo_shipments where ordersId = '".$orders['id']."'";
            $result_0 = mysql_query($sql_0, Shipment::$database_connect);
            $row_0 = mysql_fetch_assoc($result_0);
            if($row_0['num'] == 0){
                if($orders['shippingMethod'] == 'R'){
                    $shipmentId = $this->getShipmentId('SHR');
                }else{
                    $shipmentId = $this->getShipmentId();
                }
                //print_r($orders, true);
                //echo "<br>";
                $sql = "insert into qo_shipments (id,ordersId,status,shipmentMethod,remarks,shippingFeeCurrency,shippingFeeValue,shipToName,
                shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,
                shipToCountry,shipToPhoneNo,createdBy,createdOn,modifiedBy,modifiedOn) values ('".$shipmentId."','".$orders['id']."',
                'N','".$orders['shippingMethod']."','".$orders['remarks']."','".$orders['shippingFeeCurrency']."','".$orders['shippingFeeValue']."','".mysql_escape_string($orders['ebayName'])."',
                '".mysql_escape_string($orders['ebayEmail'])."','".mysql_escape_string($orders['ebayAddress1'])."','".mysql_escape_string($orders['ebayAddress2'])."','".mysql_escape_string($orders['ebayCity'])."',
                '".mysql_escape_string($orders['ebayStateOrProvince'])."','".mysql_escape_string($orders['ebayPostalCode'])."','".mysql_escape_string($orders['ebayCountry'])."','".$orders['ebayPhone']."',
                'System','".date("Y-m-d H:i:s")."','System','".date("Y-m-d H:i:s")."')";
                $this->log($sql);
                $result = mysql_query($sql, Shipment::$database_connect);
                if($result){
                    foreach($orders['detail'] as $datail){
                        $sql_1 = "insert into qo_shipments_detail (shipmentsId,skuId,skuTitle,itemId,itemTitle,quantity,barCode) values
                        ('".$shipmentId."','".$datail['skuId']."','".mysql_escape_string($datail['skuTitle'])."','".$datail['itemId']."','".mysql_escape_string($datail['itemTitle'])."',
                        '".$datail['quantity']."','".$datail['barCode']."')";
                        $this->log($sql_1);
                        $result_1 = mysql_query($sql_1, Shipment::$database_connect);
                    }
                }
            }
        }
    }
    
    private function getAllSellerToken(){
        $array = array();
        $sql = "select id,token from qo_ebay_seller";
        $result = mysql_query($sql, Shipment::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $array[$row['id']] = $row['token'];
        }
        return $array;
    }
    
    private function configEbay($token, $proxy_host = '', $proxy_port = ''){
    	// Load developer-specific configuration data from ini file
	$config = parse_ini_file(__DOCROOT__ . '/ebay.ini', true);
	$site = $config['settings']['site'];
	//$compatibilityLevel = $config['settings']['compatibilityLevel'];
	
	$dev = $config[$site]['devId'];
	$app = $config[$site]['appId'];
	$cert = $config[$site]['cert'];
	$token = $token;
	$location = $config[$site]['gatewaySOAP'];
	//$location = self::GATEWAY_SOAP;
	
	// Create and configure session
	$session = new eBaySession($dev, $app, $cert, $proxy_host, $proxy_port);
	$session->token = $token;
	$session->site = 0; // 0 = US;
	$session->location = $location;
	
	return $session;
    }
    
    private function updateEbayShipStatus($transactionId, $itemId){
        $sql = "update qo_orders_detail set ebayShipStatus = 1 where itemId = '".$itemId."' and ebayTranctionId = '".$transactionId."'";
        echo $sql."\n";
        $result = mysql_query($sql, Shipment::$database_connect);
        return $result;
    }
    
    private function CompleteSale($token, $transactionId, $itemId, $shipmentMethod, $shippedOn, $postalReferenceNo){
	$session = $this->configEbay($token);
	try {
		$client = new eBaySOAP($session);
                /*
		if(!empty($postalReferenceNo)){
			switch ($shipmentMethod){
				case "M":
					$ShippingCarrierUsed = "UPS";
					break;
					
				case "U":
					$ShippingCarrierUsed = "UPS";
					break;
					
				default:
					$ShippingCarrierUsed = "Other";
				break;
			}
			$Shipment = array("ShipmentTrackingNumber"=>$postalReferenceNo, "ShippedTime"=>$shippedOn, "ShippingCarrierUsed"=>$ShippingCarrierUsed);
			$params = array("Version"=>"607", "ItemID"=>$itemId, "Paid"=> true, "Shipment"=>$Shipment, "Shipped"=>true, "TransactionID"=>$transactionId);
			print_r($params);
			$results = $client->CompleteSale($params);
		}else{
			$params = array("Version"=>"607", "ItemID"=>$itemId, "Paid"=> true, "Shipped"=>true, "TransactionID"=>$transactionId);
			print_r($params);
			$results = $client->CompleteSale($params);
		}
		*/
                $params = array("Version"=>"607", "ItemID"=>$itemId, "Paid"=> true, "Shipped"=>true, "TransactionID"=>$transactionId);
                print_r($params);
                $results = $client->CompleteSale($params);
                        
		//print_r($results);
		if(!empty($results->Ack) && $results->Ack == "Success"){
			$this->updateEbayShipStatus($transactionId, $itemId);
		}else{
			if(!empty($results->Errors)){
				print_r($results->Errors);
			}else{
				echo $results->faultstring;
			}
			$this->updateEbayShipStatus($transactionId, $itemId);
		}
		//exit;
		sleep(1);
	} catch (SOAPFault $f) {
		print $f; // error handling
	}
    }
    
    public function synceBayShipped(){
        require_once 'eBaySOAP.php';
        $sellerToken = $this->getAllSellerToken();
        // ALTER TABLE `qo_shipments` ADD INDEX ( `shippedOn` )  
        $shippedOn = date("Y-m-d");
        //$shippedOn = "2010-06-28";
        $sql = "select ordersId,shipmentMethod,shippedOn,postalReferenceNo from qo_shipments where shippedOn like '".$shippedOn."%'";
        echo $sql."\n";
        $result = mysql_query($sql, Shipment::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $sql_1 = "select o.sellerId,od.itemId,od.ebayTranctionId from qo_orders as o left join qo_orders_detail as od on o.id = od.ordersId where o.id = '".$row['ordersId']."' and od.ebayShipStatus = 0";
            echo $sql_1."\n";
            $result_1 = mysql_query($sql_1, Shipment::$database_connect);
            while($row_1 = mysql_fetch_assoc($result_1)){
                $this->CompleteSale($sellerToken[$row_1['sellerId']], $row_1['ebayTranctionId'], $row_1['itemId'], $row['shipmentMethod'], $row['shippedOn'], $row['postalReferenceNo']);
            }
            //exit;
        }
    }
    
    
    public function __destruct(){
        mysql_close(Shipment::$database_connect);
    }
}

$action = $argv[1];
$shipment = new Shipment();
$shipment->$action();
//http://heshuai64.3322.org/eBayBO/cron/Shipment.php?start=2009-04-22%2000:00:00&end=2009-04-23%2000:00:00
?>