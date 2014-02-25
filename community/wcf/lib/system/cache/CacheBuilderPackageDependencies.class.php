<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the dependencies of a package.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderPackageDependencies implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']);
		$data = array();
		
		if ($packageID != 0) {
			$sql = "SELECT		package.packageID, package.package
				FROM		wcf".WCF_N."_package_dependency package_dependency
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = package_dependency.dependency)
				WHERE		package_dependency.packageID = ".$packageID."
				ORDER BY	package_dependency.priority";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!isset($data[$row['package']])) $data[$row['package']] = array();
				$data[$row['package']][] = $row['packageID'];
			}
			
			foreach ($data as $package => $packageIDArray) {
				if (count($packageIDArray) == 1) {
					$data[$package] = array_shift($packageIDArray);
				}
			}
		}
		
		return $data;
	}
}
?>