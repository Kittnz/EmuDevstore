<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/acp/package/update/UpdateServerEditor.class.php');

/**
 * Changes the status of an update server.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class UpdateServerChangeStatusAction extends AbstractAction {
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

		// change status
		$this->updateServer->enable(($this->updateServer->statusUpdate != 1));
		$this->executed();
		
		// redirect to the view page.
		HeaderUtil::redirect('index.php?page=UpdateServerList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
	}
}
?>