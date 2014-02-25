<?php
// wcf imports
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');

/**
 * Does a cleanup of the saved user profile visitors.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
class ProfileVisitorCleanupCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		$sql = "DELETE FROM	wcf".WCF_N."_user_profile_visitor
			WHERE		time < ".(TIME_NOW - 86400 * 14);
		WCF::getDB()->registerShutdownUpdate($sql);
	}
}
?>