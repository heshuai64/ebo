<?php
class Shipment{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaybo';
    private $startTime;
    private $endTime;
    private $complete_orders = array();
    
    public function __construct(){
        Shipment::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

        if (!Shipment::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(Shipment::$database_connect);
            exit;
        }
          
        mysql_query("SET NAMES 'UTF8'", Shipment::$database_connect);
        
        if (!mysql_select_db(self::DATABASE_NAME, Shipment::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(Shipment::$database_connect);
            exit;
        }
        $this->startTime = date("Y-m-d 13:30:00",mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
        $this->endTime = date("Y-m-d 14:00:00");
    }
    
    public function setStartTime($startTime){
        $this->startTime = $startTime;
    }
    
    public function setEndTime($endTime){
        $this->endTime = $endTime;
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
    
    private function getShipmentId(){
        $type = 'SHA';
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
        file_put_contents("/export/eBayBO/log/Shipment/CreateShipment-".date("Y-m-d").".log", date("Y-m-d H:i:s")."   ".$content."\n", FILE_APPEND);
    }
    
    public function createShipment(){
        $this->getCompleteOrder();
        //print_r($this->complete_orders);
        foreach($this->complete_orders as $orders){
            $sql_0 = "select count(*) as num from qo_shipments where ordersId = '".$orders['id']."'";
            $result_0 = mysql_query($sql_0, Shipment::$database_connect);
            $row_0 = mysql_fetch_assoc($result_0);
            if($row_0['num'] == 0){
                $shipmentId = $this->getShipmentId();
                print_r($orders, true);
                echo "<br>";
                $sql = "insert into qo_shipments (id,ordersId,status,shippingFeeCurrency,shippingFeeValue,shipToName,
                shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,
                shipToCountry,shipToPhoneNo,createdBy,createdOn,modifiedBy,modifiedOn) values ('".$shipmentId."','".$orders['id']."',
                'N','".$orders['shippingFeeCurrency']."','".$orders['shippingFeeValue']."','".$orders['ebayName']."',
                '".$orders['ebayEmail']."','".$orders['ebayAddress1']."','".$orders['ebayAddress2']."','".$orders['ebayCity']."',
                '".$orders['ebayStateOrProvince']."','".$orders['ebayPostalCode']."','".$orders['ebayCountry']."','".$orders['ebayPhone']."',
                'System','".date("Y-m-d H:i:s")."','System','".date("Y-m-d H:i:s")."')";
                $this->log($sql);
                $result = mysql_query($sql, Shipment::$database_connect);
                if($result){
                    foreach($orders['detail'] as $datail){
                        $sql_1 = "insert into qo_shipments_detail (shipmentsId,skuId,skuTitle,itemId,itemTitle,quantity,barCode) values
                        ('".$shipmentId."','".$datail['skuId']."','".$datail['skuTitle']."','".$datail['itemId']."','".$datail['itemTitle']."',
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

$shipment = new Shipment();
if(!empty($_GET['start']) && !empty($_GET['end'])){
    $shipment->setStartTime($_GET['start']);
    $shipment->setEndTime($_GET['end']);
}
$shipment->createShipment();
//http://heshuai64.3322.org/eBayBO/cron/Shipment.php?start=2009-04-22%2000:00:00&end=2009-04-23%2000:00:00
?>