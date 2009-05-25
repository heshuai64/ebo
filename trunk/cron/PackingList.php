<?php

class PackingList{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaybo';
    const FILE_PATH = '/export/eBayBO/packing/';
    const BAR_CODE_URL = '/eBayBO/cron/image.php';
    private $sellerSell = array();
    private $sellSku = array();
    private $startTime;
    private $endTime;
    private $shipment = array();
    
    public function __construct(){
        PackingList::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

        if (!PackingList::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(PackingList::$database_connect);
            exit;
        }
        
        mysql_query("SET NAMES 'UTF8'", PackingList::$database_connect);
          
        if (!mysql_select_db(self::DATABASE_NAME, PackingList::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(PackingList::$database_connect);
            exit;
        }
        $this->startTime = date("Y-m-d 14:10:00",mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
        $this->endTime = date("Y-m-d 14:10:00");
    }
    
    public function setStartTime($startTime){
        $this->startTime = $startTime;
    }
    
    public function setEndTime($endTime){
        $this->endTime = $endTime;
    }
    
    private function getSellerSell(){
        $sql = "select o.sellerId,sum(sd.quantity) as num from qo_orders as o 
        left join qo_shipments as s on o.id = s.ordersId left join qo_shipments_detail as sd on s.id = sd.shipmentsId 
        where s.modifiedOn between '$this->startTime' and '$this->endTime' and s.status = 'N' group by o.sellerId";
        $result = mysql_query($sql, PackingList::$database_connect);
        $i= 0;
        while($row = mysql_fetch_assoc($result)){
            $this->sellerSell[$i] = $row;
            $i++;
        }
        
    }
    
    private function getSellSku(){
        $sql = "select sd.skuId,sd.quantity from qo_shipments as s left join qo_shipments_detail as sd 
        on s.id=sd.shipmentsId where s.modifiedOn between '$this->startTime' and '$this->endTime' and s.status = 'N'";
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
            $sql_1 = "select i.skuId,sd.itemId,sd.quantity,i.galleryURL from qo_shipments_detail as sd left join qo_items as i on sd.itemId=i.id where sd.shipmentsId='".$row['id']."'";
            $result_1 = mysql_query($sql_1, PackingList::$database_connect);
            $j = 0;
            while($row_1 = mysql_fetch_assoc($result_1)){
                $this->shipment[$i]['shipmentDetail'][$j]['skuId'] = $row_1['skuId'];
                $this->shipment[$i]['shipmentDetail'][$j]['quantity'] = $row_1['quantity'];
                $this->shipment[$i]['shipmentDetail'][$j]['image'] = $row_1['galleryURL'];
                $j++;
            }
            $i++;
        }
    }
    
    private function getAllSellerId(){
        $sql = "select id from qo_ebay_seller where status = 'A'";
        $result = mysql_query($sql, PackingList::$database_connect);
        $array = array();
        while($row = mysql_fetch_assoc($result)){
            $array[] = $row['id'];
        }
        return $array;
    }
    
    private function getShipmentBySellerId($sellerId){
        unset($this->shipment);
        $sql = "select s.id,o.shippingMethod,s.shipToName,s.shipToAddressLine1,s.shipToAddressLine2,s.shipToCity,s.shipToStateOrProvince,s.shipToPostalCode,s.shipToCountry 
        from qo_shipments as s left join qo_orders as o on s.ordersId=o.id where o.sellerId='".$sellerId."' and s.modifiedOn between '$this->startTime' and '$this->endTime' and s.status = 'N'";
        $result = mysql_query($sql, PackingList::$database_connect);
        //echo $sql;
        
        $i = 0;
        while($row = mysql_fetch_assoc($result)){
            /*
            if(in_array($row['shippingMethod'], array('B', 'R', 'S'))){
                switch($row['shippingMethod']){
                    case "B":
                        $row['shippingMethod'] = "Bulk";
                    break;
                
                    case "R":
                        $row['shippingMethod'] = "Registered";
                    break;
                
                    case "S":
                        $row['shippingMethod'] = "SpeedPost";
                    break;
                }
            }
            */
            
            $this->shipment[$i] = $row;
            $sql_1 = "select sd.skuId,sd.itemId,sd.quantity,i.galleryURL from qo_shipments_detail as sd left join qo_items as i on sd.itemId=i.id where sd.shipmentsId='".$row['id']."'";
            $result_1 = mysql_query($sql_1, PackingList::$database_connect);
            $j = 0;
            while($row_1 = mysql_fetch_assoc($result_1)){
                $this->shipment[$i]['shipmentDetail'][$j]['skuId'] = $row_1['skuId'];
                $this->shipment[$i]['shipmentDetail'][$j]['quantity'] = $row_1['quantity'];
                $this->shipment[$i]['shipmentDetail'][$j]['image'] = $row_1['galleryURL'];
                $j++;
            }
            $i++;
        }
    }
    
    private function generateFile($fileName, $content){
        if(!file_exists(self::FILE_PATH.date("Ym"))){
            mkdir(self::FILE_PATH.date("Ym"), 0777);
        }
        
        if(!file_exists(self::FILE_PATH.date("Ym")."/".date("d"))){
            mkdir(self::FILE_PATH.date("Ym")."/".date("d"), 0777);
        }
        
        $fileName = self::FILE_PATH.date("Ym")."/".date("d").'/'.$fileName.'.html';
        
        if (!$handle = fopen($fileName, 'w')) {
            echo "not open file $fileName";
            exit;
        }

        if (fwrite($handle, $content) === FALSE) {
            echo "not write into $fileName";
            exit;
        }
                            
        fclose($handle);
    }
    
    public function getPackingList(){
        $this->getSellerSell();
        $this->getSellSku();
        /*
        $this->getShipment();
        //print_r($this->sellerSell);
        //print_r($this->sellSku);
        //print_r($this->shipment);
        ob_start();
        require("template.php");
        $content = ob_get_contents();
	ob_end_clean();
        $this->generateFile('pickinglist', $content);
        */
        $sellerIdArray = $this->getAllSellerId();
        foreach($sellerIdArray as $sellerId){
            $this->getShipmentBySellerId($sellerId);
            //print_r($this->shipment);
            ob_start();
            require("template.php");
            $content = ob_get_contents();
            ob_end_clean();
            $this->generateFile($sellerId, $content);
        }
    }
    
    public function __destruct(){
        mysql_close(PackingList::$database_connect);
    }


}
$packing_list = new PackingList();
if(!empty($_GET)){
    $packing_list->setStartTime($_GET['start']);
    $packing_list->setEndTime($_GET['end']);
}
$packing_list->getPackingList();

//http://heshuai64.3322.org/eBayBO/cron/PackingList.php?start=2009-04-22%2000:00:00&end=2009-04-23%2000:00:00
?>