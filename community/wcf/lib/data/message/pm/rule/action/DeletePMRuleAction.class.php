<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/pm/rule/action/AbstractPMRuleAction.class.php');

/**
 * Deletes incoming private messages.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm.rule.action
 * @category 	Community Framework (commercial)
 */
class DeletePMRuleAction extends AbstractPMRuleAction {
	/**
	 * @see PMRuleAction::execute()
	 */
	public function execute(PMEditor $pm, PMRule $rule, UserProfile $recipient) {
		// delete message (move to trash)
		$sql = "UPDATE	wcf".WCF_N."_pm_to_user
			SET	isDeleted = 1
			WHERE	pmID = ".$pm->pmID."
				AND recipientID = ".$recipient->userID;
		WCF::getDB()->sendQuery($sql);
		
		// return false to stop the execution of other actions
		return false;
	}
}
?>