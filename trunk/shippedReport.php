<?php
define ('__DOCROOT__', '/export/eBayBO');
//define ('__DOCROOT__', './');
define ('__DOCCLASS__', __DOCROOT__ . '/class');

require_once __DOCCLASS__ . '/PHPExcel.php';
require_once __DOCCLASS__ . '/PHPExcel/IOFactory.php';

$config = parse_ini_file(__DOCROOT__ . '/config.ini', true);
$database_connect = mysql_connect($config['database']['host'], $config['database']['user'], $config['database']['password']);

if (!$database_connect) {
    echo "Unable to connect to DB: " . mysql_error($database_connect);
    exit;
}

mysql_query("SET NAMES 'UTF8'", $database_connect);

if (!mysql_select_db($config['database']['name'], $database_connect)) {
    echo "Unable to select mydbname: " . mysql_error($database_connect);
    exit;
}

header("Content-Type: application/force-download");     
header("Content-Type: application/octet-stream");     
header("Content-Type: application/download");     
header('Content-Disposition:inline;filename="shipped-report.xls"');     
header("Content-Transfer-Encoding: binary");     
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");     
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");     
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");     
header("Pragma: no-cache");
        
$sku_array = array();
$excel = new PHPExcel();
$excel->setActiveSheetIndex(0);
$excel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'Shipment ID');
$excel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'Created On');
$excel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'SKU');
$excel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, 'Quantity');
$excel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'Shipped On');
$excel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, 'Shipped By');
$j = 2;

$sql = "select s.id,s.createdOn,s.shippedOn,s.shippedBy,sd.skuId,sd.quantity 
from qo_shipments as s, qo_shipments_detail as sd where s.id = sd.shipmentsId and 
shippedOn between '".$_GET['start']."' and '".$_GET['end']."'";
//echo $sql."<br>";
$result = mysql_query($sql);
while($row = mysql_fetch_assoc($result)){
    $sku_array[$row['skuId']]['quantity'] += $row['quantity'];
    $i = 0;
    $excel->getActiveSheet()->setCellValueByColumnAndRow($i++, $j, $row['id']);
    $excel->getActiveSheet()->setCellValueByColumnAndRow($i++, $j, $row['createdOn']);
    $excel->getActiveSheet()->setCellValueByColumnAndRow($i++, $j, $row['skuId']);
    $excel->getActiveSheet()->setCellValueByColumnAndRow($i++, $j, $row['quantity']);
    $excel->getActiveSheet()->setCellValueByColumnAndRow($i++, $j, $row['shippedOn']);
    $excel->getActiveSheet()->setCellValueByColumnAndRow($i++, $j, $row['shippedBy']);
    $j++;
}

//$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
//$writer->save('php://output');
//exit;

//---------------------------------------------------------------------------------
foreach($sku_array as $k=>$v){
    $inventory_data = json_decode(file_get_contents($config['service']['inventory'].'?action=getSkuInfo&data='.$k));
    $sku_array[$k]['chinese_title'] = $inventory_data->skuChineseTitle;
    $sku_array[$k]['warehouse_location'] = $inventory_data->locatorNumber;
    $sku_stock = json_decode(file_get_contents($config['service']['inventory'].'?action=getSkuStockFromRemote&sku='.$k));
    $sku_array[$k]['stock'] = $sku_stock->R;
}

$sheet = $excel->createSheet();
$sheet->setCellValueByColumnAndRow(0, 1, 'SKU');
$sheet->setCellValueByColumnAndRow(1, 1, 'Product Name');
$sheet->setCellValueByColumnAndRow(2, 1, 'Warehouse Location');
$sheet->setCellValueByColumnAndRow(3, 1, 'Quantity');
$sheet->setCellValueByColumnAndRow(4, 1, 'current Stock');

$j = 2;
foreach($sku_array as $k=>$v){
    $i = 0;
    $sheet->setCellValueByColumnAndRow($i++, $j, $k);
    $sheet->setCellValueByColumnAndRow($i++, $j, $v['chinese_title']);
    $sheet->setCellValueByColumnAndRow($i++, $j, $v['warehouse_location']);
    $sheet->setCellValueByColumnAndRow($i++, $j, $v['quantity']);
    $sheet->setCellValueByColumnAndRow($i++, $j, $v['stock']);
    $j++;
}
$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');

?>