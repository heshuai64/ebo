<?php
class Service{
    public function sendEmail(){
        //print_r($_POST);
        include("class/class.phpmailer.php");
        $mail  = new PHPMailer();
        
        $mail->IsSMTP();
        $mail->SMTPAuth   = true;                  // enable SMTP authentication
        $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
        $mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
        $mail->Port       = 465;                   // set the SMTP port for the GMAIL server
        
        $mail->Username   = "heshuai04@gmail.com";  // GMAIL username
        $mail->Password   = "xx860924";            // GMAIL password
        
        $mail->AddReplyTo($_POST['toEmail'], $_POST['toName']);
        
        $mail->From       = "heshuai04@gmail.com";
        $mail->FromName   = "First Last";
        
        $mail->Subject    = "PHPMailer Test Subject via gmail";
        
        $mail->Body       = $_POST['toContent'];                      //HTML Body
        $mail->AltBody    = $_POST['toContent']; // optional, comment out and test
        $mail->WordWrap   = 50; // set word wrap
        
        $mail->MsgHTML($_POST['toContent']);
        
        $mail->AddAddress($_POST['toEmail'], $_POST['toName']);
        
        $mail->IsHTML(true); // send as HTML
        
        if(!$mail->Send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            echo "1";
        }
    }
}

$service = new Service();
$action = (!empty($_GET['action']))?$_GET['action']:$_POST['action'];
switch($action){
    case "sendEmail":
        $service->sendEmail();
}
?>