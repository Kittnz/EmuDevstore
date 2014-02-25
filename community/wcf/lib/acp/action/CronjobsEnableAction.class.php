<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/AbstractCronjobsAction.class.php');

/**
 * Enables a cronjob.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class CronjobsEnableAction extends AbstractCronjobsAction {
	public $enable = true;

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		if (!$this->cronjob->canBeDisabled) {
			throw new IllegalLinkException();
		}
		WCF::getUser()->checkPermission('admin.system.cronjobs.canEnableDisableCronjob');
		
		// enable/disbale cronjob
		$this->cronjob->enable($this->enable);
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?page=CronjobsList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>