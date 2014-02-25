<?php
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');

/**
 * Deletes registered children who are not sending in parental permission within x days after registration
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	system.cronjob
 * @category 	Community Framework (commercial)
 */
class GroupAutoJoinCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_group
			WHERE	neededAge <> 0
				OR neededPoints <> 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$userIDArray = array();
			if ($row['neededAge'] > 0) {
				$sql = "SELECT	userID
					FROM	wcf".WCF_N."_user
					WHERE	registrationDate <= ".(TIME_NOW - 86400 * $row['neededAge'])."
						AND userID NOT IN (
							SELECT	userID
							FROM	wcf".WCF_N."_user_to_groups
							WHERE	groupID = ".$row['groupID']."
						)";
				$result2 = WCF::getDB()->sendQuery($sql);
				while ($row2 = WCF::getDB()->fetchArray($result2)) {
					$userIDArray[] = $row2['userID'];
				}
			}
			if ($row['neededPoints'] > 0) {
				$sql = "SELECT	userID
					FROM	wcf".WCF_N."_user
					WHERE	activityPoints >= ".$row['neededPoints']."
						AND userID NOT IN (
							SELECT	userID
							FROM	wcf".WCF_N."_user_to_groups
							WHERE	groupID = ".$row['groupID']."
						)";
				$result2 = WCF::getDB()->sendQuery($sql);
				while ($row2 = WCF::getDB()->fetchArray($result2)) {
					$userIDArray[] = $row2['userID'];
				}
			}
			
			if (count($userIDArray)) {
				$userIDArray = array_unique($userIDArray);

				// assign to group
				$sql = "INSERT INTO	wcf".WCF_N."_user_to_groups
							(userID, groupID)
					SELECT		userID, ".$row['groupID']."
					FROM		wcf".WCF_N."_user
					WHERE		userID IN (".implode(',', $userIDArray).")";
				WCF::getDB()->sendQuery($sql);
				
				// reset sesions
				Session::resetSessions($userIDArray);
			}
		}
	}
}
?>