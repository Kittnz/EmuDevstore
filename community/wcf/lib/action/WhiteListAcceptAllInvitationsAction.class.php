<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

/**
 * Accepts all white list invitations. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	action
 * @category 	Community Framework
 */
class WhiteListAcceptAllInvitationsAction extends AbstractSecureAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permissions
		if (!WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}
		
		// accept all invitations 
		$sql = "REPLACE INTO	wcf".WCF_N."_user_whitelist
					(userID, whiteUserID, confirmed)
			SELECT		whiteUserID, userID, 1
			FROM		wcf".WCF_N."_user_whitelist
			WHERE		whiteUserID = ".WCF::getUser()->userID."
					AND confirmed = 0";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE		wcf".WCF_N."_user_whitelist
			SET		confirmed = 1
			WHERE		whiteUserID = ".WCF::getUser()->userID."
					AND confirmed = 0";
		WCF::getDB()->sendQuery($sql);
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?form=WhiteListEdit'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>