<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the options and option categories
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderOption implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$information = explode('-', $cacheResource['cache']);
		if (count($information) == 3) {
			$type = $information[0].'_';
			$packageID = $information[2];
		}
		else {
			$type = '';
			$packageID = $information[1];
		}
		 
		$data = array(
			'categories' => array(),
			'options' => array(),
			'categoryStructure' => array(),
			'optionToCategories' => array()
		);

		// option categories
		// get all option categories and filter categories with low priority
		$sql = "SELECT		categoryName, categoryID 
			FROM		wcf".WCF_N."_".$type."option_category option_category,
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
			// get needed option categories
			$sql = "SELECT		option_category.*, package.packageDir
				FROM		wcf".WCF_N."_".$type."option_category option_category
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = option_category.packageID)
				WHERE		categoryID IN (".implode(',', $optionCategories).")
				ORDER BY	showOrder";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$data['categories'][$row['categoryName']] = $row;
				if (!isset($data['categoryStructure'][$row['parentCategoryName']])) {
					$data['categoryStructure'][$row['parentCategoryName']] = array();
				}
				
				$data['categoryStructure'][$row['parentCategoryName']][] = $row['categoryName'];
			}
		}
		
		// options
		// get all options and filter options with low priority
		$sql = "SELECT		optionName, optionID 
			FROM		wcf".WCF_N."_".$type."option option_table,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		option_table.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$options = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$options[$row['optionName']] = $row['optionID'];
		}
		
		if (count($options) > 0) {
			// get needed options
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_".$type."option
				WHERE		optionID IN (".implode(',', $options).")
				ORDER BY	showOrder";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				// unserialize additional data
				$row['additionalData'] = (empty($row['additionalData']) ? array() : @unserialize($row['additionalData']));
				
				$data['options'][$row['optionName']] = $row;
				if (!isset($data['optionToCategories'][$row['categoryName']])) {
					$data['optionToCategories'][$row['categoryName']] = array();
				}
				
				$data['optionToCategories'][$row['categoryName']][] = $row['optionName'];
			}
		}
		
		return $data;
	}
}
?>