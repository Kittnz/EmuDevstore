<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

/**
 * Deletes all entries in the white list of the active user. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	action
 * @category 	Community Framework
 */
class WhiteListEmptyAction extends AbstractSecureAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// delete entries
		$sql = "DELETE FROM	wcf".WCF_N."_user_whitelist
			WHERE		userID = ".WCF::getUser()->userID."
					AND confirmed = 1";
		WCF::getDB()->sendQuery($sql);
		$sql = "DELETE FROM	wcf".WCF_N."_user_whitelist
			WHERE		whiteUserID = ".WCF::getUser()->userID."
					AND confirmed = 1";
		WCF::getDB()->sendQuery($sql);
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?form=WhiteListEdit'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>