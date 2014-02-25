<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/cronjobs/CronjobsExec.class.php');

/**
 * Starts the execution of the active cronjobs.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	action
 * @category 	Community Framework
 */
class CronjobsExecAction extends AbstractAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// avoid session update
		WCF::getSession()->disableUpdate();
		
		// execute cronjobs
		new CronjobsExec();
	}
}
?>