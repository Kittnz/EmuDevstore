<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/AbstractCronjobsAction.class.php');

/**
 * Executes a cronjob manually.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class CronjobExecuteAction extends AbstractCronjobsAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.system.cronjobs.canEditCronjob');
		
		// execute cronjob
		$this->cronjob->execute();
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?page=CronjobsList&successfulExecuted=1&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>