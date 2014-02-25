<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the structure of templates.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderTemplates implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$information = explode('-', $cacheResource['cache']);
		if (count($information) == 3) {
			$prefix = $information[0].'_';
			$packageID = $information[2];
		}
		else {
			$prefix = '';
			$packageID = $information[1];
		}

		$data = array();

		// get all templates and filter options with low priority
		$sql = "SELECT		templateName, template.packageID 
			FROM		wcf".WCF_N."_".$prefix."template template,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		template.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority DESC";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($data[$row['templateName']]) || $packageID == $row['packageID']) {
				$data[$row['templateName']] = $row['packageID'];
			}
		}
		
		return $data;
	}
}
?>