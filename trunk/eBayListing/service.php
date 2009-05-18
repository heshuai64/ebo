<?php

require_once 'eBaySOAP.php';

class eBayListing{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaylisting';
    const GATEWAY_SOAP = 'https://api.sandbox.ebay.com/wsapi';
    private $startTime;
    private $endTime;
    
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
    
    
    public function GetCategories(){
        $session = $this->configEbay($dev, $app, $cert, $token, $proxy_host, $proxy_port);
        try {
                $client = new eBaySOAP($session);

                $CategorySiteID = 100;
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
    
    
    public function __destruct(){
        mysql_close(eBayListing::$database_connect);
    }
}

?>