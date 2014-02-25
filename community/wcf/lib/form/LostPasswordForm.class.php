<?php
// wcf imports
require_once(WCF_DIR.'lib/form/CaptchaForm.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * Shows the lost password form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	form
 * @category 	Community Framework
 */
class LostPasswordForm extends CaptchaForm {
	public $username = '';
	public $email = '';
	public $user;
	public $useCaptcha = LOST_PASSWORD_USE_CAPTCHA;
	public $templateName = 'lostPassword';
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->username) && empty($this->email)) {
			throw new UserInputException('username');
		}
		
		if (!empty($this->username)) {
			$this->user = new User(null, null, $this->username);
			if (!$this->user->userID) {
				throw new UserInputException('username', 'notFound');
			}
		}
		else {
			$this->user = new User(null, null, null, $this->email);
			if (!$this->user->userID) {
				throw new UserInputException('email', 'notFound');
			}
		}
		
		// check whether a lost password request was sent in the last 24 hours
		if ($this->user->lastLostPasswordRequest && TIME_NOW - 86400 < $this->user->lastLostPasswordRequest) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.lostPassword.error.tooManyRequests', array('$hours' => ceil(($this->user->lastLostPasswordRequest - (TIME_NOW - 86400)) / 3600))));
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// generate a new lost password key
		$lostPasswordKey = StringUtil::getRandomID();
		
		// save key and request time in database
		$sql = "UPDATE 	wcf".WCF_N."_user
			SET	lostPasswordKey = '".$lostPasswordKey."',
				lastLostPasswordRequest = ".TIME_NOW."
			WHERE 	userID = ".$this->user->userID;
		WCF::getDB()->registerShutdownUpdate($sql);
			
		// send mail
		$subjectData = array('PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE));
		$messageData = array(
			'PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE),
			'$username' => $this->user->username,
			'$userID' => $this->user->userID,
			'$key' => $lostPasswordKey,
			'PAGE_URL' => PAGE_URL,
			'MAIL_ADMIN_ADDRESS' => MAIL_ADMIN_ADDRESS
		);
		require_once(WCF_DIR.'lib/data/mail/Mail.class.php');
		$mail = new Mail(array($this->user->username => $this->user->email), WCF::getLanguage()->get('wcf.user.lostPassword.mail.subject', $subjectData), WCF::getLanguage()->get('wcf.user.lostPassword.mail', $messageData));
		$mail->send();
		$this->saved();		
		
		// forward to index page
		WCF::getTPL()->assign(array(
			'url' => 'index.php'.SID_ARG_1ST,
			'message' => WCF::getLanguage()->get('wcf.user.lostPassword.mail.sent')
		));
		WCF::getTPL()->display('redirect');
		exit;
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'email' => $this->email
		));
	}
}
?>