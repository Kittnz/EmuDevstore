<?php
// wcf imports
require_once(WCF_DIR.'lib/action/Action.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * This class provides default implementations for the Action interface.
 * This includes the call of the default event listeners for an action: readParameters and execute.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category 	Community Framework
 */
abstract class AbstractAction implements Action {
	/**
	 * Creates a new AbstractAction object.
	 * Calls the methods readParameters() and execute() automatically.
	 */
	public function __construct() {
		// call default methods
		$this->readParameters();
		$this->execute();
	}
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		// call readParameters event
		EventHandler::fireAction($this, 'readParameters');
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// call execute event
		EventHandler::fireAction($this, 'execute');
	}
	
	/**
	 * Calls the 'executed' event after the successful execution of this action.
	 * This functions won't called automatically. You must do this manually, if you inherit AbstractAction.
	 */
	protected function executed() {
		EventHandler::fireAction($this, 'executed');
	}
}
?>