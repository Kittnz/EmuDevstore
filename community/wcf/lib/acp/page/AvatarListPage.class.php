<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/AvatarList.class.php');

/**
 * Shows a list of avatars.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.avatar
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class AvatarListPage extends SortablePage {
	// system
	public $templateName = 'avatarList';
	public $defaultSortField = 'avatarName';
	public $deletedAvatarID = 0;
	
	/**
	 * avatar type
	 * 0 = default; 1 = user avatars
	 * 
	 * @var	integer
	 */
	public $type = 0; 
	
	/**
	 * avatar list object
	 * 
	 * @var	AvatarList
	 */
	public $avatarList = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->avatarList = new AvatarList();
		if (isset($_REQUEST['type'])) $this->type = intval($_REQUEST['type']);
		if (isset($_REQUEST['deletedAvatarID'])) $this->deletedAvatarID = intval($_REQUEST['deletedAvatarID']);
		$this->avatarList->sqlConditions .= "avatar.userID ".($this->type == 0 ? '= 0' : '<> 0');
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'avatarID':
			case 'avatarName':
			case 'avatarExtension':
			case 'width':
			case 'height':
			case 'groupName':
			case 'neededPoints':
			case 'username':
			case 'avatarCategoryTitle': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->avatarList->countObjects();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read objects
		$this->avatarList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->avatarList->sqlLimit = $this->itemsPerPage;
		$this->avatarList->sqlOrderBy = (($this->sortField != 'avatarCategoryTitle' && $this->sortField != 'username' && $this->sortField != 'groupName') ? 'avatar.' : '').$this->sortField." ".$this->sortOrder;
		$this->avatarList->readObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'type' => $this->type,
			'avatars' => $this->avatarList->getObjects(),
			'deletedAvatarID' => $this->deletedAvatarID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.avatar.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.avatar.canEditAvatar', 'admin.avatar.canDeleteAvatar', 'admin.avatar.canDisableAvatar'));
		
		parent::show();
	}
}
?>