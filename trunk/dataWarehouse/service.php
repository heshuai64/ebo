<?php
//define ('__DOCROOT__', '/export/eBayBO/dataWarehouse');
define ('__DOCROOT__', '.');

class Service{
    private static $database_connect;
    private $config;
    
    public function __construct(){
        $this->config = parse_ini_file(__DOCROOT__ . '/config.ini', true);
        
        Service::$database_connect = mysql_connect($this->config['database']['host'], $this->config['database']['user'], $this->config['database']['password']);

        if (!Service::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(Service::$database_connect);
            exit;
        }
	
        mysql_query("SET NAMES 'UTF8'", Service::$database_connect);
	
        if (!mysql_select_db($this->config['database']['name'], Service::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(Service::$database_connect);
            exit;
        }
    }
    
    public function getSkuSale(){
        $yesterday = date("Y-m-d", time() - 24 * 60 * 60);
        //$yesterday = "2010-08-03";
        
        $group = "";
        if(!empty($_POST['group'])){
            $group = "group by ".$_POST['group'];
        }
        
        if(!empty($_POST['date_start']) && !empty($_POST['date_end'])){
            $shippedOn = "createdOn between '".$_POST['date_start']."' and '".$_POST['date_end']."'";
            
        }else{
            $shippedOn = "createdOn like '".$yesterday."%'";
        }
        $sql = "select count(*) as count from skuSale where ".$shippedOn.$group;
        //echo $sql."\n";
        
	$result = mysql_query($sql, Service::$database_connect);
	$row = mysql_fetch_assoc($result);
        if(!empty($_POST['group'])){
            $totalCount = mysql_num_rows($result);
        }else{
            $totalCount = $row['count'];
        }
        
        if(empty($_POST['start']) && empty($_POST['limit'])){
            $_POST['start'] = 0;
            $_POST['limit'] = 20;
        }
        //sku,itemId,itemTitle,sellerId,shipToCity,shipToCountry,shippedOn,
        if(!empty($_POST['group'])){
            $sql = "select ".$_POST['group'].",count(".$_POST['group'].") as number,sum(skuCost) as skuCost,sum(skuLowestPrice) as skuLowestPrice,sum(salePrice) as salePrice from skuSale where ".$shippedOn." ".$group." order by ".$_POST['group']." limit ".$_POST['start'].",".$_POST['limit'];
        }else{
            $sql = "select * from skuSale where ".$shippedOn." order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
        }
        //echo $sql."\n";
        
        $result = mysql_query($sql, Service::$database_connect);
        $data = array();
        while($row = mysql_fetch_assoc($result)){
            if(!empty($row['createdOn'])){
                $row['createdOn'] = substr($row['createdOn'], 0, 10);
            }
            $data[] = $row;
        }
        
        echo json_encode(array('totalCount'=>$totalCount, 'records'=>$data));
    }
    
    public function getSkuShip(){
        $yesterday = date("Y-m-d", time() - 24 * 60 * 60);
        //$yesterday = "2010-08-03";
        
        $group = "";
        if(!empty($_POST['group'])){
            $group = "group by ".$_POST['group'];
        }
        
        if(!empty($_POST['date_start']) && !empty($_POST['date_end'])){
            $shippedOn = "shippedOn between '".$_POST['date_start']."' and '".$_POST['date_end']."'";
            
        }else{
            $shippedOn = "shippedOn like '".$yesterday."%'";
        }
        $sql = "select count(*) as count from skuShip where ".$shippedOn.$group;
        //echo $sql."\n";
        
	$result = mysql_query($sql, Service::$database_connect);
	$row = mysql_fetch_assoc($result);
        if(!empty($_POST['group'])){
            $totalCount = mysql_num_rows($result);
        }else{
            $totalCount = $row['count'];
        }
        
        if(empty($_POST['start']) && empty($_POST['limit'])){
            $_POST['start'] = 0;
            $_POST['limit'] = 20;
        }
        //sku,itemId,itemTitle,sellerId,shipToCity,shipToCountry,shippedOn,
        if(!empty($_POST['group'])){
            $sql = "select ".$_POST['group'].",count(".$_POST['group'].") as number,sum(skuCost) as skuCost,sum(skuLowestPrice) as skuLowestPrice,sum(salePrice) as salePrice from skuShip where ".$shippedOn." ".$group." order by ".$_POST['group']." limit ".$_POST['start'].",".$_POST['limit'];
        }else{
            $sql = "select * from skuShip where ".$shippedOn." order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
        }
        //echo $sql."\n";
        
        $result = mysql_query($sql, Service::$database_connect);
        $data = array();
        while($row = mysql_fetch_assoc($result)){
            if(!empty($row['shippedOn'])){
                $row['shippedOn'] = substr($row['shippedOn'], 0, 10);
            }
            $data[] = $row;
        }
        
        echo json_encode(array('totalCount'=>$totalCount, 'records'=>$data));
    }
    
    public function getSellerShippedChart(){
        $sql = "select * from skuship where shippedOn between '' and '' group by sellerId";
        
    }
    
    public function getSkuWeekMonthSale(){
        $_GET['sku'] = str_replace("\\", "", $_GET['sku']);
        $sql = "select * from skuWeekMonthSale where sku in (".$_GET['sku'].")";    
        //echo $sql;
        $result = mysql_query($sql, Service::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $array[] = $row;
        }
        echo json_encode($array);
    }
    
    public function __destruct(){
        mysql_close(Service::$database_connect);
    }
}

if(!empty($argv[1])){
    $action = $argv[1];
}else{
    $action = (!empty($_GET['action']))?$_GET['action']:$_POST['action'];
}

$service = new Service();
$service->$action();


?>