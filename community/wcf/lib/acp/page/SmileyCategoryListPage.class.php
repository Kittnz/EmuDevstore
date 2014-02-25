<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/category/SmileyCategoryList.class.php');

/**
 * Shows a list of smiley categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class SmileyCategoryListPage extends SortablePage {
	// system
	public $templateName = 'smileyCategoryList';
	public $defaultSortField = 'showOrder';
	public $deletedSmileyCategoryID = 0;
	
	/**
	 * smiley category list object
	 * 
	 * @var	SmileyCategoryList
	 */
	public $smileyCategoryList = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->smileyCategoryList = new SmileyCategoryList();
		if (isset($_REQUEST['deletedSmileyCategoryID'])) $this->deletedSmileyCategoryID = intval($_REQUEST['deletedSmileyCategoryID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read objects
		$this->smileyCategoryList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->smileyCategoryList->sqlLimit = $this->itemsPerPage;
		$this->smileyCategoryList->sqlOrderBy = ($this->sortField != 'smileys' ? 'smiley_category.' : '').$this->sortField." ".$this->sortOrder;
		$this->smileyCategoryList->readObjects();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'smileyCategoryID':
			case 'title':
			case 'smileys':
			case 'showOrder': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->smileyCategoryList->countObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'smileyCategories' => $this->smileyCategoryList->getObjects(),
			'deletedSmileyCategoryID' => $this->deletedSmileyCategoryID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.smiley.category.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.smiley.canEditSmileyCategory', 'admin.smiley.canDeleteSmileyCategory'));
		
		parent::show();
	}
}
?>