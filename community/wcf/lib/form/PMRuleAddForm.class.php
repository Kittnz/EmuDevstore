<?php
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(WCF_DIR.'lib/data/message/pm/rule/PMRuleEditor.class.php');

/**
 * Shows the form for adding new rules.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	form
 * @category 	Community Framework (commercial)
 */
class PMRuleAddForm extends AbstractForm {
	// system
	public $templateName = 'pmRuleAdd';
	
	// parameters
	public $title = '';
	public $logicalOperator = '';
	public $ruleAction = '';
	public $ruleDestination = '';
	public $ruleConditions = array();
	public $enabled = 1;
	
	/**
	 * list of available rule actions
	 * 
	 * @var	array
	 */
	public $availableRuleActions = array();
	
	/**
	 * list of available rule conditions
	 * 
	 * @var	array<PMRuleConditionType>
	 */
	public $availableRuleConditionTypes = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get available conditions
		$this->availableRuleConditionTypes = PMRule::getAvailableRuleConditionTypes();
		// get available actions
		$this->availableRuleActions = PMRule::getAvailableRuleActions();
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->enabled = 0;
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['enabled'])) $this->enabled = intval($_POST['enabled']);
		if (isset($_POST['logicalOperator'])) $this->logicalOperator = $_POST['logicalOperator'];
		if (isset($_POST['ruleAction'])) $this->ruleAction = $_POST['ruleAction'];
		if (isset($_POST['ruleDestination'])) $this->ruleDestination = $_POST['ruleDestination'];
		if (isset($_POST['ruleConditions']) && is_array($_POST['ruleConditions'])) $this->ruleConditions = $_POST['ruleConditions'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// title
		if (empty($this->title)) {
			throw new UserInputException('title');
		}
		
		// conditions
		if (!count($this->ruleConditions)) {
			throw new UserInputException('ruleConditions');
		}
		foreach ($this->ruleConditions as $ruleCondition) {
			$type = (isset($ruleCondition['type']) ? $ruleCondition['type'] : '');
			$condition = (isset($ruleCondition['condition']) ? $ruleCondition['condition'] : '');
			$value = (isset($ruleCondition['value']) ? $ruleCondition['value'] : '');
			
			// type
			if (!isset($this->availableRuleConditionTypes[$type])) {
				throw new UserInputException('ruleConditions');
			}
			// condition
			$availableConditions = $this->availableRuleConditionTypes[$type]->getAvailableConditions();
			if (count($availableConditions) > 0 && !isset($availableConditions[$condition])) {
				throw new UserInputException('ruleConditions');
			}
			// value
			$availableValues = $this->availableRuleConditionTypes[$type]->getAvailableValues();
			if (($this->availableRuleConditionTypes[$type]->getValueType() == 'text' && empty($value)) || ($this->availableRuleConditionTypes[$type]->getValueType() == 'options' && !isset($availableValues[$value]))) {
				throw new UserInputException('ruleConditions');
			}
		}
		
		// operator
		if ($this->logicalOperator != 'and' && $this->logicalOperator != 'or' && $this->logicalOperator != 'nor') {
			throw new UserInputException('logicalOperator');
		}
		
		// action
		if (!isset($this->availableRuleActions[$this->ruleAction])) {
			throw new UserInputException('ruleAction');
		}
		// destination
		$availableDestinations = $this->availableRuleActions[$this->ruleAction]->getAvailableDestinations();
		if (($this->availableRuleActions[$this->ruleAction]->getDestinationType() == 'text' && empty($this->ruleDestination)) || ($this->availableRuleActions[$this->ruleAction]->getDestinationType() == 'options' && !isset($availableDestinations[$this->ruleDestination]))) {
			throw new UserInputException('ruleAction');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save rule
		PMRuleEditor::create(WCF::getUser()->userID, $this->title, $this->logicalOperator, $this->ruleConditions, $this->ruleAction, $this->ruleDestination, intval(!$this->enabled));
		$this->saved();
		
		// reset values
		$this->title = $this->logicalOperator = $this->ruleAction = $this->ruleDestination = '';
		$this->ruleConditions = array();
		$this->enabled = 1;
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'title' => $this->title,
			'logicalOperator' => $this->logicalOperator,
			'ruleAction' => $this->ruleAction,
			'ruleDestination' => $this->ruleDestination,
			'ruleConditions' => $this->ruleConditions,
			'enabled' => $this->enabled,
			'availableRuleActions' => $this->availableRuleActions,
			'availableRuleConditionTypes' => $this->availableRuleConditionTypes,
			'action' => 'add'
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!MODULE_PM) {
			throw new IllegalLinkException();
		}
		
		// check permission
		WCF::getUser()->checkPermission('user.pm.canUsePm');
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		parent::show();
	}
}
?>