<?php
class Service{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaybo';
    const INVENTORY_SERVICE = 'http://127.0.0.1/einv2/service.php';
    
    const SHIPPED_BULK_TEMPLET = "Hi %s,<p>
            Thank you for your purchasing from us and prompt payment, your item %s has been posted to the dispatch center just now which will be sent out soon via the HongKong post regular air mail without tracking number. It normally will takes around 7 to 15 business days from the dispatch date (public holidays and weekends are not recognized as \"business days\"), please kindly wait a few days for delivery.<p>
            
            The shipping address as below:<p>
            %s<p>
            
            Hopefully you could receive it soon and leave us a valued positive feedback & all 5 stars DSRs.<p>
            
            If has any problem please feel free to contact us %s. Thanks!<p>
           
            Yours sincerely,<p>
            %s";
    
    const SHIPPED_REGISTERED_TEMPLET = "Hi %s,<p>
            Thank you for your purchasing from us and prompt payment, your item %s has been posted to the dispatch center just now which will be sent out soon via the HongKong post registered air mail with tracking number %s. It normally will takes around 7 to 15 business days from the dispatch date (public holidays and weekends are not recognized as \"business days\"), please kindly wait a few days for delivery.<p>
            
            The shipping address as below:<p>
            %s<p>
            
            Hopefully you could receive it soon and leave us a valued positive feedback & all 5 stars DSRs.<p>
            
            If has any problem please feel free to contact us %s. Thanks!<p>
            
            Yours sincerely,<p>
            %s";
    
    const SHIPPED_SPEEDPOST_TEMPLET = "Hi %s,<p>
            Thank you for your purchasing from us and prompt payment, your item %s has been posted to the dispatch center just now which will be sent out soon via the Express shipping (Worldwide EMS) with tracking number %s. It normally will takes around 3 to 5 business days from the dispatch date (public holidays and weekends are not recognized as \"business days\"), please kindly wait a few days for delivery.<p>
            
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
    
    public function getSellerEmailAccountAndPassword($sellerId){
        $sql = "select id,email,emailPassword from qo_ebay_seller where id = '".$sellerId."'";
        $result = mysql_query($sql, Service::$database_connect);
        $row = mysql_fetch_assoc($result);
        return array('id'=>$row['id'],
                     'email'=>$row['email'],
                     'emailPassword'=>$row['emailPassword']);
    }
    
    public function sendEmail(){
        //file_put_contents("/tmp/1.log", print_r($_POST, true), FILE_APPEND);
        $seller = $this->getSellerEmailAccountAndPassword($_REQUEST['sellerId']);
        $address =  $_REQUEST['shipToName'].'<br>'.
                    $_REQUEST['shipToAddressLine1'].'<br>'.
                    (!empty($_POST['shipToAddressLine2'])?$_REQUEST['shipToAddressLine2'].'<br>':'').
                    $_REQUEST['shipToCity'].'<br>'.
                    $_REQUEST['shipToStateOrProvince'].'<br>'.
                    $_REQUEST['shipToPostalCode'].'<br>'.
                    $_REQUEST['shipToCountry'].'<br>';
                                                
        switch($_REQUEST['shipmentMethod']){
            
            case "B":
                $toContent = sprintf(self::SHIPPED_BULK_TEMPLET, $_REQUEST['shipToName'], $_REQUEST['itemId'], $address, $seller['email'], $_REQUEST['sellerId']);
                break;
            
            case "R":
                $toContent = sprintf(self::SHIPPED_REGISTERED_TEMPLET, $_REQUEST['shipToName'], $_REQUEST['itemId'], $_REQUEST['postalReferenceNo'], $address, $seller['email'], $_REQUEST['sellerId']);
                break;
            
            case "S":
                $toContent = sprintf(self::SHIPPED_SPEEDPOST_TEMPLET, $_REQUEST['shipToName'], $_REQUEST['itemId'], $_REQUEST['postalReferenceNo'], $address, $seller['email'], $_REQUEST['sellerId']);
                break;
            
            case "U":
                
                break;
        }
        
        //print_r($_POST);
        include("class/class.phpmailer.php");
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
        
        $mail->Subject    = $_REQUEST['subject'];
        
        $mail->Body       = $toContent;                      //HTML Body
        //$mail->AltBody    = $toContent; // optional, comment out and test
        $mail->WordWrap   = 50; // set word wrap
        
        $mail->MsgHTML($toContent);
        
        //$mail->AddAddress($_POST['toEmail'], $_POST['toName']);
        $mail->AddAddress("heshuai64@gmail.com", "heshuai64");
        
        $mail->IsHTML(true); // send as HTML
        
        if(!$mail->Send()) {
            //file_put_contents("/tmp/1.log", $mail->ErrorInfo, FILE_APPEND);
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            echo "1";
            //file_put_contents("/tmp/1.log", "Success!", FILE_APPEND);
        }
        //http://127.0.0.1/eBayBO/service.php?action=sendEmail&itemId=350187839239&sellerId=testuser_heshuai04&shipmentMethod=S&postalReferenceNo=&shipToName=Test User&shipToAddressLine1=address&shipToAddressLine2=&shipToCity=city&shipToStateOrProvince=WA&shipToPostalCode=98102&shipToCountry=
    }
    
    public function getService($request){
        
        //$request =  'http://search.yahooapis.com/ImageSearchService/V1/imageSearch?appid=YahooDemo&query='.urlencode('Al Gore').'&results=1';
        
        // Make the request
        $json = file_get_contents($request);
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
        $sql = "select id,ebayCountry from qo_orders where id = 'ORD200904A0019'";//shippingMethodStatus = 0
        $result = mysql_query($sql, Service::$database_connect);
        //var_dump($result);
        //exit;
        while($row = mysql_fetch_assoc($result)){
            //$sku_string = "";
            $sql_1 = "select skuId,quantity from qo_orders_detail where ordersId = '".$row['id']."'";
            //echo $sql_1."<br>";
            $result_1 = mysql_query($sql_1, Service::$database_connect);
            $sku_array = array();
            while($row_1 = mysql_fetch_assoc($result_1)){
                $sku_array[] = $row_1;
                //$sku_string .= $row_1['skuId'].",";
            }
            
            $data_array = array('id'=>$row['id'], 'country'=>$row['ebayCountry'], 'sku_array'=>$sku_array);
            $data_json = json_encode($data_array);
            //$sku_string = substr($sku_string, 0, -1);
            //$json_result = $this->getService($request."?skuString=".$sku_string."&country=".$row['ebayCountry']);
            //echo $data_json;
            //echo "<br>";
            $json_result = $this->getService(self::INVENTORY_SERVICE."?action=getShippingMethodBySku&data=".urlencode($data_json));
            echo $json_result;
            echo "<br>";
            $service_result = json_decode($json_result);
            var_dump($service_result);
            $shippingMethod = $service_result->shippingMethod;
            
            if(!empty($shippingMethod)){
                $sql_2 = "update qo_orders set shippingMethodStatus = 1, shippingMethod='".$shippingMethod."' where id = '".$row['id']."'";
                echo $sql_2."<br>";
                //$result_2 = mysql_query($sql_2, Service::$database_connect);
                
                $sql_3 = "update qo_shipments set shipmentMethod = '".$shippingMethod."' where ordersId = '".$row['id']."'";
                echo $sql_3."<br>";
                //$result_3 = mysql_query($sql_3, Service::$database_connect);
                //sleep(1);
                //exit;
            }
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
?>