<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Handles all request on the package.php script 
 * and executes the requested action.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class PackagePage extends AbstractPage {
	const DO_NOT_LOG = true;
	public $queueID = 0;
	public $parentQueueID = 0;
	public $processNo = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['queueID'])) $this->queueID = intval($_REQUEST['queueID']);
		if (isset($_REQUEST['parentQueueID'])) $this->parentQueueID = intval($_REQUEST['parentQueueID']);
		if (isset($_REQUEST['processNo'])) $this->processNo = intval($_REQUEST['processNo']);
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();

		// check master password
		WCFACP::checkMasterPassword();
		
		switch ($this->action) {
			case 'install':
			case 'update':
				if ($this->action == 'install') WCF::getUser()->checkPermission('admin.system.package.canInstallPackage');
				else WCF::getUser()->checkPermission('admin.system.package.canUpdatePackage');
				
				require_once(WCF_DIR.'lib/acp/package/PackageInstallation.class.php');
				new PackageInstallation($this->queueID);
				break;
				
			case 'rollback':
				WCF::getUser()->checkPermission('admin.system.package.canInstallPackage');
				require_once(WCF_DIR.'lib/acp/package/PackageInstallationRollback.class.php');
				new PackageInstallationRollback($this->queueID);
				break;
				
			case 'uninstall':
				WCF::getUser()->checkPermission('admin.system.package.canUninstallPackage');
				require_once(WCF_DIR.'lib/acp/package/PackageUninstallation.class.php');
				new PackageUninstallation($this->queueID);
				break;
			
			case 'openQueue':
				require_once(WCF_DIR.'lib/acp/package/PackageInstallationQueue.class.php');
				PackageInstallationQueue::openQueue($this->parentQueueID, $this->processNo);
				break;
				
			case 'startUninstall':
				WCF::getUser()->checkPermission('admin.system.package.canUninstallPackage');
				require_once(WCF_DIR.'lib/acp/package/PackageUninstallation.class.php');
				PackageUninstallation::checkDependencies();
				break;
		}
	}
}
?>