<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches help items.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.help
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderHelp implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$data = array('items' => array(), 'structure' => array());
		
		// get all menu items and filter menu items with low priority
		$sql = "SELECT		helpItem, helpItemID 
			FROM		wcf".WCF_N."_help_item help_item,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		help_item.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$itemIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$itemIDs[$row['helpItem']] = $row['helpItemID'];
		}
		
		if (count($itemIDs) > 0) {
			require_once(WCF_DIR.'lib/data/help/HelpItem.class.php');
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_help_item
				WHERE		helpItemID IN (".implode(',', $itemIDs).")
						AND isDisabled = 0
				ORDER BY	showOrder";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$data['items'][$row['helpItem']] = new HelpItem(null, $row);
				
				if (!isset($data['structure'][$row['parentHelpItem']])) {
					$data['structure'][$row['parentHelpItem']] = array();
				}
				$data['structure'][$row['parentHelpItem']][] = $row['helpItem'];
			}
		}
		
		return $data;
	}
}
?>