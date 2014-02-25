<?php
// wcf imports
require_once(WCF_DIR.'lib/system/session/Session.class.php');

// wbb imports
require_once(WBB_DIR.'lib/system/cronjob/LastActivityCronjob.class.php');

/**
 * Cronjob for a hourly system cleanup.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.cronjob
 * @category 	Burning Board
 */
class CleanupCronjob extends LastActivityCronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		parent::execute($data);
		
		// delete old sessions
		Session::deleteExpiredSessions((TIME_NOW - SESSION_TIMEOUT));
		
		// delete old captchas
		$sql = "DELETE FROM	wcf".WCF_N."_captcha
			WHERE		captchaDate < ".(TIME_NOW - 3600);
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// delete post cache
		$sql = "DELETE FROM	wbb".WBB_N."_post_cache
			WHERE		threadID IN (
						SELECT	threadID
						FROM	wbb".WBB_N."_thread
						WHERE	lastPostTime < ".(TIME_NOW - 86400 * 7)."
					)";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// delete searches
		$sql = "DELETE FROM	wcf".WCF_N."_search
			WHERE		searchDate < ".(TIME_NOW - 7200);
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// delete orphaned attachments
		$sql = "SELECT	attachmentID
			FROM	wcf".WCF_N."_attachment
			WHERE	containerID = 0
				AND uploadTime < ".(TIME_NOW - 43200);
		$result = WCF::getDB()->sendQuery($sql);
		if (WCF::getDB()->countRows($result) > 0) {
			require_once(WCF_DIR.'lib/data/message/attachment/AttachmentsEditor.class.php');
			$attachmentIDs = '';
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!empty($attachmentIDs)) $attachmentIDs .= ',';
				$attachmentIDs .= $row['attachmentID'];
				
				// delete files
				AttachmentsEditor::deleteFile($row['attachmentID']);
			}
			
			if (!empty($attachmentIDs)) {
				$sql = "DELETE FROM	wcf".WCF_N."_attachment
					WHERE		attachmentID IN (".$attachmentIDs.")";
				WCF::getDB()->registerShutdownUpdate($sql);
			}
		}
		
		// delete post / pm hashes
		$sql = "DELETE FROM	wbb".WBB_N."_post_hash
			WHERE		time < ".(TIME_NOW - 3600);
		WCF::getDB()->registerShutdownUpdate($sql);
		$sql = "DELETE FROM	wcf".WCF_N."_pm_hash
			WHERE		time < ".(TIME_NOW - 3600);
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// delete bad user data
		$sql = "DELETE FROM	wbb".WBB_N."_user
			WHERE		userID NOT IN (
						SELECT	userID
						FROM	wcf".WCF_N."_user
					)";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// optimize tables to save some memory (mysql only)
		if (WCF::getDB()->getDBType() == 'MySQLDatabase' || WCF::getDB()->getDBType() == 'MySQLiDatabase' || WCF::getDB()->getDBType() == 'MySQLPDODatabase') {
			$sql = "OPTIMIZE TABLE	wcf".WCF_N."_session_data, wcf".WCF_N."_acp_session_data, wcf".WCF_N."_search";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		// clean up last post cache
		if (PROFILE_SHOW_LAST_POSTS) {
			$sql = "SELECT		userID, COUNT(*) AS counter
				FROM		wbb".WBB_N."_user_last_post
				GROUP BY 	userID
				HAVING 		counter > 20";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$sql = "DELETE FROM	wbb".WBB_N."_user_last_post
					WHERE		userID = ".$row['userID']."
					ORDER BY	time
					LIMIT		".($row['counter'] - 20);
				WCF::getDB()->registerShutdownUpdate($sql);
			}
		}
	}
}
?>