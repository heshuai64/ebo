<?php

class Lists {
    private static $database_connect;
    
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaybo';
    private $content;
    
    public function __construct(){
        Lists::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

        if (!Lists::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(Lists::$database_connect);
            exit;
        }
          
        mysql_query("SET NAMES 'UTF8'", Lists::$database_connect);
        
        if (!mysql_select_db(self::DATABASE_NAME, Lists::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(Lists::$database_connect);
            exit;
        }
        
    }
    
    public function shipmentRegistered(){
        $this->content = '<table border="1">
            <tr>
            <th>Shipment Method</th>
            <th>Tracking Number</th>
            <th>Country</th>
            <th>Buyer Name</th>
            <th>Buyer eBay ID</th>
            <th>Zip Code</th>
            <th>Address</th>
            <th>Shipment ID</th>
            </tr>
        ';
        
        $where = " where shipmentMethod = 'R' ";
        
        if(!empty($_GET['shippedOnFrom'])){
            $where .= " and s.shippedOn > '".date('Y-m-d', strtotime(substr($_GET['shippedOnFrom'], 0, -18)))."'";
        }
        
        if(!empty($_GET['shippedOnTo'])){
            $where .= " and s.shippedOn < '".date('Y-m-d', strtotime(substr($_GET['shippedOnTo'], 0, -18)))."'";
        }
        
        $sql = "select s.id,o.ebayName,o.buyerId,s.shipmentMethod,s.postalReferenceNo,s.shipToAddressLine1,s.shipToAddressLine2,s.shipToCity,s.shipToStateOrProvince,s.shipToPostalCode,s.shipToCountry,s.shipToPhoneNo  
        from qo_shipments as s left join qo_orders as o on s.ordersId = o.id ".$where;
        
        $result = mysql_query($sql, Lists::$database_connect);
        $i = 1;
        while($row = mysql_fetch_assoc($result)){
            $this->content .=
            '<tr>
                <td>'.$i.'</td>
                <td>Registered</td>
                <td>'.$row['postalReferenceNo'].'</td>
                <td>'.$row['shipToCountry'].'</td>
                <td>'.$row['ebayName'].'</td>
                <td>'.$row['buyerId'].'</td>
                <td>'.$row['shipToPostalCode'].'</td>
                <td>'.$row['shipToAddressLine1'].'<br>'.(!empty($row['shipToAddressLine2'])?$row['shipToAddressLine2'].'<br>':'').$row['shipToCity'].'<br>'.$row['shipToStateOrProvince'].'</td>
                <td>'.$row['id'].'</td>
            </tr>';
            $i++;
        }
    }
    
    public function everydaySkuList(){
        $sql = "select s.ordersId,s.id,s.shipmentMethod,sd.skuId,sd.quantity from qo_shipments as s left join qo_shipments_detail as sd on s.id = sd.shipmentsId where s.shippedOn like '".date("Y-m-d")."'%";
        $result = mysql_query($sql, Lists::$database_connect);
        $data = "No,SHIPMENT ID,EBAY ID,SHIPPING METHODS,SKU,ITEM MODEL,QUANTITY\n";
        $i = 1;
        while($row = mysql_fetch_assoc($result)){
            $sql_1 = "select buyerId from qo_orders where id = '".$row['ordersId']."'";
            $result_1 = mysql_query($sql_1, Lists::$database_connect);
            $row_1 = mysql_fetch_assoc($result_1);
            $data .= $i.",".$row['id'].",".$row_1['buyerId'].",".$row['shipmentMethod'].",".$row['skuId'].",,".$row['quantity']."\n";
            $i++;
        }
        file_put_contents("/export/eBayBO/packing/".date("Ym")."/".date("d")."/skuList.csv", $data);
    }
    
    public function __destruct(){
        echo $this->content;
        mysql_close(Lists::$database_connect);
    }
}

$Lists = new Lists();
$action = (!empty($_GET['type'])?$_GET['type']:$argv[1]);
$Lists->$action();
?>