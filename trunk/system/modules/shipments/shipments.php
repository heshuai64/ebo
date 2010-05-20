<?php
class QoShipments {
	
	private $os;
	//private $inventory_service_address = 'http://192.168.1.169:8080/inventory/service.php';
        private $inventory_service_address = 'http://127.0.0.1:6666/inventory/service.php';
	private $email_service_address = 'http://127.0.0.1/eBayBO/service.php';
	private static $memcache_connect;
	const MEMCACHE_HOST = '127.0.0.1';
	const MEMCACHE_PORT = 11211;
	const ACTIVE_MQ = "tcp://127.0.0.1:61613";
        private $message;
        
	public function __construct($os){
		$this->os = $os;
		QoShipments::$memcache_connect = new Memcache;
		QoShipments::$memcache_connect->connect(self::MEMCACHE_HOST, self::MEMCACHE_PORT);
	}
        
        private function getService($request){
        
            //$request =  'http://search.yahooapis.com/ImageSearchService/V1/imageSearch?appid=YahooDemo&query='.urlencode('Al Gore').'&results=1';
            
            // Make the request
            $json = file_get_contents($request);
            //var_dump($json);
            //echo $request;
            // Retrieve HTTP status code
            list($version,$status_code,$msg) = explode(' ',$http_response_header[0], 3);
            
            // Check the HTTP Status code
            switch($status_code) {
                    case 200:
                            return $json;
                            // Success
                            break;
                    case 503:
                            echo('Your call to Web Services failed and returned an HTTP status of 503. That means: Service unavailable. An internal problem prevented us from returning data to you.');
                            break;
                    case 403:
                            echo('Your call to Web Services failed and returned an HTTP status of 403. That means: Forbidden. You do not have permission to access this resource, or are over your rate limit.');
                            break;
                    case 400:
                            // You may want to fall through here and read the specific XML error
                            echo('Your call to Web Services failed and returned an HTTP status of 400. That means:  Bad request. The parameters passed to the service did not match as expected. The exact error is returned in the JSON response.');
                            break;
                    default:
                            echo('Your call to Web Services returned an unexpected HTTP status of:' . $status_code);
                            return false;
            }
        }
    
        public function searchShipment(){
            $where = " where 1 = 1 ";   
            
            if(!empty($_POST['id'])){
                    $where .= " and s.id like '%".$_POST['id']."%'";
            }
            
            if(!empty($_POST['ordersId'])){
                    $where .= " and s.ordersId like '%".$_POST['ordersId']."%'";
            }
            
            if(!empty($_POST['shippingMethod'])){
                    $where .= " and s.shippingMethod = '".$_POST['shippingMethod']."'";
            }
            
	    if(!empty($_POST['shipmentReason'])){
		$where .= " and s.shipmentReason = '".$_POST['shipmentReason']."'";
	    }
	    
            if(!empty($_POST['sellerId'])){
                    $where .= " and o.sellerId = '".$_POST['sellerId']."'";
            }
            
            if(!empty($_POST['shipToName'])){
                    $where .= " and s.shipToName like '%".$_POST['shipToName']."%'";
            }
	    
	    if(!empty($_POST['shipToEmail'])){
		    $where .= " and s.shipToEmail = '".$_POST['shipToEmail']."'";
	    }
            
            if(!empty($_POST['shipToAddressLine'])){
                    $where .= " and (s.shipToAddressLine1 like '%".$_POST['shipToAddressLine']."%' or s.shipToAddressLine2 like '%".$_POST['shipToAddressLine']."%')";
            }
                        
            if(!empty($_POST['postalReferenceNo'])){
                    $where .= " and s.postalReferenceNo like '%".$_POST['postalReferenceNo']."%'";
            }
            
            if(!empty($_POST['status'])){
                    $where .= " and s.status = '".$_POST['status']."'";
            }
            
            if(!empty($_POST['itemId'])){
                    $where_item .= " and sd.itemId like '%".$_POST['itemId']."%'";
            }
            
            if(!empty($_POST['itemTitle'])){
                    $where_item .= " and sd.itemTitle like '%".$_POST['itemTitle']."%'";
            }
            
            if(!empty($_POST['skuId'])){
                    $where_sku .= " and sd.skuId like '%".$_POST['skuId']."%'";
            }
            
            if(!empty($_POST['skuTitle'])){
                    $where_sku .= " and sd.skuTitle like '%".$_POST['skuTitle']."%'";
            }
            
            if(!empty($_POST['createdOnFrom'])){
                    $where .= " and s.createdOn > '".$_POST['createdOnFrom']."'";
            }
            
            if(!empty($_POST['createdOnTo'])){
                    $where .= " and s.createdOn < '".$_POST['createdOnTo']."'";
            }
            
            if(!empty($_POST['packedOnFrom'])){
                    $where .= " and s.packedOn > '".$_POST['packedOnFrom']."'";
            }
            
            if(!empty($_POST['packedOnTo'])){
                    $where .= " and s.packedOn < '".$_POST['packedOnTo']."'";
            }
            
            if(!empty($_POST['shippedOnFrom'])){
                    $where .= " and s.shippedOn < '".$_POST['shippedOnFrom']."'";
            }
            
            if(!empty($_POST['shippedOnTo'])){
                    $where .= " and s.shippedOn < '".$_POST['shippedOnTo']."'";
            }
            
	    if(!empty($where_sku) && !empty($where_item)){
		$count_sql = "select count(*) as num from (select distinct sd.shipmentsId from (qo_shipments as s left join qo_orders as o on s.ordersId=o.id) 
		left join qo_shipments_detail as sd on s.id=sd.shipmentsId ".$where.$where_sku.$where_item.") as total";
		
		$data_sql = "select s.id,o.id as ordersId,s.shipToName,s.shipToEmail,o.sellerId,s.createdOn,s.packedOn,s.shippedOn,s.status 
		from (qo_shipments as s left join qo_orders as o on s.ordersId=o.id) 
		left join qo_shipments_detail as sd on s.id=sd.shipmentsId ".$where.$where_sku.$where_item." group by s.id order by s.id desc limit ".$_POST['start'].",".$_POST['limit'];
	    }elseif(!empty($where_sku)){
		$count_sql = "select count(*) as num from (select distinct sd.shipmentsId from (qo_shipments as s left join qo_orders as o on s.ordersId=o.id) 
		left join qo_shipments_detail as sd on s.id=sd.shipmentsId ".$where.$where_sku.") as total";
		
		$data_sql = "select s.id,o.id as ordersId,s.shipToName,s.shipToEmail,o.sellerId,s.createdOn,s.packedOn,s.shippedOn,s.status 
		from (qo_shipments as s left join qo_orders as o on s.ordersId=o.id) 
		left join qo_shipments_detail as sd on s.id=sd.shipmentsId ".$where.$where_sku." group by s.id order by s.id desc limit ".$_POST['start'].",".$_POST['limit'];
	    }elseif(!empty($where_item)){
		$count_sql = "select count(*) as num from (select distinct sd.shipmentsId from (qo_shipments as s left join qo_orders as o on s.ordersId=o.id) 
		left join qo_shipments_detail as sd on s.id=sd.shipmentsId ".$where.$where_item.") as total";
		
		$data_sql = "select s.id,o.id as ordersId,s.shipToName,s.shipToEmail,o.sellerId,s.createdOn,s.packedOn,s.shippedOn,s.status 
		from (qo_shipments as s left join qo_orders as o on s.ordersId=o.id) 
		left join qo_shipments_detail as sd on s.id=sd.shipmentsId ".$where.$where_item." group by s.id order by s.id desc limit ".$_POST['start'].",".$_POST['limit'];
	    }else{
		$count_sql = "select count(s.id) as num from qo_shipments as s left join qo_orders as o on s.ordersId=o.id ".$where;
		
		$data_sql = "select s.id,o.id as ordersId,s.shipToName,s.shipToEmail,o.sellerId,s.createdOn,s.packedOn,s.shippedOn,s.status 
		from qo_shipments as s left join qo_orders as o on s.ordersId=o.id ".$where." group by s.id order by s.id desc limit ".$_POST['start'].",".$_POST['limit'];
	    }
	    
	    //echo $count_sql;
	    //echo "\n";
	    //echo $data_sql;
	    //exit;
            $count_result = mysql_query($count_sql);
	    $count_row = mysql_fetch_assoc($count_result);
	    $totalCount = $count_row['num'];
	    
	    $data_result = mysql_query($data_sql);
            $transaction_array = array();
            while($data_row = mysql_fetch_assoc($data_result)){
                $transaction_array[] = $data_row;
            }
            //var_dump($order_array);
            echo json_encode(array('totalCount'=>$totalCount, 'records'=>$transaction_array));
            mysql_free_result($result);
        }
	
	function getShipmentInfo(){
		$sql = "select * from qo_shipments where id = '".$_GET['id']."'";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		//var_dump($row);
		echo '['.json_encode($row).']';
		mysql_free_result($result);
	}
	
        public function getShipmentDetailInfo(){
            $sql = "select * from qo_shipments_detail where id='".$_GET['id']."'";
            $result = mysql_query($sql);
            $row = mysql_fetch_assoc($result);
            echo '['.json_encode($row).']';
            mysql_free_result($result);
        }
        
        public function updateShipmentDetailInfo(){
		$sql = "update qo_shipments_detail set skuId='".$_POST['skuId']."',skuTitle='".$_POST['skuTitle']."',
		itemId='".$_POST['itemId']."',itemTitle='".$_POST['itemTitle']."',quantity='".$_POST['quantity']."' 
		where id = '".$_POST['id']."'";
		$result = mysql_query($sql);
		//echo $sql;
		if($result){
			echo 	1;
			
		}else{
			echo 0;
		}
	}
        
	public function getShipmentDetail(){
		$sql = "select sd.id,sd.shipmentsId,sd.skuId,sd.skuTitle,sd.itemId,sd.itemTitle,sd.quantity,sd.barCode,qi.galleryURL 
		from qo_shipments_detail as sd left join qo_items as qi on sd.itemId = qi.id where shipmentsId ='".$_GET['id']."'";
		$result = mysql_query($sql);
		$shipment_detail_array = array();
		while($row = mysql_fetch_assoc($result)){
			$shipment_detail_array[] = $row;
			$i++;
		}
		echo json_encode(array('totalCount'=>$i, 'records'=>$shipment_detail_array));
		mysql_free_result($result);
	}
	
	public function addShipmentDetail(){
		$sql = "insert into qo_shipments_detail (shipmentsId,skuId,skuTitle,itemId,itemTitle,quantity) values (
		'".$_POST['shipmentsId']."','".$_POST['skuId']."','".$_POST['skuTitle']."','".$_POST['itemId']."',
		'".$_POST['itemTitle']."','".$_POST['quantity']."')";
		$result = mysql_query($sql);
		echo $result;
	}
	
	public function deleteShipmentDetail(){
		$sql = "delete from qo_shipments_detail where id in (".$_POST['ids'].")";
		//echo $sql;
		//exit;
		$result = mysql_query($sql);
		echo $result;
	}
	
	public function saveShipmentInfo(){
		$sql = "update qo_shipments set status='".$_POST['status']."',shipmentMethod='".$_POST['shipmentMethod']."',
		remarks='".$_POST['remarks']."',postalReferenceNo='".$_POST['postalReferenceNo']."',shippingFeeCurrency='".$_POST['shippingFeeCurrency']."',
		shippingFeeValue='".$_POST['shippingFeeValue']."',shipToName='".$_POST['shipToName']."',shipToEmail='".$_POST['shipToEmail']."',
		shipToAddressLine1='".$_POST['shipToAddressLine1']."',shipToAddressLine2='".$_POST['shipToAddressLine2']."',shipToCity='".$_POST['shipToCity']."',
		shipToStateOrProvince='".$_POST['shipToStateOrProvince']."',shipToPostalCode='".$_POST['shipToPostalCode']."',shipToCountry='".$_POST['shipToCountry']."',
		shipToPhoneNo='".$_POST['shipToPhoneNo']."',modifiedBy='".$this->os->session->get_member_name()."',modifiedOn='".date("Y-m-d H:i:s")."'
		where id = '".$_POST['id']."'";
		//echo $sql;
		$result = mysql_query($sql);
		if($result){
			echo 	'{success: true}';
			
		}else{
			echo 	'{success: false,
				  errors: {message: "can\'t create."}
				}';
		}
	}
	
	public function verifyShipment(){
		$sql_1 = "select ordersId,shipmentMethod from qo_shipments where id = '".$_POST['id']."'";
		$result_1 = mysql_query($sql_1);
		$row_1 = mysql_fetch_assoc($result_1);
		
		$sql_2 = "select sellerId from qo_orders where id = '".$row_1['ordersId']."'";
		$result_2 = mysql_query($sql_2);
		$row_2 = mysql_fetch_assoc($result_2);
		
		$sql = "select sd.id,sd.skuId,sd.skuTitle,sd.itemId,sd.itemTitle,sd.quantity,i.galleryURL from qo_shipments_detail as sd left join qo_items as i on sd.itemId = i.id where sd.shipmentsId = '".$_POST['id']."'";
		$result = mysql_query($sql);
		$shipment_detail_array = array();
		$i = 0;
		while($row = mysql_fetch_assoc($result)){
			$shipment_detail_array[$i]['id'] = $row['id'];
			$shipment_detail_array[$i]['skuId'] = $row['skuId'];
			$shipment_detail_array[$i]['skuTitle'] = $row['skuTitle'];
			$shipment_detail_array[$i]['itemId'] = $row['itemId'];
			$shipment_detail_array[$i]['itemTitle'] = $row['itemTitle'];
			$shipment_detail_array[$i]['quantity'] = $row['quantity'];
			$shipment_detail_array[$i]['galleryURL'] = $row['galleryURL'];
			$shipment_detail_array[$i]['shipmentMethod'] = $row_1['shipmentMethod'];
			$shipment_detail_array[$i]['sellerId'] = $row_2['sellerId'];
                        
                        $request = $this->inventory_service_address."?action=getSkuInfo&data=".urlencode($row['skuId']);
                        $json_result = json_decode($this->getService($request));
                        $shipment_detail_array[$i]['skuStock'] = $json_result->skuStock;
			$i++;
		}
		echo json_encode(array('totalCount'=>$i, 'records'=>$shipment_detail_array));
		mysql_free_result($result);
	}
	
	public function packShipment(){
		$sql = "select status from qo_shipments where id = '".$_POST['id']."'";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		if($row['status'] == "N"){
			$sql = "update qo_shipments set status='K',packedBy='".$this->os->session->get_member_name()."',packedOn='".date("Y-m-d H:i:s")."' where id='".$_POST['id']."'";
			$result = mysql_query($sql);
			if($result){
				echo "{success: true,info:'\'<font color=\'green\'>Operation Successfully</font>'}"; 
			}else{
				echo "{success: false, errors: { reason: 'Saving failed. Try again.' }}";
			}
		}else{
			echo "{success: false, errors: { reason: '\'<font color=\'red\'>Can\'t Pack This Shipment.</font>'}}";
		}
	}
	
	public function inventoryTakeOut($inventory_model, $quantity, $shipment_id, $shipment_method){
            /*
            require_once 'Stomp.php';
            require_once 'Stomp/Message/Map.php';
            $con = new Stomp(QoShipments::ACTIVE_MQ);
            $con->connect();
            // send a message to the queue
            $body = array("inventory_model"=> $inventory_model,
                          "quantity"=> $quantity,
                          "shipment_id"=> $shipment_id,
                          "shipment_method"=> $shipment_method);
            
            $header = array();
            $header['transformation'] = 'jms-map-json';
            $mapMessage = new StompMessageMap($body, $header);
            $result = $con->send("/topic/skuOutStock", $mapMessage, array('persistent'=>'true'));
            $con->disconnect();
            if($result){
                $sql = "update qo_shipments_detail set inventoryStatus='1' where shipmentsId='".$shipment_id."' and skuId='".$inventory_model."'";
                $result = mysql_query($sql);
            }
            return $result;
            
            $request =  $this->inventory_service_address.'?action=inventoryTakeOut&inventory_model='.urlencode($inventory_model).'&quantity='.$quantity.'&shipment_id='.urlencode($shipment_id).'&shipment_method='.urlencode($shipment_method);

            $session = curl_init($request);
            
            curl_setopt($session, CURLOPT_HEADER, true);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($session);
            
            curl_close($session);
            
            $status_code = array();
            preg_match('/\d\d\d/', $response, $status_code);
            
            switch( $status_code[0] ) {
                    case 200:
                            if(strpos($response, "success")){
                                    $sql = "update qo_shipments_detail set inventoryStatus='1' where shipmentsId='".$shipment_id."' and skuId='".$inventory_model."'";
                                    $result = mysql_query($sql);
                                    return $result;
                            }else{
                                $this->message = $response;
                                return false;
                            }
                            break;
                    case 503:
                            die('Your call to Yahoo Web Services failed and returned an HTTP status of 503. That means: Service unavailable. An internal problem prevented us from returning data to you.');
                            break;
                    case 403:
                            die('Your call to Yahoo Web Services failed and returned an HTTP status of 403. That means: Forbidden. You do not have permission to access this resource, or are over your rate limit.');
                            break;
                    case 400:
                            die('Your call to Yahoo Web Services failed and returned an HTTP status of 400. That means:  Bad request. The parameters passed to the service did not match as expected. The exact error is returned in the XML response.');
                            break;
                    default:
                            die('Your call to Yahoo Web Services returned an unexpected HTTP status of:' . $status_code[0]);
                            return false;
            }
            */
            $response = file_get_contents($this->inventory_service_address.'?action=inventoryTakeOut&inventory_model='.urlencode($inventory_model).'&quantity='.$quantity.'&shipment_id='.urlencode($shipment_id).'&shipment_method='.urlencode($shipment_method));
            $this->message = $response;
            if(strpos($response, "success")){
                $sql = "update qo_shipments_detail set inventoryStatus='1' where shipmentsId='".$shipment_id."' and skuId='".$inventory_model."'";
                $result = mysql_query($sql);
                return $result;
            }else{
                return false;
            }
        }
	
	public function sendEmailToBuyer($shipmentId, $itemId, $sellerId, $shipmentMethod, $postalReferenceNo, $shipToName, $shipToEmail, $shipToAddressLine1, $shipToAddressLine2, $shipToCity, $shipToStateOrProvince, $shipToPostalCode, $shipToCountry){
		// The POST URL and parameters
		$request =  $this->email_service_address;
		
		$postargs = 'action=sendEmail&itemId='.urlencode($itemId).'&sellerId='.urlencode($sellerId).'&shipmentMethod='.urlencode($shipmentMethod).
		'&postalReferenceNo='.urlencode($postalReferenceNo).'&shipToName='.urlencode($shipToName).'&shipToEmail='.urlencode($shipToEmail).'&shipToAddressLine1='.urlencode($shipToAddressLine1).
		'&shipToAddressLine2='.urlencode($shipToAddressLine2).'&shipToCity='.urlencode($shipToCity).'&shipToStateOrProvince='.urlencode($shipToStateOrProvince).
		'&shipToPostalCode='.urlencode($shipToPostalCode).'&shipToCountry='.urlencode($shipToCountry).'&subject='.urlencode('Shipping status of your order!');
		
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
				if(strpos($response, "success")){
					$sql = "update qo_shipments set emailStatus='1' where id='".$shipmentId."'";
					$result = mysql_query($sql);
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
				return false;
		}
		//$response
	}
	
	public function shipShipment(){
		$sql = "select id,ordersId,shipmentMethod,postalReferenceNo,shipToName,shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,shipToCountry,status from qo_shipments where id = '".$_POST['id']."'";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$info = "";
		$sku_str = "";
                $quantiry_str = "";
                
		if($row['status'] == "N"){
			$sql_4 = "select shipmentsId,skuId,quantity from qo_shipments_detail where shipmentsId = '".$_POST['id']."'";
			$result_4 = mysql_query($sql_4);
                        $flag = true;
			while($row_4 = mysql_fetch_assoc($result_4)){
			    //send stock to inventory system
                            $sku_str .= $row_4['skuId'].",";
                            $quantiry_str .= $row_4['quantity'].",";
			}
			$sku_str = substr($sku_str, 0, -1);
                        $quantiry_str = substr($quantiry_str, 0, -1);
                        $service_result_1 = $this->inventoryTakeOut($sku_str, $quantiry_str, $row['id'], $row['shipmentMethod']);
                        $info .= $this->message;
                                
                        $result_3 = false;
                        if($service_result_1){
                            //update shipment status
                            if(!empty($_POST['postalReferenceNo'])){
                                    $sql_3 = "update qo_shipments set status='S',postalReferenceNo='".$_POST['postalReferenceNo']."',shippedBy='".$this->os->session->get_member_name()."',shippedOn='".date("Y-m-d H:i:s")."' where id='".$_POST['id']."'";
                                    $result_3 = mysql_query($sql_3);
                            }else{
                                    $sql_3 = "update qo_shipments set status='S',shippedBy='".$this->os->session->get_member_name()."',shippedOn='".date("Y-m-d H:i:s")."' where id='".$_POST['id']."'";
                                    $result_3 = mysql_query($sql_3);
                            }
                        }
                        
                        /*
			//get item Id
			$sql_1 = "select itemId from qo_shipments_detail where shipmentsId = '".$_POST['id']."'";
			$result_1 = mysql_query($sql_1);
			$itemId = "";
			while($row_1 = mysql_fetch_assoc($result_1)){
				$itemId .= $row_1['itemId'].",";
			}
			$itemId = substr($itemId, 0, -1);
			
			//get seller Id
			$sql_2 = "select sellerId from qo_orders where id = '".$row['ordersId']."'";
			$result_2 = mysql_query($sql_2);
			$row_2 = mysql_fetch_assoc($result_2);
			$sellerId = $row_2['sellerId'];

			$service_result_2 = $this->sendEmailToBuyer($_POST['id'], $itemId, $sellerId, $row['shipmentMethod'], $row['postalReferenceNo'], $row['shipToName'], $row['shipToEmail'], $row['shipToAddressLine1'], $row['shipToAddressLine2'], $row['shipToCity'], $row['shipToStateOrProvince'], $row['shipToPostalCode'], $row['shipToCountry']);
			if($service_result_2){
				$info .= "send email to customer success!<br>";
			}else{
				$info .= "send email to customer failure, please notice admin.<br>";
			}
			*/
			
			//print_r($service_result_2);
			//$service_result_1 = true;
			//$service_result_2 = true;
			if($result_3 && $service_result_1){
				echo "{success: true, info:'<font color=\'green\'>".$info."</font>'}"; 
			}else{
				echo "{success: false, errors: { reason: '".$info."' }}";
			}
		}else{
			echo "{success: false, errors: { reason: '<font color=\'red\'>Can\'t Ship This Shipment, Shipment Status is ".$row['status'].".</font>'}}";
		}
	}
	
        public function outstandingShipment(){
            if(!empty($_GET['subAction'])){
                switch($_GET['subAction']){
                    case "updateRemark":
                        $sql = "update qo_shipments set remarks = '".mysql_real_escape_string($_POST['remarks'])."' where id = '".$_POST['shipmentId']."'";
                        mysql_query($sql);
                    break;
                }
                echo "[{success: 'true', msg: 'update remark success!'}]";
                return 1;
            }
            
            $today = date("Y-m-d");
            
            if(!empty($_POST['sellerId'])){
                $count_sql = "select count(*) as num from qo_shipments as s left join qo_orders as o on s.ordersId=o.id where o.sellerId = '".$_POST['sellerId']."' and (s.status = 'N' or s.status = 'H' or s.status = 'W') and s.createdOn < '".$today."'";
                $data_sql = "select s.id,o.id as ordersId,s.shipToName,o.paypalEmail as shipToEmail,o.sellerId,s.createdOn,s.shipmentMethod,s.status,s.remarks   
                from qo_shipments as s left join qo_orders as o on s.ordersId=o.id where o.sellerId = '".$_POST['sellerId']."' and (s.status = 'N' or s.status = 'H' or s.status = 'W') and s.createdOn < '".$today."' order by s.createdOn desc limit ".$_POST['start'].",".$_POST['limit'];
            }else{
                $count_sql = "select count(*) as num from qo_shipments where (status = 'N' or status = 'H' or status = 'W') and createdOn < '".$today."'";
                $data_sql = "select s.id,o.id as ordersId,s.shipToName,o.paypalEmail as shipToEmail,o.sellerId,s.createdOn,s.shipmentMethod,s.status,s.remarks  
                from qo_shipments as s left join qo_orders as o on s.ordersId=o.id where (s.status = 'N' or s.status = 'H' or s.status = 'W') and s.createdOn < '".$today."' order by s.createdOn desc limit ".$_POST['start'].",".$_POST['limit'];
            }
            
            //echo $count_sql;
            $count_result = mysql_query($count_sql);
	    $count_row = mysql_fetch_assoc($count_result);
	    $totalCount = $count_row['num'];
	    
	    $data_result = mysql_query($data_sql);
            $array = array();
            while($data_row = mysql_fetch_assoc($data_result)){
                $flag = false;
                
                $sql_1 = "select i.galleryURL,sd.skuId,sd.skuTitle,sd.quantity from qo_shipments_detail as sd left join qo_items as i on sd.itemId = i.id where shipmentsId = '".$data_row['id']."'";
                $result_1 = mysql_query($sql_1);
                $t = mysql_num_rows($result_1);
                if($t == 0){
                    $totalCount--;
                    continue;
                }
                while($row_1 = mysql_fetch_assoc($result_1)){
                    $request = $this->inventory_service_address."?action=getSkuInfo&data=".urlencode($row_1['skuId']);
                    $json_result = json_decode($this->getService($request));
                    //$json_result->skuStock = 100;
                    if(empty($_POST['stock'])){
                        $flag = true;
                    }else if($_POST['stock'] == 'Y' && $row_1['quantity'] < $json_result->skuStock){
                        $flag = true;
                    }else if(($_POST['stock'] == 'N' && empty($json_result->skuStock)) || ($_POST['stock'] == 'N' && $row_1['quantity'] > $json_result->skuStock)){
                        $flag = true;
                    }
                    /*
                    if($json_result->skuStock < $row_1['quantity']){
                        $json_result->skuStock = "<font color='blue'>".$json_result->skuStock."</font>";
                    }
                    */
                    $data_row['desc'] .= "<div style='height:100px;'><img src='".$row_1['galleryURL']."' width=100 height=100 style='float:left;'/> <font color='red'>SKU:</font> ".$row_1['skuId'] . "<br> <font color='red'>Title:</font> ".$row_1['skuTitle'] . " X " .$row_1['quantity']."<br><font color='red'>Stock:</font> ".$json_result->skuStock."<br><font color='red'>Remark:</font> ".$data_row['remarks']."</div>";
                }
                
                if($flag == true){
                    $array[] = $data_row;
                }else{
                    $totalCount--;
                }
                
            }
            //var_dump($order_array);
            echo json_encode(array('totalCount'=>$totalCount, 'records'=>$array));
            mysql_free_result($result);
        }

}

?>