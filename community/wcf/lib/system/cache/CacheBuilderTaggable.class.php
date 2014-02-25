<?php
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the taggable types.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderTaggable implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$data = array();
		
		// get all taggable types and filter menu items by priority
		$sql = "SELECT		taggable.taggableID, taggable.name
			FROM		wcf".WCF_N."_package_dependency package_dependency,
					wcf".WCF_N."_tag_taggable taggable
			WHERE 		taggable.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$itemIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$itemIDs[$row['name']] = $row['taggableID'];
		}
		
		if (count($itemIDs) > 0) {
			$sql = "SELECT		taggable.*, package.packageDir, CASE WHEN taggable.packageID = ".$packageID." THEN 0 ELSE 1 END AS sortOrder
				FROM		wcf".WCF_N."_tag_taggable taggable
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = taggable.packageID)
				WHERE 		taggable.taggableID IN (".implode(',', $itemIDs).")
				ORDER BY	sortOrder, taggable.taggableID";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$data[$row['name']] = $row;
			}
		}
		
		return $data;
	}
}
?>