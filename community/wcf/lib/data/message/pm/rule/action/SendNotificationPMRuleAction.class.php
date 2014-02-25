<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/pm/rule/action/AbstractPMRuleAction.class.php');

/**
 * Sends an e-mail notification.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm.rule.action
 * @category 	Community Framework (commercial)
 */
class SendNotificationPMRuleAction extends AbstractPMRuleAction {
	/**
	 * @see PMRuleAction::execute()
	 */
	public function execute(PMEditor $pm, PMRule $rule, UserProfile $recipient) {
		$pm->sendNotifications(array($recipient));
		return true;
	}
}
?>