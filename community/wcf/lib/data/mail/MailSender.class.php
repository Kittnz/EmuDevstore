<?php
// wcf imports
require_once(WCF_DIR.'lib/data/mail/Mail.class.php');

/**
 * Mailsender sends e-mails.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.mail
 * @category 	Community Framework
 */
abstract class MailSender {
	private static $defaultMailSender = null;
	
	/**
	 * Returns the default mail sender.
	 * 
	 * @return	MailSender
	 */
	public static function getDefault() {
		if (self::$defaultMailSender == null) {
			self::createDefault();
		}
		
		return self::$defaultMailSender;
	}
	
	/**
	 * Creates a new instance of the default mail sender.
	 */
	private static function createDefault() {
		switch (MAIL_SEND_METHOD) {
			case 'php':
				require_once(WCF_DIR.'lib/data/mail/PHPMailSender.class.php');
				self::$defaultMailSender = new PHPMailSender();
				break;
			
			case 'smtp':
				require_once(WCF_DIR.'lib/data/mail/SMTPMailSender.class.php');
				self::$defaultMailSender = new SMTPMailSender();
				break;
			
			case 'debug':
				require_once(WCF_DIR.'lib/data/mail/DebugMailSender.class.php');
				self::$defaultMailSender = new DebugMailSender();
				break;
		}
	}
	
	/**
	 * Sends an e-mail.
	 * 
	 * @param	Mail	$mail
	 */
	public abstract function sendMail(Mail $mail); 
}
?>