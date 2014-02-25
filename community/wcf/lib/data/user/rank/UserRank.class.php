<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a user rank.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.rank
 * @category 	Community Framework
 */
class UserRank extends DatabaseObject {
	/**
	 * Creates a new UserRank object.
	 * 
	 * @param	array		$row
	 * @param	integer		$rankID
	 */
	public function __construct($rankID, $row = null) {
		if ($rankID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_user_rank
				WHERE	rankID = ".$rankID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns the image of this user rank.
	 * 
	 * @return	string		html code
	 */
	public function getImage() {
		if ($this->rankImage) {
			$image = '<img src="'.(!preg_match('~^(/|https?://)~i', $this->rankImage) ? RELATIVE_WCF_DIR : '').StringUtil::encodeHTML($this->rankImage).'" alt="" />';
			if ($this->repeatImage > 1) $image = str_repeat($image, $this->repeatImage);
			return $image;
		}
		
		return '';
	}
	
	/**
	 * @see UserRank::getImage()
	 */
	public function __toString() {
		return $this->getImage();
	}
	
	/**
	 * Updates the amount of activity points of a user.
	 * 
	 * @param	integer		$points
	 * @param	integer		$userID
	 * @param	integer		$packageID
	 */
	public static function updateActivityPoints($points, $userID = null, $packageID = PACKAGE_ID) {
		// get user object
		if ($userID === null) {
			$user = WCF::getUser();
		}
		else {
			$user = new User($userID);
			if (!$user->userID) return false;
		}

		if ($points != 0) {
			// update activity points for the package
			$sql = "UPDATE	wcf".WCF_N."_user_activity_point
				SET	activityPoints = IF(".$points." > 0 OR activityPoints > ABS(".$points."), activityPoints + ".$points.", 0)
				WHERE	userID = ".$user->userID."
					AND packageID = ".$packageID;
			WCF::getDB()->sendQuery($sql);
			
			if (WCF::getDB()->getAffectedRows() == 0) {
				$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_activity_point
								(userID, packageID, activityPoints)
					VALUES			(".$user->userID.", ".$packageID.", ".($points > 0 ? $points : 0).")";
				WCF::getDB()->sendQuery($sql);
			}
		}
		
		// update user new rank
		$newRankID = 0;
		$neededPoints = intval($user->activityPoints + $points);
		if ($neededPoints < 0) $neededPoints = 0;
		$sql = "SELECT		rankID
			FROM		wcf".WCF_N."_user_rank
			WHERE		groupID IN (".($user->rankID ? "(SELECT groupID FROM wcf".WCF_N."_user_rank WHERE rankID = ".$user->rankID.")" : implode(',', $user->getGroupIDs())).") 
					AND neededPoints <= ".$neededPoints."
					AND gender IN (0,".intval($user->gender).")
			ORDER BY	neededPoints DESC, gender DESC";
		$row = WCF::getDB()->getFirstRow($sql);
		if (isset($row['rankID'])) $newRankID = $row['rankID'];
		
		// update user rank and global activity points
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	activityPoints = IF(".$points." > 0 OR activityPoints > ABS(".$points."), activityPoints + ".$points.", 0)
				".($newRankID ? ", rankID = ".$newRankID : "")."
			WHERE	userID = ".$user->userID;
		WCF::getDB()->sendQuery($sql);
		
		// update user session
		Session::resetSessions($user->userID, true, false);
		
		return true;
	}
}
?>