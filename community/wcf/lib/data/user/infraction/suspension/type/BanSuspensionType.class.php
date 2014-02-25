<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/infraction/suspension/type/AbstractSuspensionType.class.php');

/**
 * Allows a temporary or permanent ban of a user. 
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.suspension.type
 * @category 	Community Framework (commercial)
 */
class BanSuspensionType extends AbstractSuspensionType {
	/**
	 * @see SuspensionType::apply()
	 */
	public function apply(User $user, UserSuspension $userSuspension, Suspension $suspension) {
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	banned = 1
			WHERE	userID = ".$user->userID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * @see SuspensionType::revoke()
	 */
	public function revoke(User $user, UserSuspension $userSuspension, Suspension $suspension) {
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	banned = 0
			WHERE	userID = ".$user->userID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>