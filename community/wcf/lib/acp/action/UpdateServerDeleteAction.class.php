<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/acp/package/update/UpdateServerEditor.class.php');

/**
 * Deletes an update server.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class UpdateServerDeleteAction extends AbstractAction {
	public $packageUpdateServerID = 0;
	public $updateServer;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['packageUpdateServerID'])) $this->packageUpdateServerID = intval($_REQUEST['packageUpdateServerID']);
		$this->updateServer = new UpdateServerEditor($this->packageUpdateServerID);
		if (!$this->updateServer->packageUpdateServerID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// check permission.
		WCF::getUser()->checkPermission('admin.system.package.canEditServer');

		// check master password
		WCFACP::checkMasterPassword();
		
		// delete server
		$this->updateServer->delete();
		$this->executed();
		
		// redirect to the view page.
		HeaderUtil::redirect('index.php?page=UpdateServerList&deletedPackageUpdateServerID='.$this->packageUpdateServerID."&packageID=".PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
	}
}
?>