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
        
        $sql = "select s.id,o.ebayName,o.buyerId,s.shipmentMethod,s.postalReferenceNo,s.shipToCountry,s.shipToPostalCode,s.shipToAddressLine1,s.shipToAddressLine2 
        from qo_shipments as s left join qo_orders as o on s.ordersId = o.id ".$where;
        
        $result = mysql_query($sql, Lists::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $this->content .=
            '<tr>
                <td>Registered</td>
                <td>'.$row['postalReferenceNo'].'</td>
                <td>'.$row['shipToCountry'].'</td>
                <td>'.$row['ebayName'].'</td>
                <td>'.$row['buyerId'].'</td>
                <td>'.$row['shipToPostalCode'].'</td>
                <td>'.$row['shipToAddressLine1'].'<br>'.$row['shipToAddressLine2'].'</td>
                <td>'.$row['id'].'</td>
            </tr>'; 
        }
    }
    
    public function __destruct(){
        echo $this->content;
        mysql_close(Lists::$database_connect);
    }
}

$Lists = new Lists();
$Lists->$_GET['type']();
?>