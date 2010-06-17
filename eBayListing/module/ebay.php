<?php
require_once '/export/eBayListing/eBaySOAP.php';

class Ebay{
    const LOG_DIR = '/export/eBayListing/log/';
    
    private $startTime;
    private $endTime;
    
    private $env = "production";
    //private $env = "sandbox";
    
    private $session;
    private $site_id; //US 0, UK 3, AU 15, FR 71
    private $account_id;
    
    public function __construct($account_id){
        $this->account_id = $account_id;
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
		//$this->log("system", $row_0['name'] ." close, can't use.", "error");
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
	
	if(!file_exists(self::LOG_DIR.$account_name)){
            mkdir(self::LOG_DIR.$account_name, 0777);
        }
	
	if(!file_exists(self::LOG_DIR.$account_name."/".date("Ymd"))){
            mkdir(self::LOG_DIR.$account_name."/".date("Ymd"), 0777);
        }
	
	file_put_contents(self::LOG_DIR.$account_name."/".date("Ymd")."/".$file_name, $data);
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
    
    
    public function getCategories($categorySiteID = 0){
	global $argv;
	if(!empty($argv[2])){
	    $this->setAccount(1);
	    $this->configEbay();
	    $categorySiteID = $argv[2];
	}
	
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
    
    public function getStoreCategories($userID = 0){
	global $argv;
	if(!empty($argv[2])){
	    $userID = $argv[2];
	    $sql = "select id from account where name = '".$userID."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	
	    $this->setAccount($row['id']);
	    $this->configEbay();
	}else if(!empty($this->account_id)){
	    
	    $sql = "select name from account where id = ".$this->account_id;
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    $userID = $row['name'];
	    
	    $this->configEbay();
	}
	
	try {
                $client = new eBaySOAP($this->session);

                $CategoryStructureOnly = true;
                $Version = '607';
		$UserID = $userID;
		
                $params = array('Version' => $Version, 'CategoryStructureOnly' => $CategoryStructureOnly, 'UserID' => $UserID);
                //print_r($params);
		$results = $client->GetStore($params);
		//print_r($results);
                //----------   debug --------------------------------
                //print "Request: \n".$client->__getLastRequest() ."\n";
                //print "Response: \n".$client->__getLastResponse()."\n";
		$sql = "delete from account_store_categories where AccountId = ".$this->account_id;
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
	global $argv;
	if(!empty($argv[2])){
	    $this->setAccount(1);
	    $this->configEbay($argv[2]);
	}
	
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
	    $this->setAccount(1);
	    $this->configEbay($row['id']);
	    $this->getShippingServiceDetails();
	}
    }
    
    public function getShippingLocationDetails(){
	global $argv;
	if(!empty($argv[2])){
	    $this->setAccount(1);
	    $this->configEbay($argv[2]);
	}
	
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
	    $this->setAccount(1);
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
    public function getCategory2CS($categorySiteID = 0){
	global $argv;
	if(!empty($argv[2])){
	    $this->setAccount(1);
	    $this->configEbay($argv[2]);
	    $categorySiteID = $argv[2];
	}
	
	$sql = "delete from CharacteristicsSets where SiteID = '".$categorySiteID."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
		    
	try {
	    echo $this->site_id;
	    echo "\n";
	    $client = new eBaySOAP($this->session);
	    $Version = '607';
	    $DetailLevel = "ReturnAll";
	 
	    $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel);
	    $results = $client->GetCategory2CS($params);
	    $this->saveFetchData("GetCategory2CS-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
	    foreach ($results->MappedCategoryArray->Category as $category){
		$sql = "insert into CharacteristicsSets (SiteID,CategoryID,Name,AttributeSetID,AttributeSetVersion) values 
		('".$categorySiteID."','".$category->CategoryID."','".$category->CharacteristicsSets->Name."',
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
	    $this->setAccount(1);
	    $this->configEbay($row['id']);
	    $this->getCategory2CS($row['id']);
	}
    }
    
    public function getAttributesCS($categorySiteID = 0){
	global $argv;
	if(!empty($argv[2])){
	    $this->setAccount(1);
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
	    $this->setAccount(1);
	    $this->configEbay($row['id']);
	    $this->getAttributesCS($row['id']);
	}
    }
    
    //------------------------- Seller List -----------------------------------------------------
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
    
    private function getSiteIdByName($name){
	$sql = "select id from site where name = '".$name."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
    	$row = mysql_fetch_assoc($result);
	return $row['id'];
    }
    
    private function checkItem($itemId){
	$sql = "select count(*) as count from items where ItemID = '".$itemId."'";
	//echo $sql;
	//echo "<br>\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	return $row['count'];
    }
    
    private function eBayInsertItem($item, $userId){
	global $nest, $storeNest;
	
	$sql = "select id from account where name = '".$userId."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$accountId = $row['id'];
	
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
	
	$nest = 0;
	$storeNest = 0;
	$PrimaryCategoryCategoryName = $this->getCategoryPathById($this->getSiteIdByName($item->Site), $item->PrimaryCategory->CategoryID);
	$SecondaryCategoryCategoryName = $this->getCategoryPathById($this->getSiteIdByName($item->Site), $item->SecondaryCategory->CategoryID);
	
	$StoreCategoryName = $this->getStoreCategoryPathById($this->account_id, $item->Storefront->StoreCategoryID);
	$StoreCategory2Name = $this->getStoreCategoryPathById($this->account_id, $item->Storefront->StoreCategory2ID);
	
	$sql = "insert into items (ItemID,AutoPay,BuyItNowPrice,Country,Currency,Description,DispatchTimeMax,StartTime,
	EndTime,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,
	ReturnPolicyDescription,ReturnPolicyRefundOption,ReturnPolicyReturnsAcceptedOption,ReturnPolicyReturnsWithinOption,ReturnPolicyShippingCostPaidByOption,
	CurrentPrice,QuantitySold,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ListingStatus,ShippingType,Site,SKU,StartPrice,StoreCategory2ID,StoreCategory2Name,
	StoreCategoryID,StoreCategoryName,Title,UserID,accountId,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,GalleryURL,Status) values ('".mysql_escape_string($item->ItemID)."','".mysql_escape_string($item->AutoPay)."',
	'".mysql_escape_string($item->BuyItNowPrice)."','".mysql_escape_string($item->Country)."','".mysql_escape_string($item->Currency)."',
	'".htmlentities($item->Description, ENT_QUOTES)."','".mysql_escape_string($item->DispatchTimeMax)."','".mysql_escape_string($item->ListingDetails->StartTime)."',
	'".mysql_escape_string($item->ListingDetails->EndTime)."','".mysql_escape_string($item->ListingDuration)."','".mysql_escape_string($item->ListingType)."',
	'".mysql_escape_string($item->Location)."','".mysql_escape_string($item->PaymentMethods)."','".mysql_escape_string($item->PayPalEmailAddress)."',
	'".mysql_escape_string($item->PostalCode)."','".mysql_escape_string($item->PrimaryCategory->CategoryID)."','".$PrimaryCategoryCategoryName."','".mysql_escape_string($item->Quantity)."',
	'".mysql_escape_string($item->ReturnPolicy->Description)."','".mysql_escape_string($item->ReturnPolicy->RefundOption)."','".mysql_escape_string($item->ReturnPolicy->ReturnsAcceptedOption)."','".mysql_escape_string($item->ReturnPolicy->ReturnsWithinOption)."','".mysql_escape_string($item->ReturnPolicy->ShippingCostPaidByOption)."',
	'".mysql_escape_string($item->SellingStatus->CurrentPrice)."','".mysql_escape_string($item->SellingStatus->QuantitySold)."',
	'".mysql_escape_string($item->SecondaryCategory->CategoryID)."','".mysql_escape_string($SecondaryCategoryCategoryName)."',
	'".mysql_escape_string($item->SellingStatus->ListingStatus)."','".mysql_escape_string($item->ShippingDetails->ShippingType)."','".mysql_escape_string($item->Site)."',
	'".mysql_escape_string($item->SKU)."','".mysql_escape_string($item->StartPrice)."','".mysql_escape_string($StoreCategory2ID)."','".$StoreCategory2Name."',
	'".mysql_escape_string($StoreCategoryID)."','".$StoreCategoryName."','".htmlentities($item->Title, ENT_QUOTES)."','".mysql_escape_string($userId)."',".$accountId.",".$GalleryTypeFeatured.",".$GalleryTypeGallery.",".$GalleryTypePlus.",'".$item->PictureDetails->GalleryURL."','".$Status."')";
	
	echo $sql;
	echo "<br>\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	
	return mysql_insert_id(eBayListing::$database_connect);
    }
    
    private function eBayUpdateItem($item, $userId){
	global $nest, $storeNest;
	
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
	
	$nest = 0;
	$storeNest = 0;
	$PrimaryCategoryCategoryName = $this->getCategoryPathById($this->getSiteIdByName($item->Site), $item->PrimaryCategory->CategoryID);
	$SecondaryCategoryCategoryName = $this->getCategoryPathById($this->getSiteIdByName($item->Site), $item->SecondaryCategory->CategoryID);
	
	$StoreCategoryName = $this->getStoreCategoryPathById($this->account_id, $item->Storefront->StoreCategoryID);
	$StoreCategory2Name = $this->getStoreCategoryPathById($this->account_id, $item->Storefront->StoreCategory2ID);
	
	$sql = "update items set AutoPay='".mysql_escape_string($item->AutoPay)."',
	BuyItNowPrice='".mysql_escape_string($item->BuyItNowPrice)."',Country='".mysql_escape_string($item->Country)."',
	Currency='".mysql_escape_string($item->Currency)."',
	DispatchTimeMax='".mysql_escape_string($item->DispatchTimeMax)."',StartTime='".mysql_escape_string($item->ListingDetails->StartTime)."',
	EndTime='".mysql_escape_string($item->ListingDetails->EndTime)."',ViewItemURL='".mysql_escape_string($item->ListingDetails->ViewItemURL)."',ListingDuration='".mysql_escape_string($item->ListingDuration)."',
	ListingType='".mysql_escape_string($item->ListingType)."',Location='".mysql_escape_string($item->Location)."',
	PaymentMethods='".mysql_escape_string($item->PaymentMethods)."',PayPalEmailAddress='".mysql_escape_string($item->PayPalEmailAddress)."',
	PostalCode='".mysql_escape_string($item->PostalCode)."',PrimaryCategoryCategoryID='".mysql_escape_string($item->PrimaryCategory->CategoryID)."',
	PrimaryCategoryCategoryName='".mysql_escape_string($PrimaryCategoryCategoryName)."',Quantity='".mysql_escape_string($item->Quantity)."',
	ReturnPolicyDescription='".mysql_escape_string($item->ReturnPolicy->Description)."',ReturnPolicyRefundOption='".mysql_escape_string($item->ReturnPolicy->RefundOption)."',
	ReturnPolicyReturnsAcceptedOption='".mysql_escape_string($item->ReturnPolicy->ReturnsAcceptedOption)."',ReturnPolicyReturnsWithinOption='".mysql_escape_string($item->ReturnPolicy->ReturnsWithinOption)."',
	ReturnPolicyShippingCostPaidByOption='".mysql_escape_string($item->ReturnPolicy->ShippingCostPaidByOption)."',
	CurrentPrice='".mysql_escape_string($item->SellingStatus->CurrentPrice)."',QuantitySold='".mysql_escape_string($item->SellingStatus->QuantitySold)."',
	ListingStatus='".mysql_escape_string($item->SellingStatus->ListingStatus)."',SecondaryCategoryCategoryID='".$item->SecondaryCategory->CategoryID."',SecondaryCategoryCategoryName='".mysql_escape_string($SecondaryCategoryCategoryName)."',
	ShippingType='".mysql_escape_string($item->ShippingDetails->ShippingType)."',
	Site='".mysql_escape_string($item->Site)."',SKU='".mysql_escape_string($item->SKU)."',
	StartPrice='".mysql_escape_string($item->StartPrice)."',StoreCategory2ID='".mysql_escape_string($item->Storefront->StoreCategory2ID)."',StoreCategory2Name='".mysql_escape_string($StoreCategory2Name)."',
	StoreCategoryID='".mysql_escape_string($item->Storefront->StoreCategoryID)."',StoreCategoryName='".mysql_escape_string($StoreCategoryName)."',Title='".htmlentities($item->Title, ENT_QUOTES)."',
	UserID='".mysql_escape_string($userId)."',Status='".$Status."',
	GalleryTypeFeatured=".$GalleryTypeFeatured.",GalleryTypeGallery=".$GalleryTypeGallery.",GalleryTypePlus=".$GalleryTypePlus.",GalleryURL='".$item->PictureDetails->GalleryURL."' where ItemID = '".$item->ItemID."'";
	//echo $sql;
	//echo "<br>";
	//debugLog("eBay-update-item-track.log", $sql);
	//$sql = "update items set CurrentPrice='".$item->SellingStatus->CurrentPrice."',QuantitySold='".$item->SellingStatus->QuantitySold."',ListingStatus='".$item->SellingStatus->ListingStatus.",Status='".$Status."'' 
	//where ItemID = '".$item->ItemID."'";
	
	echo $sql;
	echo "<br>\n";
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
	if(!empty($argv[2]) && !empty($argv[3]) && !empty($argv[4]) && !empty($argv[5])){
	    $sql = "select id from account where name = '".$argv[2]."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    
	    $this->setAccount($row['id']);
	    $this->configEbay();
	    
	    $type = $argv[3];
	    if($type == "Start"){
		$StartTimeFrom  = $argv[4];
		$StartTimeTo    = $argv[5];
	    }elseif($type == "End"){
		$EndTimeFrom = $argv[4];
		$EndTimeTo   = $argv[5];
	    }
	    
	}elseif(!empty($argv[2])){
	    $type = $argv[2];
	    if($type == "Start"){
		if(!empty($argv[3]) && !empty($argv[4])){
		    $StartTimeFrom  = $argv[3];
		    $StartTimeTo    = $argv[4];
		}else{
		    $StartTimeFrom  = date("Y-m-d H:i:s", time() - (12 * 60 * 60) - 30);
		    $StartTimeTo    = date("Y-m-d H:i:s", time() - (8 * 60 * 50) + 30);
		}
	    }elseif($type == "End"){
		if(!empty($argv[3]) && !empty($argv[4])){
		    $EndTimeFrom = $argv[3];
		    $EndTimeTo   = $argv[4];
		}else{
		    $EndTimeFrom = date("Y-m-d H:i:s", time() - (12 * 60 * 60) - 30);
		    $EndTimeTo   = date("Y-m-d H:i:s", time() - (8 * 60 * 50) + 30);
		}
	    }
	}
	
	//print_r(array("Start"=> array("From"=>$StartTimeFrom, "To"=>$StartTimeTo), "End"=> array("From"=>$EndTimeFrom, "To"=>$EndTimeTo)));
	//print_r($this->session);
	//exit;
	
	try {
	    $client = new eBaySOAP($this->session);

	    $Version = '607';
	    $DetailLevel = "ReturnAll";
	    
	    $TotalNumberOfPages = 1;
	    //print_r($argv);
	    for($i = 1; $i <= $TotalNumberOfPages; $i++){
		//var_dump($i);
		$Pagination = array("EntriesPerPage"=> 200,
				    "PageNumber"=> $i);
		//print_r($Pagination);
		//continue;
		
		if($type == "Start"){
		    $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel, 'Pagination' => $Pagination, 'StartTimeFrom' => $StartTimeFrom, 'StartTimeTo' => $StartTimeTo);
		}elseif($type == "End"){
		    $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel, 'Pagination' => $Pagination, 'EndTimeFrom' => $EndTimeFrom, 'EndTimeTo' => $EndTimeTo);
		}
		//print_r($params);
		$results = $client->GetSellerList($params);
		//print_r($results);

		$this->saveFetchData("getSellerList-Request-".date("YmdHis").".xml", $client->__getLastRequest());
		$this->saveFetchData("getSellerList-Response-".date("YmdHis").".xml", $client->__getLastResponse());
		
		$TotalNumberOfPages = $results->PaginationResult->TotalNumberOfPages;
		if($TotalNumberOfPages == 0)
		    return 0;
	    
		if(is_array($results->ItemArray->Item)){
		    //multi item
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
			    
			    /*
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
			    */
			}
		    }
		}else{
		    //one item
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
			
			/*
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
			*/
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
}
?>