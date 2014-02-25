<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/WarningList.class.php');

/**
 * Shows a list of predefined warnings.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class WarningListPage extends SortablePage {
	// system
	public $templateName = 'warningList';
	public $defaultSortField = 'title';
	public $deletedWarningID = 0;
	
	/**
	 * warning list object
	 * 
	 * @var	WarningList
	 */
	public $warningList = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->warningList = new WarningList();
		if (isset($_REQUEST['deletedWarningID'])) $this->deletedWarningID = intval($_REQUEST['deletedWarningID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read objects
		$this->warningList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->warningList->sqlLimit = $this->itemsPerPage;
		$this->warningList->sqlOrderBy = ($this->sortField != 'warnings' ? 'warning.' : '').$this->sortField." ".$this->sortOrder;
		$this->warningList->readObjects();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'warningID':
			case 'packageID':
			case 'title':
			case 'points':
			case 'expires':
			case 'warnings': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->warningList->countObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'warnings' => $this->warningList->getObjects(),
			'deletedWarningID' => $this->deletedWarningID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.user.infraction.warning.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.user.infraction.canEditWarning', 'admin.user.infraction.canDeleteWarning'));
		
		parent::show();
	}
}
?>