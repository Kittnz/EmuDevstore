<?php
// imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the page locations for users online list.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.usersOnline
 * @subpackage	system.cache
 * @category 	Community Framework (commercial)
 */
class CacheBuilderPageLocations implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$data = array();
		
		$sql = "SELECT		location.*, package.packageDir
			FROM		wcf".WCF_N."_package_dependency package_dependency,
					wcf".WCF_N."_page_location location
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = location.packageID)
			WHERE 		location.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['className'] = StringUtil::getClassName($row['classPath']);
			$data[$row['locationName']] = $row;
		}
		
		return $data;
	}
}
?>