<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/page/menu/PageMenuItemEditor.class.php');

/**
 * Deletes a page menu item.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.pageMenu
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class PageMenuItemDeleteAction extends AbstractAction {
	/**
	 * page menu item id
	 *
	 * @var integer
	 */
	public $pageMenuItemID = 0;
	
	/**
	 * page menu item object.
	 *
	 * @var PageMenuItemEditor
	 */
	public $pageMenuItem = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['pageMenuItemID'])) $this->pageMenuItemID = intval($_REQUEST['pageMenuItemID']);
		$this->pageMenuItem = new PageMenuItemEditor($this->pageMenuItemID);
		if (!$this->pageMenuItem->menuItemID) {
			throw new IllegalLinkException();
		}
	}
		
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// check permissions
		WCF::getUser()->checkPermission('admin.pageMenu.canDeletePageMenuItem');
		
		// delete item
		$this->pageMenuItem->delete();
		
		// delete cache
		PageMenuItemEditor::clearCache();
		$this->executed();
		
		// forward to list page
		header('Location: index.php?page=PageMenuItemList&deletedPageMenuItemID='.$this->pageMenuItemID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);	
		exit;
	}
}
?>