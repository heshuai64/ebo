<?php
class Check{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaybo';
    
    public function __construct(){
        Check::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

        if (!Check::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(Check::$database_connect);
            exit;
        }
	
        mysql_query("SET NAMES 'UTF8'", Check::$database_connect);
	
        if (!mysql_select_db(self::DATABASE_NAME, Check::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(Check::$database_connect);
            exit;
        }
    }
    
    public function checkProxy($seller, $host, $port){
        $url = 'http://www.google.com';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
        curl_setopt($ch, CURLOPT_PROXY, $host.':'.$port);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_exec($ch);
        $curl_info = curl_getinfo($ch);
        curl_close($ch);
        echo date("Y-m-d H:i:s")." ".$seller.":";
        echo ($curl_info['http_code'] != 200)?'<font color="red">Error</font>'.$curl_info['http_code']."<br>":$curl_info['http_code']."<br>";
    }
    
    public function checkAllProxy(){
        $sql = "select * from qo_ebay_proxy";
        $result = mysql_query($sql, Check::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $this->checkProxy($row['ebay_seller_id'], $row['proxy_host'], $row['proxy_port']);
        }
        echo "<br>";
    }
    
    public function __destruct(){
        mysql_close(Check::$database_connect);
    }
}


if(!empty($argv[1])){
    $check = new Check();
    $action = $argv[1];
    $check->$action();
}
?> 