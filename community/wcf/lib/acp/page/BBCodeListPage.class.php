<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');

/**
 * Shows a list of installed bbcodes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.bbcode
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class BBCodeListPage extends SortablePage {
	// system
	public $templateName = 'bbcodeList';
	public $deletedBBCodeID = 0;
	public $defaultSortField = 'bbcodeTag';
	
	/**
	 * list of bbcodes
	 * 
	 * @var	array
	 */
	public $bbcodes = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['deletedBBCodeID'])) $this->deletedBBCodeID = intval($_REQUEST['deletedBBCodeID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readBBCodes();
	}
	
	/**
	 * Gets a list of bbcodes.
	 */
	protected function readBBCodes() {
		if ($this->items) {
			$sql = "SELECT		bbcode.*, COUNT(attribute.attributeNo) AS attributeCount
				FROM		wcf".WCF_N."_bbcode bbcode
				LEFT JOIN	wcf".WCF_N."_bbcode_attribute attribute
				ON		(attribute.bbcodeID = bbcode.bbcodeID)
				GROUP BY	bbcode.bbcodeID
				ORDER BY	".($this->sortField != 'attributeCount' ? 'bbcode.' : '').$this->sortField." ".$this->sortOrder;
			$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->bbcodes[] = $row;
			}
		}
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'bbcodeID':
			case 'bbcodeTag':
			case 'htmlOpen':
			case 'htmlClose':
			case 'textOpen':
			case 'textClose':
			case 'allowedChildren':
			case 'className':
			case 'attributeCount': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_bbcode";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'bbcodes' => $this->bbcodes,
			'deletedBBCodeID' => $this->deletedBBCodeID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.bbcode.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.bbcode.canEditBBCode', 'admin.bbcode.canDeleteBBCode'));
		
		parent::show();
	}
}
?>