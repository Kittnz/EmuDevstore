<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/rank/UserRank.class.php');

/**
 * Provides functions to create and edit the data of a user ran.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.rank
 * @category 	Community Framework
 */
class UserRankEditor extends UserRank {
	/**
	 * Creates a new rank.
	 * 
	 * @return	integer		rank id
	 */
	public static function create($title, $image = '', $groupID = 0, $neededPoints = 0, $gender = 0, $repeatImage = 1) {
		$sql = "INSERT INTO	wcf".WCF_N."_user_rank
					(rankTitle, rankImage, groupID, neededPoints, gender, repeatImage)
			VALUES		('".escapeString($title)."',
					'".escapeString($image)."',
					".$groupID.",
					".$neededPoints.",
					".$gender.",
					".$repeatImage.")";
		WCF::getDB()->sendQuery($sql);
		
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Updates the data of this rank.
	 */
	public function update($title, $image = '', $groupID = 0, $neededPoints = 0, $gender = 0, $repeatImage = 1)  {
		$sql = "UPDATE	wcf".WCF_N."_user_rank
			SET	rankTitle = '".escapeString($title)."',
				rankImage = '".escapeString($image)."',
				groupID = ".$groupID.",
				neededPoints = ".$neededPoints.",
				gender = ".$gender.",
				repeatImage = ".$repeatImage."
			WHERE	rankID = ".$this->rankID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes this rank.
	 */
	public function delete() {
		// delete rank
		$sql = "DELETE FROM	wcf".WCF_N."_user_rank
			WHERE		rankID = ".$this->rankID;
		WCF::getDB()->sendQuery($sql);
		
		// update users
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	rankID = 0
			WHERE	rankID = ".$this->rankID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>