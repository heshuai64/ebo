<?php
class Reports{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '';
    const DATABASE_NAME = 'ebaybo';
    
    public function __construct(){
        Reports::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

        if (!Reports::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(Reports::$database_connect);
            exit;
        }
          
        if (!mysql_select_db(self::DATABASE_NAME, Reports::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(Reports::$database_connect);
            exit;
        }
        
    }
    
    public function skuSellReport($seller_id, $start_date, $end_date){
        require ("class/class-excel-xml.inc.php");
        if(empty($seller_id)){
            $sql = "select o.sellerId,od.skuId,sum(od.quantity) as quantity from qo_orders as o left join qo_orders_detail as od on o.id=od.ordersId where o.status = 'P' and o.createdOn between '$start_date' and '$end_date' group by od.itemId";
        }else{
            $sql = "select o.sellerId,od.skuId,sum(od.quantity) as quantity from qo_orders as o left join qo_orders_detail as od on o.id=od.ordersId where o.status = 'P' and o.createdOn between '$start_date' and '$end_date' and o.sellerId='$seller_id' group by od.itemId";
        }
        //echo $sql;
        $result = mysql_query($sql, Reports::$database_connect);
        $data = array();
        $data[0] = array('Seller', 'SKU', 'Quantity');
        $i = 1;
        while($row = mysql_fetch_assoc($result)){
            $data[$i] = $row;
            $i++;
        }
        //var_dump($data);
        //exit;
        $xls = new Excel_XML;
        $xls->setWorksheetTitle("SKU Sell");
        $xls->addArray ( $data );
        $xls->generateXML ("SKU Sell(".$start_date." -- ".$end_date.")");
    }
    
    public function __destruct(){
        mysql_close(Reports::$database_connect);
    }

}
    

$t = new Reports();
switch($_GET['type']){
    case "skuSell":
        @$t->skuSellReport($_GET['seller_id'], $_GET['start_date'], $_GET['end_date']);
    break;
}
?>