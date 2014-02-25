<?php
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the warning object types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2008 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	system.cache
 * @category 	Community Framework (commercial)
 */
class CacheBuilderWarningObjectTypes implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$data = array();
		
		// get type ids
		$typeIDArray = array();
		$sql = "SELECT		objectType, objectTypeID 
			FROM		wcf".WCF_N."_user_infraction_warning_object_type object_type,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		object_type.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$typeIDArray[$row['objectType']] = $row['objectTypeID'];
		}
		
		if (count($typeIDArray) > 0) {
			$sql = "SELECT		object_type.*, package.packageDir
				FROM		wcf".WCF_N."_user_infraction_warning_object_type object_type
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = object_type.packageID)
				WHERE		object_type.objectTypeID IN (".implode(',', $typeIDArray).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$row['className'] = StringUtil::getClassName($row['classFile']);
				$data[] = $row;
			}
		}
		
		return $data;
	}
}
?>