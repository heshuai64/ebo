<?php
class Shipment{
    private static $database_connect;
    private $startTime;
    private $endTime;
    private $complete_orders = array();
    private $config;
    
    public function __construct(){
        $this->config = parse_ini_file('config.ini', true);
        
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
    
    private function getCompleteOrder(){
        $sql = "select id,shippingMethod,ebayName,ebayEmail,ebayAddress1,ebayAddress2,ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone from qo_orders 
        where status = 'P' and modifiedOn between '".$this->startTime."' and '".$this->endTime."'";
        $result = mysql_query($sql, Shipment::$database_connect);
        $i= 0;
        while($row = mysql_fetch_assoc($result)){
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
                $sql = "insert into qo_shipments (id,ordersId,status,shipmentMethod,shippingFeeCurrency,shippingFeeValue,shipToName,
                shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,
                shipToCountry,shipToPhoneNo,createdBy,createdOn,modifiedBy,modifiedOn) values ('".$shipmentId."','".$orders['id']."',
                'N','".$orders['shippingMethod']."','".$orders['shippingFeeCurrency']."','".$orders['shippingFeeValue']."','".mysql_escape_string($orders['ebayName'])."',
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
    
    public function __destruct(){
        mysql_close(Shipment::$database_connect);
    }
}

$action = $argv[1];
$shipment = new Shipment();
$shipment->$action();
//http://heshuai64.3322.org/eBayBO/cron/Shipment.php?start=2009-04-22%2000:00:00&end=2009-04-23%2000:00:00
?>