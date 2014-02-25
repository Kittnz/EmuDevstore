<?php
// wcf imports
require_once(WCF_DIR.'lib/page/util/menu/TreeMenu.class.php');

/**
 * Builds the user profile menu.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	page.util.menu
 * @category 	Community Framework
 */
class UserProfileMenu extends TreeMenu {
	protected static $instance = null;
	public $userID = 0;
	
	/**
	 * Returns an instance of the UserProfileMenu class.
	 * 
	 * @return	UserProfileMenu
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new UserProfileMenu();
		}
		
		return self::$instance;
	}
	
	/**
	 * @see TreeMenu::loadCache()
	 */
	protected function loadCache() {
		parent::loadCache();
		
		WCF::getCache()->addResource('userProfileMenu-'.PACKAGE_ID, WCF_DIR.'cache/cache.userProfileMenu-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderUserProfileMenu.class.php');
		$this->menuItems = WCF::getCache()->get('userProfileMenu-'.PACKAGE_ID);
		
		EventHandler::fireAction($this, 'loadedCache');
	}
	
	/**
	 * @see TreeMenu::parseMenuItemLink()
	 */
	protected function parseMenuItemLink($link, $path) {
		if (preg_match('~\.php$~', $link)) {
			$link .= SID_ARG_1ST; 
		}
		else {
			$link .= SID_ARG_2ND_NOT_ENCODED;
		}
		
		// insert user id
		$link = str_replace('%s', $this->userID, $link);
		
		return $link;
	}
	
	/**
	 * @see TreeMenu::parseMenuItemIcon()
	 */
	protected function parseMenuItemIcon($icon, $path) {
		return StyleManager::getStyle()->getIconPath($icon);
	}
}
?>