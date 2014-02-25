<?php
/**
 * The core class of applications that uses the page menu should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.page.headerMenu
 * @subpackage	page.util.menu
 * @category 	Community Framework
 */
interface PageMenuContainer {
	/**
	 * Returns the active object of the page menu.
	 * 
	 * @return	PageMenu
	 */
	public static function getPageMenu();
}
?>