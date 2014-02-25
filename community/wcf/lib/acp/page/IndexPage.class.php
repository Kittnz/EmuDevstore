<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/acp/package/PackageInstallationQueue.class.php');

/**
 * Shows the welcome page in admin control panel.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class IndexPage extends AbstractPage {
	public $templateName = 'index';
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		$wcfPackageID = WCFACP::getWcfPackageID();
		// check package installation queue
		if ($wcfPackageID == 0) {
			PackageInstallationQueue::checkPackageInstallationQueue();
		}
		
		if (WCFACP::getWcfPackageID() == PACKAGE_ID) {
			$packages = WCF::getCache()->get('packages');
			foreach ($packages as $packageID => $package) {
				break;
			}
			
			if (isset($packageID) && $packageID != PACKAGE_ID) {
				HeaderUtil::redirect('../'.$packages[$packageID]['packageDir'].'acp/index.php'.SID_ARG_1ST, false);
				exit;
			}
		}
		
		// show page
		parent::show();
	}
}
?>