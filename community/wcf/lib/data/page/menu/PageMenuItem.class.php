<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a page menu item.
 *
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.page.headerMenu
 * @subpackage	data.page.menu
 * @category 	Community Framework
 */
class PageMenuItem extends DatabaseObject {
	/**
	 * Creates a new PageMenuItem object.
	 * 
	 * @param 	integer		$pageMenuItemID
	 * @param 	array		$row
	 */
	public function __construct($pageMenuItemID, $row = null) {
		if ($pageMenuItemID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_page_menu_item
				WHERE	menuItemID = ".$pageMenuItemID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
}
?>