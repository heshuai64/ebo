<?php

class ExportCsv{
    private static $database_connect;
    
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaybo';
    private $file_name;
    private $data = '';
    
    public function __construct(){
        ExportCsv::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

        if (!ExportCsv::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(ExportCsv::$database_connect);
            exit;
        }
          
        mysql_query("SET NAMES 'UTF8'", ExportCsv::$database_connect);
        
        if (!mysql_select_db(self::DATABASE_NAME, ExportCsv::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(ExportCsv::$database_connect);
            exit;
        }
        
    }
    
    public function shipment(){
        //print_r($_GET);
        $this->data .= 'Shipment Method,Postal Reference Number,Country,eBay Buyer Name, eBay Buyer Id,Zip,Address,Shipment Id'."\n";
        $this->file_name = 'shipment';
        
        $where = " where shipmentMethod = 'R' and 1 = 1 ";
        
        if(!empty($_GET['createdOnFrom'])){
            $where .= " and s.createdOn > '".date('Y-m-d', strtotime(substr($_GET['createdOnFrom'], 0, -18)))."'";
        }
        
        if(!empty($_GET['createdOnTo'])){
            $where .= " and s.createdOn < '".date('Y-m-d', strtotime(substr($_GET['createdOnTo'], 0, -18)))."'";
        }
            
        $sql = "select s.id,o.ebayName,o.buyerId,s.shipmentMethod,s.shipToCountry,s.shipToPostalCode,s.shipToAddressLine1,s.shipToAddressLine2 
        from qo_shipments as s left join qo_orders as o on s.ordersId = o.id ".$where;

        $result = mysql_query($sql, ExportCsv::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            switch($row['shipmentMethod']){
                case "B":
                    $row['shipmentMethod'] = 'Bulk';
                break;
            
                case "S":
                    $row['shipmentMethod'] = 'SpeedPost';
                break;
            
                case "R":
                    $row['shipmentMethod'] = 'Registered';
                break;
            
                case "U":
                    $row['shipmentMethod'] = 'UPS';
                break;
            }
            $this->data .= '"'.$row['shipmentMethod'].'","'.$row['postalReferenceNo'].'","'.$row['shipToCountry'].'","'.$row['ebayName'].'","'.$row['buyerId'].'","'.$row['shipToPostalCode'].'","'.$row['shipToAddressLine1']."\n".$row['shipToAddressLine2'].'","'.$row['id'].'"'."\n";
        }
    }
    
    public function __destruct(){
        //echo $this->data;
        //exit;
        mysql_close(ExportCsv::$database_connect);
        header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=".$this->file_name.".csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $this->data;
        
    }
}


$export_csv = new ExportCsv();
$export_csv->$_GET['type']();

?>