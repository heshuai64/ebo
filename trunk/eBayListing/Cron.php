<?php
define ('__DOCROOT__', '/export/eBayListing');
set_time_limit(1800);
ini_set("memory_limit","256M");

class Cron{
    public static $database_connect;
    private $config;
    
    public function __construct(){
        
        $this->config = parse_ini_file(__DOCROOT__ . '/config.ini', true);
        
        Cron::$database_connect = mysql_connect($this->config['database']['host'], $this->config['database']['user'], $this->config['database']['password']);

        if (!Cron::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(Cron::$database_connect);
            exit;
        }
	
        mysql_query("SET NAMES 'UTF8'", Cron::$database_connect);
	
        if (!mysql_select_db($this->config['database']['name'], Cron::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(Cron::$database_connect);
            exit;
        }
    }
    
    /*
        Europe/London       +0100  +7h
	America/New_York    -0400  +12h
	Europe/Paris        +0200  +6h
	Australia/Canberra  +1000  -3h
	Europe/Berlin       +0100  +7
	Asia/Shanghai       +0800
    */
    private function log($file_name, $data){
        if(!file_exists($this->config['log']['cron']."/".date("Ymd"))){
            mkdir($this->config['log']['cron']."/".date("Ymd"), 0777);
        }
        file_put_contents($this->config['log']['cron']."/".date("Ymd")."/".$file_name, $data, FILE_APPEND);
    }
    
    public function calculateTemplateData(){
        set_time_limit(600);
        ini_set('memory_limit', '300M');
	require_once './Classes/PHPExcel.php';
	require_once './Classes/PHPExcel/IOFactory.php';

        if(!file_exists('/export/eBayListing/report/'.date("Ymd"))){
            mkdir('/export/eBayListing/report/'.date("Ymd"), 0777);
        }
        
        $thirty_days_ago = date("Y-m-d 4:00:00", time() - (30 * 24 * 60 * 60));
        $ten_after = date("Y-m-d 4:00:00", time() + (10 * 24 * 60 * 60));
        $thirty_after = date("Y-m-d 4:00:00", time() + (30 * 24 * 60 * 60));
        $yestoday = date("Y-m-d 4:00:00", time() - (24 * 60 * 60));
        $today = date("Y-m-d 4:00:00");
        $tomorrow = date("Y-m-d 4:00:00", time() + (24 * 60 * 60));
        
        $sql = "select id,name from account where status = 1";
        $result = mysql_query($sql, Cron::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $array = array();
            $objExcel = new PHPExcel();
            $objExcel->setActiveSheetIndex(0);
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'TemplateID');
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'SKU');
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Title');
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, 'Type');
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'Price');
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, 'Duration');
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 1, 'End 30');
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(7, 1, 'Sold 30');
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(8, 1, 'Listing All');
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(9, 1, 'Listing 10');
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(10, 1, 'Listing 30');
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 1, 'Sold');
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(12, 1, 'Start');
            $objExcel->getActiveSheet()->setCellValueByColumnAndRow(13, 1, 'End');
        
            $sql_1 = "select count(*) as EndThirty,SKU,TemplateID,Title,ListingType,CurrentPrice,ListingDuration from items where EndTime > '".$thirty_days_ago."' and EndTime < '".$today."' and (Status = 5 or Status = 6) and accountId = '".$row['id']."' group by TemplateID";
            //echo $sql_1;
            
            $result_1 = mysql_query($sql_1, Cron::$database_connect);
            while($row_1 = mysql_fetch_assoc($result_1)){
                $array[$row_1['TemplateID']]['SKU'] = $row_1['SKU'];
                $array[$row_1['TemplateID']]['Title'] = $row_1['Title'];
                $array[$row_1['TemplateID']]['ListingType'] = $row_1['ListingType'];
                $array[$row_1['TemplateID']]['CurrentPrice'] = $row_1['CurrentPrice'];
                $array[$row_1['TemplateID']]['ListingDuration'] = $row_1['ListingDuration'];
                $array[$row_1['TemplateID']]['EndThirty'] = $row_1['EndThirty'];
            }
            
            $sql_2 = "select sum(QuantitySold) as SoldThirty,TemplateID from items where EndTime > '".$thirty_days_ago."' and EndTime < '".$today."' and accountId = '".$row['id']."' group by TemplateID";
            $result_2 = mysql_query($sql_2, Cron::$database_connect);
            while($row_2 = mysql_fetch_assoc($result_2)){
                $array[$row_2['TemplateID']]['SoldThirty'] = $row_2['SoldThirty'];
            }
            
            $sql_3 = "select BuyItNowPrice,StartPrice,count(*) as AllListing,SKU,TemplateID,Title,ListingType,CurrentPrice,ListingDuration from items where Status = 2 and accountId = '".$row['id']."' group by TemplateID";
            $result_3 = mysql_query($sql_3, Cron::$database_connect);
            while($row_3 = mysql_fetch_assoc($result_3)){
                $array[$row_3['TemplateID']]['AllListing'] = $row_3['AllListing'];
                
                if(empty($array[$row_3['TemplateID']]['CurrentPrice'])){
                    $array[$row_3['TemplateID']]['CurrentPrice'] = $row_3['StartPrice'];
                }
                
                if(empty($array[$row_3['TemplateID']]['SKU'])){
                    $array[$row_3['TemplateID']]['SKU'] = $row_3['SKU'];
                }
                
                if(empty($array[$row_3['TemplateID']]['Title'])){
                    $array[$row_3['TemplateID']]['Title'] = $row_3['Title'];
                }
                
                if(empty($array[$row_3['TemplateID']]['ListingType'])){
                    $array[$row_3['TemplateID']]['ListingType'] = $row_3['ListingType'];
                }
                
                if(empty($array[$row_3['TemplateID']]['CurrentPrice'])){
                    $array[$row_3['TemplateID']]['CurrentPrice'] = $row_3['CurrentPrice'];
                }
                
                if(empty($array[$row_3['TemplateID']]['ListingDuration'])){
                    $array[$row_3['TemplateID']]['ListingDuration'] = $row_3['ListingDuration'];
                }
            }
            
            $sql_4 = "select count(*) as ListingTen,TemplateID from items where EndTime > '".$today."' and EndTime < '".$ten_after."' and Status = 2 and accountId = '".$row['id']."' group by TemplateID";
            $result_4 = mysql_query($sql_4, Cron::$database_connect);
            while($row_4 = mysql_fetch_assoc($result_4)){
                $array[$row_4['TemplateID']]['ListingTen'] = $row_4['ListingTen'];
            }
            
            $sql_5 = "select count(*) as ListingTwenty,TemplateID from items where EndTime > '".$ten_after."' and EndTime < '".$thirty_after."' and Status = 2 and accountId = '".$row['id']."' group by TemplateID";
            $result_5 = mysql_query($sql_5, Cron::$database_connect);
            while($row_5 = mysql_fetch_assoc($result_5)){
                $array[$row_5['TemplateID']]['ListingTwenty'] = $row_5['ListingTwenty'];
            }
            
            $sql_6 = "select sum(QuantitySold) as Sold,TemplateID from items where ((EndTime > '".$yestoday."' and EndTime < '".$today."') or (StartTime > '".$yestoday."' and StartTime < '".$today."')) and accountId = '".$row['id']."' group by TemplateID";
            $result_6 = mysql_query($sql_6, Cron::$database_connect);
            while($row_6 = mysql_fetch_assoc($result_6)){
                $array[$row_6['TemplateID']]['Sold'] = $row_6['Sold'];
            }
            
            $sql_7 = "select count(*) as Start,TemplateID from items where StartTime > '".$yestoday."' and StartTime < '".$today."' and accountId = '".$row['id']."' group by TemplateID";
            $result_7 = mysql_query($sql_7, Cron::$database_connect);
            while($row_7 = mysql_fetch_assoc($result_7)){
                $array[$row_7['TemplateID']]['Start'] = $row_7['Start'];
            }
            
            $sql_8 = "select count(*) as End,TemplateID from items where EndTime > '".$today."' and EndTime < '".$tomorrow."' and accountId = '".$row['id']."' group by TemplateID";
            $result_8 = mysql_query($sql_8, Cron::$database_connect);
            while($row_8 = mysql_fetch_assoc($result_8)){
                $array[$row_8['TemplateID']]['End'] = $row_8['End'];
            }
            
            $i = 2;
            foreach($array as $key=>$a){
                $j = 0;
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $key);
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $a['SKU']);
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $a['Title']);
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $a['ListingType']);
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $a['CurrentPrice']);
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $a['ListingDuration']);
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, empty($a['EndThirty'])?0:$a['EndThirty']);
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, empty($a['SoldThirty'])?0:$a['SoldThirty']);
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, empty($a['AllListing'])?0:$a['AllListing']);
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, empty($a['ListingTen'])?0:$a['ListingTen']);
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, empty($a['ListingTwenty'])?0:$a['ListingTwenty']);
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, empty($a['Sold'])?0:$a['Sold']);
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, empty($a['Start'])?0:$a['Start']);
                $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, empty($a['End'])?0:$a['End']);
                $i++;
            }
            $writer = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
            $writer->save('/export/eBayListing/report/'.date("Ymd").'/'.$row['name'].'.xls');
            echo round(memory_get_usage() / (1024 * 1024)),"M\n";
        }
        
    }
    
    /*
        US: 12:00:00
        UK: 17:00:00
        Germany: 17:00:00
        Australia: 03:00:00
        France: 18:00:00
    */
    public function calculateListingSchedule(){
        //global $argv;
        if(in_array(date("H"), array('02', '12', '17' ,'18'))){
            require_once __DOCROOT__ . '/module/template.php';
            $today = date("Y-m-d");
            //$Site = $argv[2];
            switch(date("H")){
                case "02":
		    $day = date("D");
                    $Site = "Australia";
                    $sql_1 = "select Id,scheduleTemplateName,accountId from template where ListingType = 'Chinese' and scheduleTemplateName <> '' and status = 2 and Site = 'Australia'";
                break;
            
                case "12":
		    $day = date("D", time() + 24 * 60 *60);
                    $Site = "US";
                    $sql_1 = "select Id,scheduleTemplateName,accountId from template where ListingType = 'Chinese' and scheduleTemplateName <> '' and status = 2 and (Site = 'US' or Site = 'eBayMotors')";
                break;
            
                case "17":
		    $day = date("D", time() + 24 * 60 *60);
                    $Site = "UK";
                    $sql_1 = "select Id,scheduleTemplateName,accountId from template where ListingType = 'Chinese' and scheduleTemplateName <> '' and status = 2 and Site = 'UK'";
                break;
            
                case "18":
		    $day = date("D", time() + 24 * 60 *60);
                    $Site = "Germany";
                    $sql_1 = "select Id,scheduleTemplateName,accountId from template where ListingType = 'Chinese' and scheduleTemplateName <> '' and status = 2 and (Site = 'Germany' or Site = 'France')";
                break;
            }
            
            //$sql_1 = "select scheduleTemplateName,accountId from template where status = 2 and Site = '".$Site."'";
            $this->log("calculateListingSchedule-".$Site.".html", $sql_1."<br>");
            $result_1 = mysql_query($sql_1, Cron::$database_connect);
            $i = 0;
            while($row_1 = mysql_fetch_assoc($result_1)){
                $sql_2 = "select day,time from schedule_template where name = '".$row_1['scheduleTemplateName']."' and account_id = '".$row_1['accountId']."'";
                $this->log("calculateListingSchedule-".$Site.".html", $sql_2."<br>");
                $result_2 = mysql_query($sql_2, Cron::$database_connect);
                while($row_2 = mysql_fetch_assoc($result_2)){
                    if($day == $row_2['day']){
			$template = new Template($row_1['accountId']);
                        print_r($row_2);
                        $local_time = $template->getSiteTime($Site, $today, $row_2['time']);
                        $item_id = $template->changeTemplateToItem($row_1['Id'], $local_time, $today . " " .$row_2['time'], 1);
                        $this->log("calculateListingSchedule-".$Site.".html", "t:".$row_1['Id']." ==> i:".$item_id.". BeiJing:".$local_time.", ".$Site.": ".$today . " " .$row_2['time']."<br>");
                        $this->log("calculateListingSchedule-".$Site.".html", "<br><font color='red'>++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++</font><br>");
                    }
                }
                //$sql_3 = "update template set ScheduleStartDate = '".$today."' where Id = ".$row_1['Id'];
                //$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
                //if($i > 20){
                //    exit;
                //}
                $i++;
            }
        }
    }
    
    public function calculateForeverListingSchedule(){
	echo date("H")."\n";
	if(in_array(date("H"), array('02', '12', '17' ,'18'))){
            require_once __DOCROOT__ . '/module/template.php';
            $today = date("Y-m-d");
            //$Site = $argv[2];
            switch(date("H")){
                case "02":
		    $day = date("Y-m-d", time() + 24 * 60 *60);
                    $Site = "Australia";
                    $sql_1 = "select Id,accountId,ForeverListingTime,ForeverListingChinaTime from template where status = 7 and Site = 'Australia'";
                break;
            
                case "12":
		    $day = date("Y-m-d", time() + 24 * 60 *60);
                    $Site = "US";
                    $sql_1 = "select Id,accountId,ForeverListingTime,ForeverListingChinaTime from template where status = 7 and (Site = 'US' or Site = 'eBayMotors')";
                break;
            
                case "17":
		    $day = date("Y-m-d", time() + 24 * 60 *60);
                    $Site = "UK";
                    $sql_1 = "select Id,accountId,ForeverListingTime,ForeverListingChinaTime from template where status = 7 and Site = 'UK'";
                break;
            
                case "18":
		    $day = date("Y-m-d", time() + 24 * 60 *60);
                    $Site = "Germany";
                    $sql_1 = "select Id,accountId,ForeverListingTime,ForeverListingChinaTime from template where status = 7 and (Site = 'Germany' or Site = 'France')";
                break;
            }
	    
	    //$sql_1 = "select scheduleTemplateName,accountId from template where status = 2 and Site = '".$Site."'";
            $this->log("calculateForeverListingSchedule-".$Site.".html", $sql_1."<br>");
            $result_1 = mysql_query($sql_1, Cron::$database_connect);
            $i = 0;
            while($row_1 = mysql_fetch_assoc($result_1)){
		$sql_2= "select count(*) as num from items where Status = 2 and TemplateID = ".$row_1['Id'];
		$result_2 = mysql_query($sql_2, Cron::$database_connect);
		$row_2 = mysql_fetch_assoc($result_2);
		if($row_2['num'] > 0){
		    $this->log("calculateForeverListingSchedule-".$Site.".html", $row_1['Id']." has active listings.<br>");
		    continue;
		}
		$template = new Template($row_1['accountId']);
		//$local_time = $day." ".$row_1['ForeverListingTime'];
		$china_time = $day." ".substr($template->getSiteTime($Site, "1983-11-16", $row_1['ForeverListingTime']), 11, 5);
		$local_time = $template->getLocalTimeByChinaTime($Site, $china_time);
		//$china_time = $day." ".$row_1['ForeverListingChinaTime'];
		$item_id = $template->changeTemplateToItem($row_1['Id'], $china_time, $local_time, 1);
		$this->log("calculateForeverListingSchedule-".$Site.".html", "t:".$row_1['Id']." ==> i:".$item_id.". BeiJing:".$china_time.", ".$Site.": ".$local_time."<br>");
		$this->log("calculateForeverListingSchedule-".$Site.".html", "<br><font color='red'>++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++</font><br>");
	    }
	}
    }
    
    public function calculateShareTemplateForeverListingSchedule(){
	echo date("H")."\n";
	if(in_array(date("H"), array('02', '12', '17' ,'18'))){
            require_once __DOCROOT__ . '/module/template.php';
            $today = date("Y-m-d");
            //$Site = $argv[2];
            switch(date("H")){
                case "02":
		    $day = date("Y-m-d", time() + 24 * 60 *60);
                    $Site = "Australia";
                    $sql_1 = "select Id,ForeverListingTime,ForeverListingChinaTime from share_template where status = 7 and Site = 'Australia'";
                break;
            
                case "12":
		    $day = date("Y-m-d", time() + 24 * 60 *60);
                    $Site = "US";
                    $sql_1 = "select Id,ForeverListingTime,ForeverListingChinaTime from share_template where status = 7 and (Site = 'US' or Site = 'eBayMotors')";
                break;
            
                case "17":
		    $day = date("Y-m-d", time() + 24 * 60 *60);
                    $Site = "UK";
                    $sql_1 = "select Id,ForeverListingTime,ForeverListingChinaTime from share_template where status = 7 and Site = 'UK'";
                break;
            
                case "18":
		    $day = date("Y-m-d", time() + 24 * 60 *60);
                    $Site = "Germany";
                    $sql_1 = "select Id,ForeverListingTime,ForeverListingChinaTime from share_template where status = 7 and (Site = 'Germany' or Site = 'France')";
                break;
            }
	    
	    //$sql_1 = "select scheduleTemplateName,accountId from template where status = 2 and Site = '".$Site."'";
            $this->log("calculateShareTemplateForeverListingSchedule-".$Site.".html", $sql_1."<br>");
            $result_1 = mysql_query($sql_1, Cron::$database_connect);
            //$i = 0;
	    $tmp_time = "";
            while($row_1 = mysql_fetch_assoc($result_1)){
		$template = new Template($row_1['accountId']);
		//$local_time = $day." ".$row_1['ForeverListingTime'];
		$china_time = $day." ".substr($template->getSiteTime($Site, "1983-11-16", $row_1['ForeverListingTime']), 11, 5);
		//$china_time = date("Y-m-d H:i:s", strtotime($china_time) + ($i * (2 * 60 * 60)));
		$local_time = $template->getLocalTimeByChinaTime($Site, $china_time);
		//$china_time = $day." ".$row_1['ForeverListingChinaTime'];
		$item_id = $template->changeShareTemplateToItem($row_1['Id'], $china_time, $local_time, 1);
		$this->log("calculateShareTemplateForeverListingSchedule-".$Site.".html", "t:".$row_1['Id']." ==> i:".$item_id.". BeiJing:".$china_time.", ".$Site.": ".$local_time."<br>");
		$this->log("calculateShareTemplateForeverListingSchedule-".$Site.".html", "<br><font color='red'>++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++</font><br>");
		//$i++;
	    }
	}
    }
    
    public function dealSkuStatusMessage(){
    	//ini_set('include_path', '../');
        require_once __DOCROOT__ . '/Stomp.php';
        require_once __DOCROOT__ . '/Stomp/Message/Map.php';
        $status_array = array(/*'new'=> 0,
                              'waiting for approve'=> 1,
                              'active'=> 2,
                              'out of stock'=> 3,*/
                              'under review'=> 4,
                              'inactive'=> 5);
        
        $consumer = new Stomp($this->config['service']['activeMQ']);
        $consumer->clientId = "eBayListingSkuStatus";
        $consumer->connect();
        $consumer->subscribe($this->config['queue']['skuStatus'], array('transformation' => 'jms-map-json'));
        //for($i=0; $i<60; $i++){
            $msg = $consumer->readFrame();
            if ($msg != null) {
                    //echo "Message '$msg->body' received from queue\n";
                    print_r($msg->map);
                    if(array_key_exists($msg->map['status'], $status_array)){
                        $consumer->ack($msg);
                        if(strpos($msg->map['skus'], ',')){
                            $skus = explode(',', $msg->map['skus']);
                            foreach($skus as $sku){
                                $sql = "update template set status = ".$status_array[$msg->map['status']]." where SKU = '".$sku."'";
                                echo $sql."\n";
                                $result = mysql_query($sql, Cron::$database_connect);
                            }
                        }else{
                            $sql = "update template set status = ".$status_array[$msg->map['status']]." where SKU = '".$msg->map['skus']."'";
                            echo $sql."\n";
                            $result = mysql_query($sql, Cron::$database_connect);
                        }
                    }
            }
            //sleep(1);
        //}
        $consumer->disconnect();
    }
    
    public function dealSkuOutOfStockMessage(){
        require_once __DOCROOT__ . '/Stomp.php';
        require_once __DOCROOT__ . '/Stomp/Message/Map.php';
        
        $consumer = new Stomp($this->config['service']['activeMQ']);
        $consumer->clientId = "eBayListingSkuOutOfStock";
        $consumer->connect();
        $consumer->subscribe($this->config['topic']['skuOutOfStock'], array('transformation' => 'jms-map-json'));
        //for($i=0; $i<6; $i++){
            $msg = $consumer->readFrame();
            if ( $msg != null) {
                //echo "Message '$msg->body' received from queue\n";
                //print_r($msg->map);
                $consumer->ack($msg);
                $sku_array = explode(",", $msg->map['sku']);
                foreach($sku_array as $sku){
                    $sql = "update template set status = 3 where SKU = '".$sku."' and status = 6";
                    echo $sql."\n";
                    $result = mysql_query($sql, Cron::$database_connect);
                }
            }else{
                echo date("Y-m-d H:i:s")." no message\n";
            }
            //sleep(1);
        //}
        $consumer->disconnect();
    }
    
    public function temp(){
        /*
        $sql = "select t.Id as TID,i.Id as IID from template as t left join items as i on t.SKU = i.SKU and t.Title = i.Title and t.accountId = i.accountId";
        $result = mysql_query($sql, Cron::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $sql_1 = "update items set TemplateID = ".$row['TID']." where Id = ".$row['IID'];
            $result_1 = mysql_query($sql_1, Cron::$database_connect);
            echo $sql_1."\n";
            //exit;
        }
        */
        $sql = "select name from account where status = 1";
	$result = mysql_query($sql, Cron::$database_connect);
	while($row = mysql_fetch_assoc($result)){
            if($row['name'] == "ymca200808"){
                continue;
            }
            $command = "php /export/eBayListing/service.php getSellerList ".$row['name']." End 2009-12-17 2010-01-17 > /dev/null";
            echo $command."\n";
            exec($command);
        }
    }
}

$cron = new Cron();
$action = $argv[1];
$cron->$action();
?>