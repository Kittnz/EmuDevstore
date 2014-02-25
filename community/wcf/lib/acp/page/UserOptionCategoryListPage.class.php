<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/user/option/category/UserOptionCategoryList.class.php');

/**
 * Shows a list of user option categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.option
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class UserOptionCategoryListPage extends SortablePage {
	// system
	public $templateName = 'userOptionCategoryList';
	public $defaultSortField = 'showOrder';
	public $deletedCategoryID = 0;
	
	/**
	 * user option category list object
	 * 
	 * @var	UserOptionCategoryList
	 */
	public $userOptionCategoryList = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['deletedCategoryID'])) $this->deletedCategoryID = intval($_REQUEST['deletedCategoryID']);
		$this->userOptionCategoryList = new UserOptionCategoryList();
		$this->userOptionCategoryList->sqlConditions = "option_category.parentCategoryName = 'profile'";
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read objects
		$this->userOptionCategoryList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->userOptionCategoryList->sqlLimit = $this->itemsPerPage;
		$this->userOptionCategoryList->sqlOrderBy = ($this->sortField != 'options' ? 'option_category.' : '').$this->sortField." ".$this->sortOrder;
		$this->userOptionCategoryList->readObjects();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'categoryID':
			case 'packageID':
			case 'categoryName':
			case 'options':
			case 'showOrder': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->userOptionCategoryList->countObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'userOptionCategories' => $this->userOptionCategoryList->getObjects(),
			'deletedCategoryID' => $this->deletedCategoryID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.user.option.category.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.user.option.canEditOptionCategory', 'admin.user.option.canDeleteOptionCategory'));
		
		parent::show();
	}
}
?>