<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * Caches all boards, the structure of boards and all moderators.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.cache
 * @category 	Burning Board
 */
class CacheBuilderBoard implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array('boards' => array(), 'boardStructure' => array(), 'moderators' => array());
		
		// boards
		$sql = "SELECT	*
			FROM 	wbb".WBB_N."_board";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$data['boards'][$row['boardID']] = new Board(null, $row);
		}
		
		// board structure
		$sql = "SELECT		*
			FROM 		wbb".WBB_N."_board_structure
			ORDER BY 	parentID ASC, position ASC";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$data['boardStructure'][$row['parentID']][] = $row['boardID'];
		}
		
		// board moderators
		$sql = "SELECT 		user.username, wcf_group.groupName,
					moderator.*, IFNULL(user.username, wcf_group.groupName) AS name
			FROM 		wbb".WBB_N."_board_moderator moderator
			LEFT JOIN 	wcf".WCF_N."_user user
			ON		(user.userID = moderator.userID)
			LEFT JOIN 	wcf".WCF_N."_group wcf_group
			ON 		(wcf_group.groupID = moderator.groupID)
			ORDER BY 	boardID,
					name";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (empty($row['name'])) continue;
			
			if ($row['userID'] != 0) {
				$object = new User(null, $row); 
				$key = 'u' . $row['userID'];
			}
			else {
				$object = new Group(null, $row);
				$key = 'g' . $row['groupID'];
			}
			$data['moderators'][$row['boardID']][$key] = $object;
		}
		
		return $data;
	}
}
?>