<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/UpdateServerAddForm.class.php');

/**
 * Shows the server edit form.
 *
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UpdateServerEditForm extends UpdateServerAddForm {
	public $activeMenuItem = 'wcf.acp.menu.link.package.server';
	
	public $updateServer;
	
	/**
	 * @see Page::readParameters()
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
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save server
		$this->updateServer->update($this->server, $this->htUsername, $this->htPassword);
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->server = $this->updateServer->server;
			$this->htUsername = $this->updateServer->htUsername;
			$this->htPassword = $this->updateServer->htPassword;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
			
		WCF::getTPL()->assign(array(
			'packageUpdateServerID' => $this->packageUpdateServerID,
			'packageUpdateServer' => $this->updateServer,
			'action' => 'edit'
		));
	}
}
?>