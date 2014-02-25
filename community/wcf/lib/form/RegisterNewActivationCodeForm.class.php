<?php
// wcf imports
require_once(WCF_DIR.'lib/data/mail/Mail.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');

/**
 * Shows the new activation code form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	form
 * @category 	Community Framework
 */
class RegisterNewActivationCodeForm extends AbstractForm {
	public $templateName = 'registerNewActivationCode';
	public $username = '';
	public $password = '';
	public $email = '';
	public $user;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['password'])) $this->password = $_POST['password'];
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// username
		$this->validateUsername();
		
		// password
		$this->validatePassword();
		
		// email
		$this->validateEmail();
	}
	
	/**
	 * Validates the username.
	 */
	public function validateUsername() {
		if (empty($this->username)) {
			throw new UserInputException('username');
		}
		
		$this->user = new UserEditor(null, null, $this->username);
		if (!$this->user->userID) {
			throw new UserInputException('username', 'notFound');
		}
		
		if ($this->user->activationCode == 0) {
			throw new UserInputException('username', 'alreadyEnabled');
		}
	}
	
	/**
	 * Validates the password.
	 */
	public function validatePassword() {
		if (empty($this->password)) {
			throw new UserInputException('password');
		}
		
		// check password
		if (!$this->user->checkPassword($this->password)) {
			throw new UserInputException('password', 'false');
		}
	}
	
	/**
	 * Validates the email address.
	 */
	public function validateEmail() {
		if (!empty($this->email)) {
			if (!UserRegistrationUtil::isValidEmail($this->email)) {
				throw new UserInputException('email', 'notValid');
			}
			
			// Check if email exists already.
			if (!UserUtil::isAvailableEmail($this->email)) {
				throw new UserInputException('email', 'notUnique');
			}
		}
	}
	
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// generate activation code
		$activationCode = UserRegistrationUtil::getActivationCode();
		
		// save user
		$this->user->update('', !empty($this->email) ? $this->email : '', '', null, null, array('activationCode' => $activationCode));
		
		// send activation mail
		$subjectData = array('PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE));
		$messageData = array(
			'PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE),
			'PAGE_URL' => PAGE_URL,
			'MAIL_ADMIN_ADDRESS' => MAIL_ADMIN_ADDRESS,
			'$username' => $this->user->username,
			'$userID' => $this->user->userID,
			'$activationCode' => $activationCode
		);
		$mail = new Mail(	array($this->user->username => (!empty($this->email) ? $this->email : $this->user->email)),
					WCF::getLanguage()->get('wcf.user.register.needActivation.mail.subject', $subjectData),
					WCF::getLanguage()->get('wcf.user.register.needActivation.mail', $messageData));
		$mail->send();
		$this->saved();
		
		// forward to index page
		WCF::getTPL()->assign(array(
			'url' => 'index.php'.SID_ARG_1ST,
			'message' => WCF::getLanguage()->get('wcf.user.register.newActivationCode.success', array('$email' => (!empty($this->email) ? $this->email : $this->user->email)))
		));
		WCF::getTPL()->display('redirect');
		exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST) && WCF::getUser()->userID) {
			$this->username = WCF::getUser()->username;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'password' => $this->password,
			'email' => $this->email
		));
	}
}
?>