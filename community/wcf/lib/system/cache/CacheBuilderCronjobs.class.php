<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches cronjob information.
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderCronjobs implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$allCronjobs = array();
		$data = array('cronjobs' => array(), 'nextExec' => 0);
		
		// get cronjobs that either were installed by the current package itself or by one of the packages
		// that the current package has got as a dependency.
		$sql = "SELECT		package.packageDir, cronjobs.cronjobID, cronjobs.classPath, 
					cronjobs.startMinute, cronjobs.startHour, cronjobs.startDom, 
					cronjobs.startMonth, cronjobs.startDow, cronjobs.execMultiple, 
					cronjobs.lastExec, cronjobs.nextExec 
			FROM		wcf".WCF_N."_cronjobs cronjobs, 
					wcf".WCF_N."_package_dependency package_dependency
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = package_dependency.dependency)
			WHERE		cronjobs.packageID = package_dependency.dependency 
					AND package_dependency.packageID = ".$packageID."
					AND cronjobs.active = 1
			ORDER BY	cronjobs.lastExec";
		$result = WCF::getDB()->sendQuery($sql);
		// omit unconfigured cronjobs.
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['classPath'] = $row['packageDir'].$row['classPath'];
			unset($row['packageDir']);
			$data['cronjobs'][] = $row;
		}
		// find lowest nextExec.
		if (count($data['cronjobs'])) {
			$data['nextExec'] = $data['cronjobs']['0']['nextExec'];
			foreach ($data['cronjobs'] as $cronjob) {
				if ($cronjob['nextExec'] < $data['nextExec']) {
					$data['nextExec'] = $cronjob['nextExec'];
				}
			}
		}
		return $data;
	}
}
?>