<?php

require_once 'eBaySOAP.php';

class eBayListing{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaylisting';
    const GATEWAY_SOAP = 'https://api.sandbox.ebay.com/wsapi';
    
    const EBAY_BO_SERVICE = 'http://127.0.0.1/eBayBO/service.phpss';
    const INVENTORY_SERVICE = 'http://127.0.0.1/einv2/service.php';
    private $startTime;
    private $endTime;
    private $session;
    
    public function __construct(){
	session_start();
	$_SESSION['account_id'] = 1;
	
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
       
	$sql = "select token from account where id = '".$_SESSION['account_id']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	
	
	$sql_1 = "select p.host,p.port from proxy as p left join account_to_proxy as atp on p.id = atp.proxy_id where atp.account_id = '".$_SESSION['account_id']."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$row_1 = mysql_fetch_assoc($result_1);

	$this->session = $this->configEbay($row['token'], $row_1['host'], $row_1['port']);
    }
    
    private function configEbay($token='', $proxy_host='', $proxy_port=''){
    	
	// Load developer-specific configuration data from ini file
	$config = parse_ini_file('ebay.ini', true);
	$site = $config['settings']['site'];
	//$compatibilityLevel = $config['settings']['compatibilityLevel'];
	
	$dev = $config[$site]['devId'];
	$app = $config[$site]['appId'];
	$cert = $config[$site]['cert'];
	$token = (empty($token)?$config[$site]['authToken']:$token);
	$location = $config[$site]['gatewaySOAP'];
	//$location = self::GATEWAY_SOAP;
	
	// Create and configure session
	$session = new eBaySession($dev, $app, $cert, $proxy_host, $proxy_port);
	$session->token = $token;
	$session->site = 0; // 0 = US;
	$session->location = $location;
	
	return $session;
    }
    
    private function saveFetchData($file_name, $data){
	//file_put_contents("/export/eBayBO/eBayListing/log/".$file_name, $data);
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
	    $this->saveFetchData("getCategoryFeatures-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
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
	$sql = "select id,name from listing_duration_type";
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
    
    public function getListingDuration(){
	$sql = "select id,name from listing_duration where id = '".$_POST['id']."'";
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
    
    public function addItems(){
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
    
    public function __destruct(){
        mysql_close(eBayListing::$database_connect);
    }
}

$service = new eBayListing();
$acton = (!empty($_GET['action'])?$_GET['action']:$argv[1]);
$service->$acton();

?>