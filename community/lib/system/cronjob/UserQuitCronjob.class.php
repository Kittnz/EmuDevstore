<?php
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');

/**
 * Deletes quited user accounts.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.cronjob
 * @category 	Burning Board
 */
class UserQuitCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		// get user ids
		$sql = "SELECT	userID
			FROM	wcf".WCF_N."_user
			WHERE	quitStarted > 0
				AND quitStarted < ".(TIME_NOW - 7 * 24 * 3600);
		$result = WCF::getDB()->sendQuery($sql);
		$userIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$userIDs[] = $row['userID'];
		}
		
		// delete users
		UserEditor::deleteUsers($userIDs);
	}
}
?>