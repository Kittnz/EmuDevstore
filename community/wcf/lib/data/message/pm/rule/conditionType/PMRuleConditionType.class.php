<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/pm/PMEditor.class.php');
require_once(WCF_DIR.'lib/data/message/pm/rule/PMRule.class.php');
require_once(WCF_DIR.'lib/data/message/pm/rule/PMRuleCondition.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');

/**
 * Any rule condition type should implement this interface.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm.rule.conditionType
 * @category 	Community Framework (commercial)
 */
interface PMRuleConditionType {
	/**
	 * Checks this condition.
	 *
	 * @param	PMEditor	$pm
	 * @param	PMRule		$rule
	 * @param	PMRuleCondition $condition
	 * @param 	UserProfile	$recipient
	 * @return	boolean
	 */
	public function check(PMEditor $pm, PMRule $rule, PMRuleCondition $condition, UserProfile $recipient);
	
	/**
	 * Returns a list of available conditions.
	 * 
	 * @var	array
	 */
	public function getAvailableConditions();
	
	/**
	 * Returns the value type.
	 * 
	 * @var	string
	 */
	public function getValueType();
	
	/**
	 * Returns a list of available values.
	 * 
	 * @var	array
	 */
	public function getAvailableValues();
}
?>