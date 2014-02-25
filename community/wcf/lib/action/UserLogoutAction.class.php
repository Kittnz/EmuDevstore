<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/LogoutAction.class.php');

/**
 * Does the user logout in the user interface.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	action
 * @category 	Community Framework
 */
class UserLogoutAction extends LogoutAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		AbstractAction::execute();
				
		// validate
		$this->validate();
		
		// do logout
		$this->doLogout();
		$this->executed();
		
		// redirect to url
		WCF::getTPL()->assign(array(
			'url' => 'index.php'.SID_ARG_1ST,
			'message' => WCF::getLanguage()->get('wcf.user.logout.redirect'),
			'wait' => 5
		));
		WCF::getTPL()->display('redirect');
		exit;
	}
	
	/**
	 * @see LogoutAction::doLogout()
	 */
	protected function doLogout() {
		parent::doLogout();
		
		// remove cookies
		if (isset($_COOKIE[COOKIE_PREFIX.'userID'])) {
			HeaderUtil::setCookie('userID', 0);
		}
		if (isset($_COOKIE[COOKIE_PREFIX.'password'])) {
			HeaderUtil::setCookie('password', '');
		}
	}
}
?>