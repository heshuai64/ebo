<?php
//define ('__DOCROOT__', '/export/eBayBO');
define ('__DOCROOT__', '.');
define ('__DOCCLASS__', __DOCROOT__ . '/class');

class Service{
    private static $database_connect;
    private $inventory_service;
    private $config;
    
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
            
    //XMAS ------------------------------------------------------------------------------------------------------
    const XMAS_REGISTERED_TEMPLATE_1  = "
            <p>%s</p>
            <p>Dear %s,</p>
            <p>Thank you for your purchasing, this email just to inform you that we sent your item to our dispatch center. It is estimated to arrive in 7 to 15 WORKING days in normal conditions to most of US, UK, AU destinations and 3 to 5 weeks to arrive the Europe countries and other remote regions, it depends your custom inspections and the freight efficiency. If not arrive at that time period, please do not hesitate to contact us.</p>
            <p>Here is the tracking number of your parcel (%s), you can logon http://www.singpost.com/ra/ra_article_status.asp to view the updated shipment after 2 WORKING days cause the post office need time to handle it and update the information.</p>
            <p>Hopefully the item could be arrived as quickly as possible and appreciate for your positive feedback with all 5 stars DSRs after receving it, we will leave it for you also.</p>
            <p>We sincerely hope our item and customer service can give you the BEST BUYING EXPERIENCE on eBay.</p>
            Yours Sincerely,<br>
            %s";
    /*        
    const XMAS_BLUK_TEMPLATE_1  = "<p><b>
            Note: Currently, most parts of Europe are experiencing adverse weather conditions - heavy snowfall. As such, delays on our courier delivery are inevitable despite effort to mitigate the effects. Hopefully to get your understanding.
            </b></p>
            <p>%s</p>
            <p>Dear %s,</p>
            <p>Thank you for your purchasing from us, this email just to inform you that we sent your item to our dispatch center. It is estimated to arrive in 7 to 15 WORKING days in normal conditions to most of US, UK, AU destinations and 3 to 5 weeks to arrive the Europe countries and other remote regions, it depends your custom inspections and the freight efficiency. If not arrive at that time period, please do not hesitate to contact us.</p>
            <p>Hopefully the item could be arrived as quickly as possible and appreciate for your positive feedback with all 5 stars DSRs after receving it, we will leave it for you also.</p>
            <p>We sincerely hope our item and customer service can give you the BEST BUYING EXPERIENCE on eBay.</p>
            Yours Sincerely,<br>
            %s";
            
    const XMAS_BLUK_TEMPLATE_1  = "<p><b><font color='red'>Note: To our European customers: </br>
            Your item may be delayed on the delivery for the volcanic explosion in south Iceland. Owning to this volcanic explosion, airlines to Europe have to been cancelled, and many airports also have been closed, which directly caused the shipping delivery delayed. Once the flight condition comes to normal, your item will be dispatched with no delay.</br> 
            We appreciate your understanding and patience.</br>
            European customers pls disregard the letter below sent by our ERP system automatically in normal delivery condition.</font>
            </b></p>
            <p>%s</p>
            <p>Dear %s,</p>
            <p>Thank you for your purchasing from us, this email just to inform you that we sent your item to our dispatch center. It is estimated to arrive in 7 to 15 WORKING days in normal conditions to most of US, UK, AU destinations and 3 to 5 weeks to arrive the Europe countries and other remote regions, it depends your custom inspections and the freight efficiency. If not arrive at that time period, please do not hesitate to contact us.</p> 
            <p>Hopefully the item could be arrived as quickly as possible and appreciate for your positive feedback with all 5 stars DSRs after reiceving it, we will leave it for you also.</p> 
            <p>We sincerely hope our item and customer service can give you the BEST BUYING EXPERIENCE on eBay.</p>
            <p>Yours Sincerely,<br>
            %s";
    */
    const XMAS_BLUK_TEMPLATE_1  = "
            <p>%s</p>
            <p>Dear %s,</p>
            <p>Thank you for your purchasing from us, this email just to inform you that we sent your item to our dispatch center. It is estimated to arrive in 7 to 15 WORKING days in normal conditions to most of US, UK, AU destinations and 3 to 5 weeks to arrive the Europe countries and other remote regions, it depends your custom inspections and the freight efficiency. If not arrive at that time period, please do not hesitate to contact us.</p> 
            <p>Hopefully the item could be arrived as quickly as possible and appreciate for your positive feedback with all 5 stars DSRs after reiceving it, we will leave it for you also.</p> 
            <p>We sincerely hope our item and customer service can give you the BEST BUYING EXPERIENCE on eBay.</p>
            <p>Yours Sincerely,<br>
            %s";

    const XMAS_REGISTERED_TEMPLATE_2  = "
            <p>%s</p>
            <p>Dear %s,</p>
            <p>Just keep in my mind, 12 days have passed since your item was shipped. Have you received it?</p>
            <p>The tracking number is (%s), you can track it on http://www.singpost.com/ra/ra_article_status.asp and your local post office website.</p>
            <p>If already arrived and with good condition, we sincerely hope that you will like it and satisfied with our customer services, your valued positive comment with all 5-stars Detailed Seller Ratings are much appreciated, which are of vital importance to the growth of our small company.</p> 
            <p>If received with anything you feel unsatisfied, please feel free to tell us. We will offer a satisfied resolution for you and will improve our service to better.</p>
            <p>If have not received yet please kindly wait another 10 days for delivery as the item is definitely on the transport way.</p>
            <p>Besides, PLEASE DO NOT leaves us 1, 2, 3 or 4-star Detailed Seller Ratings because they are equal to negative feedback. Like what we said before, if you are not satisfied in any regard, please email me.</p>
            <p>Thanks once more for your purchase.</p>
            Yours Sincerely,<br>
            %s";
            
    const XMAS_BLUK_TEMPLATE_2  = "
            <p>%s</p>
            <p>Dear %s,
            <p>Just keep in my mind, a few days have passed since your item was shipped. Have you received it?</p>
            <p>If already arrived and with good condition, we sincerely hope that you will like it and satisfied with our customer services, your valued positive comment with all 5-stars Detailed Seller Ratings are much appreciated, which are of vital importance to the growth of our small company.</p>
            <p>If received with anything you feel unsatisfied, please feel free to tell us. We will offer a satisfied resolution for you and will improve our service to better.</p>
            <p>If have not received yet please kindly wait another 10 days for delivery as the item is definitely on the transport way.</p>
            <p>Besides, PLEASE DO NOT leaves us 1, 2, 3 or 4-star Detailed Seller Ratings because they are equal to negative feedback. Like what we said before, if you are not satisfied in any regard, please email me.</p>
            <p>Thanks once more for your purchase.</p>
            Yours Sincerely,
            %s";
    
    const XMAS_TEMPLATE_3  = "
            <p>%s</p>
            <p>Dear %s,</p>
            <p>22 days have passed since your item was shipped. I’d like to know that have you received it?</p>
            <p>If yes and like it, hopefully to receive your valued positive feedback and with all 5 stars DSRs, our system will leave it for you also. Thanks in advance!</p>
            <p>If still no news then please do not hesitate to countact our customer service representative, we will response you within one working day.</p>
            Yours Sincerely,<br>
            %s";
    
    const OUTSTANDING = "
            Dear buyer,
            First of all, we thank you for your purchasing from us and prompt payment.
            However, I feel very regret to inform you that our system indicates your order still hasn't be dispatched yet, cause the item is out of stock temporary, and it wil be restocked to our warehouse at 5 days later.
            May I know that if you could pay a bit patience to wait for a few more days to get it restock?
            If you can't wait, then we will full refund amount for you 2 working days(exclude  Chinese public holiday) after your confirmation. .
            If you are unsatisfied with our item or service, then please follow the good eBay practice to contact us before leaving any feedback. We are committed to resolve all issues in a friendly and satisfactory manner.
            Our sincerely apology for any inconvenience caused, your kindly understanding and quick response are much appreciated.
            Best Regards
            ";
    
    public function __construct(){
        $this->config = parse_ini_file(__DOCROOT__ . '/config.ini', true);
        $this->inventory_service = $this->config['service']['inventory'];
        
        Service::$database_connect = mysql_connect($this->config['database']['host'], $this->config['database']['user'], $this->config['database']['password']);

        if (!Service::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(Service::$database_connect);
            exit;
        }
	
        mysql_query("SET NAMES 'UTF8'", Service::$database_connect);
	
        if (!mysql_select_db($this->config['database']['name'], Service::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(Service::$database_connect);
            exit;
        }
    }
    
    private function log($file_name, $data){
        /*
        if(!file_exists($this->config['log']['service'].date("Ymd"))){
            mkdir($this->config['log']['service'].date("Ymd"), 0777);
        }
        */
        file_put_contents($this->config['log']['service'].date("Ymd")."-".$file_name.".html", $data, FILE_APPEND);
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
        $config = parse_ini_file(__DOCROOT__ . '/email.ini', true);
        return $config[$sellerId];
    }
    
    private function getShipmentItems($shipmentsId){
        $sql_1 = "select itemId,itemTitle from qo_shipments_detail where shipmentsId = '".$shipmentsId."'";
        $result_1 = mysql_query($sql_1);
        $itemId = "";
        while($row_1 = mysql_fetch_assoc($result_1)){
            $itemId .= "Item: ".$row_1['itemId'].", Title: ".$row_1['itemTitle']."<br>";
        }
        return $itemId;
    }
    
    private function sendEmail($seller, $buyer, $subjet, $toContent){
        //include(__DOCCLASS__ . "/class.phpmailer.php");
        $mail  = new PHPMailer();
        $mail->PluginDir = __DOCCLASS__ . "/";
        $mail->IsSMTP();
        $mail->SMTPAuth   = true;                  // enable SMTP authentication
        $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
        $mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
        $mail->Port       = 465;                   // set the SMTP port for the GMAIL server
        
        //$mail->SMTPDebug = true;
        
        $mail->Username   = $seller['email'];  // GMAIL username
        $mail->Password   = $seller['emailPassword'];         // GMAIL password
        
        $mail->AddReplyTo($seller['email'], $seller['id']);
        
        $mail->From       = $seller['email'];
        $mail->FromName   = $seller['id'];
        
        $mail->Subject    = $subjet;
        
        $mail->Body       = $toContent;                      //HTML Body
        //$mail->AltBody    = $toContent; // optional, comment out and test
        $mail->WordWrap   = 50; // set word wrap
        
        $mail->MsgHTML($toContent);
        
        $mail->AddAddress($buyer['email'], $buyer['name']);
        //$mail->AddAddress("meidgen@hotmail.com", "meidgen");
        //$mail->AddAddress("heshuai64@gmail.com", "heshuai");
        
        $mail->IsHTML(true); // send as HTML
        
        if(!$mail->Send()) {
            //$this->log("email/sendXmasShpmentEmail", "<font color='red'>Send Email Failure: " . $mail->ErrorInfo."</font><br>");
            echo "Mailer Error: " . $mail->ErrorInfo."!";
            echo "\n";
            return 0;
        } else {
            //$this->log("email/sendXmasShpmentEmail", "Send Email Success<br>");
            echo "send email success!";
            echo "\n";
            return 1;
        }

    }
    
    public function sendXamsShipmentEmail(){
        include(__DOCCLASS__ . "/class.phpmailer.php");
        include(__DOCCLASS__ . "/class.smtp.php");
        global $argv;
        //$day = date("Y-m-d");
        $day   = date("Y-m-d", time() - (1 * 24 * 60 * 60));
        $day12 = date("Y-m-d", time() - (19 * 24 * 60 * 60));
        $day22 = date("Y-m-d", time() - (23 * 24 * 60 * 60));

        switch($argv[2]){
            case 1:
                $sql = "select id,ordersId,shipmentMethod,postalReferenceNo,shipToName,shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,shipToCountry from qo_shipments where shippedOn like '".$day."%' and emailStatus = 0 and status = 'S'";
                $result = mysql_query($sql);
                while($row = mysql_fetch_assoc($result)){
                    
                    //get seller Id
                    $sql_2 = "select sellerId from qo_orders where id = '".$row['ordersId']."'";
                    $result_2 = mysql_query($sql_2);
                    $row_2 = mysql_fetch_assoc($result_2);
                    $sellerId = $row_2['sellerId'];
                    $seller = $this->getSellerEmailAccountAndPassword($sellerId);
                    $item = $this->getShipmentItems($row['id']);
                    
                    switch($row['shipmentMethod']){
                        case "R":
                            $toContent = sprintf(self::XMAS_REGISTERED_TEMPLATE_1, $item, $row['shipToName'], $row['postalReferenceNo'], $sellerId);
                            break;
                        
                        case "B":
                        
                        case "S":
                            
                        case "U":
                            
                        default:
                            $toContent = sprintf(self::XMAS_BLUK_TEMPLATE_1, $item, $row['shipToName'], $sellerId);
                            break;
                    }
                    
                    $buyer = array('name'=> $row['shipToName'], 'email'=> $row['shipToEmail']);
                    $subjet = "Your order has been sent out";
                    $send_result = $this->sendEmail($seller, $buyer, $subjet, $toContent);
                    if($send_result){
                        $sql_3 = "update qo_shipments set emailStatus = 1 where id = '".$row['id']."'";
                        $result_3 = mysql_query($sql_3);
                        $this->log("1_sendXmasShpmentEmail", $sellerId. ": ".$row['id']." Send <br>".$toContent."<br> To ".$row['shipToName'].": ".$row['shipToEmail']." Success<br>");
                    }else{
                        $this->log("1_sendXmasShpmentEmail", "<font color='red'>".$sellerId. ": " .$row['id']." Send Email To ".$row['shipToName'].": ".$row['shipToEmail']." Failure: " . $mail->ErrorInfo."</font><br>");
                    }
                    $this->log("1_sendXmasShpmentEmail", "<br><font color='red'>+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++</font><br>");
                    //exit;
                }
            break;
        
            case 12:
                $sql = "select id,ordersId,shipmentMethod,postalReferenceNo,shipToName,shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,shipToCountry from qo_shipments where shippedOn like '".$day12."%' and emailStatus = 0 and status = 'S'";
                //echo $sql."\n";
                $result = mysql_query($sql);
                while($row = mysql_fetch_assoc($result)){
                    
                    //get seller Id
                    $sql_2 = "select sellerId from qo_orders where id = '".$row['ordersId']."'";
                    $result_2 = mysql_query($sql_2);
                    $row_2 = mysql_fetch_assoc($result_2);
                    $sellerId = $row_2['sellerId'];
                    $seller = $this->getSellerEmailAccountAndPassword($sellerId);
                    $item = $this->getShipmentItems($row['id']);
                    
                    switch($row['shipmentMethod']){
                        case "R":
                            //$toContent = sprintf(self::XMAS_REGISTERED_TEMPLATE_2, $item, $row['shipToName'], $row['postalReferenceNo'], $sellerId);
                            //break;
                        
                        case "B":
                        
                        case "S":
                            
                        case "U":
                            
                        default:
                            $toContent = sprintf(self::XMAS_BLUK_TEMPLATE_2, $item, $row['shipToName'], $sellerId);
                            break;
                        
                    }
                    
                    if($row['shipToEmail'] == "Invalid Request"){
                        continue;
                    }
                    $buyer = array('name'=> $row['shipToName'], 'email'=> $row['shipToEmail']);
                    $subjet = "Have you receive the order?";
                    $send_result = $this->sendEmail($seller, $buyer, $subjet, $toContent);
                    if($send_result){
                        $sql_3 = "update qo_shipments set emailStatus = 1 where id = '".$row['id']."'";
                        $result_3 = mysql_query($sql_3);
                        $this->log("12_sendXmasShpmentEmail", $sellerId. ": ".$row['id']." Send <br>".$toContent."<br> To ".$row['shipToName'].": ".$row['shipToEmail']." Success<br>");
                    }else{
                        $this->log("12_sendXmasShpmentEmail", "<font color='red'>".$sellerId. ": ".$row['id']." Send Email To ".$row['shipToName'].": ".$row['shipToEmail']." Failure: " . $mail->ErrorInfo."</font><br>");
                    }      
                    $this->log("12_sendXmasShpmentEmail", "<br><font color='red'>+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++</font><br>");
                    //exit;
                }
            break;
        
            case 22:
                $sql = "select id,ordersId,shipmentMethod,postalReferenceNo,shipToName,shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,shipToCountry from qo_shipments where shippedOn like '".$day22."%' and emailStatus = 0 and status = 'S'";
                $result = mysql_query($sql);
                while($row = mysql_fetch_assoc($result)){
                    
                    //get seller Id
                    $sql_2 = "select sellerId from qo_orders where id = '".$row['ordersId']."'";
                    $result_2 = mysql_query($sql_2);
                    $row_2 = mysql_fetch_assoc($result_2);
                    $sellerId = $row_2['sellerId'];
                    $seller = $this->getSellerEmailAccountAndPassword($sellerId);
                    $item = $this->getShipmentItems($row['id']);
                    
                    $toContent = sprintf(self::XMAS_TEMPLATE_3, $item, $row['shipToName'], $sellerId);
                    
                    $buyer = array('name'=> $row['shipToName'], 'email'=> $row['shipToEmail']);
                    $subjet = "Have you received the item you bought from us on ebay? How is everything?";
                    $send_result = $this->sendEmail($seller, $buyer, $subjet, $toContent);
                    if($send_result){
                        $sql_3 = "update qo_shipments set emailStatus = 1 where id = '".$row['id']."'";
                        $result_3 = mysql_query($sql_3);
                        $this->log("22_sendXmasShpmentEmail", $sellerId. ": ".$row['id']." Send <br>".$toContent."<br> To ".$row['shipToName'].": ".$row['shipToEmail']." Success<br>");
                    }else{
                        $this->log("22_sendXmasShpmentEmail", "<font color='red'>".$sellerId. ": ".$row['id']." Send Email To ".$row['shipToName'].": ".$row['shipToEmail']." Failure: " . $mail->ErrorInfo."</font><br>");
                    }
                    $this->log("22_sendXmasShpmentEmail", "<br><font color='red'>+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++</font><br>");
                    //exit;
                }
            break;
        }
    }
    
    public function sendShipShpmentEmail(){
        //file_put_contents("/tmp/1.log", print_r($_POST, true), FILE_APPEND);
        
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
            
            $this->log("sendShipShpmentEmail", "seller: ".print_r($seller, true)."<br>");
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
                $this->log("sendShipShpmentEmail", "<font color='red'>Send Email Failure: " . $mail->ErrorInfo."</font><br>");
                echo "Mailer Error: " . $mail->ErrorInfo."!";
                echo "\n";
            } else {
                $sql_3 = "update qo_shipments set emailStatus = 1 where id = '".$row['id']."'";
                $result_3 = mysql_query($sql_3);
                $this->log("sendShipShpmentEmail", $sql_3."<br>Send Email Success<br>");
                echo $row['id']." send email success!";
                echo "\n";
                //file_put_contents("/tmp/1.log", "Success!", FILE_APPEND);
            }
            $this->log("sendShipShpmentEmail", "<br><font color='red'>+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++</font><br>");
            sleep(1);
            //exit;
        }
        //php -q /export/eBayBO/service.php sendShipShpmentEmail
        //http://heshuai64.3322.org/eBayBO/service.php?action=sendShipShpmentEmail
    }
    
    public function sendOutstandingEmail(){
        include(__DOCCLASS__ . "/class.phpmailer.php");
        $config = parse_ini_file(__DOCROOT__ . '/email_template.ini', true);
        
        $day   = date("Y-m-d", time() - (3 * 24 * 60 * 60));
        $sql = "select id,ordersId,shipmentMethod,postalReferenceNo,shipToName,shipToEmail,shipToAddressLine1,shipToAddressLine2,shipToCity,shipToStateOrProvince,shipToPostalCode,shipToCountry from qo_shipments where createdOn like '".$day."%' and emailStatus = 0 and status = 'N'";
        //echo $sql."\n";
        $result = mysql_query($sql);
        while($row = mysql_fetch_assoc($result)){
            //get seller Id
            $sql_2 = "select sellerId from qo_orders where id = '".$row['ordersId']."'";
            $result_2 = mysql_query($sql_2);
            $row_2 = mysql_fetch_assoc($result_2);
            $sellerId = $row_2['sellerId'];
            $seller = $this->getSellerEmailAccountAndPassword($sellerId);
            
            $toContent = $config['OUTSTANDING'];
            //$buyer = array('name'=> "heshuai", 'email'=> "heshuai64@gmail.com");
            $buyer = array('name'=> $row['shipToName'], 'email'=> $row['shipToEmail']);
            $subjet = "important notice regarding yr ebay purchase";
            //$send_result = $this->sendEmail($seller, $buyer, $subjet, $toContent);
            //var_dump($send_result);
            //exit;
            $send_result = true;
            if($send_result){
                $sql_3 = "update qo_shipments set emailStatus = 1,status = 'H' where id = '".$row['id']."'";
                $result_3 = mysql_query($sql_3);
                $this->log("outstandingEmail", $sellerId. ": ".$row['id']." Send <br>".$toContent."<br> To ".$row['shipToName'].": ".$row['shipToEmail']." Success<br>");
            }else{
                $this->log("outstandingEmail", "<font color='red'>".$sellerId. ": ".$row['id']." Send Email To ".$row['shipToName'].": ".$row['shipToEmail']." Failure: " . $mail->ErrorInfo."</font><br>");
            }
            $this->log("outstandingEmail", "<br><font color='red'>+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++</font><br>");
            //exit;
        }
    }
    
    public function getService($request){
        return file_get_contents($request);
    }
    
    public function updateShippingMethod(){
        $a = explode(",", $this->config['shippingMethod']['R']);
        foreach($a as $b){
            $c = explode("|", $b);
            $registered_conditions[$c[0]] = $c[1];
        }
        $tmp = explode(",", $this->config['shippingMethod']['E']);
        foreach($tmp as $tmp1){
            $tmp2 = explode("|", $tmp1);
            $eyoubao_country[$tmp2[0]] = $tmp2[1];
        }
        $tmp = explode(",", $this->config['shippingMethod']['P']);
        foreach($tmp as $tmp1){
            $tmp2 = explode("|", $tmp1);
            $postlink_bulk_country[$tmp2[0]] = $tmp2[1];
        }
        
        //print_r($registered_conditions);
        //exit;
        
        //$sql = "select id,ebayCountry from qo_orders where id = 'ORD200905A0138'";
        $sql = "select id,ebayCountry,grandTotalCurrency,grandTotalValue from qo_orders where shippingMethodStatus = 0";
        $result = mysql_query($sql, Service::$database_connect);
        //var_dump($result);
        //exit;
        
        while($row = mysql_fetch_assoc($result)){
            $id = $row['id'];
            $ebayCountry = $row['ebayCountry'];
            $total_quantity = 0;
            
            //$sku_string = "";
            $sql_1 = "select skuId,quantity from qo_orders_detail where ordersId = '".$row['id']."'";
            //echo $sql_1."<br>";
            $result_1 = mysql_query($sql_1, Service::$database_connect);
            $sku_array = array();
            while($row_1 = mysql_fetch_assoc($result_1)){
                $sku_array[] = $row_1;
                //$sku_string .= $row_1['skuId'].",";
                $total_quantity += $row_1['quantity'];
            }
            
            if($total_quantity >= 20 || $row['grandTotalValue'] >= $registered_conditions[$row['grandTotalCurrency']]){
                $this->log("updateShippingMethod", "<font color='red'>[R]ordersId: ".$id.", ".$total_quantity."|".$row['grandTotalCurrency'].$row['grandTotalValue']."</font><br>");
                $shippingMethod = "R";
            }/*elseif($total_quantity > 1 && array_key_exists($row['ebayCountry'], $eyoubao_country) && $row['grandTotalValue'] > $eyoubao_country[$row['ebayCountry']]){
                $this->log("updateShippingMethod", "<font color='red'>[E]ordersId: ".$id.", ".$row['ebayCountry']."|".$row['grandTotalCurrency'].$row['grandTotalValue']."</font><br>");
                $shippingMethod = "E";   
            }*/elseif(array_key_exists($row['ebayCountry'], $postlink_bulk_country) && count($sku_array) == 1 && 
                    (($total_quantity == 1 && $row['grandTotalValue'] > $postlink_bulk_country[$row['ebayCountry']]) ||
                    ($total_quantity > 1 && $row['grandTotalValue'] > 8))
                    ){
                $this->log("updateShippingMethod", "<font color='red'>[P]ordersId: ".$id.", ".$row['ebayCountry']."|".$row['grandTotalCurrency'].$row['grandTotalValue']."</font><br>");
                $shippingMethod = "P";   
            }else{
                $data_array = array('id'=>$id, 'country'=>$ebayCountry, 'sku_array'=>$sku_array);
                //print_r($data_array);
                $data_json = json_encode($data_array);
                //print_r($data_json);
                //$sku_string = substr($sku_string, 0, -1);
                //$json_result = $this->getService($request."?skuString=".$sku_string."&country=".$row['ebayCountry']);
                //echo $data_json;
                //echo "<br>";
                $json_result = $this->getService($this->inventory_service."?action=getShippingMethodBySku&data=".urlencode($data_json));
                //echo $json_result;
                //echo "<br>";
                $this->log("updateShippingMethod", "ordersId: ".$id.", sku: ".print_r($sku_array, true).", inventory system return: ".$json_result."<br>");
                $service_result = json_decode($json_result);
                //var_dump($service_result);
                $shippingMethod = $service_result->shippingMethod;
            }
            
            if(!empty($shippingMethod)){
                $sql_2 = "update qo_orders set shippingMethodStatus = 1, shippingMethod='".$shippingMethod."' where id = '".$id."'";
                $this->log("updateShippingMethod", "ordersId: ".$id.", orders: ".$sql_2."<br>");
                $result_2 = mysql_query($sql_2, Service::$database_connect);
                
                //$sql_3 = "update qo_shipments set shipmentMethod = '".$shippingMethod."' where ordersId = '".$id."'";
                //$this->log("updateShippingMethod", "ordersId: ".$id.", shipments: ".$sql_3."<br>");
                //$result_3 = mysql_query($sql_3, Service::$database_connect);
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
            
            //print_r($skuArray);
            //flush();
            //if(count($skuArray) > 2){
            //    exit;   
            //}
            //print_r($skuArray);
            $data_json = json_encode($skuArray);
            
            $json_result = $this->getService($this->inventory_service."?action=getEnvelopeBySku&data=".urlencode($data_json));
            echo $json_result;
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
    
    public function getReadyShipShipmentSku(){
        $sql = "select sd.skuId,sum(sd.quantity) as quantity from qo_shipments as s left join qo_shipments_detail as sd 
        on s.id = sd.shipmentsId where s.status = 'N' group by sd.skuId";
        $result = mysql_query($sql, Service::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $array[] = $row;
        }
        echo json_encode($array);
    }
    
    public function getSkuFlow(){
        $three_day_ago = date("Y-m-d", time() - ((3 * 24 * 60 * 60)));
        $one_week_ago = date("Y-m-d", time() - ((7 * 24 * 60 * 60)));
        $two_week_ago = date("Y-m-d", time() - ((14 * 24 * 60 * 60)));
	$three_week_ago = date("Y-m-d", time() - ((21 * 24 * 60 * 60)));
        $today = date("Y-m-d");
        $array = array();
        
        $sql_0 = "select sd.skuId,sum(sd.quantity) as flow from qo_shipments as s left join qo_shipments_detail as sd on s.id = sd.shipmentsId where s.status = 'S' and s.shippedOn between '%s' and '%s' and sd.quantity < 20 group by sd.skuId";

        $sql = sprintf($sql_0, $three_day_ago, $today);
        //echo $sql."\n";
        $result = mysql_query($sql, Service::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $array['three_day'][] = $row;
        }
        
        $sql = sprintf($sql_0, $one_week_ago, $today);
        //echo $sql."\n";
        $result = mysql_query($sql, Service::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $array['one_week'][] = $row;
        }
        
        $sql = sprintf($sql_0, $two_week_ago, $one_week_ago);;
        //echo $sql."\n";
        $result = mysql_query($sql, Service::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $array['two_week'][] = $row;
        }
        
        $sql = sprintf($sql_0, $three_week_ago, $two_week_ago);;
        //echo $sql."\n";
        $result = mysql_query($sql, Service::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $array['three_week'][] = $row;
        }
        echo json_encode($array);
    }
    
    public function updateSkuCost(){
        $sql = "select id,skuId from qo_orders_detail where skuCostStatus = 0";
        $result = mysql_query($sql, Service::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $json_result = $this->getService($this->inventory_service."?action=getSkuCost&data=".urlencode($row['skuId']));
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
            $json_result = $this->getService($this->inventory_service."?action=getSkuInfo&data=".urlencode($row['skuId']));
            echo $json_result;
            echo "\n";
            $service_result = json_decode($json_result);
            $skuCost = $service_result->skuCost;
            $skuTitle = $service_result->skuTitle;
            $skuWeight = $service_result->skuWeight;
            $skuStock = $service_result->skuStock;
            $skuLowestPrice = $service_result->skuLowestPrice;
            
            if(!empty($service_result)){
                $sql_1 = "update qo_orders_detail set skuTitle = '".mysql_escape_string($skuTitle)."',skuCost = '".$skuCost."',skuLowestPrice='".$skuLowestPrice."',skuWeight = '".$skuWeight."',skuStock='".$skuStock."',skuInfoStatus = 1 where id = '".$row['id']."'";
                $result_1 = mysql_query($sql_1, Service::$database_connect);
                //echo $sql_1."\n";
            }else{
                $this->log("updateSkuInfo", "ordersDetailId: ".$row['id']."<br> sku: ".$row['skuId']." no in inventory system<br>");
            }
            //exit;
        }
    }
    
    private function post($request, $postargs){
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
	//echo $response;
	// Get HTTP Status code from the response
	$status_code = array();
	preg_match('/\d\d\d/', $response, $status_code);
	
	// Check for errors
	switch( $status_code[0] ) {
		case 200:
			return 1;
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
    
    public function complaints(){
        $sql = "update qo_orders_detail set complaintsStatus = 1 where id = ".$_POST['ordersDetailId'];
        $result = mysql_query($sql, Service::$database_connect);
        
        $postargs = "";
        foreach($_POST as $key=>$value){
            $postargs .= $key."=".$value."&";
        }
        $postargs = substr($postargs, 0, -1);
        echo $this->post($this->inventory_service."?action=complaints", $postargs);
    }
    
    public function getEbayAccount(){
        $array = array();
        $sql = "select id,password from qo_ebay_seller";
        $result = mysql_query($sql, Service::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $array[] = $row;
        }
        echo json_encode($array);
    }
    
    public function getGoogleAccount(){
        $array = array();
        $sql = "select id,password from qo_google_account";
        $result = mysql_query($sql, Service::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $array[] = $row;
        }
        echo json_encode($array);    
    }
    
    /*
    CREATE TABLE `ebaybo`.`qo_client_ip` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `ip` VARCHAR( 15 ) NOT NULL ,
    `last_update_time` DATETIME NOT NULL ,
    INDEX ( `ip` )
    )
  */
    public function updateClientIp(){
        $sql = "select count(*) as num from qo_client_ip where ip = '".$_SERVER['REMOTE_ADDR']."'";
        $result = mysql_query($sql, Service::$database_connect);
        $row = mysql_fetch_assoc($result);
        if($row['num'] == 0){
            $sql_1 = "insert into qo_client_ip (ip,last_update_time) values ('".$_SERVER['REMOTE_ADDR']."', '".date("Y-m-d H:i:s")."')";
            echo $sql_1;
            $result_1 = mysql_query($sql_1, Service::$database_connect);
        }else{
            $sql_1 = "update qo_client_ip set last_update_time = '".date("Y-m-d H:i:s")."' where ip = '".$_SERVER['REMOTE_ADDR']."'";
            echo $sql_1;
            $result_1 = mysql_query($sql_1, Service::$database_connect);
        }
    }
    
    public function getClientIp(){
        $sql = "select ip from qo_client_ip";
        $result = mysql_query($sql, Service::$database_connect);
        $data = array();
        while($row = mysql_fetch_assoc($result)){
            $data[] = $row['ip'];
        }
        echo json_encode($data);
    }
    
    public function remoteLogin(){
        $sql = "select id,email_address from qo_members where email_address = '".$_GET['user']."' and password = '".$_GET['password']."' and active = 1";
        $result = mysql_query($sql, Service::$database_connect);
        $row = mysql_fetch_assoc($result);
        if(!empty($row['id'])){
            $sql_1 = "select qo_groups_id from qo_groups_has_members where qo_members_id = ".$row['id'];
            $result_1 = mysql_query($sql_1, Service::$database_connect);
            $row_1 = mysql_fetch_assoc($result_1);
            echo json_encode(array('success'=> true,
                                   'user'=> $row['email_address'],
                                   'role'=> ($row_1['qo_groups_id']==1)?'admin':'user')
                             );
            //echo "{success: true, user: '".$row['email_address']."', role: '".(($row_1['qo_groups_id']==1)?'admin':'user')."'}";
        }else{
            echo json_encode(array('success'=> false,
                                   'message'=> 'bad user or password!')
                             );
            //echo "{success: false, message: 'bad user or password!'}";
        }
    }
    
    public function getShippingAddressBySku(){
        $sql = "select s.* from qo_orders as o,qo_shipments as s,qo_shipments_detail as sd where o.id = s.ordersId and s.id = sd.shipmentsId 
        and o.sellerId in (".$this->config['shipment']['split_seller'].")";//and s.modifiedOn like '".$_GET['date']."%'
        $sql .= " and o.shippingMethod = 'B' and s.status = 'N' and s.printStatus = 0 and sd.skuId = '".$_GET['sku']."' order by s.id";
        //and s.modifiedOn like '".$_GET['date']."%' 
        $this->log("getShippingAddressBySku", date("H:i:s").": ".$sql."<br>");
        $result = mysql_query($sql, Service::$database_connect);
        $row = mysql_fetch_assoc($result);
        /*
        $json_result = $this->getService($this->inventory_service."?action=getSkuStockFromRemote&sku=".$_GET['sku']);
        //echo $json_result;
        $service_result = json_decode($json_result);
        if($service_result->R < 1){
            return 0;
        }
        */
        if(!empty($row['id']) && empty($_GET['debug'])){
            $sql_1 = "update qo_shipments set printStatus = 1 where id = '".$row['id']."'";
            $this->log("getShippingAddressBySku", date("H:i:s").": ".$sql_1."<br>");
            $result_1 = mysql_query($sql_1, Service::$database_connect);
        }
        
        //$this->log("getShippingAddressBySku", date("H:i:s").":  result| ".print_r(array($result, $result_1), true)."<br><br>");
        $this->log("getShippingAddressBySku", $_GET['sku']." -- ".print_r($row, true)."<br><br>");
        echo json_encode($row);
    }
    
    public function syncShipmentPrintStatus(){
        if(!empty($_GET['debug'])){
            $this->log("syncShipmentPrintStatus", date("H:i:s") ."   |  ". print_r($_GET, true)."<br>");
            echo "SUCCESS:".$_GET['shipmentId'];
            return 1;
        }
        $shipmentId = $_GET['shipmentId'];
        $sql = "select s.shipmentMethod,sd.skuId,sd.quantity from qo_shipments as s,qo_shipments_detail as sd where s.id = sd.shipmentsId and s.id = '".$shipmentId."'";
        //$this->log("syncShipmentPrintStatus", date("H:i:s")."   |   ".$sql."<br>");
        $result = mysql_query($sql, Service::$database_connect);
        $row = mysql_fetch_assoc($result);
        
        $call_service = $this->getService($this->inventory_service."?action=clientSkuTackOut&sku=".$row['skuId']."&quantity=".$row['quantity']."&shipment_id=".$shipmentId."&shipment_method=".$row['shipmentMethod']);
        if(strpos($call_service, "success")){
            //$sql = "update qo_shipments as s,qo_shipments_detail as sd set s.printStatus=1,s.printOn='' where s.id = sd.shipmentsId and sd.skuId = '".$_GET['sku']."'";
            $sql = "update qo_shipments set status='S',printStatus=2,printOn=now(),printBy='".$_GET['by']."',shippedOn=now(),shippedBy='".$_GET['by']."' where id = '".$_GET['shipmentId']."'";
            $result = mysql_query($sql, Service::$database_connect);
            echo "SUCCESS:".$_GET['shipmentId'];
            $this->log("syncShipmentPrintStatus", date("H:i:s")."   |  success! shipmentId: ".$shipmentId.", sku: ".$row['skuId'].", quantity: ".$row['quantity'].", by: ".$_GET['by']."<br>");
        }else{
            $this->log("syncShipmentPrintStatus", date("H:i:s")."   |  failure! shipmentId: ".$shipmentId.", inventory return: ".$call_service."<br>");
            echo "";
        }
    }
    
    public function getNeedSyncStock(){
        $array = array();
        $sql = "select s.id as shipmentId,sd.skuId,sd.quantity from qo_shipments as s left join qo_shipments_detail as sd on s.id = sd.shipmentsId where s.status = 'S' and s.syncStockStatus = 0";
        $result = mysql_query($sql, Service::$database_connect);
        while($row = mysql_fetch_assoc($result)){
            $array[] = $row;
        }
        echo json_encode($array);
    }
    
    public function updateShipmentSyncStockStatus(){
        $sql = "update qo_shipments set syncStockStatus = 1 where id = '".$_GET['shipmentId']."'";
        $result = mysql_query($sql, Service::$database_connect);
        if($result){
            echo "HS:success";
        }else{
            echo "HS:failure";
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