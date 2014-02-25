<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/LoginForm.class.php');
require_once(WCF_DIR.'lib/data/image/captcha/Captcha.class.php');

/**
 * Shows the user login form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	form
 * @category 	Community Framework
 */
class UserLoginForm extends LoginForm {
	public $useCookies = 1;
	public $captchaID = 0;
	public $captchaString = '';
	public $captcha;
	public $useCaptcha = false;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		AbstractForm::readParameters();
		
		if (LOGIN_USE_CAPTCHA && !WCF::getSession()->getVar('captchaDone')) {
			$this->useCaptcha = true;
		}
	}

	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// set cookies
		if ($this->useCookies == 1) {
			UserAuth::getInstance()->storeAccessData($this->user, $this->username, $this->password);
		}
		
		// change user
		WCF::getSession()->changeUser($this->user);
		
		// delete captcha
		if ($this->useCaptcha) {
			$this->captcha->delete();
		}
		
		WCF::getSession()->unregister('captchaDone');
		
		// get redirect url
		$this->checkURL();
		$this->saved();
		
		// redirect to url
		WCF::getTPL()->assign(array(
			'url' => $this->url,
			'message' => WCF::getLanguage()->get('wcf.user.login.redirect'),
			'wait' => 5
		));
		WCF::getTPL()->display('redirect');
		exit;
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['loginUsername'])) $this->username = StringUtil::trim($_POST['loginUsername']);
		if (isset($_POST['loginPassword'])) $this->password = $_POST['loginPassword'];
		if (isset($_POST['useCookies'])) $this->useCookies = intval($_POST['useCookies']);
		else $this->useCookies = 0;
		if (isset($_POST['url'])) $this->url = StringUtil::trim($_POST['url']);
		if (isset($_POST['captchaID'])) $this->captchaID = intval($_POST['captchaID']);
		if (isset($_POST['captchaString'])) $this->captchaString = StringUtil::trim($_POST['captchaString']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// captcha
		$this->captchaID = 0;
		if ($this->useCaptcha) {
			$this->captchaID = Captcha::create();
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'useCookies' => $this->useCookies,
			'captchaID' => $this->captchaID,
			'supportsPersistentLogins' => UserAuth::getInstance()->supportsPersistentLogins()
		));
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		$this->validateCaptcha();
		
		parent::validate();
		
		/*if ($this->user->activationCode != 0) {
			throw new UserInputException('username', 'notEnabled');
		}*/
	}
	
	/**
	 * Gets the redirect url.
	 */
	protected function checkURL() {
		if (empty($this->url) || StringUtil::indexOf($this->url, 'index.php?form=UserLogin') !== false || StringUtil::indexOf($this->url, 'index.php?page=Register') !== false) {
			$this->url = 'index.php'.SID_ARG_1ST;
		}
		// append missing session id
		else if (SID_ARG_1ST != '' && !preg_match('/(?:&|\?)s=[a-z0-9]{40}/', $this->url)) {
			if (StringUtil::indexOf($this->url, '?') !== false) $this->url .= SID_ARG_2ND_NOT_ENCODED;
			else $this->url .= SID_ARG_1ST;
		}
	}
	
	/**
	 * Validates the captcha.
	 */
	protected function validateCaptcha() {
		if ($this->useCaptcha) {
			$this->captcha = new Captcha($this->captchaID);
			$this->captcha->validate($this->captchaString);
			$this->useCaptcha = false;
		}
	}
}
?>