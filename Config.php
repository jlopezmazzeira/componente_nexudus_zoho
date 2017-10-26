<?php
/**
* 
*/
class Config {
	
	const TOKEN = '*******************'; 
	const PASS = '***************'; 
	const USER = '************';
	const PROTOCOL_HTTP = 'http://';
	const PROTOCOL_HTTPS = 'https://';
	const SCOPE = 'crmapi';
	
	/*Variables E-mail*/
	const MAIL_HOST = 'smtp.gmail.com';
	const MAIL_PORT = 587;
	const MAIL_TIMEOUT = '30';
	const MAIL_USERNAME = 'email@gmail.com';
	const MAIL_PASSWORD = '******';
	const MAIL_FROM = 'email@gmail.com';
	const MAIL_FROM_NAME = 'LAUNCH coworking';
	
	public static function getToken(){ 
		return self::TOKEN; 
	}

	public static function getPass(){ 
		return self::PASS; 
	}

	public static function getUser(){ 
		return self::USER; 
	}

	public static function getProtocolHttp(){ 
		return self::PROTOCOL_HTTP; 
	}

	public static function getProtocolHttps(){ 
		return self::PROTOCOL_HTTPS; 
	}

	public static function getScope(){ 
		return self::SCOPE; 
	}

	public static function getMailHost(){ 
		return self::MAIL_HOST; 
	}

	public static function getMailPort(){ 
		return self::MAIL_PORT; 
	}

	public static function getMailTimeout(){ 
		return self::MAIL_TIMEOUT; 
	}

	public static function getMailUsername(){ 
		return self::MAIL_USERNAME; 
	}

	public static function getMailPassword(){ 
		return self::MAIL_PASSWORD; 
	}

	public static function getMailFrom(){ 
		return self::MAIL_FROM; 
	}

	public static function getMailFromName(){ 
		return self::MAIL_FROM_NAME; 
	}

	public static function getMailCC(){ 
		return self::MAIL_CC; 
	}
}
?>
