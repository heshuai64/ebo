<?php

require_once 'eBaySOAP.php';

function errorLog($file_name, $data){
    file_put_contents("/export/eBayListing/log/".$file_name, $data, FILE_APPEND);
    //file_put_contents("C:\\xampp\\htdocs\\eBayBO\\eBayListing\\log\\".$file_name, $data, FILE_APPEND);
}

function ErrorLogFunction($errno, $errstr, $errfile, $errline){
    //echo "<b>Custom error:</b> [$errno] $errstr<br />";
    //echo " Error on line $errline in $errfile<br />";
    //errorLog('errorLog.log', $errno. ' : '.$errstr.' on line '.$errline.' in '.$errfile . "\n");
}

set_error_handler("ErrorLogFunction");

$categoryPathArray = array();
$nest = 0;

/*
     英,美,法,澳,
	Europe/London       +0100  +7h
	America/New_York    -0400  +12h
	Europe/Paris        +0200  +6h
	Australia/Canberra  +1000  -3h
	
	Asia/Shanghai       +0800
*/
 
class eBayListing{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaylisting';
    //const GATEWAY_SOAP = 'https://api.sandbox.ebay.com/wsapi';
    //const GATEWAY_SOAP = 'https://api.ebay.com/wsapi';
    
    const EBAY_BO_SERVICE = 'http://127.0.0.1/eBayBO/service.phpss';
    const INVENTORY_SERVICE = 'http://127.0.0.1/einv2/service.php';
    //const UPLOAD_TEMP_DIR = 'C:\\xampp\\htdocs\\eBayBO\\eBayListing\\log\\';
    const UPLOAD_TEMP_DIR = '/export/eBayListing/tmp/';
    
    private $startTime;
    private $endTime;
    
    private $env = "production";
    //private $env = "sandbox";
    
    private $session;
    private $site_id; //US 0, UK 3, AU 15, FR 71
    private $account_id;
    
    public function __construct($site_id = 0){
	if(!empty($_COOKIE['account_id'])){
	    $this->account_id = $_COOKIE['account_id'];
	}
	
	$this->site_id = $site_id;
	
        eBayListing::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

        if (!eBayListing::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(eBayListing::$database_connect);
            exit;
        }
	
        mysql_query("SET NAMES 'UTF8'", eBayListing::$database_connect);
	
        if (!mysql_select_db(self::DATABASE_NAME, eBayListing::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(eBayListing::$database_connect);
            exit;
        }
	
	if(isset($_SERVER['HTTP_HOST'])){
	    //if(strpos($_SERVER['HTTP_HOST'], "shuai64") == false){
		//exit;
	    //}
	}
    }
    
    public  function setAccount($account_id){
	$this->account_id = $account_id;
    }
    
    public function setSite($site_id){
	$this->site_id = $site_id;
    }
    
    public function configEbay($site_id = 0){
	$this->site_id = $site_id;
	
	if(!empty($_COOKIE['account_id'])){
	    $this->account_id = $_COOKIE['account_id'];
	}
	
    	if(!empty($this->account_id)){
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
	file_put_contents("/export/eBayListing/log/".$file_name, $data);
	//file_put_contents("C:\\xampp\\htdocs\\eBayBO\\eBayListing\\log\\".$file_name, $data);
    }
    //------------------  eBay Category --------------------------------------------------------------------
    private function checkCategoriesVersion($siteId, $categoryVersion){
        try {
                $client = new eBaySOAP($this->session);

                $CategorySiteID = $siteId;
                $Version = '607';
                $params = array('Version' => $Version, 'CategorySiteID' => $CategorySiteID);
                $results = $client->GetCategories($params);
                //----------   debug --------------------------------
                //print "Request: \n".$client->__getLastRequest() ."\n";
                //print "Response: \n".$client->__getLastResponse()."\n";
		$this->saveFetchData("checkCategoriesVersion-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		
		if($categoryVersion < $results->CategoryVersion){
		    $sql = "update site set categoryVersion = '".$results->CategoryVersion."' where id = '".$siteId."'";
		    echo $sql;
		    $result = mysql_query($sql, eBayListing::$database_connect);
		    return true;
		}else{
		    return false;
		}
		
        } catch (SOAPFault $f) {
                print $f; // error handling
        }
    }
    
    
    private function getCategories($categorySiteID){
        try {
                $client = new eBaySOAP($this->session);

                $CategorySiteID = $categorySiteID;
                $Version = '607';
                $DetailLevel = "ReturnAll";
             
                $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel, 'CategorySiteID' => $CategorySiteID);
                $results = $client->GetCategories($params);
                //----------   debug --------------------------------
                //print "Request: \n".$client->__getLastRequest() ."\n";
                //print "Response: \n".$client->__getLastResponse()."\n";
                $this->saveFetchData("getCategories-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		foreach($results->CategoryArray->Category as $category){
		    $sql = "insert into categories (CategoryID,CategoryLevel,CategoryName,CategoryParentID,LeafCategory,BestOfferEnabled,AutoPayEnabled,CategorySiteID) values 
		    ('".$category->CategoryID."','".$category->CategoryLevel."','".$category->CategoryName."','".$category->CategoryParentID."',
		    '".$category->LeafCategory."','".$category->BestOfferEnabled."','".$category->AutoPayEnabled."','".$CategorySiteID."')";
		    //echo $sql;
		    $result = mysql_query($sql, eBayListing::$database_connect);
		}
                
        } catch (SOAPFault $f) {
                print $f; // error handling
        }
    }
    
    public function getAllCategories(){
	$this->setAccount(1);
	$this->configEbay();
	    
	$sql = "select id,name,categoryVersion from site where status = 1";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while ($row = mysql_fetch_assoc($result)){
	    $service_result = $this->checkCategoriesVersion($row['id'], $row['categoryVersion']);
	    if($service_result){
		$this->getCategories($row['id']);
	    }else{
		echo $row['name']." categories no change<br>";
	    }
	}
    }
    
    public function getStoreCategories($userID){
	global $argv;
	if(!empty($argv[2])){
	    $userID = $argv[2];
	    $sql = "select id from account where name = '".$userID."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	
	    $this->setAccount($row['id']);
	    $this->configEbay();
	}
	
	try {
                $client = new eBaySOAP($this->session);

                $CategoryStructureOnly = true;
                $Version = '607';
		$UserID = $userID;
		
                $params = array('Version' => $Version, 'CategoryStructureOnly' => $CategoryStructureOnly, 'UserID' => $UserID);
                $results = $client->GetStore($params);
		//print_r($results);
                //----------   debug --------------------------------
                //print "Request: \n".$client->__getLastRequest() ."\n";
                //print "Response: \n".$client->__getLastResponse()."\n";
		$sql = "delete from account_store_categories where AccountId = ".$account_id;
		$result = mysql_query($sql, eBayListing::$database_connect);
		
                $this->saveFetchData("getStoreCategories-".$userID."-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		foreach($results->Store->CustomCategories->CustomCategory as $customCategory){
		    $level = 1;
		    $sql = "INSERT INTO `account_store_categories` (`CategoryID` , `CategoryParentID` ,`Name` ,`Order` ,`AccountId`) VALUES ('".$customCategory->CategoryID."','0','".$customCategory->Name."','".$customCategory->Order."','".$this->account_id."')";
		    //echo $sql."<br>\n";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		    
		    //two level
		    if(is_array($customCategory->ChildCategory)){
			$twoCategoryParentID = $customCategory->CategoryID;
			$twoChildCategories = $customCategory->ChildCategory;
			
			foreach($twoChildCategories as $twoChildCategory){ 
			    $level = 2;
			    $sql = "INSERT INTO `account_store_categories` (`CategoryID` , `CategoryParentID` ,`Name` ,`Order` ,`AccountId`) VALUES ('".$twoChildCategory->CategoryID."','".$twoCategoryParentID."','".$twoChildCategory->Name."','".$twoChildCategory->Order."','".$this->account_id."')";
			    //echo $sql."<br>\n";
			    $result = mysql_query($sql, eBayListing::$database_connect);
			    
			    //three leve
			    if(is_array($twoChildCategory->ChildCategory)){
				$threeCategoryParentID = $twoChildCategory->CategoryID;
				$threeChildCategories = $twoChildCategory->ChildCategory;
				
				foreach($threeChildCategories as $threeChildCategory){
				    $level = 3;
				    $sql = "INSERT INTO `account_store_categories` (`CategoryID` , `CategoryParentID` ,`Name` ,`Order` ,`AccountId`) VALUES ('".$threeChildCategory->CategoryID."','".$threeCategoryParentID."','".$threeChildCategory->Name."','".$threeChildCategory->Order."','".$this->account_id."')";
				    //echo $sql."<br>\n";
				    $result = mysql_query($sql, eBayListing::$database_connect);
				}
			    }
			}
			
		    }
		}
                
        } catch (SOAPFault $f) {
                print $f; // error handling
        }
    }
    
    public function getAllStoreCategories(){
	$sql = "select * from account where status = 1";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while ($row = mysql_fetch_assoc($result)){
	    $this->setAccount($row['id']);
	    $this->configEbay();
	    $this->getStoreCategories($row['name']);
	}
    }
    
    public function getCategoryFeatures(){
	try {
	    $client = new eBaySOAP($this->session);
	    
	    $DetailLevel = 'ReturnAll';
	    $Version = '607';
	    $FeatureID = 'ListingDurations';
	    
	    $params = array('DetailLevel' => $DetailLevel, 'Version' => $Version, 'FeatureID' => $FeatureID);
	    $results = $client->GetCategoryFeatures($params);
	    //print_r($results);
	    
	    
	    print_r($results->SiteDefaults->ListingDuration);
	    foreach($results->SiteDefaults->ListingDuration as $listingDurationType){
		$sql = "insert into listing_duration_type (id,name) values ('".$listingDurationType->_."','".$listingDurationType->type."')";
		echo $sql;
		echo "<br>";
		$result = mysql_query($sql, eBayListing::$database_connect);
	    }
	    
	    print_r($results->FeatureDefinitions->ListingDurations);
	    foreach($results->FeatureDefinitions->ListingDurations->ListingDuration as $listingDuration){
		foreach($listingDuration->Duration as $duration){
		    $sql = "insert into listing_duration (id,name,version) values ('".$listingDuration->durationSetID."','".$duration."','".$results->FeatureDefinitions->ListingDurations->Version."')";
		    echo $sql;
		    echo "<br>";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		}
	    }
	    
	    //----------   debug --------------------------------
	    //print "Request: \n".$client->__getLastRequest() ."\n";
	    //print "Response: \n".$client->__getLastResponse()."\n";
	    //$this->saveFetchData("getCategoryFeatures-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
	 } catch (SOAPFault $f) {
                print $f; // error handling
        }
    }
    
    //-----------------   GeteBayDetails     ------------------------------------------
    public function getShippingServiceDetails(){
	try {
                $client = new eBaySOAP($this->session);
                $Version = '607';
                $DetailName = "ShippingServiceDetails";
             
                $params = array('Version' => $Version, 'DetailName' => $DetailName);
                $results = $client->GeteBayDetails($params);
                //print_r($results);
		//----------   debug --------------------------------
                $this->saveFetchData("ShippingServiceDetails-Request-".date("Y-m-d H:i:s").".xml", $client->__getLastRequest());
                $this->saveFetchData("ShippingServiceDetails-Response-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		
		if(!empty($results->ShippingServiceDetails)){
		    //clear up
		    $sql = "delete from shipping_service_details where SiteID = '".$this->site_id."'";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		    
		    foreach($results->ShippingServiceDetails as $shippingServiceDetail){
			if(@empty($shippingServiceDetail->InternationalService)){
			    $shippingServiceDetail->InternationalService = false;
			}
			
			if(@empty($shippingServiceDetail->ShippingTimeMax)){
			    $shippingServiceDetail->ShippingTimeMax = false;
			}
			
			if(@empty($shippingServiceDetail->ShippingTimeMin)){
			    $shippingServiceDetail->ShippingTimeMin = false;
			}
			
			$ServiceTypeFlat = false;
			$ServiceTypeCalculated = false;
			if(@is_array($shippingServiceDetail->ServiceType)){
			    foreach($shippingServiceDetail->ServiceType as $serviceType){
				if($serviceType == "Flat"){
				    $ServiceTypeFlat = true;
				}elseif($serviceType == "Calculated"){
				    $ServiceTypeCalculated = true;
				}
			    }
			}else{
			    if(@$shippingServiceDetail->ServiceType == "Flat"){
				$ServiceTypeFlat = true;
			    }elseif(@$shippingServiceDetail->ServiceType == "Calculated"){
				$ServiceTypeCalculated = true;
			    }
			}
			
			$ShippingPackageLetter = false;
			$ShippingPackageLargeEnvelope = false;
			$ShippingPackagePackageThickEnvelope = false;
			if(@is_array($shippingServiceDetail->ShippingPackage)){
			    foreach($shippingServiceDetail->ShippingPackage as $shippingPackage){
				switch($shippingPackage){
				    case "Letter":
					$ShippingPackageLetter = true;
					break;
				    
				    case "LargeEnvelope":
					$ShippingPackageLargeEnvelope = true;
					break;
				    
				    case "PackageThickEnvelope":
					$ShippingPackagePackageThickEnvelope = true;
					break;
				}
			    }
			}else{
			    switch(@$shippingServiceDetail->ShippingPackage){
				case "Letter":
				    $ShippingPackageLetter = true;
				    break;
				
				case "LargeEnvelope":
				    $ShippingPackageLargeEnvelope = true;
				    break;
				
				case "PackageThickEnvelope":
				    $ShippingPackagePackageThickEnvelope = true;
				    break;
			    }
			}
			
			if(@empty($shippingServiceDetail->ShippingCarrier)){
			    $shippingServiceDetail->ShippingCarrier = false;
			}
			
			if(@empty($shippingServiceDetail->DimensionsRequired)){
			    $shippingServiceDetail->DimensionsRequired = false;
			}
			
			if(@empty($shippingServiceDetail->WeightRequired)){
			    $shippingServiceDetail->WeightRequired = false;
			}
			
			echo "<font color='red'>".$shippingServiceDetail->Description."</font>";
			echo "<br>";
			
			$sql = "insert into shipping_service_details (SiteID,Description,InternationalService,ShippingService,
			ShippingServiceID,ShippingTimeMax,ShippingTimeMin,ServiceTypeFlat,ServiceTypeCalculated,
			ShippingPackageLetter,ShippingPackageLargeEnvelope,ShippingPackagePackageThickEnvelope,
			ShippingCarrier,DimensionsRequired,WeightRequired) values ('".$this->site_id."','".mysql_escape_string($shippingServiceDetail->Description)."',
			'".$shippingServiceDetail->InternationalService."','".mysql_escape_string($shippingServiceDetail->ShippingService)."',
			'".$shippingServiceDetail->ShippingServiceID."','".$shippingServiceDetail->ShippingTimeMax."',
			'".$shippingServiceDetail->ShippingTimeMin."','".$ServiceTypeFlat."',
			'".$ServiceTypeCalculated."','".$ShippingPackageLetter."',
			'".$ShippingPackageLargeEnvelope."','".$ShippingPackagePackageThickEnvelope."',
			'".$shippingServiceDetail->ShippingCarrier."','".$shippingServiceDetail->DimensionsRequired."',
			'".$shippingServiceDetail->WeightRequired."')";
			
			echo $sql;
			echo "<br>";
			$result = mysql_query($sql, eBayListing::$database_connect);
		    }
		}
		echo "<h2>Fetch ".$this->site_id." End.</h2>";
		echo "<br>";
		echo "<br>";
		echo "<br>";
		flush();
        } catch (SOAPFault $f) {
                print $f; // error handling
        }
    }
    
    public function getAllSiteShippingServiceDetails(){
	$sql = "select id from site where status = 1";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while ($row = mysql_fetch_assoc($result)){
	    $this->configEbay($row['id']);
	    $this->getShippingServiceDetails();
	}
    }
    
    public function getShippingLocationDetails(){
	try {
	    $client = new eBaySOAP($this->session);
	    $Version = '607';
	    $DetailName = "ShippingLocationDetails";
	 
	    $params = array('Version' => $Version, 'DetailName' => $DetailName);
	    $results = $client->GeteBayDetails($params);
	    print_r($results);
	    
	    foreach($results->ShippingLocationDetails as $shippingLocationDetails){
		$sql = "insert into ship_to_location (SiteID,ShippingLocation,Description) 
		values ('".$this->site_id."','".$shippingLocationDetails->ShippingLocation."','".mysql_escape_string($shippingLocationDetails->Description)."')";
		echo $sql;
		echo "<br>";
		$result = mysql_query($sql, eBayListing::$database_connect);
	    }
	    echo "<h2>Fetch ".$this->site_id." End.</h2>";
	    echo "<br>";
	    echo "<br>";
	    echo "<br>";
	    //----------   debug --------------------------------
	    //print "Request: \n".$client->__getLastRequest() ."\n";
	    //print "Response: \n".$client->__getLastResponse()."\n";
	    //$this->("ShippingLocationDetails-Request-".date("Y-m-d H:i:s").".xml", $client->__getLastRequest());
	    //$this->saveFetchData("ShippingLocationDetails-Response-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
	} catch (SOAPFault $f) {
            print $f; // error handling
        }
    }
    
    public function getAllSiteShippingLocationDetails(){
	$sql = "select id from site where status = 1";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while ($row = mysql_fetch_assoc($result)){
	    $this->configEbay($row['id']);
	    $this->getShippingLocationDetails();
	    //exit();
	}
    }
    
    public function getShippingLocation(){
	$sql = "select ShippingLocation from ship_to_location where SiteID = '".$_GET['SiteID']."'";
	//echo $sql;
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	while ($row = mysql_fetch_assoc($result)){
	    if($row['ShippingLocation'] != 'Worldwide' && $row['ShippingLocation'] != 'None'){
		$array[] = $row['ShippingLocation'];
	    }
	}
	echo json_encode($array);
    }
    
    //http://127.0.0.1:6666/eBayBO/eBaylisting/service.php?action=getAllCategory2CS
    //-----------------  Fetch Item Specifics From eBay ------------------------------------------
    public function getCategory2CS(){
	global $argv;
	if(!empty($argv[2])){
	    $this->configEbay($argv[2]);
	}
	
	$sql = "delete from CharacteristicsSets where SiteID = '".$this->site_id."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
		    
	try {
	    echo $this->site_id;
	    echo "\n";
	    $client = new eBaySOAP($this->session);
	    $Version = '607';
	    $DetailLevel = "ReturnAll";
	 
	    $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel);
	    $results = $client->GetCategory2CS($params);
	    
	    foreach ($results->MappedCategoryArray->Category as $category){
		$sql = "insert into CharacteristicsSets (SiteID,CategoryID,Name,AttributeSetID,AttributeSetVersion) values 
		('".$this->site_id."','".$category->CategoryID."','".$category->CharacteristicsSets->Name."',
		'".$category->CharacteristicsSets->AttributeSetID."','".$category->CharacteristicsSets->AttributeSetVersion."')";
		//echo $sql;
		//echo "\n";
		$result = mysql_query($sql, eBayListing::$database_connect);
	    }
	    
	    echo "\n****************************************************************\n";
	    flush();
	    //exit();
	} catch (SOAPFault $f) {
            print $f; // error handling
        }
    }
    
    public function getAllCategory2CS(){
	$sql = "select id from site where status = 1";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while ($row = mysql_fetch_assoc($result)){
	    $this->configEbay($row['id']);
	    $this->getCategory2CS();
	}
    }
    
    public function getAttributesCS(){
	global $argv;
	if(!empty($argv[2])){
	    $this->configEbay($argv[2]);
	}
	try {
	    $client = new eBaySOAP($this->session);
	    $Version = '607';
	    $DetailLevel = "ReturnAll";
	 
	    $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel);
	    $results = $client->GetAttributesCS($params);
	    
	    file_put_contents("GetAttributesCS-".$this->site_id.".xml", $results->AttributeData);
	    echo "\n******************   getAttributesCS Site ".$this->site_id." **************************\n";
	    flush();
	    //exit();
	} catch (SOAPFault $f) {
            print $f; // error handling
        }
    }
    
    public function getAllAttributesCS(){
	$sql = "select id from site where status = 1";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while ($row = mysql_fetch_assoc($result)){
	    $this->configEbay($row['id']);
	    $this->getAttributesCS();
	}
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
    
    public function getSkuPicture(){
	$sql = "select picture_1,picture_2,picture_3,picture_4,picture_5 from account_sku_picture 
	where account_id = '".$this->account_id."' and sku = '".$_POST['sku']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	echo json_encode($row);
    }
    
    public function saveTempDescription(){
	session_start();
	$_SESSION[$_GET['type']][$_GET['id']]['title'] = $_POST['title'];
	$_SESSION[$_GET['type']][$_GET['id']]['description'] = htmlentities($_POST['description']);
    }
    
    public function getDescriptionById(){
	$sql = "select Title,Description,UseStandardFooter from template where Id = '".$_POST['id']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	if($row['UseStandardFooter']){
	    $sql_1 = "select footer from account_footer where accountId = '".$this->account_id."'";
            $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
            $row_1 = mysql_fetch_assoc($result_1);
	    echo str_replace(array("%title%", "%description%"), array($row['Title'], html_entity_decode($row['Description'])), $row_1['footer']);	
	}else{
	    echo html_entity_decode($row['Description']);
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
    
    public function getTemplateTree(){
	$array = array();
	$i = 0;
	if(empty($_POST['node'])){
	    $parent_id = 0;
	}else{
	    $parent_id = $_POST['node'];
	}
	$sql = "select * from template_category where account_id = '".$this->account_id."' and parent_id = '".$parent_id."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
	    
	    $sql_1 = "select count(*) as count from template_to_template_cateogry where template_category_id = '".$row['id']."'";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $row_1 = mysql_fetch_assoc($result_1);
	    
            $array[$i]['id'] = $row['id'];
	    $array[$i]['text'] = $row['name'] ." (".$row_1['count'].")";
	    $sql_2 = "select count(*) as count from template_category where parent_id = '".$row['id']."'";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    $row_2 = mysql_fetch_assoc($result_2);
	    if($row_2['count'] > 0){
		$array[$i]['leaf'] = false;
	    }else{
		$array[$i]['leaf'] = true;
	    }
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function addTemplateCateogry(){
	$sql = "insert into template_category (name,parent_id,account_id) values ('".$_POST['templateCategoryName']."','".$_POST['templateCateogryParentId']."','".$this->account_id."')";
	$result = mysql_query($sql, eBayListing::$database_connect);
	echo $result;
    }
    
    public function modifyTemplateCateogry(){
	$sql = "update template_category set name = '".$_POST['templateCategoryName']."' where id = '".$_POST['templateCateogryId']."' and account_id = '".$this->account_id."'";
	//echo $sql;
	$result = mysql_query($sql, eBayListing::$database_connect);
	echo $result;
    }
    
    public function deleteTemplateCateogry(){
	$sql = "delete from template_category where id = '".$_POST['templateCateogryId']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	echo $result;
    }
    
    public function getAllTemplate(){
	$array = array();
	
	if(empty($_POST) || $_POST['parent_id'] == '0'){
	    $sql = "select count(*) as count from template where accountId = '".$this->account_id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
            $row = mysql_fetch_assoc($result);
            $totalCount = $row['count'];
	    
	    if(empty($_POST['start']) && empty($_POST['limit'])){
                $_POST['start'] = 0;
                $_POST['limit'] = 20;
            }
	    
	    $sql = "select Id,Site,SKU,Title,BuyItNowPrice,ListingType,StartPrice,Quantity,ListingDuration from template where accountId = '".$this->account_id."' order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
            $result = mysql_query($sql, eBayListing::$database_connect);
            
	}else{
	    $where = " where 1 = 1 ";
	    if(!empty($_POST['parent_id'])){
		$where .= " and tttc.template_category_id = '".$_POST['parent_id']."'";
	    }
	    $where .= "and t.accountId = '".$this->account_id."' ";
		
	    if(empty($_POST['start']) && empty($_POST['limit'])){
                $_POST['start'] = 0;
                $_POST['limit'] = 20;
            }
	    
            if(!empty($_POST['SKU'])){
                $where .= " and t.SKU like '%".$_POST['SKU']."%'";
            }
            
            if(!empty($_POST['Title'])){
                $where .= " and t.Title like '%".$_POST['Title']."%'";
            }
                
            $sql = "select count(*) as count from template as t left join template_to_template_cateogry as tttc on t.Id = tttc.template_id  ".$where;
            //echo $sql;
	    //exit;
	    $result = mysql_query($sql, eBayListing::$database_connect);
            $row = mysql_fetch_assoc($result);
            $totalCount = $row['count'];
            
            $sql = "select Id,Site,SKU,Title,BuyItNowPrice,ListingType,StartPrice,Quantity,ListingDuration from template as t left join template_to_template_cateogry as tttc on t.Id = tttc.template_id ".$where." order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
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
	    $sql_1 = "select ShippingServiceCost from template_international_shipping_service_option where templateId = '".$row['Id']."' order by ShippingServicePriority";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $row_1 = mysql_fetch_assoc($result_1);
	    $row['ShippingFee'] = $row_1['ShippingServiceCost'];
	    
	    $sql_2 = "select tc.name from template_to_template_cateogry as tttc left join template_category as tc on tttc.template_category_id = tc.id where tttc.template_id = '".$row['Id']."' and account_id = '".$this->account_id."'";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    $row_2 = mysql_fetch_assoc($result_2);
	    $row['Category'] = $row_2['name'];
	    
	    $array[] = $row;
	}
	
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$array));
	mysql_free_result($result);
    }
    
    public function getTemplateDurationStore(){
	$sql = "select ListingType from template where Id = '".$_POST['Id']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	
	$sql = "select id from listing_duration_type where name = '".$row['ListingType']."'";
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
    
    public function templateDelete(){
	if(strpos($_POST['ids'], ',')){
	    $ids = explode(',', $_POST['ids']);
	    foreach($ids as $id){
		$sql_1 = "delete from template where Id = '".$id."'";
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		
		$sql_2 = "delete from template_picture_url where templateId = '".$id."'";
		$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		
		$sql_3 = "delete from template_shipping_service_options where templateId = '".$id."'";
		$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
		
		$sql_4 = "delete from template_international_shipping_service_option where templateId = '".$id."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		
		$sql_5 = "select * from template_attribute_set where templateId = '".$id."'";
		$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
		while($row_5 = mysql_fetch_assoc($result_5)){
		    $sql_6 = "delete from template_attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
		    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
		}
    
		$sql_7 = "delete from template_attribute_set where templateId = '".$id."'";
		$result_7 = mysql_query($sql_7, eBayListing::$database_connect);
		
		$sql_8 = "delete from template_to_template_cateogry where template_id = '".$id."'";
		$result_8 = mysql_query($sql_8, eBayListing::$database_connect);
	    }
	}else{
	    $id = $_POST['ids'];
	    $sql_1 = "delete from template where Id = '".$id."'";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    
	    $sql_2 = "delete from template_picture_url where templateId = '".$id."'";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    
	    $sql_3 = "delete from template_shipping_service_options where templateId = '".$id."'";
	    $result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	    
	    $sql_4 = "delete from template_international_shipping_service_option where templateId = '".$id."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	    
	    $sql_5 = "select * from template_attribute_set where templateId = '".$id."'";
	    $result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	    while($row_5 = mysql_fetch_assoc($result_5)){
		$sql_6 = "delete from template_attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
		$result_6 = mysql_query($sql_6, eBayListing::$database_connect);
	    }

	    $sql_7 = "delete from template_attribute_set where templateId = '".$id."'";
	    $result_7 = mysql_query($sql_7, eBayListing::$database_connect);
	    
	    $sql_8 = "delete from template_to_template_cateogry where template_id = '".$id."'";
	    $result_8 = mysql_query($sql_8, eBayListing::$database_connect);
	}
	//print_r(array($result_1, $result_2, $result_3, $result_4, $result_5, $result_7));
	
	if($result_1 && $result_2 && $result_3 && $result_4 && $result_5 && $result_7 && $result_8){
	    echo 1;   
	}else{
	    echo 0;
	}
    }

    public function templateImportCsv(){
	//echo '{success:true, test:"'.print_r($_FILES, true).'"}';
	//exit;
	switch($_GET['type']){
	    case "spcsv":
		$handle = fopen($_FILES['spcsv']['tmp_name'], "r");
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		    //print_r($data);
		    $sql = "update template set StartPrice='".$data[1]."' where SKU = '".$data[0]."'";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		}
		fclose($handle);
	    break;
	
	    case "sqcsv":
		$handle = fopen($_FILES['sqcsv']['tmp_name'], "r");
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		    //print_r($data);
		    $sql = "update template set Quantity='".$data[1]."' where SKU = '".$data[0]."'";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		}
		fclose($handle);
	    break;
	    
	    case "stpcsv":
		$handle = fopen($_FILES['stpcsv']['tmp_name'], "r");
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		    //print_r($data);
		    $sql = "update template set StartPrice='".$data[2]."' where SKU = '".$data[0]."' and Title = '".mysql_real_escape_string($data[1])."'";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		}
		fclose($handle);
	    break;
	    
	    case "stcsv":
	    	$handle = fopen($_FILES['stcsv']['tmp_name'], "r");
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			    //print_r($data);
			    $sql = "select * from template where SKU = '".$data[0]."' and Title = '".mysql_real_escape_string($data[1])."'";
			    //echo $sql;
			    $result = mysql_query($sql, eBayListing::$database_connect);
			    while($row = mysql_fetch_assoc($result)){
				$this->tempalteChangeToItem($row['Id']);
			    }
			}
			fclose($handle);
	    break;
	}
	if($result){
	    echo "{success:true}";
	}else{
	    echo "{success:false}";
	}
    }
    
    //-----------------------  Template change to item ------------------------------------
    private function tempalteChangeToItem($template_id, $time = '', $local_time = '', $status = 0){
	$sql_1 = "insert into items (AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
	Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
	PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,
	ReservePrice,CurrentPrice,ScheduleTime,ScheduleLocalTime,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
	StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
	Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
	GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,
	InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,Status,UseStandardFooter) select AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
	Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
	PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,
	ReservePrice,CurrentPrice,'".$time."','".$local_time."',SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
	StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
	Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
	GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,
	InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,'".$status."',UseStandardFooter from template where Id = '".$template_id."'";
	
	//echo $sql_1."\n";
	
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$item_id = mysql_insert_id(eBayListing::$database_connect);
	
	//var_dump($item_id);
	//exit;
	$sql_2 = "insert into picture_url (ItemID,url)  select '".$item_id."',url from template_picture_url where templateId = '".$template_id."'";
	$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	
	$sql_3 = "insert into shipping_service_options (ItemID,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority) select '".$item_id."',FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority from template_shipping_service_options where templateId = '".$template_id."'";
	$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	
	$sql_4 = "insert into international_shipping_service_option (ItemID,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) select '".$item_id."',ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation from template_international_shipping_service_option where templateId = '".$template_id."'";
	$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	
	$sql_5 = "select * from template_attribute_set where templateId = '".$template_id."'";
	$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	while($row_5 = mysql_fetch_assoc($result_5)){
	    $template_attribute_set_id = $row_5['attribute_set_id'];
	    $sql_6 = "insert into attribute_set (item_id,attributeSetID) values ('".$item_id."','".$row_5['attributeSetID']."')";
	    //echo $sql_6."\n";
	    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
	    $attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
	    
	    $sql_7 = "insert into attribute (attributeID,attribute_set_id,ValueID,ValueLiteral) 
	    select attributeID,'".$attribute_set_id."',ValueID,ValueLiteral from template_attribute 
	    where attribute_set_id = '".$template_attribute_set_id."'";
	    $result_7 = mysql_query($sql_7, eBayListing::$database_connect);
	}
	//var_dump(array($result_1, $result_2, $result_3, $result_4, $result_6, $result_7));

	if($result_1){
	    return $item_id;
	}else{
	    return false;
	}
    }
    
    public function templateScheduleUpload(){
	if(strpos($_POST['ids'], ',')){
	    $ids = explode(',', $_POST['ids']);
	    $item_id = "";
	    foreach($ids as $id){
		$sql_10 = "select * from schedule where template_id = '$id'";
		$result_10 = mysql_query($sql_10, eBayListing::$database_connect);
		$num_rows = mysql_num_rows($result_10);
		if($num_rows == 0){
		    echo "[{success: false, msg: 'template ".$_POST['ids']." no set schedule date.'}]";;
		    return 1;
		}
		
		while($row_10 = mysql_fetch_assoc($result_10)){
		    $startTimestamp = strtotime($row_10['startDate']);
		    $endTimestamp = strtotime($row_10['endDate']);
		    while($startTimestamp <= $endTimestamp){
			if(date("D", $startTimestamp) == $row_10['day']){
			    if(date("D", $startTimestamp + 24 * 60 * 60) == $row_10['china_day']){
				$localTimestamp = $startTimestamp + 24 * 60 * 60;
			    }elseif(date("D", $startTimestamp - 24 * 60 * 60) == $row_10['china_day']){
				$localTimestamp = $tempTimestamp - 24 * 60 * 60;
			    }elseif(date("D", $startTimestamp) == $row_10['china_day']){
				$localTimestamp = $startTimestamp;
			    }
			    $item_id .= $this->tempalteChangeToItem($id, date("Y-m-d", $startTimestamp) . ' ' .$row_10['time'], date("Y-m-d", $localTimestamp) . ' ' .$row_10['china_time']) . ", ";
			}
			$startTimestamp += 24 * 60 * 60;
		    }
		}
	    }
	}else{
	    $sql_10 = "select * from schedule where template_id = '".$_POST['ids']."'";
	    $result_10 = mysql_query($sql_10, eBayListing::$database_connect);
	    $num_rows = mysql_num_rows($result_10);
	    if($num_rows == 0){
		echo "[{success: false, msg: 'template ".$_POST['ids']." no set schedule date.'}]";;
		return 1;
	    }
	    $item_id = "";
	    while($row_10 = mysql_fetch_assoc($result_10)){
		$startTimestamp = strtotime($row_10['startDate']);
		$endTimestamp = strtotime($row_10['endDate']);
		while($startTimestamp <= $endTimestamp){
		    if(date("D", $startTimestamp) == $row_10['day']){
			if(date("D", $startTimestamp + 24 * 60 * 60) == $row_10['china_day']){
			    $localTimestamp = $startTimestamp + 24 * 60 * 60;
			}elseif(date("D", $startTimestamp - 24 * 60 * 60) == $row_10['china_day']){
			    $localTimestamp = $tempTimestamp - 24 * 60 * 60;
			}elseif(date("D", $startTimestamp) == $row_10['china_day']){
			    $localTimestamp = $startTimestamp;
			}
			
			//echo $startTimestamp."\n";
			//echo $localTimestamp."\n";
			
			//echo $localTimestamp - $startTimestamp."\n";
			//echo date("Y-m-d", $startTimestamp)."\n";
			//echo date("Y-m-d", $localTimestamp)."\n";
			//print_r(array($_POST['ids'], date("Y-m-d", $startTimestamp) . ' ' .$row_10['time'], date("Y-m-d", $localTimestamp) . ' ' .$row_10['china_time']));
			//echo date("Y-m-d", $startTimestamp) . ' ' .$row_10['time']."\n";
			$item_id .= $this->tempalteChangeToItem($_POST['ids'], date("Y-m-d", $startTimestamp) . ' ' .$row_10['time'], date("Y-m-d", $localTimestamp) . ' ' .$row_10['china_time']) . ", ";
			//var_dump($result);
		    }
		    $startTimestamp += 24 * 60 * 60;
		}
	    }
	}
	$item_id = substr($item_id, 0, -2);
	if(!empty($item_id)){
	    echo "[{success: true, msg: 'Schedule item id is ".$item_id.".'}]";;
	}
    }
    
    public function templateAddToUpload(){
	$temp = "";
	$item_id = "";
	$ids = explode(',', $_POST['ids']);
	
	if(count($ids) > 1){
	    foreach($ids as $id){
		$sql = "select Site from template where Id = '".$id."'";
		$result = mysql_query($sql, eBayListing::$database_connect);
		$row = mysql_fetch_assoc($result);
		$item_id  .= $this->tempalteChangeToItem($id, "", "") . ", ";
	    }
	}else{
	    $sql = "select Site from template where Id = '".$_POST['ids']."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    $item_id  = $this->tempalteChangeToItem($_POST['ids'], "", "") . ", ";
	}
	
	$item_id = substr($item_id, 0, -2);
	if(!empty($item_id)){
	    echo '[{success: true, msg: "Upload item id is '.$item_id.'"}]';
	}else{
	    echo '[{success: false, msg: "Template Add To Upload Failure, Please Notice Admin."}]';
	}
    }
    
    public function templateImmediatelyUpload(){
	$now = date("Y-m-d H:i:s");
	$temp = "";
	$item_id = "";
	$ids = explode(',', $_POST['ids']);
	
	if(count($ids) > 1){
	    foreach($ids as $id){
		$sql = "select Site from template where Id = '".$id."'";
		$result = mysql_query($sql, eBayListing::$database_connect);
		$row = mysql_fetch_assoc($result);
		    
		switch($row['Site']){
		    case "US":
			$localTime = date("Y-m-d H:i:s", strtotime("-12 hour ".$now));
		    break;
		
		    case "UK":
			$localTime = date("Y-m-d H:i:s", strtotime("-7 hour ".$now));
		    break;
		
		    case "Australia":
			$localTime = date("Y-m-d H:i:s", strtotime("+2 hour ".$now));
		    break;
		
		    case "France":
			$localTime = date("Y-m-d H:i:s", strtotime("-6 hour ".$now));
		    break;
		}
	    
		//$temp .= $id. " : ". $now . "<br>";	
		$item_id  .= $this->tempalteChangeToItem($id, $now, $localTime, 1) . ", ";
	    }
	}else{
	    $sql = "select Site from template where Id = '".$_POST['ids']."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    //var_dump($row);
	    switch($row['Site']){
		case "US":
		    $localTime = date("Y-m-d H:i:s", strtotime("-12 hour ".$now));
		break;
	    
		case "UK":
		    $localTime = date("Y-m-d H:i:s", strtotime("-7 hour ".$now));
		break;
	    
		case "Australia":
		    $localTime = date("Y-m-d H:i:s", strtotime("+2 hour ".$now));
		break;
	    
		case "France":
		    $localTime = date("Y-m-d H:i:s", strtotime("-6 hour ".$now));
		break;
	    }
		
	    //$temp .= $_POST['ids']. " : ". $now . "<br>";
	    $item_id  = $this->tempalteChangeToItem($_POST['ids'], $now, $localTime, 1). ", ";
	}
	
	$item_id = substr($item_id, 0, -2);
	if(!empty($item_id)){
	    echo '[{success: true, msg: "Immediately upload item id is '.$item_id.'"}]';
	}else{
	    echo '[{success: false, msg: "Template Immediately Upload Failure, Please Notice Admin."}]';
	}
    }
    
    public function templateIntervalUpload(){
	/*
	ALTER TABLE `schedule` DROP PRIMARY KEY;
	ALTER TABLE `schedule` ADD INDEX ( `item_id` );
	ALTER TABLE `items` ADD `ScheduleLocalTime` DATETIME NOT NULL AFTER `ScheduleTime` ; 
	*/
	//echo date("Y-m-d H:i:s", strtotime("12:00:00") + 60);
	$now = date("Y-m-d H:i:s");
	$temp = "";
	$item_id = "";
	$_POST['date'] = substr($_POST['date'], 0, -18);
	//echo $_POST['date'];
	//exit;
	$ids = explode(',', $_POST['ids']);
	if(count($ids) > 1){
	    $i = 0;
	    foreach($ids as $id){
		$sql = "select Site from template where Id = '".$id."'";
		$result = mysql_query($sql, eBayListing::$database_connect);
		$row = mysql_fetch_assoc($result);
		
		$localTime = date("Y-m-d H:i:s", strtotime($_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		switch($row['Site']){
		    case "US":
			$time = date("Y-m-d H:i:s", strtotime("+12 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "UK":
			$time = date("Y-m-d H:i:s", strtotime("+7 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "Australia":
			$time = date("Y-m-d H:i:s", strtotime("-3 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "France":
			$time = date("Y-m-d H:i:s", strtotime("+6 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		}
		
		if($time < $now){
		    echo '[{success: false, msg: "Time error: '.$time.'"}]';
		    return 0;
		}
		//$temp .= $id. " : ". $time . "<br>";
		$item_id  .= $this->tempalteChangeToItem($id, $time, $localTime) . ", ";
		$i++;
	    }
	    //$temp = substr($temp, 0, -2);
	}else{
	    $sql = "select Site from template where Id = '".$_POST['ids']."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    
	    $localTime = date("Y-m-d H:i:s", strtotime($_POST['date'].' '.$_POST['time']));
	    switch($row['Site']){
		case "US":
		    $time = date("Y-m-d H:i:s", strtotime("+12 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "UK":
		    $time = date("Y-m-d H:i:s", strtotime("+7 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "Australia":
		    $time = date("Y-m-d H:i:s", strtotime("-3 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "France":
		    $time = date("Y-m-d H:i:s", strtotime("+6 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    }
	    
	    if($time < $now){
		echo '[{success: false, msg: "Time error: '.$time.'"}]';
		return 0;
	    }
	    //$temp .= $_POST['ids'] . " : " . $time;
	    $item_id  = $this->tempalteChangeToItem($_POST['ids'], $time, $localTime) . ", ";
	}
	
	$item_id = substr($item_id, 0, -2);
	if(!empty($item_id)){
	    echo '[{success: true, msg: "Interval upload item id is '.$item_id.'"}]';
	}else{
	    echo '[{success: false, msg: "Template Interval Upload Failure, Please Notice Admin."}]';
	}
    }
    
    public function getTemplateCategory(){
        $sql = "select id,name from template_category where account_id = '".$this->account_id."'";
        $result = mysql_query($sql, eBayListing::$database_connect);
        $array = array();
        while($row = mysql_fetch_assoc($result)){
            $array[] = $row;
        }
        
        echo json_encode($array);
    }
    
    public function addToTemplate(){
	/*
	CREATE TABLE IF NOT EXISTS `ship_to_location` (
	  `SiteID` int(11) NOT NULL,
	  `ShippingLocation` varchar(25) NOT NULL,
	  `Description` varchar(50) NOT NULL,
	  KEY `SiteID` (`SiteID`)
	);
	
	ALTER TABLE `template` ADD `PrimaryCategoryCategoryName` VARCHAR( 200 ) NOT NULL AFTER `PrimaryCategoryCategoryID` ;
	ALTER TABLE `template` ADD `SecondaryCategoryCategoryName` VARCHAR( 200 ) NOT NULL AFTER `SecondaryCategoryCategoryID` ;
	ALTER TABLE `template` ADD `StoreCategoryName` VARCHAR( 200 ) NOT NULL AFTER `StoreCategoryID` ;
	ALTER TABLE `template` ADD `StoreCategory2Name` VARCHAR( 200 ) NOT NULL AFTER `StoreCategory2ID` ;
	
	ALTER TABLE `items` ADD `PrimaryCategoryCategoryName` VARCHAR( 200 ) NOT NULL AFTER `PrimaryCategoryCategoryID` ;
	ALTER TABLE `items` ADD `SecondaryCategoryCategoryName` VARCHAR( 200 ) NOT NULL AFTER `SecondaryCategoryCategoryID` ;
	ALTER TABLE `items` ADD `StoreCategoryName` VARCHAR( 200 ) NOT NULL AFTER `StoreCategoryID` ;
	ALTER TABLE `items` ADD `StoreCategory2Name` VARCHAR( 200 ) NOT NULL AFTER `StoreCategory2ID` ;
	
	ALTER TABLE `template` ADD `ShippingServiceOptionsType` ENUM( "Flat", "Calculated" ) NOT NULL AFTER `PhotoDisplay` ,
	ADD `InternationalShippingServiceOptionType` ENUM( "Flat", "Calculated" ) NOT NULL AFTER `ShippingServiceOptionsType` ;
	
	ALTER TABLE `items` ADD `ShippingServiceOptionsType` ENUM( "Flat", "Calculated" ) NOT NULL AFTER `PhotoDisplay` ,
	ADD `InternationalShippingServiceOptionType` ENUM( "Flat", "Calculated" ) NOT NULL AFTER `ShippingServiceOptionsType` ;
	1> 分类属性
	2> 生成导入sp的文件
	3> 模板
	4> 查询后显示的图片可自己输入  *
	5> 隐藏拍卖用户ID
	5> 运费政策     *
	6> 输入保险费用
	*/
	//ScheduleStartDate,ScheduleEndDate
	//ShippingType
	//ShipToLocations
	//print_r($_POST);
	//exit;
	/*
	if(!empty($_POST['UseStandardFooter']) && $_POST['UseStandardFooter'] == 1){
	    $sql = "select footer from account_footer where accountId = '".$this->account_id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    $_POST['Description'] .= $row['footer'];
	}
	*/
	session_start();
	
	if($_POST['ListingType'] == "FixedPriceItem" || $_POST['ListingType'] == "StoresFixedPrice"){
	    $_POST['StartPrice'] = $_POST['BuyItNowPrice'];
	    $_POST['BuyItNowPrice'] = 0;
	}
	
	if($_POST['ListingType'] == "Chinese"){
	    $_POST['Quantity'] = 1;   
	}
	
	if(!empty($_SESSION['ReturnPolicyReturns'][$_POST['SKU']]['ReturnPolicyReturnsAcceptedOption'])){
	    //StartTime,EndTime
	    //$PaymentMethods = ($_POST['PayPalPayment'] == 1)?'PayPal':'';
	    $sql = "insert into template (BuyItNowPrice,Country,Currency,Description,DispatchTimeMax,
	    ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,PostalCode,
	    PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,Quantity,
	    ReturnPolicyDescription,ReturnPolicyRefundOption,ReturnPolicyReturnsAcceptedOption,ReturnPolicyReturnsWithinOption,ReturnPolicyShippingCostPaidByOption,
	    ReservePrice,Site,SKU,StartPrice,StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,
	    BoldTitle,Border,Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypePlus,GalleryURL,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,accountId,UseStandardFooter) values (
	    '".$_POST['BuyItNowPrice']."','CN','".$_POST['Currency']."',
	    '".htmlentities($_POST['Description'])."','".$_POST['DispatchTimeMax']."',
	    '".$_POST['ListingDuration']."','".$_POST['ListingType']."','".$_POST['Location']."','PayPal',
	    '".$_POST['PayPalEmailAddress']."','".$_POST['PostalCode']."',
	    '".$_POST['PrimaryCategoryCategoryID']."','".$_POST['PrimaryCategoryCategoryName']."','".$_POST['SecondaryCategoryCategoryID']."','".$_POST['SecondaryCategoryCategoryName']."',
	    '".@$_POST['Quantity']."',
	    '".$_SESSION['ReturnPolicyReturns'][$_POST['SKU']]['ReturnPolicyDescription']."','".$_SESSION['ReturnPolicyReturns'][$_POST['SKU']]['ReturnPolicyRefundOption']."',
	    '".$_SESSION['ReturnPolicyReturns'][$_POST['SKU']]['ReturnPolicyReturnsAcceptedOption']."','".$_SESSION['ReturnPolicyReturns'][$_POST['SKU']]['ReturnPolicyReturnsWithinOption']."',
	    '".$_SESSION['ReturnPolicyReturns'][$_POST['SKU']]['ReturnPolicyShippingCostPaidByOption']."','".@$_POST['ReservePrice']."',
	    '".$_POST['Site']."','".$_POST['SKU']."','".$_POST['StartPrice']."','".$_POST['StoreCategory2ID']."','".$_POST['StoreCategory2Name']."',
	    '".$_POST['StoreCategoryID']."','".$_POST['StoreCategoryName']."','".$_POST['SubTitle']."',
	    '".mysql_real_escape_string($_POST['Title'])."','".(empty($_POST['BoldTitle'])?0:1)."',
	    '".(empty($_POST['Border'])?0:1)."','".(empty($_POST['Featured'])?0:1)."','".(empty($_POST['Highlight'])?0:1)."',
	    '".(empty($_POST['HomePageFeatured'])?0:1)."','".(empty($_POST['GalleryTypeFeatured'])?0:1)."','".(empty($_POST['GalleryTypePlus'])?0:1)."','".$_POST['GalleryURL']."',
	    '".$_POST['ShippingServiceOptionsType']."','".$_POST['InsuranceOption']."','".$_POST['InsuranceFee']."',
	    '".$_POST['InternationalShippingServiceOptionType']."','".$_POST['InternationalInsurance']."','".$_POST['InternationalInsuranceFee']."',
	    '".$this->account_id."','".(empty($_POST['UseStandardFooter'])?0:1)."')";
	}else{
	
	    //StartTime,EndTime
	    //$PaymentMethods = ($_POST['PayPalPayment'] == 1)?'PayPal':'';
	    $sql = "insert into template (BuyItNowPrice,Country,Currency,Description,DispatchTimeMax,
	    ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,PostalCode,
	    PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,Quantity,ReservePrice,
	    ShippingType,Site,SKU,StartPrice,StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,
	    BoldTitle,Border,Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypePlus,GalleryURL,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,accountId,UseStandardFooter) values (
	    '".$_POST['BuyItNowPrice']."','CN','".$_POST['Currency']."',
	    '".htmlentities($_POST['Description'])."','".$_POST['DispatchTimeMax']."',
	    '".$_POST['ListingDuration']."','".$_POST['ListingType']."','".$_POST['Location']."','PayPal',
	    '".$_POST['PayPalEmailAddress']."','".$_POST['PostalCode']."',
	    '".$_POST['PrimaryCategoryCategoryID']."','".$_POST['PrimaryCategoryCategoryName']."','".$_POST['SecondaryCategoryCategoryID']."','".$_POST['SecondaryCategoryCategoryName']."',
	    '".@$_POST['Quantity']."','".@$_POST['ReservePrice']."','".@$_POST['ShippingType']."',
	    '".$_POST['Site']."','".$_POST['SKU']."','".$_POST['StartPrice']."','".$_POST['StoreCategory2ID']."','".$_POST['StoreCategory2Name']."',
	    '".$_POST['StoreCategoryID']."','".$_POST['StoreCategoryName']."','".$_POST['SubTitle']."',
	    '".mysql_real_escape_string($_POST['Title'])."','".(empty($_POST['BoldTitle'])?0:1)."',
	    '".(empty($_POST['Border'])?0:1)."','".(empty($_POST['Featured'])?0:1)."','".(empty($_POST['Highlight'])?0:1)."',
	    '".(empty($_POST['HomePageFeatured'])?0:1)."','".(empty($_POST['GalleryTypeFeatured'])?0:1)."','".(empty($_POST['GalleryTypePlus'])?0:1)."','".$_POST['GalleryURL']."',
	    '".$_POST['ShippingServiceOptionsType']."','".$_POST['InsuranceOption']."','".$_POST['InsuranceFee']."',
	    '".$_POST['InternationalShippingServiceOptionType']."','".$_POST['InternationalInsurance']."','".$_POST['InternationalInsuranceFee']."',
	    '".$this->account_id."','".(empty($_POST['UseStandardFooter'])?0:1)."')";
	    
	}
	$result = mysql_query($sql, eBayListing::$database_connect);
	//echo $sql;
	//exit;
	
	$id = mysql_insert_id(eBayListing::$database_connect);
	
	$i = 1;
	while(!empty($_POST['picture_'.$i])){
	    $sql_1 = "insert into template_picture_url (templateId,url) values 
	    ('".$id."','".$_POST['picture_'.$i]."')";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $i++;
	}
	
	$i = 1;
	while(!empty($_POST['ShippingService-'.$i])){
	    $sql_1 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServicePriority) values
	    ('".$id."','".@$_POST['FreeShipping-'.$i]."','".$_POST['ShippingService-'.$i]."','".$_POST['ShippingServiceCost-'.$i]."','".$i."')";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $i++;
	}
	
	$i = 1;
	while(!empty($_POST['InternationalShippingService-'.$i])){
	    $ShipToLocation = '';
	    if($_POST['InternationalShippingToLocations-'.$i] == 'Custom Locations'){
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
		
		if(!empty($_POST['Canada_'.$i]) && $_POST['Canada_'.$i] == 1){
		    $ShipToLocation .= ',CA';
		}
		
		if(!empty($_POST['UK_'.$i]) && $_POST['UK_'.$i] == 1){
		    $ShipToLocation .= ',GB';
		}
		
		if(!empty($_POST['AU_'.$i]) && $_POST['AU_'.$i] == 1){
		    $ShipToLocation .= ',AU';
		}
		
		if(!empty($_POST['Mexico_'.$i]) && $_POST['Mexico_'.$i] == 1){
		    $ShipToLocation .= ',MX';
		}
		
		if(!empty($_POST['Germany_'.$i]) && $_POST['Germany_'.$i] == 1){
		    $ShipToLocation .= ',DE';
		}
		
		if(!empty($_POST['Japan_'.$i]) && $_POST['Japan_'.$i] == 1){
		    $ShipToLocation .= ',JP';
		}
		
		$ShipToLocation = substr($ShipToLocation, 1);
	    }else{
		$ShipToLocation = 'Worldwide';
	    }
	    $sql_2 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServicePriority,ShipToLocation) values
	    ('".$id."','".$_POST['InternationalShippingService-'.$i]."','".$_POST['InternationalShippingServiceCost-'.$i]."','".$i."','".$ShipToLocation."')";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    $i++;
	}
	
	/*
	Array
	(
	    [LB00009-Mon-am-1] => Array
		(
		    [0] => 1:00 AM
		    [1] => 1:01 AM
		    [2] => 1:02 AM
		)
	)
	*/
	
	if(!empty($_SESSION['Schedule'])){
	    switch($_POST['Site']){
		case "US":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("+12 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("+12 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				//echo $sql_3;
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    
		case "UK":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("+7 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("+7 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    
		case "Australia":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("-3 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("-3 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    
		case "France":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("+6 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("+6 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    }
	}
	
	$temp_array = array();
	if(!empty($_SESSION['AttributeSet'][$_POST['SKU']])){
	    //print_r($_SESSION['AttributeSet']);
	    //exit;
	    
	    foreach($_SESSION['AttributeSet'][$_POST['SKU']] as $attributeSetID=>$Attribute){
		$sql_4 = "insert into template_attribute_set (templateId,attributeSetID) values ('".$id."', '".$attributeSetID."')";
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
				$sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) values 
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
			$sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) values 
			('".$key."', '".$attribute_set_id."', '".$ValueID."')";
			$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		    }
		}
	    }
	}
	
	$sql_5 = "insert into template_to_template_cateogry (template_id,template_category_id) values ('".$id."','".$_POST['template_category_id']."')";
	$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	
	if($result){
	    //unset($_SESSION['Schedule']);
	    //unset($_SESSION['AttributeSet']);
	    echo '{success: true, msg: "SKU '.$_POST['SKU'].' Add To Template Success, Template Id '.$id.'!"}';
	    $this->log("template", $_POST['SKU'] . " add to template.");
	}else{
	    echo '{success: false,
		    msg: "Can\'t add, please notice admin."}
		}';
	    $this->log("template", $_POST['SKU'] . " add to template failure.", "error");
	}
    }
    
    public function copyTemplate(){
	if(strpos($_POST['ids'], ',')){
	    $array = explode(',', $_POST['ids']);
	    foreach($array as $a){
		$sql_1 = "insert into template (AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
		Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
		PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,
		ReservePrice,CurrentPrice,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
		StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
		Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
		GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InternationalShippingServiceOptionType,UseStandardFooter) select AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
		Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
		PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,
		ReservePrice,CurrentPrice,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
		StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
		Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
		GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InternationalShippingServiceOptionType,UseStandardFooter from template where Id = '".$a."'";
		
		//echo $sql_1."\n";
		
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		$template_id = mysql_insert_id(eBayListing::$database_connect);
		
		//var_dump($item_id);
		//exit;
		$sql_2 = "insert into template_picture_url (templateId,url)  select '".$template_id."',url from template_picture_url where templateId = '".$a."'";
		$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		
		$sql_3 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority) select '".$template_id."',FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority from template_shipping_service_options where templateId = '".$a."'";
		$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
		
		$sql_4 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) select '".$template_id."',ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation from template_international_shipping_service_option where templateId = '".$a."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		
		$sql_5 = "select * from template_attribute_set where templateId = '".$a."'";
		$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
		while($row_5 = mysql_fetch_assoc($result_5)){
		    $template_attribute_set_id = $row_5['attribute_set_id'];
		    $sql_6 = "insert into template_attribute_set (templateId,attributeSetID) values ('".$template_id."','".$row_5['attributeSetID']."')";
		    //echo $sql_6."\n";
		    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
		    $attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
		    
		    $sql_7 = "insert into template_attribute (attributeID,attribute_set_id,ValueID,ValueLiteral) 
		    select attributeID,'".$attribute_set_id."',ValueID,ValueLiteral from template_attribute 
		    where attribute_set_id = '".$template_attribute_set_id."'";
		    $result_7 = mysql_query($sql_7, eBayListing::$database_connect);
		}
		//var_dump(array($result_1, $result_2, $result_3, $result_4, $result_6, $result_7));
		
		$sql_8 = "insert into template_to_template_cateogry (template_id,template_category_id) values select '".$template_id."',template_category_id from template_to_template_cateogry where template_id = '".$a."'";
		$result_8 = mysql_query($sql_8, eBayListing::$database_connect);
	    
		if($result_1 && $result_2 && $result_3 && $result_4 && $result_5){
		    
		}else{
		    echo 0;
		    return 0;
		}
	    }
	    echo 1;
	}else{
	    $sql_1 = "insert into template (AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
	    Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
	    PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,
	    ReservePrice,CurrentPrice,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
	    StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
	    Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
	    GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InternationalShippingServiceOptionType) select AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
	    Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
	    PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,
	    ReservePrice,CurrentPrice,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
	    StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
	    Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
	    GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InternationalShippingServiceOptionType from template where Id = '".$_POST['ids']."'";
	    
	    //echo $sql_1."\n";
	    
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $template_id = mysql_insert_id(eBayListing::$database_connect);
	    
	    //var_dump($item_id);
	    //exit;
	    $sql_2 = "insert into template_picture_url (templateId,url)  select '".$template_id."',url from template_picture_url where templateId = '".$_POST['ids']."'";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    
	    $sql_3 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost) select '".$template_id."',FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost from template_shipping_service_options where templateId = '".$_POST['ids']."'";
	    $result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	    
	    $sql_4 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShipToLocation) select '".$template_id."',ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShipToLocation from template_international_shipping_service_option where templateId = '".$_POST['ids']."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	    
	    $sql_5 = "select * from template_attribute_set where templateId = '".$_POST['ids']."'";
	    $result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	    while($row_5 = mysql_fetch_assoc($result_5)){
		$template_attribute_set_id = $row_5['attribute_set_id'];
		$sql_6 = "insert into template_attribute_set (templateId,attributeSetID) values ('".$template_id."','".$row_5['attributeSetID']."')";
		//echo $sql_6."\n";
		$result_6 = mysql_query($sql_6, eBayListing::$database_connect);
		$attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
		
		$sql_7 = "insert into template_attribute (attributeID,attribute_set_id,ValueID,ValueLiteral) 
		select attributeID,'".$attribute_set_id."',ValueID,ValueLiteral from template_attribute 
		where attribute_set_id = '".$template_attribute_set_id."'";
		$result_7 = mysql_query($sql_7, eBayListing::$database_connect);
	    }
	    //var_dump(array($result_1, $result_2, $result_3, $result_4, $result_6, $result_7));
	    
	    $sql_8 = "insert into template_to_template_cateogry (template_id,template_category_id) select '".$template_id."',template_category_id from template_to_template_cateogry where template_id = '".$_POST['ids']."'";
	    //echo $sql_8;
	    $result_8 = mysql_query($sql_8, eBayListing::$database_connect);
	    
	    if($result_1 && $result_2 && $result_3 && $result_4 && $result_5 && $result_8){
		echo 1;
	    }else{
		echo 0;
	    }
	}
	return 1;
    }
    
    public function getTemplate(){
	session_start();
	$sql = "select * from template where Id = '".$_GET['id']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$row['SiteID'] = $row['Site'];
	$row['Description'] = html_entity_decode($row['Description']);
	
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
	
	$sql_1 = "select url from template_picture_url where templateId = '".$row['Id']."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$i = 1;
	while($row_1 = mysql_fetch_assoc($result_1)){
	    $row['picture_'.$i] = $row_1['url'];
	    $i++;
	}
	$sql_2 = "select template_category_id from template_to_template_cateogry where template_id = '".$row['Id']."'";
	$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	$row_2 = mysql_fetch_assoc($result_2);
	$row['template_category_id'] = $row_2['template_category_id'];
	
	$sql_3 = "select * from template_shipping_service_options where templateId = '".$row['Id']."' order by ShippingServicePriority";
	$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	$i = 1;
	while($row_3 = mysql_fetch_assoc($result_3)){
	    $row['ShippingService_'.$i] = $row_3['ShippingService'];
	    $row['ShippingServiceCost_'.$i] = $row_3['ShippingServiceCost'];
	    $row['ShippingServiceFree_'.$i] = $row_3['FreeShipping'];
	    $i++;
	}
	
	$sql_4 = "select * from template_international_shipping_service_option where templateId = '".$row['Id']."' order by ShippingServicePriority";
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
	
	$sql_5 = "select * from template_attribute_set where templateId = '".$row['Id']."'";
	$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	$row_5 = mysql_fetch_assoc($result_5);
	
	$sql_6 = "select * from template_attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
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
	
	unset($_SESSION['Schedule']);
	
	$sql_7 = "select * from schedule where template_id = '".$_GET['id']."'";
	$result_7 = mysql_query($sql_7, eBayListing::$database_connect);
	
	$temp = array();
	while($row_7 = mysql_fetch_assoc($result_7)){
	    $row['ScheduleStartDate'] = $row_7['startDate'];
	    $row['ScheduleEndDate'] = $row_7['endDate'];
	    if(array_key_exists($_GET['id'].'-'.$row_7['china_day'].'-'.date('a-g', strtotime($row_7['china_time'])), $_SESSION['Schedule'])){
		$_SESSION['Schedule'][$_GET['id'].'-'.$row_7['china_day'].'-'.date('a-g', strtotime($row_7['china_time']))][count($_SESSION['Schedule'][$_GET['id'].'-'.$row_7['china_day'].'-'.date('a-g', strtotime($row_7['china_time']))])] = date('g:i A', strtotime($row_7['china_time']));
	    }else{
		$_SESSION['Schedule'][$_GET['id'].'-'.$row_7['china_day'].'-'.date('a-g', strtotime($row_7['china_time']))][0] = date('g:i A', strtotime($row_7['china_time']));
	    }
	    $t = date("D-a-g", strtotime($row_7['china_day'] . " " . $row_7['china_time']))."-panel";
	    if(!in_array($t, $temp)){
		$temp[] = $t;
	    }
	}
	$row['Schedule'] = implode(",", $temp);
	
	echo '['.json_encode($row).']';
	mysql_free_result($result);
    }
    
    public function updateTemplate(){
	/*
	if(!empty($_POST['UseStandardFooter']) && $_POST['UseStandardFooter'] == 1){
	    $sql = "select footer from account_footer where accountId = '".$this->account_id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    $_POST['Description'] .= $row['footer'];
	}
	*/
	session_start();
	if($_POST['ListingType'] == "FixedPriceItem" || $_POST['ListingType'] == "StoresFixedPrice"){
	    $_POST['StartPrice'] = $_POST['BuyItNowPrice'];
	    $_POST['BuyItNowPrice'] = 0;
	}
	
	if($_POST['ListingType'] == "Chinese"){
	    $_POST['Quantity'] = 1;   
	}
	
	$id = $_GET['template_id'];
	//StartTime,EndTime
	//$PaymentMethods = ($_POST['PayPalPayment'] == 1)?'PayPal':'';
	if(!empty($_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyReturnsAcceptedOption'])){
	    $sql = "update template set 
	    BuyItNowPrice='".$_POST['BuyItNowPrice']."',Country='CN',Currency='".$_POST['Currency']."',
	    Description='".htmlentities($_POST['Description'])."',DispatchTimeMax='".$_POST['DispatchTimeMax']."',
	    ListingDuration='".$_POST['ListingDuration']."',ListingType='".$_POST['ListingType']."',Location='".$_POST['Location']."',PaymentMethods='PayPal',
	    PayPalEmailAddress='".$_POST['PayPalEmailAddress']."',PostalCode='".$_POST['PostalCode']."',
	    PrimaryCategoryCategoryID='".$_POST['PrimaryCategoryCategoryID']."',PrimaryCategoryCategoryName='".$_POST['PrimaryCategoryCategoryName']."',
	    SecondaryCategoryCategoryID='".$_POST['SecondaryCategoryCategoryID']."',SecondaryCategoryCategoryName='".$_POST['SecondaryCategoryCategoryName']."',
	    Quantity='".@$_POST['Quantity']."',ReservePrice='".@$_POST['ReservePrice']."',
	    ReturnPolicyDescription='".$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyDescription']."',ReturnPolicyRefundOption='".$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyRefundOption']."',
	    ReturnPolicyReturnsAcceptedOption='".$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyReturnsAcceptedOption']."',ReturnPolicyReturnsWithinOption='".$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyReturnsWithinOption']."',
	    ReturnPolicyShippingCostPaidByOption='".$_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyShippingCostPaidByOption']."',
	    Site='".$_POST['Site']."',SKU='".$_POST['SKU']."',StartPrice='".$_POST['StartPrice']."',StoreCategory2ID='".$_POST['StoreCategory2ID']."',StoreCategory2Name='".$_POST['StoreCategory2Name']."',
	    StoreCategoryID='".$_POST['StoreCategoryID']."',StoreCategoryName='".$_POST['StoreCategoryName']."',SubTitle='".$_POST['SubTitle']."',
	    Title='".mysql_real_escape_string($_POST['Title'])."',BoldTitle='".(empty($_POST['BoldTitle'])?0:1)."',
	    Border='".(empty($_POST['Border'])?0:1)."',Featured='".(empty($_POST['Featured'])?0:1)."',Highlight='".(empty($_POST['Highlight'])?0:1)."',
	    HomePageFeatured='".(empty($_POST['HomePageFeatured'])?0:1)."',GalleryTypeFeatured='".(empty($_POST['GalleryTypeFeatured'])?0:1)."',GalleryTypePlus='".(empty($_POST['GalleryTypePlus'])?0:1)."',
	    InsuranceOption='".$_POST['InsuranceOption']."',InsuranceFee='".$_POST['InsuranceFee']."',
	    InternationalInsurance='".$_POST['InternationalInsurance']."',InternationalInsuranceFee='".$_POST['InternationalInsuranceFee']."',
	    accountId='".$this->account_id."',UseStandardFooter='".(empty($_POST['UseStandardFooter'])?0:1)."' where Id = '".$id."'";
	}else{
	    $sql = "update template set 
	    BuyItNowPrice='".$_POST['BuyItNowPrice']."',Country='CN',Currency='".$_POST['Currency']."',
	    Description='".htmlentities($_POST['Description'])."',DispatchTimeMax='".$_POST['DispatchTimeMax']."',
	    ListingDuration='".$_POST['ListingDuration']."',ListingType='".$_POST['ListingType']."',Location='".$_POST['Location']."',PaymentMethods='PayPal',
	    PayPalEmailAddress='".$_POST['PayPalEmailAddress']."',PostalCode='".$_POST['PostalCode']."',
	    PrimaryCategoryCategoryID='".$_POST['PrimaryCategoryCategoryID']."',PrimaryCategoryCategoryName='".$_POST['PrimaryCategoryCategoryName']."',
	    SecondaryCategoryCategoryID='".$_POST['SecondaryCategoryCategoryID']."',SecondaryCategoryCategoryName='".$_POST['SecondaryCategoryCategoryName']."',
	    Quantity='".@$_POST['Quantity']."',ReservePrice='".@$_POST['ReservePrice']."',
	    Site='".$_POST['Site']."',SKU='".$_POST['SKU']."',StartPrice='".$_POST['StartPrice']."',StoreCategory2ID='".$_POST['StoreCategory2ID']."',StoreCategory2Name='".$_POST['StoreCategory2Name']."',
	    StoreCategoryID='".$_POST['StoreCategoryID']."',StoreCategoryName='".$_POST['StoreCategoryName']."',SubTitle='".$_POST['SubTitle']."',
	    Title='".mysql_real_escape_string($_POST['Title'])."',BoldTitle='".(empty($_POST['BoldTitle'])?0:1)."',
	    Border='".(empty($_POST['Border'])?0:1)."',Featured='".(empty($_POST['Featured'])?0:1)."',Highlight='".(empty($_POST['Highlight'])?0:1)."',
	    HomePageFeatured='".(empty($_POST['HomePageFeatured'])?0:1)."',GalleryTypeFeatured='".(empty($_POST['GalleryTypeFeatured'])?0:1)."',GalleryTypePlus='".(empty($_POST['GalleryTypePlus'])?0:1)."',
	    InsuranceOption='".$_POST['InsuranceOption']."',InsuranceFee='".$_POST['InsuranceFee']."',
	    InternationalInsurance='".$_POST['InternationalInsurance']."',InternationalInsuranceFee='".$_POST['InternationalInsuranceFee']."',
	    accountId='".$this->account_id."',UseStandardFooter='".(empty($_POST['UseStandardFooter'])?0:1)."' where Id = '".$id."'";
	}
	$result = mysql_query($sql, eBayListing::$database_connect);
	//echo $sql;
	//exit;
	
	$sql_1 = "delete from template_picture_url where templateId = '".$id."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$i = 1;
	while(!empty($_POST['picture_'.$i])){
	    $sql_1 = "insert into template_picture_url (templateId,url) values 
	    ('".$id."','".$_POST['picture_'.$i]."')";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $i++;
	}
	
	$sql_1 = "delete from template_shipping_service_options where templateId = '".$id."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$i = 1;
	while(!empty($_POST['ShippingService_'.$i])){
	    $sql_1 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServicePriority) values
	    ('".$id."','".@$_POST['ShippingServiceFree_'.$i]."','".$_POST['ShippingService_'.$i]."','".$_POST['ShippingServiceCost_'.$i]."','".$i."')";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $i++;
	}
	
	$sql_1 = "delete from template_international_shipping_service_option where templateId = '".$id."'";
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
	    $sql_2 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServicePriority,ShipToLocation) values
	    ('".$id."','".$_POST['InternationalShippingService_'.$i]."','".$_POST['InternationalShippingServiceCost_'.$i]."','".$i."','".$ShipToLocation."')";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    $i++;
	}
	
	/*
	英,美,法,澳,
	Europe/London       +0100  -8h
	America/New_York    -0400  -12h
	Europe/Paris        +0200  -7h
	Australia/Canberra  +1000  +2h
	
	Asia/Shanghai       +0800
	
	Array
	(
	    [LB00009-Mon-am-1] => Array
		(
		    [0] => 1:00 AM
		    [1] => 1:01 AM
		    [2] => 1:02 AM
		)
	)

	*/
	$sql_1 = "delete from schedule where template_id = '".$id."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);    
	
	if(!empty($_SESSION['Schedule'])){
	    switch($_POST['Site']){
		case "US":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("+12 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("+12 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				//echo $sql_3;
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    
		case "UK":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("+7 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("+7 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    
		case "Australia":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("-3 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("-3 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    
		case "France":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("+6 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("+6 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    }
	}
	
	$temp_array = array();
	if(!empty($_SESSION['AttributeSet'][$id])){
	    //print_r($_SESSION['AttributeSet']);
	    //exit;
	    $sql_4 = "select attribute_set_id from template_attribute_set where templateId = '".$id."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	    $row_4 = mysql_fetch_assoc($result_4);
	    
	    $sql_4 = "delete from template_attribute where attribute_set_id = '".$row_4['attribute_set_id']."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	    
	    $sql_4 = "delete from template_attribute_set where templateId = '".$id."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	
	    foreach($_SESSION['AttributeSet'][$id] as $attributeSetID=>$Attribute){
		$sql_4 = "insert into template_attribute_set (templateId,attributeSetID) values ('".$id."', '".$attributeSetID."')";
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
				$sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) values 
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
			$sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) values 
			('".$key."', '".$attribute_set_id."', '".$ValueID."')";
			$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		    }
		}
	    }
	}
	
	$sql_5 = "select count(*) as num from template_to_template_cateogry where template_id = '".$id."'";
	$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	$row_5 = mysql_fetch_assoc($result_5);
	
	if($row_5['num'] > 0){
	    $sql_6 = "update template_to_template_cateogry set template_category_id = '".$_POST['template_category_id']."' where template_id = '".$id."'";
	    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
	}else{
	    $sql_6 = "insert into template_to_template_cateogry (template_id,template_category_id) values ('".$id."','".$_POST['template_category_id']."')";
	    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
	}
	
	
	if($result && $result_1 && $result_6){
	    //unset($_SESSION['Schedule']);
	    //unset($_SESSION['AttributeSet']);
	    echo '{success: true, msg: "Update Template Success!"}';
	    $this->log("template", "update template ".$id." success.");
	}else{
	    echo '{success: false,
		    errors: {message: "can\'t create."}
		}';
	    $this->log("template", "update template ".$id." failure.", "error");
	}
    }
    
    public function updateMultiTemplate(){
	//print_r($_POST);
	$ids = explode(',', $_GET['template_id']);
	$where = " where ";
	foreach($ids as $id){
	    $where .= "Id = ".$id." or ";
	}
	$where = substr($where, 0, -4);
	
	$update = "update template set ";
	if(!empty($_POST['BuyItNowPrice']) && $_POST['BuyItNowPrice'] != 'Multi Value'){
	    $update .= "BuyItNowPrice = '".$_POST['BuyItNowPrice']."',";
	}
	
	if(!empty($_POST['Currency']) && $_POST['Currency'] != 'Multi Value'){
	    $update .= "Currency = '".$_POST['Currency']."',";
	}
	
	if(!empty($_POST['Description']) && $_POST['Description'] != 'Multi Value'){
	    $update .= "Description = '".htmlentities($_POST['Description'])."',";
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
	
	if(!empty($_POST['StartPrice']) && $_POST['StartPrice'] != 'Multi Value'){
	    $update .= "StartPrice = '".$_POST['StartPrice']."',";
	}
	
	if(!empty($_POST['StoreCategory2ID']) && $_POST['StoreCategory2ID'] != 'Multi Value'){
	    $update .= "StoreCategory2ID = '".$_POST['StoreCategory2ID']."',";
	}
	
	if(!empty($_POST['StoreCategory2Name']) && $_POST['StoreCategory2Name'] != 'Multi Value'){
	    $update .= "StoreCategory2Name = '".$_POST['StoreCategory2Name']."',";
	}
	
	if(!empty($_POST['SubTitle']) && $_POST['SubTitle'] != 'Multi Value'){
	    $update .= "SubTitle = '".mysql_real_escape_string($_POST['SubTitle'])."',";
	}
	
	if(!empty($_POST['Title']) && $_POST['Title'] != 'Multi Value'){
	    $update .= "Title = '".mysql_real_escape_string($_POST['Title'])."',";
	}
	
	if(!empty($_POST['SKU']) && $_POST['SKU'] != 'Multi Value'){
	    $update .= "SKU = '".$_POST['SKU']."',";
	}
	
	if(!empty($_POST['Border'])){
	    $update .= "Border = '".$_POST['Border']."',";
	}
	
	$update = substr($update, 0, -1);
	$sql = $update . $where;
	//echo $sql."\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	//echo $result;
	//print_r($_POST);
	$where = " where ";
	foreach($ids as $id){
	    $where .= "templateId = ".$id." or ";
	}
	$where = substr($where, 0, -4);
	
	if(!empty($_POST['picture_1'])){
	    $sql_1 = "delete from template_picture_url ".$where;
	    //echo $sql_1."\n";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    
	    foreach($ids as $id){
		$i = 1;
		while(!empty($_POST['picture_'.$i])){
		    $sql_1 = "insert into template_picture_url (templateId,url) values 
		    ('".$id."','".$_POST['picture_'.$i]."')";
		    //echo $sql_1."\n";
		    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		    $i++;
		}
	    }
	}
	
	if(!empty($_POST['ShippingService_1'])){
	    $sql_1 = "delete from template_shipping_service_options ".$where;
	    //echo $sql_1."\n";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    
	    foreach($ids as $id){
		$i = 1;
		while(!empty($_POST['ShippingService_'.$i])){
		    $sql_1 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServicePriority) values
		    ('".$id."','".@$_POST['ShippingServiceFree_'.$i]."','".$_POST['ShippingService_'.$i]."','".$_POST['ShippingServiceCost_'.$i]."','".$i."')";
		    //echo $sql_1."\n";
		    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		    $i++;
		}
	    }
	}
	
	if(!empty($_POST['InternationalShippingService_1'])){
	    $sql_1 = "delete from template_international_shipping_service_option ".$where;
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
		    $sql_2 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServicePriority,ShipToLocation) values
		    ('".$id."','".$_POST['InternationalShippingService_'.$i]."','".$_POST['InternationalShippingServiceCost_'.$i]."','".$i."','".$ShipToLocation."')";
		    //echo $sql_2."\n";
		    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		    $i++;
		}
	    }
	}
	
	session_start();
	$temp_array = array();
	if(!empty($_SESSION['AttributeSet'][$_GET['item_id']])){
	    //print_r($_SESSION['AttributeSet']);
	    foreach($ids as $id){
		//exit;
		$sql_4 = "select attribute_set_id from template_attribute_set where templateId = '".$id."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		$row_4 = mysql_fetch_assoc($result_4);
		
		$sql_4 = "delete from template_attribute where attribute_set_id = '".$row_4['attribute_set_id']."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		
		$sql_4 = "delete from template_attribute_set where item_id = '".$id."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	    
		foreach($_SESSION['AttributeSet'][$_GET['item_id']] as $attributeSetID=>$Attribute){
		    $sql_4 = "insert into template_attribute_set (templateId,attributeSetID) values ('".$id."', '".$attributeSetID."')";
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
				    $sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) values 
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
			    $sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) values 
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
	    echo '{success: true, msg: "Update Template '.$_GET['template_id'].' Success!"}';
	    $this->log("template", "update multi template ".$_GET['template_id']." success.");
	}else{
	    echo '{success: false,
		    errors: {message: "can\'t update, please notice admin."}
		}';
	    $this->log("template", "update multi template ".$_GET['template_id']." failure.", "error");
	}
    }
    
    public function templateImportSpoonFeeder(){
	//$handle = fopen("./a07052200ux0011.aie", "r");
	//print_r($_FILES);
	//exit;
	$success = false;
	$i = 1;
	$template_id_str = "";
	
	$file_array = array();
	
	while($_FILES['aie-'.$i]['error'] == 0){
	    $result = move_uploaded_file($_FILES['aie-'.$i]['tmp_name'], self::UPLOAD_TEMP_DIR.$_FILES['aie-'.$i]['name']);
	    if($result != false){
		$file_array[] = self::UPLOAD_TEMP_DIR.$_FILES['aie-'.$i]['name'];
	    }
	    $i++;
	}
	
	foreach($file_array as $file){
	    $handle = fopen($file, "r");
	    if ($handle) {
		$temp == 0;
		$data = array ();
		$data['accountId'] = $this->account_id;
		$data['PaymentMethods'] = 'PayPal';
		while (!feof($handle)) {
		    $buffer = trim(fgets($handle/*, 4096*/));
		    //echo $buffer;
		    if(!empty($buffer) && $buffer[0] == "[" && $buffer[strlen($buffer)-1] == "]"){
			$var_name = substr($buffer, 1, -1);
			//$count++;
			//echo $var_name;
			//echo "<br>";
			if($temp == 1 && $var_name == "DESCRIPTIONFONT"){
			    $temp = 2;
			}
		    }else{
			//@$data[$var_name] .= $buffer;
			switch($var_name){
			    //EBAY FIXED PRICE UK, EBAY AUCTION UK,
			    case "SELLING SITE":
				//$pos = strripos($buffer, " ");
				//$data['Site'] = substr($buffer, $pos+1);
				if(strpos($buffer,"FIXED PRICE")){
				    $data['ListingType'] = "FixedPriceItem";
				}elseif(strpos($buffer,"AUCTION")){
				    $data['ListingType'] = "Dutch";
				}elseif(strpos($buffer,"SHOPS")){
				    $data['ListingType'] = "StoresFixedPrice";
				}
				
				if(strpos($buffer, "USD")){
				    $data['Site'] = "US";
				}elseif(strpos($buffer, "UK")){
				    $data['Site'] = "UK";
				}elseif(strpos($buffer, "AU")){
				    $data['Site'] = "Australia";
				}elseif(strpos($buffer, "FRANCE")){
				    $data['Site'] = "France";
				}elseif(strpos($buffer, "GERMANY")){
				    $data['Site'] = "Germany";
				}
				//$data['ListingType'] = substr($buffer, $pos+1);
			    break;
			
			    case "MINIMUM BID PRICE":
				$data['StartPrice'] = $buffer;
			    break;
			
			    case "RESERVE PRICE":
				$data['ReservePrice'] = $buffer;
			    break;
			
			    case "BUY PRICE":
				$data['BuyItNowPrice'] = $buffer;
			    break;
			    
			    case "COUNTRY":
				$data['Country'] = $buffer;
			    break;
			
			    case "CURRENCY":
				//$pos = strpos($buffer, " ");
				//$data['Currency'] = substr($buffer, 0, $pos);
				$data['Currency'] = $buffer;
			    break;
			
			    case "DESCRIPTION":
				$temp = 1;
				$data['Description'] .= $buffer;
			    break;
			
			    case "DOMESTIC HANDLING TIME":
				$data['DispatchTimeMax'] = $buffer;
			    break;
			
			    case "AUCTION DURATION":
				$data['ListingDuration'] = 'Days_'.$buffer;
			    break;
			
			    case "LOCATION":
				$data['Location'] = $buffer;
			    break;
			
			    case "PAYPAL EMAIL":
				$data['PayPalEmailAddress'] = $buffer;
			    break;
			
			    case ""://
				$data['PostalCode'] = $buffer;
			    break;
			
			    case "CATEGORY 1":
				$data['PrimaryCategoryCategoryID'] = $buffer;
			    break;
			
			    case "CATEGORYDES 1":
				$data['PrimaryCategoryCategoryName'] = $buffer;
			    break;
			    
			    case "QUANTITY":
				$data['Quantity'] = $buffer;
			    break;
			
			    case "RETURN POLICY"://
				$data['ReturnPolicyDescription'] = $buffer;
			    break;
			
			    case "RETURN POLICY REFUND":
				$data['ReturnPolicyRefundOption'] = $buffer;
			    break;
			
			    case "RETURN POLICY ENABLED":
				if($buffer == "TRUE"){
				    $data['ReturnPolicyReturnsAcceptedOption'] = "ReturnsAccepted";
				}else{
				    $data['ReturnPolicyReturnsAcceptedOption'] = "ReturnsNotAccepted";
				}
			    break;
			
			    case "RETURN POLICY DAYS WITHIN":
				$r = explode(" ", $buffer);
				$data['ReturnPolicyReturnsWithinOption'] = $r[1]."_".$r[0];
			    break;
			
			    case "RETURN POLICY SHIPPING PAID BY":
				$data['ReturnPolicyShippingCostPaidByOption'] = $buffer;
			    break;
			
			    case "CATEGORY 2":
				$data['SecondaryCategoryCategoryID'] = $buffer;
			    break;
			
			    case "CATEGORYDES 2":
				$data['SecondaryCategoryCategoryName'] = $buffer;
			    break;
			
			    case "SKU CODE":
				$data['SKU'] = $buffer;
			    break;
			
			    case "STORE CATEGORY 2":
				$data['StoreCategory2ID'] = $buffer;
			    break;
			
			    case "STORECATEGORYDES 2":
				$data['StoreCategory2Name'] = $buffer;
			    break;
			
			    case "STORE CATEGORY":
				$data['StoreCategoryID'] = $buffer;
			    break;
			
			    case "STORECATEGORYDES":
				$data['StoreCategoryName'] = $buffer;
			    break;
			
			    case "SUBTITLE":
				$data['SubTitle'] = $buffer;
			    break;
			
			    case "TITLE":
				$data['Title'] = $buffer;
			    break;
			
			    case "GALLERY URL":
				$data['GalleryURL'] = $buffer;
			    break;
			
			    case "PICTURE URL":
				$picture = $buffer;
			    break;
			
			    case "INSURANCE OPTION":
				switch($buffer){
				    case "0":
					$data['InsuranceOption'] = "NotOffered";
				    break;
				
				    case "1":
					$data['InsuranceOption'] = "Optional";
				    break;
				
				    case "2":
					$data['InsuranceOption'] = "Required";
				    break;
				
				    case "3":
					$data['InsuranceOption'] = "IncludedInShippingHandling";
				    break;
				}
			    break;
			
			    case "INSURANCE":
				$data['InsuranceFee'] = $buffer;
			    break;
			
			    case "INTERNATIONAL INSURANCE OPTION":
				switch($buffer){
				    case "0":
					$data['InternationalInsurance'] = "NotOffered";
				    break;
				
				    case "1":
					$data['InternationalInsurance'] = "Optional";
				    break;
				
				    case "2":
					$data['InternationalInsurance'] = "Required";
				    break;
				
				    case "3":
					$data['InternationalInsurance'] = "IncludedInShippingHandling";
				    break;
				}
			    break;
			
			    case "INTERNATIONAL INSURANCE":
				$data['InternationalInsuranceFee'] = $buffer;
			    break;
			    
			//-------------------------- shipping service options  ----------------------------------------   
			    case "FREE SHIPPING":
				$data_1['shipping_service_options'][1]['FreeShipping'] = ($buffer=="TRUE")?1:0;
			    break;
			
			    case "SHIPPING SERVICE NAME":
				$data_1['shipping_service_options'][1]['ShippingService'] = $buffer;
				$data_1['shipping_service_options'][1]['ShippingServicePriority'] = 1;
			    break;
			
			    case "SHIPPING COST":
				$data_1['shipping_service_options'][1]['ShippingServiceCost'] = $buffer;
			    break;
			
			    case "ADDITIONAL SHIPPING COST":
				$data_1['shipping_service_options'][1]['ShippingServiceAdditionalCost'] = $buffer;
			    break;
			
			    case "SHIPPING SERVICE NAME2":
				$data_1['shipping_service_options'][2]['ShippingService'] = $buffer;
				$data_1['shipping_service_options'][2]['ShippingServicePriority'] = 2;
			    break;
			
			    case "SHIPPING COST2":
				$data_1['shipping_service_options'][2]['ShippingServiceCost'] = $buffer;
			    break;
			
			    case "ADDITIONAL SHIPPING COST2":
				$data_1['shipping_service_options'][2]['ShippingServiceAdditionalCost'] = $buffer;
			    break;
			
			    case "SHIPPING SERVICE NAME3":
				$data_1['shipping_service_options'][3]['ShippingService'] = $buffer;
				$data_1['shipping_service_options'][3]['ShippingServicePriority'] = 3;
			    break;
			
			    case "SHIPPING COST3":
				$data_1['shipping_service_options'][3]['ShippingServiceCost'] = $buffer;
			    break;
			
			    case "ADDITIONAL SHIPPING COST3":
				$data_1['shipping_service_options'][3]['ShippingServiceAdditionalCost'] = $buffer;
			    break;
			//--------------------------  international shipping service option --------------------------
			    case "INTERNATIONAL SHIPPING SERVICE NAME":
				$data_2['international_shipping_service_option'][1]['ShippingService'] = $buffer;
				$data_2['international_shipping_service_option'][1]['ShippingServicePriority'] = 1;
			    break;
			
			    case "INTERNATIONAL SHIPPING COST":
				$data_2['international_shipping_service_option'][1]['ShippingServiceCost'] = $buffer;
			    break;
			
			    case "INTERNATIONAL ADDITIONAL SHIPPING COST":
				$data_2['international_shipping_service_option'][1]['ShippingServiceAdditionalCost'] = $buffer;
			    break;
			
			    case "INTERNATIONAL SHIP TO LOCATIONS":
				$buffer = str_replace(array("1","2","3","4","5","6","7","8","9","10","11","12"),
						      array("None", "Worldwide", "Americas", "Asia", "AU", "CA",
							    "Europe", "DE", "JP", "MX", "GB", "Americas"), $buffer);
				$data_2['international_shipping_service_option'][1]['ShipToLocation'] = $buffer;
			    break;
			
			    case "INTERNATIONAL SHIPPING SERVICE NAME2":
				$data_2['international_shipping_service_option'][2]['ShippingService'] = $buffer;
				$data_2['international_shipping_service_option'][2]['ShippingServicePriority'] = 2;
			    break;
			
			    case "INTERNATIONAL SHIPPING COST2":
				$data_2['international_shipping_service_option'][2]['ShippingServiceCost'] = $buffer;
			    break;
			
			    case "INTERNATIONAL ADDITIONAL SHIPPING COST2":
				$data_2['international_shipping_service_option'][2]['ShippingServiceAdditionalCost'] = $buffer;
			    break;
			 
			    case "INTERNATIONAL SHIP TO LOCATIONS2":
				$buffer = str_replace(array("1","2","3","4","5","6","7","8","9","10","11","12"),
						      array("None", "Worldwide", "Americas", "Asia", "AU", "CA",
							    "Europe", "DE", "JP", "MX", "GB", "Americas"), $buffer);
				$data_2['international_shipping_service_option'][2]['ShipToLocation'] = $buffer;
			    break;
			
			    case "INTERNATIONAL SHIPPING SERVICE NAME3":
				$data_2['international_shipping_service_option'][3]['ShippingService'] = $buffer;
				$data_2['international_shipping_service_option'][3]['ShippingServicePriority'] = 3;
			    break;
			
			    case "INTERNATIONAL SHIPPING COST3":
				$data_2['international_shipping_service_option'][3]['ShippingServiceCost'] = $buffer;
			    break;
			
			    case "INTERNATIONAL ADDITIONAL SHIPPING COST3":
				$data_2['international_shipping_service_option'][3]['ShippingServiceAdditionalCost'] = $buffer;
			    break;
			
			    case "INTERNATIONAL SHIP TO LOCATIONS3":
				$buffer = str_replace(array("1","2","3","4","5","6","7","8","9","10","11","12"),
						      array("None", "Worldwide", "Americas", "Asia", "AU", "CA",
							    "Europe", "DE", "JP", "MX", "GB", "Americas"), $buffer);
				$data_2['international_shipping_service_option'][3]['ShipToLocation'] = $buffer;
			    break;
			
			    //--------------------  attribute  ----------------------------------------------------
			    case "ATTRIBUTE FOR CATEGORY 1":
				//$xml = new SimpleXMLElement($buffer);
				//print_r($xml);
				//echo $buffer;
				$dom = new DOMDocument();
				$dom->loadXML($buffer);
				$AttributeSet = $dom->getElementsByTagName("AttributeSet");
				foreach($AttributeSet as $AttributeSetNode){
				    //echo $AttributeSetNode->getAttribute("id");
				    //echo "<br>";
				    $data_3['AttributeSet']['id'] = $AttributeSetNode->getAttribute("id");
				}
				
				$Attribute = $dom->getElementsByTagName("Attribute");
				$i = 0;
				foreach($Attribute as $AttributeNode){
				    $data_3['AttributeSet']['Attribute'][$i]['id'] = $AttributeNode->getAttribute("id");
				    //echo "Attribute id:" . $AttributeNode->getAttribute("id");
				    //echo "<br>";
				    $Value = $AttributeNode->getElementsByTagName("Value");
				    $j = 0;
				    foreach($Value as $ValueNode){
					$data_3['AttributeSet']['Attribute'][$i]['Value'][$j]['id'] = $ValueNode->getAttribute("id");
					//echo "Value id:" . $ValueNode->getAttribute("id");
					//echo "<br>";
					$Name = $ValueNode->getElementsByTagName("Name");
					foreach($Name as $NameNode){
					    $data_3['AttributeSet']['Attribute'][$i]['Value'][$j]['Name'] = $NameNode->textContent;
					    //echo "Name:" .$NameNode->textContent;
					    //echo "<br>";
					}
					$j++;
				    }
				    //echo "<br>";
				    $i++;
				}
				//print_r($data_3);
				//print_r($AttributeSet);
				//$data['attribute_set'][$xml->@attributes] = $buffer;
				//$data[''] = $buffer;
			    break;
			}
			if($temp == 1){
			    $var_name = "DESCRIPTION";
			}else{
			    $var_name = "";
			}
		    }
		}
		
		/*
		fclose($handle);
		print_r($data);
		print_r($data_1);
		print_r($data_2);
		print_r($data_3);
		exit;
		*/
		
		$fields = "";
		$values = "";
    
		foreach($data as $id=>$name){
		    $fields .= $id.",";
		    $values .= "'".mysql_real_escape_string($name)."',";
		}
		$fields = substr($fields, 0, -1);
		$values = substr($values, 0, -1);
		$sql = "insert into template ($fields) values ($values)";
		//echo $sql."\n";
		//$this->saveFetchData("test.sql", $sql);
		$result = mysql_query($sql, eBayListing::$database_connect);
		$template_id = mysql_insert_id(eBayListing::$database_connect);
		
		foreach($data_1['shipping_service_options'] as $t){
		    if(!empty($t['ShippingService'])){
			$sql_1 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority) 
			values ('".$template_id."','".$t['FreeShipping']."','".$t['ShippingService']."','".$t['ShippingServiceCost']."','".$t['ShippingServiceAdditionalCost']."','".$t['ShippingServicePriority']."')";
			//echo $sql_1."\n";
			$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		    }
		}
		
		foreach($data_2['international_shipping_service_option'] as $t){
		    if(!empty($t['ShippingService'])){
			$sql_2 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) 
			values ('".$template_id."','".$t['ShippingService']."','".$t['ShippingServiceCost']."','".$t['ShippingServiceAdditionalCost']."','".$t['ShippingServicePriority']."','".$t['ShipToLocation']."')";
			//echo $sql_2."\n";
			$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		    }
		}
		
		if(empty($data_3['AttributeSet'])){
		    $sql_3 = "insert into template_attribute_set (templateId,attributeSetID) values ('".$template_id."','".$data_3['AttributeSet']['id']."')";
		    $result_3 = mysql_query($sql_3, eBayListing::$database_connect);
		    $attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
		    
		    foreach($data_3['AttributeSet']['Attribute'] as $a){
			foreach($a['Value'] as $v){
			    if(empty($v['Name'])){
				$sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) 
				values('".$a['id']."','".$attribute_set_id."','".$v['id']."')";
			    }else{
				$sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueLiteral) 
				values('".$a['id']."','".$attribute_set_id."','".$v['Name']."')";
			    }
			    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
			}
		    }
		}
		
		$sql_5 = "insert into template_picture_url (templateId,url) values ('".$template_id."','".$picture."')";
		$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
		
		if(!empty($_POST['template_category_id'])){
		    $sql_6 = "insert into template_to_template_cateogry (template_id,template_category_id) values ('".$template_id."','".$_POST['template_category_id']."')";
		    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
		}
		
		if($result){
		    $success = true;
		    $template_id_str .= $template_id . ", ";
		    //echo '{success: true, msg: "Import SpoonFeeder Template Success, template id is '.$template_id.'"}';
		}else{
		    $success = false;
		    //echo '{success: false, msg: ""}';
		}
	    }
	}
	
	if($success){
	    echo '{success: true, msg: "Import SpoonFeeder Template Success, template id is '.substr($template_id_str, 0, -2).'."}';
	}else{
	    echo '{success: false, msg: ""}';
	}
    }
    
    public function templateImportTurboLister(){
	$handle = fopen($_FILES['turboLister']['tmp_name'], "r");
	//$handle = fopen('./burbo lister.csv', "r");
	$i = 0;
	while (($data = fgetcsv($handle/*, 4602, ","*/)) !== FALSE) {
	    if($i == 0){
		$i++;
		continue;
	    }
	    $array = array();
	    $array['accountId'] = $this->account_id;
	    $array['Site'] = $data[1];
	    $array['ListingType'] = $data[2];
	    $array['Title'] = mysql_real_escape_string($data[3]);
	    $array['SubTitle'] = $data[4];
	    $array['SKU'] = $data[5];
	    
	    $sql_0 = "select id from site where name = '".$data[1]."'";
	    $result_0 = mysql_query($sql_0, eBayListing::$database_connect);
	    $row_0 = mysql_fetch_assoc($result_0);
	    $array['PrimaryCategoryCategoryID'] = $data[6];
	    $array['PrimaryCategoryCategoryName'] = $this->getCategoryPathById($row_0['id'], $data[6]);
	    if(!empty($data[7])){
		$array['SecondaryCategoryCategoryID'] = $data[7];
		$array['SecondaryCategoryCategoryName'] = $this->getCategoryPathById($row_0['id'], $data[7]);
	    }
	    
	    if(!empty($data[8])){
		$array['StoreCategoryID'] = $data[8];
		$array['StoreCategoryName'] = $data[8];
	    }
	    
	    if(!empty($data[9])){
		$array['StoreCategory2ID'] = $data[9];
		$array['StoreCategory2Name'] = $data[9];
	    }
	    
	    $array['Quantity'] = $data[10];
	    $array['Currency'] = $data[12];
	    $array['StartPrice'] = $data[13];
	    $array['BuyItNowPrice'] = $data[14];
	    $array['ReservePrice'] = $data[15];
	    
	    $array['InternationalInsurance'] = $data[16];
	    $array['InternationalInsuranceFee'] = $data[17];
	    
	    $array['InsuranceOption'] = $data[18];
	    $array['InsuranceFee'] = $data[19];
	    
	    $array['ListingDuration'] = 'Days_'.$data[22];
	    $array['Country'] = $data[24];
	    
	    
	    $data[28] = str_replace("@@@@%", " ", $data[28]);
	    $data[28] = str_replace("%0D%0A", " ", $data[28]);
	    $array['Description'] = $data[28];
	    //$array['url'] = $data[30];//
	    $array['BoldTitle'] = $data[31];
	    $array['Featured'] = $data[32];
	    switch($data[33]){
		case "Featured":
		    $array['GalleryTypeFeatured'] = 1;
		break;
	    
		case "Gallery":
		    $array['GalleryTypeGallery'] = 1;
		break;
	    
		case "Plus":
		    $array['GalleryTypePlus'] = 1;
		break;
	    }
	    
	    $array['Highlight'] = $data[35];
	    $array['Border'] = $data[36];
	    $array['HomePageFeatured'] = $data[37];
	    $array['Location'] = $data[51];
	    $array['PayPalEmailAddress'] = $data[54];
	    $array['ShippingType'] = $data[73];
	    $array['DispatchTimeMax'] = $data[115];
	    
	    
	    if(!empty($data[196])){
		$array['ReturnPolicyRefundOption'] = $data[198];
		$array['ReturnPolicyReturnsAcceptedOption'] = $data[196];
		$array['ReturnPolicyReturnsWithinOption'] = $data[197];
		$array['ReturnPolicyShippingCostPaidByOption'] = $data[199];
		//$array['ReturnPolicyDescription'] = $data[115];
	    }
	    
	    $fields = "";
	    $values = "";
	    
	    foreach($array as $id=>$name){
		$fields .= $id.",";
		$values .= "'".mysql_real_escape_string($name)."',";
	    }
	    $fields = substr($fields, 0, -1);
	    $values = substr($values, 0, -1);
	    $sql = "insert into template ($fields) values ($values)";
	    //$this->saveFetchData("test.sql", $sql);
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $template_id = mysql_insert_id(eBayListing::$database_connect);
	    //var_dump($result);
	    //exit;
	    //template picture url
	    $array['template_picture_url']['url'] = $data[30];
	    $sql_0 = "insert into template_picture_url (templateId,url) 
	    values ('".$template_id."','".$array['template_picture_url']['url']."')";
	    $result_0 = mysql_query($sql_0, eBayListing::$database_connect);
		    
	    
	    //template shipping service options
	    $array['template_shipping_service_options'][1]['FreeShipping'] = $data[92];
	    $array['template_shipping_service_options'][1]['ShippingService'] = $data[88];
	    $array['template_shipping_service_options'][1]['ShippingServiceCost'] = $data[89];
	    $array['template_shipping_service_options'][1]['ShippingServiceAdditionalCost'] = $data[90];
	    $array['template_shipping_service_options'][1]['ShippingServicePriority'] = $data[91];
	    
	    $array['template_shipping_service_options'][2]['ShippingService'] = $data[94];
	    $array['template_shipping_service_options'][2]['ShippingServiceCost'] = $data[95];
	    $array['template_shipping_service_options'][2]['ShippingServiceAdditionalCost'] = $data[96];
	    $array['template_shipping_service_options'][2]['ShippingServicePriority'] = $data[97];
	    
	    $array['template_shipping_service_options'][3]['ShippingService'] = $data[99];
	    $array['template_shipping_service_options'][3]['ShippingServiceCost'] = $data[100];
	    $array['template_shipping_service_options'][3]['ShippingServiceAdditionalCost'] = $data[101];
	    $array['template_shipping_service_options'][3]['ShippingServicePriority'] = $data[102];
	    
	    foreach($array['template_shipping_service_options'] as $t){
		if(!empty($t['ShippingService'])){
		    $sql_1 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority) 
		    values ('".$template_id."','".$t['FreeShipping']."','".$t['ShippingService']."','".$t['ShippingServiceCost']."','".$t['ShippingServiceAdditionalCost']."','".$t['ShippingServicePriority']."')";
		    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		}
	    }
	    //template international shipping service option
	    $array['template_international_shipping_service_option'][1]['ShippingService'] = $data[116];
	    $array['template_international_shipping_service_option'][1]['ShippingServiceCost'] = $data[117];
	    $array['template_international_shipping_service_option'][1]['ShippingServiceAdditionalCost'] = $data[118];
	    $array['template_international_shipping_service_option'][1]['ShipToLocation'] = str_replace('|', ',' ,$data[119]);
	    $array['template_international_shipping_service_option'][1]['ShippingServicePriority'] = $data[120];
	    
	    $array['template_international_shipping_service_option'][2]['ShippingService'] = $data[121];
	    $array['template_international_shipping_service_option'][2]['ShippingServiceCost'] = $data[122];
	    $array['template_international_shipping_service_option'][2]['ShippingServiceAdditionalCost'] = $data[123];
	    $array['template_international_shipping_service_option'][2]['ShipToLocation'] = str_replace('|', ',' ,$data[124]);
	    $array['template_international_shipping_service_option'][2]['ShippingServicePriority'] = $data[125];
	    
	    $array['template_international_shipping_service_option'][3]['ShippingService'] = $data[126];
	    $array['template_international_shipping_service_option'][3]['ShippingServiceCost'] = $data[127];
	    $array['template_international_shipping_service_option'][3]['ShippingServiceAdditionalCost'] = $data[128];
	    $array['template_international_shipping_service_option'][3]['ShipToLocation'] = str_replace('|', ',' ,$data[129]);
	    $array['template_international_shipping_service_option'][3]['ShippingServicePriority'] = $data[130];
	   
	    foreach($array['template_international_shipping_service_option'] as $t){
		if(!empty($t['ShippingService'])){
		    $sql_2 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) 
		    values ('".$template_id."','".$t['ShippingService']."','".$t['ShippingServiceCost']."','".$t['ShippingServiceAdditionalCost']."','".$t['ShippingServicePriority']."','".$t['ShipToLocation']."')";
		    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		}
	    }
	    
	    if(!empty($_POST['template_category_id'])){
		$sql_3 = "insert into template_to_template_cateogry (template_id,template_category_id) values ('".$template_id."','".$_POST['template_category_id']."')";
		$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	    }
	    //$array['SKU1'] = $data[194];
	    
	    //print_r($array);
	    //$this->saveFetchData("test.html", print_r($array));
	    $i++;
	}
	
	if($result){
	    echo '{success: true, msg: "Import Turbo Lister Template Success, template id is '.$template_id.'"}';
	}else{
	    echo '{success: false, msg: ""}';
	}
    }
    
    public function templateExport(){
	$data = "SKU,Title,Price\n";
	$sql = "select SKU,Title,ListingType,BuyItNowPrice,StartPrice from template";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
	    if($row['ListingType'] == "Chinese" || $row['ListingType'] == "Dutch"){
		
	    }else{
		
	    }
	    $data .= '"'.$row['SKU'].'","'.$row['Title'].'","'.$row['StartPrice'].'"'."\n";
	}
	header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=template.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $data;
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
    	global $categoryPathArray;
	global $nest;
	
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
    			$categoryPath .= $categoryPathArray[$i-1] . " >> ";
    		}
    		$categoryPath = substr($categoryPath, 0, -4);
    		//print_r($categoryPath);
    		return $categoryPath;
    	}
    }
   
    private function getSiteIdByName($name){
	$sql = "select id from site where name = '".$name."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
    	$row = mysql_fetch_assoc($result);
	return $row['id'];
    }
    
    private function getSiteNameById($id){
	$sql = "select name from site where id = '".$id."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
    	$row = mysql_fetch_assoc($result);
	return $row['name'];
    }
    
    //--------  Template Schedule Time  --------------------------------------------------
    public function addTemplateScheduleTime(){
	if(!empty($_POST['time'])){
	    session_start();
	    if(@!is_array($_SESSION['Schedule'][$_POST['template_id'].'-'.$_POST['dayTime']])){
		$_SESSION['Schedule'][$_POST['template_id'].'-'.$_POST['dayTime']] = array();
	    }
	    if(@!in_array($_POST['time'], $_SESSION['Schedule'][$_POST['sku'].'-'.$_POST['dayTime']])){
		$_SESSION['Schedule'][$_POST['template_id'].'-'.$_POST['dayTime']][] = $_POST['time'];
	    }
	}
	print_r($_SESSION['Schedule'][$_POST['template_id'].'-'.$_POST['dayTime']]);
    }
    
    public function deleteTemplateScheduleTime(){
	session_start();
	$id_array = explode(",", $_POST['id']);
	print_r($id_array);
	foreach($id_array as $id){
	    unset($_SESSION['Schedule'][$_POST['template_id'].'-'.$_POST['dayTime']][$id]);
	}
	/*
	$i = 0;
	foreach($_SESSION[$_POST['sku'].'-'.$_POST['dayTime']] as $s){
	    $_SESSION[$_POST['sku'].'-'.$_POST['dayTime']][$i] = $s;
	    $i++;
	}
	*/
	//sort($_SESSION[$_POST['sku'].'-'.$_POST['dayTime']]);
	print_r($_SESSION['Schedule'][$_POST['template_id'].'-'.$_POST['dayTime']]);
    }
    
    public function deleteAllTemplateScheduleTime(){
	session_start();
	unset($_SESSION['Schedule'][$_POST['template_id'].'-'.$_POST['dayTime']]);
    }
    
    public function getTemplateScheduleTime(){
	session_start();
	//print_r($_SESSION[$_GET['sku'].'-'.$_GET['dayTime']]);
	//$array = array(array("time"=>"13:21"), array("time"=>"13:30"));
	if(@is_array($_SESSION['Schedule'][$_GET['template_id'].'-'.$_GET['dayTime']])){
	    sort($_SESSION['Schedule'][$_GET['template_id'].'-'.$_GET['dayTime']]);
	    $data = array();
	    $i = 0;
	    foreach($_SESSION['Schedule'][$_GET['template_id'].'-'.$_GET['dayTime']] as $s){
		$data[$i]['time'] = $s;
		$i++;
	    }
	    echo json_encode($data);
	}else{
	    echo json_encode(array());
	}
	//print_r($_SESSION['Schedule']);
    }
    
    public function updateTemplateScheduleTime(){
	
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
	$sql = "select id from site where name = '".$_POST['SiteID']."'";
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
    
    //-------------------------- Item  -------------------------------------------------------------------
    public function activeItemExport(){
	$data = "SKU,Item Title,Insertion Fee,Item ID,Start Time,End Time,Duration,Qty,Slod Qty,Price,Listing Type\n";
	$sql = "select ItemID,SKU,Title,ListingType,InsertionFee,ListingFee,Quantity,QuantitySold,ListingDuration,StartTime,EndTime,StartPrice,BuyItNowPrice from items where ListingStatus = 'Active' or Status = 1 or Status = 2";
	//echo $sql_1."\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
	    $data .= $row['SKU'].",".$row['Title'].",".$row['InsertionFee'].",".$row['ItemID'].",".$row['StartTime'].",".$row['EndTime'].",".$row['ListingDuration'].",".$row['Quantity'].",".$row['QuantitySold'].",".$row['StartPrice'].",".$row['ListingType']."\n";
	}
	header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=activeItem.csv");
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
	    
	    $sql = "select Id,SKU,Title,BuyItNowPrice,ListingDuration,ListingType,Quantity,StartPrice,ScheduleTime,ScheduleLocalTime,Site from items where accountId = '".$this->account_id."' and Status = 0 order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
            $result = mysql_query($sql, eBayListing::$database_connect);
            
	}else{
	    $where = " where accountId = '".$this->account_id."' and Status = 0 ";
		
	    if(empty($_POST['start']) && empty($_POST['limit'])){
                $_POST['start'] = 0;
                $_POST['limit'] = 20;
            }
	    
            if(!empty($_POST['SKU'])){
                $where .= " and SKU like '%".$_POST['SKU']."%'";
            }
            
            if(!empty($_POST['Title'])){
                $where .= " and Title like '%".$_POST['Title']."%'";
            }
                
            $sql = "select count(*) as count from items ".$where;
            $result = mysql_query($sql, eBayListing::$database_connect);
            $row = mysql_fetch_assoc($result);
            $totalCount = $row['count'];
            
            $sql = "select Id,SKU,Title,BuyItNowPrice,ListingDuration,ListingType,Quantity,StartPrice,ScheduleTime,ScheduleLocalTime,Site from items ".$where." order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
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
	    $sql_1 = "select ShippingServiceCost from international_shipping_service_option where ItemID = '".$row['Id']."'";
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
	    
	    $sql = "select Id,SKU,Title,BuyItNowPrice,ListingDuration,ListingType,Quantity,StartPrice,ScheduleTime,ScheduleLocalTime,Site from items where accountId = '".$this->account_id."' and Status = 1 order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
            $result = mysql_query($sql, eBayListing::$database_connect);
            
	}else{
	    $where = " where accountId = '".$this->account_id."' and Status = 1 ";
		
	    if(empty($_POST['start']) && empty($_POST['limit'])){
                $_POST['start'] = 0;
                $_POST['limit'] = 20;
            }
	    
            if(!empty($_POST['SKU'])){
                $where .= " and SKU like '%".$_POST['SKU']."%'";
            }
            
            if(!empty($_POST['Title'])){
                $where .= " and Title like '%".$_POST['Title']."%'";
            }
                
            $sql = "select count(*) as count from items ".$where;
            $result = mysql_query($sql, eBayListing::$database_connect);
            $row = mysql_fetch_assoc($result);
            $totalCount = $row['count'];
            
            $sql = "select Id,SKU,Title,BuyItNowPrice,ListingDuration,ListingType,Quantity,StartPrice,ScheduleTime,ScheduleLocalTime,Site from items ".$where." order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
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
	    $sql_1 = "select ShippingServiceCost from international_shipping_service_option where ItemID = '".$row['Id']."'";
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
		switch($row['Site']){
		    case "US":
			$time = date("Y-m-d H:i:s", strtotime("+12 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "UK":
			$time = date("Y-m-d H:i:s", strtotime("+7 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "Australia":
			$time = date("Y-m-d H:i:s", strtotime("-3 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "France":
			$time = date("Y-m-d H:i:s", strtotime("+6 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		}
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
	    switch($row['Site']){
		case "US":
		    $time = date("Y-m-d H:i:s", strtotime("+12 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "UK":
		    $time = date("Y-m-d H:i:s", strtotime("+7 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "Australia":
		    $time = date("Y-m-d H:i:s", strtotime("-3 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "France":
		    $time = date("Y-m-d H:i:s", strtotime("+6 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    }
	    
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

	//Active, Completed, Ended
	$sql = "select count(*) as count from items where accountId = '".$this->account_id."' and (ListingStatus = 'Active' or Status = 2)";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$totalCount = $row['count'];
	
	$sql_1 = "select Id,SKU,ItemID,Title,Site,ListingType,Quantity,ListingDuration,EndTime,StartPrice,BuyItNowPrice from items where accountId = '".$this->account_id."' and (ListingStatus = 'Active' or Status = 2) order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
	//echo $sql_1."\n";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$data = array();
	while($row_1 = mysql_fetch_assoc($result_1)){
	    if($row_1['ListingType'] == "FixedPriceItem" || $row_1['ListingType'] == "StoresFixedPrice"){
		$row_1['Price'] = $row_1['StartPrice'];
	    }else{
		$row_1['Price'] = $row_1['BuyItNowPrice'];
	    }
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
	    $data[] = $row_1;
	}
	
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$data));
	mysql_free_result($result);
	mysql_free_result($result_1);
    }
    
    public function copyItem(){
	if($_GET['type'] == "wait"){
	    $Status = 0;
	}elseif($_GET['type'] == "schedule"){
	    $Status = 1;
	}
	
	if(strpos($_POST['ids'], ',')){
	    $array = explode(',', $_POST['ids']);
	    foreach($array as $a){
		$sql_1 = "insert into items (AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
		Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
		PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,
		ReservePrice,CurrentPrice,ScheduleTime,ScheduleLocalTime,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
		StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
		Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
		GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,
		InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,Status) 
		select AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
		Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
		PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,
		ReservePrice,CurrentPrice,ScheduleTime,ScheduleLocalTime,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
		StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
		Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
		GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,
		InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,".$Status." from items where Id = '".$a."'";
		
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
	    PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,
	    ReservePrice,CurrentPrice,ScheduleTime,ScheduleLocalTime,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
	    StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
	    Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
	    GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,
	    InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,Status) 
	    select AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
	    Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
	    PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,
	    ReservePrice,CurrentPrice,ScheduleTime,ScheduleLocalTime,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
	    StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
	    Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
	    GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,
	    InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,".$Status." from items where Id = '".$_POST['ids']."'";
	    
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
		echo 1;
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
    
    public function addToSchedule(){
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
	$row['Description'] = html_entity_decode($row['Description']);
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
	session_start();
	if($_POST['ListingType'] == "FixedPriceItem" || $_POST['ListingType'] == "StoresFixedPrice"){
	    $_POST['StartPrice'] = $_POST['BuyItNowPrice'];
	    $_POST['BuyItNowPrice'] = 0;
	}
	
	if($_POST['ListingType'] == "Chinese"){
	    $_POST['Quantity'] = 1;   
	}
	
	$id = $_GET['item_id'];
	//StartTime,EndTime
	//$PaymentMethods = ($_POST['PayPalPayment'] == 1)?'PayPal':'';
	if(!empty($_SESSION['ReturnPolicyReturns'][$id]['ReturnPolicyReturnsAcceptedOption'])){
	    $sql = "update items set 
	    BuyItNowPrice='".$_POST['BuyItNowPrice']."',Country='CN',Currency='".$_POST['Currency']."',
	    Description='".htmlentities($_POST['Description'])."',DispatchTimeMax='".$_POST['DispatchTimeMax']."',
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
	    Title='".mysql_real_escape_string($_POST['Title'])."',BoldTitle='".(empty($_POST['BoldTitle'])?0:1)."',
	    Border='".(empty($_POST['Border'])?0:1)."',Featured='".(empty($_POST['Featured'])?0:1)."',Highlight='".(empty($_POST['Highlight'])?0:1)."',
	    HomePageFeatured='".(empty($_POST['HomePageFeatured'])?0:1)."',GalleryTypeFeatured='".(empty($_POST['GalleryTypeFeatured'])?0:1)."',GalleryTypePlus='".(empty($_POST['GalleryTypePlus'])?0:1)."',GalleryURL='".$_POST['GalleryURL']."',
	    InsuranceOption='".$_POST['InsuranceOption']."',InsuranceFee='".$_POST['InsuranceFee']."',
	    InternationalInsurance='".$_POST['InternationalInsurance']."',InternationalInsuranceFee='".$_POST['InternationalInsuranceFee']."',
	    accountId='".$this->account_id."' where Id = '".$id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	}else{
	    $sql = "update items set 
	    BuyItNowPrice='".$_POST['BuyItNowPrice']."',Country='CN',Currency='".$_POST['Currency']."',
	    Description='".htmlentities($_POST['Description'])."',DispatchTimeMax='".$_POST['DispatchTimeMax']."',
	    ListingDuration='".$_POST['ListingDuration']."',ListingType='".$_POST['ListingType']."',Location='".$_POST['Location']."',PaymentMethods='PayPal',
	    PayPalEmailAddress='".$_POST['PayPalEmailAddress']."',PostalCode='".$_POST['PostalCode']."',
	    PrimaryCategoryCategoryID='".$_POST['PrimaryCategoryCategoryID']."',PrimaryCategoryCategoryName='".$_POST['PrimaryCategoryCategoryName']."',
	    SecondaryCategoryCategoryID='".$_POST['SecondaryCategoryCategoryID']."',SecondaryCategoryCategoryName='".$_POST['SecondaryCategoryCategoryName']."',
	    Quantity='".@$_POST['Quantity']."',ReservePrice='".@$_POST['ReservePrice']."',
	    Site='".$_POST['Site']."',SKU='".$_POST['SKU']."',StartPrice='".$_POST['StartPrice']."',StoreCategory2ID='".$_POST['StoreCategory2ID']."',StoreCategory2Name='".$_POST['StoreCategory2Name']."',
	    StoreCategoryID='".$_POST['StoreCategoryID']."',StoreCategoryName='".$_POST['StoreCategoryName']."',SubTitle='".$_POST['SubTitle']."',
	    Title='".mysql_real_escape_string($_POST['Title'])."',BoldTitle='".(empty($_POST['BoldTitle'])?0:1)."',
	    Border='".(empty($_POST['Border'])?0:1)."',Featured='".(empty($_POST['Featured'])?0:1)."',Highlight='".(empty($_POST['Highlight'])?0:1)."',
	    HomePageFeatured='".(empty($_POST['HomePageFeatured'])?0:1)."',GalleryTypeFeatured='".(empty($_POST['GalleryTypeFeatured'])?0:1)."',GalleryTypePlus='".(empty($_POST['GalleryTypePlus'])?0:1)."',GalleryURL='".$_POST['GalleryURL']."',
	    InsuranceOption='".$_POST['InsuranceOption']."',InsuranceFee='".$_POST['InsuranceFee']."',
	    InternationalInsurance='".$_POST['InternationalInsurance']."',InternationalInsuranceFee='".$_POST['InternationalInsuranceFee']."',
	    accountId='".$this->account_id."' where Id = '".$id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	}
	//echo $sql;
	//exit;
	
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
	$where = " where ";
	foreach($ids as $id){
	    $where .= "Id = ".$id." or ";
	}
	$where = substr($where, 0, -4);
	
	$update = "update items set ";
	if(!empty($_POST['BuyItNowPrice']) && $_POST['BuyItNowPrice'] != 'Multi Value'){
	    $update .= "BuyItNowPrice = '".$_POST['BuyItNowPrice']."',";
	}
	
	if(!empty($_POST['Currency']) && $_POST['Currency'] != 'Multi Value'){
	    $update .= "Currency = '".$_POST['Currency']."',";
	}
	
	if(!empty($_POST['Description']) && $_POST['Description'] != 'Multi Value'){
	    $update .= "Description = '".htmlentities($_POST['Description'])."',";
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
	
	if(!empty($_POST['StartPrice']) && $_POST['StartPrice'] != 'Multi Value'){
	    $update .= "StartPrice = '".$_POST['StartPrice']."',";
	}
	
	if(!empty($_POST['StoreCategory2ID']) && $_POST['StoreCategory2ID'] != 'Multi Value'){
	    $update .= "StoreCategory2ID = '".$_POST['StoreCategory2ID']."',";
	}
	
	if(!empty($_POST['StoreCategory2Name']) && $_POST['StoreCategory2Name'] != 'Multi Value'){
	    $update .= "StoreCategory2Name = '".$_POST['StoreCategory2Name']."',";
	}
	
	if(!empty($_POST['SubTitle']) && $_POST['SubTitle'] != 'Multi Value'){
	    $update .= "SubTitle = '".mysql_real_escape_string($_POST['SubTitle'])."',";
	}
	
	if(!empty($_POST['Title']) && $_POST['Title'] != 'Multi Value'){
	    $update .= "Title = '".mysql_real_escape_string($_POST['Title'])."',";
	}
	
	if(!empty($_POST['SKU']) && $_POST['SKU'] != 'Multi Value'){
	    $update .= "SKU = '".$_POST['SKU']."',";
	}
	
	if(!empty($_POST['Border'])){
	    $update .= "Border = '".$_POST['Border']."',";
	}
	
	$update = substr($update, 0, -1);
	$sql = $update . $where;
	//echo $sql."\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	//echo $result;
	//print_r($_POST);
	$where = " where ";
	foreach($ids as $id){
	    $where .= "ItemID = ".$id." or ";
	}
	$where = substr($where, 0, -4);
	
	if(!empty($_POST['picture_1'])){
	    $sql_1 = "delete from picture_url ".$where;
	    //echo $sql_1."\n";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    
	    foreach($ids as $id){
		$i = 1;
		while(!empty($_POST['picture_'.$i])){
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
	
	session_start();
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
    
    public function updateField(){
	switch($_POST['table']){
	    case "items":
		$sql_1 = "select ScheduleTime,Status,ListingType from items where Id = '".$_POST['id']."'";
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		$row_1 = mysql_fetch_assoc($result_1);
		switch($row_1['Status']){
		    case 0:
			if($row_1['ScheduleTime'] > date("Y-m-d H:i:s")){
			    $sql = "update items set ".$_POST['field']." = '".$_POST['value']."' where Id = '".$_POST['id']."'";
			    $result = mysql_query($sql, eBayListing::$database_connect);
			    echo "[{success: true, msg: 'update ".$_POST['field']." success.'}]";
			}else{
			    echo "[{success: false, msg: 'item have been uploaded.'}]";
			    return 0;
			}
		    break;
		
		    case 1:
			
		    case 2:
			if($_POST['field'] == "Price"){
			    if($row_1['ListingType'] == "Chinese" || $row_1['ListingType'] == "Dutch"){
				echo "[{success: false, msg: 'revise ".$_POST['field']." failure.'}]";
				//$_POST['field'] = "StartPrice";
				//$sql = "update items set ".$_POST['field']." = '".$_POST['value']."',Status = 2 where Id = '".$_POST['id']."'";
				//$result = mysql_query($sql, eBayListing::$database_connect);
				//echo "[{success: true, msg: 'revise success.'}]";
			    }elseif($row_1['ListingType'] == "StoresFixedPrice" || $row_1['ListingType'] == "FixedPriceItem"){
				$_POST['field'] = "StartPrice";
				$sql = "update items set ".$_POST['field']." = '".$_POST['value']."',Status = 2 where Id = '".$_POST['id']."'";
				//echo $sql;
				$result = mysql_query($sql, eBayListing::$database_connect);
				echo "[{success: true, msg: 'revise ".$_POST['field']." success.'}]";
			    }
			}else{
			    $sql = "update items set ".$_POST['field']." = '".$_POST['value']."',Status = 2 where Id = '".$_POST['id']."'";
			    $result = mysql_query($sql, eBayListing::$database_connect);
			    echo "[{success: true, msg: 'revise ".$_POST['field']." success.'}]";
			}
		    break;
		}
	    break;
	
	    case "template":
		if($_POST['field'] == "Price"){
		    $sql_1 = "select ListingType from template where Id = '".$_POST['id']."'";
		    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		    $row_1 = mysql_fetch_assoc($result_1);
		
		    if($row_1['ListingType'] == "Chinese" || $row_1['ListingType'] == "Dutch"){
			$sql = "update template set BuyItNowPrice = '".$_POST['value']."' where Id = '".$_POST['id']."'";
			$result = mysql_query($sql, eBayListing::$database_connect);
			echo "[{success: true, msg: 'update ".$_POST['field']." success.'}]";
		    }elseif($row_1['ListingType'] == "StoresFixedPrice" || $row_1['ListingType'] == "FixedPriceItem"){
			$sql = "update template set StartPrice = '".$_POST['value']."' where Id = '".$_POST['id']."'";
			$result = mysql_query($sql, eBayListing::$database_connect);
			echo "[{success: true, msg: 'update ".$_POST['field']." success.'}]";
		    }else{
			echo "[{success: false, msg: 'no listint type.'}]";
		    }
		}else{
		    $sql = "update template set ".$_POST['field']." = '".$_POST['value']."' where Id = '".$_POST['id']."'";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		    echo "[{success: true, msg: 'update ".$_POST['field']." success.'}]";
		}
	    break;
	}
	return 1;
    }
    
    public function updateActiveItem(){
			
	if($_POST['ListingType'] == "FixedPriceItem" || $_POST['ListingType'] == "StoresFixedPrice"){
	    $_POST['StartPrice'] = $_POST['BuyItNowPrice'];
	    $_POST['BuyItNowPrice'] = 0;
	}
	
	if($_POST['ListingType'] == "Chinese"){
	    $_POST['Quantity'] = 1;   
	}
	
	$id = $_GET['item_id'];
	//StartTime,EndTime
	//$PaymentMethods = ($_POST['PayPalPayment'] == 1)?'PayPal':'';
	$sql = "update items set 
	BuyItNowPrice='".$_POST['BuyItNowPrice']."',Country='CN',Currency='".$_POST['Currency']."',
	Description='".htmlentities($_POST['Description'])."',DispatchTimeMax='".$_POST['DispatchTimeMax']."',
	ListingDuration='".$_POST['ListingDuration']."',ListingType='".$_POST['ListingType']."',Location='".$_POST['Location']."',PaymentMethods='PayPal',
	PayPalEmailAddress='".$_POST['PayPalEmailAddress']."',PostalCode='".$_POST['PostalCode']."',
	PrimaryCategoryCategoryID='".$_POST['PrimaryCategoryCategoryID']."',PrimaryCategoryCategoryName='".$_POST['PrimaryCategoryCategoryName']."',
	SecondaryCategoryCategoryID='".$_POST['SecondaryCategoryCategoryID']."',SecondaryCategoryCategoryName='".$_POST['SecondaryCategoryCategoryName']."',
	Quantity='".@$_POST['Quantity']."',ReservePrice='".@$_POST['ReservePrice']."',
	Site='".$_POST['Site']."',SKU='".$_POST['SKU']."',StartPrice='".$_POST['StartPrice']."',StoreCategory2ID='".$_POST['StoreCategory2ID']."',StoreCategory2Name='".$_POST['StoreCategory2Name']."',
	StoreCategoryID='".$_POST['StoreCategoryID']."',StoreCategoryName='".$_POST['StoreCategoryName']."',SubTitle='".$_POST['SubTitle']."',
	Title='".mysql_real_escape_string($_POST['Title'])."',BoldTitle='".(empty($_POST['BoldTitle'])?0:1)."',
	Border='".(empty($_POST['Border'])?0:1)."',Featured='".(empty($_POST['Featured'])?0:1)."',Highlight='".(empty($_POST['Highlight'])?0:1)."',
	HomePageFeatured='".(empty($_POST['HomePageFeatured'])?0:1)."',GalleryTypeFeatured='".(empty($_POST['GalleryTypeFeatured'])?0:1)."',GalleryTypePlus='".(empty($_POST['GalleryTypePlus'])?0:1)."',
	InsuranceOption='".$_POST['InsuranceOption']."',InsuranceFee='".$_POST['InsuranceFee']."',
	InternationalInsurance='".$_POST['InternationalInsurance']."',InternationalInsuranceFee='".$_POST['InternationalInsuranceFee']."',
	accountId='".$this->account_id."',Status=3 where Id = '".$id."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	
	//echo $sql;
	//exit;
	
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
	
	session_start();
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
	    echo '{success: true, msg: "Revise Item '.$id.' Success!"}';
	    $this->log("item", "revise item ".$id." success.");
	}else{
	    echo '{success: false,
		    errors: {message: "can\'t update, please notice admin."}
		}';
	    $this->log("item", "revise item ".$id." failure.", "error");
	}
    }
    
    public function updateMultiActiveItem(){
	//print_r($_POST);
	$ids = explode(',', $_GET['item_id']);
	$where = " where ";
	foreach($ids as $id){
	    $where .= "Id = ".$id." or ";
	}
	$where = substr($where, 0, -4);
	
	$update = "update items set ";
	if(!empty($_POST['BuyItNowPrice']) && $_POST['BuyItNowPrice'] != 'Multi Value'){
	    $update .= "BuyItNowPrice = '".$_POST['BuyItNowPrice']."',";
	}
	
	if(!empty($_POST['Currency']) && $_POST['Currency'] != 'Multi Value'){
	    $update .= "Currency = '".$_POST['Currency']."',";
	}
	
	if(!empty($_POST['Description']) && $_POST['Description'] != 'Multi Value'){
	    $update .= "Description = '".htmlentities($_POST['Description'])."',";
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
	
	if(!empty($_POST['StartPrice']) && $_POST['StartPrice'] != 'Multi Value'){
	    $update .= "StartPrice = '".$_POST['StartPrice']."',";
	}
	
	if(!empty($_POST['StoreCategory2ID']) && $_POST['StoreCategory2ID'] != 'Multi Value'){
	    $update .= "StoreCategory2ID = '".$_POST['StoreCategory2ID']."',";
	}
	
	if(!empty($_POST['StoreCategory2Name']) && $_POST['StoreCategory2Name'] != 'Multi Value'){
	    $update .= "StoreCategory2Name = '".$_POST['StoreCategory2Name']."',";
	}
	
	if(!empty($_POST['SubTitle']) && $_POST['SubTitle'] != 'Multi Value'){
	    $update .= "SubTitle = '".mysql_real_escape_string($_POST['SubTitle'])."',";
	}
	
	if(!empty($_POST['Title']) && $_POST['Title'] != 'Multi Value'){
	    $update .= "Title = '".mysql_real_escape_string($_POST['Title'])."',";
	}
	
	if(!empty($_POST['SKU']) && $_POST['SKU'] != 'Multi Value'){
	    $update .= "SKU = '".$_POST['SKU']."',";
	}
	
	if(!empty($_POST['Border'])){
	    $update .= "Border = '".$_POST['Border']."',";
	}
	
	$update .= "Status=3,";
	$update = substr($update, 0, -1);
	$sql = $update . $where;
	//echo $sql."\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	//echo $result;
	//print_r($_POST);
	$where = " where ";
	foreach($ids as $id){
	    $where .= "ItemID = ".$id." or ";
	}
	$where = substr($where, 0, -4);
	
	if(!empty($_POST['picture_1'])){
	    $sql_1 = "delete from picture_url ".$where;
	    //echo $sql_1."\n";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    
	    foreach($ids as $id){
		$i = 1;
		while(!empty($_POST['picture_'.$i])){
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
	
	session_start();
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
    
    public function updateSoldItem(){
	if($_POST['ListingType'] == "FixedPriceItem" || $_POST['ListingType'] == "StoresFixedPrice"){
	    $_POST['StartPrice'] = $_POST['BuyItNowPrice'];
	    $_POST['BuyItNowPrice'] = 0;
	}
	
	if($_POST['ListingType'] == "Chinese"){
	    $_POST['Quantity'] = 1;   
	}
	
	$id = $_GET['item_id'];
	//StartTime,EndTime
	//$PaymentMethods = ($_POST['PayPalPayment'] == 1)?'PayPal':'';
	$sql = "update items set 
	BuyItNowPrice='".$_POST['BuyItNowPrice']."',Country='CN',Currency='".$_POST['Currency']."',
	Description='".htmlentities($_POST['Description'])."',DispatchTimeMax='".$_POST['DispatchTimeMax']."',
	ListingDuration='".$_POST['ListingDuration']."',ListingType='".$_POST['ListingType']."',Location='".$_POST['Location']."',PaymentMethods='PayPal',
	PayPalEmailAddress='".$_POST['PayPalEmailAddress']."',PostalCode='".$_POST['PostalCode']."',
	PrimaryCategoryCategoryID='".$_POST['PrimaryCategoryCategoryID']."',PrimaryCategoryCategoryName='".$_POST['PrimaryCategoryCategoryName']."',
	SecondaryCategoryCategoryID='".$_POST['SecondaryCategoryCategoryID']."',SecondaryCategoryCategoryName='".$_POST['SecondaryCategoryCategoryName']."',
	Quantity='".@$_POST['Quantity']."',ReservePrice='".@$_POST['ReservePrice']."',
	Site='".$_POST['Site']."',SKU='".$_POST['SKU']."',StartPrice='".$_POST['StartPrice']."',StoreCategory2ID='".$_POST['StoreCategory2ID']."',StoreCategory2Name='".$_POST['StoreCategory2Name']."',
	StoreCategoryID='".$_POST['StoreCategoryID']."',StoreCategoryName='".$_POST['StoreCategoryName']."',SubTitle='".$_POST['SubTitle']."',
	Title='".mysql_real_escape_string($_POST['Title'])."',BoldTitle='".(empty($_POST['BoldTitle'])?0:1)."',
	Border='".(empty($_POST['Border'])?0:1)."',Featured='".(empty($_POST['Featured'])?0:1)."',Highlight='".(empty($_POST['Highlight'])?0:1)."',
	HomePageFeatured='".(empty($_POST['HomePageFeatured'])?0:1)."',GalleryTypeFeatured='".(empty($_POST['GalleryTypeFeatured'])?0:1)."',GalleryTypePlus='".(empty($_POST['GalleryTypePlus'])?0:1)."',
	InsuranceOption='".$_POST['InsuranceOption']."',InsuranceFee='".$_POST['InsuranceFee']."',
	InternationalInsurance='".$_POST['InternationalInsurance']."',InternationalInsuranceFee='".$_POST['InternationalInsuranceFee']."',
	accountId='".$this->account_id."',Status=4 where Id = '".$id."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	
	//echo $sql;
	//exit;
	
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
	
	session_start();
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
	    echo '{success: true, msg: "Relist Item '.$id.' Success!"}';
	    $this->log("item", "relist item ".$id." success.");
	}else{
	    echo '{success: false,
		    errors: {message: "can\'t update, please notice admin."}
		}';
	    $this->log("item", "relist item ".$id." failure.", "error");
	}
    }
    
    public function updateMultiSoldItem(){
	//print_r($_POST);
	$ids = explode(',', $_GET['item_id']);
	$where = " where ";
	foreach($ids as $id){
	    $where .= "Id = ".$id." or ";
	}
	$where = substr($where, 0, -4);
	
	$update = "update items set ";
	if(!empty($_POST['BuyItNowPrice']) && $_POST['BuyItNowPrice'] != 'Multi Value'){
	    $update .= "BuyItNowPrice = '".$_POST['BuyItNowPrice']."',";
	}
	
	if(!empty($_POST['Currency']) && $_POST['Currency'] != 'Multi Value'){
	    $update .= "Currency = '".$_POST['Currency']."',";
	}
	
	if(!empty($_POST['Description']) && $_POST['Description'] != 'Multi Value'){
	    $update .= "Description = '".htmlentities($_POST['Description'])."',";
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
	
	if(!empty($_POST['StartPrice']) && $_POST['StartPrice'] != 'Multi Value'){
	    $update .= "StartPrice = '".$_POST['StartPrice']."',";
	}
	
	if(!empty($_POST['StoreCategory2ID']) && $_POST['StoreCategory2ID'] != 'Multi Value'){
	    $update .= "StoreCategory2ID = '".$_POST['StoreCategory2ID']."',";
	}
	
	if(!empty($_POST['StoreCategory2Name']) && $_POST['StoreCategory2Name'] != 'Multi Value'){
	    $update .= "StoreCategory2Name = '".$_POST['StoreCategory2Name']."',";
	}
	
	if(!empty($_POST['SubTitle']) && $_POST['SubTitle'] != 'Multi Value'){
	    $update .= "SubTitle = '".mysql_real_escape_string($_POST['SubTitle'])."',";
	}
	
	if(!empty($_POST['Title']) && $_POST['Title'] != 'Multi Value'){
	    $update .= "Title = '".mysql_real_escape_string($_POST['Title'])."',";
	}
	
	if(!empty($_POST['SKU']) && $_POST['SKU'] != 'Multi Value'){
	    $update .= "SKU = '".$_POST['SKU']."',";
	}
	
	if(!empty($_POST['Border'])){
	    $update .= "Border = '".$_POST['Border']."',";
	}
	
	$update .= "Status=4,";
	$update = substr($update, 0, -1);
	$sql = $update . $where;
	//echo $sql."\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	//echo $result;
	//print_r($_POST);
	$where = " where ";
	foreach($ids as $id){
	    $where .= "ItemID = ".$id." or ";
	}
	$where = substr($where, 0, -4);
	
	if(!empty($_POST['picture_1'])){
	    $sql_1 = "delete from picture_url ".$where;
	    //echo $sql_1."\n";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    
	    foreach($ids as $id){
		$i = 1;
		while(!empty($_POST['picture_'.$i])){
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
	
	session_start();
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
	    echo '{success: true, msg: "Relist Item '.$_GET['item_id'].' Success!"}';
	    $this->log("item", "relist multi item ".$_GET['item_id']." success.");
	}else{
	    echo '{success: false,
		    errors: {message: "can\'t update, please notice admin."}
		}';
	    $this->log("item", "relist multi item ".$_GET['item_id']." failure.", "error");
	}
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
	$twoBefore = date("Y-m-d H:i:s", time() - (2 * 60));
	
	//$sql = "select Id from items where Status = 1 and ScheduleTime between '".$twoBefore."' and now()";
	$sql = "select Id,accountId from items where Status = 1 and accountId = 1";
	
	$result = mysql_query($sql);
	while($row = mysql_fetch_assoc($result)){
	    $this->setAccount($row['accountId']);
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
		
		$sql_01 = "select footer from account_footer where accountId = '".$row_1['accountId']."'";
		$result_01 = mysql_query($sql_01);
		$row_01 = mysql_fetch_assoc($result_01);
		
		$row_1['Description'] = str_replace(array("%title%", "%sku%", "%picture-1%", "%picture-2%", "%picture-3%", "%picture-4%", "%picture-5%", "%description%"),
						    array($row_1['Title'], $row_1['SKU'], '<img src="'.$row_0['picture_1'].'" />', '<img src="'.$row_0['picture_2'].'" />', '<img src="'.$row_0['picture_3'].'" />', '<img src="'.$row_0['picture_4'].'" />', '<img src="'.$row_0['picture_5'].'" />', html_entity_decode($row_1['Description'])), $row_01['footer']);
	    }else{
		$row_1['Description'] = html_entity_decode($row_1['Description']);
	    }
 
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
	    
	    if(!empty($item['BuyItNowPrice']) && $item['BuyItNowPrice'] != 0){
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
			    $ShipToLocations = array_merge($ShipToLocations, explode(',', $i['ShipToLocation']));
			    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShipToLocation'] = explode(',', $i['ShipToLocation']);
			}else{
			    //echo "test2";
			    array_push($ShipToLocations, $i['ShipToLocation']);
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
	    
	    if(!empty($results->faultcode)){
		$sql_0 = "update items set Status = 1 where Id = '".$item['Id']."'";
		$result_0 = mysql_query($sql_0);
		$this->log("upload", $item['Id'] ." " . $results->faultcode . ": " . $results->faultstring, "error");

	    }
	    
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
		    $this->log("upload", $item['Id'] ." " . $temp, "error");
		}else{
		    echo $results->Errors->ShortMessage." : ";
		    echo $results->Errors->LongMessage."<br>";
		    $this->log("upload", $item['Id'] ." " . $results->Errors->LongMessage, "error");
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
		    $this->log("upload", $sql);
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
		$this->log("upload", $sql);
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
		
		$sql_01 = "select footer from account_footer where accountId = '".$row_1['accountId']."'";
		$result_01 = mysql_query($sql_01);
		$row_01 = mysql_fetch_assoc($result_01);
		
		$row_1['Description'] = str_replace(array("%title%", "%sku%", "%picture-1%", "%picture-2%", "%picture-3%", "%picture-4%", "%picture-5%", "%description%"),
						    array($row_1['Title'], $row_1['SKU'], '<img src="'.$row_0['picture_1'].'" />', '<img src="'.$row_0['picture_2'].'" />', '<img src="'.$row_0['picture_3'].'" />', '<img src="'.$row_0['picture_4'].'" />', '<img src="'.$row_0['picture_5'].'" />', html_entity_decode($row_1['Description'])), $row_01['footer']);
	    }else{
		$row_1['Description'] = html_entity_decode($row_1['Description']);
	    }
	    
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
	    
	    if(!empty($item['BuyItNowPrice']) && $item['BuyItNowPrice'] != 0){
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
			    $ShipToLocations = array_merge($ShipToLocations, explode(',', $i['ShipToLocation']));
			    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShipToLocation'] = explode(',', $i['ShipToLocation']);
			}else{
			    //echo "test2";
			    array_push($ShipToLocations, $i['ShipToLocation']);
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
	    if(!empty($results->faultcode)){
		$sql_0 = "update items set Status = 3 where Id = '".$item['Id']."'";
		$result_0 = mysql_query($sql_0);
		$this->log("revise", $item['Id'] ." " . $results->faultcode . ": " . $results->faultstring, "error");

	    }
	    
	    if(!empty($results->Errors)){
		$sql_0 = "update items set Status = 3 where Id = '".$item['Id']."'";
		$result_0 = mysql_query($sql_0);
		
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
		    $this->log("revise", $sql);
		}
	    }elseif(!empty($results->ItemID)){
		$sql = "update items set Status='2' where Id = '".$item['Id']."'";
		echo $sql;
		$result = mysql_query($sql);
		$this->log("revise", $sql);
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
		
		$sql_01 = "select footer from account_footer where accountId = '".$row_1['accountId']."'";
		$result_01 = mysql_query($sql_01);
		$row_01 = mysql_fetch_assoc($result_01);
		
		$row_1['Description'] = str_replace(array("%title%", "%sku%", "%picture-1%", "%picture-2%", "%picture-3%", "%picture-4%", "%picture-5%", "%description%"),
						    array($row_1['Title'], $row_1['SKU'], '<img src="'.$row_0['picture_1'].'" />', '<img src="'.$row_0['picture_2'].'" />', '<img src="'.$row_0['picture_3'].'" />', '<img src="'.$row_0['picture_4'].'" />', '<img src="'.$row_0['picture_5'].'" />', html_entity_decode($row_1['Description'])), $row_01['footer']);
	    }else{
		$row_1['Description'] = html_entity_decode($row_1['Description']);
	    }
	    
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
	    
	    if(!empty($item['BuyItNowPrice']) && $item['BuyItNowPrice'] != 0){
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
			    $ShipToLocations = array_merge($ShipToLocations, explode(',', $i['ShipToLocation']));
			    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShipToLocation'] = explode(',', $i['ShipToLocation']);
			}else{
			    //echo "test2";
			    array_push($ShipToLocations, $i['ShipToLocation']);
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
	    
	    if(!empty($results->faultcode)){
		$sql_0 = "update items set Status = 4 where Id = '".$item['Id']."'";
		$result_0 = mysql_query($sql_0);
		$this->log("relist", $item['Id'] ." " . $results->faultcode . ": " . $results->faultstring, "error");

	    }
	    
	    if(!empty($results->Errors)){
		$sql_0 = "update items set Status = 4 where Id = '".$item['Id']."'";
		$result_0 = mysql_query($sql_0);
		
		if(is_array($results->Errors)){
		    $temp = '';
		    foreach($results->Errors as $error){
			echo $error->ShortMessage." : ";
			echo $error->LongMessage."<br>";
			$temp .= $error->LongMessage;
		    }
		    $this->log("relist", $item['Id'] ." " . $temp, "error");
		}else{
		    echo $results->Errors->ShortMessage." : ";
		    echo $results->Errors->LongMessage."<br>";
		    $this->log("relist", $item['Id'] ." " . $results->Errors->LongMessage, "error");
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
		    $this->log("relist", $sql);
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
		$this->log("relist", $sql);
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
    
    //-------------------------   Get Seller Listing ------------------------------------------------------
    /*
    	Status
	0 : ready
	1 : schedule
	2 : selling
	3 : revise
	4 : relist
	5 : unsold
	6:  sold
	
	10: uploading
	11: reviseing
	12: relisting
    */
    private function checkItem($itemId){
	$sql = "select count(*) as count from items where ItemID = '".$itemId."'";
	//echo $sql;
	//echo "<br>\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	return $row['count'];
    }
    
    private function eBayInsertItem($item, $userId){
	switch($item->SellingStatus->ListingStatus){
	    case "Active":
		$Status = 2;
	    break;
	
	    case "Completed":
		if($item->SellingStatus->QuantitySold > 0){
		    $Status = 6;
		}else{
		    $Status = 5;
		}
	    break;
	
	    case "Ended":
		if($item->SellingStatus->QuantitySold > 0){
		    $Status = 6;
		}else{
		    $Status = 5;
		}
	    break;
	}
	
	switch($item->PictureDetails->GalleryType){
	    case "Featured":
		$GalleryTypeFeatured = 1;
		$GalleryTypeGallery = 0;
		$GalleryTypePlus = 0;
	    break;
	    
	    case "Gallery":
		$GalleryTypeGallery = 1;
		$GalleryTypeFeatured = 0;
		$GalleryTypePlus = 0;
	    break;
	
	    case "Plus":
		$GalleryTypePlus = 1;
		$GalleryTypeFeatured = 0;
		$GalleryTypeGallery = 0;
	    break;
	}
	
	$PrimaryCategoryCategoryName = $this->getCategoryPathById($this->getSiteIdByName($item->Site), $item->PrimaryCategory->CategoryID);
	$SecondaryCategoryCategoryName = $this->getCategoryPathById($this->getSiteIdByName($item->Site), $item->SecondaryCategory->CategoryID);
	
	$sql = "insert into items (ItemID,AutoPay,BuyItNowPrice,Country,Currency,Description,DispatchTimeMax,StartTime,
	EndTime,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,
	ReturnPolicyDescription,ReturnPolicyRefundOption,ReturnPolicyReturnsAcceptedOption,ReturnPolicyReturnsWithinOption,ReturnPolicyShippingCostPaidByOption,
	CurrentPrice,QuantitySold,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ListingStatus,ShippingType,Site,SKU,StartPrice,StoreCategory2ID,StoreCategory2Name,
	StoreCategoryID,StoreCategoryName,Title,UserID,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,Status) values ('".mysql_escape_string($item->ItemID)."','".mysql_escape_string($item->AutoPay)."',
	'".mysql_escape_string($item->BuyItNowPrice)."','".mysql_escape_string($item->Country)."','".mysql_escape_string($item->Currency)."',
	'".mysql_escape_string($item->Description)."','".mysql_escape_string($item->DispatchTimeMax)."','".mysql_escape_string($item->ListingDetails->StartTime)."',
	'".mysql_escape_string($item->ListingDetails->EndTime)."','".mysql_escape_string($item->ListingDuration)."','".mysql_escape_string($item->ListingType)."',
	'".mysql_escape_string($item->Location)."','".mysql_escape_string($item->PaymentMethods)."','".mysql_escape_string($item->PayPalEmailAddress)."',
	'".mysql_escape_string($item->PostalCode)."','".mysql_escape_string($item->PrimaryCategory->CategoryID)."','".$PrimaryCategoryCategoryName."','".mysql_escape_string($item->Quantity)."',
	'".mysql_escape_string($item->ReturnPolicy->Description)."','".mysql_escape_string($item->ReturnPolicy->RefundOption)."','".mysql_escape_string($item->ReturnPolicy->ReturnsAcceptedOption)."','".mysql_escape_string($item->ReturnPolicy->ReturnsWithinOption)."','".mysql_escape_string($item->ReturnPolicy->ShippingCostPaidByOption)."',
	'".mysql_escape_string($item->SellingStatus->CurrentPrice)."','".mysql_escape_string($item->SellingStatus->QuantitySold)."',
	'".mysql_escape_string($item->SecondaryCategory->CategoryID)."','".mysql_escape_string($SecondaryCategoryCategoryName)."',
	'".mysql_escape_string($item->SellingStatus->ListingStatus)."','".mysql_escape_string($item->ShippingDetails->ShippingType)."','".mysql_escape_string($item->Site)."',
	'".mysql_escape_string($item->SKU)."','".mysql_escape_string($item->StartPrice)."','".mysql_escape_string($item->Storefront->StoreCategory2ID)."','',
	'".mysql_escape_string($item->Storefront->StoreCategoryID)."','','".mysql_escape_string($item->Title)."','".mysql_escape_string($userId)."',".$GalleryTypeFeatured.",".$GalleryTypeGallery.",".$GalleryTypePlus.",'".$Status."')";
	//echo $sql;
	//echo "\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	
	return mysql_insert_id(eBayListing::$database_connect);
    }
    
    private function eBayUpdateItem($item, $userId){
	
	switch($item->SellingStatus->ListingStatus){
	    case "Active":
		$Status = 2;
	    break;
	
	    case "Completed":
		if($item->SellingStatus->QuantitySold > 0){
		    $Status = 6;
		}else{
		    $Status = 5;
		}
	    break;
	
	    case "Ended":
		if($item->SellingStatus->QuantitySold > 0){
		    $Status = 6;
		}else{
		    $Status = 5;
		}
	    break;
	}
	
	switch($item->PictureDetails->GalleryType){
	    case "Featured":
		$GalleryTypeFeatured = 1;
		$GalleryTypeGallery = 0;
		$GalleryTypePlus = 0;
	    break;
	    
	    case "Gallery":
		$GalleryTypeGallery = 1;
		$GalleryTypeFeatured = 0;
		$GalleryTypePlus = 0;
	    break;
	
	    case "Plus":
		$GalleryTypePlus = 1;
		$GalleryTypeFeatured = 0;
		$GalleryTypeGallery = 0;
	    break;
	}
	
	$sql = "update items set AutoPay='".mysql_escape_string($item->AutoPay)."',
	BuyItNowPrice='".mysql_escape_string($item->BuyItNowPrice)."',Country='".mysql_escape_string($item->Country)."',
	Currency='".mysql_escape_string($item->Currency)."',Description='".mysql_escape_string($item->Description)."',
	DispatchTimeMax='".mysql_escape_string($item->DispatchTimeMax)."',StartTime='".mysql_escape_string($item->ListingDetails->StartTime)."',
	EndTime='".mysql_escape_string($item->ListingDetails->EndTime)."',ListingDuration='".mysql_escape_string($item->ListingDuration)."',
	ListingType='".mysql_escape_string($item->ListingType)."',Location='".mysql_escape_string($item->Location)."',
	PaymentMethods='".mysql_escape_string($item->PaymentMethods)."',PayPalEmailAddress='".mysql_escape_string($item->PayPalEmailAddress)."',
	PostalCode='".mysql_escape_string($item->PostalCode)."',CategoryID='".mysql_escape_string($item->PrimaryCategory->CategoryID)."',
	CategoryName='".mysql_escape_string($item->PrimaryCategory->CategoryName)."',Quantity='".mysql_escape_string($item->Quantity)."',
	ReturnPolicyDescription='".mysql_escape_string($item->ReturnPolicy->Description)."',ReturnPolicyRefundOption='".mysql_escape_string($item->ReturnPolicy->RefundOption)."',
	ReturnPolicyReturnsAcceptedOption='".mysql_escape_string($item->ReturnPolicy->ReturnsAcceptedOption)."',ReturnPolicyReturnsWithinOption='".mysql_escape_string($item->ReturnPolicy->ReturnsWithinOption)."',
	ReturnPolicyShippingCostPaidByOption='".mysql_escape_string($item->ReturnPolicy->ShippingCostPaidByOption)."',
	CurrentPrice='".mysql_escape_string($item->SellingStatus->CurrentPrice)."',QuantitySold='".mysql_escape_string($item->SellingStatus->QuantitySold)."',
	ListingStatus='".mysql_escape_string($item->SellingStatus->ListingStatus)."',ShippingType='".mysql_escape_string($item->ShippingDetails->ShippingType)."',
	Site='".mysql_escape_string($item->Site)."',SKU='".mysql_escape_string($item->SKU)."',
	StartPrice='".mysql_escape_string($item->StartPrice)."',StoreCategory2ID='".mysql_escape_string($item->Storefront->StoreCategory2ID)."',
	StoreCategoryID='".mysql_escape_string($item->Storefront->StoreCategoryID)."',Title='".mysql_escape_string($item->Title)."',
	UserID='".mysql_escape_string($userId)."',Status='".$Status."'',
	GalleryTypeFeatured=".$GalleryTypeFeatured.",GalleryTypeGallery=".$GalleryTypeGallery.",GalleryTypePlus=".$GalleryTypePlus." where ItemID = '".$item->ItemID."'";
	echo $sql;
	echo "<br>";
	
	//$sql = "update items set CurrentPrice='".$item->SellingStatus->CurrentPrice."',QuantitySold='".$item->SellingStatus->QuantitySold."',ListingStatus='".$item->SellingStatus->ListingStatus.",Status='".$Status."'' 
	//where ItemID = '".$item->ItemID."'";
	//echo $sql;
	//echo "<br>\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	
	$sql = "select Id from items where ItemID = '".$item->ItemID."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	return $row['Id'];
    }
    
    private function deleteShippingServiceOptions($itemId){
	$sql = "delete from shipping_service_options where ItemID = '".$itemId."'";
	//echo $sql;
	//echo "<br>\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	return $result;
    }
    
    private function insertShippingServiceOptions($itemID, $shippingServiceOptions){
	$sql = "insert into shipping_service_options (ItemID,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,FreeShipping) values 
	('".$itemID."','".$shippingServiceOptions->ShippingService."','".$shippingServiceOptions->ShippingServiceCost."','".$shippingServiceOptions->ShippingServiceAdditionalCost."','".$shippingServiceOptions->ShippingServicePriority."','".$shippingServiceOptions->FreeShipping."')";
	//echo $sql;
	//echo "<br>\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	return $result;
    }
    private function updateShippingServiceOptions($itemID, $shippingServiceOptions){
	$sql = "update shipping_service_options set ShippingService='".$shippingServiceOptions->ShippingService."',
	ShippingServiceCost='".$shippingServiceOptions->ShippingServiceCost."',
	ShippingServiceAdditionalCost='".$shippingServiceOptions->ShippingServiceAdditionalCost."',
	ShippingServicePriority='".$shippingServiceOptions->ShippingServicePriority."',
	FreeShipping='".$shippingServiceOptions->FreeShipping."' where ItemID='".$itemID."'";
	//echo $sql;
	//echo "<br>\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	return $result;
    }
    
    private function deleteInternationalShippingServiceOption($itemId){
	$sql = "delete from international_shipping_service_option where ItemID = '".$itemId."'";
	//echo $sql;
	//echo "<br>\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
    }
    
    private function insertInternationalShippingServiceOption($itemID, $internationalShippingServiceOption){
	//print_r($internationalShippingServiceOption);
	$ShipToLocation = "";
	if(is_array($internationalShippingServiceOption->ShipToLocation)){
	    foreach($internationalShippingServiceOption->ShipToLocation as $STL){
		$ShipToLocation .= $STL . ",";
	    }
	    $ShipToLocation = substr($ShipToLocation, 0, -1);
	}else{
	    $ShipToLocation = $internationalShippingServiceOption->ShipToLocation;
	}
	$sql = "insert into international_shipping_service_option (ItemID,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) values 
	('".$itemID."','".$internationalShippingServiceOption->ShippingService."','".$internationalShippingServiceOption->ShippingServiceCost."','".$internationalShippingServiceOption->ShippingServiceAdditionalCost."','".$internationalShippingServiceOption->ShippingServicePriority."','".$ShipToLocation."')";
	//echo $sql;
	//echo "<br>\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
    }
    
    private function updateInternationalShippingServiceOption($itemID, $internationalShippingServiceOption){
	$ShipToLocation = "";
	if(is_array($internationalShippingServiceOption->ShipToLocation)){
	    foreach($internationalShippingServiceOption->ShipToLocation as $STL){
		$ShipToLocation .= $STL . ",";
	    }
	    $ShipToLocation = substr($ShipToLocation, 0, -1);
	}else{
	    $ShipToLocation = $internationalShippingServiceOption->ShipToLocation;
	}
	$sql = "update international_shipping_service_option set ShippingService='".$internationalShippingServiceOption->ShippingService."',
	ShippingServiceCost='".$internationalShippingServiceOption->ShippingServiceCost."',
	ShippingServiceAdditionalCost='".$internationalShippingServiceOption->ShippingServiceAdditionalCost."',
	ShippingServicePriority='".$internationalShippingServiceOption->ShippingServicePriority."',
	ShipToLocation='".$ShipToLocation."' where ItemID = '".$itemID."'";
	//echo $sql;
	//echo "<br>\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
    }
    
    private function insertPictureUrl($itemID, $PictureURL){
	if(is_array($PictureURL)){
	    foreach($PictureURL as $url){
		$sql = "insert into picture_url (ItemID,url) values ('".$itemID."', '".$url."')";
		$result = mysql_query($sql, eBayListing::$database_connect);
	    }
	}else{
	    $sql = "insert into picture_url (ItemID,url) values ('".$itemID."', '".$PictureURL."')";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	}
    }
    
    private function updatePictureUrl($itemID, $PictureURL){
	if(is_array($PictureURL)){
	    foreach($PictureURL as $url){
		$sql = "update picture_url set url='".$url."' where ItemID = '".$itemID."'";
		$result = mysql_query($sql, eBayListing::$database_connect);
	    }
	}else{
	    $sql = "update picture_url set url='".$PictureURL."' where ItemID = '".$itemID."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	}
    }
    //http://127.0.0.1:6666/eBayBO/eBaylisting/service.php?action=getSellerList&EndTimeFrom=2009-05-29&EndTimeTo=2009-05-30
    public function getSellerList(){
	global $argv;
	if(!empty($argv[2])){
	    if($argv[2] == "Start"){
		if(!empty($argv[3]) && !empty($argv[4])){
		    $StartTimeFrom  = $argv[3];
		    $StartTimeTo    = $argv[4];
		}else{
		    $StartTimeFrom  = date("Y-m-d H:i:s", time() - (12 * 60 * 60));
		    $StartTimeTo    = date("Y-m-d H:i:s", time() - (8 * 60 * 60));
		}
	    }elseif($argv[2] == "End"){
		if(!empty($argv[3]) && !empty($argv[4])){
		    $EndTimeFrom = $argv[3];
		    $EndTimeTo   = $argv[4];
		}else{
		    $EndTimeFrom = date("Y-m-d H:i:s", time() - (12 * 60 * 60));
		    $EndTimeTo   = date("Y-m-d H:i:s", time() - (8 * 60 * 60));
		}
	    }
	}
	
	try {
	    $client = new eBaySOAP($this->session);

	    $Version = '607';
	    $DetailLevel = "ReturnAll";
	    $Pagination = array("EntriesPerPage"=> 200,
				"PageNumber"=> 1);
	     
	    //$EndTimeFrom = $_GET['EndTimeFrom'];
	    //$EndTimeTo = $_GET['EndTimeTo'];
	    
	    //$StartTimeFrom = $_GET['StartTimeFrom'];
	    //$StartTimeTo = $_GET['StartTimeTo'];
	    
	    //$params = array('Version' => $Version, 'DetailLevel' => $DetailLevel, 'Pagination' => $Pagination, /*'StartTimeFrom' => $StartTimeFrom, 'StartTimeTo' => $StartTimeTo*/'EndTimeFrom' => $EndTimeFrom, 'EndTimeTo' => $EndTimeTo);
	    //$results = $client->GetSellerList($params);
	    
	    //----------   debug --------------------------------
	    //print "Request: \n".$client->__getLastRequest() ."\n";
	    //print "Response: \n".$client->__getLastResponse()."\n";    
	    $TotalNumberOfPages = 1;
	    
	    for($i=1; $i <= $TotalNumberOfPages; $i++){
		$Pagination = array("EntriesPerPage"=> 200,
				    "PageNumber"=> $i);
		
		if($argv[2] == "Start"){
		    $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel, 'Pagination' => $Pagination, 'StartTimeFrom' => $StartTimeFrom, 'StartTimeTo' => $StartTimeTo);
		}elseif($argv[2] == "End"){
		    $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel, 'Pagination' => $Pagination,'EndTimeFrom' => $EndTimeFrom, 'EndTimeTo' => $EndTimeTo);
		}
		//print_r($params);
		$results = $client->GetSellerList($params);
		//print_r($results);

		//$this->saveFetchData("getSellerList-Request-".date("YmdHis").".xml", $client->__getLastRequest());
		$this->saveFetchData("getSellerList-Response-".date("YmdHis").".xml", $client->__getLastResponse());
		
		$TotalNumberOfPages = $results->PaginationResult->TotalNumberOfPages;
		if($results->PaginationResult->TotalNumberOfPages == 0)
		    return 0;
	    
		if(is_array($results->ItemArray->Item)){
		    foreach($results->ItemArray->Item as $item){
			if($this->checkItem($item->ItemID) == 0){
			    $id = $this->eBayInsertItem($item, $results->Seller->UserID);
			    
			    //ShippingServiceOptions
			    if(is_array($item->ShippingDetails->ShippingServiceOptions)){
				//$this->deleteShippingServiceOptions($id);
				foreach($item->ShippingDetails->ShippingServiceOptions as $shippingServiceOptions){
				    $this->insertShippingServiceOptions($id, $shippingServiceOptions);
				}
			    }else{
				if(!empty($item->ShippingDetails->ShippingServiceOptions->ShippingService)){
				    $this->deleteShippingServiceOptions($id);
				    $this->insertShippingServiceOptions($id, $item->ShippingDetails->ShippingServiceOptions);
				}
			    }
			    
			    //InternationalShippingServiceOption
			    if(is_array($item->ShippingDetails->InternationalShippingServiceOption)){
				//$this->deleteInternationalShippingServiceOption($id);
				foreach($item->ShippingDetails->InternationalShippingServiceOption as $internationalShippingServiceOption){
				    $this->insertInternationalShippingServiceOption($id, $internationalShippingServiceOption);
				}
			    }else{
				if(!empty($item->ShippingDetails->InternationalShippingServiceOption->ShippingService)){
				    $this->deleteInternationalShippingServiceOption($id);
				    $this->insertInternationalShippingServiceOption($id, $item->ShippingDetails->InternationalShippingServiceOption);
				}
			    }
			    
			    $this->insertPictureUrl($id, $item->PictureDetails->PictureURL);
			}else{
			    $id = $this->eBayUpdateItem($item, $results->Seller->UserID);
			    
			    //ShippingServiceOptions
			    if(is_array($item->ShippingDetails->ShippingServiceOptions)){
				foreach($item->ShippingDetails->ShippingServiceOptions as $shippingServiceOptions){
				    $this->updateShippingServiceOptions($id, $shippingServiceOptions);
				}
			    }else{
				if(!empty($item->ShippingDetails->ShippingServiceOptions->ShippingService)){
				    $this->updateShippingServiceOptions($id, $item->ShippingDetails->ShippingServiceOptions);
				}
			    }
			    
			    //InternationalShippingServiceOption
			    if(is_array($item->ShippingDetails->InternationalShippingServiceOption)){
				foreach($item->ShippingDetails->InternationalShippingServiceOption as $internationalShippingServiceOption){
				    $this->updateInternationalShippingServiceOption($id, $internationalShippingServiceOption);
				}
			    }else{
				if(!empty($item->ShippingDetails->InternationalShippingServiceOption->ShippingService)){
				    $this->updateInternationalShippingServiceOption($id, $item->ShippingDetails->InternationalShippingServiceOption);
				}
			    }
			    $this->updatePictureUrl($id, $item->PictureDetails->PictureURL);
			}
			
			
		    }
		}else{
		    if($this->checkItem($results->ItemArray->Item->ItemID) == 0){
		    	$id = $this->eBayInsertItem($results->ItemArray->Item, $results->Seller->UserID);
			
			//ShippingServiceOptions
			if(is_array($results->ItemArray->Item->ShippingDetails->ShippingServiceOptions)){
			    //$this->deleteShippingServiceOptions($id);
			    foreach($results->ItemArray->Item->ShippingDetails->ShippingServiceOptions as $shippingServiceOptions){
				$this->insertShippingServiceOptions($id, $shippingServiceOptions);
			    }
			}else{
			    if(!empty($results->ItemArray->Item->ShippingDetails->ShippingServiceOptions->ShippingService)){
				$this->deleteShippingServiceOptions($id);
				$this->insertShippingServiceOptions($id, $results->ItemArray->Item->ShippingDetails->ShippingServiceOptions);
			    }
			}
			
			//InternationalShippingServiceOption
			if(is_array($results->ItemArray->Item->ShippingDetails->InternationalShippingServiceOption)){
			    //$this->deleteInternationalShippingServiceOption($id);
			    foreach($results->ItemArray->Item->ShippingDetails->InternationalShippingServiceOption as $internationalShippingServiceOption){
				$this->insertInternationalShippingServiceOption($id, $internationalShippingServiceOption);
			    }
			}else{
			    if(!empty($results->ItemArray->Item->ShippingDetails->InternationalShippingServiceOption->ShippingService)){
				$this->deleteInternationalShippingServiceOption($id);
				$this->insertInternationalShippingServiceOption($id, $results->ItemArray->Item->ShippingDetails->InternationalShippingServiceOption);
			    }
			}
			
			$this->insertPictureUrl($id, $results->ItemArray->Item->PictureDetails->PictureURL);
		    }else{
			$id = $this->eBayUpdateItem($results->ItemArray->Item, $results->Seller->UserID);
			
			//ShippingServiceOptions
			if(is_array($results->ItemArray->Item->ShippingDetails->ShippingServiceOptions)){
			    foreach($results->ItemArray->Item->ShippingDetails->ShippingServiceOptions as $shippingServiceOptions){
				$this->updateShippingServiceOptions($id, $shippingServiceOptions);
			    }
			}else{
			    if(!empty($results->ItemArray->Item->ShippingDetails->ShippingServiceOptions->ShippingService)){
				$this->updateShippingServiceOptions($id, $results->ItemArray->Item->ShippingDetails->ShippingServiceOptions);
			    }
			}
			
			//InternationalShippingServiceOption
			if(is_array($results->ItemArray->Item->ShippingDetails->InternationalShippingServiceOption)){
			    foreach($results->ItemArray->Item->ShippingDetails->InternationalShippingServiceOption as $internationalShippingServiceOption){
				$this->updateInternationalShippingServiceOption($id, $internationalShippingServiceOption);
			    }
			}else{
			    if(!empty($results->ItemArray->Item->ShippingDetails->InternationalShippingServiceOption->ShippingService)){
				$this->updateInternationalShippingServiceOption($id, $results->ItemArray->Item->ShippingDetails->InternationalShippingServiceOption);
			    }
			}
			
			$this->updatePictureUrl($id, $results->ItemArray->Item->PictureDetails->PictureURL);
		    }
		    
		}
	    }
	    
        } catch (SOAPFault $f) {
                print $f; // error handling
        }
    }
    
    public function getAllSellerList(){
	$sql = "select id,token from account where status = 1";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
	    /*
	    $sql_1 = "select p.host,p.port from proxy as p left join account_to_proxy as atp on p.id = atp.proxy_id where atp.account_id = '".$row['id']."'";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $row_1 = mysql_fetch_assoc($result_1);
	    $this->session = $this->configEbay($row['token'], $row_1['host'], $row_1['port']);
	    $this->getSellerList();
	    */
	    $this->setAccount($row['id']);
	    $this->configEbay();
	    $this->getSellerList();
	}
    }
    
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
	/*
	ALTER TABLE `account` ADD `role` ENUM( 'user', 'admin' ) NOT NULL DEFAULT 'user' AFTER `password` ;
	*/
	$sql = "select id,role from account where name = '".$_POST['name']."' and password = '".$_POST['password']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	
	//$_SESSION['account_id'] = $row['id'];
	if(!empty($row['id'])){
	    setcookie("account_id", $row['id'], time() + (60 * 60 * 24), '/');
	    setcookie("role", $row['role'], time() + (60 * 60 * 24), '/');
	    echo "{success: true}";
	}else{
	    echo "{success: false}";
	}
    }
    
    public function logout(){
	unset($_COOKIE['account_id']);
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
	$session = $this->configEbay(0);
        try {
		$this->session->token = NULL;
		//print_r($session);
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
	$array = array();
	$type = $_GET['type'];
	
	if($_COOKIE['role'] == "admin"){
	    $sql = "select count(*) as num from log where type = '".$type."'";
	}else{
	    $sql = "select count(*) as num from log where account_id = '".$this->account_id."' and type = '".$type."'";
	}
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$totalCount = $row['num'];
	
	if(empty($_POST['start']) && empty($_POST['limit'])){
	    $_POST['start'] = 0;
	    $_POST['limit'] = 20;
	}
	
	if($_COOKIE['role'] == "admin"){	
	    $sql = "select * from log where type = '".$type."' limit ".$_POST['start'].",".$_POST['limit'];
	}else{
	    $sql = "select * from log where account_id = '".$this->account_id."' and type = '".$type."' limit ".$_POST['start'].",".$_POST['limit'];   
	}
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
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
    
    public function __destruct(){
        mysql_close(eBayListing::$database_connect);
    }
}

if(!empty($argv[2])){
    $service = new eBayListing($argv[2]);
}else{
    $service = new eBayListing();
}
//$service->setAccount(1);
$acton = (!empty($_GET['action'])?$_GET['action']:$argv[1]);
if(in_array($acton, array("getAllSiteShippingServiceDetails", "getAllSiteShippingLocationDetails", "getAllCategory2CS", "getAllAttributesCS"))){
    $service->$acton();
}else{
    //$service->configEbay();
    $service->$acton();
}
?>