<?php
require_once(WBB_DIR.'lib/action/AbstractThreadAction.class.php');

/**
 * Unsubscribes from a thread.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	action
 * @category 	Burning Board
 */
class ThreadUnsubscribeAction extends AbstractThreadAction {
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		$this->thread->unsubscribe();
		$this->thread->updateSubscription();
		if ($this->thread->subscribed) {
			WCF::getSession()->unregister('lastSubscriptionsStatusUpdateTime');
		}
		$this->executed();
	}
}
?>