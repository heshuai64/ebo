<?php
class Reports{
    private static $database_connect;
    private static $memcache_connect;
    
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaybo';
    const INVENTORY_SERVICE = 'http://192.168.1.169:8080/inventory/service.php';
    const MEMCACHE_HOST = '127.0.0.1';
    const MEMCACHE_PORT = 11211;
    
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
        
        Reports::$memcache_connect = new Memcache;
        Reports::$memcache_connect->connect(self::MEMCACHE_HOST, self::MEMCACHE_PORT);
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
        //$fourWeekAgo = date("Y-m-d", strtotime("-4 week"));
        $today = date("D");
        if($today == "Sun"){
            $nextSun = date("Y-m-d");
        }else{
            $nextSun = date("Y-m-d", strtotime("next Sunday"));
        }
        
        $eightWeekAgoMon = date("Y-m-d 23:59:59", strtotime("-8 week", strtotime($nextSun)));
        $fiveWeekAgoMon = date("Y-m-d 23:59:59", strtotime("-5 week", strtotime($nextSun)));
        $fourWeekAgoMon = date("Y-m-d 23:59:59", strtotime("-4 week", strtotime($nextSun)));
        $threeWeekAgoMon = date("Y-m-d 23:59:59", strtotime("-3 week", strtotime($nextSun)));
        $twoWeekAgoMon = date("Y-m-d 23:59:59", strtotime("-2 week", strtotime($nextSun)));
        $oneWeekAgoMon = date("Y-m-d 23:59:59", strtotime("-1 week", strtotime($nextSun)));
        
        $lastWeekToday = date("Y-m-d", strtotime("-1 week", strtotime(date("Y-m-d"))));
        $today = date("Y-m-d");
        /*
        echo $nextSun;
        echo "<br>";
        echo $eightWeekAgoMon;
        echo "<br>";
        echo $fiveWeekAgoMon;
        echo "<br>";
        echo $fourWeekAgoMon;
        echo "<br>";
        echo $threeWeekAgoMon;
        echo "<br>";
        echo $twoWeekAgoMon;
        echo "<br>";
        echo $oneWeekAgoMon;
        echo "<br>";
        exit;
        */
        
        $timestamp = strtotime($fiveWeekAgoMon);
        //$data_array = Reports::$memcache_connect->get($timestamp);
        $data_array = false;
        if($data_array == false){
            $day_data = array();
            $sku_array = array();
            
            //********************************************* Today  ***********************************************
            if(!empty($_GET['sellerId'])){
                $sql_1 = "select o.id,od.skuId,od.itemTitle,od.quantity,DATE_FORMAT(o.createdOn, '%Y-%m-%d') as date1,DATE_FORMAT(o.createdOn, '%a') as date2 from qo_orders as o left join qo_orders_detail as od on o.id = od.ordersId where o.sellerId='".$_GET['sellerId']."' and o.createdOn like '".$today."%' order by createdOn";
            }else{
                $sql_1 = "select o.id,od.skuId,od.itemTitle,od.quantity,DATE_FORMAT(o.createdOn, '%Y-%m-%d') as date1,DATE_FORMAT(o.createdOn, '%a') as date2 from qo_orders as o left join qo_orders_detail as od on o.id = od.ordersId where o.createdOn like '".$today."%' order by createdOn";
            }
            
            $result_1 = mysql_query($sql_1, Reports::$database_connect);
            while($row_1 = mysql_fetch_assoc($result_1)){
                if(empty($sku_array[$row_1['skuId']]['sku_id'])){
                    $sku_array[$row_1['skuId']]['sku_id'] = $row_1['skuId'];
                    $sku_array[$row_1['skuId']]['item_title'] = $row_1['itemTitle'];
                }
                
                if(empty($sku_array[$row_1['skuId']]['7_'.$row_1['date2'].'_quantity'])){
                    $sku_array[$row_1['skuId']]['7_'.$row_1['date2'].'_quantity'] = $row_1['quantity'];
                    
                    if(empty($sku_array[$row_1['skuId']]['7_total_num'])){
                        $sku_array[$row_1['skuId']]['7_total_num'] = $row_1['quantity'];
                    }else{
                        $sku_array[$row_1['skuId']]['7_total_num'] += $row_1['quantity'];
                    }
                    
                }else{
                    $sku_array[$row_1['skuId']]['7_'.$row_1['date2'].'_quantity'] += $row_1['quantity'];
                    $sku_array[$row_1['skuId']]['7_total_num'] += $row_1['quantity'];
                }
            }
            
            //****************************************************************************************************
            
            
            //********************************************* Last Week Today  *************************************
            if(!empty($_GET['sellerId'])){
                $sql_1 = "select o.id,od.skuId,od.itemTitle,od.quantity,DATE_FORMAT(o.createdOn, '%Y-%m-%d') as date1,DATE_FORMAT(o.createdOn, '%a') as date2 from qo_orders as o left join qo_orders_detail as od on o.id = od.ordersId where o.sellerId='".$_GET['sellerId']."' and o.createdOn like '".$lastWeekToday."%' order by createdOn";
            }else{
                $sql_1 = "select o.id,od.skuId,od.itemTitle,od.quantity,DATE_FORMAT(o.createdOn, '%Y-%m-%d') as date1,DATE_FORMAT(o.createdOn, '%a') as date2 from qo_orders as o left join qo_orders_detail as od on o.id = od.ordersId where o.createdOn like '".$lastWeekToday."%' order by createdOn";
            }
            
            if(empty($sku_array[$row_1['skuId']]['8_'.$row_1['date2'].'_quantity'])){
                $sku_array[$row_1['skuId']]['8_'.$row_1['date2'].'_quantity'] = $row_1['quantity'];
                
                if(empty($sku_array[$row_1['skuId']]['8_total_num'])){
                    $sku_array[$row_1['skuId']]['8_total_num'] = $row_1['quantity'];
                }else{
                    $sku_array[$row_1['skuId']]['8_total_num'] += $row_1['quantity'];
                }
                
            }else{
                $sku_array[$row_1['skuId']]['8_'.$row_1['date2'].'_quantity'] += $row_1['quantity'];
                $sku_array[$row_1['skuId']]['8_total_num'] += $row_1['quantity'];
            }
                
            //****************************************************************************************************
            
            $sku_array[$row_1['skuId']]['today_growth_rate'] = (empty($sku_array[$row_1['skuId']]['8_total_num'])?-($sku_array[$row_1['skuId']]['7_total_num'] * 100):((empty($sku_array[$row_1['skuId']]['7_total_num']))?($row_1['skuId']]['7_total_num'] * 100):(($sku_array[$row_1['skuId']]['8_total_num'] - $sku_array[$row_1['skuId']]['7_total_num']) / $sku_array[$row_1['skuId']]['7_total_num'])));
            
            //******************************************  Last 4 Week  *******************************************
            if(!empty($_GET['sellerId'])){
                $sql_1 = "select o.id,od.skuId,od.itemTitle,od.quantity,DATE_FORMAT(o.createdOn, '%Y-%m-%d') as date1,DATE_FORMAT(o.createdOn, '%a') as date2 from qo_orders as o left join qo_orders_detail as od on o.id = od.ordersId where o.sellerId='".$_GET['sellerId']."' and o.createdOn between '".$eightWeekAgoMon."' and '".$fourWeekAgoMon."' order by createdOn";
            }else{
                $sql_1 = "select o.id,od.skuId,od.itemTitle,od.quantity,DATE_FORMAT(o.createdOn, '%Y-%m-%d') as date1,DATE_FORMAT(o.createdOn, '%a') as date2 from qo_orders as o left join qo_orders_detail as od on o.id = od.ordersId where o.createdOn between '".$eightWeekAgoMon."' and '".$fourWeekAgoMon."' order by createdOn";
            }
            
            //echo $sql_1;
            //echo "<br>";
            
            $result_1 = mysql_query($sql_1, Reports::$database_connect);
            while($row_1 = mysql_fetch_assoc($result_1)){
                if(empty($sku_array[$row_1['skuId']]['sku_id'])){
                    $sku_array[$row_1['skuId']]['sku_id'] = $row_1['skuId'];
                    $sku_array[$row_1['skuId']]['item_title'] = $row_1['itemTitle'];
                }
                
                if(empty($sku_array[$row_1['skuId']]['5_'.$row_1['date2'].'_quantity'])){
                    $sku_array[$row_1['skuId']]['5_'.$row_1['date2'].'_quantity'] = $row_1['quantity'];
                    
                    if(empty($sku_array[$row_1['skuId']]['5_total_num'])){
                        $sku_array[$row_1['skuId']]['5_total_num'] = $row_1['quantity'];
                    }else{
                        $sku_array[$row_1['skuId']]['5_total_num'] += $row_1['quantity'];
                    }
                    
                }else{
                    $sku_array[$row_1['skuId']]['5_'.$row_1['date2'].'_quantity'] += $row_1['quantity'];
                    $sku_array[$row_1['skuId']]['5_total_num'] += $row_1['quantity'];
                }
            }         
            
            //******************************************  This 4 Week  *******************************************
            
            if(!empty($_GET['sellerId'])){
                $sql_2 = "select o.id,od.skuId,od.itemTitle,od.quantity,DATE_FORMAT(o.createdOn, '%Y-%m-%d') as date1,DATE_FORMAT(o.createdOn, '%a') as date2 from qo_orders as o left join qo_orders_detail as od on o.id = od.ordersId where o.sellerId='".$_GET['sellerId']."' and o.createdOn between '".$fourWeekAgoMon."' and '".$nextSun."' order by createdOn";
            }else{
                $sql_2 = "select o.id,od.skuId,od.itemTitle,od.quantity,DATE_FORMAT(o.createdOn, '%Y-%m-%d') as date1,DATE_FORMAT(o.createdOn, '%a') as date2 from qo_orders as o left join qo_orders_detail as od on o.id = od.ordersId where o.createdOn between '".$fourWeekAgoMon."' and '".$nextSun."' order by createdOn";
            }
            
            //echo $sql_2;
            //echo "<br>";
            
            //exit;
            $result_2 = mysql_query($sql_2, Reports::$database_connect);
            while($row_2 = mysql_fetch_assoc($result_2)){
                if(empty($sku_array[$row_2['skuId']]['sku_id'])){
                    $sku_array[$row_2['skuId']]['sku_id'] = $row_2['skuId'];
                    $sku_array[$row_2['skuId']]['item_title'] = $row_2['itemTitle'];
                }
                
                if(empty($sku_array[$row_2['skuId']]['6_'.$row_2['date2'].'_quantity'])){
                    $sku_array[$row_2['skuId']]['6_'.$row_2['date2'].'_quantity'] = $row_2['quantity'];
                    
                    if(empty($sku_array[$row_2['skuId']]['6_total_num'])){
                        $sku_array[$row_2['skuId']]['6_total_num'] = $row_2['quantity'];
                    }else{
                        $sku_array[$row_2['skuId']]['6_total_num'] += $row_2['quantity'];
                    }
                    
                }else{
                    $sku_array[$row_2['skuId']]['6_'.$row_2['date2'].'_quantity'] += $row_2['quantity'];
                    $sku_array[$row_2['skuId']]['6_total_num'] += $row_2['quantity'];
                }
            }         
        
            //************************************************************************************************
            
            for($i=0; $i<35; $i++){
                $date = date("Y-m-d", $timestamp + ($i * 60 * 60 * 24));
                
                $day = date("D", strtotime($date));
                $index = $date ."|".$day;
                //echo $date;
                //echo "<br>";
                if(!empty($_GET['sellerId'])){
                    $sql = "select o.id,od.skuId,od.itemTitle,sum(od.quantity) as quantity,DATE_FORMAT(o.createdOn, '%Y-%m-%d') as date1,DATE_FORMAT(o.createdOn, '%a') as date2 from qo_orders as o left join qo_orders_detail as od on o.id = od.ordersId where o.sellerId='".$_GET['sellerId']."' and o.createdOn like '".$date."%' group by skuId order by createdOn";
                }else{
                    $sql = "select o.id,od.skuId,od.itemTitle,sum(od.quantity) as quantity,DATE_FORMAT(o.createdOn, '%Y-%m-%d') as date1,DATE_FORMAT(o.createdOn, '%a') as date2 from qo_orders as o left join qo_orders_detail as od on o.id = od.ordersId where o.createdOn like '".$date."%' group by skuId order by createdOn";    
                }
                //echo $sql;
                //echo "<br>";
                $result = mysql_query($sql, Reports::$database_connect);
                $j = 0;
                while($row = mysql_fetch_assoc($result)){
                    //print_r($row);
                    $day_data[$index][$j] = $row;
                    
                    //before 5 week age
                    if($row['date1'] >= $$fiveWeekAgoMon && $row['date1'] < $fourWeekAgoMon){
                        if(empty($sku_array[$row['skuId']]['sku_id'])){
                            $sku_array[$row['skuId']]['sku_id'] = $row['skuId'];
                        }
                        
                        $sku_array[$row['skuId']]['0_title'] = $row['itemTitle'];
                        
                        if(empty($sku_array[$row['skuId']]['0_'.$row['date2'].'_quantity'])){
                            $sku_array[$row['skuId']]['0_'.$row['date2'].'_quantity'] = $row['quantity'];
                            
                            if(empty($sku_array[$row['skuId']]['0_total_num'])){
                                $sku_array[$row['skuId']]['0_total_num'] = $row['quantity'];
                            }else{
                                $sku_array[$row['skuId']]['0_total_num'] += $row['quantity'];
                            }
                            
                        }else{
                            $sku_array[$row['skuId']]['0_'.$row['date2'].'_quantity'] += $row['quantity'];
                            $sku_array[$row['skuId']]['0_total_num'] += $row['quantity'];
                        }
                    //before 4 week age
                    }elseif($row['date1'] >= $fourWeekAgoMon && $row['date1'] < $threeWeekAgoMon){
                        if(empty($sku_array[$row['skuId']]['sku_id'])){
                            $sku_array[$row['skuId']]['sku_id'] = $row['skuId'];
                        }
                        
                        $sku_array[$row['skuId']]['1_title'] = $row['itemTitle'];
                        
                        if(empty($sku_array[$row['skuId']]['1_'.$row['date2'].'_quantity'])){
                            $sku_array[$row['skuId']]['1_'.$row['date2'].'_quantity'] = $row['quantity'];
                            
                            if(empty($sku_array[$row['skuId']]['1_total_num'])){
                                $sku_array[$row['skuId']]['1_total_num'] = $row['quantity'];
                            }else{
                                $sku_array[$row['skuId']]['1_total_num'] += $row['quantity'];
                            }
                            
                        }else{
                            $sku_array[$row['skuId']]['1_'.$row['date2'].'_quantity'] += $row['quantity'];
                            $sku_array[$row['skuId']]['1_total_num'] += $row['quantity'];
                        }
                    //before 3 week age
                    }elseif($row['date1'] >= $threeWeekAgoMon && $row['date1'] < $twoWeekAgoMon){
                        if(empty($sku_array[$row['skuId']]['sku_id'])){
                            $sku_array[$row['skuId']]['sku_id'] = $row['skuId'];
                        }
                        
                        $sku_array[$row['skuId']]['2_title'] = $row['itemTitle'];
                         
                        if(empty($sku_array[$row['skuId']]['2_'.$row['date2'].'_quantity'])){
                            $sku_array[$row['skuId']]['2_'.$row['date2'].'_quantity'] = $row['quantity'];
                            
                            if(empty($sku_array[$row['skuId']]['2_total_num'])){
                                $sku_array[$row['skuId']]['2_total_num'] = $row['quantity'];
                            }else{
                                $sku_array[$row['skuId']]['2_total_num'] += $row['quantity'];
                            }
                            
                        }else{
                            $sku_array[$row['skuId']]['2_'.$row['date2'].'_quantity'] += $row['quantity'];
                            $sku_array[$row['skuId']]['2_total_num'] += $row['quantity'];
                        }
                    //before 2 week age
                    }elseif($row['date1'] >= $twoWeekAgoMon && $row['date1'] < $oneWeekAgoMon){
                        if(empty($sku_array[$row['skuId']]['sku_id'])){
                            $sku_array[$row['skuId']]['sku_id'] = $row['skuId'];
                        }
                        
                        $sku_array[$row['skuId']]['3_title'] = $row['itemTitle'];
                        
                        if(empty($sku_array[$row['skuId']]['3_'.$row['date2'].'_quantity'])){
                            $sku_array[$row['skuId']]['3_'.$row['date2'].'_quantity']= $row['quantity'];
                            
                            if(empty($sku_array[$row['skuId']]['3_total_num'])){
                                $sku_array[$row['skuId']]['3_total_num'] = $row['quantity'];
                            }else{
                                $sku_array[$row['skuId']]['3_total_num'] += $row['quantity'];
                            }
                            
                        }else{ 
                            $sku_array[$row['skuId']]['3_'.$row['date2'].'_quantity'] += $row['quantity'];
                            $sku_array[$row['skuId']]['3_total_num'] += $row['quantity'];
                        }
                    //before 1 week age
                    }elseif($row['date1'] >= $oneWeekAgoMon){
                        if(empty($sku_array[$row['skuId']]['sku_id'])){
                            $sku_array[$row['skuId']]['sku_id'] = $row['skuId'];
                        }
                        
                        $sku_array[$row['skuId']]['4_title'] = $row['itemTitle'];
                        
                        if(empty($sku_array[$row['skuId']]['4_'.$row['date2'].'_quantity'])){
                            $sku_array[$row['skuId']]['4_'.$row['date2'].'_quantity'] = $row['quantity'];
                            
                            if(empty($sku_array[$row['skuId']]['4_total_num'])){
                                $sku_array[$row['skuId']]['4_total_num'] = $row['quantity'];
                            }else{
                                $sku_array[$row['skuId']]['4_total_num'] += $row['quantity'];
                            }
                            
                        }else{
                            $sku_array[$row['skuId']]['4_'.$row['date2'].'_quantity'] += $row['quantity'];
                            $sku_array[$row['skuId']]['4_total_num'] += $row['quantity'];
                        }
                    }
                    
                    $j++;
                   
                }
                //flush();
            }
            //print_r($sku_array);
            $temp = array();
            $totalCount = 0;
            foreach($sku_array as $sku){
                $sku['1_growth_rate'] = (empty($sku['0_total_num']) || $sku['0_total_num'] == 0)?($sku['1_total_num'] * 100):((empty($sku['1_total_num']) || $sku['1_total_num'] == 0)?-($sku['0_total_num'] * 100):round((($sku['1_total_num'] - $sku['0_total_num']) / $sku['0_total_num']) * 100));
                $sku['2_growth_rate'] = (empty($sku['1_total_num']) || $sku['1_total_num'] == 0)?($sku['2_total_num'] * 100):((empty($sku['2_total_num']) || $sku['2_total_num'] == 0)?-($sku['1_total_num'] * 100):round((($sku['2_total_num'] - $sku['1_total_num']) / $sku['1_total_num']) * 100));
                $sku['3_growth_rate'] = (empty($sku['2_total_num']) || $sku['2_total_num'] == 0)?($sku['3_total_num'] * 100):((empty($sku['3_total_num']) || $sku['3_total_num'] == 0)?-($sku['2_total_num'] * 100):round((($sku['3_total_num'] - $sku['2_total_num']) / $sku['2_total_num']) * 100));
                $sku['4_growth_rate'] = (empty($sku['3_total_num']) || $sku['3_total_num'] == 0)?($sku['4_total_num'] * 100):((empty($sku['4_total_num']) || $sku['4_total_num'] == 0)?-($sku['3_total_num'] * 100):round((($sku['4_total_num'] - $sku['3_total_num']) / $sku['3_total_num']) * 100));
                $sku['5_growth_rate'] = (empty($sku['5_total_num']) || $sku['5_total_num'] == 0)?($sku['6_total_num'] * 100):((empty($sku['6_total_num']) || $sku['6_total_num'] == 0)?-($sku['5_total_num'] * 100):round((($sku['6_total_num'] - $sku['5_total_num']) / $sku['5_total_num']) * 100));
                $temp[] = $sku;
                $totalCount++;
            }
            
            $data_array = array('totalCount'=>$totalCount, 'records'=>$temp);
            Reports::$memcache_connect->set($timestamp, $data_array, MEMCACHE_COMPRESSED, 86400);
            mysql_free_result($result);
        }
        //print_r($temp);
        echo json_encode($data_array);
        //print_r($data_array);
    }
    
    public function __destruct(){
        mysql_close(Reports::$database_connect);
        Reports::$memcache_connect->close();
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