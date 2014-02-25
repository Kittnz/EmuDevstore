<?php
// wcf imports
require_once(WCF_DIR.'lib/system/session/UserSession.class.php');

/**
 * Abstract class for wbb user and guest sessions.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.user
 * @category 	Burning Board
 */
class AbstractWBBUserSession extends UserSession {
	protected $boardPermissions = array();
	protected $boardModeratorPermissions = array();
	
	/**
	 * Checks whether this user has the permission with the given name on the board with the given board id.
	 * 
	 * @param	string		$permission	name of the requested permission
	 * @param	integer		$boardID
	 * @return	mixed				value of the permission
	 */
	public function getBoardPermission($permission, $boardID) {
		if (isset($this->boardPermissions[$boardID][$permission])) {
			return $this->boardPermissions[$boardID][$permission];
		}
		return $this->getPermission('user.board.'.$permission);
	}
	
	/**
	 * Checks whether this user has the moderator permission with the given name on the board with the given board id.
	 * 
	 * @param	string		$permission	name of the requested permission
	 * @param	integer		$boardID
	 * @return	mixed				value of the permission
	 */
	public function getBoardModeratorPermission($permission, $boardID) {
		if (isset($this->boardModeratorPermissions[$boardID][$permission])) {
			return $this->boardModeratorPermissions[$boardID][$permission];
		}
		
		return (($this->getPermission('mod.board.isSuperMod') || isset($this->boardModeratorPermissions[$boardID])) && $this->getPermission('mod.board.'.$permission));
	}
	
	/**
	 * @see UserSession::getGroupData()
	 */
	protected function getGroupData() {
		parent::getGroupData();
		
		// get group permissions from cache (board_to_group)
		$groups = implode(",", $this->groupIDs);
		$groupsFileName = StringUtil::getHash(implode("-", $this->groupIDs));
		
		// register cache resource
		WCF::getCache()->addResource('boardPermissions-'.$groups, WBB_DIR.'cache/cache.boardPermissions-'.$groupsFileName.'.php', WBB_DIR.'lib/system/cache/CacheBuilderBoardPermissions.class.php');
		
		// get group data from cache
		$this->boardPermissions = WCF::getCache()->get('boardPermissions-'.$groups);
		if (isset($this->boardPermissions['groupIDs']) && $this->boardPermissions['groupIDs'] != $groups) {
			$this->boardPermissions = array();
		}
		
		// get board moderator permissions
		$sql = "SELECT		*
			FROM		wbb".WBB_N."_board_moderator
			WHERE		groupID IN (".implode(',', $this->groupIDs).")
					".($this->userID ? " OR userID = ".$this->userID : '')."
			ORDER BY 	userID DESC";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$boardID = $row['boardID'];
			unset($row['boardID'], $row['userID'], $row['groupID']);
			
			if (!isset($this->boardModeratorPermissions[$boardID])) {
				$this->boardModeratorPermissions[$boardID] = array();
			}
			
			foreach ($row as $permission => $value) {
				if ($value == -1) continue;
				
				if (!isset($this->boardModeratorPermissions[$boardID][$permission])) $this->boardModeratorPermissions[$boardID][$permission] = $value;
				else $this->boardModeratorPermissions[$boardID][$permission] = $value || $this->boardModeratorPermissions[$boardID][$permission];
			}
		}
		
		if (count($this->boardModeratorPermissions)) {
			require_once(WBB_DIR.'lib/data/board/Board.class.php');
			Board::inheritPermissions(0, $this->boardModeratorPermissions);
		}
	}
}
?>