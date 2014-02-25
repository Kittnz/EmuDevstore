<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/user/login/FailedLoginList.class.php');

/**
 * Shows a list of failed user logins.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.security.login
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class FailedLoginListPage extends SortablePage {
	// system
	public $templateName = 'failedLoginList';
	public $defaultSortField = 'time';
	public $defaultSortOrder = 'DESC';
	
	/**
	 * failed login list object
	 * 
	 * @var	FailedLoginList
	 */
	public $failedLoginList = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->failedLoginList = new FailedLoginList();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read objects
		$this->failedLoginList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->failedLoginList->sqlLimit = $this->itemsPerPage;
		$this->failedLoginList->sqlOrderBy = $this->sortField." ".$this->sortOrder;
		$this->failedLoginList->readObjects();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'failedLoginID':
			case 'environment':
			case 'username':
			case 'time':
			case 'ipAddress':
			case 'userAgent': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->failedLoginList->countObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'failedLogins' => $this->failedLoginList->getObjects()
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.log.failedLogin');
		
		// check permission
		WCF::getUser()->checkPermission('admin.system.canViewLog');
		
		parent::show();
	}
}
?>