<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/AbstractCronjobsAction.class.php');

/**
 * Deletes a cronjob.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class CronjobsDeleteAction extends AbstractCronjobsAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		if (!$this->cronjob->canBeEdited) {
			throw new IllegalLinkException();
		}
		WCF::getUser()->checkPermission('admin.system.cronjobs.canDeleteCronjob');
		
		// delete cronjob
		$this->cronjob->delete();
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?page=CronjobsList&deleteJob='.$this->cronjobID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>