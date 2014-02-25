<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/page/menu/PageMenuItemEditor.class.php');

/**
 * Sorts the structure of page menu items.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.pageMenu
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class PageMenuItemSortAction extends AbstractAction {
	public $headerPositions = array();
	public $footerPositions = array();
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get positions
		if (isset($_POST['headerMenuListPositions']) && is_array($_POST['headerMenuListPositions'])) $this->headerPositions = ArrayUtil::toIntegerArray($_POST['headerMenuListPositions']);
		if (isset($_POST['footerMenuListPositions']) && is_array($_POST['footerMenuListPositions'])) $this->footerPositions = ArrayUtil::toIntegerArray($_POST['footerMenuListPositions']);
	}
		
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permissions
		WCF::getUser()->checkPermission('admin.pageMenu.canEditPageMenuItem');
				
		// update positions
		foreach ($this->headerPositions as $menuItemID => $showOrder) {
			PageMenuItemEditor::updateShowOrder(intval($menuItemID), 'header', $showOrder);
		}
		foreach ($this->footerPositions as $menuItemID => $showOrder) {
			PageMenuItemEditor::updateShowOrder(intval($menuItemID), 'footer', $showOrder);
		}
		
		// delete cache
		PageMenuItemEditor::clearCache();
		$this->executed();
		
		// forward to list page
		header('Location: index.php?page=PageMenuItemList&successfullSorting=1&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>