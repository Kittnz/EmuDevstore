<?php
require_once(WBB_DIR.'lib/action/AbstractThreadAction.class.php');

/**
 * Marks a thread as read.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	action
 * @category 	Burning Board
 */
class ThreadMarkAsReadAction extends AbstractThreadAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		if ($this->thread->isNew()) {
			WCF::getUser()->setThreadVisitTime($this->threadID, TIME_NOW);
			if ($this->thread->subscribed) {
				WCF::getSession()->unregister('lastSubscriptionsStatusUpdateTime');
			}
		}
		$this->executed();
	}
}
?>