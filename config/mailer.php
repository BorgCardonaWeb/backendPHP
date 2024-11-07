<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true); 

       
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.openxchange.eu'; 
        $this->mail->SMTPAuth = true; 
        $this->mail->Username = getenv('SMTP2GO_USER'); 
        $this->mail->Password = getenv('SMTP2GO_PASSWORD'); 
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $this->mail->Port = 587; 

       
        $this->mail->SMTPDebug = 0; 
    }

    public function send($from, $to, $subject, $body) {
        try {
            
            $this->mail->setFrom($from, 'Gardening Malta'); 
            $this->mail->addAddress($to); 

            $this->mail->isHTML(true); 
            $this->mail->Subject = $subject; 
            $this->mail->Body = $body; 

            if ($this->mail->send()) {
                return true; 
            } else {
                throw new Exception('Could not send email');
            }
        } catch (Exception $e) {
            echo 'Could not send email: ' . $e->getMessage();
            return false;
        }
    }
}
?>
