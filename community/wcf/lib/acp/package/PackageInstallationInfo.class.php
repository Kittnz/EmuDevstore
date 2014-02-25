<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/PackageInstallation.class.php');

/**
 * PackageInstallationInfo is used to calculate the progress bar of a package installation.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package
 * @category 	Community Framework
 */
class PackageInstallationInfo extends PackageInstallation {
	/**
	 * Creates a new PackageInstallationInfo object.
	 * 
	 * @param 	integer		$queueID
	 */
	public function __construct($queueID) {
		$this->queueID = $queueID;
		$this->getInstallationInfo();
	}
	
	/**
	 * @see PackageInstallationQueue::getInstallationInfo()
	 */
	protected function getInstallationInfo() {
		$info = PackageInstallationQueue::getInstallationInfo();
		$this->package = $info['packageID'] ? new Package(null, $info) : null;
		$this->packageArchive = new PackageArchive($info['archive'], $this->package);
		$this->packageArchive->openArchive();
	}
}
?>