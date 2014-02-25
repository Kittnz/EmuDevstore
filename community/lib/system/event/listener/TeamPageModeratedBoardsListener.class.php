<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Shows the by a user moderated boards on team page..
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class TeamPageModeratedBoardsListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (TEAM_SHOW_MODERATED_BOARDS) {
			if ($eventName == 'readData') {
				$eventObj->activeFields[] = 'moderatedBoards';
			}
			else if ($eventName == 'assignVariables') {
				require_once(WBB_DIR.'lib/data/board/Board.class.php');

				// get user ids
				$userIDArray = array_keys($eventObj->members);
				if (count($userIDArray)) {
					// get user to groups
					$userToGroups = array();
					$groupIDArray = array();
					$sql = "SELECT	userID, groupID
						FROM	wcf".WCF_N."_user_to_groups
						WHERE	userID IN (".implode(',', $userIDArray).")";
					$result = WCF::getDB()->sendQuery($sql);
					while ($row = WCF::getDB()->fetchArray($result)) {
						if (!isset($userToGroups[$row['userID']])) $userToGroups[$row['userID']] = array();
						$userToGroups[$row['userID']][] = $row['groupID'];
						$groupIDArray[$row['groupID']] = $row['groupID'];
					}
					
					// get user to boards
					$userToBoards = array();
					$sql = "SELECT	userID, boardID
						FROM	wbb".WBB_N."_board_moderator
						WHERE	userID IN (".implode(',', $userIDArray).")";
					$result = WCF::getDB()->sendQuery($sql);
					while ($row = WCF::getDB()->fetchArray($result)) {
						if (!isset($userToBoards[$row['userID']])) $userToBoards[$row['userID']] = array();
						$userToBoards[$row['userID']][] = $row['boardID'];
					}
		
					// get group to boards
					$groupToBoards = array();
					if (count($groupIDArray)) {
						$sql = "SELECT	groupID, boardID
							FROM	wbb".WBB_N."_board_moderator
							WHERE	groupID IN (".implode(',', $groupIDArray).")";
						$result = WCF::getDB()->sendQuery($sql);
						while ($row = WCF::getDB()->fetchArray($result)) {
							if (!isset($groupToBoards[$row['groupID']])) $groupToBoards[$row['groupID']] = array();
							$groupToBoards[$row['groupID']][] = $row['boardID'];
						}
					}
					
					foreach ($eventObj->members as $key => $memberData) {
						// get board ids
						$boardIDArray = array();
						if (isset($userToBoards[$key])) $boardIDArray = $userToBoards[$key];
						if (isset($userToGroups[$key])) {
							foreach ($userToGroups[$key] as $groupID) {
								if (isset($groupToBoards[$groupID])) $boardIDArray = array_merge($boardIDArray, $groupToBoards[$groupID]);
							}
						}
						
						// get boards and check permissions
						array_unique($boardIDArray);
						$boards = array();
						foreach ($boardIDArray as $boardID) {
							$board = Board::getBoard($boardID);
							if ($board->getPermission('canViewBoard')) {
								$boards[$board->boardID] = WCF::getLanguage()->get(StringUtil::encodeHTML($board->title));
							}
						}
						
						if (count($boards)) {
							// sort boards
							StringUtil::sort($boards);
							
							// generate output
							$output = '';
							foreach ($boards as $boardID => $title) {
								if (!empty($output)) $output .= ', ';
								$output .= '<a href="index.php?page=Board&amp;boardID='.$boardID.SID_ARG_2ND.'">'.$title.'</a>';
							}
							$eventObj->members[$key]['moderatedBoards'] = $output;
						}
					}
				}
			}
		}
	}
}
?>