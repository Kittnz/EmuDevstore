<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/category/AvatarCategoryList.class.php');

/**
 * Shows a list of avatar categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.avatar
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class AvatarCategoryListPage extends SortablePage {
	// system
	public $templateName = 'avatarCategoryList';
	public $defaultSortField = 'showOrder';
	public $deletedAvatarCategoryID = 0;
	
	/**
	 * avatar category list object
	 * 
	 * @var	AvatarCategoryList
	 */
	public $avatarCategoryList = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->avatarCategoryList = new AvatarCategoryList();
		if (isset($_REQUEST['deletedAvatarCategoryID'])) $this->deletedAvatarCategoryID = intval($_REQUEST['deletedAvatarCategoryID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read objects
		$this->avatarCategoryList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->avatarCategoryList->sqlLimit = $this->itemsPerPage;
		$this->avatarCategoryList->sqlOrderBy = ($this->sortField != 'avatars' ? 'avatar_category.' : '').$this->sortField." ".$this->sortOrder;
		$this->avatarCategoryList->readObjects();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'avatarCategoryID':
			case 'title':
			case 'avatars':
			case 'showOrder': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->avatarCategoryList->countObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'avatarCategories' => $this->avatarCategoryList->getObjects(),
			'deletedAvatarCategoryID' => $this->deletedAvatarCategoryID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.avatar.category.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.avatar.canEditAvatarCategory', 'admin.avatar.canDeleteAvatarCategory'));
		
		parent::show();
	}
}
?>