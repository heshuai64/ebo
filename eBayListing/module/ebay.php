<?php
ini_set('memory_limit', '128M');
set_time_limit('1800');

class Ebay{
    const LOG_DIR = '/export/eBayListing/log/eBay/';
    
    private $startTime;
    private $endTime;
    
    private $env = "production";
    //private $env = "sandbox";
    
    private $session;
    private $site_id; //US 0, UK 3, AU 15, FR 71, DE 77
    private $account_id;
    private $version = '679';
    
    public function __construct($account_id){
        $this->account_id = $account_id;
    }
    
    private function log($type, $content, $level = 'normal'){
	//print_r($_COOKIE);
	$content = str_replace("<", "[", $content);
	$content = str_replace(">", "]", $content);
	$sql = "insert into log (level,type,content,account_id) values('".$level."','".$type."','".mysql_real_escape_string($content)."','".$this->account_id."')";
	//echo $sql;
	$result = mysql_query($sql, eBayListing::$database_connect);
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
                $Version = $this->version;
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
	    $this->configEbay($argv[2]);
	    $categorySiteID = $argv[2];
	}else{
	    $this->setAccount(1);
	    $this->configEbay(0);
	    $categorySiteID = 0;
	}
	
        try {
	    $client = new eBaySOAP($this->session);

	    $CategorySiteID = $categorySiteID;
	    $Version = $this->version;
	    $DetailLevel = "ReturnAll";
	 
	    $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel, 'WarningLevel' => 'High', 'CategorySiteID' => $CategorySiteID, 'ViewAllNodes' => true);
	    $results = $client->GetCategories($params);
	    //----------   debug --------------------------------
	    //print "Request: \n".$client->__getLastRequest() ."\n";
	    //print "Response: \n".$client->__getLastResponse()."\n";
	    print_r($results->Errors);
            if(!empty($results->CategoryArray)){
		$sql_0 = "delete from categories where CategorySiteID = ".$CategorySiteID;
		echo $sql_0."\n";
		$result_0 = mysql_query($sql_0, eBayListing::$database_connect);
		
		//$this->saveFetchData("getCategories-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		foreach($results->CategoryArray->Category as $category){
		    $sql = "insert into categories (CategoryID,CategoryLevel,CategoryName,CategoryParentID,LeafCategory,BestOfferEnabled,AutoPayEnabled,SellerGuaranteeEligible,CategorySiteID) values 
		    ('".$category->CategoryID."','".$category->CategoryLevel."','".mysql_real_escape_string($category->CategoryName)."','".$category->CategoryParentID."',
		    '".$category->LeafCategory."','".$category->BestOfferEnabled."','".$category->AutoPayEnabled."','".$category->SellerGuaranteeEligible."','".$CategorySiteID."')";
		    echo $sql."\n";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		}
	    }else{
		echo "failure.\n";
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
	    $sql = "select id,site from account where name = '".$userID."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	
	    $this->setAccount($row['id']);
	    $this->configEbay($row['site']);
	}else if(!empty($this->account_id)){
	    
	    $sql = "select name,site from account where id = ".$this->account_id;
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    $userID = $row['name'];
	    
	    $this->configEbay($row['site']);
	}else{
            echo "error, no account id.";
        }
	echo $userID."\n";
	try {
                $client = new eBaySOAP($this->session);

                $CategoryStructureOnly = true;
                $Version = $this->version;
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
		echo $sql."\n";
		
		$this->saveFetchData("getStoreCategoriesRequest-".date("Y-m-d H:i:s").".xml", $client->__getLastRequest());
                $this->saveFetchData("getStoreCategoriesResponse-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		foreach($results->Store->CustomCategories->CustomCategory as $customCategory){
		    $level = 1;
		    $sql = "INSERT INTO `account_store_categories` (`CategoryID` , `CategoryParentID` ,`Name` ,`Order` ,`AccountId`) VALUES ('".$customCategory->CategoryID."','0','".$customCategory->Name."','".$customCategory->Order."','".$this->account_id."')";
		    echo $sql."\n";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		    
		    //two level
		    if(is_array($customCategory->ChildCategory)){
			$twoCategoryParentID = $customCategory->CategoryID;
			$twoChildCategories = $customCategory->ChildCategory;
			
			foreach($twoChildCategories as $twoChildCategory){ 
			    $level = 2;
			    $sql = "INSERT INTO `account_store_categories` (`CategoryID` , `CategoryParentID` ,`Name` ,`Order` ,`AccountId`) VALUES ('".$twoChildCategory->CategoryID."','".$twoCategoryParentID."','".$twoChildCategory->Name."','".$twoChildCategory->Order."','".$this->account_id."')";
			    echo $sql."\n";
			    $result = mysql_query($sql, eBayListing::$database_connect);
			    
			    //three leve
			    if(is_array($twoChildCategory->ChildCategory)){
				$threeCategoryParentID = $twoChildCategory->CategoryID;
				$threeChildCategories = $twoChildCategory->ChildCategory;
				
				foreach($threeChildCategories as $threeChildCategory){
				    $level = 3;
				    $sql = "INSERT INTO `account_store_categories` (`CategoryID` , `CategoryParentID` ,`Name` ,`Order` ,`AccountId`) VALUES ('".$threeChildCategory->CategoryID."','".$threeCategoryParentID."','".$threeChildCategory->Name."','".$threeChildCategory->Order."','".$this->account_id."')";
				    echo $sql."\n";
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
	    //$this->configEbay();
	    $this->getStoreCategories($row['name']);
	}
    }
    
    public function getCategoryFeatures(){
        global $argv;
	try {
            if(!empty($argv[2])){
                $this->setAccount(1);
                $this->configEbay($argv[2]);
                $categorySiteID = $argv[2];
            }else{
                $this->setAccount(1);
                $this->configEbay();
                $categorySiteID = 0;
            }

	    $client = new eBaySOAP($this->session);
	    
	    $DetailLevel = 'ReturnAll';
	    $Version = $this->version;
	    //$FeatureID = 'ListingDurations';
	    $FeatureID = array('ConditionEnabled', 'ConditionValues');
            
	    $params = array('DetailLevel' => $DetailLevel, 'Version' => $Version, 'FeatureID' => $FeatureID, ' ViewAllNodes' => true);
	    $results = $client->GetCategoryFeatures($params);
	    //print_r($results);
	    
	    /*
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
            */
            
            /*
            CREATE TABLE `category_condition` (
            `site_id` INT NOT NULL ,
            `category_id` INT NOT NULL ,
            `condition_id` INT NOT NULL ,
            `condition_display_name` VARCHAR( 30 ) NOT NULL ,
            `condition_help_url` VARCHAR( 90 ) NOT NULL ,
            INDEX ( `site_id` , `category_id` )
            ); 
            
            ALTER TABLE `categories` ADD `ConditionEnabled` VARCHAR( 19 );
            */
            $sql_1 = "delete from category_condition where site_id = ".$categorySiteID;
            echo $sql_1."\n";
            $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
            sleep(6);
            
            //file_put_contents("/tmp/getCategoryFeatures.txt", print_r($results, true));
            //exit;
            foreach($results->Category as $Category){
                //echo $Category->CategoryID."\n";
                //echo $Category->ConditionEnabled."\n";
                //echo $xml->ConditionHelpURL."\n";
                $xml = simplexml_load_string($Category->any);
                //print_r($xml);
                if(preg_match('/<ConditionEnabled>.*?<\/ConditionEnabled>/', $Category->any, $matches)){
                    //print_r($matches);
                    if(strpos($matches[0], "Disabled")){
                        $ConditionEnabled = "Disabled";
                    }elseif(strpos($matches[0], "Enabled")){
                        $ConditionEnabled = "Enabled";
                    }elseif(strpos($matches[0], "Required")){
                        $ConditionEnabled = "Required";
                    }
                    $sql = "update categories set ConditionEnabled = '".$ConditionEnabled."' where CategorySiteID = ".$categorySiteID." and CategoryID = ".$Category->CategoryID;
                    echo $sql."\n";
                    $result = mysql_query($sql, eBayListing::$database_connect);
                }
                //print_r($xml);
                
                foreach($xml->Condition as $x){
                    //echo $x->ID."\n";
                    //echo $x->DisplayName."\n";
                    $sql_1 = "insert into category_condition (site_id,category_id,condition_id,condition_display_name,condition_help_url) values 
                    ('".$categorySiteID."','".$Category->CategoryID."','".$x->ID."','".$x->DisplayName."','".$xml->ConditionHelpURL."')";
                    echo $sql_1."\n";
                    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
                    
                    $sql_2 = "update categories set ConditionEnabled = 'Enabled' where CategorySiteID = ".$categorySiteID." and CategoryID = ".$Category->CategoryID;
                    echo $sql_2."\n";
                    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
                }
            }
            
            if(!empty($results->SiteDefaults)){
                echo $results->SiteDefaults->any;
            }
            //print_r($results->SiteDefaults);
	    //----------   debug --------------------------------
	    //print "Request: \n".$client->__getLastRequest() ."\n";
	    //print "Response: \n".$client->__getLastResponse()."\n";
	    $this->saveFetchData("getCategoryFeatures-".$categorySiteID.".xml", $client->__getLastResponse());
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
                $Version = $this->version;
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
	    $Version = $this->version;
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
	    $Version = $this->version;
	    $DetailLevel = "ReturnAll";
	 
	    $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel);
	    $results = $client->GetCategory2CS($params);
	    $this->saveFetchData("GetCategory2CS-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
	    foreach ($results->MappedCategoryArray->Category as $category){
		$sql = "insert into CharacteristicsSets (SiteID,CategoryID,Name,AttributeSetID,AttributeSetVersion) values 
		('".$categorySiteID."','".$category->CategoryID."','".$category->CharacteristicsSets->Name."',
		'".$category->CharacteristicsSets->AttributeSetID."','".$category->CharacteristicsSets->AttributeSetVersion."')";
		echo $sql."\n";
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
	    $Version = $this->version;
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
	$sql = "select count(*) as num from items where ItemID = '".$itemId."'";
	//echo $sql;
	//echo "<br>\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	return $row['num'];
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
	/*
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
	*/
	echo "insert closed!";
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
	/*
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
	*/
	$sql = "update items set QuantitySold='".mysql_escape_string($item->SellingStatus->QuantitySold)."',
	ListingStatus='".mysql_escape_string($item->SellingStatus->ListingStatus)."',
	UserID='".mysql_escape_string($userId)."',Status='".$Status."' where ItemID = '".$item->ItemID."'"; 
	//echo $sql;
	//echo "<br>";
	//debugLog("eBay-update-item-track.log", $sql);
	//$sql = "update items set CurrentPrice='".$item->SellingStatus->CurrentPrice."',QuantitySold='".$item->SellingStatus->QuantitySold."',ListingStatus='".$item->SellingStatus->ListingStatus.",Status='".$Status."'' 
	//where ItemID = '".$item->ItemID."'";
	
	echo $sql;
	echo "<br>\n";
	$result = mysql_query($sql, eBayListing::$database_connect);
	
	$sql = "select Id,TemplateID from items where ItemID = '".$item->ItemID."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	
	//get template status for forever listing
	$sql_1 = "select status from template where Id = ".$row['TemplateID'];
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$row_1 = mysql_fetch_assoc($result_1);
	if($row_1 == 7){
	    require_once "template.php";
	    $template = new Template();
	    $item_id = $template->changeTemplateToItem($row['TemplateID'], '', date("Y-m-d H:i:s", time() + 2 * 60), 1);
	    $this->log("template", "TemplateID: ".$row['TemplateID']." auto change to item upload(forever listing status).");
	}
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
    public function getSellerList($type, $start="", $end="", $account=""){
	if(!empty($type) && !empty($start) && !empty($end) && !empty($account)){
	    if($type == "Start"){
		$StartTimeFrom  = $start." 00:00:00";
		$StartTimeTo    = $end." 00:00:00";
	    }elseif($type == "End"){
		$EndTimeFrom = $start." 00:00:00";
		$EndTimeTo   = $end." 00:00:00";
	    }
	}elseif(!empty($type)){
	    if($type == "Start"){
		if(!empty($start) && !empty($end)){
		    $StartTimeFrom  = $start." 00:00:00";
		    $StartTimeTo    = $end." 00:00:00";
		}else{
		    $StartTimeFrom  = date("Y-m-d H:i:s", time() - (12 * 60 * 60));
		    $StartTimeTo    = date("Y-m-d H:i:s", time() - (8 * 60 * 60));
		}
	    }elseif($type == "End"){
		if(!empty($start) && !empty($end)){
		    $EndTimeFrom = $start." 00:00:00";
		    $EndTimeTo   = $end." 00:00:00";
		}else{
		    if(date("H") == 0 || date("H") == 12){
			$EndTimeFrom = date("Y-m-d H:i:s", time() - (32 * 60 * 60));
			$EndTimeTo   = date("Y-m-d H:i:s", time() - (8 * 60 * 60));
		    }else{
			$EndTimeFrom = date("Y-m-d H:i:s", time() - (12 * 60 * 60));
			$EndTimeTo   = date("Y-m-d H:i:s", time() - (8 * 60 * 60));
		    }
		}
	    }
	}else{
	    echo "no set type!\n";
	}
	
	//print_r(array("Start"=> array("From"=>$StartTimeFrom, "To"=>$StartTimeTo), "End"=> array("From"=>$EndTimeFrom, "To"=>$EndTimeTo)));
	//print_r($this->session);
	//exit;
	
	try {
	    $client = new eBaySOAP($this->session);

	    $Version = $this->version;
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
		print_r($params);
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
			    /*
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
			    */
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
			/*
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
			*/
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
	global $argv;
	//print_r($argv);
	
	$type = $argv[2];
	$start = $argv[3];
	$end = $argv[4];
	$account = $argv[5];
	
	if(!empty($account)){
	    $sql = "select id,name,token from account where name = '".$account."' and status = 1";
	}else{
	    $sql = "select id,name,token from account where status = 1";
	}
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
	    /*
	    $sql_1 = "select p.host,p.port from proxy as p left join account_to_proxy as atp on p.id = atp.proxy_id where atp.account_id = '".$row['id']."'";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $row_1 = mysql_fetch_assoc($result_1);
	    $this->session = $this->configEbay($row['token'], $row_1['host'], $row_1['port']);
	    $this->getSellerList();
	    */
	    echo "Start fetch [".$row['name']."] from ".$start." to ".$end." by ".$type."\n";
	    $this->setAccount($row['id']);
	    $this->configEbay();
	    $this->getSellerList($type, $start, $end, $account);
	}
    }
    
    //----------------------------------------------------------------------------------------------
    
    private function getItemTemplateStatus($itemId){
	$sql_1 = "select TemplateID from items where Id = ".$itemId;
	$result_1 = mysql_query($sql_1);
	$row_1 = mysql_fetch_assoc($result_1);
	
        if(empty($row_1['TemplateID'])){
            return 6;
        }
	$sql_2 = "select status from template where Id = ".$row_1['TemplateID'];
	$result_2 = mysql_query($sql_2);
	$row_2 = mysql_fetch_assoc($result_2);
	
	return $row_2['status'];
    }
    
    private function getTemplateActiveItemCount($templateId){
	$sql = "select count(*) as num from items where Status = 2 and TemplateID = ".$templateId;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	//file_put_contents("/tmp/getTemplateActiveItemCount-".date("Ymd").".log", $sql."\n".$row['num']."\n\n");
	return $row['num'];
    }
    //-------------------------- Upload  -----------------------------------------------------------------
    private function getCondition($CategorySiteID, $CategoryID){
        $sql = "select CategoryID,CategoryParentID,ConditionEnabled from categories where CategorySiteID = ".$CategorySiteID." and CategoryID = ".$CategoryID;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        if(!empty($row['ConditionEnabled'])){
            if($row['ConditionEnabled'] == "Disabled"){
                return false;
            }else{
                return true;
            }
        }elseif($row['CategoryID'] != $row['CategoryParentID']){
            $this->getCondition($CategorySiteID, $row['CategoryParentID']);
        }else{
            return false;
        }
    }
    
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
	
	$sql = "select Id,AccountId,TemplateID,SKU from items where Status = 1 and ScheduleTime between '".$from."' and '".$to."'";
	//$sql = "select Id,AccountId from items where Status = 1";
	
	$result = mysql_query($sql);
	while($row = mysql_fetch_assoc($result)){
	    $this->setAccount($row['AccountId']);
	    $template_status = $this->getItemTemplateStatus($row['Id']);
	    if($template_status !=2 && $template_status != 3 && $template_status != 7){
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
		
		    case 6:
			$status = "forever inactive";
		    break;
		}
		$this->log("upload", $row['Id'] . " template status is ".$status, "warn");
		continue;
	    }
	    
	    if($this->getTemplateActiveItemCount($row['TemplateID']) > 0){
		$this->log("upload", $row['Id'] . " has active listings", "warn");
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
	    
	    
	    //$row_1['Description'] = utf8_encode($row_1['Description']);
	    $row_1['Title'] = html_entity_decode($row_1['Title'], ENT_QUOTES);
	    if(mb_strlen($row_1['Title'], "utf8") > 55){
                $row_1['Title'] = mb_substr($row_1['Title'], 0, 55, "utf8");
            }
            //$row_1['Title'] = utf8_encode($row_1['Title']);
	    
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
	    
	    $sql_7 = "select accountLocation from account where id = ".$row_1['accountId'];
	    $result_7 = mysql_query($sql_7);
	    $row_7 = mysql_fetch_assoc($result_7);
	    $accountLocation = $row_7['accountLocation'];
	    
	    $row_1['AttributeSetArray'] = $AttributeSetArray;
	    $row_1['ShippingServiceOptions'] = $ShippingServiceOptions;
	    $row_1['InternationalShippingServiceOption'] = $InternationalShippingServiceOption;
	    $row_1['PictureURL'] = $PictureURL;
	    if(!empty($accountLocation)){
		$row_1['Country'] = $accountLocation;
		switch($accountLocation){
		    case "US":
			$row_1['Location'] = 'United States';
		    break;
			
		    case "UK":
			$row_1['Country'] = 'GB';
			$row_1['Location'] = 'United Kingdom';
		    break;
		
		    case "HK":
			$row_1['Location'] = 'Hong Kong';
		    break;
		    
		    case "DE":
			$row_1['Location'] = 'Germany';
		    break;
		
		    case "AU":
			$row_1['Location'] = 'Australia';
		    break;
		
		    case "FR":
			$row_1['Location'] = 'France';
		    break;
		}
	    }

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
	    $Version = $this->version;
	    
	    $itemArray = array();
	    
            //if($this->getCondition($row['id'], $item['PrimaryCategoryCategoryID'])){
                $itemArray['ConditionID'] = 1000;
            //}
        
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
	    if(empty($item['Country']) || $item['Country'] == 'CN' || $item['Country'] == 'HK'){
		$itemArray['Country'] = 'HK';
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
	    $itemArray['PrivateListing'] = true;
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
	    
	    if($item['Site'] == 'Germany' && $item['Motors']){
		$itemArray['MotorsGermanySearchable'] = true;
	    }
	    
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
	    
	    //$row_1['Description'] = utf8_encode($row_1['Description']);
	    $row_1['Title'] = html_entity_decode($row_1['Title'], ENT_QUOTES);
	    //$row_1['Title'] = utf8_encode($row_1['Title']);
	    if(mb_strlen($row_1['Title'], "utf8") > 55){
                $row_1['Title'] = mb_substr($row_1['Title'], 0, 55, "utf8");
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
	    
	    $sql_7 = "select accountLocation from account where id = ".$row_1['accountId'];
	    $result_7 = mysql_query($sql_7);
	    $row_7 = mysql_fetch_assoc($result_7);
	    $accountLocation = $row_7['accountLocation'];
	    
	    $row_1['AttributeSetArray'] = $AttributeSetArray;
	    $row_1['ShippingServiceOptions'] = $ShippingServiceOptions;
	    $row_1['InternationalShippingServiceOption'] = $InternationalShippingServiceOption;
	    $row_1['PictureURL'] = $PictureURL;
	    if(!empty($accountLocation)){
		$row_1['Country'] = $accountLocation;
		switch($accountLocation){
		    case "US":
			$row_1['Location'] = 'United States';
		    break;
			
		    case "UK":
			$row_1['Country'] = 'GB';
			$row_1['Location'] = 'United Kingdom';
		    break;
		
		    case "HK":
			$row_1['Location'] = 'Hong Kong';
		    break;
		    
		    case "DE":
			$row_1['Location'] = 'Germany';
		    break;
		
		    case "AU":
			$row_1['Location'] = 'Australia';
		    break;
		
		    case "FR":
			$row_1['Location'] = 'France';
		    break;
		}
	    }
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
	    $Version = $this->version;
	    
	    $itemArray = array();
	    
            $itemArray['ConditionID'] = 1000;
            
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
	    $itemArray['PrivateListing'] = true;
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
                $sql_0 = "update items set Status = 20 where Id = '".$row['Id']."'";
                $result_0 = mysql_query($sql_0);
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
	    
	    //$row_1['Description'] = utf8_encode($row_1['Description']);
	    $row_1['Title'] = html_entity_decode($row_1['Title'], ENT_QUOTES);
	    //$row_1['Title'] = utf8_encode($row_1['Title']);
	    
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
	    
	    $sql_7 = "select accountLocation from account where id = ".$row_1['accountId'];
	    $result_7 = mysql_query($sql_7);
	    $row_7 = mysql_fetch_assoc($result_7);
	    $accountLocation = $row_7['accountLocation'];
	    
	    $row_1['AttributeSetArray'] = $AttributeSetArray;
	    $row_1['ShippingServiceOptions'] = $ShippingServiceOptions;
	    $row_1['InternationalShippingServiceOption'] = $InternationalShippingServiceOption;
	    $row_1['PictureURL'] = $PictureURL;
	    if(!empty($accountLocation)){
		$row_1['Country'] = $accountLocation;
		switch($accountLocation){
		    case "US":
			$row_1['Location'] = 'United States';
		    break;
			
		    case "UK":
			$row_1['Country'] = 'GB';
			$row_1['Location'] = 'United Kingdom';
		    break;
		
		    case "HK":
			$row_1['Location'] = 'Hong Kong';
		    break;
		    
		    case "DE":
			$row_1['Location'] = 'Germany';
		    break;
		
		    case "AU":
			$row_1['Location'] = 'Australia';
		    break;
		
		    case "FR":
			$row_1['Location'] = 'France';
		    break;
		}
	    }
	    //$row_1['ListingDuration'] = "Days_7";
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
	    $Version = $this->version;
	    
	    $itemArray = array();
            
	    $itemArray['ConditionID'] = 1000;
            
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
	    if(empty($item['Country']) || $item['Country'] == 'CN' || $item['Country'] == 'HK'){
		$itemArray['Country'] = 'HK';
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
	    $Version = $this->version;
	
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
    
    public function getToken(){
	//echo "test";
        session_start();
        try {
            $this->setAccount(1);
            $this->configEbay();
            
            $this->session->token = NULL;
            //print_r($this->session);
            //exit;
            $client = new eBaySOAP($this->session);
            
            $Version = $this->version;
            $RuName = "Creasion-Creasion-02dd-4-qtossfvtu";
            $params = array('Version' => $Version, 'RuName' => $RuName);
            $results = $client->GetSessionID($params);
            $_SESSION['SessionID'] = $results->SessionID;
            if(!empty($results->SessionID)){
                //$results->SessionID
                //echo "https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=Creasion-Creasion-1ca1-4-vldylhxcb&&sid=$results->SessionID";
                //header("Location: https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=".$RuName."&sid=".$results->SessionID);
                header("Location: https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&RuName=".$RuName."&SessID=".$results->SessionID);
                //var_dump("https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=Creasion-Creasion-1ca1-4-vldylhxcb&&sid=$results->SessionID");
                //----------   debug --------------------------------
                //print "Request: \n".$client->__getLastRequest() ."\n";
                //print "Response: \n".$client->__getLastResponse()."\n";
            }else{
                print_r($results);
            }
            //return $results;
                
        } catch (SOAPFault $f) {
                print $f; // error handling
        }
    }
    
    public function saveToken(){
        session_start();
        try {
            $this->setAccount(1);
            $this->configEbay();
            $this->session->token = NULL;
            
            $client = new eBaySOAP($this->session);
            $Version = $this->version;
            $params = array('Version' => $Version, 'SessionID' => $_SESSION['SessionID']);
            $results = $client->FetchToken($params);
           
            //print_r($results);
            $_GET['ebaytkn'] = $results->eBayAuthToken;
	    $_GET['tknexp'] = $results->HardExpirationTime;
            //eBayAuthToken
            //HardExpirationTime
	    if(!empty($_GET['ebaytkn'])){
		$sql_1 = "select count(*) as num from account where name = '".$_GET['username']."'";
		$result_1 = mysql_query($sql_1);
		$row_1 = mysql_fetch_assoc($result_1);
		if($row_1['num'] == 0){   
		    $sql = "insert into account (name,token,tokenExpiry,status) values ('".$_GET['username']."','".$_GET['ebaytkn']."','".$_GET['tknexp']."',1)";
		    //echo $sql;
		    $result = mysql_query($sql);
		}else{
		    $sql = "update account set token = '".$_GET['ebaytkn']."',tokenExpiry = '".$_GET['tknexp']."' where name = '".$_GET['username']."'";
		    //echo $sql;
		    $result = mysql_query($sql);
		}
		
		if($result){
			echo "<h1>Thank you, Success!</h1>";
		}else{
			echo "<h1>Failure!</h1>";
		}
	    }else{
		echo "<h1>Failure!</h1>";
	    }
	    
        } catch (SOAPFault $f) {
                print $f; // error handling
        }
    }
}

if($argv[0] == "ebay.php"){
    require_once '../service.php';
    $acton = $argv[1];
    echo $acton."\n";
    if(!empty($acton)){
        $ebay = new Ebay(1);
        $ebay->$acton();
    }
}

?>