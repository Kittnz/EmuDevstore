<?php
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMEditor.class.php');
require_once(WCF_DIR.'lib/data/message/pm/rule/PMRuleCondition.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');

/**
 * Represents a rule for private messages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm.rule
 * @category 	Community Framework (commercial)
 */
class PMRule extends DatabaseObject {
	/**
	 * list of available rule condition types
	 * 
	 * @var	array<PMRuleConditionType>
	 */
	public static $availableRuleConditionTypes = null;
	
	/**
	 * list of available rule actions
	 * 
	 * @var	array<PMRuleAction>
	 */
	public static $availableRuleActions = null;

	/**
	 * Creates a new PMRule object.
	 * 
	 * @param	integer		$ruleID
	 * @param	array		$row
	 */
	public function __construct($ruleID, $row = null) {
		if ($ruleID !== null) {
			$sql = "SELECT		*
				FROM 		wcf".WCF_N."_pm_rule
				WHERE 		ruleID = ".$ruleID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		parent::__construct($row);
	}
	
	/**
	 * Applies this rule.
	 *
	 * @param	PMEditor	$pm
	 * @param	UserProfile	$recipient
	 * @return	boolean		false, to stop the execution of other rules
	 */
	public function apply(PMEditor $pm, UserProfile $recipient) {
		// check conditions
		$result = ($this->logicalOperator == 'or' ? false : true);
		foreach ($this->getConditions() as $condition) {
			switch ($this->logicalOperator) {
				case 'or':
					$result = $result || $condition->check($pm, $this, $recipient);
					break;
				case 'and':
					$result = $result && $condition->check($pm, $this, $recipient);
					break;
				case 'nor':
					$result = $result && !$condition->check($pm, $this, $recipient);
					break;
			}
		}
		
		// apply action
		if ($result) {
			$actionObject = self::getActionObject($this->ruleAction);
			return $actionObject->execute($pm, $this, $recipient);
		}
		
		return true;
	}
	
	/**
	 * Returns the conditions of this rule.
	 *
	 * @return	array<PMRuleCondition>
	 */
	public function getConditions() {
		if (!isset($this->data['conditions'])) {
			$this->data['conditions'] = array();
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_pm_rule_condition
				WHERE	ruleID = ".$this->ruleID;
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->data['conditions'][] = new PMRuleCondition(null, $row);
			}
		}
		
		return $this->conditions;
	}
	
	/**
	 * Returns a specific action object.
	 *
	 * @param	string		$action
	 * @return	PMRuleAction
	 */
	public static function getActionObject($action) {
		$actions = self::getAvailableRuleActions();
		if (!isset($actions[$action])) {
			throw new SystemException("Unknown pm rule action '".$action."'", 11000);
		}
		
		return $actions[$action];
	}
	
	/**
	 * Returns a list of available rule condition types.
	 * 
	 * @return	array<PMRuleConditionType>
	 */
	public static function getAvailableRuleConditionTypes() {
		if (self::$availableRuleConditionTypes === null) {
			WCF::getCache()->addResource('ruleConditionTypes', WCF_DIR.'cache/cache.ruleConditionTypes.php', WCF_DIR.'lib/system/cache/CacheBuilderPMRuleConditionTypes.class.php');
			$types = WCF::getCache()->get('ruleConditionTypes');
			foreach ($types as $type) {
				// get path to class file
				if (empty($type['packageDir'])) {
					$path = WCF_DIR;
				}
				else {						
					$path = FileUtil::getRealPath(WCF_DIR.$type['packageDir']);
				}
				$path .= $type['ruleConditionTypeClassFile'];
				
				// include class file
				if (!class_exists($type['ruleConditionTypeClassName'])) {
					if (!file_exists($path)) {
						throw new SystemException("Unable to find class file '".$path."'", 11000);
					}
					require_once($path);
				}
				
				// instance object
				if (!class_exists($type['ruleConditionTypeClassName'])) {
					throw new SystemException("Unable to find class '".$type['ruleConditionTypeClassName']."'", 11001);
				}
				self::$availableRuleConditionTypes[$type['ruleConditionType']] = new $type['ruleConditionTypeClassName'];
			}
		}
		
		return self::$availableRuleConditionTypes;
	}
	
	/**
	 * Returns a list of available rule actions.
	 * 
	 * @return	array<PMRuleAction>
	 */
	public static function getAvailableRuleActions() {
		if (self::$availableRuleActions === null) {
			WCF::getCache()->addResource('ruleActions', WCF_DIR.'cache/cache.ruleActions.php', WCF_DIR.'lib/system/cache/CacheBuilderPMRuleActions.class.php');
			$actions = WCF::getCache()->get('ruleActions');
			foreach ($actions as $action) {
				// get path to class file
				if (empty($action['packageDir'])) {
					$path = WCF_DIR;
				}
				else {						
					$path = FileUtil::getRealPath(WCF_DIR.$action['packageDir']);
				}
				$path .= $action['ruleActionClassFile'];
				
				// include class file
				if (!class_exists($action['ruleActionClassName'])) {
					if (!file_exists($path)) {
						throw new SystemException("Unable to find class file '".$path."'", 11000);
					}
					require_once($path);
				}
				
				// instance object
				if (!class_exists($action['ruleActionClassName'])) {
					throw new SystemException("Unable to find class '".$action['ruleActionClassName']."'", 11001);
				}
				self::$availableRuleActions[$action['ruleAction']] = new $action['ruleActionClassName'];
			}
		}
		
		return self::$availableRuleActions;
	}
}
?>