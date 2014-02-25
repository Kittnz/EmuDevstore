<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

/**
 * Disables whitelist notifications. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	action
 * @category 	Community Framework
 */
class WhiteListNotificationDisableAction extends AbstractSecureAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// delete entries
		$sql = "UPDATE	wcf".WCF_N."_user_whitelist
			SET	notified = 1
			WHERE	whiteUserID = ".WCF::getUser()->userID."
				AND confirmed = 0";
		WCF::getDB()->sendQuery($sql);
		// update session
		WCF::getSession()->resetUserData();
		$this->executed();
		
		// forward
		if (!isset($_REQUEST['ajax'])) HeaderUtil::redirect('index.php?form=WhiteListEdit'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>