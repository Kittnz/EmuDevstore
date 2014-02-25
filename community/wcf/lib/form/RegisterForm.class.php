<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/UserAddForm.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/data/mail/Mail.class.php');
require_once(WCF_DIR.'lib/system/language/Language.class.php');
require_once(WCF_DIR.'lib/data/image/captcha/Captcha.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');
require_once(WCF_DIR.'lib/system/auth/UserAuth.class.php');

/**
 * Shows the user registration form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	form
 * @category 	Community Framework
 */
class RegisterForm extends UserAddForm {
	/**
	 * @see AbstractPage::$templateName
	 */
	public $templateName = 'register';
	
	/**
	 * holds a language variable with information about the registration process
	 * e.g. if you need to activate your account
	 *
	 * @var string
	 */
	public $message = '';
	
	/**
	 * holds the id of the captcha
	 *
	 * @var integer
	 */
	public $captchaID = 0;
	
	/**
	 * holds the captcha string
	 *
	 * @var string
	 */
	public $captchaString = '';
	
	/**
	 * the captcha object
	 *
	 * @var Captcha
	 */
	public $captcha;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->groupIDs = array();
		if (isset($_POST['captchaID'])) $this->captchaID = intval($_POST['captchaID']);
		if (isset($_POST['captchaString'])) $this->captchaString = StringUtil::trim($_POST['captchaString']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		$this->validateCaptcha();
		parent::validate();
		$this->validateIpAddress();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// captcha
		$this->captchaID = 0;
		if (REGISTER_USE_CAPTCHA && !WCF::getSession()->getVar('captchaDone')) {
			$this->captchaID = Captcha::create();
		}
		
		$this->options = $this->getOptionTree('profile');
		//$this->options = $this->getCategoryOptions();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' 			=> $this->username,
			'email'				=> $this->email,
			'confirmEmail'			=> $this->confirmEmail,
			'password'			=> $this->password,
			'confirmPassword'		=> $this->confirmPassword,
			'optionCategories' 		=> $this->options,
			'captchaID'			=> $this->captchaID,
			'availableLanguages'		=> $this->getAvailableLanguages(),
			'languageID'			=> $this->languageID,
			'visibleLanguages' 		=> $this->visibleLanguages,
			'availableContentLanguages' 	=> $this->getAvailableContentLanguages()
		));
	}
	
	/**
	 * @see Form::show()
	 */
	public function show() {
		// user is already registered
		if (WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		// registration disabled
		if (REGISTER_DISABLED) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.register.error.disabled'));
		}
		
		// get the default langauge id
		$this->languageID = WCF::getLanguage()->getLanguageID();
		
		// get user options and categories from cache
		$this->readCache();
		
		AbstractForm::show();
	}

	/**
	 * @see DynamicOptionListForm::checkOption()
	 */
	protected function checkOption($optionName) {
		$option = $this->cachedOptions[$optionName];
		
		// show only enabled and required options
		if ($option['disabled'] || (!$option['required'] && !$option['askDuringRegistration'] && $option['editable'] != 2)) return false;

		// show options editable by user 
		return ($option['editable'] <= 2);
	}
	
	/**
	 * Validates the captcha.
	 */
	protected function validateCaptcha() {
		if (REGISTER_USE_CAPTCHA && !WCF::getSession()->getVar('captchaDone')) {
			$this->captcha = new Captcha($this->captchaID);
			
			try {
				$this->captcha->validate($this->captchaString);
			}
			catch (UserInputException $e) {
				$this->errorType[$e->getField()] = $e->getType();
			}
		}
	}
	
	/**
	 * Allows only one registration from same ip address per hour.
	 */
	protected function validateIpAddress() {
		if (REGISTER_UNIQUE_IP_ADDRESS > 0) {
			$sql = "SELECT	COUNT(*) AS users
				FROM 	wcf".WCF_N."_user
				WHERE 	registrationIpAddress = '".escapeString(WCF::getSession()->ipAddress)."'
					AND registrationDate > ".(TIME_NOW - REGISTER_UNIQUE_IP_ADDRESS);
			$row = WCF::getDB()->getFirstRow($sql);
			if ($row['users'] > 0) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see UserAddForm::validateUsername()
	 */
	protected function validateUsername($username) {
		parent::validateUsername($username);
		
		// check for min-max length
		if (!UserRegistrationUtil::isValidUsername($username)) {
			throw new UserInputException('username', 'notValid');
		}
	}
	
	/**
	 * @see UserAddForm::validatePassword()
	 */
	protected function validatePassword($password, $confirmPassword) {
		parent::validatePassword($password, $confirmPassword);
		
		// check security of the given password
		if (!UserRegistrationUtil::isSecurePassword($password)) {
			throw new UserInputException('password', 'notSecure');
		}
	}
	
	/**
	 * @see UserAddForm::validateEmail()
	 */
	protected function validateEmail($email, $confirmEmail) {
		parent::validateEmail($email, $confirmEmail);
		
		if (!UserRegistrationUtil::isValidEmail($email)) {
			throw new UserInputException('email', 'notValid');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save language id
		$this->additionalFields['languageID'] = $this->languageID;
		
		// save registration ip address
		$this->additionalFields['registrationIpAddress'] = WCF::getSession()->ipAddress;
		
		// generate activation code
		$addDefaultGroups = true;
		if (REGISTER_ACTIVATION_METHOD == 1 || REGISTER_ACTIVATION_METHOD == 2) {
			$activationCode = UserRegistrationUtil::getActivationCode();
			$this->additionalFields['activationCode'] = $activationCode;
			$addDefaultGroups = false;
			$this->groupIDs = Group::getGroupIdsByType(array(Group::EVERYONE, Group::GUESTS));
		}

		// create
		$this->user = UserEditor::create($this->username, $this->email, $this->password, $this->groupIDs, $this->activeOptions, $this->additionalFields, $this->visibleLanguages, $addDefaultGroups);
		
		// update session
		WCF::getSession()->changeUser($this->user);
		
		// activation management
		if (REGISTER_ACTIVATION_METHOD == 0) {
			$this->message = 'wcf.user.register.success';
		}
		
		if (REGISTER_ACTIVATION_METHOD == 1) {
			$mail = new Mail(	array($this->username => $this->email),
						WCF::getLanguage()->get('wcf.user.register.needActivation.mail.subject', array('PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE))),
						WCF::getLanguage()->get('wcf.user.register.needActivation.mail',
							array('PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE), '$username' => $this->username, '$userID' => $this->user->userID, '$activationCode' => $activationCode, 'PAGE_URL' => PAGE_URL, 'MAIL_ADMIN_ADDRESS' => MAIL_ADMIN_ADDRESS)));
			$mail->send();
			$this->message = 'wcf.user.register.needActivation';
		}

		if (REGISTER_ACTIVATION_METHOD == 2) {
			$this->message = 'wcf.user.register.awaitActivation';
		}
		
		// notify admin
		if (REGISTER_ADMIN_NOTIFICATION) {
			// get default language
			$language = (WCF::getLanguage()->getLanguageID() != Language::getDefaultLanguageID() ? new Language(Language::getDefaultLanguageID()) : WCF::getLanguage());
			$language->setLocale();
			
			// send mail
			$mail = new Mail(	MAIL_ADMIN_ADDRESS, 
						$language->get('wcf.user.register.notification.mail.subject', array('PAGE_TITLE' => $language->get(PAGE_TITLE))),
						$language->get('wcf.user.register.notification.mail', array('PAGE_TITLE' => $language->get(PAGE_TITLE), '$username' => $this->username)));
			$mail->send();
			
			WCF::getLanguage()->setLocale();
		}
		
		// delete captcha
		if (REGISTER_USE_CAPTCHA && !WCF::getSession()->getVar('captchaDone')) {
			$this->captcha->delete();
		}
		WCF::getSession()->unregister('captchaDone');
		
		// login user
		UserAuth::getInstance()->storeAccessData($this->user, $this->username, $this->password);
		$this->saved();
		
		// forward to index page
		WCF::getTPL()->assign(array(
			'url' => 'index.php'.SID_ARG_1ST,
			'message' => WCF::getLanguage()->get($this->message, array('$username' => $this->username, '$email' => $this->email))
		));
		WCF::getTPL()->display('redirect');
		exit;
	}
}
?>