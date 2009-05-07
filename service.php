<?php
class Service{
    private static $database_connect;
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '';
    const DATABASE_NAME = 'ebaybo';
    
    const SHIPPED_REGISTERED_TEMPLET = "Hi %s,<p>
            Thank you for your purchasing from us and prompt payment, your item %s has been posted to our dispatch center just now and will be sent out soon via the HongKong post regular air mail without tracking number. It normally will takes around 7 to 15 business days (public holidays and weekends are not recognized as \"business days\"), please kindly wait a few days for delivery.<p>
            
            The shipping address as below:<p>
            %s<p>
            
            Hopefully you'll receive it soon and could leave us a positive feedback & 5stars DSRs. If has any problem please do not hesitate to advise. Thanks!<p>

            Yours sincerely,<p>
            %s";    
    
    const SHIPPED_SPEEDPOST_TEMPLET = "Hi %s,<p>
            Thank you for your purchasing from us and prompt payment, your item %s has been posted to our dispatch center just now and will be sent out soon via the HongKong post registered air mail with tracking number  . It normally will takes around 7 to 15 business days (public holidays and weekends are not recognized as \"business days\"), please kindly wait a few days for delivery.<p>
            
            The shipping address as below:<p>
            %s<p>
            
            Hopefully you'll receive it soon and could leave us a positive feedback & 5stars DSRs. If has any problem please do not hesitate to advise. Thanks!<p>
            
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
            case "R":
                $toContent = sprintf(self::SHIPPED_REGISTERED_TEMPLET, $_REQUEST['shipToName'], $_REQUEST['itemId'], $address, $_REQUEST['sellerId']);
                break;
            
            case "S":
                $toContent = sprintf(self::SHIPPED_SPEEDPOST_TEMPLET, $_REQUEST['shipToName'], $_REQUEST['itemId'], $address, $_REQUEST['sellerId']);
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
        
        $mail->Subject    = "PHPMailer Test Subject via gmail";
        
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
    
    public function __destruct(){
        mysql_close(Service::$database_connect);
    }
}

$service = new Service();
$action = (!empty($_GET['action']))?$_GET['action']:$_POST['action'];
$service->$action();

?>