<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/data/image/captcha/Captcha.class.php');

/**
 * Logs failed user logins.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.security.login
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class FailedLoginListener implements EventListener {
	public $captchaID = 0;
	public $captchaString = '';
	public $captcha = null;
	public $useCaptcha = false;

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (FAILED_LOGIN_IP_CAPTCHA > 0 || FAILED_LOGIN_IP_BAN > 0) {
			if ($eventName == 'readParameters') {
				// get number of failed logins
				require_once(WCF_DIR.'lib/data/user/login/FailedLogin.class.php');
				$failedLogins = FailedLogin::countFailedLogins();
				if (FAILED_LOGIN_IP_BAN > 0 && $failedLogins >= FAILED_LOGIN_IP_BAN) {
					throw new PermissionDeniedException();
				}
				else if (FAILED_LOGIN_IP_CAPTCHA > 0 && $failedLogins >= FAILED_LOGIN_IP_CAPTCHA) {
					if (!($eventObj instanceof UserLoginForm) || !LOGIN_USE_CAPTCHA || WCF::getSession()->getVar('captchaDone')) {
						$this->useCaptcha = true;
					}
				}
			}
			else if ($eventName == 'readFormParameters') {
				if ($this->useCaptcha) {
					if (isset($_POST['captchaID'])) $this->captchaID = intval($_POST['captchaID']);
					if (isset($_POST['captchaString'])) $this->captchaString = StringUtil::trim($_POST['captchaString']);
				}
			}
			else if ($eventName == 'validate') {
				if ($this->useCaptcha) {
					$this->captcha = new Captcha($this->captchaID);
					$this->captcha->validate($this->captchaString);
				}
			}
			else if ($eventName == 'save') {
				// delete captcha
				if ($this->useCaptcha) {
					$this->captcha->delete();
				}
			}
			else if ($eventName == 'readData') {
				// captcha
				$this->captchaID = 0;
				if ($this->useCaptcha) {
					$this->captchaID = Captcha::create();
				}
				
				// save failed logins
				if ($eventObj->errorField == 'username' || $eventObj->errorField == 'password') {
					require_once(WCF_DIR.'lib/data/user/login/FailedLoginEditor.class.php');
					FailedLoginEditor::create((($eventObj instanceof UserLoginForm) ? 'user' : 'admin'), ($eventObj->user !== null ? $eventObj->userID : 0), $eventObj->username, TIME_NOW, WCF::getSession()->ipAddress, WCF::getSession()->userAgent);
				}
			}
			else if ($eventName == 'assignVariables') {
				if ($this->useCaptcha) {
					WCF::getTPL()->assign(array(
						'captchaID' => $this->captchaID,
						'errorField' => $eventObj->errorField,
						'errorType' => $eventObj->errorType
					));
					WCF::getTPL()->append('additionalFields', WCF::getTPL()->fetch('captcha'));
					WCF::getTPL()->clearAssign('captchaID');
				}
			}
		}
	}
}
?>