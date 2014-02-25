<?php
// wcf imports
require_once(WCF_DIR.'lib/page/util/menu/TreeMenu.class.php');

/**
 * Builds the acp menu.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page.util.menu
 * @category 	Community Framework
 */
class ACPMenu extends TreeMenu {
	/**
	 * @see TreeMenu::loadCache()
	 */
	protected function loadCache() {
		parent::loadCache();
		
		if (PACKAGE_ID == 0) {
			return;
		}
		
		WCF::getCache()->addResource('menu-'.PACKAGE_ID, WCF_DIR.'cache/cache.menu-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderACPMenu.class.php');
		$this->menuItems = WCF::getCache()->get('menu-'.PACKAGE_ID);
	}
	
	/**
	 * @see TreeMenu::parseMenuItemLink()
	 */
	protected function parseMenuItemLink($link, $path) {
		if (preg_match('~\.php$~', $link)) {
			$link .= '?'; 
		}
		else {
			$link .= '&';
		}
		
		return $link.'packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED;
	}
}
?>