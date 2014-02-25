<?php
// wcf imports
require_once(WCF_DIR.'lib/form/AbstractSecureForm.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');

/**
 * Shows the account management form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	form
 * @category 	Community Framework
 */
class AccountManagementForm extends AbstractSecureForm {
	// system
	public $templateName = 'accountManagement';
	
	// parameters
	public $password = '';
	// email
	public $email = '';
	public $confirmEmail = '';
	// password
	public $newPassword = '';
	public $confirmNewPassword = '';
	// username
	public $username = '';
	// quit
	public $quit = 0;
	public $cancelQuit = 0;
	public $quitStarted = 0;
	
	/**
	 * indicates whether the user can changed his username.
	 * 
	 * @var	boolean
	 */
	public $canChangeUsername = true;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// check permissions
		if (!WCF::getUser()->getPermission('user.profile.canRename') || WCF::getUser()->lastUsernameChange + WCF::getUser()->getPermission('user.profile.renamePeriod') * 86400 > TIME_NOW) {
			$this->canChangeUsername = false;
		}
		$this->quitStarted = WCF::getUser()->quitStarted;
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['password'])) $this->password = $_POST['password'];
		if (isset($_POST['email'])) $this->email = $_POST['email'];
		if (isset($_POST['confirmEmail'])) $this->confirmEmail = $_POST['confirmEmail'];
		if (isset($_POST['newPassword'])) $this->newPassword = $_POST['newPassword'];
		if (isset($_POST['confirmNewPassword'])) $this->confirmNewPassword = $_POST['confirmNewPassword'];
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['quit'])) $this->quit = intval($_POST['quit']);
		if (isset($_POST['cancelQuit'])) $this->cancelQuit = intval($_POST['cancelQuit']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// password
		if (empty($this->password)) {
			throw new UserInputException('password');
		}
		
		if (!WCF::getUser()->checkPassword($this->password)) {
			throw new UserInputException('password', 'false');
		}
		
		// username
		if ($this->canChangeUsername && $this->username != WCF::getUser()->username) {
			if (StringUtil::toLowerCase($this->username) != StringUtil::toLowerCase(WCF::getUser()->username)) {
				// check for forbidden chars (e.g. the ",")
				if (!UserRegistrationUtil::isValidUsername($this->username)) {
					throw new UserInputException('username', 'notValid');
				}
				
				// Check if username exists already.
				if (!UserUtil::isAvailableUsername($this->username)) {
					throw new UserInputException('username', 'notUnique');
				}
			}
		}
		// password
		if (!empty($this->newPassword) || !empty($this->confirmNewPassword)) {
			if (empty($this->newPassword)) {
				throw new UserInputException('newPassword');
			}
			
			if (empty($this->confirmNewPassword)) {
				throw new UserInputException('confirmNewPassword');
			}
			
			if (!UserRegistrationUtil::isSecurePassword($this->newPassword)) {
				throw new UserInputException('newPassword', 'notSecure');
			}
			
			if ($this->newPassword != $this->confirmNewPassword) {
				throw new UserInputException('confirmNewPassword', 'notEqual');
			}
		}
		// email
		if (WCF::getUser()->getPermission('user.profile.canChangeEmail') && $this->email != WCF::getUser()->email && $this->email != WCF::getUser()->newEmail) {
			if (empty($this->email)) {	
				throw new UserInputException('email');
			}
		
			// check if only letter case is changed
			if (StringUtil::toLowerCase($this->email) != StringUtil::toLowerCase(WCF::getUser()->email)) {
				// check for valid email (one @ etc.)
				if (!UserRegistrationUtil::isValidEmail($this->email)) {
					throw new UserInputException('email', 'notValid');
				}
				
				// Check if email exists already.
				if (!UserUtil::isAvailableEmail($this->email)) {
					throw new UserInputException('email', 'notUnique');
				}
			}
			
			// check confirm input
			if (StringUtil::toLowerCase($this->email) != StringUtil::toLowerCase($this->confirmEmail)) {
				throw new UserInputException('confirmEmail', 'notEqual');
			}
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// default values
		if (!count($_POST)) {
			$this->username = WCF::getUser()->username;
			$this->email = $this->confirmEmail = WCF::getUser()->email;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'password' => $this->password,
			'email' => $this->email,
			'confirmEmail' => $this->confirmEmail,
			'newPassword' => $this->newPassword,
			'confirmNewPassword' => $this->confirmNewPassword,
			'username' => $this->username,
			'renamePeriod' => WCF::getUser()->getPermission('user.profile.renamePeriod'),
			'canChangeUsername' => $this->canChangeUsername,
			'quitStarted' => $this->quitStarted,
			'quit' => $this->quit,
			'cancelQuit' => $this->cancelQuit
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
				
		// set active tab
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.profile.account');
		
		parent::show();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// get user editor
		$editor = WCF::getUser()->getEditor();
		$success = array();
		
		// quit
		if (WCF::getUser()->getPermission('user.profile.canQuit')) {
			if (!WCF::getUser()->quitStarted && $this->quit == 1) {
				$sql = "UPDATE	wcf".WCF_N."_user
					SET	quitStarted = ".TIME_NOW."
					WHERE	userID = ".WCF::getUser()->userID;
				WCF::getDB()->sendQuery($sql);
				$this->quitStarted = TIME_NOW;
				$success[] = 'wcf.user.quit.success';
			}
			else if (WCF::getUser()->quitStarted && $this->cancelQuit == 1) {
				$sql = "UPDATE	wcf".WCF_N."_user
					SET	quitStarted = 0
					WHERE	userID = ".WCF::getUser()->userID;
				WCF::getDB()->sendQuery($sql);
				$this->quitStarted = 0;
				$success[] = 'wcf.user.quit.cancel.success';
			}
		}
		
		// username
		if ($this->canChangeUsername && $this->username != WCF::getUser()->username) {
			$fields = array();
			if (StringUtil::toLowerCase($this->username) != StringUtil::toLowerCase(WCF::getUser()->username)) {
				if (!$this->canChangeUsername) {
					$this->username = WCF::getUser()->username;
					return;
				}
				$fields = array('lastUsernameChange' => TIME_NOW, 'oldUsername' => $editor->username);
			}
			$editor->update($this->username, '', '', null, null, $fields);
			$success[] = 'wcf.user.rename.success';
		}
		
		// email
		if (WCF::getUser()->getPermission('user.profile.canChangeEmail') && $this->email != WCF::getUser()->email && $this->email != WCF::getUser()->newEmail) {
			if (REGISTER_ACTIVATION_METHOD == 0 || REGISTER_ACTIVATION_METHOD == 2 || StringUtil::toLowerCase($this->email) == StringUtil::toLowerCase(WCF::getUser()->email)) {
				// update email
				$editor->update('', $this->email);
				
				$success[] = 'wcf.user.emailChange.success';
			}
			else if (REGISTER_ACTIVATION_METHOD == 1) {
				// get reactivation code
				$activationCode = UserRegistrationUtil::getActivationCode();
				
				// save as new email
				$sql = "UPDATE	wcf".WCF_N."_user
					SET	reactivationCode = ".$activationCode.",
						newEmail = '".escapeString($this->email)."'
					WHERE	userID = ".WCF::getUser()->userID;
				WCF::getDB()->registerShutdownUpdate($sql);
				
				$subjectData = array('PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE));
				$messageData = array(
					'PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE),
					'$username' => WCF::getUser()->username,
					'$userID' => WCF::getUser()->userID,
					'$activationCode' => $activationCode,
					'PAGE_URL' => PAGE_URL,
					'MAIL_ADMIN_ADDRESS' => MAIL_ADMIN_ADDRESS
				);
				require_once(WCF_DIR.'lib/data/mail/Mail.class.php');
				$mail = new Mail(array(WCF::getUser()->username => $this->email), WCF::getLanguage()->get('wcf.user.emailChange.needReactivation.mail.subject', $subjectData), WCF::getLanguage()->get('wcf.user.emailChange.needReactivation.mail', $messageData));
				$mail->send();
				$success[] = 'wcf.user.emailChange.needReactivation';
			}
		}
		
		// password
		if (!empty($this->newPassword) || !empty($this->confirmNewPassword)) {
			$editor->update('', '', $this->newPassword);
		
			// update cookie
			if (isset($_COOKIE[COOKIE_PREFIX.'password'])) {
				HeaderUtil::setCookie('password', StringUtil::getSaltedHash($this->newPassword, $editor->salt), TIME_NOW + 365 * 24 * 3600);
			}
			
			$success[] = 'wcf.user.passwordChange.success';
		}
		
		// reset session
		WCF::getSession()->resetUserData();
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', $success);
		
		// reset password
		$this->password = '';
		$this->newPassword = $this->confirmNewPassword = '';
	}
}
?>