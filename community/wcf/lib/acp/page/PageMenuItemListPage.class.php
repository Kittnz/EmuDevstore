<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/data/page/menu/PageMenuItemEditor.class.php');

/**
 * Shows a list of all page menu items.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.pageMenu
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class PageMenuItemListPage extends AbstractPage {
	// system
	public $templateName = 'pageMenuItemList';
	public $deletedPageMenuItemID = 0;
	
	/**
	 * If the list was sorted successfully
	 * @var boolean
	 */
	public $successfullSorting = false;
	
	/**
	 * list of header menu items
	 * 
	 * @var array<PageMenuItem>
	 */
	public $headerMenuItemList = array();
	
	/**
	 * list of footer menu items
	 * 
	 * @var array<PageMenuItem>
	 */
	public $footerMenuItemList = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['successfullSorting'])) $this->successfullSorting = true;
		if (isset($_REQUEST['deletedPageMenuItemID'])) $this->deletedPageMenuItemID = intval($_REQUEST['deletedPageMenuItemID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readPageMenuItems();
	}
	
	/**
	 * Gets page menu items.
	 */
	protected function readPageMenuItems() {
		$headerPosition = $footerPosition = 1;
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_page_menu_item
			WHERE		packageID IN (
						SELECT	dependency
						FROM	wcf".WCF_N."_package_dependency
						WHERE	packageID = ".PACKAGE_ID."
					)
			ORDER BY	showOrder";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$hasEnabledOption = true;
			// check the options of this item
			if (!empty($row['options'])) {
				$hasEnabledOption = false;
				$options = explode(',', strtoupper($row['options']));
				foreach ($options as $option) {
					if (defined($option) && constant($option)) {
						$hasEnabledOption = true;
						break;
					}
				}
			}
			if (!$hasEnabledOption) {
				continue;
			}
			
			if ($row['menuPosition'] == 'header') {
				$row['showOrder'] = $headerPosition;
				$this->headerMenuItemList[$row['menuItemID']] = new PageMenuItem(null, $row);
				$headerPosition++;
			}
			else  {
				$row['showOrder'] = $footerPosition;
				$this->footerMenuItemList[$row['menuItemID']] = new PageMenuItem(null, $row);
				$footerPosition++;
			}
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'headerMenuItemList' => $this->headerMenuItemList,
			'footerMenuItemList' => $this->footerMenuItemList,
			'deletedPageMenuItemID' => $this->deletedPageMenuItemID,
			'successfullSorting' => $this->successfullSorting
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.pageMenuItem.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.pageMenu.canEditPageMenuItem', 'admin.pageMenu.canDeletePageMenuItem'));
		
		parent::show();
	}
}
?>