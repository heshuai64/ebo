<?php
class Reports{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaybo';
    const INVENTORY_SERVICE = 'http://192.168.1.169:8080/inventory/service.php';
    
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
    
    private function log($file_name, $data){
        file_put_contents("/export/eBayBO/log/".$file_name."-".date("Y-m-d").".html", $data, FILE_APPEND);  
    }
    
    private function get($request){
        
	$session = curl_init($request);
		
	curl_setopt($session, CURLOPT_HEADER, true);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($session);
	
	curl_close($session);
	
	$status_code = array();
	preg_match('/\d\d\d/', $response, $status_code);
	
	switch( $status_code[0] ) {
		case 200:
		    if ($result = strstr($response, 'model:')) {
			return $result;
		    }
		    break;
		case 503:
			die('Your call to Web Services failed and returned an HTTP status of 503. That means: Service unavailable. An internal problem prevented us from returning data to you.');
			break;
		case 403:
			die('Your call to Web Services failed and returned an HTTP status of 403. That means: Forbidden. You do not have permission to access this resource, or are over your rate limit.');
			break;
		case 400:
			die('Your call to Web Services failed and returned an HTTP status of 400. That means:  Bad request. The parameters passed to the service did not match as expected. The exact error is returned in the XML response.');
			break;
		default:
			die('Your call to Web Services returned an unexpected HTTP status of:' . $status_code[0]);
			return false;
	}

    }
    
    public function skuSellReport($seller_id, $start_date, $end_date){
        require ("class/class-excel-xml.inc.php");
        if(empty($seller_id)){
            $sql = "select o.sellerId,od.skuId,sum(od.quantity) as quantity from qo_orders as o left join qo_orders_detail as od on o.id=od.ordersId where o.status = 'P' and o.createdOn between '$start_date 14:10:00' and '$end_date 14:10:00' group by od.itemId";
        }else{
            $sql = "select o.sellerId,od.skuId,sum(od.quantity) as quantity from qo_orders as o left join qo_orders_detail as od on o.id=od.ordersId where o.status = 'P' and o.createdOn between '$start_date 14:10:00' and '$end_date 14:10:00' and o.sellerId='$seller_id' group by od.itemId";
        }
        //echo $sql;
        $result = mysql_query($sql, Reports::$database_connect);
        $data = array();
        $data[0] = array('Seller', 'SKU', 'MODEL', 'Quantity');
        $i = 1;
        while($row = mysql_fetch_assoc($result)){
            $service_result = $this->get(self::INVENTORY_SERVICE.'?action=getModelBySkuId&skuId='.urlencode($row['skuId']));
            $this->log('getModelBySkuId.html','skuId:'.$row['skuId'].', return:'.$service_result);
            $data[$i]['sellerId'] = $row['sellerId'];
            $data[$i]['skuId'] = $row['skuId'];
            $data[$i]['model'] = substr($service_result, 6);
            $data[$i]['quantity'] = $row['quantity'];
            $i++;
        }
        //var_dump($data);
        //exit;
        $xls = new Excel_XML;
        $xls->setWorksheetTitle("SKU Sell");
        $xls->addArray ( $data );
        $xls->generateXML ("SKU Sell(".$start_date." -- ".$end_date.")");
    }
    
    public function salesReport(){
        //$fourWeekAgo = date("Y-m-d", strtotime("last Monday", strtotime(date("Y-m-d", strtotime("-4 week")))));
        $fourWeekAgo = date("Y-m-d", strtotime(date("Y-m-d", strtotime("-4 week"))));
        //echo $fourWeekAgo;
        //echo "<br>";
        $timestamp = strtotime($fourWeekAgo);
	
        $day_data = array();
        for($i=0; $i<28; $i++){
            $date = date("Y-m-d", $timestamp + ($i * 60 * 60 * 24));
            //echo $date;
            //echo "<br>";
            $sql = "select o.id,od.skuId,sum(od.quantity) as quantity,o.createdOn from qo_orders as o left join qo_orders_detail as od on o.id = od.ordersId where o.createdOn like '".$date."%' group by skuId";
            //echo $sql;
            //echo "<br>";
            $result = mysql_query($sql, Reports::$database_connect);
            $j = 0;
            while($row = mysql_fetch_assoc($result)){
                //print_r($row);
                $day_data[$date][$j] = $row;
                $j++;
            }
            flush();
        }
        var_dump($day_data);
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

    case "salesReport":
        $t->salesReport();
    break;
}
?>