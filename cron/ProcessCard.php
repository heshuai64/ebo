<?php
define ('__DOCROOT__', '/export/eBayBO');
ini_set("memory_limit","256M");

function cmp($a, $b)
{
    return strcmp($a["sort"], $b["sort"]);
}

class ProcessCard{
    private $database_connect;
    private $config;
    private $startTime;
    private $endTime;
    const FILE_PATH = '/export/eBayBO/excel/';
    const BAR_CODE_URL = '/eBayBO/cron/image.php';
    
    public function __construct(){
	global $argv;
	
        $this->config = parse_ini_file(__DOCROOT__ . '/config.ini', true);
        $this->database_connect = mysql_connect($this->config['database']['host'], $this->config['database']['user'], $this->config['database']['password']);
        if (!$this->database_connect) {
            echo "Unable to connect to DB: " . mysql_error($this->database_connect);
            exit;
        }
        
        mysql_query("SET NAMES 'UTF8'", $this->database_connect);
        
        if (!mysql_select_db($this->config['database']['name'], $this->database_connect)) {
            echo "Unable to select mydbname: " . mysql_error($this->database_connect);
            exit;
        }
        
	if(!empty($argv[2]) && !empty($argv[3])){
	    $this->startTime = $argv[2];
	    $this->endTime = $argv[3];
	}else{
	    $this->startTime = date("Y-m-d 09:10:00",mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
	    $this->endTime   = date("Y-m-d 09:10:00");
	}
	
	if(!file_exists(self::FILE_PATH.date("Ym"))){
	    mkdir(self::FILE_PATH.date("Ym"), 0777);
	}
	
	if(!file_exists(self::FILE_PATH.date("Ym")."/".date("d"))){
	    mkdir(self::FILE_PATH.date("Ym")."/".date("d"), 0777);
	}
    }
    
    public function setStartTime($startTime){
        $this->startTime = $startTime;
    }
        
    public function setEndTime($endTime){
        $this->endTime = $endTime;
    }
    
    private function getService($url, $action, $args){
	$argss = "";
	foreach($args as $key=>$value){
	    $argss .= "&".$key."=".$value;
	}
	$address = $url."?action=".$action.$argss;
	//echo $address."\n";
        return file_get_contents($address);
    }
    
    private function getReadyPackSkuByBulk(){
        $sql = "select s.createdOn,sd.skuId,sum(sd.quantity) as quantity from qo_orders as o,qo_shipments as s,
        qo_shipments_detail as sd where o.id = s.ordersId and o.shippingMethod = 'B' and s.id = sd.shipmentsId and s.modifiedOn between '".$this->startTime."' and '".$this->endTime."'
        and s.status = 'N' group by sd.skuId order by sd.skuId";
        //echo $sql."\n";
        $result = mysql_query($sql, $this->database_connect);
	$i = 0;
        while($row = mysql_fetch_assoc($result)){
	    //print_r($row);
	    //exit;
	    //$row['skuId'] = "CO00006";
	    $row['inventory_data'] = json_decode($this->getService($this->config['service']['inventory'], 'getSkuProcessCardInfo', array('sku'=>$row['skuId'])));
            $row['sort'] = (!empty($row['inventory_data'][0]->combo_locator_number))?$row['inventory_data'][0]->combo_locator_number:$row['inventory_data'][0]->locator_number;
	    $data[] = $row;
	    $i++;
        }
	usort($data, "cmp");
	return $data;
    }
    
    private function getReadyPackSkuByNoBulk(){
        $sql = "select s.createdOn,sd.skuId,sum(sd.quantity) as quantity from qo_orders as o,qo_shipments as s,
        qo_shipments_detail as sd where o.id = s.ordersId and o.shippingMethod <> 'B' and s.id = sd.shipmentsId and s.modifiedOn between '".$this->startTime."' and '".$this->endTime."'
        and s.status = 'N' group by sd.skuId order by sd.skuId";
        //echo $sql."\n";
        $result = mysql_query($sql, $this->database_connect);
	$i = 0;
        while($row = mysql_fetch_assoc($result)){
	    //print_r($row);
	    //exit;
	    //$row['skuId'] = "CO00006";
	    $row['inventory_data'] = json_decode($this->getService($this->config['service']['inventory'], 'getSkuProcessCardInfo', array('sku'=>$row['skuId'])));
            $row['sort'] = $row['inventory_data'][0]->locator_number;
	    $data[] = $row;
	    $i++;
        }
	usort($data, "cmp");
	return $data;
    }
    
    private function getReadyPackSku(){
	$single = array();
	$multi = array();
	
	$sql = "select s.id,sd.skuId,sd.quantity from qo_shipments as s,qo_shipments_detail as sd 
	where s.id = sd.shipmentsId and s.modifiedOn between '".$this->startTime."' and '".$this->endTime."' 
        and s.status = 'N'";
	$result = mysql_query($sql, $this->database_connect);
	while($row = mysql_fetch_assoc($result)){
	    $sql_1 = "select count(*) as num from qo_shipments where id = '".$row['id']."'";
	    $result_1 = mysql_query($sql_1, $this->database_connect);
	    $row_1 = mysql_fetch_assoc($result_1);
	    if($row_1['num'] > 1){
		echo $row['id']." num > 1\n";
		$multi[$row['skuId']]['quantity'] += $row['quantity'];
	    }elseif($row['quantity'] > 1){
		echo $row['id']." quantity > 1\n";
		$multi[$row['skuId']]['quantity'] += $row['quantity'];
	    }else{
		echo $row['id']." quantity = 1\n";
		$single[$row['skuId']]['quantity'] += $row['quantity'];
	    }
	}
	
	foreach($single as $k=>$v){
	    $inventory_data = json_decode($this->getService($this->config['service']['inventory'], 'getSkuProcessCardInfo', array('sku'=>$k)));
            $single[$k]['inventory_data'] = $inventory_data;
	    $single[$k]['sort'] = $inventory_data[0]->locator_number;
	}
	
	foreach($multi as $k=>$v){
	    $inventory_data = json_decode($this->getService($this->config['service']['inventory'], 'getSkuProcessCardInfo', array('sku'=>$k)));
	    $multi[$k]['inventory_data'] = $inventory_data;
	    $multi[$k]['sort'] = $inventory_data[0]->locator_number;
	}
	
	return array('single'=>$single,
		     'multi'=>$multi);
    }
    
    private function template($data){
	$tempalte_str = '
	<table border="1">
	    <tr>
		<td>仓位</td>
		<td>日期</td>
		<td>是否测试</td>
		<td>重量</td>
		<td>一周销量</td>
		<td>发货数量</td>
		<td>仓库签字</td>
		<td>QC确认</td>
		<td>布吉仓库存</td>
	    </tr>
	    <tr>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>%s</td>
	    </tr>
	    <tr>
		<td>产品编号</td>
		<td>%s</td>
		<td colspan="7">配件说明:%s</td>
	    </tr>
	    <tr>
		<td>产品条码</td>
		<td colspan="3" style="text-align: center;"><img src="'.ProcessCard::BAR_CODE_URL.'?code=code39&o=1&t=30&r=1&text=%s&f1=-1&f2=8&a1=&a2=&a3="></td>
		<td colspan="5">
		    <table border="1">
			<tr>
			    <td>良品数</td>
			    <td>不良品数</td>
			    <td colspan="2">包装签名</td>
			    <td>抽检确认</td>
			</tr>
			<tr>
			    <td>&nbsp;</td>
			    <td>&nbsp;</td>
			    <td>&nbsp;</td>
			    <td>&nbsp;</td>
			</tr>
		    </table>
		</td>
	    </tr>
	    <tr>
		<td>材料编号</td>
		<td>名称</td>
		<td>BOM</td>
		<td>库存</td>
		<td>条形棉编号</td>
		<td>个数</td>
		<td>块状棉编号</td>
		<td>个数</td>
		<td>信封</td>
	    </tr>
	    %s
	</table>';
	
	//print_r($data);
	$bom = '';
	foreach($data['inventory_data'] as $inventory_data){
	    //var_dump($inventory_data);
	    $locator_number .= $inventory_data->locator_number.",";
	    $weight += $inventory_data->weight;
	    //$sku_and_title .= $inventory_data->sku ." ". $inventory_data->china_title."<br/>";
	    $week_sale = (!empty($inventory_data->combo_week_sale))?$inventory_data->combo_week_sale:$inventory_data->week_sale;
	    $stock = (!empty($inventory_data->combo_stock))?$inventory_data->combo_stock:$inventory_data->stock;
	    //$accessories .= $inventory_data->accessories."<br/>";
	    $bom .= '
	    <tr>
		<td>'.$inventory_data->sku.'</td>
		<td>'.$inventory_data->china_title.'</td>
		<td>'.$inventory_data->quantity.'</td>
		<td>'.$inventory_data->stock.'</td>
		<td>'.$inventory_data->bar_cotton.'</td>
		<td>'.$inventory_data->bar_cotton_number.'</td>
		<td>'.$inventory_data->massive_cotton.'</td>
		<td>'.$inventory_data->massive_cotton_number.'</td>
		<td>'.$inventory_data->envelope.'</td>
	    </tr>';
	}
	
	
	$locator_number = (!empty($data['inventory_data'][0]->combo_locator_number))?$data['inventory_data'][0]->combo_locator_number:$data['inventory_data'][0]->locator_number;
	$sku = (!empty($data['inventory_data'][0]->combo_sku))?$data['inventory_data'][0]->combo_sku:$data['inventory_data'][0]->sku;
	$weight = (!empty($data['inventory_data'][0]->combo_weight))?$data['inventory_data'][0]->combo_weight:$data['inventory_data'][0]->weight;
	$week_sale = (!empty($data['inventory_data'][0]->combo_week_sale))?$data['inventory_data'][0]->combo_week_sale:$data['inventory_data'][0]->week_sale;
	$stock = (!empty($data['inventory_data'][0]->combo_stock))?$data['inventory_data'][0]->combo_stock:$data['inventory_data'][0]->stock;
	$accessories = (!empty($data['inventory_data'][0]->combo_accessories))?$data['inventory_data'][0]->combo_accessories:$data['inventory_data'][0]->accessories;
	$test_it = (!empty($data['inventory_data'][0]->combo_test_it))?$data['inventory_data'][0]->combo_test_it:$data['inventory_data'][0]->test_it;
	
	return sprintf($tempalte_str, $locator_number, /*substr($data['createdOn'], 0, 10)*/ date("Y-m-d"), $test_it, $weight.'g',
		       $week_sale, $data['quantity'], $stock, $sku, $accessories, $sku, $bom);
    }
    
    public function generateBulkProcessCard(){
        $data = $this->getReadyPackSkuByBulk();
	$t = '
	<html>
	    <head>
		<title>Process Card</title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	    <style>
		table{
		    border-collapse:collapse;
		    width: 100%;
		    font-size: 12px;
		}
	    </style>
	    </head>
	    <body>';
	$i = 1;
	foreach($data as $d){
	    $t .= $this->template($d)."<br/>";
	    if($i % 7 == 0){
		$t .= "<br/>";
	    }
	    $i++;
	}
	
	$t .= '
	    </body>
	</html>';
	file_put_contents(self::FILE_PATH.date("Ym")."/".date("d").'/process-card(bulk).html', $t);
    }
    
    public function generateNoBulkProcessCard(){
        $data = $this->getReadyPackSkuByNoBulk();
	$t = '
	<html>
	    <head>
		<title>Process Card</title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	    <style>
		table{
		    border-collapse:collapse;
		    width: 100%;
		    font-size: 12px;
		}
	    </style>
	    </head>
	    <body>';
	$i = 1;
	foreach($data as $d){
	    $t .= $this->template($d)."<br/>";
	    if($i % 7 == 0){
		$t .= "<br/>";
	    }
	    $i++;
	}
	$t .= '
	    </body>
	</html>';
	file_put_contents(self::FILE_PATH.date("Ym")."/".date("d").'/process-card(non-bulk).html', $t);
    }
    
    public function generateSkuProcessCard(){
        $data = $this->getReadyPackSku();
	foreach($data as $k=>$v){
	    $t = '
	    <html>
		<head>
		    <title>Process Card</title>
		    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<style>
		    table{
			border-collapse:collapse;
			width: 100%;
			font-size: 12px;
		    }
		</style>
		</head>
		<body>';
	    $i = 1;
	    foreach($v as $d){
		$t .= $this->template($d)."<br/>";
		if($i % 7 == 0){
		    $t .= "<br/>";
		}
		$i++;
	    }
	    
	    $t .= '
		</body>
	    </html>';
	    file_put_contents(self::FILE_PATH.date("Ym")."/".date("d").'/process-card('.$k.').html', $t);
	}
    }
    
}

$pc = new ProcessCard();
//$pc->setStartTime("2009-06-03");
//$pc->setEndTime("2009-06-04");

$pc->generateBulkProcessCard();
$pc->generateNoBulkProcessCard();

$pc->generateSkuProcessCard();
?>