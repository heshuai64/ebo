<?php

class PackingList{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '';
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
          
        if (!mysql_select_db(self::DATABASE_NAME, PackingList::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(PackingList::$database_connect);
            exit;
        }
        //$this->startTime = date("Y-m-d 10:00:00",mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
        //$this->endTime = date("Y-m-d 10:00:00");
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
            $sql_1 = "select sd.skuId,sd.quantity,i.galleryURL from qo_shipments_detail as sd left join qo_items as i on sd.itemId=i.id where sd.shipmentsId='".$row['id']."'";
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
        if(!file_exists(self::FILE_PATH.date("Ymd"))){
            mkdir(self::FILE_PATH.date("Ymd"), 0777);
        }
        
        $fileName = self::FILE_PATH.date("Ymd").'/packingList.html';
        
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
        $this->getShipment();
        //print_r($this->sellerSell);
        //print_r($this->sellSku);
        //print_r($this->shipment);
        ob_start();
        require("template.php");
        $content = ob_get_contents();
	ob_end_clean();
        $this->generateFile('pickinglist',$content);
    }
    
    public function __destruct(){
        mysql_close(PackingList::$database_connect);
    }


}

if(!empty($_GET)){
    $packing_list = new PackingList();
    $packing_list->setStartTime($_GET['start']);
    $packing_list->setEndTime($_GET['end']);
    $packing_list->getPackingList();
}else{
    $packing_list = new PackingList();
    $packing_list->setStartTime(date("Y-m-d H:i:s", time() - ((4 * 60 * 60) + (20 * 60))));
    $packing_list->setEndTime(date("Y-m-d H:i:s"));
    $packing_list->getPackingList();
}


?>