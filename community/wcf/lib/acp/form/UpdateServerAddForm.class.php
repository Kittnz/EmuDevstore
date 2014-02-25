<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/acp/package/update/UpdateServerEditor.class.php');

/**
 * Shows the server add form.
 *
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UpdateServerAddForm extends ACPForm {
	public $templateName = 'updateServerAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.package.server.add';
	public $neededPermissions = 'admin.system.package.canEditServer';
	
	public $packageUpdateServerID = 0;
	public $server = '';
	public $htUsername = '';
	public $htPassword = '';
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['server'])) $this->server = StringUtil::trim($_POST['server']);
		if (isset($_POST['htUsername'])) $this->htUsername = $_POST['htUsername'];
		if (isset($_POST['htPassword'])) $this->htPassword = $_POST['htPassword'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->server)) {
			throw new UserInputException('server');
		}
		
		if (!UpdateServer::isValidServerURL($this->server)) {
			throw new UserInputException('server', 'notValid');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save server
		$this->packageUpdateServerID = UpdateServerEditor::create($this->server, $this->htUsername, $this->htPassword);
		$this->saved();
		
		// reset values
		$this->server = $this->htUsername = $this->htPassword = '';
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'server' => $this->server,
			'htUsername' => $this->htUsername,
			'htPassword' => $this->htPassword,
			'action' => 'add'
		));
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
?>