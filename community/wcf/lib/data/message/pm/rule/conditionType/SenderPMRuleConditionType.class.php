<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/pm/rule/conditionType/AbstractPMRuleConditionType.class.php');

/**
 * Checks the sender of private messages.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm.rule.conditionType
 * @category 	Community Framework (commercial)
 */
class SenderPMRuleConditionType extends AbstractPMRuleConditionType {
	/**
	 * @see PMRuleConditionType::check()
	 */
	public function check(PMEditor $pm, PMRule $rule, PMRuleCondition $condition, UserProfile $recipient) {
		return $this->checkCondition($condition, $pm->username);
	}
	
	/**
	 * Checks the condition.
	 *
	 * @param	PMRuleCondition		$condition
	 * @param	string			$string
	 * @return	boolean
	 */
	protected function checkCondition(PMRuleCondition $condition, $string) {
		$value = StringUtil::toLowerCase($condition->ruleConditionValue);
		$string = StringUtil::toLowerCase($string);
		
		switch ($condition->ruleCondition) {
			case 'contains': 
				if (StringUtil::indexOf($string, $value) !== false) return true;
				break;
			case 'dontContains': 
				if (StringUtil::indexOf($string, $value) === false) return true;
				break;
			case 'beginsWith': 
				if (StringUtil::indexOf($string, $value) === 0) return true;
				break;
			case 'endsWith': 
				if (StringUtil::substring($string, -1 * StringUtil::length($value)) == $value) return true;
				break;
			case 'isEqualTo': 
				if ($value == $string) return true;
				break;
		}
		
		return false;
	}
	
	/**
	 * @see PMRuleConditionType::getAvailableConditions()
	 */
	public function getAvailableConditions() {
		return array(
			'contains' => WCF::getLanguage()->get('wcf.pm.rule.condition.type.message.contains'),
			'dontContains' => WCF::getLanguage()->get('wcf.pm.rule.condition.type.message.dontContains'),
			'beginsWith' => WCF::getLanguage()->get('wcf.pm.rule.condition.type.message.beginsWith'),
			'endsWith' => WCF::getLanguage()->get('wcf.pm.rule.condition.type.message.endsWith'),
			'isEqualTo' => WCF::getLanguage()->get('wcf.pm.rule.condition.type.message.isEqualTo')
		);
	}
	
	/**
	 * @see PMRuleConditionType::getValueType()
	 */
	public function getValueType() {
		return 'text';
	}
}
?>