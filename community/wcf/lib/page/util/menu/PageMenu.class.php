<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Builds the page menu.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.page.headerMenu
 * @subpackage	page.util.menu
 * @category 	Community Framework
 */
class PageMenu {
	protected static $activeMenuItem = '';
	public $menuItems = null;
	
	/**
	 * Loads cached menu items.
	 */
	protected function loadCache() {
		// call loadCache event
		EventHandler::fireAction($this, 'loadCache');
		
		WCF::getCache()->addResource('pageMenu-'.PACKAGE_ID, WCF_DIR.'cache/cache.pageMenu-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderPageMenu.class.php');
		$this->menuItems = WCF::getCache()->get('pageMenu-'.PACKAGE_ID);
	}
	
	/**
	 * Builds the menu.
	 */
	protected function buildMenu() {
		// get menu items from cache
		$this->loadCache();
		
		// check item permissions
		$this->checkPermissions();
		
		// check item options
		$this->checkOptions();
		
		// parse menu items
		$this->parseMenuItems();
		
		// call buildMenu event
		EventHandler::fireAction($this, 'buildMenu');
	}
	
	/**
	 * Parses the menu items.
	 */
	protected function parseMenuItems() {
		foreach ($this->menuItems as $key => $item) {
			// get relative path
			$path = $applicationPath = '';
			if (empty($item['packageDir'])) {
				$path = RELATIVE_WCF_DIR;
			}
			else if ($item['packageID'] != PACKAGE_ID) {						
				$path = $applicationPath = FileUtil::getRealPath(RELATIVE_WCF_DIR.$item['packageDir']);
			}
			
			// add path and session id to link
			if (!empty($applicationPath) && !preg_match('~^(?:https?://|/)~', $item['menuItemLink'])) {
				$item['menuItemLink'] = $applicationPath.$item['menuItemLink'];
			}
			
			// append session id
			if (!preg_match('~^https?://~', $item['menuItemLink'])) {
				if (strpos($item['menuItemLink'], '?') !== false) {
					$item['menuItemLink'] .= SID_ARG_2ND_NOT_ENCODED;
				}
				else {
					$item['menuItemLink'] .= SID_ARG_1ST; 
				}
			}

			// add path to image link
			if (!empty($item['menuItemIconS'])) {
				$item['menuItemIconS'] = StyleManager::getStyle()->getIconPath($item['menuItemIconS']);
			}
			if (!empty($item['menuItemIconM'])) {
				$item['menuItemIconM'] = StyleManager::getStyle()->getIconPath($item['menuItemIconM']);
			}
			
			// check active menu item
			$item['activeMenuItem'] = ($item['menuItem'] == self::$activeMenuItem);
			
			$this->menuItems[$key] = $item;
		}
	}
	
	/**
	 * Checks the permissions of the menu items.
	 * Removes items without permission.
	 */
	protected function checkPermissions() {
		foreach ($this->menuItems as $key => $item) {
			$hasPermission = true;
			// check the permission of this item for the active user
			if (!empty($item['permissions'])) {
				$hasPermission = false;
				$permissions = explode(',', $item['permissions']);
				foreach ($permissions as $permission) {
					if (WCF::getUser()->getPermission($permission)) {
						$hasPermission = true;
						break;
					}
				}
			}
			
			if (!$hasPermission) {
				// remove this item
				unset($this->menuItems[$key]);
			}
		}
	}
	
	/**
	 * Checks the options of the menu items.
	 * Removes items of disabled options.
	 */
	protected function checkOptions() {
		foreach ($this->menuItems as $key => $item) {
			$hasEnabledOption = true;
			// check the options of this item
			if (!empty($item['options'])) {
				$hasEnabledOption = false;
				$options = explode(',', strtoupper($item['options']));
				foreach ($options as $option) {
					if (defined($option) && constant($option)) {
						$hasEnabledOption = true;
						break;
					}
				}
			}
			
			if (!$hasEnabledOption) {
				// remove this item
				unset($this->menuItems[$key]);
			}
		}
	}
	
	/**
	 * Sets the active menu item. 
	 * This should be done before the headerMenu.tpl template calls the function getMenu().
	 * 
	 * This function should be used in each script which uses a template that includes the headerMenu.tpl.
	 * 
	 * @param	string		$menuItem	name of the active menu item
	 */
	public static function setActiveMenuItem($menuItem) {
		self::$activeMenuItem = $menuItem;
	}
	
	/**
	 * Returns the name of the active menu item.
	 * 
	 * @return	string
	 */
	public static function getActiveMenuItem() {
		return self::$activeMenuItem;
	}
	
	/**
	 * Returns the list of menu items.
	 * 
	 * @return	array
	 */
	public function getMenuItems($position = 'header') {
		if ($this->menuItems === null) {
			$this->buildMenu();
		}
		
		if (!empty($position)) {
			$items = array();
			foreach ($this->menuItems as $item) {
				if ($item['menuPosition'] == $position) {
					$items[] = $item;
				}
			}
			
			return $items;
		}
		
		return $this->menuItems;
	}
}
?>