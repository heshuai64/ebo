<?php
//define ('__DOCROOT__', '/export/eBayListing');
define ('__DOCROOT__', '.');
require_once __DOCROOT__ . '/eBaySOAP.php';

function debugLog($file_name, $data){
    //file_put_contents("C:\\xampp\\htdocs\\eBayBO\\eBayListing\\log\\".$file_name, $data ."\n", FILE_APPEND);
    @file_put_contents("/export/eBayListing/log/".$file_name, $data ."\n", FILE_APPEND);
}

function ErrorLogFunction($errno, $errstr, $errfile, $errline){
    //echo "<b>Custom error:</b> [$errno] $errstr<br />";
    //echo " Error on line $errline in $errfile<br />";
    //debugLog('errorLog\\'.date("Ymd").'.log', date("Y-m-d H:i:s"). '  '.$errno. ' : '.$errstr.' on line '.$errline.' in '.$errfile . "\n");
    debugLog('errorLog/'.date("Ymd").'.log', date("Y-m-d H:i:s"). '  '.$errno. ' : '.$errstr.' on line '.$errline.' in '.$errfile . "\n");
}

set_error_handler("ErrorLogFunction");

$nest = 0;
$categoryPathArray = array();
$storeNest = 0;
$storeCategoryPathArray = array();
$templateCategoryDeep = "";

/*
     英,美,法,澳,
	Europe/London       +0100  +7h
	America/New_York    -0400  +12h
	Europe/Paris        +0200  +6h
	Australia/Canberra  +1000  -3h
	Europe/Berlin       +0100  +7
	Asia/Shanghai       +0800
*/
 
class eBayListing{
    public static $database_connect;
    public static $service;
    public static $exchange_rate;
    public static $install;
    //const GATEWAY_SOAP = 'https://api.sandbox.ebay.com/wsapi';
    //const GATEWAY_SOAP = 'https://api.ebay.com/wsapi';
    
    const EBAY_BO_SERVICE = 'http://127.0.0.1/eBayBO/service.phpss';
    const INVENTORY_SERVICE = 'http://127.0.0.1/einv2/service.php';
    //const UPLOAD_TEMP_DIR = 'C:\\xampp\\htdocs\\eBayBO\\eBayListing\\log\\';
    const UPLOAD_TEMP_DIR = '/export/eBayListing/tmp/';
    const LOG_DIR = '/export/eBayListing/log/';
    //const LOG_DIR = 'C:\\xampp\\htdocs\\eBayBO\\eBayListing\\log\\';
    
    private $startTime;
    private $endTime;
    
    private $env = "production";
    //private $env = "sandbox";
    
    private $session;
    private $site_id; //US 0, UK 3, AU 15, FR 71
    private $account_id;
    
    private $config;
    
    public function __construct($site_id = 0){
	$this->config = parse_ini_file(__DOCROOT__ . '/config.ini', true);
	
	if(!empty($_COOKIE['account_id'])){
	    $this->account_id = $_COOKIE['account_id'];
	}
	
	$this->site_id = $site_id;
	
        eBayListing::$database_connect = mysql_connect($this->config['database']['host'], $this->config['database']['user'], $this->config['database']['password']);
	eBayListing::$service = $this->config['service'];
	eBayListing::$exchange_rate = $this->config['exchange_rate'];
	eBayListing::$install = $this->config['install'];
	
        if (!eBayListing::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(eBayListing::$database_connect);
            exit;
        }
	
        mysql_query("SET NAMES 'UTF8'", eBayListing::$database_connect);
	
        if (!mysql_select_db($this->config['database']['name'], eBayListing::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(eBayListing::$database_connect);
            exit;
        }
	
	if(isset($_SERVER['HTTP_HOST'])){
	    //if(strpos($_SERVER['HTTP_HOST'], "shuai64") == false){
		//exit;
	    //}
	}
	
	header( 'Content-Type: text/html; charset=UTF-8' );
    }
    
    public static function getExchangeRateS($currency){
	return eBayListing::$exchange_rate[$currency];
    }
    
    public  function setAccount($account_id){
	$this->account_id = $account_id;
    }
    
    public function getAccount(){
	return $this->account_id;
    }
    
    public function setSite($site_id){
	$this->site_id = $site_id;
    }
    
    public function configEbay($site_id = 0){
	$this->site_id = $site_id;
	
	if(!empty($_COOKIE['account_id']) && empty($this->account_id)){
	    $this->account_id = $_COOKIE['account_id'];
	}
	
    	if(!empty($this->account_id)){
	    $sql_0 = "select id from account where id = ".$this->account_id." and status = 1";
	    $result_0 = mysql_query($sql_0, eBayListing::$database_connect);
	    $row_0 = mysql_fetch_assoc($result_0);
	    if(empty($row_0['id'])){
		$sql_0 = "select name from account where id = ".$this->account_id;
		$result_0 = mysql_query($sql_0, eBayListing::$database_connect);
		$this->log("system", $row_0['name'] ." close, can't use.", "error");
		exit;
	    }
	    
	    $sql = "select token from account where id = '".$this->account_id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    
	    
	    $sql_1 = "select p.host,p.port from proxy as p left join account_to_proxy as atp on p.id = atp.proxy_id where atp.account_id = '".$this->account_id."'";
	    //echo $sql_1;
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $row_1 = mysql_fetch_assoc($result_1);
	    
	    //$row_1['host'] = null;
	    //$row_1['port'] = null;
	    //------------------------------------------------------------------------------------------------
	    // Load developer-specific configuration data from ini file
	    $config = parse_ini_file('ebay.ini', true);
	    $env = $this->env;
	    //$compatibilityLevel = $config['settings']['compatibilityLevel'];
	    
	    $dev =   $config[$env]['devId'];
	    $app =   $config[$env]['appId'];
	    $cert =  $config[$env]['cert'];
	    if($env == "production"){
		$token = $row['token'];
	    }else{
		$token = $config[$env]['authToken'];
	    }
	    $location = $config[$env]['gatewaySOAP'];
	    //$token = $row['token'];
	    //$token = (empty($token)?$config[$env]['authToken']:$token);
	    //$location = $config[$env]['gatewaySOAP'];
	    
	    // Create and configure session
	    $this->session = new eBaySession($dev, $app, $cert, $row_1['host'], $row_1['port']);
	    $this->session->token = $token;
	    //$this->session->site = 0; // 0 = US;
	    $this->session->site = $this->site_id;
	    $this->session->location = $location;
	}
    }
    
    private function saveFetchData($file_name, $data){
	$sql = "select name from account where id = ".$this->account_id;
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$account_name = $row['name'];
	
	if(!file_exists($this->config['log']['ebay']."/".$account_name)){
            mkdir($this->config['log']['ebay']."/".$account_name, 0777);
        }
	
	if(!file_exists($this->config['log']['ebay']."/".$account_name."/".date("Ymd"))){
            mkdir($this->config['log']['ebay']."/".$account_name."/".date("Ymd"), 0777);
        }
	
	file_put_contents($this->config['log']['ebay']."/".$account_name."/".date("Ymd")."/".$file_name, $data);
    }
    
    // ----------------   GET  POST -----------------------------------------------------------------------
    private function get($request){
	$session = curl_init($request);
		
	curl_setopt($session, CURLOPT_HEADER, true);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($session);
	
	curl_close($session);
	
	$status_code = array();
	preg_match('/\d\d\d/', $response, $status_code);
	
	switch( $status_code[0] ) {
		case 200:
		    if ($result = strstr($response, '{')) {
			return $result;
		    }
		    break;
		case 503:
			die('Your call to Web Services failed and returned an HTTP status of 503. That means: Service unavailable. An internal problem prevented us from returning data to you.');
			break;
		case 403:
			die('Your call to Web Services failed and returned an HTTP status of 403. That means: Forbidden. You do not have permission to access this resource, or are over your rate limit.');
			break;
		case 400:
			die('Your call to Web Services failed and returned an HTTP status of 400. That means:  Bad request. The parameters passed to the service did not match as expected. The exact error is returned in the XML response.');
			break;
		default:
			die('Your call to Web Services returned an unexpected HTTP status of:' . $status_code[0]);
			return false;
	}
    }
    
    private function post($postargs){
	
	$postargs = 'appid='.$appid.'&context='.urlencode($context).'&query='.urlencode($query);
	
	// Get the curl session object
	$session = curl_init($request);
	
	// Set the POST options.
	curl_setopt ($session, CURLOPT_POST, true);
	curl_setopt ($session, CURLOPT_POSTFIELDS, $postargs);
	curl_setopt($session, CURLOPT_HEADER, true);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	
	// Do the POST and then close the session
	$response = curl_exec($session);
	curl_close($session);
	
	// Get HTTP Status code from the response
	$status_code = array();
	preg_match('/\d\d\d/', $response, $status_code);
	
	// Check for errors
	switch( $status_code[0] ) {
		case 200:
			if ($result = strstr($response, '{')) {
			    return $result;
			}
			break;
		case 503:
			die('Your call to Web Services failed and returned an HTTP status of 503. That means: Service unavailable. An internal problem prevented us from returning data to you.');
			break;
		case 403:
			die('Your call to Web Services failed and returned an HTTP status of 403. That means: Forbidden. You do not have permission to access this resource, or are over your rate limit.');
			break;
		case 400:
			// You may want to fall through here and read the specific XML error
			die('Your call to Web Services failed and returned an HTTP status of 400. That means:  Bad request. The parameters passed to the service did not match as expected. The exact error is returned in the XML response.');
			break;
		default:
			die('Your call to Web Services returned an unexpected HTTP status of:' . $status_code[0]);
	}

    }
    
    public static function getInventoryServiceS($request){
        $json = file_get_contents(eBayListing::$service['inventory'].$request);
        return json_decode($json);
    }
    
    public static function getSkuLowPriceS($sku='', $currency=''){
	$rate = eBayListing::getExchangeRateS($currency);
        $json_object = eBayListing::getInventoryServiceS("?action=getSkuLowestPrice&sku=".$sku);
        $l_price = $json_object->L / $rate;
        //echo round($l_price, 2);
        return round($l_price, 2);
    }
    
    public function getSkuLowPrice(){
	echo eBayListing::getSkuLowPriceS($_GET['sku'], $_GET['currency']);
    }
    
    private function getShippingCost1ByTemplateName($shippingTemplate){
	$sql_8 = "select id from shipping_template where name = '".$shippingTemplate."' and account_id = '".$this->account_id."'";
	//echo $sql_8."\n";
	$result_8 = mysql_query($sql_8, eBayListing::$database_connect);
	$row_8 = mysql_fetch_assoc($result_8);
	
	$sql_31 = "select ShippingServiceCost from s_template where template_id = '".$row_8['id']."' and ShippingServicePriority = '1'";
	//echo $sql_31."\n";
	$result_31 = mysql_query($sql_31, eBayListing::$database_connect);
	$row_31 = mysql_fetch_assoc($result_31);
	return $row_31['ShippingServiceCost'];
    }
    
    public function getSkuLowSoldPrice(){
	$skuLowPrice = eBayListing::getSkuLowPriceS($_GET['sku'], $_GET['currency']);
	$shippingCost1 = $this->getShippingCost1ByTemplateName($_GET['shippingTemplate']);
	$lowPrice = $skuLowPrice - $shippingCost1;
	
	if($_GET['type'] == "auction"){
            if($_GET['price'] > 0.01 && $_GET['price'] < 0.99){
                $lowPrice += 0.1;
            }elseif($_GET['price'] > 1 && $_GET['price'] < 9.9){
                $lowPrice += 0.25;
            }
        }
        
        echo $lowPrice;
    }
    
    //-----------------  Template --------------------------------------------------------------------------
    /*
    ALTER TABLE `items` ADD `UseStandardFooter` BOOL NOT NULL ;
    ALTER TABLE `template` ADD `UseStandardFooter` BOOL NOT NULL ;
    ALTER TABLE `template` ADD `InsuranceOption` ENUM( "", "IncludedInShippingHandling", "NotOffered", "Optional", "Required" ) NOT NULL AFTER `ShippingServiceOptionsType` ;
    ALTER TABLE `template` ADD `InsuranceFee` DECIMAL( 10, 2 ) NOT NULL AFTER `InsuranceOption` ;
    ALTER TABLE `template` ADD `InternationalInsurance` ENUM( "", "IncludedInShippingHandling", "NotOffered", "Optional", "Required" ) NOT NULL AFTER `InternationalShippingServiceOptionType` ;
    ALTER TABLE `template` ADD `InternationalInsuranceFee` DECIMAL( 10, 2 ) NOT NULL AFTER `InternationalInsurance` ;
    
    ALTER TABLE `template` CHANGE `ReturnPolicyReturnsAcceptedOption` `ReturnPolicyReturnsAcceptedOption` ENUM( '', 'ReturnsAccepted', 'ReturnsNotAccepted' ) NOT NULL; 
    ALTER TABLE `items` CHANGE `ReturnPolicyReturnsAcceptedOption` `ReturnPolicyReturnsAcceptedOption` ENUM( '', 'ReturnsAccepted', 'ReturnsNotAccepted' ) NOT NULL; 
    */
    
    public function saveSkuPicture(){
	$sql_1 = "select count(*) as num from account_sku_picture where account_id = '".$this->account_id."' and sku = '".$_POST['sku']."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$row_1 = mysql_fetch_assoc($result_1);
	if($row_1['num'] == 0){
	    $sql_2 = "insert into account_sku_picture (account_id,sku,picture_1,picture_2,picture_3,picture_4,picture_5) values 
	    ('".$this->account_id."','".$_POST['sku']."','".$_POST['picture_1']."','".$_POST['picture_2']."','".$_POST['picture_3']."','".$_POST['picture_4']."','".$_POST['picture_5']."')";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    $row_2 = mysql_fetch_assoc($result_2);
	}else{
	    $sql_2 = "update account_sku_picture set picture_1='".$_POST['picture_1']."',picture_2='".$_POST['picture_2']."',
	    picture_3='".$_POST['picture_3']."',picture_4='".$_POST['picture_4']."',picture_5='".$_POST['picture_5']."' 
	    where account_id = '".$this->account_id."' and sku = '".$_POST['sku']."'";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    $row_2 = mysql_fetch_assoc($result_2);
	}
    }
    
    public function getSkuPicture($sku=''){
	if(!empty($sku)){
	    $_POST['sku'] = $sku;
	}
	$sql = "select picture_1,picture_2,picture_3,picture_4,picture_5 from account_sku_picture 
	where account_id = '".$this->account_id."' and sku = '".$_POST['sku']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	if(!empty($sku)){
	    return $row['picture_1'];
	}else{
	    echo json_encode($row);
	}
    }
    
    public function saveTempDescription(){
	session_start();
	$_SESSION[$_GET['type']][$_GET['id']]['title'] = $_POST['title'];
	$_SESSION[$_GET['type']][$_GET['id']]['description'] = htmlentities($_POST['description'], ENT_QUOTES);
	$_SESSION[$_GET['type']][$_GET['id']]['sku'] = $_POST['sku'];
    }
    
    public function getDescriptionById(){
	$sql = "select Title,Description,UseStandardFooter,SKU,PrimaryCategoryCategoryName,StoreCategoryName,StandardStyleTemplateId from template where Id = '".$_POST['id']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	
	$sql_0 = "select * from account_sku_picture where account_id = '".$this->account_id."' and sku = '".$row['SKU']."'";
        $result_0 = mysql_query($sql_0, eBayListing::$database_connect);
        $row_0 = mysql_fetch_assoc($result_0);
	
	echo '<div style="text-align:center; border: solid;">'.$row['PrimaryCategoryCategoryName'].'<br>'.
                    $row['StoreCategoryName'].'
            </div>';
	
	if($row['UseStandardFooter']){
	    $sql_1 = "select content from standard_style_template where id = '".$row['StandardStyleTemplateId']."' and accountId = '".$this->account_id."'";
            $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
            $row_1 = mysql_fetch_assoc($result_1);
	    //echo str_replace(array("%title%", "%description%"), array($row['Title'], html_entity_decode($row['Description'])), $row_1['footer']);	
	    echo str_replace(array("%title%", "%sku%", "%picture-1%", "%picture-2%", "%picture-3%", "%picture-4%", "%picture-5%", "%description%"),
                             array($row['Title'], $row['SKU'], '<img src="'.$row_0['picture_1'].'" />', '<img src="'.$row_0['picture_2'].'" />', '<img src="'.$row_0['picture_3'].'" />', '<img src="'.$row_0['picture_4'].'" />', '<img src="'.$row_0['picture_5'].'" />', html_entity_decode($row['Description'], ENT_QUOTES)), html_entity_decode($row_1['content']));
	}else{
	    echo html_entity_decode($row['Description'], ENT_QUOTES);
	}
    }
    
    public function saveFooter(){
	$sql_1 = "select count(*) as num from account_footer where accountId = '".$this->account_id."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$row_1 = mysql_fetch_assoc($result_1);
	
	if($row_1['num'] > 0){
	    $sql_2 = "update account_footer set footer = '".$_POST['content']."' where accountId = '".$this->account_id."'";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);	
	}else{
	    $sql_2 = "insert into account_footer (accountId,footer) values ('".$this->account_id."','".$_POST['content']."')";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);	
	}
	echo '<script type="text/javascript">window.close()</script>';
    }
    
    public function getCategoryById(){
	$sql = "select * from categories where CategorySiteID = ".$_POST['SiteID']." and CategoryID like '".$_POST['query']."%'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)){
	    $array[$i]['id'] = $row['CategoryID'];
	    $array[$i]['name'] = $this->getCategoryPathById($_POST['SiteID'], $row['CategoryID']);
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    private function getCategoryPathById($SiteID, $CategoryID){
    	global $categoryPathArray, $nest;
	
	if(empty($CategoryID)){
	    return "";    
	}
	
    	$sql = "select CategoryName,CategoryParentID,CategoryLevel from categories where  CategorySiteID = ".$SiteID." and CategoryID = ".$CategoryID;
    	//echo $sql."\n";
    	$result = mysql_query($sql, eBayListing::$database_connect);
    	$row = mysql_fetch_assoc($result);
	$nest++;
	
    	if($row['CategoryLevel'] != 1){
		if($nest >= 30){
		    return 0;
		}
    		array_push($categoryPathArray, $row['CategoryName']);
    		return $this->getCategoryPathById($SiteID, $row['CategoryParentID']);
    	}else{
    		array_push($categoryPathArray, $row['CategoryName']);
    		//print_r($categoryPathArray);
    		$categoryPath = "";
    		for($i = count($categoryPathArray); $i > 0; $i--){
    			$categoryPath .= $categoryPathArray[$i-1] . " > ";
    		}
		$categoryPathArray = array();
    		$categoryPath = substr($categoryPath, 0, -3);
    		//print_r($categoryPath);
    		return $categoryPath;
    	}
    }
   
    public function getStoreCategoryPathById($AccountId = 0, $CategoryID = 0){
	global $storeCategoryPathArray,$storeNest,$argv;
	
	if(!empty($argv[2]) && !empty($argv[3])){
	    $AccountId = $argv[2];
	    $CategoryID = $argv[3];
	    unset($argv[2]);
	    unset($argv[3]);
	}
	
	if(empty($CategoryID)){
	    return "";    
	}
	
	$sql = "select Name,CategoryParentID,AccountId from account_store_categories where CategoryID = ".$CategoryID." and AccountId = ".$AccountId;
    	//echo $sql."\n";
    	$result = mysql_query($sql, eBayListing::$database_connect);
    	$row = mysql_fetch_assoc($result);
	$storeNest++;
	
    	if($row['CategoryParentID'] != 0){
		if($storeNest >= 30){
		    return 0;
		}
    		array_push($storeCategoryPathArray, $row['Name']);
    		return $this->getStoreCategoryPathById($row['AccountId'], $row['CategoryParentID']);
    	}else{
    		array_push($storeCategoryPathArray, $row['Name']);
    		//print_r($categoryPathArray);
    		$categoryPath = "";
    		for($i = count($storeCategoryPathArray); $i > 0; $i--){
    			$categoryPath .= $storeCategoryPathArray[$i-1] . " > ";
    		}
		$storeCategoryPathArray = array();
    		$categoryPath = substr($categoryPath, 0, -3);
    		//print_r($categoryPath);
    		return $categoryPath;
    	}
    }
    
    //------------------------------------------------------------------------------------
    public function getAllInventorySkus(){
	$result = $this->get(self::INVENTORY_SERVICE."?action=getAllSkus");
	echo $result;
    }

    public function getCategoriesTree(){
	$sql = "select id from site where name = '".$_GET['SiteID']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$CategorySiteID = $row['id'];
	
	if($_POST['node'] == "0"){
	    $sql = "select CategoryID,CategoryName,LeafCategory from categories where CategoryID = CategoryParentID and CategorySiteID = '".$CategorySiteID."'";
	}else{
	    $sql = "select CategoryID,CategoryName,LeafCategory from categories where CategoryParentID = '".$_POST['node']."' and CategoryID != CategoryParentID and CategorySiteID = '".$CategorySiteID."'";
	}
	//echo $sql;
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)){
	    $array[$i]['id'] = $row['CategoryID'];
	    $array[$i]['text'] = $row['CategoryName'];
	    if($row['LeafCategory'] == 1){
		$array[$i]['leaf'] = true;
	    }
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function getStoreCategoriesTree(){
	$sql = "select CategoryID,Name from account_store_categories where AccountId = '".$this->account_id."' and CategoryParentID ='".$_POST['node']."' order by `Order`";
	
	//echo $sql;
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)){
	    $array[$i]['id'] = $row['CategoryID'];
	    $array[$i]['text'] = $row['Name'];
	    $sql_1 = "select count(*) as count from account_store_categories where CategoryParentID = '".$row['CategoryID']."'";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $row_1 = mysql_fetch_assoc($result_1);
	    
	    if($row_1['count'] == 0){
		$array[$i]['leaf'] = true;
	    }
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function getListingDurationType(){
	$sql = "select name from listing_duration_type";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)){
	    $array[$i]['id'] = $row['name'];
	    $array[$i]['name'] = $row['name'];
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function getListingDuration(){
	$sql = "select id from listing_duration_type where name = '".$_POST['id']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	
	$sql = "select name from listing_duration where id = '".$row['id']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)){
	    $array[$i]['id'] = $row['name'];
	    $array[$i]['name'] = $row['name'];
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function getShippingService(){
	//echo $InternationalService;
	//echo "\n";
	if($_POST['SiteID'] == 100){
	    $_POST['SiteID'] = 0;
	}
	$sql = "select id from site where name = '".$_POST['SiteID']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$SiteID = $row['id'];
	
	if($_POST['serviceType'] == "Flat"){
	    $sql = "select Description,ShippingService from shipping_service_details where  SiteID = '".$SiteID."' and ServiceTypeFlat = 1 and InternationalService = 0";
	}elseif($_POST['serviceType'] == "Calculated"){
	    $sql = "select Description,ShippingService from shipping_service_details where  SiteID = '".$SiteID."' and ServiceTypeCalculated = 1 and InternationalService = 0";
	}
	
	//echo $sql;
	//echo "\n";
	
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)){
	    $array[$i]['id'] = $row['ShippingService'];
	    $array[$i]['name'] = $row['Description'];
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function getInternationalShippingService(){
	if($_POST['SiteID'] == 100){
	    $_POST['SiteID'] = 0;
	}
	
	$sql = "select id from site where name = '".$_POST['SiteID']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$SiteID = $row['id'];
	
	if($_POST['serviceType'] == "Flat"){
	    $sql = "select Description,ShippingService from shipping_service_details where  SiteID = '".$SiteID."' and ServiceTypeFlat = 1 and InternationalService = 1";
	}elseif($_POST['serviceType'] == "Calculated"){
	    $sql = "select Description,ShippingService from shipping_service_details where  SiteID = '".$SiteID."' and ServiceTypeCalculated = 1 and InternationalService = 1";
	}
	
	//echo $sql;
	//echo "\n";
	
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)){
	    $array[$i]['id'] = $row['ShippingService'];
	    $array[$i]['name'] = $row['Description'];
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function synchronize(){
	
    }

    // -----------------  Item Specifics ---------------------------------------------
    public function getAttributes(){
	session_start();
	$sql = "select id from site where name = '".$_GET['SiteID']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$SiteID = $row['id'];
	
	//$_GET['CategoryID'] = 34;
	$sql = "select AttributeSetID from CharacteristicsSets where SiteID = '".$SiteID."' and CategoryID = '".$_GET['CategoryID']."'";
	//echo $sql;
	//echo "<br>";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$AttributeSetID = $row['AttributeSetID'];
	
	$array = array();
	$array['CharacteristicsSetId'] = $AttributeSetID;
	$sql = "select CharacteristicsSetId,AttributeId,Label,Type from CharacteristicsLists where (Type <> '' and Type <> 'radio' and Type <> 'textfield') and CharacteristicsSetId = '".$AttributeSetID."'";
	$result = mysql_query($sql);
	
	$i = 0;
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	    $array['Attribute'][$i]['id'] = $row['AttributeId'];
	    $array['Attribute'][$i]['fieldLabel'] = $row['Label'];
	    
	    $sql_1 = "select id,name from CharacteristicsAttributeValueLists where CharacteristicsSetId = '".$row['CharacteristicsSetId']."' and AttributeId = '".$row['AttributeId']."'";
	    //echo $sql_1;
	    $result_1 = mysql_query($sql_1);
	    $j = 0;
	    
	    switch($row['Type']){
		
		case "checkbox":
		    $array['Attribute'][$i]['xtype'] = "checkboxgroup";
		    $array['Attribute'][$i]['fieldLabel'] = $row['Label'];
		    //$array['Attribute'][$i]['name'] = $row['AttributeId'];
		    $array['Attribute'][$i]['items'] = "[";
		    while($row_1 = mysql_fetch_array($result_1, MYSQL_ASSOC)){
			$array['Attribute'][$i]['items'] .= "{id: '".$row_1['id']."_checkbox"."', boxLabel: '".$row_1['name']."', name: '".$row_1['id']."_checkbox"."', inputValue: '".$row['AttributeId']."_on'},";
		    }
		    $array['Attribute'][$i]['items'] = substr($array['Attribute'][$i]['items'], 0, -1);
		    $array['Attribute'][$i]['items'] .= "]";
		break;
	    
		case "collapsible_textarea":
		    $array['Attribute'][$i]['xtype'] = "textarea";
		    $array['Attribute'][$i]['name'] = $row['AttributeId'];
		break;
		
		case "dropdown":
		    $array['Attribute'][$i]['xtype'] = "combo";
		    $array['Attribute'][$i]['name'] = $row['AttributeId'];
		    $array['Attribute'][$i]['hiddenName'] = $row['AttributeId'];
		    $array['Attribute'][$i]['store'] = "{xtype: 'arraystore', fields: ['id','name'], data: [";
		    while($row_1 = mysql_fetch_array($result_1, MYSQL_ASSOC)){
			$array['Attribute'][$i]['store'] .= "[" .$row_1['id'] . ",'" . $row_1['name'] ."'],";
		    }
		    $array['Attribute'][$i]['store'] = substr($array['Attribute'][$i]['store'], 0, -1);
		    $array['Attribute'][$i]['store'] .= "]";
		    $array['Attribute'][$i]['store'] .= "}";
		break;
	    
		case "multiple":
		    /*
		    $array['Attribute'][$i]['xtype'] = "checkboxgroup";
		    $array['Attribute'][$i]['name'] = $row['AttributeId'];
		    $array['Attribute'][$i]['columns'] = 2;
		    while($row_1 = mysql_fetch_array($result_1, MYSQL_ASSOC)){
			$array['Attribute'][$i]['items'][$j]['name'] = $row_1['id'];
			$array['Attribute'][$i]['items'][$j]['boxLabel'] = $row_1['name'];
			$array['Attribute'][$i]['items'][$j]['inputValue'] = $row_1['id'];
			$j++;
		    }
		    */
		break;
	    }
	   
	    $i++;
	}
	//print_r($array);
	echo json_encode($array);
    }
    
    public function loadSpecifics(){
	if(!empty($_GET['sku'])){
	    $id = $_GET['sku'];
	}elseif(!empty($_GET['template_id'])){
	    $id = $_GET['template_id'];
	}elseif(!empty($_GET['item_id'])){
	    $id = $_GET['item_id'];
	}
	session_start();
	if(!empty($_SESSION['AttributeSet'][$id][$_GET['AttributeSetID']])){
	    echo '['.json_encode($_SESSION['AttributeSet'][$id][$_GET['AttributeSetID']]).']';
	}
	//print_r($_SESSION['AttributeSet']);
    }
    
    public function saveSpecifics(){
	if(!empty($_GET['sku'])){
	    $id = $_GET['sku'];
	}elseif(!empty($_GET['template_id'])){
	    $id = $_GET['template_id'];
	}elseif(!empty($_GET['item_id'])){
	    $id = $_GET['item_id'];
	}
	session_start();
	unset($_SESSION['AttributeSet'][$id][$_POST['CharacteristicsSetId']]);
	//unset($_SESSION);
	foreach($_POST as $key=>$value){
	    if($key != "CharacteristicsSetId"){
		$_SESSION['AttributeSet'][$id][$_POST['CharacteristicsSetId']][$key] = $value;
	    }
	}
	//print_r($_SESSION['AttributeSet'][$_GET['sku']][$_POST['CharacteristicsSetId']]);
	if(!empty($_SESSION['AttributeSet'][$id][$_POST['CharacteristicsSetId']])){
		echo 	'{success: true}';
		
	}else{
		echo 	'{success: false,
			  errors: {message: "can\'t save."}
			}';
	}
    }
    
    //---------------------------------------------------------------------------------
    public function loadReturnPolicyReturns(){
	if(!empty($_GET['sku'])){
	    $id = $_GET['sku'];
	}elseif(!empty($_GET['template_id'])){
	    $id = $_GET['template_id'];
	}elseif(!empty($_GET['item_id'])){
	    $id = $_GET['item_id'];
	}
	session_start();
	if(!empty($_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyReturnsAcceptedOption'])){
	    echo '['.json_encode($_SESSION['ReturnPolicyReturns'][$id]).']';
	}
    }
    
    public function saveReturnPolicyReturns(){
	if(!empty($_GET['sku'])){
	    $id = $_GET['sku'];
	}elseif(!empty($_GET['template_id'])){
	    $id = $_GET['template_id'];
	}elseif(!empty($_GET['item_id'])){
	    $id = $_GET['item_id'];
	}
	session_start();
	unset($_SESSION['ReturnPolicyReturns'][$id]);
	
	$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyReturnsAcceptedOption'] = (($_POST['ReturnPolicyReturnsAcceptedOption1'] == "true")?'ReturnsAccepted':(($_POST['ReturnPolicyReturnsAcceptedOption2'] == "true")?'ReturnsNotAccepted':''));
	$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyReturnsWithinOption'] = $_POST['ReturnPolicyReturnsWithinOption'];
	$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyRefundOption'] = $_POST['ReturnPolicyRefundOption'];
	$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyShippingCostPaidByOption'] = $_POST['ReturnPolicyShippingCostPaidByOption'];//(!empty($_POST['ReturnPolicyShippingCostPaidByOption1']))?$_POST['ReturnPolicyShippingCostPaidByOption1']:(!empty($_POST['ReturnPolicyShippingCostPaidByOption2']))?$_POST['ReturnPolicyShippingCostPaidByOption2']:'';
	$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyDescription'] = $_POST['ReturnPolicyDescription'];
    }
    
    
    public function getAllSites(){
	$sql = "select * from site where status = '1'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)){
	    $array[$i]['id'] = $row['id'];
	    $array[$i]['name'] = $row['name'];
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function getAllCountries(){
	$sql = "select countries_name from countries";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$i = 0;
	$array = array();
	while ($row = mysql_fetch_assoc($result)){
	    $array[$i]['id'] = $row['countries_name'];
	    $array[$i]['name'] = $row['countries_name'];
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function updateFields(){
	switch($_POST['table']){
	    case "items":
		$sql_1 = "select ScheduleTime,Status,ListingType from items where Id = '".$_POST['id']."'";
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		$row_1 = mysql_fetch_assoc($result_1);
		//print_r($row_1);
		switch($row_1['Status']){
		    case 0:
			/*
			if($row_1['ScheduleTime'] > date("Y-m-d H:i:s")){
			    $sql = "update items set ".$_POST['field']." = '".$_POST['value']."' where Id = '".$_POST['id']."'";
			    $result = mysql_query($sql, eBayListing::$database_connect);
			    echo "[{success: true, msg: 'update ".$_POST['field']." success.'}]";
			}else{
			    echo "[{success: false, msg: 'item have been uploaded.'}]";
			    return 0;
			}
			*/
			$sql = "update items set Title = '".html_entity_decode($_POST['Title'], ENT_QUOTES)."' where Id = '".$_POST['id']."'";
			$result = mysql_query($sql, eBayListing::$database_connect);
			echo "[{success: true, msg: 'update success.'}]";
		    break;
		
		    case 1:
			$sql = "update items set Title = '".html_entity_decode($_POST['Title'], ENT_QUOTES)."' where Id = '".$_POST['id']."'";
			$result = mysql_query($sql, eBayListing::$database_connect);
			echo "[{success: true, msg: 'update success.'}]";
		    break;
		
		    case 2:
			if(!empty($_POST['Title'])){
			    $update .= "Title='".html_entity_decode($_POST['Title'], ENT_QUOTES)."',";
			}
			
			if(!empty($_POST['Price'])){
			    $sql_1 = "select ListingType from template where Id = '".$_POST['id']."'";
			    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
			    $row_1 = mysql_fetch_assoc($result_1);
			    if($row_1['ListingType'] == "Chinese" || $row_1['ListingType'] == "Dutch"){
				$update .= "BuyItNowPrice = ".$_POST['Price'].",";
			    }else{
				$update .= "StartPrice = ".$_POST['Price'].",BuyItNowPrice=0,";
			    }
			}
			
			if(!empty($_POST['Quantity'])){
			    $update .= "Quantity='".$_POST['Quantity']."',";
			}
			
			if(strlen($update) > 3){
			    $update .= " Status=3";
			    $sql = "update items set ".$update." where Id = '".$_POST['id']."'";
			    //echo $sql;
			    $result = mysql_query($sql, eBayListing::$database_connect);
			    echo "[{success: true, msg: 'revise ".$_POST['field']." success.'}]";
			}else{
			    echo "[{success: false, msg: 'data no change, no update.'}]";
			    return 1;
			}
		    break;
		}
	    break;
	
	    case "template":
		$update = "";
		if(!empty($_POST['Title'])){
		    $update .= "Title='".html_entity_decode($_POST['Title'], ENT_QUOTES)."',";
		}
		
		if(!empty($_POST['Price'])){
		    $sql_1 = "select ListingType from template where Id = '".$_POST['id']."'";
		    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		    $row_1 = mysql_fetch_assoc($result_1);
		    if($row_1['ListingType'] == "Chinese" || $row_1['ListingType'] == "Dutch"){
			$update .= "BuyItNowPrice = ".$_POST['Price'].",";
		    }else{
			$update .= "StartPrice = ".$_POST['Price'].",BuyItNowPrice=0,";
		    }
		}
		
		if(!empty($_POST['Quantity'])){
		    $update .= "Quantity='".$_POST['Quantity']."',";
		}
		
		if(!empty($_POST['ListingDuration'])){
		    $update .= "ListingDuration='".$_POST['ListingDuration']."',";
		}
		
		$update .= "status = 0,";
		
		if(!empty($_POST['Category'])){
		    $sql_5 = "select count(*) as num from template_to_template_cateogry where template_id = '".$_POST['id']."'";
		    $result_5 = mysql_query($sql_5, eBayListing::$database_connect);
		    $row_5 = mysql_fetch_assoc($result_5);
		    
		    if($row_5['num'] > 0){
			$sql_6 = "update template_to_template_cateogry set template_category_id = '".$_POST['Category']."' where template_id = '".$_POST['id']."'";
			$result = mysql_query($sql_6, eBayListing::$database_connect);
		    }else{
			$sql_6 = "insert into template_to_template_cateogry (template_id,template_category_id) values ('".$_POST['id']."','".$_POST['Category']."')";
			$result = mysql_query($sql_6, eBayListing::$database_connect);
		    }
		}else{
		    if(strlen($update) > 3){
			$update = substr($update, 0, -1);
			$sql = "update template set ".$update. " where Id = '".$_POST['id']."'";
			//echo $sql;
			$result = mysql_query($sql, eBayListing::$database_connect);
		    }else{
			echo "[{success: false, msg: 'data no change, no update.'}]";
			return 1;
		    }
		}
		
		if($result){
		    echo "[{success: true, msg: 'update template success.'}]";
		}else{
		    echo "[{success: false, msg: 'please notice admin.'}]";
		}
	    break;
	}
	return 1;
    }
    
    //-------------------------- Item  -------------------------------------------------------------------
    
    private function getItemTemplateStatus($itemId){
	$sql_1 = "select TemplateID from items where Id = ".$itemId;
	$result_1 = mysql_query($sql_1);
	$row_1 = mysql_fetch_assoc($result_1);
	    
	$sql_2 = "select status from template where Id = ".$row_1['TemplateID'];
	$result_2 = mysql_query($sql_2);
	$row_2 = mysql_fetch_assoc($result_2);
	
	return $row_2['status'];
    }
    //-------------------------- Upload  -----------------------------------------------------------------
    public function uploadItem(){
	$date = date("Y-m-d");
	$day = date("D");
	$time = date("H:i:00");
	//$sql = "select item_id from schedule where startDate <= '".$date."' and endDate => '".$date."' and day = '".$day."' and time ='".$time."'";
	//$sql = "select item_id from schedule where day = '".$day."' and time ='".$time."'";
	//$sql = "select item_id from schedule where day = '".$day."'";
	//$sql = "select Id from items where ScheduleTime <> '' and ScheduleTime <= now() and Status = 0";
	$from = date("Y-m-d H:i:s", time() - 60);
	$to = date("Y-m-d H:i:s", time() + 30);
	
	$sql = "select Id,AccountId,SKU from items where Status = 1 and ScheduleTime between '".$from."' and '".$to."'";
	//$sql = "select Id,AccountId from items where Status = 1";
	
	$result = mysql_query($sql);
	while($row = mysql_fetch_assoc($result)){
	    $this->setAccount($row['AccountId']);
	    $template_status = $this->getItemTemplateStatus($row['Id']);
	    if($template_status !=2 && $template_status != 3){
		switch($template_status){
		    case 0:
			$status = "new";
		    break;
		
		    case 1:
			$status = "waiting for approve";
		    break;
		
		    case 4:
			$status = "under review";
		    break;
		
		    case 5:
			$status = "inactive";
		    break;
		}
		$this->log("upload", $row['Id'] . " template status is ".$status, "warn");
		continue;
	    }
	    
	    $sql_0 = "update items set Status = 10 where Id = '".$row['Id']."'";
	    $result_0 = mysql_query($sql_0);
	    
	    //$row['item_id'] = 98;
	    $sql_1 = "select * from items where Id = '".$row['Id']."'";
	    $result_1 = mysql_query($sql_1);
	    $row_1 = mysql_fetch_assoc($result_1);
	    
	    if($row_1['UseStandardFooter']){
		$sql_0 = "select * from account_sku_picture where account_id = '".$row_1['accountId']."' and sku = '".$row_1['SKU']."'";
		$result_0 = mysql_query($sql_0);
		$row_0 = mysql_fetch_assoc($result_0);
		
		$sql_01 = "select content from standard_style_template where id = '".$row_1['StandardStyleTemplateId']."' and accountId = '".$row_1['accountId']."'";
		$result_01 = mysql_query($sql_01);
		$row_01 = mysql_fetch_assoc($result_01);
		
		$row_1['Description'] = str_replace(array("%title%", "%sku%", "%picture-1%", "%picture-2%", "%picture-3%", "%picture-4%", "%picture-5%", "%description%"),
						    array(html_entity_decode($row_1['Title'], ENT_QUOTES), $row_1['SKU'], '<img src="'.$row_0['picture_1'].'" />', '<img src="'.$row_0['picture_2'].'" />', '<img src="'.$row_0['picture_3'].'" />', '<img src="'.$row_0['picture_4'].'" />', '<img src="'.$row_0['picture_5'].'" />', html_entity_decode($row_1['Description'], ENT_QUOTES)), html_entity_decode($row_01['content'], ENT_QUOTES));
	    }else{
		$row_1['Description'] = html_entity_decode($row_1['Description'], ENT_QUOTES);
	    }
	    
	    
	    $row_1['Description'] = utf8_encode($row_1['Description']);
	    $row_1['Title'] = html_entity_decode($row_1['Title'], ENT_QUOTES);
	    
	    $sql_2 = "select * from shipping_service_options where ItemID = '".$row['Id']."'";
	    $result_2 = mysql_query($sql_2);
	    $ShippingServiceOptions = array();
	    while($row_2 = mysql_fetch_assoc($result_2)){
		$ShippingServiceOptions[] = $row_2;
	    }
	    
	    $sql_3 = "select * from international_shipping_service_option where ItemID = '".$row['Id']."'";
	    $result_3 = mysql_query($sql_3);
	    $InternationalShippingServiceOption = array();
	    while($row_3 = mysql_fetch_assoc($result_3)){
		$InternationalShippingServiceOption[] = $row_3;
	    }
	    
	    $sql_4 = "select * from picture_url where ItemID = '".$row['Id']."'";
	    //echo $sql_4;
	    //echo "<br>";
	    $result_4 = mysql_query($sql_4);
	    $PictureURL = array();
	    while($row_4 = mysql_fetch_assoc($result_4)){
		$PictureURL[] = $row_4['url'];
	    } 
	    
	    $sql_5 = "select * from attribute_set where item_id = '".$row['Id']."'";
	    $result_5 = mysql_query($sql_5);
	    $AttributeSetArray = array();
	    $i = 0;
	    while($row_5 = mysql_fetch_assoc($result_5)){
		/*
		$AttributeSetArray[$i]['AttributeSet']['attributeSetID'] = $row_5['attributeSetID'];
		
		$sql_6 = "select * from attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
		$result_6 = mysql_query($sql_6);
		$j = 0;
		while($row_6 = mysql_fetch_assoc($result_6)){
		    $AttributeSetArray[$i]['AttributeSet'][$j]['Attribute']['attributeID'] = $row_6['attributeID'];
		    $AttributeSetArray[$i]['AttributeSet'][$j]['Attribute']['Value']['ValueID'] = $row_6['ValueID'];
		    if(!empty($row_6['ValueLiteral'])){
			$AttributeSetArray[$i]['AttributeSet'][$j]['Attribute']['Value']['ValueLiteral'] = $row_6['ValueLiteral'];
		    }
		    $j++;
		}
		$i++;
		*/
		$AttributeSetArray['AttributeSet']['attributeSetID'] = $row_5['attributeSetID'];
		
		$sql_6 = "select * from attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
		$result_6 = mysql_query($sql_6);
		$j = 0;
		while($row_6 = mysql_fetch_assoc($result_6)){
		    $AttributeSetArray['AttributeSet']['Attribute'][$j]['attributeID'] = $row_6['attributeID'];
		    if(strpos($row_6['ValueID'], ",") != false){
			$ValueIDArray = explode(",", $row_6['ValueID']);
			$k = 0;
			//print_r($ValueIDArray);
			foreach($ValueIDArray as $ValueID){
			    $AttributeSetArray['AttributeSet']['Attribute'][$j]['Value'][$k]['ValueID'] = $ValueID;
			    $k++;
			}
		    }else{
			$AttributeSetArray['AttributeSet']['Attribute'][$j]['Value']['ValueID'] = $row_6['ValueID'];
		    }
		    if(!empty($row_6['ValueLiteral'])){
			$AttributeSetArray['AttributeSet']['Attribute'][$j]['Value']['ValueLiteral'] = $row_6['ValueLiteral'];
		    }
		    $j++;
		}
	    }
	    
	    $row_1['AttributeSetArray'] = $AttributeSetArray;
	    $row_1['ShippingServiceOptions'] = $ShippingServiceOptions;
	    $row_1['InternationalShippingServiceOption'] = $InternationalShippingServiceOption;
	    $row_1['PictureURL'] = $PictureURL;
	    
	    //print_r($row_1);
	    //exit;
	    $this->addItem($row_1);
	}
    }
    
    private function addItem($item){
	/*
	<AttributeSetArray> AttributeSetArrayType 
	    <AttributeSet attributeSetID="int" attributeSetVersion="string"> AttributeSetType 
		<Attribute attributeID="int"> AttributeType 
		    <Value> ValType 
			<ValueID> int </ValueID>
			<ValueLiteral> string </ValueLiteral>
		    </Value>
		    <!-- ... more Value nodes here ... -->
		</Attribute>
		<!-- ... more Attribute nodes here ... -->
	    </AttributeSet>
	    <!-- ... more AttributeSet nodes here ... -->
	</AttributeSetArray>
	
	<BuyItNowPrice currencyID="CurrencyCodeType"> AmountType (double) </BuyItNowPrice>
 
	<CategoryMappingAllowed> boolean </CategoryMappingAllowed>

	<Country> CountryCodeType </Country>
	//CN,HK
	
	<Currency> CurrencyCodeType </Currency>
	//GBP,USD,EUR,AUD
	
	<Description> string </Description>

	<DispatchTimeMax> int </DispatchTimeMax>

	<ListingDuration> token </ListingDuration>
	
	<ListingEnhancement> ListingEnhancementsCodeType </ListingEnhancement>

	//AdType,Chinese,CustomCode,Dutch,Express(Germany),FixedPriceItem,LeadGeneration,StoresFixedPrice
	<ListingType> ListingTypeCodeType </ListingType>
	<Location> string </Location>
	//China
	
	<PaymentMethods> BuyerPaymentMethodCodeType </PaymentMethods>
	<!-- ... more PaymentMethods nodes here ... -->
	<PayPalEmailAddress> string </PayPalEmailAddress>


	<PictureDetails> PictureDetailsType 
	    <GalleryDuration> token </GalleryDuration>
	    Describes the number of days that "Featured" Gallery type applies to a listing.
	    The values that can be specified in this field are in ListingEnhancementDurationCodeType.
	    When a seller chooses "Featured" as the Gallery type,
	    the listing is highlighted and is included at the top of search results.
	    This functionality is applicable only for Gallery Featured items and returns an error for any other Gallery type.
	    Additionally, an error is returned if the seller attempts to downgrade from Lifetime to limited duration,
	    but the seller can upgrade from limited duration to Lifetime duration.
	    This field is not applicable to auction listings.
	    <GalleryType> GalleryTypeCodeType </GalleryType>
	    Featured  Gallery  
	    <GalleryURL> anyURI </GalleryURL>
	    <PhotoDisplay> PhotoDisplayCodeType </PhotoDisplay>
	    <PictureURL> anyURI </PictureURL>
	    <!-- ... more PictureURL nodes here ... -->
	</PictureDetails>

	<PostalCode> string </PostalCode>

	<PrimaryCategory> CategoryType 
	    <CategoryID> string </CategoryID>
	</PrimaryCategory>


	<Quantity> int </Quantity>

	<ReturnPolicy> ReturnPolicyType 
	    <Description> string </Description>
	    <EAN> string </EAN>
	    <RefundOption> token </RefundOption>
	    <ReturnsAcceptedOption> token </ReturnsAcceptedOption>
	    <ReturnsWithinOption> token </ReturnsWithinOption>
	    <ShippingCostPaidByOption> token </ShippingCostPaidByOption>
	    <WarrantyDurationOption> token </WarrantyDurationOption>
	    <WarrantyOfferedOption> token </WarrantyOfferedOption>
	    <WarrantyTypeOption> token </WarrantyTypeOption>
	</ReturnPolicy>


	<ScheduleTime> dateTime </ScheduleTime>
	
	<SecondaryCategory> CategoryType 
	    <CategoryID> string </CategoryID>
	</SecondaryCategory>


	<ShippingDetails>
	    <InsuranceDetails> InsuranceDetailsType 
		<InsuranceFee currencyID="CurrencyCodeType"> AmountType (double) </InsuranceFee>
		<InsuranceOption> InsuranceOptionCodeType </InsuranceOption>
	     </InsuranceDetails>
	     <InsuranceFee currencyID="CurrencyCodeType"> AmountType (double) </InsuranceFee>
	     <InsuranceOption> InsuranceOptionCodeType </InsuranceOption>
	     <InternationalInsuranceDetails> InsuranceDetailsType 
		<InsuranceFee currencyID="CurrencyCodeType"> AmountType (double) </InsuranceFee>
		<InsuranceOption> InsuranceOptionCodeType </InsuranceOption>
	    </InternationalInsuranceDetails>

	    <InternationalShippingServiceOption> InternationalShippingServiceOptionsType 
		<ShippingService> token </ShippingService>
		<ShippingServiceAdditionalCost currencyID="CurrencyCodeType"> AmountType (double) </ShippingServiceAdditionalCost>
		<ShippingServiceCost currencyID="CurrencyCodeType"> AmountType (double) </ShippingServiceCost>
		<ShippingServicePriority> int </ShippingServicePriority>
		<ShipToLocation> string </ShipToLocation>
		<!-- ... more ShipToLocation nodes here ... -->
	    </InternationalShippingServiceOption>

	    <ShippingServiceOptions> ShippingServiceOptionsType 
		<FreeShipping> boolean </FreeShipping>
		<ShippingService> token </ShippingService>
		<ShippingServiceAdditionalCost currencyID="CurrencyCodeType"> AmountType (double) </ShippingServiceAdditionalCost>
		<ShippingServiceCost currencyID="CurrencyCodeType"> AmountType (double) </ShippingServiceCost>
		<ShippingServicePriority> int </ShippingServicePriority>
		<ShippingSurcharge currencyID="CurrencyCodeType"> AmountType (double) </ShippingSurcharge>
	    </ShippingServiceOptions>
	    
	    <ShippingType> ShippingTypeCodeType </ShippingType>
	    //Calculated,CalculatedDomesticFlatInternational,CustomCode,Flat,FlatDomesticCalculatedInternational,FreightFlat,NotSpecified
	</ShippingDetails>

	<Site> SiteCodeType </Site>
	<SKU> SKUType </SKU>

	<StartPrice currencyID="CurrencyCodeType"> AmountType (double) </StartPrice>


	<Storefront> StorefrontType 
	    <StoreCategory2ID> long </StoreCategory2ID>
	    <StoreCategoryID> long </StoreCategoryID>
	</Storefront>
	
	<SubTitle> string </SubTitle>
	<Title> string </Title>

	*/
	$ShipToLocations = array();
	
	$sql = "select id from site where name = '".$item['Site']."'";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$this->configEbay($row['id']);
	
	try {
	    $client = new eBaySOAP($this->session);
	    $Version = '607';
	    
	    $itemArray = array();
	    
	    if(count($item['AttributeSetArray']) > 0){
		$itemArray['AttributeSetArray'] = $item['AttributeSetArray'];
	    }
	    
	    if(!empty($item['BuyItNowPrice']) && $item['BuyItNowPrice'] != 0 && $itemArray['BuyItNowPrice'] > $itemArray['StartPrice']){
		$itemArray['BuyItNowPrice'] = $item['BuyItNowPrice'];
	    }
	    $itemArray['CategoryMappingAllowed'] = true;
	    $itemArray['Country'] = $item['Country'];
	    $itemArray['Currency'] = $item['Currency'];
	    $itemArray['Description'] = $item['Description'];
	    if(!empty($item['DispatchTimeMax'])){
		$itemArray['DispatchTimeMax'] = $item['DispatchTimeMax'];
	    }
	    $itemArray['ListingDuration'] = $item['ListingDuration'];
	    
	    if($item['BoldTitle'] == true){
		$itemArray['ListingEnhancement'][] = "BoldTitle";
	    }
	    if($item['Border'] == true){
		$itemArray['ListingEnhancement'][] = "Border";
	    }
	    if($item['Featured'] == true){
		$itemArray['ListingEnhancement'][] = "Featured";
	    }
	    if($item['Highlight'] == true){
		$itemArray['ListingEnhancement'][] = "Highlight";
	    }
	    if($item['HomePageFeatured'] == true){
		$itemArray['ListingEnhancement'][] = "HomePageFeatured";
	    }
	    $itemArray['ListingType'] = $item['ListingType'];
	    if(!empty($item['Location'])){
		$itemArray['Location'] = $item['Location'];
	    }
	    $itemArray['Country'] = 'HK';
	    
	    $itemArray['PaymentMethods'] = $item['PaymentMethods'];
	    $itemArray['PayPalEmailAddress'] = $item['PayPalEmailAddress'];
	    //PictureDetails
	    if($item['GalleryTypeFeatured']){
		$itemArray['PictureDetails']['GalleryType'] = "Featured";
	    }
	    if($item['GalleryTypeGallery']){
		$itemArray['PictureDetails']['GalleryType'] = "Gallery";
	    }
	    if($item['GalleryTypePlus']){
		$itemArray['PictureDetails']['GalleryType'] = "Plus";
	    }
	    if(!empty($item['GalleryURL'])){
		$itemArray['PictureDetails']['GalleryURL'] = $item['GalleryURL'];
	    }
	    if(!empty($item['PictureURL']) && is_array($item['PictureURL'])){
		$i = 0;
		foreach($item['PictureURL'] as $p){
		    $itemArray['PictureDetails']['PictureURL'][$i] = $p;
		    $i++;
		}
	    }
	    if(!empty($item['PostalCode'])){
		$itemArray['PostalCode'] = $item['PostalCode'];
	    }
	    $itemArray['PrimaryCategory']['CategoryID'] = $item['PrimaryCategoryCategoryID'];
	    $itemArray['Quantity'] = $item['Quantity'];
	    
	    if(!empty($item['ReturnPolicyReturnsAcceptedOption'])){
		$itemArray['ReturnPolicy']['ReturnsAcceptedOption'] = $item['ReturnPolicyReturnsAcceptedOption'];
		if(!empty($item['ReturnPolicyDescription'])){
		    $itemArray['ReturnPolicy']['Description'] = $item['ReturnPolicyDescription'];
		}
		if(!empty($item['ReturnPolicyRefundOption'])){
		    $itemArray['ReturnPolicy']['RefundOption'] = $item['ReturnPolicyRefundOption'];
		}
		if(!empty($item['ReturnPolicyReturnsWithinOption'])){
		    $itemArray['ReturnPolicy']['ReturnsWithinOption'] = $item['ReturnPolicyReturnsWithinOption'];
		}
		if(!empty($item['ReturnPolicyShippingCostPaidByOption'])){
		    $itemArray['ReturnPolicy']['ShippingCostPaidByOption'] = $item['ReturnPolicyShippingCostPaidByOption'];
		}
	    }
	    
	    if(!empty($item['SecondaryCategoryCategoryID']) && $item['SecondaryCategoryCategoryID'] != 0){
		$itemArray['SecondaryCategory']['CategoryID'] = $item['SecondaryCategoryCategoryID'];
	    }
	    if(!empty($item['InsuranceOption'])){
		$itemArray['ShippingDetails']['InsuranceDetails']['InsuranceOption'] = $item['InsuranceOption'];
		$itemArray['ShippingDetails']['InsuranceDetails']['InsuranceFee'] = $item['InsuranceFee'];
	    }
	    if(!empty($item['InternationalInsurance'])){
		$itemArray['ShippingDetails']['InternationalInsuranceDetails']['InsuranceOption'] = $item['InternationalInsurance'];
		$itemArray['ShippingDetails']['InternationalInsuranceDetails']['InsuranceFee'] = $item['InternationalInsuranceFee'];
	    }
	    $itemArray['ShippingDetails']['ShippingType'] = $item['ShippingType'];
	    if(!empty($item['ShippingServiceOptions']) && is_array($item['ShippingServiceOptions'])){
		$i = 0;
		foreach($item['ShippingServiceOptions'] as $s){
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['FreeShipping'] = $s['FreeShipping'];
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingService'] = $s['ShippingService'];
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingServiceCost'] = $s['ShippingServiceCost'];
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingServiceAdditionalCost'] = $s['ShippingServiceAdditionalCost'];
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingServicePriority'] = $s['ShippingServicePriority'];
		    $i++;
		}
	    }
	    if(!empty($item['InternationalShippingServiceOption']) && is_array($item['InternationalShippingServiceOption'])){
		$j = 0;
		foreach($item['InternationalShippingServiceOption'] as $i){
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingService'] = $i['ShippingService'];
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingServiceCost'] = $i['ShippingServiceCost'];
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingServiceAdditionalCost'] = $i['ShippingServiceAdditionalCost'];
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingServicePriority'] = $i['ShippingServicePriority'];
		    if(!empty($i['ShipToLocation'])){
			if(strpos($i['ShipToLocation'], ',') != false){
			    //echo "test1";
			    $stl_array = explode(',', $i['ShipToLocation']);
			    foreach($stl_array as $stl){
				if(!in_array($stl, $ShipToLocations)){
				    array_push($ShipToLocations, $stl);
				}
			    }
			    //$ShipToLocations = array_merge($ShipToLocations, explode(',', $i['ShipToLocation']));
			    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShipToLocation'] = explode(',', $i['ShipToLocation']);
			}else{
			    //echo "test2";
			    if(!in_array($i['ShipToLocation'], $ShipToLocations)){
				array_push($ShipToLocations, $i['ShipToLocation']);
			    }
			    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShipToLocation'] = $i['ShipToLocation'];
			}
		    }
		    $j++;
		}
	    }
	    
	    //print_r($itemArray['ShippingDetails']['InternationalShippingServiceOption']);
	    //exit;
	    //ShipToLocations
	    //$itemArray['ShipToLocations'] = "Worldwide";
	    if(!empty($ShipToLocations)){
		$itemArray['ShipToLocations'] = $ShipToLocations;
	    }
	    $itemArray['BuyerResponsibleForShipping'] = false; 
	    $itemArray['ShippingTermsInDescription'] = true;
	    
	    $itemArray['Site'] = $item['Site'];
	    $itemArray['SKU'] = $item['SKU'];
	    if(!empty($item['StartPrice']) && $item['StartPrice'] != 0){
		$itemArray['StartPrice'] = $item['StartPrice'];
	    }
	    if(!empty($item['StoreCategoryID'])){
		$itemArray['Storefront']['StoreCategoryID'] = $item['StoreCategoryID'];
		if(!empty($item['StoreCategory2ID'])){
		    $itemArray['Storefront']['StoreCategory2ID'] = $item['StoreCategory2ID'];
		}else{
		    $itemArray['Storefront']['StoreCategory2ID'] = 0;
		}
	    }
	    if(!empty($item['SubTitle'])){
		$itemArray['SubTitle'] = $item['SubTitle'];
	    }
	    $itemArray['Title'] = $item['Title'];
	   
	    //unset($itemArray['Description']);
	    //print_r($itemArray);
	    //exit;
	    $params = array('Version' => $Version,
			    'Item' => $itemArray);
	    
	    $results = $client->AddItem($params);
	    //print_r($results);
	    
	    if(!empty($results->Errors)){
		$sql_0 = "update items set Status = 1 where Id = '".$item['Id']."'";
		$result_0 = mysql_query($sql_0);
		
		if(is_array($results->Errors)){
		    $temp = '';
		    foreach($results->Errors as $error){
			echo $error->ShortMessage." : ";
			echo $error->LongMessage."<br>";
			$temp .= $error->LongMessage;
		    }
		    $this->log("upload", $item['Id'] ." " . $temp, (empty($results->ItemID)?"error":"warn"));
		}else{
		    echo $results->Errors->ShortMessage." : ";
		    echo $results->Errors->LongMessage."<br>";
		    $this->log("upload", $item['Id'] ." " . $results->Errors->LongMessage, (empty($results->ItemID)?"error":"warn"));
		}
		
		if(!empty($results->ItemID)){
		    foreach($results->Fees->Fee as $fee){
			switch($fee->Name){
			    case "InsertionFee":
				$InsertionFee = $fee->Fee->_;
			    break;
			
			    case "ListingFee":
				$ListingFee = $fee->Fee->_;
			    break;
			}
			
		    }
		
		    $sql = "update items set ItemID = '".$results->ItemID."',Status='2',StartTime='".$results->StartTime."',
		    EndTime='".$results->EndTime."',InsertionFee='".$InsertionFee."',ListingFee='".$ListingFee."' where Id = '".$item['Id']."'";
		    echo $sql;
		    $result = mysql_query($sql);
		    $this->log("upload", $item['Id']." upload success, ItemID is ".$results->ItemID);
		}
	    }elseif(!empty($results->ItemID)){
		foreach($results->Fees->Fee as $fee){
		    switch($fee->Name){
			case "InsertionFee":
			    $InsertionFee = $fee->Fee->_;
			break;
		    
			case "ListingFee":
			    $ListingFee = $fee->Fee->_;
			break;
		    }
		    
		}
		//echo $results->ItemID;
		//echo $results->StartTime;
		//echo $results->EndTime;
		$sql = "update items set ItemID = '".$results->ItemID."',Status='2',StartTime='".$results->StartTime."',
		EndTime='".$results->EndTime."',InsertionFee='".$InsertionFee."',ListingFee='".$ListingFee."' where Id = '".$item['Id']."'";
		echo $sql;
		$result = mysql_query($sql);
		$this->log("upload", $item['Id']." upload success, ItemID is ".$results->ItemID);
	    }
	    
	    if(!empty($results->faultcode)){
		$sql_0 = "update items set Status = 1 where Id = '".$item['Id']."'";
		$result_0 = mysql_query($sql_0);
		$this->log("upload", $item['Id'] ." " . $results->faultcode . ": " . $results->faultstring, "error");

	    }
	    //----------   debug --------------------------------
	    //print "Request: \n".$client->__getLastRequest() ."\n";
	    //print "Response: \n".$client->__getLastResponse()."\n";
	    //$this->saveFetchData("addItem-Request-".date("YmdHis").".html", print_r($results, true));
	    $this->saveFetchData("addItem-Request-".date("YmdHis").".xml", $client->__getLastRequest());
	    $this->saveFetchData("addItem-Response-".date("YmdHis").".xml", $client->__getLastResponse());
        } catch (SOAPFault $f) {
            print $f; // error handling
        }
    }
    
    //-------------------------  ReviseItem --------------------------------------------------------------
    public function modifyActiveItem(){
	$sql = "select Id,accountId from items where Status = 3";
	
	$result = mysql_query($sql);
	while($row = mysql_fetch_assoc($result)){
	    $this->setAccount($row['accountId']);
	    $sql_0 = "update items set Status = 11 where Id = '".$row['Id']."'";
	    $result_0 = mysql_query($sql_0);
	    
	    //$row['item_id'] = 98;
	    $sql_1 = "select * from items where Id = '".$row['Id']."'";
	    $result_1 = mysql_query($sql_1);
	    $row_1 = mysql_fetch_assoc($result_1);
	    
	    if($row_1['UseStandardFooter']){
	    	$sql_0 = "select * from account_sku_picture where account_id = '".$row_1['accountId']."' and sku = '".$row_1['SKU']."'";
		$result_0 = mysql_query($sql_0);
		$row_0 = mysql_fetch_assoc($result_0);
		
		$sql_01 = "select content from standard_style_template where id = '".$row_1['StandardStyleTemplateId']."' and accountId = '".$row_1['accountId']."'";
		$result_01 = mysql_query($sql_01);
		$row_01 = mysql_fetch_assoc($result_01);
		
		$row_1['Description'] = str_replace(array("%title%", "%sku%", "%picture-1%", "%picture-2%", "%picture-3%", "%picture-4%", "%picture-5%", "%description%"),
						    array(html_entity_decode($row_1['Title'], ENT_QUOTES), $row_1['SKU'], '<img src="'.$row_0['picture_1'].'" />', '<img src="'.$row_0['picture_2'].'" />', '<img src="'.$row_0['picture_3'].'" />', '<img src="'.$row_0['picture_4'].'" />', '<img src="'.$row_0['picture_5'].'" />', html_entity_decode($row_1['Description'], ENT_QUOTES)), html_entity_decode($row_01['content'], ENT_QUOTES));
	    }else{
		$row_1['Description'] = html_entity_decode($row_1['Description'], ENT_QUOTES);
	    }
	    
	    $row_1['Description'] = utf8_encode($row_1['Description']);
	    $row_1['Title'] = html_entity_decode($row_1['Title'], ENT_QUOTES);
	    $row_1['Title'] = utf8_encode($row_1['Title']);
	    
	    $sql_2 = "select * from shipping_service_options where ItemID = '".$row['Id']."'";
	    $result_2 = mysql_query($sql_2);
	    $ShippingServiceOptions = array();
	    while($row_2 = mysql_fetch_assoc($result_2)){
		$ShippingServiceOptions[] = $row_2;
	    }
	    
	    $sql_3 = "select * from international_shipping_service_option where ItemID = '".$row['Id']."'";
	    $result_3 = mysql_query($sql_3);
	    $InternationalShippingServiceOption = array();
	    while($row_3 = mysql_fetch_assoc($result_3)){
		$InternationalShippingServiceOption[] = $row_3;
	    }
	    
	    $sql_4 = "select * from picture_url where ItemID = '".$row['Id']."'";
	    //echo $sql_4;
	    //echo "<br>";
	    $result_4 = mysql_query($sql_4);
	    $PictureURL = array();
	    while($row_4 = mysql_fetch_assoc($result_4)){
		$PictureURL[] = $row_4['url'];
	    } 
	    
	    $sql_5 = "select * from attribute_set where item_id = '".$row['Id']."'";
	    $result_5 = mysql_query($sql_5);
	    $AttributeSetArray = array();
	    $i = 0;
	    while($row_5 = mysql_fetch_assoc($result_5)){
		/*
		$AttributeSetArray[$i]['AttributeSet']['attributeSetID'] = $row_5['attributeSetID'];
		
		$sql_6 = "select * from attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
		$result_6 = mysql_query($sql_6);
		$j = 0;
		while($row_6 = mysql_fetch_assoc($result_6)){
		    $AttributeSetArray[$i]['AttributeSet'][$j]['Attribute']['attributeID'] = $row_6['attributeID'];
		    $AttributeSetArray[$i]['AttributeSet'][$j]['Attribute']['Value']['ValueID'] = $row_6['ValueID'];
		    if(!empty($row_6['ValueLiteral'])){
			$AttributeSetArray[$i]['AttributeSet'][$j]['Attribute']['Value']['ValueLiteral'] = $row_6['ValueLiteral'];
		    }
		    $j++;
		}
		$i++;
		*/
		$AttributeSetArray['AttributeSet']['attributeSetID'] = $row_5['attributeSetID'];
		
		$sql_6 = "select * from attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
		$result_6 = mysql_query($sql_6);
		$j = 0;
		while($row_6 = mysql_fetch_assoc($result_6)){
		    $AttributeSetArray['AttributeSet']['Attribute'][$j]['attributeID'] = $row_6['attributeID'];
		    if(strpos($row_6['ValueID'], ",") != false){
			$ValueIDArray = explode(",", $row_6['ValueID']);
			$k = 0;
			//print_r($ValueIDArray);
			foreach($ValueIDArray as $ValueID){
			    $AttributeSetArray['AttributeSet']['Attribute'][$j]['Value'][$k]['ValueID'] = $ValueID;
			    $k++;
			}
		    }else{
			$AttributeSetArray['AttributeSet']['Attribute'][$j]['Value']['ValueID'] = $row_6['ValueID'];
		    }
		    if(!empty($row_6['ValueLiteral'])){
			$AttributeSetArray['AttributeSet']['Attribute'][$j]['Value']['ValueLiteral'] = $row_6['ValueLiteral'];
		    }
		    $j++;
		}
	    }
	    
	    $row_1['AttributeSetArray'] = $AttributeSetArray;
	    $row_1['ShippingServiceOptions'] = $ShippingServiceOptions;
	    $row_1['InternationalShippingServiceOption'] = $InternationalShippingServiceOption;
	    $row_1['PictureURL'] = $PictureURL;
	    
	    //print_r($row_1);
	    //exit;
	    $this->reviseItem($row_1);
	}
    }
    
    private function reviseItem($item){
	$ShipToLocations = array();
	$sql = "select id from site where name = '".$item['Site']."'";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$this->configEbay($row['id']);
	try {
	    $client = new eBaySOAP($this->session);
	    $Version = '607';
	    
	    $itemArray = array();
	    
	    if(count($item['AttributeSetArray']) > 0){
		$itemArray['AttributeSetArray'] = $item['AttributeSetArray'];
	    }
	    
	    if(!empty($item['BuyItNowPrice']) && $item['BuyItNowPrice'] != 0 && $itemArray['BuyItNowPrice'] > $itemArray['StartPrice']){
		$itemArray['BuyItNowPrice'] = $item['BuyItNowPrice'];
	    }
	    $itemArray['CategoryMappingAllowed'] = true;
	    $itemArray['Country'] = $item['Country'];
	    $itemArray['Currency'] = $item['Currency'];
	    $itemArray['Description'] = $item['Description'];
	    if(!empty($item['DispatchTimeMax'])){
		$itemArray['DispatchTimeMax'] = $item['DispatchTimeMax'];
	    }
	    $itemArray['ItemID'] = $item['ItemID'];
	    
	    $itemArray['ListingDuration'] = $item['ListingDuration'];
	    
	    if($item['BoldTitle'] == true){
		$itemArray['ListingEnhancement'][] = "BoldTitle";
	    }
	    if($item['Border'] == true){
		$itemArray['ListingEnhancement'][] = "Border";
	    }
	    if($item['Featured'] == true){
		$itemArray['ListingEnhancement'][] = "Featured";
	    }
	    if($item['Highlight'] == true){
		$itemArray['ListingEnhancement'][] = "Highlight";
	    }
	    if($item['HomePageFeatured'] == true){
		$itemArray['ListingEnhancement'][] = "HomePageFeatured";
	    }
	    $itemArray['ListingType'] = $item['ListingType'];
	    if(!empty($item['Location'])){
		$itemArray['Location'] = $item['Location'];
	    }
	    $itemArray['PaymentMethods'] = $item['PaymentMethods'];
	    $itemArray['PayPalEmailAddress'] = $item['PayPalEmailAddress'];
	    //PictureDetails
	    if($item['GalleryTypeFeatured']){
		$itemArray['PictureDetails']['GalleryType'] = "Featured";
	    }
	    if($item['GalleryTypeGallery']){
		$itemArray['PictureDetails']['GalleryType'] = "Gallery";
	    }
	    if($item['GalleryTypePlus']){
		$itemArray['PictureDetails']['GalleryType'] = "Plus";
	    }
	    if(!empty($item['GalleryURL'])){
		$itemArray['PictureDetails']['GalleryURL'] = $item['GalleryURL'];
	    }
	    if(!empty($item['PictureURL']) && is_array($item['PictureURL'])){
		$i = 0;
		foreach($item['PictureURL'] as $p){
		    $itemArray['PictureDetails']['PictureURL'][$i] = $p;
		    $i++;
		}
	    }
	    if(!empty($item['PostalCode'])){
		$itemArray['PostalCode'] = $item['PostalCode'];
	    }
	    $itemArray['PrimaryCategory']['CategoryID'] = $item['PrimaryCategoryCategoryID'];
	    $itemArray['Quantity'] = $item['Quantity'];
	    
	    
	    if(!empty($item['ReturnPolicyReturnsAcceptedOption'])){
		$itemArray['ReturnPolicy']['ReturnsAcceptedOption'] = $item['ReturnPolicyReturnsAcceptedOption'];
		if(!empty($item['ReturnPolicyDescription'])){
		    $itemArray['ReturnPolicy']['Description'] = $item['ReturnPolicyDescription'];
		}
		if(!empty($item['ReturnPolicyRefundOption'])){
		    $itemArray['ReturnPolicy']['RefundOption'] = $item['ReturnPolicyRefundOption'];
		}
		if(!empty($item['ReturnPolicyReturnsWithinOption'])){
		    $itemArray['ReturnPolicy']['ReturnsWithinOption'] = $item['ReturnPolicyReturnsWithinOption'];
		}
		if(!empty($item['ReturnPolicyShippingCostPaidByOption'])){
		    $itemArray['ReturnPolicy']['ShippingCostPaidByOption'] = $item['ReturnPolicyShippingCostPaidByOption'];
		}
	    }
	    
	    if(!empty($item['SecondaryCategoryCategoryID'])){
		$itemArray['SecondaryCategory']['CategoryID'] = $item['SecondaryCategoryCategoryID'];
	    }
	   
	    if(!empty($item['InsuranceOption'])){
		$itemArray['ShippingDetails']['InsuranceDetails']['InsuranceOption'] = $item['InsuranceOption'];
		$itemArray['ShippingDetails']['InsuranceDetails']['InsuranceFee'] = $item['InsuranceFee'];
	    }
	    if(!empty($item['InternationalInsurance'])){
		$itemArray['ShippingDetails']['InternationalInsuranceDetails']['InsuranceOption'] = $item['InternationalInsurance'];
		$itemArray['ShippingDetails']['InternationalInsuranceDetails']['InsuranceFee'] = $item['InternationalInsuranceFee'];
	    }
	    $itemArray['ShippingDetails']['ShippingType'] = $item['ShippingType'];
	    if(!empty($item['ShippingServiceOptions']) && is_array($item['ShippingServiceOptions'])){
		$i = 0;
		foreach($item['ShippingServiceOptions'] as $s){
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['FreeShipping'] = $s['FreeShipping'];
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingService'] = $s['ShippingService'];
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingServiceCost'] = $s['ShippingServiceCost'];
		    if(!empty($s['ShippingServiceAdditionalCost'])){
			$itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingServiceAdditionalCost'] = $s['ShippingServiceAdditionalCost'];
		    }
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingServicePriority'] = $s['ShippingServicePriority'];
		    $i++;
		}
	    }
	    if(!empty($item['InternationalShippingServiceOption']) && is_array($item['InternationalShippingServiceOption'])){
		$j = 0;
		foreach($item['InternationalShippingServiceOption'] as $i){
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingService'] = $i['ShippingService'];
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingServiceCost'] = $i['ShippingServiceCost'];
		    if(!empty($i['ShippingServiceAdditionalCost'])){
			$itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingServiceAdditionalCost'] = $i['ShippingServiceAdditionalCost'];
		    }
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingServicePriority'] = $i['ShippingServicePriority'];
		    if(!empty($i['ShipToLocation'])){
			if(strpos($i['ShipToLocation'], ',') != false){
			    //echo "test1";
			    $stl_array = explode(',', $i['ShipToLocation']);
			    foreach($stl_array as $stl){
				if(!in_array($stl, $ShipToLocations)){
				    array_push($ShipToLocations, $stl);
				}
			    }
			    //$ShipToLocations = array_merge($ShipToLocations, explode(',', $i['ShipToLocation']));
			    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShipToLocation'] = explode(',', $i['ShipToLocation']);
			}else{
			    //echo "test2";
			    if(!in_array($i['ShipToLocation'], $ShipToLocations)){
				array_push($ShipToLocations, $i['ShipToLocation']);
			    }
			    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShipToLocation'] = $i['ShipToLocation'];
			}
		    }
		    $j++;
		}
	    }
	    
	    //print_r($itemArray['ShippingDetails']['InternationalShippingServiceOption']);
	    //exit;
	    //ShipToLocations
	    /*
	    switch($item['Site']){
		case "US":
		    $itemArray['ShipToLocations'] = "US";
		break;
	    
		case "UK":
		    $itemArray['ShipToLocations'] = "GB";
		break;
	    
		case "Australia":
		    $itemArray['ShipToLocations'] = "AU";
		break;
	    
		case "France":
		    $itemArray['ShipToLocations'] = "Europe";
		break;
	    }
	    */
	    //$itemArray['ShipToLocations'] = "Worldwide";
	    if(!empty($ShipToLocations)){
		$itemArray['ShipToLocations'] = $ShipToLocations;
	    }
	    $itemArray['BuyerResponsibleForShipping'] = false; 
	    $itemArray['ShippingTermsInDescription'] = true;
	    
	    $itemArray['Site'] = $item['Site'];
	    $itemArray['SKU'] = $item['SKU'];
	    if(!empty($item['StartPrice']) && $item['StartPrice'] != 0){
		$itemArray['StartPrice'] = $item['StartPrice'];
	    }
	   
	    if(!empty($item['StoreCategoryID'])){
		$itemArray['Storefront']['StoreCategoryID'] = $item['StoreCategoryID'];
		if(!empty($item['StoreCategory2ID'])){
		    $itemArray['Storefront']['StoreCategory2ID'] = $item['StoreCategory2ID'];
		}else{
		    $itemArray['Storefront']['StoreCategory2ID'] = 0;
		}
	    }
	    if(!empty($item['SubTitle'])){
		$itemArray['SubTitle'] = $item['SubTitle'];
	    }
	    $itemArray['Title'] = $item['Title'];
	   
	    //print_r($itemArray);
	    //exit;
	    $params = array('Version' => $Version,
			    'Item' => $itemArray);
	    
	    $results = $client->ReviseItem($params);
	    //print_r($results);
	   
	    
	    if(!empty($results->Errors)){
		//$sql_0 = "update items set Status = 3 where Id = '".$item['Id']."'";
		//$result_0 = mysql_query($sql_0);
		
		if(is_array($results->Errors)){
		    $temp = '';
		    foreach($results->Errors as $error){
			echo $error->ShortMessage." : ";
			echo $error->LongMessage."<br>";
			$temp .= $error->LongMessage;
		    }
		    $this->log("revise", $item['Id'] ." " . $temp, "error");
		}else{
		    echo $results->Errors->ShortMessage." : ";
		    echo $results->Errors->LongMessage."<br>";
		    $this->log("revise", $item['Id'] ." " . $results->Errors->LongMessage, "error");
		}
		if(!empty($results->ItemID)){
		    $sql = "update items set Status='2' where Id = '".$item['Id']."'";
		    echo $sql;
		    $result = mysql_query($sql);
		    $this->log("revise", $item['Id']." revise success, ItemID is ".$results->ItemID);
		}
	    }elseif(!empty($results->ItemID)){
		$sql = "update items set Status='2' where Id = '".$item['Id']."'";
		echo $sql;
		$result = mysql_query($sql);
		$this->log("revise", $item['Id']." revise success, ItemID is ".$results->ItemID);
	    }
	    
	    if(!empty($results->faultcode)){
		//$sql_0 = "update items set Status = 3 where Id = '".$item['Id']."'";
		//$result_0 = mysql_query($sql_0);
		$this->log("revise", $item['Id'] ." " . $results->faultcode . ": " . $results->faultstring, "error");

	    }
	    //----------   debug --------------------------------
	    //print "Request: \n".$client->__getLastRequest() ."\n";
	    //print "Response: \n".$client->__getLastResponse()."\n";
	    //$this->saveFetchData("addItem-Request-".date("YmdHis").".html", print_r($results, true));
	    $this->saveFetchData("reviseItem-Request-".date("YmdHis").".xml", $client->__getLastRequest());
	    $this->saveFetchData("reviseItem-Response-".date("YmdHis").".xml", $client->__getLastResponse());
        } catch (SOAPFault $f) {
            print $f; // error handling
        }
    }
    
    //--------------------------  Relist ----------------------------------------------------------------
    public function reUploadItem(){
	$sql = "select Id,accountId from items where Status = 4";
	
	$result = mysql_query($sql);
	while($row = mysql_fetch_assoc($result)){
	    $this->setAccount($row['accountId']);
	    $template_status = $this->getItemTemplateStatus($row['Id']);
	    if($template_status !=2 && $template_status != 3){
		switch($template_status){
		    case 0:
			$status = "new";
		    break;
		
		    case 1:
			$status = "waiting for approve";
		    break;
		
		    case 4:
			$status = "under review";
		    break;
		
		    case 5:
			$status = "inactive";
		    break;
		}
		$this->log("relist", $row['Id'] . " template status is ".$status, "warn");
		continue;
	    }
	    
	    $sql_0 = "update items set Status = 12 where Id = '".$row['Id']."'";
	    $result_0 = mysql_query($sql_0);
	    //$row['item_id'] = 98;
	    $sql_1 = "select * from items where Id = '".$row['Id']."'";
	    $result_1 = mysql_query($sql_1);
	    $row_1 = mysql_fetch_assoc($result_1);
	    
	    if($row_1['UseStandardFooter']){
	    	$sql_0 = "select * from account_sku_picture where account_id = '".$row_1['accountId']."' and sku = '".$row_1['SKU']."'";
		$result_0 = mysql_query($sql_0);
		$row_0 = mysql_fetch_assoc($result_0);
		
		$sql_01 = "select content from standard_style_template where id = '".$row_1['StandardStyleTemplateId']."' and accountId = '".$row_1['accountId']."'";
		$result_01 = mysql_query($sql_01);
		$row_01 = mysql_fetch_assoc($result_01);
		
		$row_1['Description'] = str_replace(array("%title%", "%sku%", "%picture-1%", "%picture-2%", "%picture-3%", "%picture-4%", "%picture-5%", "%description%"),
						    array(html_entity_decode($row_1['Title'], ENT_QUOTES), $row_1['SKU'], '<img src="'.$row_0['picture_1'].'" />', '<img src="'.$row_0['picture_2'].'" />', '<img src="'.$row_0['picture_3'].'" />', '<img src="'.$row_0['picture_4'].'" />', '<img src="'.$row_0['picture_5'].'" />', html_entity_decode($row_1['Description'], ENT_QUOTES)), html_entity_decode($row_01['content'], ENT_QUOTES));
	    }else{
		$row_1['Description'] = html_entity_decode($row_1['Description'], ENT_QUOTES);
	    }
	    
	    $row_1['Description'] = utf8_encode($row_1['Description']);
	    $row_1['Title'] = html_entity_decode($row_1['Title'], ENT_QUOTES);
	    
	    $sql_2 = "select * from shipping_service_options where ItemID = '".$row['Id']."'";
	    $result_2 = mysql_query($sql_2);
	    $ShippingServiceOptions = array();
	    while($row_2 = mysql_fetch_assoc($result_2)){
		$ShippingServiceOptions[] = $row_2;
	    }
	    
	    $sql_3 = "select * from international_shipping_service_option where ItemID = '".$row['Id']."'";
	    $result_3 = mysql_query($sql_3);
	    $InternationalShippingServiceOption = array();
	    while($row_3 = mysql_fetch_assoc($result_3)){
		$InternationalShippingServiceOption[] = $row_3;
	    }
	    
	    $sql_4 = "select * from picture_url where ItemID = '".$row['Id']."'";
	    //echo $sql_4;
	    //echo "<br>";
	    $result_4 = mysql_query($sql_4);
	    $PictureURL = array();
	    while($row_4 = mysql_fetch_assoc($result_4)){
		$PictureURL[] = $row_4['url'];
	    } 
	    
	    $sql_5 = "select * from attribute_set where item_id = '".$row['Id']."'";
	    $result_5 = mysql_query($sql_5);
	    $AttributeSetArray = array();
	    $i = 0;
	    while($row_5 = mysql_fetch_assoc($result_5)){
		/*
		$AttributeSetArray[$i]['AttributeSet']['attributeSetID'] = $row_5['attributeSetID'];
		
		$sql_6 = "select * from attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
		$result_6 = mysql_query($sql_6);
		$j = 0;
		while($row_6 = mysql_fetch_assoc($result_6)){
		    $AttributeSetArray[$i]['AttributeSet'][$j]['Attribute']['attributeID'] = $row_6['attributeID'];
		    $AttributeSetArray[$i]['AttributeSet'][$j]['Attribute']['Value']['ValueID'] = $row_6['ValueID'];
		    if(!empty($row_6['ValueLiteral'])){
			$AttributeSetArray[$i]['AttributeSet'][$j]['Attribute']['Value']['ValueLiteral'] = $row_6['ValueLiteral'];
		    }
		    $j++;
		}
		$i++;
		*/
		$AttributeSetArray['AttributeSet']['attributeSetID'] = $row_5['attributeSetID'];
		
		$sql_6 = "select * from attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
		$result_6 = mysql_query($sql_6);
		$j = 0;
		while($row_6 = mysql_fetch_assoc($result_6)){
		    $AttributeSetArray['AttributeSet']['Attribute'][$j]['attributeID'] = $row_6['attributeID'];
		    if(strpos($row_6['ValueID'], ",") != false){
			$ValueIDArray = explode(",", $row_6['ValueID']);
			$k = 0;
			//print_r($ValueIDArray);
			foreach($ValueIDArray as $ValueID){
			    $AttributeSetArray['AttributeSet']['Attribute'][$j]['Value'][$k]['ValueID'] = $ValueID;
			    $k++;
			}
		    }else{
			$AttributeSetArray['AttributeSet']['Attribute'][$j]['Value']['ValueID'] = $row_6['ValueID'];
		    }
		    if(!empty($row_6['ValueLiteral'])){
			$AttributeSetArray['AttributeSet']['Attribute'][$j]['Value']['ValueLiteral'] = $row_6['ValueLiteral'];
		    }
		    $j++;
		}
	    }
	    
	    $row_1['AttributeSetArray'] = $AttributeSetArray;
	    $row_1['ShippingServiceOptions'] = $ShippingServiceOptions;
	    $row_1['InternationalShippingServiceOption'] = $InternationalShippingServiceOption;
	    $row_1['PictureURL'] = $PictureURL;
	    $row_1['ListingDuration'] = "Days_7";
	    //print_r($row_1);
	    //exit;
	    $this->reListItem($row_1);
	}
    }
    
    private function reListItem($item){
	$ShipToLocations = array();
	$sql = "select id from site where name = '".$item['Site']."'";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$this->configEbay($row['id']);
	try {
	    $client = new eBaySOAP($this->session);
	    $Version = '607';
	    
	    $itemArray = array();
	    
	    if(count($item['AttributeSetArray']) > 0){
		$itemArray['AttributeSetArray'] = $item['AttributeSetArray'];
	    }
	    
	    if(!empty($item['BuyItNowPrice']) && $item['BuyItNowPrice'] != 0 && $itemArray['BuyItNowPrice'] > $itemArray['StartPrice']){
		$itemArray['BuyItNowPrice'] = $item['BuyItNowPrice'];
	    }
	    $itemArray['CategoryMappingAllowed'] = true;
	    $itemArray['Country'] = $item['Country'];
	    $itemArray['Currency'] = $item['Currency'];
	    $itemArray['Description'] = $item['Description'];
	    if(!empty($item['DispatchTimeMax'])){
		$itemArray['DispatchTimeMax'] = $item['DispatchTimeMax'];
	    }
	    $itemArray['ItemID'] = $item['ItemID'];
	    
	    $itemArray['ListingDuration'] = $item['ListingDuration'];
	    
	    if($item['BoldTitle'] == true){
		$itemArray['ListingEnhancement'][] = "BoldTitle";
	    }
	    if($item['Border'] == true){
		$itemArray['ListingEnhancement'][] = "Border";
	    }
	    if($item['Featured'] == true){
		$itemArray['ListingEnhancement'][] = "Featured";
	    }
	    if($item['Highlight'] == true){
		$itemArray['ListingEnhancement'][] = "Highlight";
	    }
	    if($item['HomePageFeatured'] == true){
		$itemArray['ListingEnhancement'][] = "HomePageFeatured";
	    }
	    $itemArray['ListingType'] = $item['ListingType'];
	    if(!empty($item['Location'])){
		$itemArray['Location'] = $item['Location'];
	    }
	    $itemArray['Country'] = 'HK';
	    
	    $itemArray['PaymentMethods'] = $item['PaymentMethods'];
	    $itemArray['PayPalEmailAddress'] = $item['PayPalEmailAddress'];
	    //PictureDetails
	    if($item['GalleryTypeFeatured']){
		$itemArray['PictureDetails']['GalleryType'] = "Featured";
	    }
	    if($item['GalleryTypeGallery']){
		$itemArray['PictureDetails']['GalleryType'] = "Gallery";
	    }
	    if($item['GalleryTypePlus']){
		$itemArray['PictureDetails']['GalleryType'] = "Plus";
	    }
	    if(!empty($item['GalleryURL'])){
		$itemArray['PictureDetails']['GalleryURL'] = $item['GalleryURL'];
	    }
	    if(!empty($item['PictureURL']) && is_array($item['PictureURL'])){
		$i = 0;
		foreach($item['PictureURL'] as $p){
		    $itemArray['PictureDetails']['PictureURL'][$i] = $p;
		    $i++;
		}
	    }
	    if(!empty($item['PostalCode'])){
		$itemArray['PostalCode'] = $item['PostalCode'];
	    }
	    $itemArray['PrimaryCategory']['CategoryID'] = $item['PrimaryCategoryCategoryID'];
	    $itemArray['Quantity'] = $item['Quantity'];
	
	    if(!empty($item['ReturnPolicyReturnsAcceptedOption'])){
		$itemArray['ReturnPolicy']['ReturnsAcceptedOption'] = $item['ReturnPolicyReturnsAcceptedOption'];
		if(!empty($item['ReturnPolicyDescription'])){
		    $itemArray['ReturnPolicy']['Description'] = $item['ReturnPolicyDescription'];
		}
		if(!empty($item['ReturnPolicyRefundOption'])){
		    $itemArray['ReturnPolicy']['RefundOption'] = $item['ReturnPolicyRefundOption'];
		}
		if(!empty($item['ReturnPolicyReturnsWithinOption'])){
		    $itemArray['ReturnPolicy']['ReturnsWithinOption'] = $item['ReturnPolicyReturnsWithinOption'];
		}
		if(!empty($item['ReturnPolicyShippingCostPaidByOption'])){
		    $itemArray['ReturnPolicy']['ShippingCostPaidByOption'] = $item['ReturnPolicyShippingCostPaidByOption'];
		}
	    }
	    
	    if(!empty($item['SecondaryCategoryCategoryID'])){
		$itemArray['SecondaryCategory']['CategoryID'] = $item['SecondaryCategoryCategoryID'];
	    }
	    if(!empty($item['InsuranceOption'])){
		$itemArray['ShippingDetails']['InsuranceDetails']['InsuranceOption'] = $item['InsuranceOption'];
		$itemArray['ShippingDetails']['InsuranceDetails']['InsuranceFee'] = $item['InsuranceFee'];
	    }
	    if(!empty($item['InternationalInsurance'])){
		$itemArray['ShippingDetails']['InternationalInsuranceDetails']['InsuranceOption'] = $item['InternationalInsurance'];
		$itemArray['ShippingDetails']['InternationalInsuranceDetails']['InsuranceFee'] = $item['InternationalInsuranceFee'];
	    }
	    $itemArray['ShippingDetails']['ShippingType'] = $item['ShippingType'];
	    if(!empty($item['ShippingServiceOptions']) && is_array($item['ShippingServiceOptions'])){
		$i = 0;
		foreach($item['ShippingServiceOptions'] as $s){
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['FreeShipping'] = $s['FreeShipping'];
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingService'] = $s['ShippingService'];
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingServiceCost'] = $s['ShippingServiceCost'];
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingServiceAdditionalCost'] = $s['ShippingServiceAdditionalCost'];
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingServicePriority'] = $s['ShippingServicePriority'];
		    $i++;
		}
	    }
	    if(!empty($item['InternationalShippingServiceOption']) && is_array($item['InternationalShippingServiceOption'])){
		$j = 0;
		foreach($item['InternationalShippingServiceOption'] as $i){
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingService'] = $i['ShippingService'];
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingServiceCost'] = $i['ShippingServiceCost'];
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingServiceAdditionalCost'] = $i['ShippingServiceAdditionalCost'];
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingServicePriority'] = $i['ShippingServicePriority'];
		    if(!empty($i['ShipToLocation'])){
			if(strpos($i['ShipToLocation'], ',') != false){
			    //echo "test1";
			    $stl_array = explode(',', $i['ShipToLocation']);
			    foreach($stl_array as $stl){
				if(!in_array($stl, $ShipToLocations)){
				    array_push($ShipToLocations, $stl);
				}
			    }
			    //$ShipToLocations = array_merge($ShipToLocations, explode(',', $i['ShipToLocation']));
			    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShipToLocation'] = explode(',', $i['ShipToLocation']);
			}else{
			    //echo "test2";
			    if(!in_array($i['ShipToLocation'], $ShipToLocations)){
				array_push($ShipToLocations, $i['ShipToLocation']);
			    }
			    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShipToLocation'] = $i['ShipToLocation'];
			}
		    }
		    $j++;
		}
	    }
	    
	    //print_r($itemArray['ShippingDetails']['InternationalShippingServiceOption']);
	    //exit;
	    //ShipToLocations
	    //$itemArray['ShipToLocations'] = "Worldwide";
	    if(!empty($ShipToLocations)){
		$itemArray['ShipToLocations'] = $ShipToLocations;
	    }
	    $itemArray['BuyerResponsibleForShipping'] = false; 
	    $itemArray['ShippingTermsInDescription'] = true;
	    
	    $itemArray['Site'] = $item['Site'];
	    $itemArray['SKU'] = $item['SKU'];
	    if(!empty($item['StartPrice']) && $item['StartPrice'] != 0){
		$itemArray['StartPrice'] = $item['StartPrice'];
	    }
	    
	    if(!empty($item['StoreCategoryID'])){
		$itemArray['Storefront']['StoreCategoryID'] = $item['StoreCategoryID'];
		if(!empty($item['StoreCategory2ID'])){
		    $itemArray['Storefront']['StoreCategory2ID'] = $item['StoreCategory2ID'];
		}else{
		    $itemArray['Storefront']['StoreCategory2ID'] = 0;
		}
	    }
	    if(!empty($item['SubTitle'])){
		$itemArray['SubTitle'] = $item['SubTitle'];
	    }
	    $itemArray['Title'] = $item['Title'];
	   
	    //print_r($itemArray);
	    $params = array('Version' => $Version,
			    'Item' => $itemArray);
	    
	    $results = $client->RelistItem($params);
	    //print_r($results);
	    
	  
	    if(!empty($results->Errors)){
		//$sql_0 = "update items set Status = 4 where Id = '".$item['Id']."'";
		//$result_0 = mysql_query($sql_0);
		
		if(is_array($results->Errors)){
		    $temp = '';
		    foreach($results->Errors as $error){
			echo $error->ShortMessage." : ";
			echo $error->LongMessage."<br>";
			$temp .= $error->LongMessage;
		    }
		    $this->log("relist", $item['Id'] ." " . $temp, (empty($results->ItemID)?"error":"warn"));
		}else{
		    echo $results->Errors->ShortMessage." : ";
		    echo $results->Errors->LongMessage."<br>";
		    $this->log("relist", $item['Id'] ." " . $results->Errors->LongMessage, (empty($results->ItemID)?"error":"warn"));
		}
		
		if(!empty($results->ItemID)){
		    foreach($results->Fees->Fee as $fee){
			switch($fee->Name){
			    case "InsertionFee":
				$InsertionFee = $fee->Fee->_;
			    break;
			
			    case "ListingFee":
				$ListingFee = $fee->Fee->_;
			    break;
			}
			
		    }
		
		    $sql = "update items set ItemID = '".$results->ItemID."',Status='2',StartTime='".$results->StartTime."',
		    EndTime='".$results->EndTime."',InsertionFee='".$InsertionFee."',ListingFee='".$ListingFee."' where Id = '".$item['Id']."'";
		    echo $sql;
		    $result = mysql_query($sql);
		    
		    $sql_1 = "select parentId from items where Id = '".$item['Id']."'";
		    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		    $row_1 = mysql_fetch_assoc($result_1);
		    
		    $sql_2 = "update items set Relist = 'Y' where Id = ".$row_1['parentId'];
		    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    
		    $this->log("relist", $item['Id']." relist success, ItemID is ".$results->ItemID);
		}
	    }elseif(!empty($results->ItemID)){
		foreach($results->Fees->Fee as $fee){
		    switch($fee->Name){
			case "InsertionFee":
			    $InsertionFee = $fee->Fee->_;
			break;
		    
			case "ListingFee":
			    $ListingFee = $fee->Fee->_;
			break;
		    }
		    
		}
		    
		$sql = "update items set ItemID = '".$results->ItemID."',Status='2',StartTime='".$results->StartTime."',
		EndTime='".$results->EndTime."',InsertionFee='".$InsertionFee."',ListingFee='".$ListingFee."' where Id = '".$item['Id']."'";
		echo $sql;
		$result = mysql_query($sql);
		
		$sql_1 = "select parentId from items where Id = '".$item['Id']."'";
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		$row_1 = mysql_fetch_assoc($result_1);
		
		$sql_2 = "update items set Relist = 'Y' where Id = ".$row_1['parentId'];
		$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		
		$this->log("relist", $item['Id']." relist success, ItemID is ".$results->ItemID);
	    }
	    
	    if(!empty($results->faultcode)){
		//$sql_0 = "update items set Status = 4 where Id = '".$item['Id']."'";
		//$result_0 = mysql_query($sql_0);
		$this->log("relist", $item['Id'] ." " . $results->faultcode . ": " . $results->faultstring, "error");

	    }
	    
	    //----------   debug --------------------------------
	    //print "Request: \n".$client->__getLastRequest() ."\n";
	    //print "Response: \n".$client->__getLastResponse()."\n";
	    //$this->saveFetchData("addItem-Request-".date("YmdHis").".html", print_r($results, true));
	    $this->saveFetchData("relistItem-Request-".date("YmdHis").".xml", $client->__getLastRequest());
	    $this->saveFetchData("relistItem-Response-".date("YmdHis").".xml", $client->__getLastResponse());
        } catch (SOAPFault $f) {
            print $f; // error handling
        }
    }
    
    //-------------------------  End Item ----------------------------------------------------------------
    public function endListingItem(){
	$sql = "select Id,accountId from items where Status = 7";
	$result = mysql_query($sql);
	while($row = mysql_fetch_assoc($result)){
	    $this->setAccount($row['accountId']);
	    $sql_0 = "update items set Status = 13 where Id = '".$row['Id']."'";
	    $result_0 = mysql_query($sql_0);
	    //$row['item_id'] = 98;
	    $sql_1 = "select * from items where Id = '".$row['Id']."'";
	    $result_1 = mysql_query($sql_1);
	    $row_1 = mysql_fetch_assoc($result_1);
	    $this->endItem($row_1);
	}
    }
    
    private function endItem($item){
	$sql = "select id from site where name = '".$item['Site']."'";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$this->configEbay($row['id']);
	try {
	    $client = new eBaySOAP($this->session);
	    $Version = '607';
	
	    $params = array('Version' => $Version,
			    'ItemID' => $item['ItemID'],
			    'EndingReason' => 'NotAvailable');
		
	    $results = $client->EndItem($params);
	    
	    if(!empty($results->Errors)){
		if(is_array($results->Errors)){
		    $temp = '';
		    foreach($results->Errors as $error){
			echo $error->ShortMessage." : ";
			echo $error->LongMessage."<br>";
			$temp .= $error->LongMessage;
		    }
		    $this->log("end", $item['Id'] ." " . $temp, (empty($results->ItemID)?"error":"warn"));
		}else{
		    echo $results->Errors->ShortMessage." : ";
		    echo $results->Errors->LongMessage."<br>";
		    $this->log("end", $item['Id'] ." " . $results->Errors->LongMessage, (empty($results->ItemID)?"error":"warn"));
		}
	    }elseif($results->Ack == "Success"){
		$sql_2 = "update items set Status = 9,EndTime = '".$results->EndTime."' where Id = '".$item['Id']."'";
		echo $sql_2."<br>";
		$result_2 = mysql_query($sql_2);
	    }
	    
	    $this->saveFetchData("endItem-Request-".date("YmdHis").".xml", $client->__getLastRequest());
	    $this->saveFetchData("endItem-Response-".date("YmdHis").".xml", $client->__getLastResponse());
	    
	} catch (SOAPFault $f) {
            print $f; // error handling
        }
    }
    
    //-------------------------   Get Seller Listing ------------------------------------------------------
    /*
    	Status
	0 : ready
	1 : schedule
	2 : selling
	3 : revise
	4 : relist
	5 : unsold
	6 : sold
	7 : end
	8 : waiting to relist
	9 : ended
	
	10: uploading
	11: reviseing
	12: relisting
	13: ending
    */
    
    public function getSalesReport(){
	$today = date("Y-m-d");
	//$lastWeekToday = date("Y-m-d", strtotime("last Monday", strtotime("2009-05-25")));
	$lastWeekToday = date("Y-m-d", strtotime("last Monday", strtotime(date("Y-m-d", strtotime("-4 week")))));
	echo $lastWeekToday;
	exit;
	
	//$lastWeekToday = "";
	date("D", strtotime($today));
	$sql = "select CurrentPrice,QuantitySold,SKU,Title from items where StartTime > '".$lastWeekToday."' and UserID = 'bestnbestonline' group by DATE_FORMAT(date,format)";
	echo $sql;
	echo "<br>";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	while($row = mysql_fetch_assoc($result)){
	    $array[] = $row;
	}
	print_r($array);
    }
    //-----------------   Schedule  -----------------------------------------------------------------------------
    /*
    ALTER TABLE `schedule` ADD `account_id` INT NOT NULL ;
    ALTER TABLE `schedule` ADD INDEX ( `account_id` ) ;     
    */
    public function addSkuScheduleTime(){
	if(!empty($_POST['time'])){
	    session_start();
	    if(@!is_array($_SESSION['Schedule'][$_POST['sku'].'-'.$_POST['dayTime']])){
		$_SESSION['Schedule'][$_POST['sku'].'-'.$_POST['dayTime']] = array();
	    }
	    if(@!in_array($_POST['time'], $_SESSION['Schedule'][$_POST['sku'].'-'.$_POST['dayTime']])){
		$_SESSION['Schedule'][$_POST['sku'].'-'.$_POST['dayTime']][] = $_POST['time'];
	    }
	}
	print_r($_SESSION['Schedule'][$_POST['sku'].'-'.$_POST['dayTime']]);
    }
    
    public function deleteSkuScheduleTime(){
	session_start();
	$id_array = explode(",", $_POST['id']);
	print_r($id_array);
	foreach($id_array as $id){
	    unset($_SESSION['Schedule'][$_POST['sku'].'-'.$_POST['dayTime']][$id]);
	}
	/*
	$i = 0;
	foreach($_SESSION[$_POST['sku'].'-'.$_POST['dayTime']] as $s){
	    $_SESSION[$_POST['sku'].'-'.$_POST['dayTime']][$i] = $s;
	    $i++;
	}
	*/
	//sort($_SESSION[$_POST['sku'].'-'.$_POST['dayTime']]);
	print_r($_SESSION['Schedule'][$_POST['sku'].'-'.$_POST['dayTime']]);
    }
    
    public function deleteAllSkuScheduleTime(){
	session_start();
	unset($_SESSION['Schedule'][$_POST['sku'].'-'.$_POST['dayTime']]);
    }
    
    public function getSkuScheduleTime(){
	session_start();
	//print_r($_SESSION[$_GET['sku'].'-'.$_GET['dayTime']]);
	//$array = array(array("time"=>"13:21"), array("time"=>"13:30"));
	if(@is_array($_SESSION['Schedule'][$_GET['sku'].'-'.$_GET['dayTime']])){
	    sort($_SESSION['Schedule'][$_GET['sku'].'-'.$_GET['dayTime']]);
	    $data = array();
	    $i = 0;
	    foreach($_SESSION['Schedule'][$_GET['sku'].'-'.$_GET['dayTime']] as $s){
		$data[$i]['time'] = $s;
		$i++;
	    }
	    echo json_encode($data);
	}else{
	    echo json_encode(array());
	}
	//print_r($_SESSION['Schedule']);
    }
    
    public function saveSkuScheduleTime(){
	/*
	session_start();
	$sql = "select Id from items where SKU = '".$_POST['sku']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$item_id = $row['Id'];
	
	$temp = explode("-", $_POST['dayTime']);
	foreach($_SESSION[$_POST['sku'].'-'.$_POST['dayTime']] as $s){
	    strftime("%H:%M", strtotime($s));
	    $sql = "insert into schedule (item_id,day,time) values ('".$item_id."','".$temp[0]."','".strftime("%H:%M", strtotime($s))."')";
	    echo $sql;
	}
	*/
    }
    
    //-----------------    Login  -----------------------------------------------------------------------------
    public function login(){
	if($_SERVER['REMOTE_ADDR'] != "127.0.0.1" && substr($_SERVER['REMOTE_ADDR'], 0, 8) != "192.168."){
	    $ip_array = json_decode(file_get_contents("http://192.168.1.168:8888/eBayBO/service.php?action=getClientIp"));
	    //file_put_contents("/tmp/xx.log", print_r($ip_array, true));
	    if(!in_array($_SERVER['REMOTE_ADDR'], $ip_array)){
		echo "{success: false}";
		return 0;
	    }
	}
	$sql = "select id,role,pagination from account where name = '".$_POST['name']."' and password = '".$_POST['password']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	
	//$_SESSION['account_id'] = $row['id'];
	if(!empty($row['id'])){
	    setcookie("account_id", $row['id'], time() + (60 * 60 * 24), '/');
	    setcookie("account_name", $_POST['name'], time() + (60 * 60 * 24), '/');
	    setcookie("role", $row['role'], time() + (60 * 60 * 24), '/');
	    setcookie("pagination", $row['pagination'], time() + (60 * 60 * 24), '/');
	    echo "{success: true}";
	}else{
	    echo "{success: false}";
	}
    }
    
    public function logout(){
	unset($_COOKIE['account_id']);
	unset($_COOKIE['account_name']);
	unset($_COOKIE['role']);
    }
    
    public function testComet(){
	$i = 1;
	while(1 == 1){
	    file_put_contents('log/t.log', $i."\n", FILE_APPEND);
	    //usleep(10000);
	    sleep(1);
	    if($i > 9){
		exit;
	    }
	    $i++;
	}
	echo "finish";
    }
    //ALTER TABLE `log` ADD `cometStatus` BOOL NOT NULL DEFAULT '0';
    public function logComet(){
	$data = "";
	$sql = "select level,type,content,time from log where cometStatus = '0' and account_id = '".$this->account_id."'";
	$result = mysql_query($sql);
	sleep(1);
	while($row = mysql_fetch_assoc($result)){
	    $data .= $row['level'].' '.$row['time'].' '.$row['content'].'<br>';
	}
	echo $data;
    }
    
    //-------------------- Mange --------------------------------------------------------------------------
    public function getAllAccount(){
	$sql = "select id,name from account";
        $result = mysql_query($sql, eBayListing::$database_connect);
        $array = array();
        while($row = mysql_fetch_assoc($result)){
            $array[] = $row;
        }
        
        echo json_encode($array);
    }
    
    public function switchAccount(){
	unset($_COOKIE['account_id']);
	setcookie("account_id", $_POST['id'], time() + (60 * 60 * 24), '/');
	echo 1;
    }
    
    public function getAlleBayAccount(){
	$sql = "select * from account";
	$result = mysql_query($sql);
	$array = array();
	while($row = mysql_fetch_assoc($result)){
		$array[] = $row;
	}
	echo json_encode(array('result'=>$array));
	mysql_free_result($result);
    }
    
    public function addeBayAccount(){
	$sql = "insert into account (name,password,token,tokenExpiry,status) values 
	('".$_POST['name']."','".$_POST['password']."','".$_POST['token']."','".$_POST['tokenExpiry']."','".$_POST['status']."')";
	$result = mysql_query($sql);
	echo $result;
    }
	
    public function updateeBayAccount(){
	$sql = "update account set name='".$_POST['name']."',password='".$_POST['password']."',
	token='".$_POST['token']."',tokenExpiry='".$_POST['tokenExpiry']."',status='".$_POST['status']."' where id = '".$_POST['id']."'";
	$result = mysql_query($sql);
	//echo $sql;
	echo $result;
    }
    
    public function getPayPalEmailAndPagination(){
	if($_POST['mPassword'] == "20091209"){
	    $sql = "select PayPalEmailAddress,pagination from account where id = '".$this->account_id."'";
	    $result = mysql_query($sql);
	    $row = mysql_fetch_assoc($result);
	    echo '[{success: true, paypalEmail: "'.$row['PayPalEmailAddress'].'", pagination: "'.$row['pagination'].'"}]';
	}else{
	    echo '[{success: true, msg: "Admin password is error!"}]';
	}
    }
    
    public function updateAccountInfo(){
	if($_POST['mPassword'] == "20091209"){
	    $sql = "select count(*) as num from account where id = '".$this->account_id."' and password = '".$_POST['oPassword']."'";
	    $result = mysql_query($sql);
	    $row = mysql_fetch_assoc($result);
	    if($row['num'] > 0){
		$sql = "update account set password='".$_POST['nPassword']."',pagination='".$_POST['pagination']."' where id = '".$this->account_id."'";
		$result = mysql_query($sql);
		echo '[{success: true, msg: "update success!"}]';
	    }else{
		echo '[{success: false, msg: "Old password is error!"}]';
	    }
	}else{
	    echo '[{success: false, msg: "Admin password is error!"}]';
	}
    }
    
    public function deleteeBayAccount(){
	$sql = "delete from account where id = '".$_POST['id']."'";
	$result = mysql_query($sql);
	echo $result;
    }
	
    public function getAlleBayProxy(){
	$sql = "select id as account_id,name as account_name from account";
	$result = mysql_query($sql);
	$seller_array = array();
	while($row = mysql_fetch_assoc($result)){
	    $seller_array[] = $row;
	}
	
	$sql = "select * from proxy";
	$result = mysql_query($sql);
	$proxy_array = array();
	while($row = mysql_fetch_assoc($result)){
	    $sql_1 = "select a.id,a.name from account as a left join account_to_proxy as atp on a.id = atp.account_id where atp.proxy_id = '".$row['id']."'";
	    $result_1 = mysql_query($sql_1);
	    $row_1 = mysql_fetch_assoc($result_1);
	    $row['account_id'] = $row_1['id'];
	    $row['account_name'] = $row_1['name'];
	    $proxy_array[] = $row;
	}
	echo json_encode(array('result'=>array('seller'=>$seller_array,'proxy'=>$proxy_array)));
	mysql_free_result($result);
    }
    
    public function addeBayProxy(){
	$sql = "select count(*) as num from account_to_proxy where account_id = '".$_POST['account_id']."'";
	//echo $sql;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	if($row['num'] ==0 ){
	    $sql = "insert into proxy (host,port) values ('".$_POST['host']."','".$_POST['port']."')";
	    //echo $sql;
	    $result = mysql_query($sql);
	    $proxy_id = mysql_insert_id();
	    
	    $sql = "insert into account_to_proxy (account_id,proxy_id) values ('".$_POST['account_id']."','".$proxy_id."')";
	    $result = mysql_query($sql);
	    echo $result;
	}else{
	    echo 0;
	}
    }
    
    public function updateeBayProxy(){
	$sql = "update proxy set host = '".$_POST['host']."', port='".$_POST['port']."' where id = '".$_POST['id']."'";
	$result = mysql_query($sql);

	$sql = "update account_to_proxy set account_id = '".$_POST['account_id']."' where proxy_id = '".$_POST['id']."'";
	$result = mysql_query($sql);
	echo $result;
    }
    
    public function deleteeBayProxy(){
	$sql = "delete from proxy where id = '".$_POST['id']."'";
	$result = mysql_query($sql);
	
	$sql = "delete from account_to_proxy where proxy_id = '".$_POST['id']."'";
	$result = mysql_query($sql);
	
	echo $result;
    }
    
    public function getToken(){
	//echo "test";
	$this->setAccount(1);
	$this->configEbay();
        try {
		$this->session->token = NULL;
		//print_r($this->session);
		//exit;
                $client = new eBaySOAP($this->session);
                
		$Version = "607";
		$RuName = "Creasion-Creasion-1ca1-4-vuhhajsuh";
                $params = array('Version' => $Version, 'RuName' => $RuName);
                $results = $client->GetSessionID($params);
		//$results->SessionID
		//echo "https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=Creasion-Creasion-1ca1-4-vldylhxcb&&sid=$results->SessionID";
		header("Location: https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=".$RuName."&sid=".$results->SessionID);
		//var_dump("https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=Creasion-Creasion-1ca1-4-vldylhxcb&&sid=$results->SessionID");
                //----------   debug --------------------------------
                //print "Request: \n".$client->__getLastRequest() ."\n";
                //print "Response: \n".$client->__getLastResponse()."\n";
        
                //return $results;
                
        } catch (SOAPFault $f) {
                print $f; // error handling
        }
    }
    //------------------------------- Log ---------------------------------------------------------------
    private function log($type, $content, $level = 'normal'){
	//print_r($_COOKIE);
	$sql = "insert into log (level,type,content,account_id) values('".$level."','".$type."','".mysql_real_escape_string($content)."','".$this->account_id."')";
	//echo $sql;
	$result = mysql_query($sql, eBayListing::$database_connect);
    }
    
    public function getUploadLog(){
	$seller = array();
	$sql = "select id,name from account";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
	    $seller[$row['id']] = $row['name'];
	}
	
	$default_start = date("Y-m-d", time() - 24 * 60 * 60);
	
	$array = array();
	$type = $_GET['type'];
	
	//if($_COOKIE['role'] == "admin"){
	    //$sql = "select count(*) as num from log where type = '".$type."'";
	//}else{
	    $sql = "select count(*) as num from log where account_id = '".$this->account_id."' and type = '".$type."'";
	//}
	
	if(!empty($_POST['id'])){
	    $sql .= " and content like '%".$_POST['id']."%'";
	}
	
	if(!empty($_POST['startDate'])){
	    $sql .= " and time > '".$_POST['startDate']."'";
	}else{
	    $sql .= " and time > '".$default_start."'";
	}
	
	if(!empty($_POST['endDate'])){
	    $sql .= " and time < '".$_POST['endDate']."'";
	}
	
	if(!empty($_POST['level'])){
	    $sql .= " and level = '".$_POST['level']."'";
	}
	
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$totalCount = $row['num'];
	
	if(empty($_POST['start']) && empty($_POST['limit'])){
	    $_POST['start'] = 0;
	    $_POST['limit'] = 20;
	}
	
	//if($_COOKIE['role'] == "admin"){	
	    //$sql = "select * from log where type = '".$type."'";
	//}else{
	    $sql = "select * from log where account_id = '".$this->account_id."' and type = '".$type."'";
	//}
	
	if(!empty($_POST['id'])){
	    $sql .= " and content like '%".$_POST['id']."%'";
	}
	
	if(!empty($_POST['startDate'])){
	    $sql .= " and time > '".$_POST['startDate']."'";
	}
	
	if(!empty($_POST['endDate'])){
	    $sql .= " and time < '".$_POST['endDate']."'";
	}
	
	if(!empty($_POST['level'])){
	    $sql .= " and level = '".$_POST['level']."'";
	}
	
	$sql .= " order by id desc limit ".$_POST['start'].",".$_POST['limit'];
	
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
	    /*
	    if(strlen($row['content']) > 130){
		$temp = "";
		for($i=0; $i< strlen($row['content']); $i++){
		    $temp .= $row['content'][$i];
		    if($i !=0 && $i % 130 == 0){
			$temp .= "<br>";
		    }
		}
		$row['content'] = $temp;
	    }
	    */
	    $row['content'] = chunk_split($row['content'], 130, "<br>");
	    $row['account'] = $seller[$row['account_id']];
	    $array[] = $row;
	}
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$array));
	mysql_free_result($result);
    }
    
    /*
    CREATE TABLE IF NOT EXISTS `sku_sales_statistics` (
	`account_id` int( 11 ) NOT NULL ,
	`sku` varchar( 30 ) NOT NULL ,
	`clock` enum( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23' ) NOT NULL ,
	`quantity` int( 11 ) NOT NULL ,
	PRIMARY KEY ( `account_id` , `sku` , `clock` )
    );
    */
    
    public function calculateSkuSales(){
	$sql = "select accountId,SKU,sum(QuantitySold) as total from items where EndTime between '".date("Y-m-d H:i:s", time() - 60 * 60)."' and now() and QuantitySold > 0 group by accountId,SKU";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	while($row = mysql_fetch_assoc($result)){
	    $sql_1 = "update sku_sales_statistics set set quantity = quantity + ".$row['total']." where account_id = '".$row['accountId']."' and sku = '".$row['SKU']."' and clock = '".date("H", time() - 30 * 60)."'";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	}
    }
    
    public function skuSaleStatistics(){
	$fields = array('clock');
	$data = array();
	
	for($i=0; $i<24; $i++){
	    $sql = "select * from ebaylisting.sku_sales_statistics where account_id = '".$this->account_id."' and clock = '".$i."'";
	    //echo $sql;
	    //echo "<br>";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    while($row = mysql_fetch_assoc($result)){
		if(!in_array($row['sku'], $fields)){
		    array_push($fields, $row['sku']);
		}
		$data[$row['clock']][$row['sku']] = $row['quantity'];;
	    }
	}

	//var_dump($fields);
	//var_dump($data);
	
	$js_fields = "[";
	$js_series = "[";
	foreach($fields as $f){
	    $js_fields .= "'".$f."', ";
	    if($f != "clock"){
	    $js_series .= "{
                xField: '".$f."',
		displayName: '".$f."'},";
	    }
	}
	$js_fields = substr($js_fields, 0, -2);
	$js_fields .= "]";
	
	$js_series = substr($js_series, 0, -1);
	$js_series .= "]";
	
	$js_data = "[";
	foreach($data as $key=>$value){
	    $js_data .= "{clock: ".$key.", ";
	    foreach($value as $id=>$name){
		$js_data .= $id.": ".$name.",";
	    }
	    $js_data = substr($js_data, 0, -1);
	    $js_data .=  "},";
	}
	$js_data = substr($js_data, 0, -1);
	$js_data .= "]";
	
	/*
	var store = new Ext.data.JsonStore({
	    fields: ['year', 'comedy', 'action', 'drama', 'thriller'],
	    data: [
		    {year: 2005, comedy: 34000000, action: 23890000, drama: 18450000, thriller: 20060000},
		    {year: 2006, comedy: 56703000, action: 38900000, drama: 12650000, thriller: 21000000},
		    {year: 2007, comedy: 42100000, action: 50410000, drama: 25780000, thriller: 23040000},
		    {year: 2008, comedy: 38910000, action: 56070000, drama: 24810000, thriller: 26940000}
		  ]
	});
	
	var chart = Ext.chart.StackedBarChart({
	    store: store,
            yField: 'clock',
            xAxis: new Ext.chart.NumericAxis({
                stackingEnabled: true
            }),
            series: [{
                xField: 'comedy',
                displayName: 'Comedy'
            },{
                xField: 'action',
                displayName: 'Action'
            },{
                xField: 'drama',
                displayName: 'Drama'
            },{
                xField: 'thriller',
                displayName: 'Thriller'
            }]
	})
	*/
	$js = "var store = new Ext.data.JsonStore({
	    fields: ".$js_fields.",
	    data: ".$js_data."
	});
	
	var chart = new Ext.chart.StackedBarChart({
	    store: store,
            yField: 'clock',
            xAxis: new Ext.chart.NumericAxis({
                stackingEnabled: true
            }),
            series: ".$js_series."
	});";
	
	echo $js;
	
    }
    
    public function updateSkuDescription(){
	$msg = "";
	switch(true){
	    case !empty($_POST['english']):
		$sql = "update template set Description = '".htmlentities($_POST['english'], ENT_QUOTES)."' where (Site = 'US' or Site = 'UK' or Site = 'Australia') and SKU = '".$_POST['sku']."'";
		$result = mysql_query($sql, eBayListing::$database_connect);
		
		$sql_1 = "update items set Description = '".htmlentities($_POST['english'], ENT_QUOTES)."' where (Site = 'US' or Site = 'UK' or Site = 'Australia') and SKU = '".$_POST['sku']."' and Status in (0,1,3,4.8)";
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		
		if($result && $result_1){
		    $msg .= "Update English language description success. ";
		}
	    
	    case !empty($_POST['french']):
		$sql = "update template set Description = '".htmlentities($_POST['french'], ENT_QUOTES)."' where Site = 'France' and SKU = '".$_POST['sku']."'";
		$result = mysql_query($sql, eBayListing::$database_connect);
		
		$sql_1 = "update items set Description = '".htmlentities($_POST['french'], ENT_QUOTES)."' where Site = 'France' and SKU = '".$_POST['sku']."' and Status in (0,1,3,4.8)";
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		
		if($result && $result_1){
		    $msg .= "Update French language description success. ";
		}
	
	    case !empty($_POST['germany']):
		$sql = "update template set Description = '".htmlentities($_POST['germany'], ENT_QUOTES)."' where Site = 'Germany' and SKU = '".$_POST['sku']."'";
		$result = mysql_query($sql, eBayListing::$database_connect);
		
		$sql_1 = "update items set Description = '".htmlentities($_POST['germany'], ENT_QUOTES)."' where Site = 'Germany' and SKU = '".$_POST['sku']."' and Status in (0,1,3,4.8)";
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		
		if($result && $result_1){
		    $msg .= "Update German language description success. ";
		}
	    break;
	}
	
	if(strlen($msg) > 3){
	    echo '{"sucess":true,"msg":"'.$msg.'"}';
	}else{
	    echo '{"sucess":false,"msg":"Update upload system failure, description is empty!"}';
	}
    }
    
    public function __destruct(){
        mysql_close(eBayListing::$database_connect);
    }
}

/*
if(!empty($argv[2])){
    $service = new eBayListing($argv[2]);
}else{
    $service = new eBayListing();
}
*/
$ebay_service = array(/*'getAllCategories', 'getStoreCategories', 'getAllStoreCategories', 'getCategoryFeatures',
		      'getAllSiteShippingServiceDetails', 'getAllSiteShippingLocationDetails', 'getShippingLocation',
		      'getAllCategory2CS', 'getAllAttributesCS',*/
		      'getStoreCategories',
		      'getAllSellerList',
		      'uploadItem', 'modifyActiveItem', 'reUploadItem', 'endListingItem',
		      'getToken', 'saveToken');   
//$service->setAccount(1);
$service = new eBayListing();
$acton = (!empty($_GET['action'])?$_GET['action']:$argv[1]);
if(isset($_GET['action']) && strpos($acton, 'Template')){
    require_once 'module/template.php';
    $template = new Template($service->getAccount());
    $template->$acton();
}elseif(isset($_GET['action']) && strpos($acton, 'Item')){
    require_once 'module/item.php';
    $item = new Item($service->getAccount());
    $item->$acton();
}elseif(in_array($acton, $ebay_service)){
    require_once 'module/ebay.php';
    $ebay = new Ebay($service->getAccount());
    $ebay->$acton();
}else{
    if(method_exists($service, $acton)){
	$service->$acton();
    }
}
?>