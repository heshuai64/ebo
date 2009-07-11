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
	$this->configEbay();
    }
    
    private function configEbay(){
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
	    $this->session->site = 0; // 0 = US;
	    $this->session->location = $location;
	    
	}
    }
    
    private function saveFetchData($file_name, $data){
	file_put_contents("/export/eBayBO/eBayListing/log/".$file_name, $data);
    }
    
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
	$InternationalService = "";
	if($_POST['InternationalService'] == "1"){
	    $InternationalService = "and InternationalService = 1";
	}else{
	    $InternationalService = "and InternationalService = 0";
	}
	
	//echo $InternationalService;
	//echo "\n";
	
	if($_POST['serviceType'] == "Flat"){
	    $sql = "select Description,ShippingService from shipping_service_details where  ServiceTypeFlat = 1 ".$InternationalService;
	}elseif($_POST['serviceType'] == "Calculated"){
	    $sql = "select Description,ShippingService from shipping_service_details where  ServiceTypeCalculated = 1 ".$InternationalService;
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
    
    public function geteBayDetailsByShippingServiceDetails(){
	try {
                $client = new eBaySOAP($this->session);
                $Version = '607';
                $DetailName = "ShippingServiceDetails";
             
                $params = array('Version' => $Version, 'DetailName' => $DetailName);
                $results = $client->GeteBayDetails($params);
                //print_r($results);
		//----------   debug --------------------------------
                //print "Request: \n".$client->__getLastRequest() ."\n";
                print "Response: \n".$client->__getLastResponse()."\n";
                //$this->saveFetchData("geteBayDetails-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		
		$ServiceTypeFlat = false;
		$ServiceTypeCalculated = false;
                foreach($results->ShippingServiceDetails as $shippingServiceDetail){
		    foreach($shippingServiceDetail->ServiceType as $serviceType){
			if($serviceType == "Flat"){
			    $ServiceTypeFlat = true;
			}elseif($serviceType == "Calculated"){
			    $ServiceTypeCalculated = true;
			}
		    }
		    
		    $ShippingPackageLetter = false;
		    $ShippingPackageLargeEnvelope = false;
		    $ShippingPackagePackageThickEnvelope = false;
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
		    
		    $sql = "insert into shipping_service_details (Description,InternationalService,ShippingService,
		    ShippingServiceID,ShippingTimeMax,ShippingTimeMin,ServiceTypeFlat,ServiceTypeCalculated,
		    ShippingPackageLetter,ShippingPackageLargeEnvelope,ShippingPackagePackageThickEnvelope,
		    ShippingCarrier,DimensionsRequired,WeightRequired) values ('".mysql_escape_string($shippingServiceDetail->Description)."',
		    '".$shippingServiceDetail->InternationalService."','".mysql_escape_string($shippingServiceDetail->ShippingService)."',
		    '".$shippingServiceDetail->ShippingServiceID."','".$shippingServiceDetail->ShippingTimeMax."',
		    '".$shippingServiceDetail->ShippingTimeMin."','".$ServiceTypeFlat."',
		    '".$ServiceTypeCalculated."','".$ShippingPackageLetter."',
		    '".$ShippingPackageLargeEnvelope."','".$ShippingPackagePackageThickEnvelope."',
		    '".$shippingServiceDetail->ShippingCarrier."','".$shippingServiceDetail->DimensionsRequired."',
		    '".$shippingServiceDetail->WeightRequired."')";
		    
		    echo $sql;
		    echo "<be>";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		}
		
		
        } catch (SOAPFault $f) {
                print $f; // error handling
        }
    }
    
    public function getAllSites(){
	$sql = "select * from site where status = '1'";
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
    
    public function addItem($item){
	/*
	英,美,法,澳,
	Europe/London       +0100
	America/New_York    -0400
	Europe/Paris        +0200
	Australia/Canberra  +1000
	
	Asia/Shanghai       +0800
	
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
	    <GalleryType> GalleryTypeCodeType </GalleryType>
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
	    
	    if(!empty($item['BuyItNowPrice'])){
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
		    $i++;
		}
	    }
	    if(!empty($item['InternationalShippingServiceOption']) && is_array($item['InternationalShippingServiceOption'])){
		$j = 0;
		foreach($item['ShippingServiceOptions'] as $i){
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingService'] = $i['ShippingService'];
		    $itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShippingServiceCost'] = $i['ShippingServiceCost'];
		    if(!empty($i['ShipToLocation'])){
			$itemArray['ShippingDetails']['InternationalShippingServiceOption'][$j]['ShipToLocation'] = $i['ShipToLocation'];
		    }
		    $j++;
		}
	    }
	    //ShipToLocations
	    $itemArray['Site'] = $item['Site'];
	    $itemArray['SKU'] = $item['SKU'];
	    if(!empty($item['StartPrice'])){
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
		    foreach($results->Errors as $error){
			echo $error->ShortMessage." : ";
			echo $error->LongMessage."<br>";
		    }
		}else{
		    echo $results->Errors->ShortMessage." : ";
		    echo $results->Errors->LongMessage."<br>";
		}
	    }else{
		echo $results->ItemID;
		echo $results->StartTime;
		echo $results->EndTime;
	    }
	    //----------   debug --------------------------------
	    //print "Request: \n".$client->__getLastRequest() ."\n";
	    //print "Response: \n".$client->__getLastResponse()."\n";
	    $this->saveFetchData("addItem-Request-".date("Y-m-d H:i:s").".xml", $client->__getLastRequest());
	    $this->saveFetchData("addItem-Response-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
        } catch (SOAPFault $f) {
            print $f; // error handling
        }
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
    
    public function saveItem(){
	/*
	ALTER TABLE `items` ADD `GalleryTypeFeatured` BOOL NOT NULL AFTER `HomePageFeatured` ,
	ADD `GalleryTypeGallery` BOOL NOT NULL AFTER `GalleryTypeFeatured` ,
	ADD `GalleryURL` VARCHAR( 255 ) NOT NULL AFTER `GalleryTypeGallery` ,
	ADD `PhotoDisplay` ENUM( "PicturePack", "SiteHostedPictureShow", "SuperSize", "SuperSizePictureShow", "VendorHostedPictureShow" ) NOT NULL AFTER `GalleryURL` ;
	
	CREATE TABLE `ebaylisting`.`PictureURL` (
	    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	    `ItemID` INT NOT NULL ,
	    `url` VARCHAR( 150 ) NOT NULL ,
	    INDEX ( `ItemID` )
	) ENGINE = MYISAM
	
	
	ALTER TABLE `shipping_service_options` DROP PRIMARY KEY
	ALTER TABLE `shipping_service_options` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
	ALTER TABLE `shipping_service_options` ADD INDEX ( `ItemID` )
	 
	ALTER TABLE `international_shipping_service_option` DROP INDEX `SKU`
	ALTER TABLE `international_shipping_service_option` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
	
	ALTER TABLE `items` ADD `ReturnPolicyDescription` TEXT NOT NULL AFTER `Quantity` ;
	
	ReturnPolicyReturnsAcceptedOption:ReturnsAccepted,ReturnsNotAccepted
	*/
	
	//ScheduleStartDate,ScheduleEndDate
	//Location,PostalCode
	//CurrentPrice
	//Site
	//ShippingType
	//ListingEnhancement
	if(!empty($_POST['UseStandardFooter']) && $_POST['UseStandardFooter'] == 1){
	    $sql = "select footer from account_footer where accountId = '".$this->account_id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    $_POST['Description'] .= $row['footer'];
	}
	//StartTime,EndTime
	//$PaymentMethods = ($_POST['PayPalPayment'] == 1)?'PayPal':'';
	$sql = "insert into items (BuyItNowPrice,Country,Currency,Description,DispatchTimeMax,ScheduleStartDate,
	ScheduleEndDate,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,PostalCode,
	PrimaryCategoryCategoryID,SecondaryCategoryCategoryID,Quantity,ReservePrice,
	ShippingType,Site,SKU,StartPrice,StoreCategory2ID,StoreCategoryID,SubTitle,Title,accountId,
	BoldTitle,Border,Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery) values (
	'".$_POST['BuyItNowPrice']."','CN','".$_POST['Currency']."',
	'".$_POST['Description']."','".$_POST['DispatchTimeMax']."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."',
	'".$_POST['ListingDuration']."','".$_POST['ListingType']."','".$_POST['Location']."','PayPal',
	'".$_POST['PayPalEmailAddress']."','".$_POST['PostalCode']."',
	'".$_POST['PrimaryCategoryCategoryID']."','".$_POST['SecondaryCategoryCategoryID']."',
	'".$_POST['Quantity']."','".$_POST['ReservePrice']."','".$_POST['ShippingType']."',
	'".$_POST['Site']."','".$_POST['SKU']."','".$_POST['StartPrice']."','".$_POST['StoreCategory2ID']."',
	'".$_POST['StoreCategoryID']."','".$_POST['SubTitle']."',
	'".$_POST['Title']."','".$this->account_id."','".(empty($_POST['BoldTitle'])?0:1)."',
	'".(empty($_POST['Border'])?0:1)."','".(empty($_POST['Featured'])?0:1)."','".(empty($_POST['Highlight'])?0:1)."',
	'".(empty($_POST['HomePageFeatured'])?0:1)."','".(empty($_POST['GalleryTypeFeatured'])?0:1)."','".(empty($_POST['GalleryTypeGallery'])?0:1)."')";
	$result = mysql_query($sql, eBayListing::$database_connect);
	
	//echo $sql;
	//exit;
	
	$id = mysql_insert_id(eBayListing::$database_connect);
	
	$i = 1;
	while(!empty($_POST['picture_'.$i])){
	    $sql_1 = "insert into PictureURL (ItemID,url) values 
	    ('".$id."','".$_POST['picture_'.$i]."')";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $i++;
	}
	
	$i = 1;
	while(!empty($_POST['ShippingService-'.$i])){
	    $sql_1 = "insert into shipping_service_options (ItemID,FreeShipping,ShippingService,ShippingServiceCost) values
	    ('".$id."','".$_POST['FreeShipping-'.$i]."','".$_POST['ShippingService-'.$i]."','".$_POST['ShippingServiceCost-'.$i]."')";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $i++;
	}
	
	$i = 1;
	while(!empty($_POST['InternationalShippingService-'.$i])){
	    $sql_2 = "insert into international_shipping_service_option (ItemID,ShippingService,ShippingServiceCost) values
	    ('".$id."','".$_POST['InternationalShippingService-'.$i]."','".$_POST['InternationalShippingServiceCost-'.$i]."')";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    $i++;
	}
	
	if($result && $result_1 && $result_2){
		echo 	'{success: true}';
		
	}else{
		echo 	'{success: false,
			  errors: {message: "can\'t create."}
			}';
	}
    }
    
    public function uploadItem(){
	/*
	CREATE TABLE `ebaylisting`.`schedule` (
	`item_id` INT NOT NULL ,
	`day` VARCHAR( 3 ) NOT NULL ,
	`time` TIME NOT NULL ,
	PRIMARY KEY ( `item_id` )
	) ENGINE = MYISAM
	*/
	
	$day = date("D");
	$time = date("H:i:s");
	
	//$sql = "select item_id from schedule where day = '".$day."' and time ='".$time."'";
	$sql = "select item_id from schedule where day = '".$day."'";

	$result = mysql_query($sql);
	while($row = mysql_fetch_assoc($result)){
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
	    
	    $sql_4 = "select * from PictureURL where ItemID = '".$row['item_id']."'";
	    //echo $sql_4;
	    //echo "<br>";
	    $result_4 = mysql_query($sql_4);
	    $PictureURL = array();
	    while($row_4 = mysql_fetch_assoc($result_4)){
		$PictureURL[] = $row_4['url'];
	    } 
	    
	    $row_1['ShippingServiceOptions'] = $ShippingServiceOptions;
	    $row_1['InternationalShippingServiceOption'] = $InternationalShippingServiceOption;
	    $row_1['PictureURL'] = $PictureURL;
	    
	    //print_r($row_1);
	    //exit;
	    $this->addItem($row_1);
	}
    }
    
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
		    }
		}else{
		    if($this->checkItem($results->ItemArray->Item->ItemID) == 0){
			$this->insertItem($results->ItemArray->Item, $results->Seller->UserID);
		    }else{
			$this->updateItem($results->ItemArray->Item, $results->Seller->UserID);
		    }
		    
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
    
    public function login(){
	$sql = "select id from account where name = '".$_POST['name']."' and password = '".$_POST['password']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	
	//$_SESSION['account_id'] = $row['id'];
	if(!empty($row['id'])){
	    setcookie("account_id", $row['id'], time() + (60 * 60 * 24));
	    echo "{success: true}";
	}else{
	    echo "{success: false}";
	}
    }
    
    public function logout(){
	unset($_COOKIE['account_id']);
    }
    
    public function __destruct(){
        mysql_close(eBayListing::$database_connect);
    }
}

$service = new eBayListing();
$service->setAccount(1);
$acton = (!empty($_GET['action'])?$_GET['action']:$argv[1]);
$service->$acton();

?>