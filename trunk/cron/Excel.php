<?php
require_once '../class/PHPExcel.php';
require_once '../class/PHPExcel/IOFactory.php';

class eBayBOExcel{
	private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaybo';
    private static $php_excel;
    
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
	}
	
	public function shipmentList(){
		$start = '2009-01-12';
		$end = '2009-09-13';
		$sql = "select s.id,o.buyerId,s.shipmentMethod from qo_shipments as s left join qo_orders as o on s.ordersId = o.id 
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
		
		$i = 2;
		while($row = mysql_fetch_assoc($result)){
			$j = 0;
			$sql_1 = "select skuId,skuTitle,quantity from qo_shipments_detail where shipmentsId = '".$row['id']."'";
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
			
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $i);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['id']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['buyerId']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['shipmentMethod']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $sku);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $skuTitle);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $quantity);
			$i++;
		}
		$writer = PHPExcel_IOFactory::createWriter($this->php_excel, 'Excel5');
		$writer->save('address-list.xls');
	} 
	
	public function reSentShipment(){
		$start = '2009-01-12';
		$end = '2009-09-13';
		
		$this->php_excel->setActiveSheetIndex(0);
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'No');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'Shipment Id');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Resend Reason');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, 'Country');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'Shipping Method');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, 'Sku');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(6, 1, 'Resend Date');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(7, 1, 'Cost');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(8, 1, 'Weight(KG)');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(9, 1, 'Postage');
		
		$sql = "select id,ordersId,shipmentReason,shipmentMethod,modifiedOn,shipToCountry from qo_shipments where shipmentReason <> '' and shipmentReason <> '1' and modifiedOn between '".$start."' and '".$end."'";
		$result = mysql_query($sql, eBayBOExcel::$database_connect);
		$i = 2;
		while($row = mysql_fetch_assoc($result)){
			$j = 0;
			$sql_1 = "select od.skuId,od.skuCost,od.skuWeight from qo_orders as o left join qo_orders_detail on o.id = od.ordersId where o.id = '".$row['ordersId']."'";
			$result_1 = mysql_query($sql_1, eBayBOExcel::$database_connect);
			$sku = '';
			$cost = 0;
			$weight = 0;
			while($row_1 = mysql_fetch_assoc($result_1)){
				$sku = $row_1['skuId'];
				$cost += $row_1['skuCost'];
				$weight += $row_1['skuWeight'];
			}
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $i);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['id']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['shipmentReason']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['shipToCountry']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['shipmentMethod']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $sku);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['modifiedOn']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $cost);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $weight);
		}
		$writer = PHPExcel_IOFactory::createWriter($this->php_excel, 'Excel5');
		$writer->save('resent-list.xls');
	}
	
	public function registerShipment(){
		$start = '2009-01-12';
		$end = '2009-09-13';
		
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
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(24, 1, 'Shipment ID');
		$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(25, 1, 'Shipment URL');
		
		$sql = "select id,shipToName,shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,
		shipToStateOrProvince,shipToPostalCode,shipToCountry from qo_shipments";// where shipmentMethod = 'R' and modifiedOn between '".$start."' and '".$end."'";
		$result = mysql_query($sql, eBayBOExcel::$database_connect);
		$i = 2;
		while($row = mysql_fetch_assoc($result)){
			$sql_1 = "select countries_iso_code_2 from qo_countries where countries_name = '".$row['shipToCountry']."'";
			$result_1 = mysql_query($sql_1, eBayBOExcel::$database_connect);
			$row_1 = mysql_fetch_assoc($result_1);
			
			$address = $row['shipToAddressLine1']."\n".
			(!empty($row['shipToAddressLine2'])?$row['shipToAddressLine2']."\n":'').
			$row['shipToCity']."\n".
			$row['shipToStateOrProvince']."\n".
			$row['shipToPostalCode'];
			
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(0, $i, $i);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(10, $i, $row['shipToName']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(11, $i, $address);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(12, $i, $row_1['countries_iso_code_2']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(24, $i, $row['id']);
			$this->php_excel->getActiveSheet()->setCellValueByColumnAndRow(25, $i, "http://heshuai64.3322.org/eBayBO/cron/image.php?code=code39&o=1&t=30&r=1&text=".$row['id']."&f1=Arial.ttf&f2=8&a1=&a2=&a3=");
			$i++;
		}
		$writer = PHPExcel_IOFactory::createWriter($this->php_excel, 'Excel5');
		$writer->save('register.xls');
	}
	
	public function __destruct(){
		mysql_close(eBayBOExcel::$database_connect);
	}
}

$action = (empty($_GET['action'])?$argv[1]:$_GET['action']);
$excel = new eBayBOExcel();
$excel->$action();

?>