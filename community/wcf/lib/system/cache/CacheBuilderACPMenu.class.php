<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the acp menu items tree.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderACPMenu implements CacheBuilder {
	protected $optionCategoryStructure = array();

	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$data = array();

		// get all menu items and filter menu items with low priority
		$sql = "SELECT		menuItem, menuItemID 
			FROM		wcf".WCF_N."_acp_menu_item menu_item,
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
			$sql = "SELECT		menu_item.packageID, menuItem, parentMenuItem,
						menuItemLink, menuItemIcon, permissions, options, packageDir
				FROM		wcf".WCF_N."_acp_menu_item menu_item
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
		
		// get top option categories
		$optionCategories = $this->getTopOptionCategories($packageID);
		if (count($optionCategories) > 0) {
			if (!isset($data['wcf.acp.menu.link.option.category'])) {
				$data['wcf.acp.menu.link.option.category'] = array();
			}
			
			// get option category data
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_option_category
				WHERE		categoryID IN (".implode(',', $optionCategories).")
				ORDER BY	showOrder";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$data['wcf.acp.menu.link.option.category'][] = array(	'packageID' => $packageID,
										'menuItem' => 'wcf.acp.option.category.'.$row['categoryName'],
										'parentMenuItem' => 'wcf.acp.menu.link.option.category',
										'menuItemLink' => 'index.php?form=Option&categoryID='.$row['categoryID'],
										'menuItemIcon' => '',
										'permissions' => '',
										'packageDir' => '',
										'permissions' => $row['permissions'],
										'options' => $row['options']);
			}
		}
		
		return $data;
	}
	
	protected function getTopOptionCategories($packageID) {
		// get all option categories and filter categories with low priority
		$sql = "SELECT		categoryName, categoryID 
			FROM		wcf".WCF_N."_option_category option_category,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		option_category.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$optionCategories = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$optionCategories[$row['categoryName']] = $row['categoryID'];
		}
		
		$sql = "SELECT 		categoryID, parentCategoryName, categoryName,
					(
						SELECT COUNT(*) FROM wcf".WCF_N."_option WHERE categoryName = category.categoryName AND packageID IN (
							SELECT dependency FROM wcf".WCF_N."_package_dependency WHERE packageID = ".$packageID."
						)
					) AS count
			FROM		wcf".WCF_N."_option_category category
			WHERE		categoryID IN (".implode(',', $optionCategories).")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($this->optionCategoryStructure[$row['parentCategoryName']])) $this->optionCategoryStructure[$row['parentCategoryName']] = array();
			$this->optionCategoryStructure[$row['parentCategoryName']][] = $row;
		}
		
		$topOptionCategories = array();
		foreach ($this->optionCategoryStructure[''] as $optionCategory) {
			$count = $optionCategory['count'] + $this->countOptions($optionCategory['categoryName']);
			if ($count > 0) $topOptionCategories[] = $optionCategory['categoryID'];
		}
		
		return $topOptionCategories;
	}
	
	protected function countOptions($parentCategoryName) {
		if (!isset($this->optionCategoryStructure[$parentCategoryName])) return 0;
		
		$count = 0;
		foreach ($this->optionCategoryStructure[$parentCategoryName] as $optionCategory) {
			$count += $optionCategory['count'] + $this->countOptions($optionCategory['categoryName']);
		}
		
		return $count;
	}
}
?>