<?php
// wcf imports
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(WCF_DIR.'lib/data/image/captcha/Captcha.class.php');

/**
 * CaptchaForm is an abstract form implementation for the use of a captcha.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.image.captcha
 * @subpackage	form
 * @category 	Community Framework
 */
abstract class CaptchaForm extends AbstractForm {
	/**
	 * captcha string
	 *
	 * @var string
	 */
	public $captchaString = '';
	
	/**
	 * captcha object
	 *
	 * @var Captcha
	 */
	public $captcha = null;
	
	/**
	 * captcha id
	 *
	 * @var integer
	 */
	public $captchaID = 0;
	
	/**
	 * true enables the captcha
	 *
	 * @var boolean
	 */
	public $useCaptcha = true;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (WCF::getUser()->userID || WCF::getSession()->getVar('captchaDone')) {
			$this->useCaptcha = false;
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['captchaID'])) $this->captchaID = intval($_POST['captchaID']);
		if (isset($_POST['captchaString'])) $this->captchaString = StringUtil::trim($_POST['captchaString']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->validateCaptcha();
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
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// delete captcha
		if ($this->useCaptcha) {
			$this->captcha->delete();
		}
		
		WCF::getSession()->unregister('captchaDone');
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
		
		WCF::getTPL()->assign('captchaID', $this->captchaID);
	}
}
?>