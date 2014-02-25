<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/page/menu/PageMenuItemEditor.class.php');

/**
 * Renames a page menu item.
 * 
 * @author	Arian Glander
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.pageMenu
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class PageMenuItemRenameAction extends AbstractAction {
	public $menuItemID = 0;
	public $title = '';
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['menuItemID'])) $this->menuItemID = intval($_REQUEST['menuItemID']);
		if (isset($_POST['title'])) {
			$this->title = $_POST['title'];
			if (CHARSET != 'UTF-8') $this->title = StringUtil::convertEncoding('UTF-8', CHARSET, $this->title);
		}
	}
	
	/**
	 * @see Action::execute();
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.pageMenu.canEditPageMenuItem');
				
		// get menu item
		$menuItem = new PageMenuItemEditor($this->menuItemID);
		if (!$menuItem->menuItemID) {
			throw new IllegalLinkException();
		}
		
		// check menu item title
		if (StringUtil::encodeHTML($menuItem->menuItem) != WCF::getLanguage()->get(StringUtil::encodeHTML($menuItem->menuItem))) {
			// change language variable
			require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
			$language = new LanguageEditor(WCF::getLanguage()->getLanguageID());
			$language->updateItems(array(($menuItem->menuItem) => $this->title), 0, PACKAGE_ID, array($menuItem->menuItem => 1));
		}
		else {
			$menuItem->update($this->title, $menuItem->link, $menuItem->iconS, $menuItem->iconM, $menuItem->showOrder, $menuItem->position, $menuItem->languageID);
		}
		
		// reset cache
		WCF::getCache()->clearResource('menu-' . PACKAGE_ID);
		$this->executed();
	}
}
?>