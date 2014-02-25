<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');

/**
 * Represents a user in the forum.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.user
 * @category 	Burning Board
 */
class WBBUser extends UserProfile {
	protected $avatar = null;
	
	/**
	 * @see UserProfile::__construct()
	 */
	public function __construct($userID = null, $row = null, $username = null, $email = null) {
		$this->sqlSelects .= 'wbb_user.*,';
		$this->sqlJoins .= ' LEFT JOIN wbb'.WBB_N.'_user wbb_user ON (wbb_user.userID = user.userID) ';
		parent::__construct($userID, $row, $username, $email);
	}
	
	/**
	 * Updates the amount of posts of a user.
	 * 
	 * @param	integer		$userID
	 * @param	integer		$posts
	 */
	public static function updateUserPosts($userID, $posts) {
		$sql = "UPDATE	wbb".WBB_N."_user
			SET	posts = IF(".$posts." > 0 OR posts > ABS(".$posts."), posts + ".$posts.", 0)
			WHERE	userID = ".$userID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
}
?>