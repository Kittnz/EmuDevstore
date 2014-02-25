<?php
// wcf imports
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');

/**
 * Deletes old entries from cronjob log.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
class CleanUpCronjobLogCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		$sql = "DELETE FROM	wcf".WCF_N."_cronjobs_log
			WHERE		execTime < ".(TIME_NOW - (86400 * 7));
		WCF::getDB()->sendQuery($sql);
	}
}
?>