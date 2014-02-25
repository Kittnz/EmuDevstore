<?php
require_once(WCF_DIR.'lib/data/message/pm/rule/PMRule.class.php');

/**
 * Provides functions to manage rules.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm.rule
 * @category 	Community Framework (commercial)
 */
class PMRuleEditor extends PMRule {
	/**
	 * Creates a new rule.
	 * 
	 * @param	string		$title
	 * @param	string		$logicalOperator
	 * @param	array		$ruleConditions
	 * @param	string		$action
	 * @param	string		$destination
	 * @param	integer		$disabled
	 * @return	integer		new rule id
	 */
	public static function create($userID, $title, $logicalOperator, $ruleConditions, $action, $destination, $disabled = 0) {
		// create rule
		$sql = "INSERT INTO	wcf".WCF_N."_pm_rule
					(userID, title, logicalOperator, ruleAction, ruleDestination, disabled)
			VALUES		(".$userID.", '".escapeString($title)."', '".$logicalOperator."', '".escapeString($action)."', '".escapeString($destination)."', ".$disabled.")";
		WCF::getDB()->sendQuery($sql);
		$ruleID = WCF::getDB()->getInsertID("wcf".WCF_N."_pm_rule", 'ruleID');
		
		// create conditions
		$inserts = '';
		foreach ($ruleConditions as $ruleCondition) {
			$type = (isset($ruleCondition['type']) ? $ruleCondition['type'] : '');
			$condition = (isset($ruleCondition['condition']) ? $ruleCondition['condition'] : '');
			$value = (isset($ruleCondition['value']) ? $ruleCondition['value'] : '');
			
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$ruleID.", '".escapeString($type)."', '".escapeString($condition)."', '".escapeString($value)."')";
		}
		$sql = "INSERT INTO	wcf".WCF_N."_pm_rule_condition
					(ruleID, ruleConditionType, ruleCondition, ruleConditionValue)
			VALUES		".$inserts;
		WCF::getDB()->sendQuery($sql);
		
		return $ruleID;
	}
	
	/**
	 * Updates an existing rule.
	 *
	 * @param	string		$title
	 * @param	string		$logicalOperator
	 * @param	array		$ruleConditions
	 * @param	string		$action
	 * @param	string		$destination
	 * @param	integer		$disabled
	 */
	public function update($title, $logicalOperator, $ruleConditions, $action, $destination, $disabled = 0) {
		// update rule
		$sql = "UPDATE	wcf".WCF_N."_pm_rule
			SET	title = '".escapeString($title)."',
				logicalOperator = '".$logicalOperator."',
				ruleAction = '".escapeString($action)."',
				ruleDestination = '".escapeString($destination)."',
				disabled = ".$disabled."
			WHERE	ruleID = ".$this->ruleID;
		WCF::getDB()->sendQuery($sql);
		
		// delete old conditions
		$sql = "DELETE FROM	wcf".WCF_N."_pm_rule_condition
			WHERE		ruleID = ".$this->ruleID;
		WCF::getDB()->sendQuery($sql);
		
		// save conditions
		$inserts = '';
		foreach ($ruleConditions as $ruleCondition) {
			$type = (isset($ruleCondition['type']) ? $ruleCondition['type'] : '');
			$condition = (isset($ruleCondition['condition']) ? $ruleCondition['condition'] : '');
			$value = (isset($ruleCondition['value']) ? $ruleCondition['value'] : '');
			
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$this->ruleID.", '".escapeString($type)."', '".escapeString($condition)."', '".escapeString($value)."')";
		}
		$sql = "INSERT INTO	wcf".WCF_N."_pm_rule_condition
					(ruleID, ruleConditionType, ruleCondition, ruleConditionValue)
			VALUES		".$inserts;
		WCF::getDB()->sendQuery($sql);
	}

	/**
	 * Deletes this rule.
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_pm_rule_condition
			WHERE		ruleID = ".$this->ruleID;
		WCF::getDB()->sendQuery($sql);
		
		$sql = "DELETE FROM	wcf".WCF_N."_pm_rule
			WHERE		ruleID = ".$this->ruleID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Disables this rule.
	 */
	public function disable() {
		$sql = "UPDATE	wcf".WCF_N."_pm_rule
			SET	disabled = 1
			WHERE	ruleID = ".$this->ruleID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Enables this rule.
	 */
	public function enable() {
		$sql = "UPDATE	wcf".WCF_N."_pm_rule
			SET	disabled = 0
			WHERE	ruleID = ".$this->ruleID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>