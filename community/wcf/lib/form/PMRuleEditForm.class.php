<?php
require_once(WCF_DIR.'lib/form/PMRuleAddForm.class.php');

/**
 * Shows the form for editing existing rules.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	form
 * @category 	Community Framework (commercial)
 */
class PMRuleEditForm extends PMRuleAddForm {
	/**
	 * rule id
	 * 
	 * @var	integer
	 */
	public $ruleID = 0;
	
	/**
	 * rule editor object
	 * 
	 * @var	PMRuleEditor
	 */
	public $rule = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['ruleID'])) $this->ruleID = intval($_REQUEST['ruleID']);
		$this->rule = new PMRuleEditor($this->ruleID);
		if (!$this->rule->ruleID || !WCF::getUser()->userID || $this->rule->userID != WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save rule
		$this->rule->update($this->title, $this->logicalOperator, $this->ruleConditions, $this->ruleAction, $this->ruleDestination, intval(!$this->enabled));
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// set default values
		if (!count($_POST)) {
			$this->title = $this->rule->title;
			$this->logicalOperator = $this->rule->logicalOperator;
			$this->enabled = intval(!$this->rule->disabled);
			$this->ruleAction = $this->rule->ruleAction;
			$this->ruleDestination = $this->rule->ruleDestination;
			
			// conditions
			foreach ($this->rule->getConditions() as $condition) {
				$this->ruleConditions[] = array(
					'type' => $condition->ruleConditionType,
					'condition' => $condition->ruleCondition,
					'value' => $condition->ruleConditionValue
				);
			}
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'ruleID' => $this->ruleID,
			'rule' => $this->rule,
			'action' => 'edit'
		));
	}
}
?>