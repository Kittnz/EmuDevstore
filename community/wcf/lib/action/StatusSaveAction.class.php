<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/system/session/UserSession.class.php');

/**
 * Saves the status of a specific page element. e.g. a closable list.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category 	Community Framework
 */
class StatusSaveAction extends AbstractAction {
	public $name = '';
	public $status = '';
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['status'])) $this->status = $_POST['status'];
		if (isset($_POST['name'])) $this->name = $_POST['name'];
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// call execute event
		parent::execute();
		
		// save status
		UserSession::saveStatus($this->name, $this->status);
		
		// call executed event
		$this->executed();
	}
}
?>