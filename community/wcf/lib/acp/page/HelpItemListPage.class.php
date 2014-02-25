<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/data/help/HelpItem.class.php');

/**
 * Shows a list of all help items.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.help
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class HelpItemListPage extends AbstractPage {
	// system
	public $templateName = 'helpItemList';
	public $deletedHelpItemID = 0;

	/**
	 * If the list was sorted successfully
	 * 
	 * @var boolean
	 */
	public $successfullSorting = false;
	
	/**
	 * help item list
	 * 
	 * @var array<array>
	 */
	public $helpItemList = array();
	
	/**
	 * structured help item list
	 * 
	 * @var array<array>
	 */
	public $helpItems = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['successfullSorting'])) $this->successfullSorting = true;
		if (isset($_REQUEST['deletedHelpItemID'])) $this->deletedHelpItemID = intval($_REQUEST['deletedHelpItemID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readHelpItems();
		$this->makeHelpItemList();
	}
	
	/**
	 * Gets a list of all help items.
	 */
	protected function readHelpItems() {
		$sql = "SELECT		help_item.*,
					IFNULL((SELECT helpItemID FROM wcf".WCF_N."_help_item WHERE helpItem = help_item.parentHelpItem LIMIT 1), 0) AS parentHelpItemID 
			FROM		wcf".WCF_N."_help_item help_item
			WHERE		packageID IN (
						SELECT	dependency
						FROM	wcf".WCF_N."_package_dependency
						WHERE	packageID = ".PACKAGE_ID."
					)
			ORDER BY	parentHelpItemID, showOrder";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->helpItems[$row['parentHelpItemID']][] = new HelpItem(null, $row);
		}
	}

	/**
	 * Renders one level of the help item structure.
	 *
	 * @param	integer		$parentHelpItemID
	 * @param	integer		$depth
	 * @param	integer		$openParents
	 */
	protected function makeHelpItemList($parentHelpItemID = 0, $depth = 1, $openParents = 0) {
		if (!isset($this->helpItems[$parentHelpItemID])) return;
		
		$i = 0; $children = count($this->helpItems[$parentHelpItemID]);
		foreach ($this->helpItems[$parentHelpItemID] as $helpItem) {
			$childrenOpenParents = $openParents + 1;
			$hasChildren = isset($this->helpItems[$helpItem->helpItemID]);
			$last = $i == count($this->helpItems[$parentHelpItemID]) - 1;
			if ($hasChildren && !$last) $childrenOpenParents = 1;
			$this->helpItemList[] = array('depth' => $depth, 'hasChildren' => $hasChildren, 'openParents' => ((!$hasChildren && $last) ? ($openParents) : (0)), 'helpItem' => $helpItem, 'position' => $i + 1, 'maxPosition' => $children);
			
			// make next level of the list
			$this->makeHelpItemList($helpItem->helpItemID, $depth + 1, $childrenOpenParents);
			$i++;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'helpItems' => $this->helpItemList,
			'deletedHelpItemID' => $this->deletedHelpItemID,
			'successfullSorting' => $this->successfullSorting
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.helpItem.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.help.canEditHelpItem', 'admin.help.canDeleteHelpItem'));
		
		parent::show();
	}
}
?>