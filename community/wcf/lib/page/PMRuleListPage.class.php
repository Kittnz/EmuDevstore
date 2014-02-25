<?php
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/message/pm/rule/PMRuleList.class.php');

/**
 * Shows a list of all rules.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	page
 * @category 	Community Framework (commercial)
 */
class PMRuleListPage extends SortablePage {
	// system
	public $templateName = 'pmRuleList';
	public $defaultSortField = 'title';
	public $deletedRuleID = 0;
	
	/**
	 * rule list object
	 * 
	 * @var	PMRuleList
	 */
	public $ruleList = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->ruleList = new PMRuleList();
		$this->ruleList->sqlConditions = 'pm_rule.userID = '.WCF::getUser()->userID;
		if (isset($_REQUEST['deletedRuleID'])) $this->deletedRuleID = intval($_REQUEST['deletedRuleID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read objects
		$this->ruleList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->ruleList->sqlLimit = $this->itemsPerPage;
		$this->ruleList->sqlOrderBy = ($this->sortField != 'conditions' ? 'pm_rule.' : '').$this->sortField." ".$this->sortOrder;
		$this->ruleList->readObjects();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'rule':
			case 'title':
			case 'disabled':
			case 'conditions': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->ruleList->countObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'rules' => $this->ruleList->getObjects(),
			'deletedRuleID' => $this->deletedRuleID
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