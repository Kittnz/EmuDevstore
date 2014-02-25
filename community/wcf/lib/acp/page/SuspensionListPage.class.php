<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/SuspensionList.class.php');

/**
 * Shows a list of predefined suspensions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class SuspensionListPage extends SortablePage {
	// system
	public $templateName = 'suspensionList';
	public $defaultSortField = 'title';
	public $deletedSuspensionID = 0;
	
	/**
	 * suspension list object
	 * 
	 * @var	SuspensionList
	 */
	public $suspensionList = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->suspensionList = new SuspensionList();
		if (isset($_REQUEST['deletedSuspensionID'])) $this->deletedSuspensionID = intval($_REQUEST['deletedSuspensionID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read objects
		$this->suspensionList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->suspensionList->sqlLimit = $this->itemsPerPage;
		$this->suspensionList->sqlOrderBy = ($this->sortField != 'suspensions' ? 'suspension.' : '').$this->sortField." ".$this->sortOrder;
		$this->suspensionList->readObjects();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'suspensionID':
			case 'packageID':
			case 'title':
			case 'points':
			case 'expires':
			case 'suspensionType':
			case 'suspensions': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->suspensionList->countObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'suspensions' => $this->suspensionList->getObjects(),
			'deletedSuspensionID' => $this->deletedSuspensionID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.user.infraction.suspension.view');
		
		// check permission
		// WCF::getUser()->checkPermission(array('admin.user.infraction.canEditSuspension', 'admin.user.infraction.canEditSuspension'));
		
		parent::show();
	}
}
?>