<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the board permissions for a combination of user groups.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.cache
 * @category 	Burning Board
 */
class CacheBuilderBoardPermissions implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $groupIDs) = explode('-', $cacheResource['cache']);
		$data = array();
		
		$sql = "SELECT		*
			FROM		wbb".WBB_N."_board_to_group
			WHERE		groupID IN (".$groupIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$boardID = $row['boardID'];
			unset($row['boardID'], $row['groupID']);
			
			foreach ($row as $permission => $value) {
				if ($value == -1) continue;
				
				if (!isset($data[$boardID][$permission])) $data[$boardID][$permission] = $value;
				else $data[$boardID][$permission] = $value || $data[$boardID][$permission];
			}
		}
		
		if (count($data)) {
			require_once(WBB_DIR.'lib/data/board/Board.class.php');
			Board::inheritPermissions(0, $data);
		}
		
		$data['groupIDs'] = $groupIDs;
		return $data;
	}
}
?>