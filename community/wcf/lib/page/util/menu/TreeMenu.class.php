<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Basis class for a tree menu.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page.util.menu
 * @category 	Community Framework
 */
abstract class TreeMenu {
	/**
	 * List of visible menu items.
	 * 
	 * @var array
	 */
	public $menuItemList = array();
	
	/**
	 * List of active menu items.
	 * 
	 * @var array
	 */
	public $activeMenuItems = array();
	
	/**
	 * List of all menu items.
	 * 
	 * @var array
	 */
	public $menuItems = null;
	
	/**
	 * Loads cached menu items.
	 */
	protected function loadCache() {
		// call loadCache event
		EventHandler::fireAction($this, 'loadCache');
		
		$this->menuItems = array();
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
		
		// remove items without children
		$this->removeEmptyItems();
		
		// parse menu items
		$this->parseMenuItems();
		
		// call buildMenu event
		EventHandler::fireAction($this, 'buildMenu');
	}
	
	/**
	 * Parses a menu link.
	 * 
	 * @param	string		$link
	 * @param	string		$path
	 * @return	string
	 */
	protected abstract function parseMenuItemLink($link, $path);

	/**
	 * Parses the menu items.
	 */
	protected function parseMenuItems() {
		foreach ($this->menuItems as $parentMenuItem => $items) {
			foreach ($items as $key => $item) {
				if (!empty($item['menuItemLink']) || !empty($item['menuItemIcon'])) {
					// get relative path
					$path = '';
					if (empty($item['packageDir'])) {
						$path = RELATIVE_WCF_DIR;
					}
					else {						
						$path = FileUtil::getRealPath(RELATIVE_WCF_DIR.$item['packageDir']);
					}
					
					// add package id and session id to link
					if (!empty($item['menuItemLink'])) {
						$item['menuItemLink'] = $this->parseMenuItemLink($item['menuItemLink'], $path);
					}
					
					if (!empty($item['menuItemIcon'])) {
						$item['menuItemIcon'] =$this->parseMenuItemIcon($item['menuItemIcon'], $path); 
					}
					
					$this->menuItems[$parentMenuItem][$key] = $item;
				}
				
				$this->menuItemList[$item['menuItem']] =& $this->menuItems[$parentMenuItem][$key];
			}
		}
	}
	
	/**
	 * Parses an icon link.
	 * 
	 * @param	string		$icon
	 * @param	string		$path
	 * @return	string
	 */
	protected function parseMenuItemIcon($icon, $path) {
		return $path.$icon;
	}
	
	/**
	 * Removes items without children.
	 * 
	 * @param	string		$parentMenuItem
	 */
	protected function removeEmptyItems($parentMenuItem = '') {
		if (!isset($this->menuItems[$parentMenuItem])) return;
		
		foreach ($this->menuItems[$parentMenuItem] as $key => $item) {
			$this->removeEmptyItems($item['menuItem']);
			if (empty($item['menuItemLink']) && (!isset($this->menuItems[$item['menuItem']]) || !count($this->menuItems[$item['menuItem']]))) {
				// remove this item
				unset($this->menuItems[$parentMenuItem][$key]);
			}
		}
	}
	
	/**
	 * Checks the permissions of the menu items.
	 * Removes item without permission.
	 * 
	 * @param	string		$parentMenuItem
	 */
	protected function checkPermissions($parentMenuItem = '') {
		if (!isset($this->menuItems[$parentMenuItem])) return;
		
		foreach ($this->menuItems[$parentMenuItem] as $key => $item) {
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
			
			if ($hasPermission) {
				// check permission of the children
				$this->checkPermissions($item['menuItem']);
			}
			else {
				// remove this item
				unset($this->menuItems[$parentMenuItem][$key]);
			}
		}
	}
	
	/**
	 * Checks the options of the menu items.
	 * Removes items of disabled options.
	 * 
	 * @param	string		$parentMenuItem
	 */
	protected function checkOptions($parentMenuItem = '') {
		if (!isset($this->menuItems[$parentMenuItem])) return;
		
		foreach ($this->menuItems[$parentMenuItem] as $key => $item) {
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
			
			if ($hasEnabledOption) {
				// check option of the children
				$this->checkOptions($item['menuItem']);
			}
			else {
				// remove this item
				unset($this->menuItems[$parentMenuItem][$key]);
			}
		}
	}
	
	/**
	 * Sets the active menu item. 
	 * This should be done before the menu.tpl template calls the function getMenu().
	 * 
	 * This function should be used in each script which uses a template that includes the menu.tpl.
	 * 
	 * @param	string		$menuItem	name of the active menu item
	 */
	public function setActiveMenuItem($menuItem) {
		if ($this->menuItems === null) {
			$this->buildMenu();
		}
		
		$this->activeMenuItems = array(); 
		
		// build active menu list
		while (isset($this->menuItemList[$menuItem])) {
			$this->activeMenuItems[] = $menuItem;
			$menuItem = $this->menuItemList[$menuItem]['parentMenuItem'];
		}
	}
	
	/**
	 * Returns a list of the active menu items.
	 * 
	 * @return	array
	 */
	public function getActiveMenuItems() {
		return $this->activeMenuItems;
	}
	
	/**
	 * Returns the active menu item.
	 * 
	 * @param	integer		$level
	 * @return	string
	 */
	public function getActiveMenuItem($level = 0) {
		if ($level < count($this->activeMenuItems)) {
			return $this->activeMenuItems[(count($this->activeMenuItems) - ($level + 1))];
		}
		return null;
	}
	
	/**
	 * Returns the list of menu items.
	 * 
	 * @param 	string		$parentMenuItem
	 * @return	array
	 */
	public function getMenuItems($parentMenuItem = null) {
		if ($this->menuItems === null) {
			$this->buildMenu();
		}
		
		if ($parentMenuItem === null) return $this->menuItems;
		if (isset($this->menuItems[$parentMenuItem])) return $this->menuItems[$parentMenuItem];
		return array();
	}
}
?>