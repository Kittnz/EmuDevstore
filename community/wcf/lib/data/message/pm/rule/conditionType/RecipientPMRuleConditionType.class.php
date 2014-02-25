<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/pm/rule/conditionType/SenderPMRuleConditionType.class.php');

/**
 * Checks the recipients of private messages.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm.rule.conditionType
 * @category 	Community Framework (commercial)
 */
class RecipientPMRuleConditionType extends SenderPMRuleConditionType {
	/**
	 * @see PMRuleConditionType::check()
	 */
	public function check(PMEditor $pm, PMRule $rule, PMRuleCondition $condition, UserProfile $recipient) {
		$result = true;
		foreach ($pm->getRecipients() as $aRecipient) {
			$result = $result && $this->checkCondition($condition, $aRecipient->recipient);
		}
		
		return $result;
	}
}
?>