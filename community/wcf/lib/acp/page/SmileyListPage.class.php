<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/SmileyList.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/category/SmileyCategory.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/SmileyEditor.class.php');

/**
 * Shows a list of installed smilies.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class SmileyListPage extends SortablePage {
	// system
	public $templateName = 'smileyList';
	public $defaultSortField = 'showOrder';
	public $deletedSmileyID = 0;
	
	/**
	 * smiley list object
	 * 
	 * @var	SmileyList
	 */
	public $smileyList = null;
	
	/**
	 * smiley category id
	 * 
	 * @var	integer
	 */
	public $smileyCategoryID = 0;
	
	/**
	 * list of smiley categories.
	 * 
	 * @var	array<SmileyCategory>
	 */
	public $smileyCategories = array();
	
	/**
	 * number of default smileys
	 * 
	 * @var	integer
	 */
	public $defaultSmileys = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['deletedSmileyID'])) $this->deletedSmileyID = intval($_REQUEST['deletedSmileyID']);
		if (isset($_REQUEST['smileyCategoryID'])) $this->smileyCategoryID = intval($_REQUEST['smileyCategoryID']);
		$this->smileyList = new SmileyList();
		$this->smileyList->sqlConditions = 'smiley.smileyCategoryID = '.$this->smileyCategoryID;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get smileys
		$this->smileyList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->smileyList->sqlLimit = $this->itemsPerPage;
		$this->smileyList->sqlOrderBy = 'smiley.'.$this->sortField." ".$this->sortOrder;
		$this->smileyList->readObjects();
		
		// get categories
		$this->smileyCategories = SmileyCategory::getSmileyCategories();
		
		// get default smileys
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_smiley
			WHERE	smileyCategoryID = 0";
		$row = WCF::getDB()->getFirstRow($sql);
		$this->defaultSmileys = $row['count'];
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'smileyID':
			case 'smileyPath':
			case 'smileyTitle':
			case 'smileyCode':
			case 'showOrder': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->smileyList->countObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'smilies' => $this->smileyList->getObjects(),
			'deletedSmileyID' => $this->deletedSmileyID,
			'smileyCategoryID' => $this->smileyCategoryID,
			'smileyCategories' => $this->smileyCategories,
			'defaultSmileys' => $this->defaultSmileys,
			'markedSmileys' => SmileyEditor::getMarkedSmileys()
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.smiley.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.smiley.canEditSmiley', 'admin.smiley.canDeleteSmiley'));
		
		parent::show();
	}
}
?>