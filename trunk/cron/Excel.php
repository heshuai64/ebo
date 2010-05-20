<?php
require_once '/export/eBayBO/class/PHPExcel.php';
require_once '/export/eBayBO/class/PHPExcel/IOFactory.php';

class eBayBOExcel{
	private static $database_connect;
	const DATABASE_HOST = 'localhost';
	const DATABASE_USER = 'root';
	const DATABASE_PASSWORD = '5333533';
	const DATABASE_NAME = 'ebaybo';
	const FILE_PATH = '/export/eBayBO/excel/';
	const INVENTORY_SERVICE = 'http://192.168.1.169:8080/inventory/service.php';
	private static $php_excel;
	private $startTime;
	private $endTime;
	
	public function __construct(){
		eBayBOExcel::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

		if (!eBayBOExcel::$database_connect) {
		    echo "Unable to connect to DB: " . mysql_error(eBayBOExcel::$database_connect);
		    exit;
		}
		
		mysql_query("SET NAMES 'UTF8'", eBayBOExcel::$database_connect);
		
		if (!mysql_select_db(self::DATABASE_NAME, eBayBOExcel::$database_connect)) {
		    echo "Unable to select mydbname: " . mysql_error(eBayBOExcel::$database_connect);
		    exit;
		}
		
		$this->php_excel = new PHPExcel();

		//$this->startTime = date("Y-m-d 14:10:00",mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
		$this->startTime = date("Y-m-d 09:00:00",mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
		$this->endTime   = date("Y-m-d 09:00:00");
		
	}
	
	public function setStartTime($startTime){
		$this->startTime = $startTime;
	}
	    
	public function setEndTime($endTime){
		$this->endTime = $endTime;
	}
    
	private function getService($request){
        
		//$request =  'http://search.yahooapis.com/ImageSearchService/V1/imageSearch?appid=YahooDemo&query='.urlencode('Al Gore').'&results=1';
		
		// Make the request
		$json = file_get_contents($request);
		//var_dump($json);
		//echo $request;
		// Retrieve HTTP status code
		list($version,$status_code,$msg) = explode(' ',$http_response_header[0], 3);
		
		// Check the HTTP Status code
		switch($status_code) {
			case 200:
				return $json;
				// Success
				break;
			case 503:
				echo('Your call to Web Services failed and returned an HTTP status of 503. That means: Service unavailable. An internal problem prevented us from returning data to you.');
				break;
			case 403:
				echo('Your call to Web Services failed and returned an HTTP status of 403. That means: Forbidden. You do not have permission to access this resource, or are over your rate limit.');
				break;
			case 400:
				// You may want to fall through here and read the specific XML error
				echo('Your call to Web Services failed and returned an HTTP status of 400. That means:  Bad request. The parameters passed to the service did not match as expected. The exact error is returned in the JSON response.');
				break;
			default:
				echo('Your call to Web Services returned an unexpected HTTP status of:' . $status_code);
				return false;
		}
	    }
    
	private function getFilePath($fileName){
		if(!file_exists(self::FILE_PATH.date("Ym"))){
		    mkdir(self::FILE_PATH.date("Ym"), 0777);
		}
		
		if(!file_exists(self::FILE_PATH.date("Ym")."/".date("d"))){
		    mkdir(self::FILE_PATH.date("Ym")."/".date("d"), 0777);
		}
		
		$fileName = self::FILE_PATH.date("Ym")."/".date("d").'/'.$fileName;
		return $fileName;
	}
	
	private function getShipmentMethod($method){
		switch($method){
			case "B":
				return "Bulk";
			break;
		
			case "S":
				return "SpeedPost";
			break;
		
			case "R":
				return "Registered";
			break;
		
			case "U":
				return "UPS";
			break;
		}
	}
	
	private function getShipmentReason($reason){
		switch($reason){
			case "L":
				return "Lost in transit";
			break;
		
			case "D":
				return "Damaged item returned";
			break;
		
			case "F":
				return "Defective item returned";
			break;
		
			case "W":
				return "Wrong item returned";
			break;
		
			case "N":
				return "Normal item returned";
			break;
		
			case "B":
				return "Bounced back";
			break;
		
			case "M":
				return "Missing item";
			break;
		}
	}
	
	public function shipmentList(){
		$start = $this->startTime;
		$end = $this->endTime;
		
		$sql = "select s.id,o.buyerId,s.shipmentMethod,s.ordersId from qo_shipments as s left join qo_orders as o on s.ordersId = o.id 
		where s.modifiedOn between '".$start."' and '".$end."' and s.status = 'N'";
		$result = mysql_query($sql, eBayBOExcel::$database_connect);
		
		$this->php_excel->setActiveSheetIndex(0);
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'No');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'Shipment Id');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'eBay Id');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, 'Shipping Method');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'Sku');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, 'Item Model');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(6, 1, 'Quantity');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(7, 1, 'Stock');
		
		$i = 2;
		while($row = mysql_fetch_assoc($result)){
			$j = 0;
			$sql_1 = "select skuId,skuTitle,quantity from qo_shipments_detail where shipmentsId = '".$row['id']."'";
			//$sql_1 = "select skuId,skuTitle,quantity from qo_orders_detail where ordersId = '".$row['ordersId']."'";
			$result_1 = mysql_query($sql_1, eBayBOExcel::$database_connect);
			$sku = '';
			$skuTitle = '';
			$quantity = '';
			while($row_1 = mysql_fetch_assoc($result_1)){
				$sku .= $row_1['skuId'] . ', ';
				$skuTitle .= $row_1['skuTitle'] . ', ';
				$quantity .= $row_1['quantity'] . ', ';
			}
			$sku = substr($sku, 0, -2);
			$skuTitle = substr($skuTitle, 0, -2);
			$quantity = substr($quantity, 0, -2);
			
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $i-1);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['id']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['buyerId']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $this->getShipmentMethod($row['shipmentMethod']));
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $sku);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $skuTitle);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $quantity);
			$request = eBayBOExcel::INVENTORY_SERVICE."?action=getSkuInfo&data=".urlencode($sku);
                        $json_result = json_decode($this->getService($request));
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $json_result->skuStock);
			$i++;
		}
		$writer = PHPExcel_IOFactory::createWriter($this->php_excel, 'Excel5');
		$writer->save($this->getFilePath('address-list.xls'));
	} 
	
	public function reSentShipment(){
		if(!empty($_GET['start']) && !empty($_GET['end'])){
			$start = $_GET['start'];
			$end = $_GET['end'];
		}else{
			echo "<font color='red'>Please input start and end parameter in browser address url.</font>";
			return 0;
		}
		$this->php_excel->setActiveSheetIndex(0);
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'No');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'Account');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Shipment Id');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, 'Resend Reason');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'Country');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, 'Shipping Method');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(6, 1, 'Sku');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(7, 1, 'Quantity');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(8, 1, 'Created Date');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(9, 1, 'Resent Date');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(10, 1, 'Cost');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(11, 1, 'Weight(KG)');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(12, 1, 'Postage');
		
		$sql = "select s.id,o.sellerId,s.ordersId,s.shipmentReason,s.shipmentMethod,s.createdOn,s.modifiedOn,s.shipToCountry from qo_shipments as s left join qo_orders as o on s.ordersId = o.id where s.shipmentReason <> '' and s.shipmentReason <> '1' and s.modifiedOn between '".$start."' and '".$end."'";
		$result = mysql_query($sql, eBayBOExcel::$database_connect);
		$i = 2;
		while($row = mysql_fetch_assoc($result)){
			$sql_2 = "select createdOn from qo_shipments where status = 'S' and ordersId = '".$row['ordersId']."' order by createdOn";
			$result_2 = mysql_query($sql_2, eBayBOExcel::$database_connect);
			$row_2 = mysql_fetch_assoc($result_2);
			$createdOn = $row_2['createdOn'];
			
			$j = 0;
			$sql_1 = "select skuId,quantity from qo_shipments_detail where shipmentsId = '".$row['id']."'";
			//$sql_1 = "select od.skuId,od.skuCost,od.skuWeight,od.quantity from qo_orders as o left join qo_orders_detail as od on o.id = od.ordersId where o.id = '".$row['ordersId']."'";
			$result_1 = mysql_query($sql_1, eBayBOExcel::$database_connect);
			$sku = '';
			$cost = 0;
			$weight = 0;
			$quantity = 0;
			while($row_1 = mysql_fetch_assoc($result_1)){
				$json_result = $this->getService(self::INVENTORY_SERVICE."?action=getSkuInfo&data=".urlencode($row_1['skuId']));
				//echo $json_result;
				$service_result = json_decode($json_result);
				$sku .= $row_1['skuId'] . ', ';
				$cost += $service_result->skuCost;
				$weight += $service_result->skuWeight;
				$quantity += $row_1['quantity'];
			}
			$sku = substr($sku, 0, -2);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $i-1);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['sellerId']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['id']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $this->getShipmentReason($row['shipmentReason']));
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['shipToCountry']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $this->getShipmentMethod($row['shipmentMethod']));
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $sku);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $quantity);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $createdOn);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['modifiedOn']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $cost);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $weight);
			$i++;
		}
		$writer = PHPExcel_IOFactory::createWriter($this->php_excel, 'Excel5');
		$writer->save('/export/eBayBO/excel/resent-list('.$start.' -- '.$end.').xls');
		echo "From ".$start." to ". $end." resend shipment generate Success!<br><a href='http://heshuai64.3322.org:8888/eBayBO/excel/resent-list(".$start." -- ".$end.").xls'>please click download</a>";
	}
	
	public function registerShipment(){
		$start = $this->startTime;
		$end = $this->endTime;
		
		$this->php_excel->setActiveSheetIndex(0);
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, '序号');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, '参考号');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, '外包装件数');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, '重量');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, '中转渠道');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, '寄件人公司或人名');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(6, 1, '寄件人地址');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(7, 1, '收件人邮箱');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(8, 1, '收件人电话');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(9, 1, '收件人公司名');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(10, 1, '收件人姓名');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(11, 1, '收件人地址');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(12, 1, '到达国家');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(13, 1, '申报品名1');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(14, 1, '数量1');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(15, 1, '币种');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(16, 1, '报价1');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(17, 1, '申报品名2');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(18, 1, '数量2');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(19, 1, '报价2');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(20, 1, '总值');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(21, 1, '是否保险');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(22, 1, '自定义配货信息1');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(23, 1, '自定义配货信息2');
		//$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(24, 1, 'Shipment ID');
		//$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(25, 1, 'Shipment URL');
		
		$sql = "select id,shipToName,shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,
		shipToStateOrProvince,shipToPostalCode,shipToCountry,shipToPhoneNo from qo_shipments where status = 'N' and modifiedOn between '".$start."' and '".$end."' and shipmentMethod = 'R' ";
		$result = mysql_query($sql, eBayBOExcel::$database_connect);
		$i = 2;
		while($row = mysql_fetch_assoc($result)){
			$sql_1 = "select countries_iso_code_2 from qo_countries where countries_name = '".$row['shipToCountry']."'";
			$result_1 = mysql_query($sql_1, eBayBOExcel::$database_connect);
			$row_1 = mysql_fetch_assoc($result_1);
			
			$address = $row['shipToAddressLine1']."\n".
			(!empty($row['shipToAddressLine2'])?$row['shipToAddressLine2']."\n":'').
			(!empty($row['shipToCity'])?$row['shipToCity']."\n":'').
			(!empty($row['shipToStateOrProvince'])?$row['shipToStateOrProvince']."\n":'').
			(!empty($row['shipToPostalCode'])?$row['shipToPostalCode']:'');
			
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(0, $i, $i-1);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(1, $i, $row['id']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(4, $i, '新加坡小包挂号');
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(5, $i, 'Richart');
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(8, $i, (($row['shipToPhoneNo'] != 'Invalid Request')?$row['shipToPhoneNo']:''));
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(10, $i, $row['shipToName']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(11, $i, $address);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(12, $i, $row_1['countries_iso_code_2']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(13, $i, 'Computer Parts');
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(14, $i, 1);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(15, $i, 'USD');
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(16, $i, 30);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(20, $i, 30);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(21, $i, 'N');
			//$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(25, $i, "http://heshuai64.3322.org/eBayBO/cron/image.php?code=code39&o=1&t=30&r=1&text=".$row['id']."&f1=Arial.ttf&f2=8&a1=&a2=&a3=");
			$i++;
		}
		$writer = PHPExcel_IOFactory::createWriter($this->php_excel, 'Excel5');
		$writer->save($this->getFilePath('register.xls'));
	}
	
	public function __destruct(){
		mysql_close(eBayBOExcel::$database_connect);
	}
}

$action = (empty($_GET['action'])?$argv[1]:$_GET['action']);
$excel = new eBayBOExcel();
if(!empty($_GET)){
    $excel->setStartTime($_GET['start']);
    $excel->setEndTime($_GET['end']);
}elseif(!empty($argv[2]) && !empty($argv[3])){
    $excel->setStartTime($argv[2]);
    $excel->setEndTime($argv[3]);
}
$excel->$action();

?>