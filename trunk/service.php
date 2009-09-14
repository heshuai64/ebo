<?php
class Service{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaybo';
    const INVENTORY_SERVICE = 'http://192.168.1.169:8080/inventory/service.php';
    
    const SHIPPED_BULK_TEMPLET = "Hi %s,<p>
            Thank you for your purchasing from us and prompt payment, your item #%s has been posted to the dispatch center just now which will be sent out soon via the HongKong post regular air mail without tracking number. It normally will takes around 7 to 15 business days from the dispatch date (public holidays and weekends are not recognized as \"business days\"), please kindly wait a few days for delivery.<p>
            
            The shipping address as below:<p>
            %s<p>
            
            Hopefully you could receive it soon and leave us a valued positive feedback & all 5 stars DSRs.<p>
            
            If has any problem please feel free to contact us %s. Thanks!<p>
           
            Yours sincerely,<p>
            %s";
    
    const SHIPPED_REGISTERED_TEMPLET = "Hi %s,<p>
            Thank you for your purchasing from us and prompt payment, your item #%s has been posted to the dispatch center just now which will be sent out soon via the HongKong post registered air mail with tracking number %s. It normally will takes around 7 to 15 business days from the dispatch date (public holidays and weekends are not recognized as \"business days\"), please kindly wait a few days for delivery.<p>
            
            The shipping address as below:<p>
            %s<p>
            
            Hopefully you could receive it soon and leave us a valued positive feedback & all 5 stars DSRs.<p>
            
            If has any problem please feel free to contact us %s. Thanks!<p>
            
            Yours sincerely,<p>
            %s";
    
    const SHIPPED_SPEEDPOST_TEMPLET = "Hi %s,<p>
            Thank you for your purchasing from us and prompt payment, your item #%s has been posted to the dispatch center just now which will be sent out soon via the Express shipping (Worldwide EMS) with tracking number %s. It normally will takes around 3 to 5 business days from the dispatch date (public holidays and weekends are not recognized as \"business days\"), please kindly wait a few days for delivery.<p>
            
            The shipping address as below:<p>
            %s<p>
            
            Hopefully you could receive it soon and leave us a valued positive feedback & all 5 stars DSRs. <p>
            
            If has any problem please feel free to contact us %s. Thanks!<p>
            
            Yours sincerely,<p>
            %s";
            
            
    public function __construct(){
        Service::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

        if (!Service::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(Service::$database_connect);
            exit;
        }
	
        mysql_query("SET NAMES 'UTF8'", Service::$database_connect);
	
        if (!mysql_select_db(self::DATABASE_NAME, Service::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(Service::$database_connect);
            exit;
        }
    }
    
    private function log($file_name, $data){
        file_put_contents("/export/eBayBO/log/".$file_name."-".date("Y-m-d").".html", $data, FILE_APPEND);
        //echo $data;   
    }
    
    public function getSellerEmailAccountAndPassword($sellerId){
        /*
        $sql = "select id,email,emailPassword from qo_ebay_seller where id = '".$sellerId."'";
        $result = mysql_query($sql, Service::$database_connect);
        $row = mysql_fetch_assoc($result);
        return array('id'=>$row['id'],
                     'email'=>$row['email'],
                     'emailPassword'=>$row['emailPassword']);
        */
        
        $array = array('ymca200808'=> array('id'=>'ymca200808', 'email'=> 'ymca2u@gmail.com', 'emailPassword'=> 'yingying3510'),
                       'libra.studio'=> array('id'=>'libra.studio', 'email'=> 'libra.studio.cn@gmail.com', 'emailPassword'=> 'ldpandll99'),
                       'bestnbestonline'=> array('id'=>'bestnbestonline', 'email'=> 'bestnbestonline@gmail.com', 'emailPassword'=> 'sun2769kk'),
                       'nereus.store'=> array('id'=>'nereus.store', 'email'=> 'nereus.art@gmail.com', 'emailPassword'=> 'coloryourlife'));
        
        return $array[$sellerId];
    }
    
    public function sendShipShpmentEmail(){
        //file_put_contents("/tmp/1.log", print_r($_POST, true), FILE_APPEND);
        include("class/class.phpmailer.php");
        
        $sql = "select id,ordersId,shipmentMethod,postalReferenceNo,shipToName,shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,shipToCountry from qo_shipments where emailStatus = 0 and status = 'S'";
        $result = mysql_query($sql);
        while($row = mysql_fetch_assoc($result)){
            //get item Id
            $sql_1 = "select itemId from qo_shipments_detail where shipmentsId = '".$row['id']."'";
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
            $seller = $this->getSellerEmailAccountAndPassword($sellerId);
            
            $this->log("email/sendShipShpmentEmail", "seller: ".print_r($seller, true)."<br>");
            $address =  $row['shipToName'].'<br>'.
                        $row['shipToAddressLine1'].'<br>'.
                        (!empty($row['shipToAddressLine2'])?$row['shipToAddressLine2'].'<br>':'').
                        $row['shipToCity'].'<br>'.
                        $row['shipToStateOrProvince'].'<br>'.
                        $row['shipToPostalCode'].'<br>'.
                        $row['shipToCountry'].'<br>';
                                                    
            switch($row['shipmentMethod']){
                
                case "B":
                    $toContent = sprintf(self::SHIPPED_BULK_TEMPLET, $row['shipToName'], $itemId, $address, $seller['email'], $sellerId);
                    break;
                
                case "R":
                    $toContent = sprintf(self::SHIPPED_REGISTERED_TEMPLET, $row['shipToName'], $itemId, $row['postalReferenceNo'], $address, $row['shipToEmail'], $sellerId);
                    break;
                
                case "S":
                    $toContent = sprintf(self::SHIPPED_SPEEDPOST_TEMPLET, $row['shipToName'], $itemId, $row['postalReferenceNo'], $address, $row['shipToEmail'], $sellerId);
                    break;
                
                case "U":
                    
                    break;
            }
            
            //print_r($_POST);
            
            $mail  = new PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPAuth   = true;                  // enable SMTP authentication
            $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
            $mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
            $mail->Port       = 465;                   // set the SMTP port for the GMAIL server
            
            $mail->SMTPDebug = true;
            
            $mail->Username   = $seller['email'];  // GMAIL username
            $mail->Password   = $seller['emailPassword'];         // GMAIL password
            
            $mail->AddReplyTo($seller['email'], $seller['id']);
            
            $mail->From       = $seller['email'];
            $mail->FromName   = $seller['id'];
            
            $mail->Subject    = "Shipping status of your order!";
            
            $mail->Body       = $toContent;                      //HTML Body
            //$mail->AltBody    = $toContent; // optional, comment out and test
            $mail->WordWrap   = 50; // set word wrap
            
            $mail->MsgHTML($toContent);
            
            //$mail->AddAddress($_POST['shipToEmail'], $_POST['shipToName']);
            $mail->AddAddress("karentina_86@sina.com", "meidgen de");
            //$mail->AddAddress("heshuai64@gmail.com", "heshuai");
            
            $mail->IsHTML(true); // send as HTML
            
            if(!$mail->Send()) {
                //file_put_contents("/tmp/1.log", $mail->ErrorInfo, FILE_APPEND);
                $this->log("email/sendShipShpmentEmail", "<font color='red'>Send Email Failure: " . $mail->ErrorInfo."</font><br>");
                echo "Mailer Error: " . $mail->ErrorInfo."!";
                echo "\n";
            } else {
                $sql_3 = "update qo_shipments set emailStatus = 1 where id = '".$row['id']."'";
                $result_3 = mysql_query($sql_3);
                $this->log("email/sendShipShpmentEmail", $sql_3."<br>Send Email Success<br>");
                echo $row['id']." send email success!";
                echo "\n";
                //file_put_contents("/tmp/1.log", "Success!", FILE_APPEND);
            }
            $this->log("email/sendShipShpmentEmail", "<br><font color='red'>+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++</font><br>");
            sleep(1);
            //exit;
        }
        //php -q /export/eBayBO/service.php sendShipShpmentEmail
        //http://heshuai64.3322.org/eBayBO/service.php?action=sendShipShpmentEmail
    }
    
    public function getService($request){
        
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
    
    public function updateShippingMethod(){
        //$sql = "select id,ebayCountry from qo_orders where id = 'ORD200905A0138'";
        $sql = "select id,ebayCountry from qo_orders where shippingMethodStatus = 0";
        $result = mysql_query($sql, Service::$database_connect);
        //var_dump($result);
        //exit;
        
        while($row = mysql_fetch_assoc($result)){
            $id = $row['id'];
            $ebayCountry = $row['ebayCountry'];
            
            //$sku_string = "";
            $sql_1 = "select skuId,quantity from qo_orders_detail where ordersId = '".$row['id']."'";
            //echo $sql_1."<br>";
            $result_1 = mysql_query($sql_1, Service::$database_connect);
            $sku_array = array();
            while($row_1 = mysql_fetch_assoc($result_1)){
                $sku_array[] = $row_1;
                //$sku_string .= $row_1['skuId'].",";
            }
            
            $data_array = array('id'=>$id, 'country'=>$ebayCountry, 'sku_array'=>$sku_array);
            //print_r($data_array);
            $data_json = json_encode($data_array);
            //print_r($data_json);
            //$sku_string = substr($sku_string, 0, -1);
            //$json_result = $this->getService($request."?skuString=".$sku_string."&country=".$row['ebayCountry']);
            //echo $data_json;
            //echo "<br>";
            $json_result = $this->getService(self::INVENTORY_SERVICE."?action=getShippingMethodBySku&data=".urlencode($data_json));
            //echo $json_result;
            //echo "<br>";
            $this->log("updateShippingMethod", "ordersId: ".$id.", sku: ".print_r($sku_array, true).", inventory system return: ".$json_result."<br>");
            $service_result = json_decode($json_result);
            //var_dump($service_result);
            $shippingMethod = $service_result->shippingMethod;
            
            if(!empty($shippingMethod)){
                $sql_2 = "update qo_orders set shippingMethodStatus = 1, shippingMethod='".$shippingMethod."' where id = '".$id."'";
                $this->log("updateShippingMethod", "ordersId: ".$id.", orders: ".$sql_2."<br>");
                $result_2 = mysql_query($sql_2, Service::$database_connect);
                
                $sql_3 = "update qo_shipments set shipmentMethod = '".$shippingMethod."' where ordersId = '".$id."'";
                $this->log("updateShippingMethod", "ordersId: ".$id.", shipments: ".$sql_3."<br>");
                $result_3 = mysql_query($sql_3, Service::$database_connect);
                //sleep(1);
                //exit;
            }else{
                $this->log("updateShippingMethod", "ordersId: ".$id."<br> sku: ".print_r($sku_array, true)." no in inventory system<br>");
            }
            $this->log("updateShippingMethod", "<br>+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br>");
        }
        
    }
    
    public function updateShipmentEnvelope(){
        //$sql = "select id from qo_shipments where id = 'SHA200906A0746'";
        $sql = "select id from qo_shipments where envelopeStatus = 0 order by id desc";
        $result = mysql_query($sql, Service::$database_connect);
        //$i = 0;
        while($row = mysql_fetch_assoc($result)){
            $sql_1 = "select skuId,quantity from qo_shipments_detail where shipmentsId = '".$row['id']."'";
            $result_1 = mysql_query($sql_1, Service::$database_connect);
            $skuArray = array();
            while($row_1 = mysql_fetch_assoc($result_1)){
                $skuArray[] = $row_1;
            }
            
            print_r($skuArray);
            flush();
            //if(count($skuArray) > 2){
            //    exit;   
            //}
            //print_r($skuArray);
            $data_json = json_encode($skuArray);
            
            $json_result = $this->getService(self::INVENTORY_SERVICE."?action=getEnvelopeBySku&data=".urlencode($data_json));
            //echo $json_result;
            //echo "<br>";
            $this->log("updateShipmentEnvelope", "inventory system return: ".$json_result. "<br>");
            
            $service_result = json_decode($json_result);
            $envelope = $service_result->envelope;
            if(!empty($envelope)){
                $sql_2 = "update qo_shipments set envelope = '$envelope',envelopeStatus = 1 where id = '".$row['id']."'";
                //echo $sql_2;
                $this->log("updateShipmentEnvelope", $sql_2."<br>");
                $result_2 = mysql_query($sql_2, Service::$database_connect);
            }else{
                $this->log("updateShipmentEnvelope", "shipmentsId: ".$row['id'].", sku: ".print_r($skuArray, true)." no in inventory system<br>\n");
            }
            $this->log("updateShipmentEnvelope", "<br>+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br>");
            //if($i > 3){
            //    exit;
            //}
            //$i++;
            //sleep(1);
        }
    }
    
    public function updateSkuCost(){
        $sql = "select id,skuId from qo_orders_detail where skuCostStatus = 0";
        $result = mysql_query($sql, Service::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $json_result = $this->getService(self::INVENTORY_SERVICE."?action=getSkuCost&data=".urlencode($row['skuId']));
            echo $json_result;
            echo "<br>";
            $service_result = json_decode($json_result);
            $skuCost = $service_result->skuCost;
            if(!empty($skuCost)){
                $sql_1 = "update qo_orders_detail set skuCost = '$skuCost',skuCostStatus = '1' where id = '".$row['id']."'";
                $result_1 = mysql_query($sql_1, Service::$database_connect);
            }else{
                $this->log("updateSkuCost", "ordersDetailId: ".$row['id']."<br> sku: ".$row['skuId']." no in inventory system<br>");
            }
            //exit;
        }
        
    }
    
    public function updateSkuInfo(){
    	$sql = "select id,skuId from qo_orders_detail where skuInfoStatus = 0";
    	$result = mysql_query($sql, Service::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $json_result = $this->getService(self::INVENTORY_SERVICE."?action=getSkuInfo&data=".urlencode($row['skuId']));
            echo $json_result;
            echo "\n";
            $service_result = json_decode($json_result);
            $skuCost = $service_result->skuCost;
            $skuTitle = $service_result->skuTitle;
            $skuWeight = $service_result->skuWeight;
            if(!empty($service_result)){
                $sql_1 = "update qo_orders_detail set skuTitle = '".mysql_escape_string($skuTitle)."',skuCost = '".$skuCost."',skuWeight = '".$skuWeight."',skuInfoStatus = 1 where id = '".$row['id']."'";
                $result_1 = mysql_query($sql_1, Service::$database_connect);
                //echo $sql_1;
            	//echo "\n";
            }else{
                $this->log("updateSkuInfo", "ordersDetailId: ".$row['id']."<br> sku: ".$row['skuId']." no in inventory system<br>");
            }
            //exit;
        }
    }
    
    public function __destruct(){
        mysql_close(Service::$database_connect);
    }
}

if(!empty($argv[1])){
    $action = $argv[1];
}else{
    $action = (!empty($_GET['action']))?$_GET['action']:$_POST['action'];
}

$service = new Service();
$service->$action();

//http://127.0.0.1:6666/eBayBO/service.php?action=updateShippingMethod
//http://heshuai64.3322.org/eBayBO/service.php?action=updateShippingMethod
//http://heshuai64.3322.org/eBayBO/service.php?action=updateSkuCost
?>