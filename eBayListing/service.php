<?php

require_once 'eBaySOAP.php';

class eBayListing{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaylisting';
    const GATEWAY_SOAP = 'https://api.sandbox.ebay.com/wsapi';
    //const GATEWAY_SOAP = 'https://api.ebay.com/wsapi';
    
    const EBAY_BO_SERVICE = 'http://127.0.0.1/eBayBO/service.phpss';
    const INVENTORY_SERVICE = 'http://127.0.0.1/einv2/service.php';
    private $startTime;
    private $endTime;
    
    //private $env = "production";
    private $env = "sandbox";
    
    private $session;
    private $site_id = 0;
    private $account_id;
    
    public function __construct(){
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
    }
    
    public  function setAccount($account_id){
	$this->account_id = $account_id;
    }
    
    public function setSite($site_id){
	$this->site_id = $site_id;
    }
    
    public function configEbay(){
	if(!empty($_COOKIE['account_id'])){
	    $this->account_id = $_COOKIE['account_id'];
	}
	
    	if(!empty($this->account_id)){
	    $sql = "select token from account where id = '".$this->account_id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    
	    
	    $sql_1 = "select p.host,p.port from proxy as p left join account_to_proxy as atp on p.id = atp.proxy_id where atp.account_id = '".$this->account_id."'";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $row_1 = mysql_fetch_assoc($result_1);
    
	    //------------------------------------------------------------------------------------------------
	    // Load developer-specific configuration data from ini file
	    $config = parse_ini_file('ebay.ini', true);
	    $env = $this->env;
	    //$compatibilityLevel = $config['settings']['compatibilityLevel'];
	    
	    $dev =   $config[$env]['devId'];
	    $app =   $config[$env]['appId'];
	    $cert =  $config[$env]['cert'];
	    $token = $config[$env]['authToken'];
	    //$token = $row['token'];
	    //$token = (empty($token)?$config[$env]['authToken']:$token);
	    //$location = $config[$env]['gatewaySOAP'];
	    $location = self::GATEWAY_SOAP;
	    
	    // Create and configure session
	    $this->session = new eBaySession($dev, $app, $cert, $row_1['host'], $row_1['port']);
	    $this->session->token = $token;
	    //$this->session->site = 0; // 0 = US;
	    $this->session->site = $this->site_id;
	    $this->session->location = $location;
	}
    }
    
    private function saveFetchData($file_name, $data){
	file_put_contents("/export/eBayBO/eBayListing/log/".$file_name, $data);
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
    
    private function getStoreCategories($userID){
	$sql = "select id from account where name = '".$userID."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$account_id = $row['id'];
	
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
                $this->saveFetchData("getStoreCategories-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		foreach($results->Store->CustomCategories->CustomCategory as $customCategory){
		    $level = 1;
		    $sql = "INSERT INTO `account_store_categories` (`CategoryID` , `CategoryParentID` ,`Name` ,`Order` ,`AccountId`) VALUES ('".$customCategory->CategoryID."','0','".$customCategory->Name."','".$customCategory->Order."','".$account_id."')";
		    //echo $sql."<br>\n";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		    
		    //two level
		    if(is_array($customCategory->ChildCategory)){
			$twoCategoryParentID = $customCategory->CategoryID;
			$twoChildCategories = $customCategory->ChildCategory;
			
			foreach($twoChildCategories as $twoChildCategory){ 
			    $level = 2;
			    $sql = "INSERT INTO `account_store_categories` (`CategoryID` , `CategoryParentID` ,`Name` ,`Order` ,`AccountId`) VALUES ('".$twoChildCategory->CategoryID."','".$twoCategoryParentID."','".$twoChildCategory->Name."','".$twoChildCategory->Order."','".$account_id."')";
			    //echo $sql."<br>\n";
			    $result = mysql_query($sql, eBayListing::$database_connect);
			    
			    //three leve
			    if(is_array($twoChildCategory->ChildCategory)){
				$threeCategoryParentID = $twoChildCategory->CategoryID;
				$threeChildCategories = $twoChildCategory->ChildCategory;
				
				foreach($threeChildCategories as $threeChildCategory){
				    $level = 3;
				    $sql = "INSERT INTO `account_store_categories` (`CategoryID` , `CategoryParentID` ,`Name` ,`Order` ,`AccountId`) VALUES ('".$threeChildCategory->CategoryID."','".$threeCategoryParentID."','".$threeChildCategory->Name."','".$threeChildCategory->Order."','".$account_id."')";
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
	    
	    $sql = "select Id,SKU,Title,BuyItNowPrice,ListingType,StartPrice,Quantity,ListingDuration from template where accountId = '".$this->account_id."' limit ".$_POST['start'].",".$_POST['limit'];
            $result = mysql_query($sql, eBayListing::$database_connect);
            
	}else{
	    $where = " where tttc.template_category_id = '".$_POST['parent_id']."' and t.accountId = '".$this->account_id."' ";
		
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
                
            $sql = "select count(*) as count from template as t left join template_to_template_cateogry as tttc on t.Id = tttc.template_id  ".$where;
            //echo $sql;
	    //exit;
	    $result = mysql_query($sql, eBayListing::$database_connect);
            $row = mysql_fetch_assoc($result);
            $totalCount = $row['count'];
            
            $sql = "select Id,SKU,Title,BuyItNowPrice,ListingType,StartPrice,Quantity,ListingDuration from template as t left join template_to_template_cateogry as tttc on t.Id = tttc.template_id ".$where." limit ".$_POST['start'].",".$_POST['limit'];
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
	    $array[] = $row;
	}
	
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$array));
	mysql_free_result($result);
    }
    
    private function tempalteChangeToItem($template_id, $time){
	$sql_1 = "insert into items (AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
	Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
	PostalCode,PrimaryCategoryCategoryID,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,
	ReservePrice,CurrentPrice,ScheduleTime,SecondaryCategoryCategoryID,ShippingType,Site,SKU,StartPrice,
	StoreCategory2ID,StoreCategoryID,SubTitle,Title,UserID,accountId,BoldTitle,Border,
	Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
	GalleryURL,PhotoDisplay,Status) select AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
	Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
	PostalCode,PrimaryCategoryCategoryID,Quantity,ReturnPolicyDescription,ReturnPolicyReturnsAcceptedOption,
	ReservePrice,CurrentPrice,'".$time."',SecondaryCategoryCategoryID,ShippingType,Site,SKU,StartPrice,
	StoreCategory2ID,StoreCategoryID,SubTitle,Title,UserID,accountId,BoldTitle,Border,
	Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
	GalleryURL,PhotoDisplay,'0' from template where Id = '".$template_id."'";
	
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$item_id = mysql_insert_id(eBayListing::$database_connect);
	
	$sql_2 = "insert into picture_url (ItemID,url)  select '".$item_id."',url from template_picture_url where templateId = '".$template_id."'";
	$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	
	$sql_3 = "insert into shipping_service_options (ItemID,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost) select '".$item_id."',FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost from template_shipping_service_options where templateId = '".$template_id."'";
	$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	
	$sql_4 = "insert into international_shipping_service_option (ItemID,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShipToLocation) select '".$item_id."',ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShipToLocation from template_international_shipping_service_option where templateId = '".$template_id."'";
	$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	
	$sql_5 = "select * from template_attribute_set where templateId = '".$template_id."'";
	$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	while($row_5 = mysql_fetch_assoc($result_5)){
	    $template_attribute_set_id = $row_5['attribute_set_id'];
	    $sql_6 = "insert into attribute_set (ItemID,attributeSetID) values ('".$item_id."','".$row_5['attributeSetID']."')";
	    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
	    $attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
	    
	    $sql_7 = "insert into attribute (attributeID,attribute_set_id,ValueID,ValueLiteral) 
	    select attributeID,'".$attribute_set_id."',ValueID,ValueLiteral from template_attribute
	    where attribute_set_id = '".$template_attribute_set_id."'";
	    $result_7 = mysql_query($sql_7, eBayListing::$database_connect);
	}
	
	if($result_1 && $result_2 && $result_3 && $result_4 && $result_5 && $result_6 && $result_7){
	    return true;
	}else{
	    return false;
	}
    }
    
    public function templateAddToUpload(){
	if(strpos($_POST['ids'], ',')){
	    $ids = explode(',', $_POST['ids']);
	    foreach($ids as $id){
		$sql_10 = "select * from schedule where template_id = '$id'";
		$result_10 = mysql_query($sql_10, eBayListing::$database_connect);
		while($row_10 = mysql_fetch_assoc($result_10)){
		    $startTimestamp = strtotime($row_10['startDate']);
		    $endTimestamp = strtotime($row_10['endDate']);
		    while($startTimestamp <= $endTimestamp){
			if(date("D", $startTimestamp) == $row_10['day']){
			    $result = $this->tempalteChangeToItem($id, date("Y-m-d", $startTimestamp) . ' ' .$row_10['time']);
			}
			$startTimestamp += 24 * 60 * 60;
		    }
		}
	    }
	}else{
	    $sql_10 = "select * from schedule where template_id = '".$_POST['ids']."'";
	    $result_10 = mysql_query($sql_10, eBayListing::$database_connect);
	    while($row_10 = mysql_fetch_assoc($result_10)){
		$startTimestamp = strtotime($row_10['startDate']);
		$endTimestamp = strtotime($row_10['endDate']);
		while($startTimestamp <= $endTimestamp){
		    if(date("D", $startTimestamp) == $row_10['day']){
			$result = $this->tempalteChangeToItem($_POST['ids'], date("Y-m-d", $startTimestamp) . ' ' .$row_10['time']);
		    }
		    $startTimestamp += 24 * 60 * 60;
		}
	    }
	}
	echo $result;
    }
    
    public function templateDelete(){
	if(strpos($_POST['ids'], ',')){
	    $ids = explode(',', $_POST['ids']);
	    foreach($ids as $id){
		$sql = "delete template items where Id = '".$id."'";
	    }
	}else{
	    $sql = "delete from template where Id = '".$_POST['ids']."'";
	}
	$result = mysql_query($sql, eBayListing::$database_connect);
	
	echo $result;
    }
    
    public function getWaitingUploadItem(){
	$array = array();
	
	if(empty($_POST)){
	    $sql = "select count(*) as count from items where accountId = '".$this->account_id."' and Status = '0'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
            $row = mysql_fetch_assoc($result);
            $totalCount = $row['count'];
	    
	    if(empty($_POST['start']) && empty($_POST['limit'])){
                $_POST['start'] = 0;
                $_POST['limit'] = 20;
            }
	    
	    $sql = "select Id,SKU,Title,BuyItNowPrice,ListingDuration,ListingType,Quantity,StartPrice,ScheduleTime from items where accountId = '".$this->account_id."' and Status = '0' limit ".$_POST['start'].",".$_POST['limit'];
            $result = mysql_query($sql, eBayListing::$database_connect);
            
	}else{
	    $where = " where accountId = '".$this->account_id."' and Status = '0' ";
		
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
            
            $sql = "select Id,SKU,Title,BuyItNowPrice,ListingDuration,ListingType,Quantity,StartPrice,ScheduleTime from items ".$where." limit ".$_POST['start'].",".$_POST['limit'];
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
	    $array[] = $row;
	}
	
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$array));
	mysql_free_result($result);
    }
    
    public function templateImportCsv(){
	//echo '{success:true, test:"'.print_r($_FILES, true).'"}';
	//exit;
	$handle = fopen($_FILES['csv']['tmp_name'], "r");
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	    //$data[0]
	    //$data[1]
	    $sql = "";
	}
	fclose($handle);
	echo "{success:true}";
    }
    
    public function templateIntervalUpload(){
	/*
	ALTER TABLE `schedule` DROP PRIMARY KEY;
	ALTER TABLE `schedule` ADD INDEX ( `item_id` );
	 
	*/
	//echo date("Y-m-d H:i:s", strtotime("12:00:00") + 60);
	$_POST['date'] = substr($_POST['date'], 0, -24);
	$ids = explode(',', $_POST['ids']);
	if(count($ids) > 1){
	    $i = 0;
	    foreach($ids as $id){
		$sql = "select Site from template where Id = '".$id."'";
		$result = mysql_query($sql, eBayListing::$database_connect);
		$row = mysql_fetch_assoc($result);
		
		switch($row['Site']){
		    case "US":
			$time = date("Y-m-d H:i:s", strtotime("-12 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "UK":
			$time = date("Y-m-d H:i:s", strtotime("-8 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "AU":
			$time = date("Y-m-d H:i:s", strtotime("+2 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "FR":
			$time = date("Y-m-d H:i:s", strtotime("-7 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		}
		$result = $this->tempalteChangeToItem($id, $time);
		$i++;
	    }
	}else{
	    $sql = "select Site from template where Id = '".$_POST['ids']."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);

	    switch($row['Site']){
		case "US":
		    $time = date("Y-m-d H:i:s", strtotime("-12 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "UK":
		    $time = date("Y-m-d H:i:s", strtotime("-8 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "AU":
		    $time = date("Y-m-d H:i:s", strtotime("+2 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "FR":
		    $time = date("Y-m-d H:i:s", strtotime("-7 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    }
	  
	    $result = $this->tempalteChangeToItem($_POST['ids'], $time);
	}
	echo $result;
    }
    
    public function getTemplateCategory(){
        $sql = "select id,name from templates where account_id = '".$this->account_id."'";
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
	if(!empty($_POST['UseStandardFooter']) && $_POST['UseStandardFooter'] == 1){
	    $sql = "select footer from account_footer where accountId = '".$this->account_id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    $_POST['Description'] .= $row['footer'];
	}
	
	if($_POST['ListingType'] == "FixedPriceItem" || $_POST['ListingType'] == "StoresFixedPrice"){
	    $_POST['StartPrice'] = $_POST['BuyItNowPrice'];
	    $_POST['BuyItNowPrice'] = 0;
	}
	
	if($_POST['ListingType'] == "Chinese"){
	    $_POST['Quantity'] = 1;   
	}
	
	//StartTime,EndTime
	//$PaymentMethods = ($_POST['PayPalPayment'] == 1)?'PayPal':'';
	$sql = "insert into template (BuyItNowPrice,Country,Currency,Description,DispatchTimeMax,
	ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,PostalCode,
	PrimaryCategoryCategoryID,SecondaryCategoryCategoryID,Quantity,ReservePrice,
	ShippingType,Site,SKU,StartPrice,StoreCategory2ID,StoreCategoryID,SubTitle,Title,
	BoldTitle,Border,Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypePlus,accountId) values (
	'".$_POST['BuyItNowPrice']."','CN','".$_POST['Currency']."',
	'".$_POST['Description']."','".$_POST['DispatchTimeMax']."',
	'".$_POST['ListingDuration']."','".$_POST['ListingType']."','".$_POST['Location']."','PayPal',
	'".$_POST['PayPalEmailAddress']."','".$_POST['PostalCode']."',
	'".$_POST['PrimaryCategoryCategoryID']."','".$_POST['SecondaryCategoryCategoryID']."',
	'".@$_POST['Quantity']."','".@$_POST['ReservePrice']."','".@$_POST['ShippingType']."',
	'".$_POST['Site']."','".$_POST['SKU']."','".$_POST['StartPrice']."','".$_POST['StoreCategory2ID']."',
	'".$_POST['StoreCategoryID']."','".$_POST['SubTitle']."',
	'".$_POST['Title']."','".(empty($_POST['BoldTitle'])?0:1)."',
	'".(empty($_POST['Border'])?0:1)."','".(empty($_POST['Featured'])?0:1)."','".(empty($_POST['Highlight'])?0:1)."',
	'".(empty($_POST['HomePageFeatured'])?0:1)."','".(empty($_POST['GalleryTypeFeatured'])?0:1)."','".(empty($_POST['GalleryTypePlus'])?0:1)."','".$this->account_id."')";
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
	    $sql_1 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost) values
	    ('".$id."','".@$_POST['FreeShipping-'.$i]."','".$_POST['ShippingService-'.$i]."','".$_POST['ShippingServiceCost-'.$i]."')";
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
	    $sql_2 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShipToLocation) values
	    ('".$id."','".$_POST['InternationalShippingService-'.$i]."','".$_POST['InternationalShippingServiceCost-'.$i]."','".$ShipToLocation."')";
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
	session_start();
	if(!empty($_SESSION['Schedule'])){
	    switch($_POST['Site']){
		case "US":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("-12 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("-12 hour ".$name));
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
				$day = date("D", strtotime("-8 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("-8 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    
		case "AU":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("+2 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("+2 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    
		case "FR":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("-7 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("-7 hour ".$name));
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
	if(!empty($_SESSION['AttributeSet'])){
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
			foreach($value as $id=>$name){
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
	
	if($result && $result_1 && $result_2){
	    unset($_SESSION['Schedule']);
	    unset($_SESSION['AttributeSet']);
	    echo '{success: true}';
	    $this->log("template", $_POST['SKU'] . " add to template.");
	}else{
	    echo '{success: false,
		    errors: {message: "can\'t create."}
		}';
	    $this->log("template", $_POST['SKU'] . " add to template failure.", "error");
	}
    }
    
    public function getTemplate(){
	$sql = "select * from template where Id = '".$_GET['id']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	echo '['.json_encode($row).']';
	mysql_free_result($result);
    }
    
    //-------------------------------------------------------------------------------------------------------
    public function getAllInventorySkus(){
	$result = $this->get(self::INVENTORY_SERVICE."?action=getAllSkus");
	echo $result;
    }
    
    public function getActiveItem(){
	if(empty($_POST['start']) && empty($_POST['limit'])){
	       $_POST['start'] = 0;
	       $_POST['limit'] = 20;
	}

	//Active Completed Ended
	$sql = "select count(*) as count from items";// where ListingStatus = 'Active' and EndTime > NOW()";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$totalCount = $row['count'];
	
	$sql_1 = "select * from items limit ".$_POST['start'].",".$_POST['limit'];// where ListingStatus = 'Active' and EndTime > NOW() limit ".$_POST['start'].",".$_POST['limit'];
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$data = array();
	while($row_1 = mysql_fetch_assoc($result_1)){
	    $data[] = $row_1;
	}
	
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$data));
	mysql_free_result($result);
	mysql_free_result($result_1);
    }
    
    public function getEndItem(){
	if(empty($_POST['start']) && empty($_POST['limit'])){
	       $_POST['start'] = 0;
	       $_POST['limit'] = 20;
	}

	//Active Completed Ended
	$sql = "select count(*) as count from items where (ListingStatus = 'Completed' or ListingStatus = 'Ended') or EndTime < NOW()";
	$result = mysql_query($sql, Service::$database_connect);
	$row = mysql_fetch_assoc($result);
	$totalCount = $row['count'];
	
	$sql_1 = "select count(*) as count from items where (ListingStatus = 'Completed' or ListingStatus = 'Ended') or EndTime < NOW() limit ".$_POST['start'].",".$_POST['limit'];
	$result_1 = mysql_query($sql_1, Service::$database_connect);
	$data = array();
	while($row_1 = mysql_fetch_assoc($result_1)){
	    $data[] = $row_1;
	}
	
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$data));
	mysql_free_result($result);
	mysql_free_result($result_1);
    }
    
    public function getCategoriesTree(){
	if($_POST['node'] == "0"){
	    $sql = "select CategoryID,CategoryName,LeafCategory from categories where CategoryID = CategoryParentID";
	}else{
	    $sql = "select CategoryID,CategoryName,LeafCategory from categories where CategoryParentID = '".$_POST['node']."' and CategoryID != CategoryParentID";
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
	$sql = "select CategoryID,Name from account_store_categories where AccountId = '".$_SESSION['account_id']."' and CategoryParentID ='".$_POST['node']."' order by `Order`";
	
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
	
	if($_POST['serviceType'] == "Flat"){
	    $sql = "select Description,ShippingService from shipping_service_details where  SiteID = '".$_POST['SiteID']."' and ServiceTypeFlat = 1 and InternationalService = 0";
	}elseif($_POST['serviceType'] == "Calculated"){
	    $sql = "select Description,ShippingService from shipping_service_details where  SiteID = '".$_POST['SiteID']."' and ServiceTypeCalculated = 1 and InternationalService = 0";
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

	if($_POST['serviceType'] == "Flat"){
	    $sql = "select Description,ShippingService from shipping_service_details where  SiteID = '".$_POST['SiteID']."' and ServiceTypeFlat = 1 and InternationalService = 1";
	}elseif($_POST['serviceType'] == "Calculated"){
	    $sql = "select Description,ShippingService from shipping_service_details where  SiteID = '".$_POST['SiteID']."' and ServiceTypeCalculated = 1 and InternationalService = 1";
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
	    $this->setSite($row['id']);
	    $this->configEbay();
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
	    $this->setSite($row['id']);
	    $this->configEbay();
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
    
    //-----------------  Item Specifics -----------------------------------------------
    //http://127.0.0.1:6666/eBayBO/eBaylisting/service.php?action=getAllCategory2CS
    public function getCategory2CS(){
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
		echo $sql;
		echo "\n";
		$result = mysql_query($sql, eBayListing::$database_connect);
	    }
	    
	    echo "\n****************************************************************\n";
	    flush();
	    exit();
	} catch (SOAPFault $f) {
            print $f; // error handling
        }
    }
    
    public function getAllCategory2CS(){
	$sql = "select id from site where status = 1";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while ($row = mysql_fetch_assoc($result)){
	    $this->setSite($row['id']);
	    $this->configEbay();
	    $this->getCategory2CS();
	}
    }
    
    public function getAttributesCS(){
	try {
	    $client = new eBaySOAP($this->session);
	    $Version = '607';
	    $DetailLevel = "ReturnAll";
	 
	    $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel);
	    $results = $client->GetAttributesCS($params);
	    
	    file_put_contents("GetAttributesCS-".$this->site_id.".xml", $results->AttributeData);
	    echo "\n******************   getAttributesCS Site ".$this->site_id." **************************\n";
	    flush();
	    exit();
	} catch (SOAPFault $f) {
            print $f; // error handling
        }
    }
    
    public function getAllAttributesCS(){
	$sql = "select id from site where status = 1";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while ($row = mysql_fetch_assoc($result)){
	    $this->setSite($row['id']);
	    $this->configEbay();
	    $this->getAttributesCS();
	}
    }
    
    public function getAttributes(){
	session_start();
	//$_GET['CategoryID'] = 34;
	$sql = "select AttributeSetID from CharacteristicsSets where SiteID = '".$_GET['SiteID']."' and CategoryID = '".$_GET['CategoryID']."'";
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
    public function loadItemSpecifics(){
	session_start();
	if(!empty($_SESSION['AttributeSet'][$_GET['sku']][$_GET['AttributeSetID']])){
	    echo '['.json_encode($_SESSION['AttributeSet'][$_GET['sku']][$_GET['AttributeSetID']]).']';
	}
	//print_r($_SESSION['AttributeSet']);
    }
    
    public function saveItemSpecifics(){
	session_start();
	unset($_SESSION['AttributeSet'][$_GET['sku']][$_POST['CharacteristicsSetId']]);
	//unset($_SESSION);
	foreach($_POST as $key=>$value){
	    if($key != "CharacteristicsSetId"){
		$_SESSION['AttributeSet'][$_GET['sku']][$_POST['CharacteristicsSetId']][$key] = $value;
	    }
	}
	//print_r($_SESSION['AttributeSet'][$_GET['sku']][$_POST['CharacteristicsSetId']]);
	if(!empty($_SESSION['AttributeSet'][$_GET['sku']][$_POST['CharacteristicsSetId']])){
		echo 	'{success: true}';
		
	}else{
		echo 	'{success: false,
			  errors: {message: "can\'t create."}
			}';
	}
    }
    
    //---------------------------------------------------------------------------------
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
    
    //-------------------------- Upload  -------------------------------------------------------------------
    public function uploadItem(){
	$date = date("Y-m-d");
	$day = date("D");
	$time = date("H:i:00");
	/*
	$sql = "select item_id from schedule where startDate <= '".$date."' and endDate => '".$date."' and day = '".$day."' and time ='".$time."'";
	//$sql = "select item_id from schedule where day = '".$day."' and time ='".$time."'";
	//$sql = "select item_id from schedule where day = '".$day."'";
	$sql = "select item_id from schedule where item_id = '15'";
	
	$result = mysql_query($sql);
	while($row = mysql_fetch_assoc($result)){
	*/
	    $row['item_id'] = 18;
	    $sql_1 = "select * from items where Id = '".$row['item_id']."'";
	    $result_1 = mysql_query($sql_1);
	    $row_1 = mysql_fetch_assoc($result_1);
	    
	    $sql_2 = "select * from shipping_service_options where ItemID = '".$row['item_id']."'";
	    $result_2 = mysql_query($sql_2);
	    $ShippingServiceOptions = array();
	    while($row_2 = mysql_fetch_assoc($result_2)){
		$ShippingServiceOptions[] = $row_2;
	    }
	    
	    $sql_3 = "select * from international_shipping_service_option where ItemID = '".$row['item_id']."'";
	    $result_3 = mysql_query($sql_3);
	    $InternationalShippingServiceOption = array();
	    while($row_3 = mysql_fetch_assoc($result_3)){
		$InternationalShippingServiceOption[] = $row_3;
	    }
	    
	    $sql_4 = "select * from picture_url where ItemID = '".$row['item_id']."'";
	    //echo $sql_4;
	    //echo "<br>";
	    $result_4 = mysql_query($sql_4);
	    $PictureURL = array();
	    while($row_4 = mysql_fetch_assoc($result_4)){
		$PictureURL[] = $row_4['url'];
	    } 
	    
	    $sql_5 = "select * from attribute_set where ItemID = '".$row['item_id']."'";
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
	//}
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
	    if(!empty($item['ReturnPolicyDescription'])){
		$itemArray['ReturnPolicy']['Description'] = $item['ReturnPolicyDescription'];
	    }
	    if(!empty($item['ReturnPolicyReturnsAcceptedOption'])){
		$itemArray['ReturnPolicy']['ReturnsAcceptedOption'] = $item['ReturnPolicyReturnsAcceptedOption'];
	    }
	    if(!empty($item['SecondaryCategoryCategoryID'])){
		$itemArray['SecondaryCategory']['CategoryID'] = $item['SecondaryCategoryCategoryID'];
	    }
	    //$itemArray['ShippingDetails']['ShippingType'] = $item['ShippingType'];
	    if(!empty($item['ShippingServiceOptions']) && is_array($item['ShippingServiceOptions'])){
		$i = 0;
		foreach($item['ShippingServiceOptions'] as $s){
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['FreeShipping'] = $s['FreeShipping'];
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingService'] = $s['ShippingService'];
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingServiceCost'] = $s['ShippingServiceCost'];
		    $itemArray['ShippingDetails']['ShippingServiceOptions'][$i]['ShippingServiceAdditionalCost'] = $s['ShippingServiceAdditionalCost'];
		    $i++;
		}
	    }
	    if(!empty($item['InternationalShippingServiceOption']) && is_array($item['InternationalShippingServiceOption'])){
		$j = 0;
		foreach($item['InternationalShippingServiceOption'] as $i){
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingService'] = $i['ShippingService'];
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingServiceCost'] = $i['ShippingServiceCost'];
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingServiceAdditionalCost'] = $i['ShippingServiceAdditionalCost'];
		    if(!empty($i['ShipToLocation'])){
			if(strpos($i['ShipToLocation'], ',') != false){
			    //echo "test1";
			    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShipToLocation'] = explode(',', $i['ShipToLocation']);
			}else{
			    //echo "test2";
			    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShipToLocation'] = $i['ShipToLocation'];
			}
		    }
		    $j++;
		}
	    }
	    
	    //print_r($itemArray['ShippingDetails']['InternationalShippingServiceOption']);
	    //exit;
	    //ShipToLocations
	    $itemArray['Site'] = $item['Site'];
	    $itemArray['SKU'] = $item['SKU'];
	    if(!empty($item['StartPrice']) && $item['StartPrice'] != 0){
		$itemArray['StartPrice'] = $item['StartPrice'];
	    }
	    if(!empty($item['StoreCategory2ID'])){
		$itemArray['Storefront']['StoreCategory2ID'] = $item['StoreCategory2ID'];
	    }
	    if(!empty($item['StoreCategoryID'])){
		$itemArray['Storefront']['StoreCategoryID'] = $item['StoreCategoryID'];
	    }
	    if(!empty($item['SubTitle'])){
		$itemArray['SubTitle'] = $item['SubTitle'];
	    }
	    $itemArray['Title'] = $item['Title'];
	   
	    //print_r($itemArray);
	    $params = array('Version' => $Version,
			    'Item' => $itemArray);
	    
	    $results = $client->AddItem($params);
	    print_r($results);
	    if(!empty($results->Errors)){
		if(is_array($results->Errors)){
		    $temp = '';
		    foreach($results->Errors as $error){
			echo $error->ShortMessage." : ";
			echo $error->LongMessage."<br>";
			$temp .= $error->LongMessage;
		    }
		    $this->log("upload", $temp, "error");
		}else{
		    echo $results->Errors->ShortMessage." : ";
		    echo $results->Errors->LongMessage."<br>";
		    $this->log("upload", $results->Errors->LongMessage, "error");
		}
	    }else{
		//echo $results->ItemID;
		//echo $results->StartTime;
		//echo $results->EndTime;
		$sql = "update items set ItemID = '".$results->ItemID."',Status='1',StartTime='".$results->StartTime."',
		EndTime='".$results->EndTime."' where Id = '".$item['Id']."'";
		echo $sql;
		$result = mysql_query($sql);
		$this->log("upload", $sql);
	    }
	    //----------   debug --------------------------------
	    //print "Request: \n".$client->__getLastRequest() ."\n";
	    //print "Response: \n".$client->__getLastResponse()."\n";
	    $this->saveFetchData("addItem-Request-".date("YmdHis").".xml", $client->__getLastRequest());
	    $this->saveFetchData("addItem-Response-".date("YmdHis").".xml", $client->__getLastResponse());
        } catch (SOAPFault $f) {
            print $f; // error handling
        }
    }
    
    //-------------------------   Listing ---------------------------------------------------------------------
    /*
    	Status
	0 : uploading
	1 : selling
	2 : sold
	3 : sold end
    */
    private function checkItem($itemId){
	$sql = "select count(*) as count from items where ItemID = '$itemId->ItemID'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	return $row['count'];
    }
    
    private function insertItem($item, $userId){
	$sql = "insert into items (ItemID,AutoPay,BuyItNowPrice,Country,Currency,Description,DispatchTimeMax,StartTime,
	EndTime,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,PostalCode,CategoryID,CategoryName,
	Quantity,CurrentPrice,QuantitySold,ListingStatus,ShippingType,Site,SKU,StartPrice,StoreCategory2ID,
	StoreCategoryID,Title,UserID) values ('".mysql_escape_string($item->ItemID)."','".mysql_escape_string($item->AutoPay)."',
	'".mysql_escape_string($item->BuyItNowPrice)."','".mysql_escape_string($item->Country)."','".mysql_escape_string($item->Currency)."',
	'".mysql_escape_string($item->Description)."','".mysql_escape_string($item->DispatchTimeMax)."','".mysql_escape_string($item->ListingDetails->StartTime)."',
	'".mysql_escape_string($item->ListingDetails->EndTime)."','".mysql_escape_string($item->ListingDuration)."','".mysql_escape_string($item->ListingType)."',
	'".mysql_escape_string($item->Location)."','".mysql_escape_string($item->PaymentMethods)."','".mysql_escape_string($item->PayPalEmailAddress)."',
	'".mysql_escape_string($item->PostalCode)."','".mysql_escape_string($item->PrimaryCategory->CategoryID)."','".mysql_escape_string($item->PrimaryCategory->CategoryName)."',
	'".mysql_escape_string($item->Quantity)."','".mysql_escape_string($item->SellingStatus->CurrentPrice)."','".mysql_escape_string($item->SellingStatus->QuantitySold)."',
	'".mysql_escape_string($item->SellingStatus->ListingStatus)."','".mysql_escape_string($item->ShippingDetails->ShippingType)."','".mysql_escape_string($item->Site)."',
	'".mysql_escape_string($item->SKU)."','".mysql_escape_string($item->StartPrice)."','".mysql_escape_string($item->Storefront->StoreCategory2ID)."',
	'".mysql_escape_string($item->Storefront->StoreCategoryID)."','".mysql_escape_string($item->Title)."','".mysql_escape_string($userId)."')";
	echo $sql;
	echo "<br>";
	$result = mysql_query($sql, eBayListing::$database_connect);
    }
    
    private function updateItem($item, $userId){
	/*
	$sql = "update items set AutoPay='".mysql_escape_string($item->AutoPay)."',AutoPay='".mysql_escape_string($item->AutoPay)."',
	BuyItNowPrice='".mysql_escape_string($item->BuyItNowPrice)."',Country='".mysql_escape_string($item->Country)."',
	Currency='".mysql_escape_string($item->Currency)."',Description='".mysql_escape_string($item->Description)."',
	DispatchTimeMax='".mysql_escape_string($item->DispatchTimeMax)."',StartTime='".mysql_escape_string($item->ListingDetails->StartTime)."',
	EndTime='".mysql_escape_string($item->ListingDetails->EndTime)."',ListingDuration='".mysql_escape_string($item->ListingDuration)."',
	ListingType='".mysql_escape_string($item->ListingType)."',Location='".mysql_escape_string($item->Location)."',
	PaymentMethods='".mysql_escape_string($item->PaymentMethods)."',PayPalEmailAddress='".mysql_escape_string($item->PayPalEmailAddress)."',
	PostalCode='".mysql_escape_string($item->PostalCode)."',CategoryID='".mysql_escape_string($item->PrimaryCategory->CategoryID)."',
	CategoryName='".mysql_escape_string($item->PrimaryCategory->CategoryName)."',Quantity='".mysql_escape_string($item->Quantity)."',
	CurrentPrice='".mysql_escape_string($item->SellingStatus->CurrentPrice)."',QuantitySold='".mysql_escape_string($item->SellingStatus->QuantitySold)."',
	ListingStatus='".mysql_escape_string($item->SellingStatus->ListingStatus)."',ShippingType='".mysql_escape_string($item->ShippingDetails->ShippingType)."',
	Site='".mysql_escape_string($item->Site)."',SKU='".mysql_escape_string($item->SKU)."',
	StartPrice='".mysql_escape_string($item->StartPrice)."',StoreCategory2ID='".mysql_escape_string($item->Storefront->StoreCategory2ID)."',
	StoreCategoryID='".mysql_escape_string($item->Storefront->StoreCategoryID)."',Title='".mysql_escape_string($item->Title)."',
	UserID='".mysql_escape_string($userId)."' where ItemID = '".$item->ItemID."'";
	echo $sql;
	echo "<br>";
	*/
	switch($item->SellingStatus->ListingStatus){
	    case "Active":
		$Status = 2;
	    break;
	
	    case "Completed":
		$Status = 3;
	    break;
	
	    case "Ended":
		$Status = 2;
	    break;
	}
	$sql = "update items set CurrentPrice='".$item->SellingStatus->CurrentPrice."',QuantitySold='".$item->SellingStatus->QuantitySold."',ListingStatus='".$item->SellingStatus->ListingStatus.",Status='".$Status."'' 
	where ItemID = '".$item->ItemID."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
    }
    
    private function deleteShippingServiceOptions($itemId, $d = false){
	if($d == true){
	    $sql = "delete from shipping_service_options where ItemID = '".$itemId."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	}
    }
    
    private function insertShippingServiceOptions($itemID, $shippingServiceOptions){
	$sql = "insert into shipping_service_options (ItemID,ShippingService,ShippingServiceCost,FreeShipping) values 
	('".$itemID."','".$shippingServiceOptions->ShippingService."','".$shippingServiceOptions->ShippingServiceCost."','".$shippingServiceOptions->FreeShipping."')";
	echo $sql;
	echo "<br>";
	$result = mysql_query($sql, eBayListing::$database_connect);
    }
    
    private function deleteInternationalShippingServiceOption($itemId, $d = false){
	if($d == true){
	    $sql = "delete from international_shipping_service_option where ItemID = '".$itemId."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	}
    }
    
    private function insertInternationalShippingServiceOption($itemID, $internationalShippingServiceOption){
	$sql = "insert into international_shipping_service_option (ItemID,ShippingService,ShippingServiceCost) values 
	('".$itemID."','".$internationalShippingServiceOption->ShippingService."','".$internationalShippingServiceOption->ShippingServiceCost."')";
	echo $sql;
	echo "<br>";
	$result = mysql_query($sql, eBayListing::$database_connect);
    }
    
    
    //http://127.0.0.1:6666/eBayBO/eBaylisting/service.php?action=getSellerList&EndTimeFrom=2009-05-29&EndTimeTo=2009-05-30
    public function getSellerList(){
	try {
	    $client = new eBaySOAP($this->session);

	    $Version = '607';
	    $DetailLevel = "ReturnAll";
	    $Pagination = array("EntriesPerPage"=> 200,
				"PageNumber"=> 1);
	    
	    $EndTimeFrom = $_GET['EndTimeFrom'];
	    $EndTimeTo = $_GET['EndTimeTo'];
	    
	    $StartTimeFrom = $_GET['StartTimeFrom'];
	    $StartTimeTo = $_GET['StartTimeTo'];
	    
	    $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel, 'Pagination' => $Pagination, 'StartTimeFrom' => $StartTimeFrom, 'StartTimeTo' => $StartTimeTo/*'EndTimeFrom' => $EndTimeFrom, 'EndTimeTo' => $EndTimeTo*/);
	    //$results = $client->GetSellerList($params);
	    
	    //----------   debug --------------------------------
	    //print "Request: \n".$client->__getLastRequest() ."\n";
	    //print "Response: \n".$client->__getLastResponse()."\n";    
	    $TotalNumberOfPages = 1;
	    
	    for($i=1; $i <= $TotalNumberOfPages; $i++){
		$Pagination = array("EntriesPerPage"=> 200,
				    "PageNumber"=> $i);
		$params = array('Version' => $Version, 'DetailLevel' => $DetailLevel, 'Pagination' => $Pagination, 'EndTimeFrom' => $EndTimeFrom, 'EndTimeTo' => $EndTimeTo);
		$results = $client->GetSellerList($params);
		
		//$this->saveFetchData("getSellerList-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		
		$TotalNumberOfPages = $results->PaginationResult->TotalNumberOfPages;
		if($results->PaginationResult->TotalNumberOfPages == 0)
		    return 0;
	    
		if(is_array($results->ItemArray->Item)){
		    foreach($results->ItemArray->Item as $item){
			if($this->checkItem($item->ItemID) == 0){
			    $this->insertItem($item, $results->Seller->UserID);
			}else{
			    $this->updateItem($item, $results->Seller->UserID);
			}
			/*
			//ShippingServiceOptions
			$d = true;
			if(is_array($item->ShippingDetails->ShippingServiceOptions)){
			    foreach($item->ShippingDetails->ShippingServiceOptions as $shippingServiceOptions){
				$this->deleteShippingServiceOptions($item->ItemID, $d);
				$this->insertShippingServiceOptions($item->ItemID, $shippingServiceOptions);
				$d = false;
			    }
			}else{
			    $this->deleteShippingServiceOptions($item->ItemID, $d);
			    $this->insertShippingServiceOptions($item->ItemID, $item->ShippingDetails->ShippingServiceOptions);
			}
			
			//InternationalShippingServiceOption
			if(is_array($item->ShippingDetails->InternationalShippingServiceOption)){
			    foreach($item->ShippingDetails->InternationalShippingServiceOption as $internationalShippingServiceOption){
				$this->deleteInternationalShippingServiceOption($item->ItemID, $d);
				$this->insertInternationalShippingServiceOption($item->ItemID, $internationalShippingServiceOption);
				$d = false;
			    }
			}else{
			    $this->deleteInternationalShippingServiceOption($item->ItemID, $d);
			    $this->insertInternationalShippingServiceOption($item->ItemID, $item->ShippingDetails->InternationalShippingServiceOption);
			}
			*/
		    }
		}else{
		    if($this->checkItem($results->ItemArray->Item->ItemID) == 0){
			$this->insertItem($results->ItemArray->Item, $results->Seller->UserID);
		    }else{
			$this->updateItem($results->ItemArray->Item, $results->Seller->UserID);
		    }
		    /*
		    //ShippingServiceOptions
		    $d = true;
		    if(is_array($results->ItemArray->Item->ShippingDetails->ShippingServiceOptions)){
			foreach($results->ItemArray->Item->ShippingDetails->ShippingServiceOptions as $shippingServiceOptions){
			    $this->deleteShippingServiceOptions($results->ItemArray->Item->ItemID, $d);
			    $this->insertShippingServiceOptions($results->ItemArray->Item->ItemID, $shippingServiceOptions);
			    $d = false;
			}
		    }else{
			$this->deleteShippingServiceOptions($results->ItemArray->Item->ItemID, $d);
			$this->insertShippingServiceOptions($results->ItemArray->Item->ItemID, $results->ItemArray->Item->ShippingDetails->ShippingServiceOptions);
		    }
		    
		    //InternationalShippingServiceOption
		    if(is_array($results->ItemArray->Item->ShippingDetails->InternationalShippingServiceOption)){
			foreach($results->ItemArray->Item->ShippingDetails->InternationalShippingServiceOption as $internationalShippingServiceOption){
			    $this->deleteInternationalShippingServiceOption($results->ItemArray->Item->ItemID, $d);
			    $this->insertInternationalShippingServiceOption($results->ItemArray->Item->ItemID, $internationalShippingServiceOption);
			    $d = false;
			}
		    }else{
			$this->deleteInternationalShippingServiceOption($results->ItemArray->Item->ItemID, $d);
			$this->insertInternationalShippingServiceOption($results->ItemArray->Item->ItemID, $results->ItemArray->Item->ShippingDetails->InternationalShippingServiceOption);
		    }
		    */
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
	    $sql_1 = "select p.host,p.port from proxy as p left join account_to_proxy as atp on p.id = atp.proxy_id where atp.account_id = '".$row['id']."'";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $row_1 = mysql_fetch_assoc($result_1);
	    $this->session = $this->configEbay($row['token'], $row_1['host'], $row_1['port']);
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
    
    public function saveFooter(){
	$sql_1 = "select count(*) as num from account_footer where accountId = '".$this->account_id."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$row_1 = mysql_fetch_assoc($result_1);
	
	if($row_1['num'] > 0){
	    $sql_2 = "update account_footer set footer = '".$_POST['elm1']."' where accountId = '".$this->account_id."'";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);	
	}else{
	    $sql_2 = "insert into account_footer (accountId,footer) values ('".$this->account_id."','".$_POST['elm1']."')";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);	
	}
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
	    setcookie("account_id", $row['id'], time() + (60 * 60 * 24));
	    setcookie("role", $row['role'], time() + (60 * 60 * 24));
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
	token='".$_POST['token']."',status='".$_POST['status']."' where id = '".$_POST['id']."'";
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
	echo $result;
    }
    
    //------------------------------- Log ---------------------------------------------------------------
    private function log($type, $content, $level = 'normal'){
	$sql = "insert into log (level,type,content,account_id) values('".$level."','".$type."','".$content."','".$this->account_id."')";
	//echo $sql;
	$result = mysql_query($sql, eBayListing::$database_connect);
    }
    
    public function getUploadLog(){
	$array = array();
	
	$sql = "select count(*) as num from log where account_id = '".$this->account_id."' and type = 'upload'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$totalCount = $row['num'];
	
	if(empty($_POST['start']) && empty($_POST['limit'])){
	    $_POST['start'] = 0;
	    $_POST['limit'] = 20;
	}
	    
	$sql = "select * from log where account_id = '".$this->account_id."' and type = 'upload' limit ".$_POST['start'].",".$_POST['limit'];
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
	    $array[] = $row;
	}
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$array));
	mysql_free_result($result);
    }
    public function __destruct(){
        mysql_close(eBayListing::$database_connect);
    }
}

$service = new eBayListing();
$service->setAccount(1);
$acton = (!empty($_GET['action'])?$_GET['action']:$argv[1]);
if(in_array($acton, array("getAllSiteShippingServiceDetails", "getAllSiteShippingLocationDetails", "getAllCategory2CS", "getAllAttributesCS"))){
    $service->$acton();
}else{
    $service->configEbay();
    $service->$acton();
}
?>