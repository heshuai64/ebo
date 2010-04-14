<?php
class Cron{
    public static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaylisting';
    const LOG_DIR ='/export/eBayListing/log/cron';
    
    public function __construct(){
        set_time_limit(1800);
        Cron::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

        if (!Cron::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(Cron::$database_connect);
            exit;
        }
	
        mysql_query("SET NAMES 'UTF8'", Cron::$database_connect);
	
        if (!mysql_select_db(self::DATABASE_NAME, Cron::$database_connect)) {
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
        if(!file_exists(self::LOG_DIR."/".date("Ymd"))){
            mkdir(self::LOG_DIR."/".date("Ymd"), 0777);
        }
        file_put_contents(self::LOG_DIR."/".date("Ymd")."/".$file_name, $data, FILE_APPEND);
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
        US: 12:00:00 18
        UK: 07:00:00 13
        AU: 22:00:00 04
        FR: 06:00:00 12
    */
    public function calculateListingSchedule(){
        global $argv;
        require_once 'module/template.php';
        $template = new Template();
        $today = date("Y-m-d");
        $Site = $argv[2];
        $sql_1 = "select * from template where Site = '".$Site."' and ScheduleStartDate = '".$today."'";
        echo $sql_1."\n";
        $result_1 = mysql_query($sql_1, Cron::$database_connect);
	while($row_1 = mysql_fetch_assoc($result_1)){
            $sql_2 = "select * from schedule_template where name = '".$row_1['scheduleTemplateName']."' and account_id = '".$row_1['accountId']."'";
            echo $sql_2."\n";
            $result_2 = mysql_query($sql_2, Cron::$database_connect);
            while($row_2 = mysql_fetch_assoc($result_2)){
                if(date("D") == $row_2['day']){
                    $local_time = $template->getSiteTime($row_1['Site'], $today, $row_2['time']);
                    //$item_id = $template->changeTemplateToItem($row_1['Id'], $local_time, $today . " " .$row_2['time']);
                    $this->log("calculateListingSchedule.log", "t:".$row_1['Id']." ==> i:".$item_id.". ".$Site.":".$local_time.", BeiJing:".$today . " " .$row_2['time']."\n");
                }
            }
            //$sql_3 = "update template set ScheduleStartDate = '".$today."' where Id = ".$row_1['Id'];
            //$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
        }
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