<?php
$data = array();
$count = 0;
//$seller_id = "";

class AIE{
	private static $database_connect;
	const DATABASE_HOST = 'localhost';
	const DATABASE_USER = 'root';
	const DATABASE_PASSWORD = '';
	const DATABASE_NAME = 'uspoon';
	//const DATABASE_PASSWORD = '5333533';
	//const DATABASE_NAME = 'test';
	const NO_SYNC_AIE_FILE = '/export/uspoon/shell/NoSynchronized.txt';
	const NEW_AIE_FILE_DIR = '/export/uspoon/aiefilelist/';
	const SUCCESS_NEW_AIE_FILE_DIR = '/export/uspoon/aiefilelist/success/';
	
	private $currency_path_array = array();
	private $currency_path_string = "";
	private $ignore_field = array('seller_no', 'seller_id', 'aie_file_name', 'aie_file_path', 'last_update_time', 'last_generate_time', 'description_before_begin','description','description_after_end'); 
	private $config = array();
	private $seller_id;
	private $seller_no;
	
	public function __construct(){
		self::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

		if (!self::$database_connect) {
		    echo "Unable to connect to DB: " . mysql_error(self::$database_connect);
		    exit;
		}
		
		mysql_query("SET NAMES 'UTF8'", self::$database_connect);
		  
		if (!mysql_select_db(self::DATABASE_NAME, self::$database_connect)) {
		    echo "Unable to select mydbname: " . mysql_error(self::$database_connect);
		    exit;
		}
		
		$ini_array = parse_ini_file("aie.ini", true);
		$this->config = $ini_array;
	}
	
	public function set_path($path){
		$this->currency_path_array = explode('/', $path);
		$this->currency_path_string = implode('/', $this->currency_path_array);
	}
	
	public function push($path){
		array_push($this->currency_path_array, $path);
		$this->currency_path_string = implode('/', $this->currency_path_array);
		print_r($this->currency_path_array);
	}
	
	public function pop(){
		array_pop($this->currency_path_array);
		$this->currency_path_string = implode('/', $this->currency_path_array);
	}
	
	public function process_dir($dir_name = ''){
		global $argv;

		if(!empty($argv[2])){
			$this->seller_id = $argv[2];
			$ini_array = parse_ini_file("aie.ini", true);
			$this->set_path($ini_array['aieDir'][$argv[2]]);
			unset($argv[2]);
		}
		
		if ($handle = opendir($this->currency_path_string)) {
		    while (false !== ($file = readdir($handle))) {
		    	if($file != '.' && $file != '..'){
		    		if(false === strpos($file, '.')){
		    			//echo "YYY";
		    			$yy = explode('/', $file);
		    			$path = $yy[count($yy)-1];
		    			$this->push($path);
			    		if(is_dir($this->currency_path_string)){
			    			$this->process_dir($this->currency_path_string);
			    			$this->pop();
			    		}else{
			    			$this->pop();
			    			echo '<font color="red">'.$file.'<br>';
			        echo '<br>';
			    			}
		    		}else{
			        //echo $file;
			        //echo '<br>';
			        echo $this->currency_path_string . '/' .$file . "\n";
			        flush();
			        $this->process_file($file, $this->currency_path_string);
		    			}
		    		flush();
		    		}
		    	  }
		    closedir($handle);
		}
	}
	
	public function process_file($file_name='', $file_path=''){
		global $argv;
		//print_r($argv);
		if(!empty($argv[3]) && !empty($argv[4])){
			$this->seller_id = $argv[2];
			$file_path = $argv[3];
			$file_name = $argv[4];
			unset($argv[2]);
			unset($argv[3]);
			unset($argv[4]);
		}
		
		$file_path = trim($file_path);
		$file_name = trim($file_name);
		//echo $file_path.'/' .$file_name;
		//exit;
		//$file_name = 'a07052200ux0011.aie';
		//$file_path = '/data/unifiedspoonfeeder/actionace007/SpoonFeeder/Auctions/2007/200705';
		if(strpos($file_name, '.aie') == false){
			return 0;
		}
		
		$description_step = 0;
		//$mark = false;
		//global $seller_id;
		//global $data;
		//global $count;
		
		$handle = @fopen($file_path.'/' .$file_name, "r");
		if ($handle) {
	    while (!feof($handle)) {
        $buffer = trim(fgets($handle, 4096));
        //var_dump($buffer);
        
        if(!empty($buffer) && $buffer[0] == "[" && $buffer[strlen($buffer)-1] == "]"){
        	$var_name = substr($buffer, 1, -1);
        	if($var_name == "DESCRIPTION"){
        		$description_step = 1;
        	}elseif($var_name == "DESCRIPTIONFONT"){
        		$description_step = 0;
        				}
        	//$count++;
        	//echo $var_name;
        	//echo "<br>";
        	continue;
       	}else if(!empty($buffer)){
       		
       		if($buffer == "<!-- Description Begin -->"){
       			$description_step = 2;
       			continue;
       		}elseif($buffer == "<!-- Description End -->"){
       			$description_step = 3;
       			continue;
       		}/*elseif(strpos($buffer, 'Description:') || strpos($buffer, 'Features:')){
       			$description_step = 2;
       			//$mark = true;
       					}
       					*/
       	 if($description_step > 0){
	       		switch($description_step){
		        	case 1:
		        		@$data['description_before_begin'] .= $buffer;
		        	break;
		        		
		        	case 2:
		        		@$data['description'] .= $buffer;
		        	break;
		        		
		        	case 3:
		        		@$data['description_after_end'] .= $buffer;
		        	break;
		        			 }
       					}
       		//echo $var_name." : ".var_dump($var);
       		//echo "<br>";
       		@$data[$var_name] .= $buffer;
       		 		}
		    	}
		   fclose($handle);
		}
		unset($data['DESCRIPTION']);
		$data['aie_file_name'] = $file_name;
		$data['aie_file_path'] = $file_path;
		$data['seller_id'] = $this->seller_id;
		$sql_1 = "select seller_no from seller_id_no_map where seller_id = '".$this->seller_id."'";
		$result_1 = mysql_query($sql_1, self::$database_connect);
		$row_1 = mysql_fetch_assoc($result_1);
		$data['seller_no'] = $row_1['seller_no'];
		$data['last_generate_time'] = date("Y-m-d H:i:s", time() + (2 * 60));
		//$data['last_update_time'] = date("Y-m-d H:i:s");
		//var_dump($data);
		//exit;
		//if($mark){
		//	var_dump($file_name);
		//}
		$sql = "delete from aie_repository where seller_no = '".$data['seller_no']."' and aie_file_name = '".$file_name."' and aie_file_path = '".$file_path."'";
		$result = mysql_query($sql, self::$database_connect);
		
		$field = "";
		$field_value = "";
		foreach($data as $key=>$value){
			$field .= str_replace(" ", "_", $key).",";
			$field_value .= "'".mysql_real_escape_string(iconv("ISO-8859-1","UTF-8", $value))."',";
			//$field_value .= "'".mysql_real_escape_string($value)."',";
		}
		$field = substr($field, 0, -1);
		$field_value = substr($field_value, 0, -1);
		$sql = "insert into aie_repository (".$field.") values (".$field_value.")";
		$result = mysql_query($sql, self::$database_connect);
		//echo $sql;
		//exit;
		return $result;
		//var_dump(self::$database_connect);
	}
	
	public function process_description(){
		$sql = "select aie_file_name,aie_file_path,description_before_begin from aie_repository where description = ''";	
		$result = mysql_query($sql, self::$database_connect);
		$i = 1;
		while($row = mysql_fetch_assoc($result)){
			if(strpos($row['aie_file_name'], '.out')){
				continue;
			}
			if($pos = strpos($row['description_before_begin'], '<p>Features:')){
				$description_before_begin = substr($row['description_before_begin'], 0, $pos);
				$description = substr($row['description_before_begin'], $pos);
			}elseif($pos = strpos($row['description_before_begin'], '<p>Description:')){
				$description_before_begin = substr($row['description_before_begin'], 0, $pos);
				$description = substr($row['description_before_begin'], $pos);
			}else{
				continue;
			}
			//var_dump($row['aie_file_path']);
			//var_dump($row['aie_file_name']);
			//var_dump($description_before_begin);
			//var_dump($description);
			echo $i;
			echo "\n";
			echo $row['aie_file_path']. '/'. $row['aie_file_name'];
			echo "\n";
			$sql_1 = "update aie_repository set description_before_begin = '".mysql_real_escape_string($description_before_begin)."',
			description='".mysql_real_escape_string($description)."' where aie_file_name = '".$row['aie_file_name']."' and aie_file_path = '".$row['aie_file_path']."'";
			//echo $sql;
			$result_1 = mysql_query($sql_1, self::$database_connect);
			flush();
			$i++;
		}
	}
	
	private function getSellerNo($seller_id){
		$sql = "select seller_no from seller_id_no_map where seller_id = '".$seller_id."'";	
		$result = mysql_query($sql, self::$database_connect);
		$row = mysql_fetch_assoc($result);
		return $row['seller_no'];
	}
	
	public function generate_file(){
		global $argv;
		$line = '';
		$mark = true;
		
		$seller_id = (empty($_GET['id'])?$argv[2]:$_GET['id']);
		if(empty($seller_id)){
			$sql = "select * from aie_repository where last_update_time > last_generate_time";	
		}else{
			$this->seller_id = $seller_id;
			$this->seller_no = $this->getSellerNo($seller_id);
			$sql = "select * from aie_repository where seller_no = '".$this->seller_no."' and last_update_time > last_generate_time";	
		}
		//$sql = "select * from aie_repository where aie_file_path='/data/unifiedspoonfeeder/actionace007/SpoonFeeder/Auctions/2007/200705' and aie_file_name='a07052200ux0011_FS.aie'";
		//echo $sql;
		//exit;
		$result = mysql_query($sql, self::$database_connect);
		$num_rows = mysql_num_rows($result);
		if(empty($argv[3]) && $num_rows > 1000){
			file_put_contents("/export/uspoon/log/bigUpdate.html", date("Y-m-d H:i:s") . ": " .$sql."\n", FILE_APPEND);
			return 0;
		}
		
		while($row = mysql_fetch_assoc($result)){
			$fp = fopen($row['aie_file_path'].'/'.$row['aie_file_name'], 'w');
			foreach($row as $key=>$value){
				if(!in_array($key, $this->ignore_field)){
					fwrite($fp, '['.str_replace("_", " ", $key).']'."\n");
					if($mark == true && $value == "0.00"){
						fwrite($fp, "0\n"."\n");
					}else{
						fwrite($fp, iconv("UTF-8", "ISO-8859-1", $value)."\n"."\n");
						//fwrite($fp, $value."\n"."\n");
					}
					if($key == "SUBTITLESCROLL"){
						fwrite($fp, '[DESCRIPTION]'."\n");
						fwrite($fp, $row['description_before_begin']."\n<!-- Description Begin -->\n".utf8_decode($row['description'])."\n<!-- Description End -->\n".utf8_decode($row['description_after_end'])."\n"."\n");
					}
				}
			}
			fclose($fp);
			
			$sql_1 = "update aie_repository set last_generate_time = '".date('Y-m-d H:i:s')."' where seller_no = '".$this->seller_no."' and aie_file_name = '".$row['aie_file_name']."' and aie_file_path = '".$row['aie_file_path']."'";
			echo "<font color='red'>generate file ". $row['aie_file_path']. "/".$row['aie_file_name']."</font><br>";
			//echo $sql_1;
			//echo "<br>";
			$result_1 = mysql_query($sql_1, self::$database_connect);
			
			$line .= 'rsync -avz --password-file=/etc/rsync_client.pass '.$row['aie_file_path'].'/'.$row['aie_file_name'].' '.$this->config['virtualHost'][$row['seller_id']].'::'.str_replace('/data/unifiedspoonfeeder/'.$row['seller_id'].'/SpoonFeeder', 'spoonfeeder', $row['aie_file_path']).'/'.$row['aie_file_name']."\n";
		}
		if(strlen($line) > 3){
			$this->generate_sync_file($line);
		}
	}
	
	public function getChangeSkus(){
		$str = "?endtime=".urlencode(date("Y-m-d H:i"))."&minute=30";
		//endtime=2009-08-27&minute=60
		$return_json = file_get_contents($this->config['inventory']['getChangeSkusUrl'] . $str);
		echo "<font color='red'>".date("Y-m-d H:i:s")." return: ".$return_json;
		echo "</font><br>";
		$json = json_decode($return_json);
		//print_r($json);
		//var_dump($json);

		if(is_array($json)){
			$sql = "select seller_no from seller_id_no_map where status = '1'";	
			$result = mysql_query($sql, self::$database_connect);
			while($row = mysql_fetch_assoc($result)){
				$seller_no = $row['seller_no'];
				
				foreach($json as $j){
						$sql_1 = "select count(*) as num from aie_change where sku = '".$j->skuid."' and seller_no = '".$seller_no."'";
						$result_1 = mysql_query($sql_1, self::$database_connect);
						$row_1 = mysql_fetch_assoc($result_1);
						if($row_1['num'] > 0){
								$sql_2 = "update aie_change set updatetime = '".$j->updatetime."',status = '0' where sku = '".$j->skuid."' and seller_no = '".$seller_no."'";
								echo "<font color='green'>getChangeSkus</font><br>";
								echo $sql_2;
								echo "<br>";
						}else{
								$sql_2 = "insert into aie_change (sku,updatetime,seller_no) values('".$j->skuid."','".$j->updatetime."','".$seller_no."')";
								echo "<font color='green'>getChangeSkus</font><br>";
								echo $sql_2;
								echo "<br>";
						}
						$result_2 = mysql_query($sql_2, self::$database_connect);
				}
			}
		}else{
			echo $this->config['inventory']['getChangeSkusUrl'] . $str ."<br>";
			var_dump($json);
		}
		echo "<br>";
		echo "<br>";
		echo "<br>";
	}
	
	public function checkChangeSKus(){
		global $argv;
		if(!empty($argv[2])){
			$minute = $argv[2];
		}else{
			$minute = 120;
		}
		$str = "?endtime=".urlencode(date("Y-m-d H:i", time() - (1 * 60 * 60)))."&minute=".$minute;
		//endtime=2009-08-27&minute=60
		//echo $str."\n";
		
		$return_json = file_get_contents($this->config['inventory']['getChangeSkusUrl'] . $str);
		echo "<font color='red'>".date("Y-m-d H:i:s")." return: ".$return_json;
		echo "</font><br>";
		$json = json_decode($return_json);
		//print_r($json);
		//var_dump($json);

		if(is_array($json)){
			$sql = "select seller_no from seller_id_no_map where status = '1'";	
			$result = mysql_query($sql, self::$database_connect);
			while($row = mysql_fetch_assoc($result)){
				$seller_no = $row['seller_no'];
				
				foreach($json as $j){
						$sql_1 = "select count(*) as num from aie_change where sku = '".$j->skuid."' and updatetime = '".$j->updatetime."' and seller_no = '".$seller_no."'";
						$result_1 = mysql_query($sql_1, self::$database_connect);
						$row_1 = mysql_fetch_assoc($result_1);
						if($row_1['num'] > 0){
								//$sql_2 = "update aie_change set updatetime = '".$j->updatetime."',status = '0' where sku = '".$j->skuid."' and seller_no = '".$seller_no."'";
								//echo "<font color='green'>".date("Y-m-d H:i:s").": getChangeSkus</font><br>";
								//echo $sql_2;
								//echo "<br>";
						}else{
								$sql_2 = "insert into aie_change (sku,updatetime,seller_no) values('".$j->skuid."','".$j->updatetime."','".$seller_no."')";
								echo "<font color='green'>check change skus</font><br>";
								echo $sql_2;
								echo "<br>";
						}
						$result_2 = mysql_query($sql_2, self::$database_connect);
				}
			}
		}else{
			echo $this->config['inventory']['getChangeSkusUrl'] . $str ."<br>";
			var_dump($json);
		}
		echo "<br>";
		echo "<br>";
		echo "<br>";
	}
	
	public function getSkuEbayInfo($sku){
		//$sku = "a08120100ux0001";
		$sql = "select title_id from seller_title_map where seller_id = '".$this->seller_id."'";
		$result = mysql_query($sql, self::$database_connect);
		$row = mysql_fetch_assoc($result);
		$title_id = 'title'.$row['title_id'];
		//echo $sql;
		//var_dump($row);
		//echo "<br>";
		
		$json = json_decode(file_get_contents($this->config['inventory']['getSkuEbayInfoUrl'] . '?skuid='.$sku));
		if(is_array($json)){
			
			echo "<font color='green'>".date("Y-m-d H:i:s").": getSkuEbayInfo</font><br>";
			if(!empty($this->seller_no)){
				$sql = "update aie_repository set description = '".mysql_real_escape_string($json[0]->description)."',TITLE = '". mysql_real_escape_string($json[0]->$title_id)."' where seller_no = '".$this->seller_no."' and (aie_file_name = '".$sku.".aie' or aie_file_name = '".$sku."_FS.aie' or aie_file_name = '".$sku."_STORE.aie')";
			}else{
				$sql = "update aie_repository set description = '".mysql_real_escape_string($json[0]->description)."',TITLE = '". mysql_real_escape_string($json[0]->$title_id)."' where aie_file_name = '".$sku.".aie' or aie_file_name = '".$sku."_FS.aie' or aie_file_name = '".$sku."_STORE.aie'";
			}
			echo $sql;
			echo "<br>";
			$result = mysql_query($sql, self::$database_connect);
			//var_dump($result);
			return $result;
		}else{
			echo "<font color='red'>Error: getSkuEbayInfo service ".$this->config['inventory']['getSkuEbayInfoUrl'] . '?skuid='.$sku."<br>";
			echo "return: ".var_dump($json);
			echo "</font><br><br><br>";
			return false;
		}
	}
	
	
	public function proecssTDChange(){
			global $argv;

			if(!empty($argv[2])){
				//echo "ps aux | grep 'proecssTDChange ".$argv[2]."' | wc -l\n";
				//exec("ps aux | grep 'proecssTDChange ".$argv[2]."' | wc -l", $shell_result);
				//echo escapeshellcmd("ps aux | grep 'proecssTDChange ".$argv[2]."' | wc -l")."\n";
				//exec("ps aux | grep 'php' | wc -l", $shell_result);
				//var_dump($shell_result);
				//exit;
				//if($shell_result > 1){
				//	echo date("Y-m-d H:i:s").": proecssTDChange ".$argv[2]." is runing.\n";
				//	return 0;
				//}
				$this->seller_id = $argv[2];
				$this->seller_no = $this->getSellerNo($argv[2]);
			}
			
			$sql = "select * from aie_change where status = '0' and seller_no = '".$this->seller_no."' order by id";
			$result = mysql_query($sql, self::$database_connect);
			while($row = mysql_fetch_assoc($result)){
				$a = $this->getSkuEbayInfo($row['sku']);
				//var_dump($a);
				if($a){
					$sql_1 = "update aie_change set status = '1' where id = '".$row['id']."'";
					echo "<font color='green'>".date("Y-m-d H:i:s").": proecssTDChange</font><br>";
					echo $sql_1;
					echo "<br>";
					$result_1 = mysql_query($sql_1, self::$database_connect);
				}else{
					echo "<br><font color='red'>Error: ".$row['sku']." update title and description failure.";
					echo "</font><br>";
				}
			}
	}
	
	private function generate_sync_file($data){
		//rsync -avz --password-file=/etc/rsync_client.pass /data/unifiedspoonfeeder/actionace007/SpoonFeeder/Auctions/2009/200907/a09070700ux0085.aie  192.168.5.121::spoonfeeder/Auctions/2009/200907/a09070700ux0085.aie
		$result = file_put_contents($this->config['m']['shell'].$this->seller_id.date("YmdHis").'.txt', $data);
		echo "<font color='green'>".$this->seller_id."  " .date("Y-m-d H:i:s").": generate sync file</font><br><br><br>";
		//echo $this->config['m']['shell'].$this->seller_id.date("YmdHis");
		//echo "<br>";
	}
	
	public function temp(){
		$array = array();
		$temp = '';
		$fp = fopen('sku.csv', 'w');
		
		$data = "SKU\n";
		$sql = "select seller_id,aie_file_path,aie_file_name,description_before_begin,description from aie_repository where description = '' order by aie_file_name";	
		$result = mysql_query($sql, self::$database_connect);
		while($row = mysql_fetch_assoc($result)){
			//echo "<font color='red'>description_before_begin:</font><br>".$row['description_before_begin'];
			//echo "<font color='red'>description:</font><br>".$row['description'];
			
			$sku = substr($row['aie_file_name'], 0, 15);
			//echo $sku;
			//echo "<br>";
			//flush();
			if(!in_array( $sku, $array)){
				$array[] = $sku;
				//$data .= $sku."\n";
				fwrite($fp, $sku."\n");
				fwrite($fp, ",".$row['aie_file_path']."/".$row['aie_file_name']."\n");
			}else{
			
				if($temp == $sku){
					//$data .= ",".$row['seller_id']."\n";
					fwrite($fp, ",".$row['aie_file_path']."/".$row['aie_file_name']."\n");
				}
				
			}
			$temp = $sku;
		}
		
		fclose($fp);
		exit;
		
		header("Content-type: application/x-msdownload");
		header("Content-Disposition: attachment; filename=sku.csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo $data;
	}
	
	public function importCsv(){
			$handle = fopen("revise_pricing_uk.csv", "r");
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			     //echo $data[$c]
				$aie_file_name = $data[1];
				$t = explode('\\', $aie_file_name);
				
				$aie_file_name = $t[count($t)-1];
				$aie_file_path = "/data/unifiedspoonfeeder/costcocity002/SpoonFeeder/Auctions/".$t[3]."/".$t[4];
				
				$MINIMUM_BID_PRICE = $data[3];
				$INTERNATIONAL_SHIPPING_COST2 = $data[8];
				$INTERNATIONAL_ADDITIONAL_SHIPPING_COST2 = $data[9];
				$sql = "update aie_repository set MINIMUM_BID_PRICE='".$MINIMUM_BID_PRICE."',
				INTERNATIONAL_SHIPPING_COST2='".$INTERNATIONAL_SHIPPING_COST2."',
				INTERNATIONAL_ADDITIONAL_SHIPPING_COST2='".$INTERNATIONAL_ADDITIONAL_SHIPPING_COST2."' 
				where aie_file_name = '".$aie_file_name."' and aie_file_path = '".$aie_file_path."' and seller_id = 'costcocity002'";
				echo $sql;
				echo "<br>";
				$result = mysql_query($sql, self::$database_connect);
			}
			fclose($handle);
	}
	
	public function noSyncAieFileList(){
		$handle = @fopen(self::NO_SYNC_AIE_FILE, "r");
		if ($handle) {
		  while (!feof($handle)) {
			  $buffer = fgets($handle, 4096);
			  echo $buffer;
		    }
		  fclose($handle);
		}
	}
	
	public function newAieFileDeal(){
		$failure = "";
		if ($dh = opendir(self::NEW_AIE_FILE_DIR)) {
	   while (($file = readdir($dh)) !== false) {
	   	if($file != '' && $file != '.' && $file != '..' && !is_dir(self::NEW_AIE_FILE_DIR.$file)){
	   		//var_dump($file);
	   		//continue;
				  $handle = @fopen(self::NEW_AIE_FILE_DIR.$file, "r");
					if ($handle) {
						  while (!feof($handle)) {
							  $buffer = fgets($handle, 4096);
							  $array = explode("/", $buffer);
							  $this->seller_id = $array[3];
							  	
							  $pos = strripos($buffer, "/");
							  $file_path = substr($buffer, 0, $pos);
							  $file_name = substr($buffer, $pos+1);
							  
							  if(strpos($file_name, "aie") == false){
							  		continue;
							  	 }
							  //var_dump(array($file_path, $file_name));
							  echo $file_path."/".$file_name."<br>";
							  $result = $this->process_file($file_name, $file_path);
							  if(!$result){
							  		$failure .= $buffer."\n";
							 	 }
						    }
						  fclose($handle);
					}
					rename(self::NEW_AIE_FILE_DIR.$file, self::SUCCESS_NEW_AIE_FILE_DIR.$file);
	   			}
	        }
	   closedir($dh);
	    }
	  if(@strlen($buffer) > 0){
	    	file_put_contents(self::NEW_AIE_FILE_DIR.$file.'-failure', $failure);
	    }
	}
	
	public function checkAll(){
		echo "<br><font color='blue'>--------------------------  Start On ".date("Y-m-d H:i:s")."  -----------------------------------------------------</font><br>";
		$str = "?endtime=".urlencode(date("Y-m-d H:i", time() - (1 * 60 * 60)))."&minute=120";
		//endtime=2009-08-27&minute=60
		$return_json = file_get_contents($this->config['inventory']['getChangeSkusUrl'] . $str);
		//echo "<font color='red'>".date("Y-m-d H:i:s")." return: ".$return_json;
		//echo "</font><br>";
		$json = json_decode($return_json);
		$error = false;
		if(is_array($json)){
			$sql_1 = "select seller_no,seller_id from seller_id_no_map where status = '1'";	
			$result_1 = mysql_query($sql_1, self::$database_connect);
			while($row_1 = mysql_fetch_assoc($result_1)){
				echo "<br>--------------------------  ".date("Y-m-d H:i:s")." ".$row_1['seller_id']." Start ----------------------------------<br>";
				$sql_2 = "select title_id from seller_title_map where seller_id = '".$row_1['seller_id']."'";
				$result_2 = mysql_query($sql_2, self::$database_connect);
				$row_2 = mysql_fetch_assoc($result_2);
				$title_id = 'title'.$row_2['title_id'];
		
				foreach($json as $j){
						$td_json = json_decode(file_get_contents($this->config['inventory']['getSkuEbayInfoUrl'] . '?skuid='.$j->skuid));
						if(is_array($td_json)){
							$sql_3 = "select TITLE,description from aie_repository where seller_no = '".$row_1['seller_no']."' and aie_file_name = '".$j->skuid.".aie'";
							$result_3 = mysql_query($sql_3, self::$database_connect);
							$row_3 = mysql_fetch_assoc($result_3);
							
							if(empty($row_3['TITLE']) && empty($row_3['description'])){
								echo "<font color='purple'> Error: ".$row_1['seller_id']." ".$j->skuid.", new aie file, no sync to 119.";
								echo "</font><br>";
								$error = true;
								continue;
							}
							
							if($row_3['TITLE'] != $td_json[0]->$title_id){
								echo "<br><font color='red'>TITLE Error: ".$row_1['seller_id']." ".$j->skuid.", update time: ".$j->updatetime;
								echo "</font><br>";
								echo "<h2>Product Center: </h2><font color='green'>".$td_json[0]->$title_id."</font><br>";
								echo "<h2>Local: </h2><font color='green'>".$row_3['TITLE']."</font><br>";
								$error = true;
							}elseif($row_3['description'] != $td_json[0]->description){
								echo "<br><font color='red'>Description Error: ".$row_1['seller_id']." ".$j->skuid.", update time: ".$j->updatetime;
								echo "</font><br>";
								$error = true;
							  $l_description = $row_3['description'];
							  $pc_description = $td_json[0]->description;
								$k = 0;
								$temp = "";
								if(strlen($pc_description) > strlen($l_description)){
									for($i = 0; $i < strlen($pc_description); ){
										    /*
											if($k >= strlen($l_description)){
												 $temp .= substr($pc_description, $k);
													break;
											}
											*/
											if($pc_description[$i] != $l_description[$k]){
												$temp .= $pc_description[$i];
												$i++;
											}else{
												$i++;
												$k++;
											}
									}
									echo "<h2>Product Center: Add </h2><font color='green'>".$temp."</font><br>";
								}else{
									for($i = 0; $i < strlen($l_description); ){
										     /*
											if($k >= strlen($pc_description)){
												 $temp .= substr($l_description, $k);
													break;
											}
											*/
											if($l_description[$i] != $pc_description[$k]){
												$temp .= $l_description[$i];
												$i++;
											}else{
												$i++;
												$k++;
											}
									}	
									echo "<h2>Product Center: Delete </h2><font color='green'>".$temp."</font><br>";
								}
								//echo "pc: ".$td_json[0]->description."<br>";
								//echo "local: ".$row_3['description']."<br>";
							}
						}else{
							echo "<font color='red'>Error: ".$this->config['inventory']['getSkuEbayInfoUrl'] . '?skuid='.$j->skuid."<br>";
							echo "return: ".var_dump($td_json);
							echo "</font><br>";
						}
				}
				//if($error == true){
					echo "<br>--------------------------  ".date("Y-m-d H:i:s")." ".$row_1['seller_id']." End ------------------------------------<br>";
				//}
			}
		}else{
			var_dump($json);
		}
		echo "<br><font color='blue'>--------------------------  End On ".date("Y-m-d H:i:s")."  -----------------------------------------------------</font><br>";
	}
	
	public function checkSku(){
			$sql_1 = "select seller_no,seller_id from seller_id_no_map where status = '1'";	
			$result_1 = mysql_query($sql_1, self::$database_connect);
			while($row_1 = mysql_fetch_assoc($result_1)){
						echo "<br>--------------------------  ".date("Y-m-d H:i:s")." ".$row_1['seller_id']." Start ----------------------------------<br>";
						$sql_2 = "select title_id from seller_title_map where seller_id = '".$row_1['seller_id']."'";
						$result_2 = mysql_query($sql_2, self::$database_connect);
						$row_2 = mysql_fetch_assoc($result_2);
						$title_id = 'title'.$row_2['title_id'];
						$td_json = json_decode(file_get_contents($this->config['inventory']['getSkuEbayInfoUrl'] . '?skuid='.$_GET['sku']));
						if(is_array($td_json)){
								$sql_3 = "select TITLE,description from aie_repository where seller_no = '".$row_1['seller_no']."' and aie_file_name = '".$_GET['sku'].".aie'";
								$result_3 = mysql_query($sql_3, self::$database_connect);
								$row_3 = mysql_fetch_assoc($result_3);
								
								if(empty($row_3['TITLE']) && empty($row_3['description'])){
									echo "<font color='purple'> Error: ".$row_1['seller_id']." ".$_GET['sku'].", new aie file, no sync to 119.";
									echo "</font><br>";
									$error = true;
									continue;
								}
								
								if($row_3['TITLE'] != $td_json[0]->$title_id){
									echo "<br><font color='red'>TITLE Error: ".$row_1['seller_id']." ".$_GET['sku'];
									echo "</font><br>";
									echo "<h2>Product Center: </h2><font color='green'>".$td_json[0]->$title_id."</font><br>";
									echo "<h2>Local: </h2><font color='green'>".$row_3['TITLE']."</font><br>";
									$error = true;
								}elseif($row_3['description'] != $td_json[0]->description){
									echo "<br><font color='red'>Description Error: ".$row_1['seller_id']." ".$_GET['sku'];
									echo "</font><br>";
									$error = true;
								  $l_description = $row_3['description'];
								  $pc_description = $td_json[0]->description;
									$k = 0;
									$temp = "";
									if(strlen($pc_description) > strlen($l_description)){
										for($i = 0; $i < strlen($pc_description); ){
											    /*
												if($k >= strlen($l_description)){
													 $temp .= substr($pc_description, $k);
														break;
												}
												*/
												if($pc_description[$i] != $l_description[$k]){
													$temp .= $pc_description[$i];
													$i++;
												}else{
													$i++;
													$k++;
												}
										}
										echo "<h2>Product Center: Add </h2><font color='green'>".$temp."</font><br>";
									}else{
										for($i = 0; $i < strlen($l_description); ){
											     /*
												if($k >= strlen($pc_description)){
													 $temp .= substr($l_description, $k);
														break;
												}
												*/
												if($l_description[$i] != $pc_description[$k]){
													$temp .= $l_description[$i];
													$i++;
												}else{
													$i++;
													$k++;
												}
										}	
										echo "<h2>Product Center: Delete </h2><font color='green'>".$temp."</font><br>";
									}
									//echo "pc: ".$td_json[0]->description."<br>";
									//echo "local: ".$row_3['description']."<br>";
								}
						}else{
								echo "<font color='red'>Error: ".$this->config['inventory']['getSkuEbayInfoUrl'] . '?skuid='.$_GET['sku']."<br>";
								echo "return: ".var_dump($td_json);
								echo "</font><br>";
						}
						echo "<br>--------------------------  ".date("Y-m-d H:i:s")." ".$row_1['seller_id']." End ------------------------------------<br>";
					flush();
			}
	}
	
	public function __destruct(){
		
	}
}

//php aie.php process_dir costcocity002
//php aie.php getChangeSkus                     http://192.168.5.119/aie.php?action=getChangeSkus
//php aie.php proecssTDChange costcocity002     http://192.168.5.119/aie.php?action=proecssTDChange
//php aie.php generate_file costcocity002       http://192.168.5.119/aie.php?action=generate_file&id=actionace007
/*
php aie.php process_dir actionace007
php aie.php process_dir ausluna0001
php aie.php process_dir bestvaluezone
php aie.php process_dir bigbigbargain
php aie.php process_dir buonshopping
php aie.php process_dir ceo1shop 
php aie.php process_dir costcocity002
php aie.php process_dir costcocity003
php aie.php process_dir digitalzone01
 */


/*
if($argv[1] == "process_dir"){
	$ini_array = parse_ini_file("aie.ini", true);
	//print_r($ini_array);
	
	foreach($ini_array['aieDir'] as $key=>$value){
		echo $key . $value . "\n";
		$seller_id = $key;
		$aie = new AIE();
		$aie->set_path($value);
		$aie->process_dir();
	}
	
	exit;	
	
}
*/

$aie = new AIE();
if(!empty($_GET['action'])){
	$action = $_GET['action'];
}else{
	$action = $argv[1];
}
$aie->$action();
//$aie->generate_file('actionace007');
exit;
//$aie->set_path("/data/unifiedspoonfeeder/actionace007/SpoonFeeder/Auctions");
//$aie->process_dir();
//$aie->process_file("a08070400ux0022_FS.aie", "/export/test/");
//$aie->generate_file("actionace007");
/*

$ini_array = parse_ini_file("aie.ini", true);
//print_r($ini_array);

foreach($ini_array['aieDir'] as $key=>$value){
	echo $key . $value . "\n";
	$seller_id = $key;
	$aie = new AIE();
	$aie->set_path($value);
	$aie->process_dir();
}

exit;



$aie = new AIE();
//$aie->process_file();
$aie->generate_file();
@print_r($data['description_before_begin']);
echo "\n\n";
@print_r($data['Description']);
echo "\n\n";
@print_r($data['Description_After_End']);*/

//print_r($data);
//var_dump($count);
?>