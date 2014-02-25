<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the searchable message types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.search
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderSearchableMessageType implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$data = array();
		
		// get all searchable message types and filter menu items by priority
		$sql = "SELECT		message_type.typeID, message_type.typeName
			FROM		wcf".WCF_N."_package_dependency package_dependency,
					wcf".WCF_N."_searchable_message_type message_type
			WHERE 		message_type.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$itemIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$itemIDs[$row['typeName']] = $row['typeID'];
		}
		
		if (count($itemIDs) > 0) {
			$sql = "SELECT		message_type.*, package.packageDir, IF(message_type.packageID = ".$packageID.", 0, 1) AS sortOrder
				FROM		wcf".WCF_N."_searchable_message_type message_type
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = message_type.packageID)
				WHERE 		message_type.typeID IN (".implode(',', $itemIDs).")
				ORDER BY	sortOrder, message_type.typeID";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$data[$row['typeName']] = $row;
			}
		}
		
		return $data;
	}
}
?>