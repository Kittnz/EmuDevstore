<?php
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

/**
 * Marks all boards as read.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	action
 * @category 	Burning Board
 */
class BoardMarkAllAsReadAction extends AbstractSecureAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// set last mark as read time
		WCF::getUser()->setLastMarkAllAsReadTime(TIME_NOW);
		
		// update subscriptions
		if (WCF::getUser()->userID) {
			require_once(WBB_DIR.'lib/data/thread/SubscribedThread.class.php');
			SubscribedThread::clearSubscriptions();
			
			$sql = "UPDATE	wbb".WBB_N."_board_subscription
				SET	emails = 0
				WHERE	userID = ".WCF::getUser()->userID;
			WCF::getDB()->registerShutdownUpdate($sql);
			$sql = "UPDATE	wbb".WBB_N."_thread_subscription
				SET	emails = 0
				WHERE	userID = ".WCF::getUser()->userID;
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		// reset session
		WCF::getSession()->resetUserData();
		WCF::getSession()->unregister('lastSubscriptionsStatusUpdateTime');
		$this->executed();
		
		if (empty($_REQUEST['ajax'])) HeaderUtil::redirect('index.php'.SID_ARG_1ST);
		exit;
	}
}
?>