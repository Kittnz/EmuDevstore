<?php
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');

/**
 * Updates the last activity timestamp in the user table.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.cronjob
 * @category 	Burning Board
 */
class LastActivityCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		// update global last activity
		$sql = "UPDATE	wcf".WCF_N."_user user_table,
				wcf".WCF_N."_session session
			SET	user_table.lastActivityTime = session.lastActivityTime
			WHERE	user_table.userID = session.userID
				AND session.userID <> 0";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// update board last activity
		$sql = "UPDATE	wbb".WBB_N."_user user_table,
				wcf".WCF_N."_session session
			SET	user_table.boardLastActivityTime = session.lastActivityTime
			WHERE	user_table.userID = session.userID
				AND session.userID <> 0
				AND session.packageID = ".PACKAGE_ID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
}
?>