<?php
class QoReports {
	private static $memcache_connect;
	const MEMCACHE_HOST = '127.0.0.1';
	const MEMCACHE_PORT = 11211;
	
	private $os;

	public function __construct($os){
		$this->os = $os;
		QoReports::$memcache_connect = new Memcache;
		QoReports::$memcache_connect->connect(self::MEMCACHE_HOST, self::MEMCACHE_PORT);
	}
	
	public function getSeller(){
		$seller_array = QoReports::$memcache_connect->get("seller");
		if($seller_array == false){
			$sql = "select id as id, id as name from qo_ebay_seller";
			$result = mysql_query($sql);
			$seller_array = array();
			while($row = mysql_fetch_assoc($result)){
				$seller_array[] = $row;
			}
			QoReports::$memcache_connect->set("seller", $seller_array);
			mysql_free_result($result);
		}
		echo json_encode($seller_array);
	}
	
}