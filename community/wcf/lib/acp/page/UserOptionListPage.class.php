<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');

/**
 * Shows a list of installed user options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.option
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class UserOptionListPage extends SortablePage {
	public $templateName = 'userOptionList';
	public $deletedOptionID = 0;
	public $defaultSortField = 'categoryName';
	protected $optionIDs = '';
	protected $options = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['deletedOptionID'])) $this->deletedOptionID = intval($_REQUEST['deletedOptionID']);
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'optionID':
			case 'optionName':
			case 'categoryName':
			case 'optionType':
			case 'showOrder': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		$this->getOptionIDs();
		if (empty($this->optionIDs)) return 0;
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_option
			WHERE	optionID IN (".$this->optionIDs.")";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readOptions();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'options' => $this->options,
			'deletedOptionID' => $this->deletedOptionID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.user.option.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.user.option.canEditOption', 'admin.user.option.canDeleteOption'));
		
		parent::show();
	}
	
	/**
	 * Gets user options ids.
	 */
	protected function getOptionIDs() {
		$sql = "SELECT		optionName, optionID 
			FROM		wcf".WCF_N."_user_option option_table,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		option_table.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".PACKAGE_ID."
					AND option_table.categoryName IN (
						SELECT	categoryName
						FROM	wcf".WCF_N."_user_option_category
						WHERE	parentCategoryName = 'profile'
					)
					AND option_table.editable < 4
					AND option_table.visible < 4
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$options = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$options[$row['optionName']] = $row['optionID'];
		}
		
		$this->optionIDs = implode(',', $options);
	}
	
	/**
	 * Gets a list of user options.
	 */
	protected function readOptions() {
		if (!$this->items) return array();
		
		$this->options = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_user_option
			WHERE		optionID IN (".$this->optionIDs.")
			ORDER BY	".$this->sortField." ".$this->sortOrder;
		$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->options[] = $row;
		}
	}
}
?>