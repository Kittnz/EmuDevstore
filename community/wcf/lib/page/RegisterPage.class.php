<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Handles all requests on the register.php script.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	page
 * @category 	Community Framework
 */
class RegisterPage extends AbstractPage {
	protected $decline = false;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['decline'])) $this->decline = $_REQUEST['decline'];
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// disclaimer declined
		if ($this->decline !== false) {
			HeaderUtil::redirect('index.php'.SID_ARG_1ST);
			exit;
		}
		
		// email reactivation (move to usercp)
		if (REGISTER_ACTIVATION_METHOD == 1) {
			switch ($this->action) {
				case 'enable':
				case 'a':
					require_once(WCF_DIR.'lib/form/RegisterActivationForm.class.php');
					new RegisterActivationForm();
					exit;
				
				case 'newActivationCode':
					require_once(WCF_DIR.'lib/form/RegisterNewActivationCodeForm.class.php');
					new RegisterNewActivationCodeForm();
					exit;
					
				case 'reenable':
				case 'r':
					require_once(WCF_DIR.'lib/form/EmailActivationForm.class.php');
					new EmailActivationForm();
					exit;
					
				case 'newReactivationCode':
					require_once(WCF_DIR.'lib/form/EmailNewActivationCodeForm.class.php');
					new EmailNewActivationCodeForm();
					exit;
			}
		}
		
		// user is already registered
		if (WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		// registration disabled
		if (REGISTER_DISABLED) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.register.error.disabled'));
		}
		
		parent::show();
		
		// show page
		switch ($this->action) {
			case '':
				if (REGISTER_ENABLE_DISCLAIMER) {
					require_once(WCF_DIR.'lib/page/RegisterDisclaimerPage.class.php');
					new RegisterDisclaimerPage();
					break;
				}
			
			default:
				require_once(WCF_DIR.'lib/form/RegisterForm.class.php');
				new RegisterForm();
		}
	}
}
?>