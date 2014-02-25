<?php
/**
 * The core class of applications that uses the usercp menu should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	page.util.menu
 * @category 	Community Framework
 */
interface UserCPMenuContainer {
	/**
	 * Returns the active object of the usercp menu.
	 * 
	 * @return	UserCPMenu
	 */
	public static function getUserCPMenu();
}
?>