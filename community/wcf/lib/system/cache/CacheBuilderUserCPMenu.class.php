<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the user control panel menu items tree.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderUserCPMenu implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$data = array();

		// get all menu items and filter menu items with low priority
		$sql = "SELECT		menuItem, menuItemID 
			FROM		wcf".WCF_N."_usercp_menu_item menu_item,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		menu_item.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$itemIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$itemIDs[$row['menuItem']] = $row['menuItemID'];
		}
		
		if (count($itemIDs) > 0) {
			// get needed menu items and build item tree
			$sql = "SELECT		menuItem, parentMenuItem, menuItemLink,
						menuItemIcon, permissions, options, packageDir
				FROM		wcf".WCF_N."_usercp_menu_item menu_item
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = menu_item.packageID)
				WHERE		menuItemID IN (".implode(',', $itemIDs).")
				ORDER BY	showOrder";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!isset($data[$row['parentMenuItem']])) {
					$data[$row['parentMenuItem']] = array();
				}
				
				$data[$row['parentMenuItem']][] = $row;
			}
		}
		
		// get all option categories and filter categories with low priority
		$sql = "SELECT		categoryName, categoryID 
			FROM		wcf".WCF_N."_user_option_category option_category,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		option_category.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$optionCategories = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$optionCategories[$row['categoryName']] = $row['categoryID'];
		}
		
		if (count($optionCategories) > 0) {
			if (!isset($data['wcf.user.usercp.menu.link.settings'])) {
				$data['wcf.user.usercp.menu.link.settings'] = array();
			}
			
			// get needed option categories
			$sql = "SELECT		option_category.*, packageDir
				FROM		wcf".WCF_N."_user_option_category option_category
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = option_category.packageID)				
				WHERE		categoryID IN (".implode(',', $optionCategories).")
						AND parentCategoryName = 'settings'
				ORDER BY	showOrder";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$data['wcf.user.usercp.menu.link.settings'][] = array(
					'menuItem' => 'wcf.user.option.category.'.$row['categoryName'],
					'parentMenuItem' => 'wcf.user.usercp.menu.link.settings',
					'menuItemLink' => 'index.php?form=UserProfileEdit&category='.$row['categoryName'],
					'menuItemIcon' => $row['categoryIconS'],
					'permissions' => $row['permissions'],
					'options' => $row['options'],
					'packageDir' => $row['packageDir']
				);
			}
		}
		
		return $data;
	}
}
?>