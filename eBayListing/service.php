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
    
    
    public function getCategories($siteId){
        try {
                $client = new eBaySOAP($this->session);

                $CategorySiteID = $siteId;
                $Version = '607';
                $DetailLevel = "ReturnAll";
             
                $params = array('Version' => $Version, 'DetailLevel' => $DetailLevel, 'CategorySiteID' => $CategorySiteID);
                $results = $client->GetCategories($params);
                //----------   debug --------------------------------
                print "Request: \n".$client->__getLastRequest() ."\n";
                print "Response: \n".$client->__getLastResponse()."\n";
                
		return $results;
                
        } catch (SOAPFault $f) {
                print $f; // error handling
        }
    }
    
    public function checkCategoriesVersion($siteId, $version){
        try {
                $client = new eBaySOAP($this->session);

                $CategorySiteID = $siteId;
                $Version = '607';
                $params = array('Version' => $Version, 'CategorySiteID' => $CategorySiteID);
                $results = $client->GetCategories($params);
                //----------   debug --------------------------------
                print "Request: \n".$client->__getLastRequest() ."\n";
                print "Response: \n".$client->__getLastResponse()."\n";
		return $results;
                exit;
        } catch (SOAPFault $f) {
                print $f; // error handling
        }
    }
    
    
    public function getAllCategories(){
	$sql = "select id,name,version from site where status = 1";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while ($row = mysql_fetch_assoc($result)){
	    $result = $this->checkCategoriesVersion($row['id'], $row['version']);
	    if($result){
		$this->getCategories($row['id']);
	    }else{
		echo $row['name']." categories no change<br>";
	    }
	}
    }
    
    public function __destruct(){
        mysql_close(eBayListing::$database_connect);
    }
}

$service = new eBayListing();
$service->getAllCategories();
?>