<?php

require_once 'eBaySOAP.php';

class eBayListing{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '';
    const DATABASE_NAME = 'ebaylisting';
    const GATEWAY_SOAP = 'https://api.sandbox.ebay.com/wsapi';
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
	file_put_contents("/export/eBayBO/eBayListing/log".$file_name, $data);
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
		$this->saveFetchData("/checkCategoriesVersion-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		
		if($categoryVersion < $results->CategoryVersion){
		    $sql = "update site set version = '".$results->CategoryVersion."' where id = '".$siteId."'";
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
                $this->saveFetchData("/getCategories-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
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
	$sql = "select id,name,version from site where status = 1";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while ($row = mysql_fetch_assoc($result)){
	    $service_result = $this->checkCategoriesVersion($row['id'], $row['version']);
	    if($service_result){
		$this->getCategories($row['id']);
	    }else{
		echo $row['name']." categories no change<br>";
	    }
	}
    }
    
    private function getStoreCategories($userID){
	try {
                $client = new eBaySOAP($this->session);

                $CategoryStructureOnly = true;
                $Version = '607';
		$UserID = $userID;
		
                $params = array('Version' => $Version, 'CategoryStructureOnly' => $CategoryStructureOnly, 'UserID' => $UserID);
                $results = $client->GetStore($params);
                //----------   debug --------------------------------
                //print "Request: \n".$client->__getLastRequest() ."\n";
                //print "Response: \n".$client->__getLastResponse()."\n";
                $this->saveFetchData("/getStoreCategories-".date("Y-m-d H:i:s").".xml", $client->__getLastResponse());
		foreach($results->Store->CustomCategories->CustomCategory as $customCategory){
		    $level = 1;
		    $sql = "INSERT INTO `account_categories` (`CategoryID` ,`CategoryLevel` ,`Name` ,`Order` ,`UserID`) VALUES ('".$customCategory->CategoryID."','".$level."','".$customCategory->Name."','".$customCategory->Order."','".$userID."')";
		    echo $sql."<br>\n";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		    
		    if(is_array($customCategory->ChildCategory)){
			$twoChildCategories = $customCategory->ChildCategory;
			//two level
			foreach($twoChildCategories as $twoChildCategory){
			    $level = 2;
			    $sql = "INSERT INTO `account_categories` (`CategoryID` ,`CategoryLevel` ,`Name` ,`Order` ,`UserID`) VALUES ('".$twoChildCategory->CategoryID."','".$level."','".$twoChildCategory->Name."','".$twoChildCategory->Order."','".$userID."')";
			    echo $sql."<br>\n";
			    $result = mysql_query($sql, eBayListing::$database_connect);
			    
			    //three leve
			    if(is_array($twoChildCategory->ChildCategory)){
				$threeChildCategories = $twoChildCategory->ChildCategory;
				foreach($threeChildCategories as $threeChildCategory){
				    $level = 3;
				    $sql = "INSERT INTO `account_categories` (`CategoryID` ,`CategoryLevel` ,`Name` ,`Order` ,`UserID`) VALUES ('".$threeChildCategory->CategoryID."','".$level."','".$threeChildCategory->Name."','".$threeChildCategory->Order."','".$userID."')";
				    echo $sql."<br>\n";
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
	
    }
    
    public function addItems(){
	/*
	 
	<BuyItNowPrice currencyID="CurrencyCodeType"> AmountType (double) </BuyItNowPrice>
 
	
	<Currency> CurrencyCodeType </Currency>
	<Description> string </Description>


	<ListingDuration> token </ListingDuration>
	
	
	<ListingType> ListingTypeCodeType </ListingType>
	//Auction,Chinese,Dutch,FixedPriceItem,StoresFixedPrice
	
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


	<PrimaryCategory> CategoryType 
	    <CategoryID> string </CategoryID>
	</PrimaryCategory>


	<Quantity> int </Quantity>

	<ScheduleTime> dateTime </ScheduleTime>
	
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
    
    public function __destruct(){
        mysql_close(eBayListing::$database_connect);
    }
}

$service = new eBayListing();
$acton = (!empty($_GET['action'])?$_GET['action']:$argv[1]);
$service->$acton();

?>