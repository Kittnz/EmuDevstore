<?php
// wcf imports
require_once(WCF_DIR.'lib/page/util/menu/TreeMenu.class.php');

/**
 * Builds the user cp menu.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	page.util.menu
 * @category 	Community Framework
 */
class UserCPMenu extends TreeMenu {
	protected static $instance = null;
	
	/**
	 * Returns an instance of the UserCPMenu class.
	 * 
	 * @return	UserCPMenu
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new UserCPMenu();
		}
		
		return self::$instance;
	}
	
	/**
	 * @see TreeMenu::loadCache()
	 */
	protected function loadCache() {
		parent::loadCache();
		
		WCF::getCache()->addResource('userCPMenu-'.PACKAGE_ID, WCF_DIR.'cache/cache.userCPMenu-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderUserCPMenu.class.php');
		$this->menuItems = WCF::getCache()->get('userCPMenu-'.PACKAGE_ID);
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