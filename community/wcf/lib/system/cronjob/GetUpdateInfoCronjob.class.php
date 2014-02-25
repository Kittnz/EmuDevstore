<?php
// wcf imports
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');

/**
 * Gets update package information..
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
class GetUpdateInfoCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		require_once(WCF_DIR.'lib/acp/package/update/PackageUpdate.class.php');
		PackageUpdate::refreshPackageDatabaseAutomatically();
	}
}
?>