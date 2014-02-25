<?php
// wbb imports
require_once(WBB_DIR.'lib/acp/action/UpdateCounterAction.class.php');

/**
 * Updates the users.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class UpdateUsersAction extends UpdateCounterAction {
	public $action = 'UpdateUsers';
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// count users
		$sql = "SELECT	COUNT(*) AS count
			FROM	wbb".WBB_N."_user";
		$row = WCF::getDB()->getFirstRow($sql);
		$count = $row['count'];
		
		// get user ids
		$userIDs = '';
		$sql = "SELECT		userID
			FROM		wbb".WBB_N."_user
			ORDER BY	userID";
		$result = WCF::getDB()->sendQuery($sql, $this->limit, ($this->limit * $this->loop));
		while ($row = WCF::getDB()->fetchArray($result)) {
			$userIDs .= ','.$row['userID'];
			
			// update last posts
			if (PROFILE_SHOW_LAST_POSTS) {
				// delete old entries
				$sql = "DELETE FROM	wbb".WBB_N."_user_last_post
					WHERE		userID = ".$row['userID'];
				WCF::getDB()->sendQuery($sql);
				
				// get new entries
				$sql = "SELECT		postID, time
					FROM		wbb".WBB_N."_post
					WHERE		userID = ".$row['userID']."
					ORDER BY	time DESC";
				$result2 = WCF::getDB()->sendQuery($sql, 20);
				while ($row2 = WCF::getDB()->fetchArray($result2)) {
					$sql = "INSERT INTO	wbb".WBB_N."_user_last_post
								(userID, postID, time)
						VALUES		(".$row['userID'].", ".$row2['postID'].", ".$row2['time'].")";
					WCF::getDB()->sendQuery($sql);
				}
			}
		}
		
		if (empty($userIDs)) {
			$this->calcProgress();
			$this->finish();
		}
		
		// get boards
		$boardIDs = '';
		$sql = "SELECT	boardID
			FROM	wbb".WBB_N."_board
			WHERE	boardType = 0
				AND countUserPosts = 1";
		$result2 = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result2)) {
			$boardIDs .= ','.$row['boardID'];
		}
		
		// update users posts
		$sql = "UPDATE	wbb".WBB_N."_user user
			SET	posts = (
					SELECT		COUNT(*)
					FROM		wbb".WBB_N."_post post
					LEFT JOIN	wbb".WBB_N."_thread thread
					ON		(thread.threadID = post.threadID)
					WHERE		post.userID = user.userID
							AND post.isDeleted = 0
							AND post.isDisabled = 0
							AND thread.boardID IN (0".$boardIDs.")
				)
			WHERE	user.userID IN (0".$userIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		// update activity points
		$sql = "SELECT		wbb_user.userID, wbb_user.posts, user.activityPoints, COUNT(thread.threadID) AS threads
			FROM		wbb".WBB_N."_user wbb_user
			LEFT JOIN	wcf".WCF_N."_user user
			ON		(user.userID = wbb_user.userID)
			LEFT JOIN	wbb".WBB_N."_thread thread
			ON		(thread.userID = wbb_user.userID AND thread.boardID IN (0".$boardIDs.") AND thread.isDeleted = 0 AND thread.isDisabled = 0)
			WHERE		wbb_user.userID IN (0".$userIDs.")
			GROUP BY	wbb_user.userID";
		$result2 = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result2)) {
			$activityPoints = ($row['threads'] * ACTIVITY_POINTS_PER_THREAD) + (($row['posts'] - $row['threads']) * ACTIVITY_POINTS_PER_POST);
			// update activity points for this package
			$sql = "REPLACE INTO	wcf".WCF_N."_user_activity_point
						(userID, packageID, activityPoints)
				VALUES 		(".$row['userID'].", ".PACKAGE_ID.", ".$activityPoints.")";
			WCF::getDB()->sendQuery($sql);
		}
		
		// remove obsolet activity points
		$sql = "DELETE FROM	wcf".WCF_N."_user_activity_point
			WHERE		packageID NOT IN (
						SELECT	packageID
						FROM	wcf".WCF_N."_package
					)";
		WCF::getDB()->sendQuery($sql);
		
		// update global activity points
		$sql = "UPDATE	wcf".WCF_N."_user user
			SET	user.activityPoints = (
					SELECT	SUM(activityPoints)
					FROM	wcf".WCF_N."_user_activity_point
					WHERE	userID = user.userID
				)
			WHERE	user.userID IN (0".$userIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		// update pm counts
		$sql = "UPDATE	wcf".WCF_N."_user user
			SET	pmUnreadCount = (
					SELECT	COUNT(*)
					FROM 	wcf".WCF_N."_pm_to_user
					WHERE 	recipientID = user.userID
						AND isDeleted < 2
						AND isViewed = 0
				),
				pmTotalCount = (
					SELECT	COUNT(*)
					FROM 	wcf".WCF_N."_pm_to_user
					WHERE 	recipientID = user.userID
						AND isDeleted < 2)
						+ (
					SELECT		COUNT(*)
					FROM 		wcf".WCF_N."_pm pm
					LEFT JOIN	wcf".WCF_N."_pm_to_user pm_to_user
					ON		(pm_to_user.pmID = pm.pmID
							AND pm_to_user.recipientID = pm.userID
							AND pm_to_user.isDeleted < 2)
					WHERE 		userID = user.userID
							AND (saveInOutBox = 1
							OR isDraft = 1)
							AND pm_to_user.pmID IS NULL)
			WHERE	user.userID IN (0".$userIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		// update user rank
		require_once(WCF_DIR.'lib/data/user/rank/UserRank.class.php');
		WCF::getDB()->seekResult($result, 0);
		while ($row = WCF::getDB()->fetchArray($result)) {
			UserRank::updateActivityPoints(0, $row['userID']);
		}
		
		$this->executed();
		
		$this->calcProgress(($this->limit * $this->loop), $count);
		$this->nextLoop();
	}
}
?>