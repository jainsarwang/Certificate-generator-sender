<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require __DIR__. '/../vendor/autoload.php';
require __DIR__. '/emailConfig.php';

class MailSender {
    private $mail;
    public function __construct() {
        $mail = new PHPMailer(true);

        $mail->SMTPDebug = SMTP::DEBUG_OFF; // SMTP::DEBUG_CLIENT, SMTP::DEBUG_SERVER      
        $mail->isSMTP();
        $mail->Host       = MAIL_SERVER;
        $mail->Port       = 587;
        // $mail->Port       = 465;
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = 'tsl';
        $mail->SMTPKeepAlive = true;
        $mail->Username   = MAIL_ACCOUNT;
        $mail->Password   = MAIL_PASSWORD;
        $mail->CharSet = 'UTF-8';
        $mail->SMPTOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' =>false
            ]
        ];

        $this->mail = $mail;
    }

    public function __destruct(){
        $this->mail->SmtpClose();
    }

    public function setFrom($email, $name=""){
        $email = trim($email);
        $name = trim($name);

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Error("Please provide valid From email Address ");
        }

        if(empty($name)) $name = MAIL_DEFAULT_SENDER;

        $this->mail->setFrom($email, $name);
    }

    public function addRecipent($recipientEmail, $recipientName=''){
        
        $recipientEmail = trim($recipientEmail);
        $recipientName = trim($recipientName);

        if(!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Error("Please provide valid email Address of Recipient");
        }

        $this->mail->addAddress($recipientEmail, $recipientName);
    }

    public function addReplyTo($replyTo, $name){
        $replyTo = trim($replyTo);
        $name = trim($name);

        if(!filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            throw new Error("Please provide valid Reply-to email Address");
        }
        if(empty($name)) $name = MAIL_DEFAULT_SENDER;

        $this->mail->addReplyTo($replyTo, $name);
    }

    public function addBCC($email) {
        $this->mail->addBCC($email);
    }

    public function addSubject($subject) {
        if(empty($subject)) throw new Error("Subject is requried please add it");
        $this->mail->Subject = $subject;
    }

    public function addHTMLBody($html){
        $this->mail->isHTML(true);
        // $this->mail->Body = trim($html);
        $this->mail->msgHTML(trim($html));
    }
    
    public function addBody($body) {
        $this->mail->Body = trim($body);
    }

    public function addAltBody($text){
        $this->mail->AltBody = $text;
    }

    public function addAttachment($filePath, $fileName=""){
        $this->mail->addAttachment($filePath, $fileName);
    }

    public function send($errorCallback) {
        try{
            $mail = $this->mail;
            
            // checking for subject
            if($mail->Subject == null) throw new Error("Please provide Subject to mail");

            // checking for body
            if($mail->Body == null) throw new Error("Please Provide the Body to send");

            $this->mail->send();
            $this->mail->clearAllRecipients();
            $this->mail->clearAttachments();
            return true;
        } catch(PHPMailerException $err) {
            $error = ($err->errorMessage());
            $errorCallback($error);
            
            return false;
        } catch(Error $err) {
            $error = ($err->getMessage());
            $errorCallback($error);

            return false;
        } catch (Exception $e) {
            $error = ($mail->ErrorInfo);
            $errorCallback($error);

            return false;
        }
    }
}


?>