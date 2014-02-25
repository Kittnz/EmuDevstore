<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches all registered packages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderPackages implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array();

		// get all packages
		try {
			$sql = "SELECT		package.*, CASE WHEN package.package='com.woltlab.wcf' THEN 1 ELSE 0 END AS wcfPackage,
						CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
				FROM		wcf".WCF_N."_package package
				ORDER BY	standalone DESC, wcfPackage, packageName";
			$result = WCF::getDB()->sendQuery($sql);
		}
		catch (DatabaseException $e) {
			// horizon update workaround
			$sql = "SELECT		package.*, CASE WHEN package.package='com.woltlab.wcf' THEN 1 ELSE 0 END AS wcfPackage
				FROM		wcf".WCF_N."_package package
				ORDER BY	standalone DESC, wcfPackage, packageName";
			$result = WCF::getDB()->sendQuery($sql);
		}
		
		while ($row = WCF::getDB()->fetchArray($result)) {
			$data[$row['packageID']] = $row;
		}
		
		return $data;
	}
}
?>