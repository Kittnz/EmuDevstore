<?php
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');

/**
 * Clears failed logins.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.security.login
 * @subpackage	system.cronjob
 * @category 	Community Framework (commercial)
 */
class CleanupFailedLoginCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		$sql = "DELETE FROM	wcf".WCF_N."_user_failed_login
			WHERE		time < ".(TIME_NOW - 86400 * 14);
		WCF::getDB()->sendQuery($sql);
	}
}
?>