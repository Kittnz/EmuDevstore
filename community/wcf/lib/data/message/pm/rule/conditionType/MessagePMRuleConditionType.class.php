<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/pm/rule/conditionType/SenderPMRuleConditionType.class.php');

/**
 * Checks the message body of private messages.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm.rule.conditionType
 * @category 	Community Framework (commercial)
 */
class MessagePMRuleConditionType extends SenderPMRuleConditionType {
	/**
	 * @see PMRuleConditionType::check()
	 */
	public function check(PMEditor $pm, PMRule $rule, PMRuleCondition $condition, UserProfile $recipient) {
		return $this->checkCondition($condition, $pm->message);
	}
}
?>