<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches all user groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderGroups implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array('types' => array(), 'groups' => array());

		// get all user groups
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_group
			ORDER BY	groupName";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($data['types'][$row['groupType']])) {
				$data['types'][$row['groupType']] = array();
			}
			
			$data['types'][$row['groupType']][] = $row['groupID'];
			$data['groups'][$row['groupID']] = $row;
		}
		
		return $data;
	}
}
?>