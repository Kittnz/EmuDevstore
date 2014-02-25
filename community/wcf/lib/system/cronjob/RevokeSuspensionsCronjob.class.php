<?php
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');

/**
 * Revokes expired suspensions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	system.cronjob
 * @category 	Community Framework (commercial)
 */
class RevokeSuspensionsCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		// require classes
		require_once(WCF_DIR.'lib/data/user/User.class.php');
		require_once(WCF_DIR.'lib/data/user/infraction/suspension/Suspension.class.php');
		require_once(WCF_DIR.'lib/data/user/infraction/suspension/UserSuspension.class.php');

		// get user suspensions
		$userSuspensions = $users = $suspensions = $userIDArray = $suspensionIDArray = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_user_infraction_suspension_to_user
			WHERE		packageID IN (
						SELECT	dependency
						FROM	wcf".WCF_N."_package_dependency
						WHERE	packageID = ".PACKAGE_ID."
					)
					AND revoked = 0
					AND expires > 0
					AND expires < ".TIME_NOW."
			ORDER BY 	expires";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$userSuspensions[] = new UserSuspension(null, $row);
			$userIDArray[$row['userID']] = $row['userID'];
			$suspensionIDArray[$row['suspensionID']] = $row['suspensionID'];
		}
		
		// get unexpired suspensions of the affected users 
		$unexpiredUserSuspensions = array();
		if (count($userIDArray)) {
			$sql = "SELECT		suspension_to_user.*, suspension.suspensionType
				FROM		wcf".WCF_N."_user_infraction_suspension_to_user suspension_to_user
				LEFT JOIN	wcf".WCF_N."_user_infraction_suspension suspension
				ON		(suspension.suspensionID = suspension_to_user.suspensionID)
				WHERE		suspension_to_user.userID IN (".implode(',', $userIDArray).")
						AND suspension_to_user.packageID IN (
							SELECT	dependency
							FROM	wcf".WCF_N."_package_dependency
							WHERE	packageID = ".PACKAGE_ID."
						)
						AND suspension_to_user.revoked = 0
						AND (suspension_to_user.expires = 0 OR suspension_to_user.expires >= ".TIME_NOW.")
				ORDER BY 	suspension_to_user.expires";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!isset($unexpiredUserSuspensions[$row['userID']])) $unexpiredUserSuspensions[$row['userID']] = array();
				$unexpiredUserSuspensions[$row['userID']][] = new UserSuspension(null, $row);
			}
		}
		
		// get users
		if (count($userIDArray)) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_user
				WHERE	userID IN (".implode(',', $userIDArray).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$users[$row['userID']] = new User(null, $row);
			}
		}
		
		// get suspensions
		if (count($suspensionIDArray)) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_user_infraction_suspension
				WHERE	suspensionID IN (".implode(',', $suspensionIDArray).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$suspensions[$row['suspensionID']] = new Suspension(null, $row);
			}
		}
		
		// revoke suspensions
		$revokedIDArray = array();
		foreach ($userSuspensions as $userSuspension) {
			if (isset($suspensions[$userSuspension->suspensionID]) && isset($users[$userSuspension->userID])) {
				$revokedIDArray[] = $userSuspension->userSuspensionID;
				$suspensionType = $suspensions[$userSuspension->suspensionID]->suspensionType;
				
				// check unexpired suspensions of this user
				if (isset($unexpiredUserSuspensions[$userSuspension->userID])) {
					foreach ($unexpiredUserSuspensions[$userSuspension->userID] as $unexpiredUserSuspension) {
						if ($unexpiredUserSuspension->suspensionType == $suspensionType) {
							continue 2;
						}
					}
				}
				
				// revoke suspension
				$object = Suspension::getSuspensionTypeObject($suspensionType);
				$object->revoke($users[$userSuspension->userID], $userSuspension, $suspensions[$userSuspension->suspensionID]);
			}
		}
		
		// flag revoked suspensions
		if (count($revokedIDArray)) {
			$sql = "UPDATE	wcf".WCF_N."_user_infraction_suspension_to_user
				SET	revoked = 1
				WHERE	userSuspensionID IN (".implode(',', $revokedIDArray).")";
			 WCF::getDB()->sendQuery($sql);
		}
		
		// reset sessions
		if (count($userIDArray)) {
			Session::resetSessions($userIDArray);
		}
	}
}
?>