<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/pm/rule/conditionType/PMRuleConditionType.class.php');

/**
 * Provides default implementations for rule condition types.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm.rule.conditionType
 * @category 	Community Framework (commercial)
 */
abstract class AbstractPMRuleConditionType implements PMRuleConditionType {
	/**
	 * @see PMRuleConditionType::getAvailableConditions()
	 */
	public function getAvailableConditions() {
		return array();
	}
	
	/**
	 * @see PMRuleConditionType::getValueType()
	 */
	public function getValueType() {
		return '';
	}
	
	/**
	 * @see PMRuleConditionType::getAvailableValues()
	 */
	public function getAvailableValues() {
		return array();
	}
}
?>