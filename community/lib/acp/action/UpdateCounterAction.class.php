<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/WorkerAction.class.php');

/**
 * Provides default implementations for a counter update.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
abstract class UpdateCounterAction extends WorkerAction {
	public $limit = 1000;
	
	/**
	 * Creates a new UpdateCounterAction object.
	 */
	public function __construct() {
		WCF::getUser()->checkPermission('admin.maintenance.canUpdateCounters');
		parent::__construct();
	}
	
	/**
	 * Shows the worker finish page.
	 */
	protected function finish($title = '', $url = '') {
		parent::finish('wcf.acp.worker.progress.finish', 'index.php?form=UpdateCounters&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
	}
}
?>