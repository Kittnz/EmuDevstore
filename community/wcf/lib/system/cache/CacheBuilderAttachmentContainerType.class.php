<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');
require_once(WCF_DIR.'lib/data/attachment/AttachmentContainerType.class.php');

/**
 * Caches the attachment container types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.attachment
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderAttachmentContainerType implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$data = array();
		
		// get all searchable message types and filter menu items by priority
		$sql = "SELECT		container_type.containerTypeID, container_type.containerType
			FROM		wcf".WCF_N."_package_dependency package_dependency,
					wcf".WCF_N."_attachment_container_type container_type
			WHERE 		container_type.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$itemIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$itemIDs[$row['containerType']] = $row['containerTypeID'];
		}
		
		if (count($itemIDs) > 0) {
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_attachment_container_type
				WHERE 		containerTypeID IN (".implode(',', $itemIDs).")
				ORDER BY	containerTypeID";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$data[$row['containerType']] = new AttachmentContainerType(null, $row);
			}
		}
		
		return $data;
	}
}
?>