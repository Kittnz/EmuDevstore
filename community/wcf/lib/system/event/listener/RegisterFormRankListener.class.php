<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');

/**
 * Handles the user rank during user registration.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	system.event.listener
 * @category 	Community Framework
 */
class RegisterFormRankListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_USER_RANK == 1) {
			if ($eventName == 'validate') {
				$sql = "SELECT		rankID
					FROM		wcf".WCF_N."_user_rank
					WHERE		groupID IN (0,".implode(',', Group::getGroupIdsByType(array(Group::EVERYONE, Group::USERS))).")
							AND neededPoints = 0
							AND gender = 0";
				$row = WCF::getDB()->getFirstRow($sql);
				if (isset($row['rankID'])) {
					$eventObj->additionalFields['rankID'] = $row['rankID'];
				}
			}
		}
	}
}
?>