<?php
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMEditor.class.php');
require_once(WCF_DIR.'lib/data/message/pm/rule/PMRule.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');

/**
 * Represents a condition of a rule.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm.rule
 * @category 	Community Framework (commercial)
 */
class PMRuleCondition extends DatabaseObject {
	/**
	 * Creates a new PMRuleCondition object.
	 * 
	 * @param	integer		$ruleConditionID
	 * @param	array		$row
	 */
	public function __construct($ruleConditionID, $row = null) {
		if ($ruleConditionID !== null) {
			$sql = "SELECT		*
				FROM 		wcf".WCF_N."_pm_rule_condition
				WHERE 		ruleConditionID = ".$ruleConditionID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		parent::__construct($row);
	}
	
	/**
	 * Checks this condition.
	 *
	 * @param	PMEditor	$pm
	 * @param	PMRule		$rule
	 * @param 	UserProfile	$recipient
	 * @return	boolean
	 */
	public function check(PMEditor $pm, PMRule $rule, UserProfile $recipient) {
		$conditionTypeObject = self::getConditionTypeObject($this->ruleConditionType);
		return $conditionTypeObject->check($pm, $rule, $this, $recipient);
	}
	
	/**
	 * Returns a specific condition type object.
	 *
	 * @param	string		$conditionType
	 * @return	PMRuleConditionType
	 */
	public static function getConditionTypeObject($conditionType) {
		$types = PMRule::getAvailableRuleConditionTypes();
		if (!isset($types[$conditionType])) {
			throw new SystemException("Unknown pm condition type '".$conditionType."'", 11000);
		}
		
		return $types[$conditionType];
	}
}
?>