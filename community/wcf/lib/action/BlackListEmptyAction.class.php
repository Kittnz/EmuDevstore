<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

/**
 * Deletes all entries in the black list of the active user. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	action
 * @category 	Community Framework
 */
class BlackListEmptyAction extends AbstractSecureAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// delete entries
		$sql = "DELETE FROM	wcf".WCF_N."_user_blacklist
			WHERE		userID = ".WCF::getUser()->userID;
		WCF::getDB()->sendQuery($sql);
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?form=BlackListEdit'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>