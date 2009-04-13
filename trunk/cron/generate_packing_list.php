<?php

class PackingList{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '';
    const DATABASE_NAME = 'ebaybo';
    private $sellerSell = array();
    private $sellSku = array();
    private $startTime;
    private $endTime;
    private $shipment = array();
    
    public function __construct(){
        PackingList::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

        if (!PayPal::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(PackingList::$database_connect);
            exit;
        }
          
        if (!mysql_select_db(self::DATABASE_NAME, PayPal::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(PackingList::$database_connect);
            exit;
        }
        $this->startTime = date("Y-m-d 10:00:00",mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
        $this->endTime = date("Y-m-d 10:00:00");
    }
    
    public function setStartTime($startTime){
        $this->startTime = $startTime;
    }
    
    public function setEndTime($endTime){
        $this->endTime = $endTime;
    }
    
    private function getSellerSell(){
        $sql = "select sellerId,count(*) as num from qo_orders as o left join qo_shipments as s on o.id = s.ordersId
        where s.modifiedOn between '$this->startTime' and '$this->endTime' and s.status = 'N' group by o.sellerId";
        $result = mysql_query($sql, PackingList::$database_connect);
        $i= 0;
        while($row = mysql_fetch_assoc($result)){
            $this->sellerSell[$i] = $row;
            $i++;
        }
        
    }
    
    private function getSellSku(){
        $sql = "select sd.skuId,sd.quantity from qo_shipments as s left join s.id=sd.shipmentsId
        where s.modifiedOn between '$this->startTime' and '$this->endTime' and s.status = 'N'";
        $result = mysql_query($sql, PackingList::$database_connect);
        $i= 0;
        $temp_sku = array();
        $temp_num = array();
        while($row = mysql_fetch_assoc($result)){
            $temp_sku[$i] = $row['skuId'];
            $temp_num[$i] = $row['quantity'];
            $i++;
        }
        
        foreach($temp_sku as $key=>$value){
            if(array_key_exists($value, $this->sellSku)){
                $this->sellSku[$value] += $temp_num[$key];
            }else{
                 $this->sellSku[$value] = $temp_num[$key];
            }
        }
        
    }
    
    private function getShipment(){
        $sql = "select id,shipToName,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,shipToCountry
        from qo_shipments where modifiedOn between '$this->startTime' and '$this->endTime' and status = 'N'";
        $result = mysql_query($sql, PackingList::$database_connect);
        
        $i = 0;
        while($row = mysql_fetch_assoc($result)){
            $this->shipment[$i] = $row;
            $sql_1 = "select skuId,quantity from qo_shipments_detail where shipmentsId='".$row['id']."'";
            $result_1 = mysql_query($sql_1, PackingList::$database_connect);
            $j = 0;
            while($row_1 = mysql_fetch_assoc($result_1)){
                $this->shipment[$i]['shipmentDetail'][$j]['skuId'] = $row_1['skuId'];
                $this->shipment[$i]['shipmentDetail'][$j]['quantity'] = $row_1['quantity'];
                $j++;
            }
            $i++;
        }
    }

    public function generateFile(){
        $this->getSellerSell();
        $this->getSellSku();
        $this->getShipment();
        foreach($this->shipment as $shipment){
            
        }
    }
    
    public function __destruct(){
        mysql_close(PackingList::$database_connect);
    }


}
?>