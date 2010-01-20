<?php
class Item{
    private $account_id;
    
    public function __construct($account_id){
        $this->account_id = $account_id;
    }
    
    private function getSiteTime($site, $date, $time, $num = 0, $interval = 0){
	switch($site){
	    case "US":
		$time = date("Y-m-d H:i:s", strtotime("+12 hour ".$date.' '.$time) + ($num * $interval * 60));
	    break;
	
	    case "UK":
		$time = date("Y-m-d H:i:s", strtotime("+7 hour ".$date.' '.$time) + ($num * $interval * 60));
	    break;
	
	    case "Germany":
		$time = date("Y-m-d H:i:s", strtotime("+7 hour ".$date.' '.$time) + ($num * $interval * 60));
	    break;
	
	    case "Australia":
		$time = date("Y-m-d H:i:s", strtotime("-3 hour ".$date.' '.$time) + ($num * $interval * 60));
	    break;
	
	    case "France":
		$time = date("Y-m-d H:i:s", strtotime("+6 hour ".$date.' '.$time) + ($num * $interval * 60));
	    break;
	}
	return $time;
    }
    
    private function log($type, $content, $level = 'normal'){
	//print_r($_COOKIE);
	$sql = "insert into log (level,type,content,account_id) values('".$level."','".$type."','".mysql_real_escape_string($content)."','".$this->account_id."')";
	//echo $sql;
	$result = mysql_query($sql, eBayListing::$database_connect);
    }
    
    //------------------------------------------------------------------------------------------------------------
    public function activeItemExport(){
        /*
	$data = "SKU,Item Title,Insertion Fee,Item ID,Start Time,End Time,Duration,Qty,Slod Qty,Price,Listing Type\n";
	$sql = "select ItemID,SKU,Title,ListingType,InsertionFee,ListingFee,Quantity,QuantitySold,ListingDuration,StartTime,EndTime,StartPrice,BuyItNowPrice from items where Status = 2 and accountId = '".$this->account_id."'";
	//echo $sql_1."\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
	    $data .= '"'.$row['SKU'].'","'.mysql_escape_string(str_replace("\\", "", $row['Title'])).'","'.$row['InsertionFee'].'","'.$row['ItemID'].'","'.$row['StartTime'].'","'.$row['EndTime'].'","'.$row['ListingDuration'].'","'.$row['Quantity'].'","'.$row['QuantitySold'].'","'.$row['StartPrice'].'","'.$row['ListingType'].'"'."\n";
	}
	header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=activeItem.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $data;
        */
        set_time_limit(600);
	require_once './Classes/PHPExcel.php';
	require_once './Classes/PHPExcel/IOFactory.php';
        
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $objExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'Item ID');
	$objExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'SKU');
	$objExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Item Title');
        $objExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, 'Item Description');
	$objExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'Insertion Fee');
	$objExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, 'Start Time');
        $objExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 1, 'End Time');
        $objExcel->getActiveSheet()->setCellValueByColumnAndRow(7, 1, 'Duration');
        $objExcel->getActiveSheet()->setCellValueByColumnAndRow(8, 1, 'Qty');
        $objExcel->getActiveSheet()->setCellValueByColumnAndRow(9, 1, 'Slod Qty');
        $objExcel->getActiveSheet()->setCellValueByColumnAndRow(10, 1, 'Price');
        $objExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 1, 'Listing Type');
        
        $sql = "select ItemID,SKU,Title,Description,ListingType,InsertionFee,ListingFee,Quantity,QuantitySold,ListingDuration,StartTime,EndTime,StartPrice,BuyItNowPrice from items where Status = 2 and accountId = '".$this->account_id."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
        $i = 2;
	while($row = mysql_fetch_assoc($result)){
	    $j = 0;
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['ItemID']);
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['SKU']);
	    $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['Title']);
	    $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, html_entity_decode($row['Description'], ENT_QUOTES));
	    $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['InsertionFee']);
	    $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['StartTime']);
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['EndTime']);
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['ListingDuration']);
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['Quantity']);
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['QuantitySold']);
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['StartPrice']);
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['ListingType']);
	    $i++;
	}
        $outputFileName = "output.xls";
	header("Content-Type: application/force-download");     
	header("Content-Type: application/octet-stream");     
	header("Content-Type: application/download");     
	header('Content-Disposition:inline;filename="'.$outputFileName.'"');     
	header("Content-Transfer-Encoding: binary");     
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");     
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");     
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");     
	header("Pragma: no-cache");     
    
	$writer = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
	$writer->save('php://output');	//echo $data;
    }
    
    public function activeItemImport(){
        set_time_limit(600);
	require_once './Classes/PHPExcel.php';
	require_once './Classes/PHPExcel/IOFactory.php';
        
        $objPHPExcel = PHPExcel_IOFactory::load($_FILES['alexcel']['tmp_name']);
        //$objPHPExcel = $objReader->load($_FILES['alexcel']['tmp_name']);
	$objWorksheet = $objPHPExcel->getActiveSheet();
        foreach ($objWorksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ($cellIterator as $cell) {
                echo $cell->getValue();
            }
        }
    }
    
    public function soldItemExport(){
	$data = "SKU,Item Title,Insertion Fee,Item ID,Start Time,End Time,Duration,Qty,Slod Qty,Price,Listing Type\n";
	$sql = "select ItemID,SKU,Title,ListingType,sum(InsertionFee) as InsertionFee,sum(ListingFee) as ListingFee,sum(Quantity) as Quantity,sum(QuantitySold) as QuantitySold,ListingDuration,StartTime,EndTime,sum(StartPrice) as StartPrice,sum(BuyItNowPrice) as BuyItNowPrice from items where Status = 6 and StartTime > '".$_GET['StartTime']."' and EndTime < '".$_GET['EndTime']."' and accountId = '".$this->account_id."' group by Title";
	//echo $sql."\n";
	//exit;
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
	    $data .= '"'.$row['SKU'].'","'.mysql_escape_string(str_replace("\\", "", $row['Title'])).'","'.$row['InsertionFee'].'","'.$row['ItemID'].'","'.$row['StartTime'].'","'.$row['EndTime'].'","'.$row['ListingDuration'].'","'.$row['Quantity'].'","'.$row['QuantitySold'].'","'.$row['StartPrice'].'","'.$row['ListingType'].'"'."\n";
	}
	header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=soldItem(".$_GET['StartTime']."--".$_GET['EndTime'].").csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $data;
    }
    
    public function unSoldItemExport(){
	$data = "SKU,Item Title,Insertion Fee,Item ID,Start Time,End Time,Duration,Qty,Price,Listing Type\n";
	$sql = "select ItemID,SKU,Title,ListingType,sum(InsertionFee) as InsertionFee,sum(ListingFee) as ListingFee,sum(Quantity) as Quantity,ListingDuration,StartTime,EndTime,sum(StartPrice) as StartPrice,sum(BuyItNowPrice) as BuyItNowPrice from items where Status = 5 and StartTime > '".$_GET['StartTime']."' and EndTime < '".$_GET['EndTime']."' and accountId = '".$this->account_id."' group by Title";
	//echo $sql."\n";
	//exit;
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
	    $data .= '"'.$row['SKU'].'","'.mysql_escape_string(str_replace("\\", "", $row['Title'])).'","'.$row['InsertionFee'].'","'.$row['ItemID'].'","'.$row['StartTime'].'","'.$row['EndTime'].'","'.$row['ListingDuration'].'","'.$row['Quantity'].'","'.$row['StartPrice'].'","'.$row['ListingType'].'"'."\n";
	}
	header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=unSoldItem(".$_GET['StartTime']."--".$_GET['EndTime'].").csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $data;
    }
    
    public function getWaitingUploadItem(){
	$array = array();
	
	if(empty($_POST)){
	    $sql = "select count(*) as count from items where accountId = '".$this->account_id."' and Status = 0";
	    $result = mysql_query($sql, eBayListing::$database_connect);
            $row = mysql_fetch_assoc($result);
            $totalCount = $row['count'];
	    
	    if(empty($_POST['start']) && empty($_POST['limit'])){
                $_POST['start'] = 0;
                $_POST['limit'] = 20;
            }
	    
	    $sql = "select Id,TemplateID,SKU,Title,BuyItNowPrice,ListingDuration,ListingType,Quantity,StartPrice,ScheduleTime,ScheduleLocalTime,Site from items where accountId = '".$this->account_id."' and Status = 0 order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
            $result = mysql_query($sql, eBayListing::$database_connect);
            
	}else{
	    $where = " where accountId = '".$this->account_id."' and Status = 0 ";
		
	    if(empty($_POST['start']) && empty($_POST['limit'])){
                $_POST['start'] = 0;
                $_POST['limit'] = 20;
            }
	    
            if(!empty($_POST['TID'])){
                $where .= " and TemplateID = '".$_POST['TID']."'";
            }
            
            if(!empty($_POST['SKU'])){
                $where .= " and SKU like '%".mysql_real_escape_string($_POST['SKU'])."%'";
            }
            
            if(!empty($_POST['Title'])){
                $where .= " and Title like '%".html_entity_decode($_POST['Title'], ENT_QUOTES)."%'";
            }
                
            if(!empty($_POST['ListingDuration'])){
                $where .= " and ListingDuration = '".$_POST['ListingDuration']."'";
            }
                
            $sql = "select count(*) as count from items ".$where;
            $result = mysql_query($sql, eBayListing::$database_connect);
            $row = mysql_fetch_assoc($result);
            $totalCount = $row['count'];
            
            $sql = "select Id,TemplateID,SKU,Title,BuyItNowPrice,ListingDuration,ListingType,Quantity,StartPrice,ScheduleTime,ScheduleLocalTime,Site from items ".$where." order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
            //echo $sql;
            $result = mysql_query($sql, eBayListing::$database_connect);
	}
	
	//echo $sql;
	
	while($row = mysql_fetch_assoc($result)){
	    if($row['ListingType'] == "FixedPriceItem" || $row['ListingType'] == "StoresFixedPrice"){
		$row['Price'] = $row['StartPrice'];
	    }else{
		$row['Price'] = $row['BuyItNowPrice'];
	    }
	    $sql_1 = "select ShippingServiceCost from international_shipping_service_option where ItemID = '".$row['Id']."' order by ShippingServicePriority";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $row_1 = mysql_fetch_assoc($result_1);
	    $row['ShippingFee'] = $row_1['ShippingServiceCost'];
	    $array[] = $row;
	}
	
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$array));
	mysql_free_result($result);
    }
    
    public function geScheduleItem(){
	$array = array();
	
	if(empty($_POST)){
	    $sql = "select count(*) as count from items where accountId = '".$this->account_id."' and Status = 1";
	    $result = mysql_query($sql, eBayListing::$database_connect);
            $row = mysql_fetch_assoc($result);
            $totalCount = $row['count'];
	    
	    if(empty($_POST['start']) && empty($_POST['limit'])){
                $_POST['start'] = 0;
                $_POST['limit'] = 20;
            }
	    
	    $sql = "select Id,TemplateID,SKU,Title,BuyItNowPrice,ListingDuration,ListingType,Quantity,StartPrice,ScheduleTime,ScheduleLocalTime,Site from items where accountId = '".$this->account_id."' and Status = 1 order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
            $result = mysql_query($sql, eBayListing::$database_connect);
            
	}else{
	    $where = " where accountId = '".$this->account_id."' and Status = 1 ";
		
	    if(empty($_POST['start']) && empty($_POST['limit'])){
                $_POST['start'] = 0;
                $_POST['limit'] = 20;
            }
	    
            if(!empty($_POST['TID'])){
                $where .= " and TemplateID = '".$_POST['TID']."'";
            }
            
            if(!empty($_POST['SKU'])){
                $where .= " and SKU like '%".mysql_real_escape_string($_POST['SKU'])."%'";
            }
            
            if(!empty($_POST['Title'])){
                $where .= " and Title like '%".html_entity_decode($_POST['Title'], ENT_QUOTES)."%'";
            }
                
            if(!empty($_POST['ListingDuration'])){
                $where .= " and ListingDuration = '".$_POST['ListingDuration']."'";
            }
            
            $sql = "select count(*) as count from items ".$where;
            $result = mysql_query($sql, eBayListing::$database_connect);
            $row = mysql_fetch_assoc($result);
            $totalCount = $row['count'];
            
            $sql = "select Id,TemplateID,SKU,Title,BuyItNowPrice,ListingDuration,ListingType,Quantity,StartPrice,ScheduleTime,ScheduleLocalTime,Site from items ".$where." order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
            //echo $sql;
            $result = mysql_query($sql, eBayListing::$database_connect);
	}
	
	//echo $sql;
	
	while($row = mysql_fetch_assoc($result)){
	    if($row['ListingType'] == "FixedPriceItem" || $row['ListingType'] == "StoresFixedPrice"){
		$row['Price'] = $row['StartPrice'];
	    }else{
		$row['Price'] = $row['BuyItNowPrice'];
	    }
	    $sql_1 = "select ShippingServiceCost from international_shipping_service_option where ItemID = '".$row['Id']."' order by ShippingServicePriority";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $row_1 = mysql_fetch_assoc($result_1);
	    $row['ShippingFee'] = $row_1['ShippingServiceCost'];
	    $array[] = $row;
	}
	
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$array));
	mysql_free_result($result);
    }
    
    public function updateItemUploadTime(){
	$now = date("Y-m-d H:i:s");
	$temp = "";
	$_POST['date'] = substr($_POST['date'], 0, -18);
	$ids = explode(',', $_POST['ids']);
	if(count($ids) > 1){
	    $i = 0;
	    foreach($ids as $id){
		$sql = "select Site from items where Id = '".$id."'";
		$result = mysql_query($sql, eBayListing::$database_connect);
		$row = mysql_fetch_assoc($result);
		
		$localTime = date("Y-m-d H:i:s", strtotime($_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		
		$time = $this->getSiteTime($row['Site'], $_POST['date'], $_POST['time'], $i, $_POST['minute']);
		/*
		switch($row['Site']){
		    case "US":
			$time = date("Y-m-d H:i:s", strtotime("+12 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "UK":
			$time = date("Y-m-d H:i:s", strtotime("+7 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "Germany":
		    	$time = date("Y-m-d H:i:s", strtotime("+7 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "Australia":
			$time = date("Y-m-d H:i:s", strtotime("-3 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "France":
			$time = date("Y-m-d H:i:s", strtotime("+6 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		}
		*/
		
		if($time < $now){
		    echo '[{success: false, msg: "Time error: '.$time.'"}]';
		    return 0;
		}
		$temp .= $id. " : ". $time . "<br>";
		$sql_1 = "update items set ScheduleTime = '".$time."',ScheduleLocalTime='".$localTime."' where Id = '".$id."'";
		$result_2 = mysql_query($sql_1, eBayListing::$database_connect);
		$i++;
	    }
	    //$temp = substr($temp, 0, -2);
	}else{
	    $sql = "select Site from items where Id = '".$_POST['ids']."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    
	    $localTime = date("Y-m-d H:i:s", strtotime($_POST['date'].' '.$_POST['time']));
	    
	    $time = $this->getSiteTime($row['Site'], $_POST['date'], $_POST['time']);
	    /*
	    switch($row['Site']){
		case "US":
		    $time = date("Y-m-d H:i:s", strtotime("+12 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "UK":
		    $time = date("Y-m-d H:i:s", strtotime("+7 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "Germany":
		    $time = date("Y-m-d H:i:s", strtotime("+7 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "Australia":
		    $time = date("Y-m-d H:i:s", strtotime("-3 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "France":
		    $time = date("Y-m-d H:i:s", strtotime("+6 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    }
	    */
	    
	    if($time < $now){
		echo '[{success: false, msg: "Time error: '.$time.'"}]';
		return 0;
	    }
		
	    $temp .= $_POST['ids'] . " : " . $time;
	    $sql_1 = "update items set ScheduleTime = '".$time."',ScheduleLocalTime = '".$localTime."' where Id = '".$_POST['ids']."'";
	    $result_2 = mysql_query($sql_1, eBayListing::$database_connect);
	}
	if($result){
	    echo '[{success: true, msg: "'.$temp.'"}]';
	}else{
	    echo '[{success: false, msg: "Update Upload Time Failure, Please Notice Admin."}]';
	}
    }
    
    public function getActiveItem(){
	if(empty($_POST['start']) && empty($_POST['limit'])){
	       $_POST['start'] = 0;
	       $_POST['limit'] = 20;
	}

	$where = "";
	
	if(!empty($_POST['SKU'])){
	    $where .= " and SKU like '".$_POST['SKU']."%'";
	}
	
	if(!empty($_POST['ItemID'])){
	    $where .= " and ItemID like '".$_POST['ItemID']."%'";
	}
	
	if(!empty($_POST['Title'])){
	    $where .= " and Title like '".html_entity_decode($_POST['Title'], ENT_QUOTES)."%'";
	}
	
	if(!empty($_POST['ListingDuration'])){
	    $where .= " and ListingDuration = '".$_POST['ListingDuration']."'";
	}
	
	//Active, Completed, Ended
	$sql = "select count(*) as count from items where accountId = '".$this->account_id."' ".$where." and Status = 2";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$totalCount = $row['count'];
	
	$sql_1 = "select Id,TemplateID,SKU,ItemID,Title,Site,ListingType,Quantity,ListingDuration,EndTime,StartPrice,BuyItNowPrice from items where accountId = '".$this->account_id."' ".$where." and Status = 2 order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
	//echo $sql_1."\n";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$data = array();
	while($row_1 = mysql_fetch_assoc($result_1)){
	    if($row_1['ListingType'] == "FixedPriceItem" || $row_1['ListingType'] == "StoresFixedPrice"){
		$row_1['Price'] = $row_1['StartPrice'];
	    }else{
		$row_1['Price'] = $row_1['BuyItNowPrice'];
	    }
            $row_1['EndTime'] = date("Y-m-d H:i:s", strtotime($row_1['EndTime']) + (8 * 60 * 60));
	    $data[] = $row_1;
	}
	
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$data));
	mysql_free_result($result);
	mysql_free_result($result_1);
    }
    
    public function getSoldItem(){
	if(empty($_POST['start']) && empty($_POST['limit'])){
	       $_POST['start'] = 0;
	       $_POST['limit'] = 20;
	}

	//Active, Completed, Ended
	$sql = "select count(*) as count from items where accountId = '".$this->account_id."' and Status = 6";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$totalCount = $row['count'];
	
	$sql_1 = "select * from items where accountId = '".$this->account_id."' and Status = 6 order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$data = array();
	while($row_1 = mysql_fetch_assoc($result_1)){
	    if($row_1['ListingType'] == "FixedPriceItem" || $row_1['ListingType'] == "StoresFixedPrice"){
		$row_1['Price'] = $row_1['StartPrice'];
	    }else{
		$row_1['Price'] = $row_1['BuyItNowPrice'];
	    }
            $row_1['EndTime'] = date("Y-m-d H:i:s", strtotime($row_1['EndTime']) + (8 * 60 * 60));
	    $data[] = $row_1;
	}
	
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$data));
	mysql_free_result($result);
	mysql_free_result($result_1);
    }
    
    public function getUnSoldItem(){
	if(empty($_POST['start']) && empty($_POST['limit'])){
	       $_POST['start'] = 0;
	       $_POST['limit'] = 20;
	}

	//Active, Completed, Ended
	$sql = "select count(*) as count from items where accountId = '".$this->account_id."' and Status = 5";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$totalCount = $row['count'];
	
	$sql_1 = "select * from items where accountId = '".$this->account_id."' and Status = 5 order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$data = array();
	while($row_1 = mysql_fetch_assoc($result_1)){
	    if($row_1['ListingType'] == "FixedPriceItem" || $row_1['ListingType'] == "StoresFixedPrice"){
		$row_1['Price'] = $row_1['StartPrice'];
	    }else{
		$row_1['Price'] = $row_1['BuyItNowPrice'];
	    }
            $row_1['EndTime'] = date("Y-m-d H:i:s", strtotime($row_1['EndTime']) + (8 * 60 * 60));
	    $data[] = $row_1;
	}
	
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$data));
	mysql_free_result($result);
	mysql_free_result($result_1);
    }
    
    public function copyItem($id='', $status=''){
	if($_GET['type'] == "wait"){
	    $Status = 0;
	}elseif($_GET['type'] == "schedule"){
	    $Status = 1;
	}elseif($_GET['type'] == "relist"){
            $Status = 6;
        }elseif(!empty($id) && !empty($status)){
            $Status = $status;
            $_POST['ids'] = $id;
        }
	
	if(strpos($_POST['ids'], ',')){
	    $array = explode(',', $_POST['ids']);
	    foreach($array as $a){
		$sql_1 = "insert into items (AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
		Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
		PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,ReturnPolicyRefundOption,ReturnPolicyReturnsWithinOption,ReturnPolicyShippingCostPaidByOption,
		ReservePrice,CurrentPrice,ScheduleTime,ScheduleLocalTime,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
		StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
		Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
		GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,
		InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,UseStandardFooter,Status) 
		select AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
		Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
		PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,ReturnPolicyRefundOption,ReturnPolicyReturnsWithinOption,ReturnPolicyShippingCostPaidByOption,
		ReservePrice,CurrentPrice,ScheduleTime,ScheduleLocalTime,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
		StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
		Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
		GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,
		InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,UseStandardFooter,".$Status." from items where Id = '".$a."'";
		
		//echo $sql_1."\n";
		
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		$item_id = mysql_insert_id(eBayListing::$database_connect);
		
		//var_dump($item_id);
		//exit;
		$sql_2 = "insert into picture_url (ItemID,url)  select '".$item_id."',url from picture_url where ItemID = '".$a."'";
		$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		
		$sql_3 = "insert into shipping_service_options (ItemID,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority) select '".$item_id."',FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority from shipping_service_options where ItemID = '".$a."'";
		$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
		
		$sql_4 = "insert into international_shipping_service_option (ItemID,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) select '".$item_id."',ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation from international_shipping_service_option where ItemID = '".$a."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		
		$sql_5 = "select * from attribute_set where item_id = '".$a."'";
		$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
		while($row_5 = mysql_fetch_assoc($result_5)){
		    $template_attribute_set_id = $row_5['attribute_set_id'];
		    $sql_6 = "insert into attribute_set (item_id,attributeSetID) values ('".$item_id."','".$row_5['attributeSetID']."')";
		    //echo $sql_6."\n";
		    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
		    $attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
		    
		    $sql_7 = "insert into attribute (attributeID,attribute_set_id,ValueID,ValueLiteral) 
		    select attributeID,'".$attribute_set_id."',ValueID,ValueLiteral from attribute 
		    where attribute_set_id = '".$template_attribute_set_id."'";
		    $result_7 = mysql_query($sql_7, eBayListing::$database_connect);
		}
		
		//var_dump(array($result_1, $result_2, $result_3, $result_4, $result_5, $result_6, $result_7));
	    }
	    if($result_1 && $result_2 && $result_3 && $result_4){
                echo 1;
	    }else{
		echo 0;
	    }
	}else{
	    $sql_1 = "insert into items (AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
	    Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
	    PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,ReturnPolicyRefundOption,ReturnPolicyReturnsWithinOption,ReturnPolicyShippingCostPaidByOption,
	    ReservePrice,CurrentPrice,ScheduleTime,ScheduleLocalTime,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
	    StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
	    Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
	    GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,
	    InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,UseStandardFooter,Status) 
	    select AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
	    Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
	    PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,ReturnPolicyRefundOption,ReturnPolicyReturnsWithinOption,ReturnPolicyShippingCostPaidByOption,
	    ReservePrice,CurrentPrice,ScheduleTime,ScheduleLocalTime,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
	    StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
	    Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
	    GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,
	    InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,UseStandardFooter,".$Status." from items where Id = '".$_POST['ids']."'";
	    
	    //echo $sql_1."\n";
	    
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $item_id = mysql_insert_id(eBayListing::$database_connect);
	    
	    //var_dump($item_id);
	    //exit;
	    $sql_2 = "insert into picture_url (ItemID,url)  select '".$item_id."',url from picture_url where ItemID = '".$_POST['ids']."'";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    
	    $sql_3 = "insert into shipping_service_options (ItemID,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority) select '".$item_id."',FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority from shipping_service_options where ItemID = '".$_POST['ids']."'";
	    $result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	    
	    $sql_4 = "insert into international_shipping_service_option (ItemID,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) select '".$item_id."',ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation from international_shipping_service_option where ItemID = '".$_POST['ids']."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	    
	    $sql_5 = "select * from attribute_set where item_id = '".$_POST['ids']."'";
	    $result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	    while($row_5 = mysql_fetch_assoc($result_5)){
		$template_attribute_set_id = $row_5['attribute_set_id'];
		$sql_6 = "insert into attribute_set (item_id,attributeSetID) values ('".$item_id."','".$row_5['attributeSetID']."')";
		//echo $sql_6."\n";
		$result_6 = mysql_query($sql_6, eBayListing::$database_connect);
		$attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
		
		$sql_7 = "insert into attribute (attributeID,attribute_set_id,ValueID,ValueLiteral) 
		select attributeID,'".$attribute_set_id."',ValueID,ValueLiteral from attribute 
		where attribute_set_id = '".$template_attribute_set_id."'";
		$result_7 = mysql_query($sql_7, eBayListing::$database_connect);
	    }
	
	    if($result_1 && $result_2 && $result_3 && $result_4){
                if(empty($id) && empty($status)){
                    echo 1;
                }else{
                    return $item_id;
                }
	    }else{
		echo 0;
	    }
	}
    }
    
    public function waitUploadItemDelete(){
	if(strpos($_POST['ids'], ',')){
	    $ids = explode(',', $_POST['ids']);
	    foreach($ids as $id){
		$sql_1 = "delete from items where Id = '".$id."'";
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		
		$sql_2 = "delete from picture_url where ItemID = '".$id."'";
		$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		
		$sql_3 = "delete from shipping_service_options where ItemID = '".$id."'";
		$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
		
		$sql_4 = "delete from international_shipping_service_option where ItemID = '".$id."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		
		$sql_5 = "select * from attribute_set where item_id = '".$id."'";
		$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
		while($row_5 = mysql_fetch_assoc($result_5)){
		    $sql_6 = "delete from attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
		    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
		}

		$sql_7 = "delete from attribute_set where item_id = '".$id."'";
		$result_7 = mysql_query($sql_7, eBayListing::$database_connect);
	    }
	}else{
	    $id = $_POST['ids'];
	    $sql_1 = "delete from items where Id = '".$id."'";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    
	    $sql_2 = "delete from picture_url where ItemID = '".$id."'";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    
	    $sql_3 = "delete from shipping_service_options where ItemID = '".$id."'";
	    $result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	    
	    $sql_4 = "delete from international_shipping_service_option where ItemID = '".$id."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	    
	    $sql_5 = "select * from attribute_set where item_id = '".$id."'";
	    $result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	    while($row_5 = mysql_fetch_assoc($result_5)){
		$sql_6 = "delete from attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
		$result_6 = mysql_query($sql_6, eBayListing::$database_connect);
	    }

	    $sql_7 = "delete from attribute_set where item_id = '".$id."'";
	    $result_7 = mysql_query($sql_7, eBayListing::$database_connect);
	}
	
	//print_r(array($result_1, $result_2, $result_3, $result_4, $result_5, $result_7));
	
	if($result_1 && $result_2 && $result_3 && $result_4 && $result_5 && $result_7){
	    echo 1;   
	}else{
	    echo 0;
	}
    }
    
    public function addItemToSchedule(){
	if(strpos($_POST['ids'], ',')){
	    $ids = explode(',', $_POST['ids']);
	    foreach($ids as $id){
		$sql = "update items set Status = 1 where Id = ".$id;
		$result = mysql_query($sql, eBayListing::$database_connect);
	    }
	}else{
	    $id = $_POST['ids'];
	    $sql = "update items set Status = 1 where Id = ".$id;
	    $result = mysql_query($sql, eBayListing::$database_connect);
	}
	
	//echo $sql."\n";
	if($result){
	    echo 1;   
	}else{
	    echo 0;
	}
    }
    public function getItem(){
    	session_start();
	$sql = "select * from items where Id = '".$_GET['id']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$row['SiteID'] = $row['Site'];
	$row['Description'] = html_entity_decode($row['Description'], ENT_QUOTES);
	$row['Title'] = html_entity_decode($row['Title'], ENT_QUOTES);
	
	$_SESSION['ReturnPolicyReturns'][$_GET['id']]['ReturnPolicyReturnsAcceptedOption'] = $row['ReturnPolicyReturnsAcceptedOption'];
	$_SESSION['ReturnPolicyReturns'][$_GET['id']]['ReturnPolicyReturnsWithinOption'] = $row['ReturnPolicyReturnsWithinOption'];
	$_SESSION['ReturnPolicyReturns'][$_GET['id']]['ReturnPolicyRefundOption'] = $row['ReturnPolicyRefundOption'];
	$_SESSION['ReturnPolicyReturns'][$_GET['id']]['ReturnPolicyShippingCostPaidByOption'] = $row['ReturnPolicyShippingCostPaidByOption'];
	$_SESSION['ReturnPolicyReturns'][$_GET['id']]['ReturnPolicyDescription'] = $row['ReturnPolicyDescription'];
	
	if($row['ListingType'] == "FixedPriceItem" || $row['ListingType'] == "StoresFixedPrice"){
	    $row['BuyItNowPrice'] = $row['StartPrice'];
	    $row['StartPrice'] = 0;
	}
	
	unset($_SESSION['AttributeSet'][$row['Id']]);
	
	$sql_1 = "select url from picture_url where ItemID = '".$row['Id']."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$i = 1;
	while($row_1 = mysql_fetch_assoc($result_1)){
	    $row['picture_'.$i] = $row_1['url'];
	    $i++;
	}
	
	$sql_3 = "select * from shipping_service_options where ItemID = '".$row['Id']."' order by ShippingServicePriority";
	$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	$i = 1;
	while($row_3 = mysql_fetch_assoc($result_3)){
	    $row['ShippingService_'.$i] = $row_3['ShippingService'];
	    $row['ShippingServiceCost_'.$i] = $row_3['ShippingServiceCost'];
	    $row['ShippingServiceFree_'.$i] = $row_3['FreeShipping'];
	    $i++;
	}
	
	$sql_4 = "select * from international_shipping_service_option where ItemID = '".$row['Id']."' order by ShippingServicePriority";
	$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	$i = 1;
	while($row_4 = mysql_fetch_assoc($result_4)){
	    $row['InternationalShippingService_'.$i] = $row_4['ShippingService'];
	    $row['InternationalShippingServiceCost_'.$i] = $row_4['ShippingServiceCost'];
	    $array = explode(",", $row_4['ShipToLocation']);
	    if(count($array) > 1){
		$row['InternationalShippingToLocations_'.$i] = "Custom Locations";
		foreach($array as $v){
		    $row[$v.'_'.$i] = 1;
		}
	    }elseif($row_4['ShipToLocation'] == "Worldwide"){
		$row['InternationalShippingToLocations_'.$i] = "Worldwide";
	    }else{
		$row['InternationalShippingToLocations_'.$i] = "Custom Locations";
		$row[$row_4['ShipToLocation'].'_'.$i] = 1;
	    }
	    $i++;
	}
	
	$sql_5 = "select * from attribute_set where item_id = '".$row['Id']."'";
	$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	$row_5 = mysql_fetch_assoc($result_5);
	
	$sql_6 = "select * from attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
	$result_6 = mysql_query($sql_6, eBayListing::$database_connect);
	while($row_6 = mysql_fetch_assoc($result_6)){
	    if(strpos($row_6['ValueID'], ',')){
		$array = explode(',', $row_6['ValueID']);
		foreach($array as $a){
		    $_SESSION['AttributeSet'][$row['Id']][$row_5['attributeSetID']][$a.'_checkbox'] = $row_6['attributeID'].'_on';
		}
	    }else{
		$_SESSION['AttributeSet'][$row['Id']][$row_5['attributeSetID']][$row_6['attributeID']] = $row_6['ValueID'];
	    }
	}
	
	echo '['.json_encode($row).']';
	mysql_free_result($result);
    }
    
    public function updateItem(){
	/*
	if(!empty($_POST['UseStandardFooter']) && $_POST['UseStandardFooter'] == 1){
	    $sql = "select footer from account_footer where accountId = '".$this->account_id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    $_POST['Description'] .= $row['footer'];
	}
	*/
        $id = $_GET['item_id'];
        
	if(!empty($_GET['status'])){
            if($_GET['status'] == 4){
                $id = $this->copyItem($id, $_GET['status']);
                //$id = $this->copyItem($id, 6);
            }else{
                $status .= ",Status = '".$_GET['status']."'";
            }
	}
	
	session_start();
	if($_POST['ListingType'] == "FixedPriceItem" || $_POST['ListingType'] == "StoresFixedPrice"){
	    $_POST['StartPrice'] = $_POST['BuyItNowPrice'];
	    $_POST['BuyItNowPrice'] = 0;
	}
	
	if($_POST['ListingType'] == "Chinese"){
	    $_POST['Quantity'] = 1;   
	}
	
	//StartTime,EndTime
	//$PaymentMethods = ($_POST['PayPalPayment'] == 1)?'PayPal':'';
	if(!empty($_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyReturnsAcceptedOption'])){
	    $sql = "update items set 
	    BuyItNowPrice='".$_POST['BuyItNowPrice']."',Country='CN',Currency='".$_POST['Currency']."',
	    Description='".htmlentities($_POST['Description'], ENT_QUOTES)."',DispatchTimeMax='".$_POST['DispatchTimeMax']."',
	    ListingDuration='".$_POST['ListingDuration']."',ListingType='".$_POST['ListingType']."',Location='".$_POST['Location']."',PaymentMethods='PayPal',
	    PayPalEmailAddress='".$_POST['PayPalEmailAddress']."',PostalCode='".$_POST['PostalCode']."',
	    PrimaryCategoryCategoryID='".$_POST['PrimaryCategoryCategoryID']."',PrimaryCategoryCategoryName='".$_POST['PrimaryCategoryCategoryName']."',
	    SecondaryCategoryCategoryID='".$_POST['SecondaryCategoryCategoryID']."',SecondaryCategoryCategoryName='".$_POST['SecondaryCategoryCategoryName']."',
	    ReturnPolicyDescription='".$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyDescription']."',ReturnPolicyRefundOption='".$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyRefundOption']."',
	    ReturnPolicyReturnsAcceptedOption='".$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyReturnsAcceptedOption']."',ReturnPolicyReturnsWithinOption='".$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyReturnsWithinOption']."',
	    ReturnPolicyShippingCostPaidByOption='".$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyShippingCostPaidByOption']."',
	    Quantity='".@$_POST['Quantity']."',ReservePrice='".@$_POST['ReservePrice']."',
	    Site='".$_POST['Site']."',SKU='".$_POST['SKU']."',StartPrice='".$_POST['StartPrice']."',StoreCategory2ID='".$_POST['StoreCategory2ID']."',StoreCategory2Name='".$_POST['StoreCategory2Name']."',
	    StoreCategoryID='".$_POST['StoreCategoryID']."',StoreCategoryName='".$_POST['StoreCategoryName']."',SubTitle='".$_POST['SubTitle']."',
	    Title='".htmlentities($_POST['Title'], ENT_QUOTES)."',BoldTitle='".(empty($_POST['BoldTitle'])?0:1)."',
	    Border='".(empty($_POST['Border'])?0:1)."',Featured='".(empty($_POST['Featured'])?0:1)."',Highlight='".(empty($_POST['Highlight'])?0:1)."',
	    HomePageFeatured='".(empty($_POST['HomePageFeatured'])?0:1)."',GalleryTypeFeatured='".(empty($_POST['GalleryTypeFeatured'])?0:1)."',GalleryTypePlus='".(empty($_POST['GalleryTypePlus'])?0:1)."',GalleryURL='".$_POST['GalleryURL']."',
	    ShippingServiceOptionsType='".$_POST['ShippingServiceOptionsType']."',InsuranceOption='".$_POST['InsuranceOption']."',InsuranceFee='".$_POST['InsuranceFee']."',
	    InternationalShippingServiceOptionType='".$_POST['InternationalShippingServiceOptionType']."',InternationalInsurance='".$_POST['InternationalInsurance']."',InternationalInsuranceFee='".$_POST['InternationalInsuranceFee']."',
	    UseStandardFooter='".(empty($_POST['UseStandardFooter'])?0:1)."',accountId='".$this->account_id."'".$status." where Id = '".$id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	}else{
	    $sql = "update items set 
	    BuyItNowPrice='".$_POST['BuyItNowPrice']."',Country='CN',Currency='".$_POST['Currency']."',
	    Description='".htmlentities($_POST['Description'], ENT_QUOTES)."',DispatchTimeMax='".$_POST['DispatchTimeMax']."',
	    ListingDuration='".$_POST['ListingDuration']."',ListingType='".$_POST['ListingType']."',Location='".$_POST['Location']."',PaymentMethods='PayPal',
	    PayPalEmailAddress='".$_POST['PayPalEmailAddress']."',PostalCode='".$_POST['PostalCode']."',
	    PrimaryCategoryCategoryID='".$_POST['PrimaryCategoryCategoryID']."',PrimaryCategoryCategoryName='".$_POST['PrimaryCategoryCategoryName']."',
	    SecondaryCategoryCategoryID='".$_POST['SecondaryCategoryCategoryID']."',SecondaryCategoryCategoryName='".$_POST['SecondaryCategoryCategoryName']."',
	    Quantity='".@$_POST['Quantity']."',ReservePrice='".@$_POST['ReservePrice']."',
	    Site='".$_POST['Site']."',SKU='".$_POST['SKU']."',StartPrice='".$_POST['StartPrice']."',StoreCategory2ID='".$_POST['StoreCategory2ID']."',StoreCategory2Name='".$_POST['StoreCategory2Name']."',
	    StoreCategoryID='".$_POST['StoreCategoryID']."',StoreCategoryName='".$_POST['StoreCategoryName']."',SubTitle='".$_POST['SubTitle']."',
	    Title='".htmlentities($_POST['Title'], ENT_QUOTES)."',BoldTitle='".(empty($_POST['BoldTitle'])?0:1)."',
	    Border='".(empty($_POST['Border'])?0:1)."',Featured='".(empty($_POST['Featured'])?0:1)."',Highlight='".(empty($_POST['Highlight'])?0:1)."',
	    HomePageFeatured='".(empty($_POST['HomePageFeatured'])?0:1)."',GalleryTypeFeatured='".(empty($_POST['GalleryTypeFeatured'])?0:1)."',GalleryTypePlus='".(empty($_POST['GalleryTypePlus'])?0:1)."',GalleryURL='".$_POST['GalleryURL']."',
	    ShippingServiceOptionsType='".$_POST['ShippingServiceOptionsType']."',InsuranceOption='".$_POST['InsuranceOption']."',InsuranceFee='".$_POST['InsuranceFee']."',
	    InternationalShippingServiceOptionType='".$_POST['InternationalShippingServiceOptionType']."',InternationalInsurance='".$_POST['InternationalInsurance']."',InternationalInsuranceFee='".$_POST['InternationalInsuranceFee']."',
	    UseStandardFooter='".(empty($_POST['UseStandardFooter'])?0:1)."',accountId='".$this->account_id."'".$status." where Id = '".$id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	}
	//echo $sql;
	//exit;
	//$this->log("item", $sql);
	
	$sql_1 = "delete from picture_url where ItemID = '".$id."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$i = 1;
	while(!empty($_POST['picture_'.$i])){
	    $sql_1 = "insert into picture_url (ItemID,url) values 
	    ('".$id."','".$_POST['picture_'.$i]."')";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $i++;
	}
	
	$sql_1 = "delete from shipping_service_options where ItemID = '".$id."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$i = 1;
	while(!empty($_POST['ShippingService_'.$i])){
	    $sql_1 = "insert into shipping_service_options (ItemID,FreeShipping,ShippingService,ShippingServiceCost,ShippingServicePriority) values
	    ('".$id."','".@$_POST['ShippingServiceFree_'.$i]."','".$_POST['ShippingService_'.$i]."','".$_POST['ShippingServiceCost_'.$i]."','".$i."')";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $i++;
	}
	
	$sql_1 = "delete from international_shipping_service_option where ItemID = '".$id."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$i = 1;
	while(!empty($_POST['InternationalShippingService_'.$i])){
	    $ShipToLocation = '';
	    if($_POST['InternationalShippingToLocations_'.$i] == 'Custom Locations'){
		if(!empty($_POST['Americas_'.$i]) && $_POST['Americas_'.$i] == 1){
		    $ShipToLocation .= ',Americas';
		}
		
		if(!empty($_POST['US_'.$i]) && $_POST['US_'.$i] == 1){
		    $ShipToLocation .= ',US';
		}
		
		if(!empty($_POST['Europe_'.$i]) && $_POST['Europe_'.$i] == 1){
		    $ShipToLocation .= ',Europe';
		}
		
		if(!empty($_POST['Asia_'.$i]) && $_POST['Asia_'.$i] == 1){
		    $ShipToLocation .= ',Asia';
		}
		
		if(!empty($_POST['CA_'.$i]) && $_POST['CA_'.$i] == 1){
		    $ShipToLocation .= ',CA';
		}
		
		if(!empty($_POST['GB_'.$i]) && $_POST['GB_'.$i] == 1){
		    $ShipToLocation .= ',GB';
		}
		
		if(!empty($_POST['AU_'.$i]) && $_POST['AU_'.$i] == 1){
		    $ShipToLocation .= ',AU';
		}
		
		if(!empty($_POST['MX_'.$i]) && $_POST['MX_'.$i] == 1){
		    $ShipToLocation .= ',MX';
		}
		
		if(!empty($_POST['DE_'.$i]) && $_POST['DE_'.$i] == 1){
		    $ShipToLocation .= ',DE';
		}
		
		if(!empty($_POST['JP_'.$i]) && $_POST['JP_'.$i] == 1){
		    $ShipToLocation .= ',JP';
		}
		
		$ShipToLocation = substr($ShipToLocation, 1);
	    }else{
		$ShipToLocation = 'Worldwide';
	    }
	    $sql_2 = "insert into international_shipping_service_option (ItemID,ShippingService,ShippingServiceCost,ShippingServicePriority,ShipToLocation) values
	    ('".$id."','".$_POST['InternationalShippingService_'.$i]."','".$_POST['InternationalShippingServiceCost_'.$i]."','".$i."','".$ShipToLocation."')";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    $i++;
	}
	
	
	$temp_array = array();
	if(!empty($_SESSION['AttributeSet'][$id])){
	    //print_r($_SESSION['AttributeSet']);
	    //exit;
	    $sql_4 = "select attribute_set_id from attribute_set where item_id = '".$id."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	    $row_4 = mysql_fetch_assoc($result_4);
	    
	    $sql_4 = "delete from attribute where attribute_set_id = '".$row_4['attribute_set_id']."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	    
	    $sql_4 = "delete from attribute_set where item_id = '".$id."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	
	    foreach($_SESSION['AttributeSet'][$id] as $attributeSetID=>$Attribute){
		$sql_4 = "insert into attribute_set (item_id,attributeSetID) values ('".$id."', '".$attributeSetID."')";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		
		$attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
		$temp_array = array();
		foreach($Attribute as $attributeID=>$ValueID){
		    if(!empty($ValueID)){
			if(strpos($ValueID, "on") != false){
			    $tempAttributeID = $attributeID;
			    $attributeID = substr($ValueID, 0, -3);
			    $ValueID = substr($tempAttributeID, 0, -9);
			    //echo $attributeID.":".$ValueID;
			    //echo "\n";
			    $temp_array[$attributeID][] = $ValueID;
			}else{
				$sql_4 = "insert into attribute (attributeID,attribute_set_id,ValueID) values 
				('".$attributeID."', '".$attribute_set_id."', '".$ValueID."')";
				$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
				
				//echo $sql_4;
				//echo "\n";
			}
		    }
		}
		
		//print_r($temp_array);
		if(count($temp_array) > 0){
		    foreach($temp_array as $key=>$value){
			$ValueID = "";
			foreach($value as $name){
			    $ValueID .= $name.',';
			}
			$ValueID = substr($ValueID, 0, -1);
			$sql_4 = "insert into attribute (attributeID,attribute_set_id,ValueID) values 
			('".$key."', '".$attribute_set_id."', '".$ValueID."')";
			$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		    }
		}
	    }
	}
	
	if($result && $result_1){
	    //unset($_SESSION['Schedule']);
	    //unset($_SESSION['AttributeSet']);
	    echo '{success: true, msg: "Update Item Success!"}';
	    $this->log("item", "update item ".$id." success.");
	}else{
	    echo '{success: false,
		    errors: {message: "can\'t update, please notice admin."}
		}';
	    $this->log("item", "update item ".$id." failure.", "error");
	}
    }
    
    public function updateMultiItem(){
	//print_r($_POST);
	$ids = explode(',', $_GET['item_id']);
	$where = " where Id in (";
	foreach($ids as $id){
	    $where .= $id.",";
	}
	$where = substr($where, 0, -1);
	$where .= ")";
	
	$update = "update items set ";
	if(!empty($_GET['status'])){
	    $update .= "Status = '".$_GET['status']."',";
	}
	
	if(!empty($_POST['ListingType']) && $_POST['ListingType'] == "FixedPriceItem" || $_POST['ListingType'] == "StoresFixedPrice"){
	    if(!empty($_POST['BuyItNowPrice']) && $_POST['BuyItNowPrice'] != 'Multi Value'){
		$update .= "StartPrice = '".$_POST['BuyItNowPrice']."',";
		$update .= "BuyItNowPrice = '0',";
	    }
	}else{
	    if(!empty($_POST['BuyItNowPrice']) && $_POST['BuyItNowPrice'] != 'Multi Value'){
		$update .= "BuyItNowPrice = '".$_POST['BuyItNowPrice']."',";
	    }
	    if(!empty($_POST['StartPrice']) && $_POST['StartPrice'] != 'Multi Value'){
		$update .= "StartPrice = '".$_POST['StartPrice']."',";
	    }
	}
	
	if(!empty($_POST['Currency']) && $_POST['Currency'] != 'Multi Value'){
	    $update .= "Currency = '".$_POST['Currency']."',";
	}
	
	if(!empty($_POST['Description']) && strpos('Multi Value', $_POST['Description'])){
	    $update .= "Description = '".htmlentities($_POST['Description'], ENT_QUOTES)."',";
	}
	
	if(!empty($_POST['DispatchTimeMax']) && $_POST['DispatchTimeMax'] != 'Multi Value'){
	    $update .= "DispatchTimeMax = '".$_POST['DispatchTimeMax']."',";
	}
	
	if(!empty($_POST['ListingDuration']) && $_POST['ListingDuration'] != 'Multi Value'){
	    $update .= "ListingDuration = '".$_POST['ListingDuration']."',";
	}
	
	if(!empty($_POST['ListingType']) && $_POST['ListingType'] != 'Multi Value'){
	    $update .= "ListingType = '".$_POST['ListingType']."',";
	}
	
	if(!empty($_POST['Location']) && $_POST['Location'] != 'Multi Value'){
	    $update .= "Location = '".$_POST['Location']."',";
	}
	
	if(!empty($_POST['PayPalEmailAddress']) && $_POST['PayPalEmailAddress'] != 'Multi Value'){
	    $update .= "PayPalEmailAddress = '".$_POST['PayPalEmailAddress']."',";
	}
	
	if(!empty($_POST['PostalCode']) && $_POST['PostalCode'] != 'Multi Value'){
	    $update .= "PostalCode = '".$_POST['PostalCode']."',";
	}
	
	if(!empty($_POST['PrimaryCategoryCategoryID']) && $_POST['PrimaryCategoryCategoryID'] != 'Multi Value'){
	    $update .= "PrimaryCategoryCategoryID = '".$_POST['PrimaryCategoryCategoryID']."',";
	}
	
	if(!empty($_POST['PrimaryCategoryCategoryName']) && $_POST['PrimaryCategoryCategoryName'] != 'Multi Value'){
	    $update .= "PrimaryCategoryCategoryName = '".$_POST['PrimaryCategoryCategoryName']."',";
	}
	
	if(!empty($_POST['SecondaryCategoryCategoryID']) && $_POST['SecondaryCategoryCategoryID'] != 'Multi Value'){
	    $update .= "SecondaryCategoryCategoryID = '".$_POST['SecondaryCategoryCategoryID']."',";
	}
	
	if(!empty($_POST['SecondaryCategoryCategoryName']) && $_POST['SecondaryCategoryCategoryName'] != 'Multi Value'){
	    $update .= "SecondaryCategoryCategoryName = '".$_POST['SecondaryCategoryCategoryName']."',";
	}
	
	if(!empty($_POST['Quantity']) && $_POST['Quantity'] != 'Multi Value'){
	    $update .= "Quantity = '".$_POST['Quantity']."',";
	}
	
	if(!empty($_POST['ReservePrice']) && $_POST['ReservePrice'] != 'Multi Value'){
	    $update .= "ReservePrice = '".$_POST['ReservePrice']."',";
	}
	
	if(!empty($_POST['Site']) && $_POST['Site'] != 'Multi Value'){
	    $update .= "Site = '".$_POST['Site']."',";
	}
	
	if(!empty($_POST['SKU']) && $_POST['SKU'] != 'Multi Value'){
	    $update .= "SKU = '".$_POST['SKU']."',";
	}
	
	if(!empty($_POST['StoreCategory2ID']) && $_POST['StoreCategory2ID'] != 'Multi Value'){
	    $update .= "StoreCategory2ID = '".$_POST['StoreCategory2ID']."',";
	}
	
	if(!empty($_POST['StoreCategory2Name']) && $_POST['StoreCategory2Name'] != 'Multi Value'){
	    $update .= "StoreCategory2Name = '".$_POST['StoreCategory2Name']."',";
	}
	
	if(!empty($_POST['StoreCategoryID']) && $_POST['StoreCategoryID'] != 'Multi Value'){
	    $update .= "StoreCategoryID = '".$_POST['StoreCategoryID']."',";
	}
	
	if(!empty($_POST['StoreCategoryName']) && $_POST['StoreCategoryName'] != 'Multi Value'){
	    $update .= "StoreCategoryName = '".$_POST['StoreCategoryName']."',";
	}
	
	if(!empty($_POST['SubTitle']) && $_POST['SubTitle'] != 'Multi Value'){
	    $update .= "SubTitle = '".mysql_real_escape_string($_POST['SubTitle'])."',";
	}
	
	if(!empty($_POST['Title']) && $_POST['Title'] != 'Multi Value'){
	    $update .= "Title = '".htmlentities($_POST['Title'], ENT_QUOTES)."',";
	}
	
	if(!empty($_POST['SKU']) && $_POST['SKU'] != 'Multi Value'){
	    $update .= "SKU = '".$_POST['SKU']."',";
	}
	
	if(!empty($_POST['Border'])){
	    $update .= "Border = '".$_POST['Border']."',";
	}
	
	//---------------------------------------------------------------------------------
	if(!empty($_POST['GalleryURL'])){
	    $update .= "GalleryURL = '".$_POST['GalleryURL']."',";
	}
	
	if(!empty($_POST['ShippingServiceOptionsType']) && $_POST['ShippingServiceOptionsType'] != 'Multi Value'){
	    $update .= "ShippingServiceOptionsType = '".$_POST['ShippingServiceOptionsType']."',";
	}
	
	if(!empty($_POST['InsuranceOption']) && $_POST['InsuranceOption'] != 'Multi Value'){
	    $update .= "InsuranceOption = '".$_POST['InsuranceOption']."',";
	}
	
	if(!empty($_POST['InsuranceFee']) && $_POST['InsuranceFee'] != 'Multi Value'){
	    $update .= "InsuranceFee = '".$_POST['InsuranceFee']."',";
	}
	
	if(!empty($_POST['InternationalShippingServiceOptionType']) && $_POST['InternationalShippingServiceOptionType'] != 'Multi Value'){
	    $update .= "InternationalShippingServiceOptionType = '".$_POST['InternationalShippingServiceOptionType']."',";
	}
	
	if(!empty($_POST['InternationalInsurance']) && $_POST['InternationalInsurance'] != 'Multi Value'){
	    $update .= "InternationalInsurance = '".$_POST['InternationalInsurance']."',";
	}
	
	if(!empty($_POST['InternationalInsuranceFee']) && $_POST['InternationalInsuranceFee'] != 'Multi Value'){
	    $update .= "InternationalInsuranceFee = '".$_POST['InternationalInsuranceFee']."',";
	}
	
	session_start();
	if(!empty($_SESSION['ReturnPolicyReturns'][$_GET['item_id']])){
	    if(!empty($_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyReturnsAcceptedOption']) && $_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyReturnsAcceptedOption'] != 'Multi Value'){
		$update .= "ReturnPolicyReturnsAcceptedOption = '".$_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyReturnsAcceptedOption']."',";
	    }
	    
	    if(!empty($_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyReturnsWithinOption']) && $_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyReturnsWithinOption'] != 'Multi Value'){
		$update .= "ReturnPolicyReturnsWithinOption = '".$_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyReturnsWithinOption']."',";
	    }
	    
	    if(!empty($_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyRefundOption']) && $_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyRefundOption'] != 'Multi Value'){
		$update .= "ReturnPolicyRefundOption = '".$_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyRefundOption']."',";
	    }
	    
	    if(!empty($_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyShippingCostPaidByOption']) && $_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyShippingCostPaidByOption'] != 'Multi Value'){
		$update .= "ReturnPolicyShippingCostPaidByOption = '".$_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyShippingCostPaidByOption']."',";
	    }
	    
	    if(!empty($_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyDescription']) && $_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyDescription'] != 'Multi Value'){
		$update .= "ReturnPolicyDescription = '".$_SESSION['ReturnPolicyReturns'][$_GET['item_id']]['ReturnPolicyDescription']."',";
	    }
	}
	
	if(!empty($_POST['UseStandardFooter'])){
	    $update .= "UseStandardFooter = '1',";
	}
	
	$update = substr($update, 0, -1);
	$sql = $update . $where;
	//$this->log("item", $sql);
	$result = mysql_query($sql, eBayListing::$database_connect);
	//echo $result;
	//print_r($_POST);
	
	$where = " where ItemID in (";
	foreach($ids as $id){
	    $where .= $id.",";
	}
	$where = substr($where, 0, -1);
	$where .= ")";
	
	if(!empty($_POST['picture_1']) && $_POST['picture_1'] != 'Multi Value'){
	    $sql_1 = "delete from picture_url ".$where;
	    //echo $sql_1."\n";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    
	    foreach($ids as $id){
		$i = 1;
		while(!empty($_POST['picture_'.$i]) && $_POST['picture_'.$i] != 'Multi Value'){
		    $sql_1 = "insert into picture_url (ItemID,url) values 
		    ('".$id."','".$_POST['picture_'.$i]."')";
		    //echo $sql_1."\n";
		    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		    $i++;
		}
	    }
	}
	
	if(!empty($_POST['ShippingService_1'])){
	    $sql_1 = "delete from shipping_service_options ".$where;
	    //echo $sql_1."\n";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    
	    foreach($ids as $id){
		$i = 1;
		while(!empty($_POST['ShippingService_'.$i])){
		    $sql_1 = "insert into shipping_service_options (ItemID,FreeShipping,ShippingService,ShippingServiceCost,ShippingServicePriority) values
		    ('".$id."','".@$_POST['ShippingServiceFree_'.$i]."','".$_POST['ShippingService_'.$i]."','".$_POST['ShippingServiceCost_'.$i]."','".$i."')";
		    //echo $sql_1."\n";
		    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		    $i++;
		}
	    }
	}
	
	if(!empty($_POST['InternationalShippingService_1'])){
	    $sql_1 = "delete from international_shipping_service_option ".$where;
	    //echo $sql_1."\n";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);

	    foreach($ids as $id){
		$i = 1;
		while(!empty($_POST['InternationalShippingService_'.$i])){
		    $ShipToLocation = '';
		    if($_POST['InternationalShippingToLocations_'.$i] == 'Custom Locations'){
			if(!empty($_POST['Americas_'.$i]) && $_POST['Americas_'.$i] == 1){
			    $ShipToLocation .= ',Americas';
			}
			
			if(!empty($_POST['US_'.$i]) && $_POST['US_'.$i] == 1){
			    $ShipToLocation .= ',US';
			}
			
			if(!empty($_POST['Europe_'.$i]) && $_POST['Europe_'.$i] == 1){
			    $ShipToLocation .= ',Europe';
			}
			
			if(!empty($_POST['Asia_'.$i]) && $_POST['Asia_'.$i] == 1){
			    $ShipToLocation .= ',Asia';
			}
			
			if(!empty($_POST['CA_'.$i]) && $_POST['CA_'.$i] == 1){
			    $ShipToLocation .= ',CA';
			}
			
			if(!empty($_POST['GB_'.$i]) && $_POST['GB_'.$i] == 1){
			    $ShipToLocation .= ',GB';
			}
			
			if(!empty($_POST['AU_'.$i]) && $_POST['AU_'.$i] == 1){
			    $ShipToLocation .= ',AU';
			}
			
			if(!empty($_POST['MX_'.$i]) && $_POST['MX_'.$i] == 1){
			    $ShipToLocation .= ',MX';
			}
			
			if(!empty($_POST['DE_'.$i]) && $_POST['DE_'.$i] == 1){
			    $ShipToLocation .= ',DE';
			}
			
			if(!empty($_POST['JP_'.$i]) && $_POST['JP_'.$i] == 1){
			    $ShipToLocation .= ',JP';
			}
			
			$ShipToLocation = substr($ShipToLocation, 1);
		    }else{
			$ShipToLocation = 'Worldwide';
		    }
		    $sql_2 = "insert into international_shipping_service_option (ItemID,ShippingService,ShippingServiceCost,ShippingServicePriority,ShipToLocation) values
		    ('".$id."','".$_POST['InternationalShippingService_'.$i]."','".$_POST['InternationalShippingServiceCost_'.$i]."','".$i."','".$ShipToLocation."')";
		    //echo $sql_2."\n";
		    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		    $i++;
		}
	    }
	}
	
	$temp_array = array();
	if(!empty($_SESSION['AttributeSet'][$_GET['item_id']])){
	    //print_r($_SESSION['AttributeSet']);
	    foreach($ids as $id){
		//exit;
		$sql_4 = "select attribute_set_id from attribute_set where item_id = '".$id."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		$row_4 = mysql_fetch_assoc($result_4);
		
		$sql_4 = "delete from attribute where attribute_set_id = '".$row_4['attribute_set_id']."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		
		$sql_4 = "delete from attribute_set where item_id = '".$id."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	    
		foreach($_SESSION['AttributeSet'][$_GET['item_id']] as $attributeSetID=>$Attribute){
		    $sql_4 = "insert into attribute_set (item_id,attributeSetID) values ('".$id."', '".$attributeSetID."')";
		    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		    
		    $attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
		    $temp_array = array();
		    foreach($Attribute as $attributeID=>$ValueID){
			if(!empty($ValueID)){
			    if(strpos($ValueID, "on") != false){
				$tempAttributeID = $attributeID;
				$attributeID = substr($ValueID, 0, -3);
				$ValueID = substr($tempAttributeID, 0, -9);
				//echo $attributeID.":".$ValueID;
				//echo "\n";
				$temp_array[$attributeID][] = $ValueID;
			    }else{
				    $sql_4 = "insert into attribute (attributeID,attribute_set_id,ValueID) values 
				    ('".$attributeID."', '".$attribute_set_id."', '".$ValueID."')";
				    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
				    //echo $sql_4."\n";
			    }
			}
		    }
		    
		    //print_r($temp_array);
		    if(count($temp_array) > 0){
			foreach($temp_array as $key=>$value){
			    $ValueID = "";
			    foreach($value as $name){
				$ValueID .= $name.',';
			    }
			    $ValueID = substr($ValueID, 0, -1);
			    $sql_4 = "insert into attribute (attributeID,attribute_set_id,ValueID) values 
			    ('".$key."', '".$attribute_set_id."', '".$ValueID."')";
			    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
			    //echo $sql_4."\n";
			}
		    }
		}
	    }
	}
	
	if($result){
	    //unset($_SESSION['Schedule']);
	    //unset($_SESSION['AttributeSet']);
	    echo '{success: true, msg: "Update Item '.$_GET['item_id'].' Success!"}';
	    $this->log("item", "update multi item ".$_GET['item_id']." success.");
	}else{
	    echo '{success: false,
		    errors: {message: "can\'t update, please notice admin."}
		}';
	    $this->log("item", "update multi item ".$_GET['item_id']." failure.", "error");
	}
    }
}
?>