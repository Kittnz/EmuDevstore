<?php
/**
 * The core class of applications that uses the user profile menu should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	page.util.menu
 * @category 	Community Framework
 */
interface UserProfileMenuContainer {
	/**
	 * Returns the active object of the user profile menu.
	 * 
	 * @return	UserProfileMenu
	 */
	public static function getUserProfileMenu();
}
?>