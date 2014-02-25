<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Does the user logout in the admin control panel.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class LogoutAction extends AbstractSecureAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// validate
		$this->validate();
		
		// do logout
		$this->doLogout();
		$this->executed();
		
		// forward to index page
		// warning: if doLogout() writes a cookie this is buggy in MS IIS
		HeaderUtil::redirect('index.php?packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Validates the active user.
	 * Throws an IllegalLinkException if the active user can not logout.
	 */
	protected function validate() {
		if (!WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Changes the user of the active session to guest.
	 */
	protected function doLogout() {
		require_once(WCF_DIR.'lib/system/session/UserSession.class.php');
		WCF::getSession()->delete();
		//WCF::getSession()->changeUser(new UserSession());
	}
}
?>