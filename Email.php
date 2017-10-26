<?php
include_once('phpMailer/PHPMailerAutoload.php');
include_once('Config.php');

class Email{
	  
	private $mail;
	
	function __construct() {
	
	    $this->mail = new PHPMailer();
		$this->mail->IsSMTP();
		$this->mail->SMTPDebug = 1;
		$this->mail->SMTPAuth = true;
		$this->mail->SMTPSecure = 'tls';
		$this->mail->Host = Config::getMailHost();
		$this->mail->Port = Config::getMailPort();
		$this->mail->IsHTML(true);
		$this->mail->Username = Config::getMailUsername();
		$this->mail->Password = Config::getMailPasword();
		$this->mail->FromName = Config::getMailFromName();
	}

	public function send($address,$subject,$body){
		$this->mail->Subject = $subject;
		$this->mail->Body = $body;
		$this->mail->AddAddress($address);

		 if(!$this->mail->Send()) {
		    echo "Mailer Error: " . $mail->ErrorInfo;
		 } else {
		    echo "Message has been sent";
		 }
	}
	
}
?>