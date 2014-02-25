<?php
// wcf imports
require_once(WCF_DIR.'lib/data/page/menu/PageMenuItem.class.php');
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');

/**
 * Provides functions to edit page menu items.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.page.headerMenu
 * @subpackage	data.page.menu
 * @category 	Community Framework
 */
class PageMenuItemEditor extends PageMenuItem {
	/**
	 * Creates a new page menu item.
	 * 
	 * @param 	string		$name
	 * @param 	string		$link
	 * @param 	string		$iconS
	 * @param 	string		$iconM
	 * @param 	integer		$showOrder
	 * @param	string		$position
	 * @param	integer		$languageID
	 * @param	integer		$packageID
	 * @return	PageMenuItemEditor
	 */	
	public static function create($name, $link, $iconS = '', $iconM = '', $showOrder = 0, $position = 'header', $languageID = 0, $packageID = PACKAGE_ID) {
		// get show order
		if ($showOrder == 0) {
			// get next number in row
			$sql = "SELECT	MAX(showOrder) AS showOrder
				FROM	wcf".WCF_N."_page_menu_item
				WHERE	menuPosition = '".escapeString($position)."'";
			$row = WCF::getDB()->getFirstRow($sql);
			if (!empty($row)) $showOrder = intval($row['showOrder']) + 1;
			else $showOrder = 1;
		}
		else {
			$sql = "UPDATE	wcf".WCF_N."_page_menu_item
				SET 	showOrder = showOrder + 1
				WHERE 	showOrder >= ".$showOrder."
					AND menuPosition = '".escapeString($position)."'";
			WCF::getDB()->sendQuery($sql);
		}
		
		// get menu item name
		$menuItem = '';
		if ($languageID == 0) $menuItem = $name;
		
		// save
		$sql = "INSERT INTO	wcf".WCF_N."_page_menu_item
					(packageID, menuItem, menuItemLink, menuItemIconS, menuItemIconM, menuPosition, showOrder)
			VALUES		(".$packageID.", '".escapeString($menuItem)."', '".escapeString($link)."', '".escapeString($iconS)."', '".escapeString($iconM)."', '".escapeString($position)."', ".$showOrder.")";
		WCF::getDB()->sendQuery($sql);
		
		// get item id
		$menuItemID = WCF::getDB()->getInsertID("wcf".WCF_N."_page_menu_item", 'menuItemID');
		
		if ($languageID != 0) {
			// set name
			$menuItem = "wcf.header.menu.pageMenuItem".$menuItemID;
			$sql = "UPDATE	wcf".WCF_N."_page_menu_item
				SET	menuItem = '".escapeString($menuItem)."'
				WHERE 	menuItemID = ".$menuItemID;
			WCF::getDB()->sendQuery($sql);
			
			// save language variables
			$language = new LanguageEditor($languageID);
			$language->updateItems(array($menuItem => $name));
		}
		
		return new PageMenuItemEditor($menuItemID);
	}
	
	/**
	 * Updates this page menu item.
	 * 
	 * @param 	string		$name
	 * @param 	string		$link
	 * @param 	string		$iconS
	 * @param 	string		$iconM
	 * @param 	integer		$showOrder
	 * @param	string		$position
	 * @param	integer		$languageID
	 */	
	public function update($name, $link, $iconS = '', $iconM = '', $showOrder = 0, $position = 'header', $languageID = 0) {
		if ($position == $this->menuPosition) {
			if ($this->showOrder != $showOrder) {
				if ($showOrder < $this->showOrder) {
					$sql = "UPDATE	wcf".WCF_N."_page_menu_item
						SET 	showOrder = showOrder + 1
						WHERE 	showOrder >= ".$showOrder."
							AND showOrder < ".$this->showOrder."
							AND menuPosition = '".escapeString($position)."'";
					WCF::getDB()->sendQuery($sql);
				}
				else if ($showOrder > $this->showOrder) {
					$sql = "UPDATE	wcf".WCF_N."_page_menu_item
						SET	showOrder = showOrder - 1
						WHERE	showOrder <= ".$showOrder."
							AND showOrder > ".$this->showOrder."
							AND menuPosition = '".escapeString($position)."'";
					WCF::getDB()->sendQuery($sql);
				}
			}
		}
		else {
			$sql = "UPDATE	wcf".WCF_N."_page_menu_item
				SET 	showOrder = showOrder - 1
				WHERE 	showOrder >= ".$this->showOrder."
					AND menuPosition = '".escapeString($this->menuPosition)."'";
			WCF::getDB()->sendQuery($sql);
				
			$sql = "UPDATE 	wcf".WCF_N."_page_menu_item
				SET 	showOrder = showOrder + 1
				WHERE 	showOrder >= ".$showOrder."
					AND menuPosition = '".escapeString($position)."'";
			WCF::getDB()->sendQuery($sql);
		}
		
		// Update
		$sql = "UPDATE	wcf".WCF_N."_page_menu_item
			SET	".($languageID == 0 ? "menuItem = '".escapeString($name)."'," : '')."
				menuItemlink	= '".escapeString($link)."',
				menuItemIconS 	= '".escapeString($iconS)."',
				menuItemIconM 	= '".escapeString($iconM)."',
				menuPosition	= '".escapeString($position)."',
				showOrder 	= ".$showOrder."
			WHERE 	menuItemID 	= ".$this->menuItemID;
		WCF::getDB()->sendQuery($sql);
		
		if ($languageID != 0) {
			// save language variables
			$language = new LanguageEditor($languageID);
			$language->updateItems(array($this->menuItem => $name), 0, PACKAGE_ID, array($this->menuItem => 1));
		}
	}
	
	/**
	 * Deletes this page menu item.
	 */
	public function delete() {
		// update show order
		$sql = "UPDATE	wcf".WCF_N."_page_menu_item
			SET	showOrder = showOrder - 1
			WHERE	showOrder >= ".$this->showOrder."
				AND menuPosition = '".escapeString($this->menuPosition)."'";
		WCF::getDB()->sendQuery($sql);
		
		// delte
		$sql = "DELETE FROM	wcf".WCF_N."_page_menu_item
			WHERE		menuItemID = ".$this->menuItemID;
		WCF::getDB()->sendQuery($sql);
			
		// delete language variables
		LanguageEditor::deleteVariable($this->menuItem);
	}
	
	/**
	 * Clears the page menu cache.
	 */
	public static function clearCache() {
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.pageMenu-*.php');
	}
	
	/**
	 * Updates the positions of a page menu item directly.
	 *
	 * @param	integer		$menuItemID
	 * @param	string		$position
	 * @param	integer		$showOrder
	 */
	public static function updateShowOrder($menuItemID, $position = 'header', $showOrder = 1) {
		// Update
		$sql = "UPDATE	wcf".WCF_N."_page_menu_item
			SET	showOrder = ".$showOrder.",
				menuPosition = '".escapeString($position)."'
			WHERE 	menuItemID = ".$menuItemID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Enables / disables this item.
	 * 
	 * @param	boolean		$enable
	 */
	public function enable($enable = 1) {
		$sql = "UPDATE	wcf".WCF_N."_page_menu_item
			SET	isDisabled = ".intval(!$enable)."
			WHERE	menuItemID = ".$this->menuItemID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>