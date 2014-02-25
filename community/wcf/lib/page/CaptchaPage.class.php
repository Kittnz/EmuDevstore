<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/data/image/captcha/Captcha.class.php');
require_once(WCF_DIR.'lib/data/image/captcha/CaptchaImage.class.php');

/**
 * Shows the captcha image.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.image.captcha
 * @subpackage	page
 * @category 	Community Framework
 */
class CaptchaPage extends AbstractPage {
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
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if ($this->action != 'newCaptchaID') {
			if (isset($_REQUEST['captchaID'])) $this->captchaID = intval($_REQUEST['captchaID']);
			$this->captcha = new Captcha($this->captchaID);
			if (!$this->captcha->captchaID) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();
		
		if ($this->action == 'newCaptchaID') {
			// get new captcha id
			$captchaID = Captcha::create();
			
			// send header
			header('Content-type: text/xml');
			HeaderUtil::sendNoCacheHeaders();
			
			// print xml
			echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n<captchaid>".$captchaID."</captchaid>";
			exit;
		}
		else {
			// send header
			header("Content-type: image/png");
	
			// show image
			$image = new CaptchaImage($this->captcha->captchaString);
		}
	}
}
?>