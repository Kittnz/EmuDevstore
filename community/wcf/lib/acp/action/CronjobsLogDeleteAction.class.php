<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/cronjobs/CronjobEditor.class.php');

/**
 * Deletes the cronjobs log.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class CronjobsLogDeleteAction extends AbstractAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.system.cronjobs.canDeleteCronjob');
		
		// enable/disbale cronjob
		CronjobEditor::clearLog();
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?page=CronjobsShowLog&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>