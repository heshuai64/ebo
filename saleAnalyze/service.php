<?php
class Service{
    private $config;
    
    public function __construct(){
        $this->config = parse_ini_file("config.ini", true);
    }
    
    private function proxy($url, $post_data=''){
        $fields_string = "";
        if(!empty($post_data)){
            foreach($post_data as $key=>$value){
                $fields_string .= $key."=".$value."&";    
            }
            $fields_string = substr($fields_string, 0, -1);
        }
        
        //echo $url."&".$fields_string."\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;    
    }
    
    public function statisticsSkuSale(){
        //$_POST['sku'] = 'EL00010';
        /*
        CE00002
        CT00004
        */
        
        if(empty($_POST['sku'])){
            $result = $this->proxy($this->config['service']['inventory']."?action=searchSkuByTitle_CNTitle_Status", array('status'=>$_POST['status'], 'title'=>urlencode($_POST['title']), 'cn_title'=> urlencode($_POST['cn_title'])));
            $result_json = json_decode($result);
            $sku = $result_json->records;
            //$totalrecords = $result_json->totalCount;
        }else{
            $sku = explode("\n", $_POST['sku']);
            
            $sku_string = "";
            foreach($sku as $s){
                $sku_string .= "'".$s."',";
            }
            $sku_string = substr($sku_string, 0, -1);
            $result = $this->proxy($this->config['service']['inventory']."?action=searchSkuByTitle_CNTitle_Status", array('status'=>$_POST['status'], 'title'=>urlencode($_POST['title']), 'cn_title'=> urlencode($_POST['cn_title']), 'sku'=>$sku_string));
            $result_json = json_decode($result);
            $sku = $result_json->records;
            //$totalrecords = count($sku);
        }
        
        //echo $result."\n";
        
        $sku_data = array();
        if(count($sku) > 0){
            $sku_string = "";
            foreach($sku as $s){
                if(!empty($s->sku)){
                    $sku_string .= "'".$s->sku."',";
                    $sku_data[$s->sku]['title'] = $s->title;
                    $sku_data[$s->sku]['cn_title'] = $s->cn_title;
                    $sku_data[$s->sku]['status'] = $s->status;
                    $sku_data[$s->sku]['v_stock'] = $s->v_stock;
                }else{
                    $sku_string .= "'".$s."',";
                }
            }
            $sku_string = substr($sku_string, 0, -1);
            
            $result = $this->proxy($this->config['service']['dataWarehouse']."?action=getSkuWeekMonthSale", array('sku'=>$sku_string));
            $sku_week_month_sale = json_decode($result);
            
            //print_r($sku_week_month_sale);
            $tmp = array();
            foreach($sku_week_month_sale as $swms){
                $swms->title = $sku_data[$swms->sku]['title'];
                $swms->cn_title = $sku_data[$swms->sku]['cn_title'];
                $swms->status = $sku_data[$swms->sku]['status'];
                $swms->v_stock = $sku_data[$swms->sku]['v_stock'];
                $tmp[] = $swms;
            }
            
            echo json_encode(array('totalrecords'=> count($tmp), 'records'=>$tmp));
        }else{
            echo json_encode(array('totalrecords'=> 0, 'records'=> '[]'));
        }
    }
    
    public function getLingSale(){
        //$_POST['start'] = "2011-12-01";
        //$_POST['end'] = "2011-12-04";
        
        $sku = explode("\n", $_POST['sku']);
        $sku_string = "";
        foreach($sku as $s){
            $sku_string .= "'".$s."',";
        }
        $sku_string = substr($sku_string, 0, -1);
        $result = $this->proxy($this->config['service']['dataWarehouse']."?action=getListingSkuSaleByEndTime", array('start'=>$_POST['start'], 'end'=>$_POST['end'], 'sku'=>$sku_string));
        //echo $result."\n";
        $result_json = json_decode($result);
        
        foreach($result_json as $s){
            $sku_string .= "'".$s->SKU."',";
        }
        $sku_string = substr($sku_string, 0, -1);
        $result = $this->proxy($this->config['service']['inventory']."?action=searchSkuByTitle_CNTitle_Status", array('status'=>$_POST['status'], 'title'=>urlencode($_POST['title']), 'cn_title'=> urlencode($_POST['cn_title']), 'sku'=>$sku_string));
        //echo $result."\n";
        $result_json = json_decode($result);
        $sku = $result_json->records;
        
        //print_r($sku);
        $sku_data = array();
        $sku_string = "";
        foreach($sku as $s){
            $sku_string .= "'".$s->sku."',";
            $sku_data[$s->sku]['title'] = $s->title;
            $sku_data[$s->sku]['cn_title'] = $s->cn_title;
            $sku_data[$s->sku]['status'] = $s->status;
            $sku_data[$s->sku]['v_stock'] = $s->v_stock;
            $sku_data[$s->sku]['sku_low_price'] = $s->sku_low_price;
        }
        $sku_string = substr($sku_string, 0, -1);
            
        $result = $this->proxy($this->config['service']['dataWarehouse']."?action=getListingSaleByEndTime", array('start'=>$_POST['start'], 'end'=>$_POST['end'], 'sku'=>$sku_string));
        //echo $result."\n";
        $listing_sale = json_decode($result);
        //print_r($sku_week_month_sale);
        $tmp = array();
        foreach($listing_sale as $ls){
            //$ls->title = $sku_data[$swms->sku]['title'];
            //$ls->cn_title = $sku_data[$swms->sku]['cn_title'];
            $ls->SkuLowPrice = $sku_data[$ls->SKU]['sku_low_price'];
            $ls->Status = $sku_data[$ls->SKU]['status'];
            $ls->V_Stock = $sku_data[$ls->SKU]['v_stock'];
            $tmp[] = $ls;
        }
        
        echo json_encode(array('totalrecords'=> count($tmp), 'records'=>$tmp));
    }
    
    public function login(){
        if($_SERVER['REMOTE_ADDR'] != "127.0.0.1" && substr($_SERVER['REMOTE_ADDR'], 0, 8) != "192.168."){
	    $ip_array = json_decode(file_get_contents("http://192.168.1.168:8888/eBayBO/service.php?action=getClientIp"));
	    //file_put_contents("/tmp/xx.log", print_r($ip_array, true));
	    if(!in_array($_SERVER['REMOTE_ADDR'], $ip_array)){
		echo "{success: false}";
		return 0;
	    }
	}
        
        session_set_cookie_params(24 * 60 * 60);
        session_start();
        $sql = "select id,name,role from user where name = '".$_POST['name']."' and password = '".$_POST['password']."'";
        $result = mysql_query($sql, $this->conn);
        $row = mysql_fetch_assoc($result);
        if(!empty($row['id'])){
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['role'] = $row['role'];
            setcookie("user_name", $row['name'], time() + (60 * 60 * 24), '/');
            setcookie("user_role", $row['role'], time() + (60 * 60 * 24), '/');
            echo "{success: true}";
        }else{
            echo "{success: false}";
        }
    }
    
    public function __destruct(){
        
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