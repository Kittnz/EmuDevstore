<?php
// wbb imports
require_once(WBB_DIR.'lib/data/thread/SubscribedThread.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractSecurePage.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');

/**
 * Handles the actions on the subscriptions.php page.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class SubscriptionsPage extends AbstractSecurePage {
	public $threadID = 0;
	public $boardID = 0;
	public $url = '';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['threadID'])) $this->threadID = ArrayUtil::toIntegerArray($_REQUEST['threadID']);
		if (isset($_REQUEST['boardID'])) $this->boardID = intval($_REQUEST['boardID']);
		if (isset($_REQUEST['url'])) $this->url = $_REQUEST['url'];
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		parent::show();
		
		switch ($this->action) {
			case 'mark':
			case 'unmark':
				if (!is_array($this->threadID)) $this->threadID = array($this->threadID);
				foreach ($this->threadID as $threadID) {
					$thread = new SubscribedThread($threadID);
					if ($thread->threadID) $thread->{$this->action}();
				}
				break;
			
			case 'unmarkAll':
				SubscribedThread::unmarkAll();
				break;
			
			case 'unsubscribeThread':
				$thread = new SubscribedThread($this->threadID);
				if ($thread->threadID) $thread->unsubscribe();
				$thread->unmark();
				
				if (!empty($this->url)) HeaderUtil::redirect($this->url);
				else HeaderUtil::redirect('index.php?page=SubscriptionsList'.SID_ARG_2ND_NOT_ENCODED);
				exit;
			
			case 'unsubscribeMarkedThreads':
				SubscribedThread::unsubscribeMarked();
				SubscribedThread::unmarkAll();
				
				if (!empty($this->url)) HeaderUtil::redirect($this->url);
				else HeaderUtil::redirect('index.php?page=SubscriptionsList'.SID_ARG_2ND_NOT_ENCODED);
				exit;
				
			case 'unsubscribeThreads':
				SubscribedThread::unsubscribeAll();
				SubscribedThread::unmarkAll();
				
				if (!empty($this->url)) HeaderUtil::redirect($this->url);
				else HeaderUtil::redirect('index.php?page=SubscriptionsList'.SID_ARG_2ND_NOT_ENCODED);
				exit;
				
			case 'unsubscribeBoard':
				// remove subscriptions
				$sql = "DELETE FROM	wbb".WBB_N."_board_subscription
					WHERE		userID = ".WCF::getUser()->userID."
							AND boardID = ".$this->boardID;
				WCF::getDB()->sendQuery($sql);
				
				// reset user data
				WCF::getSession()->resetUserData();
				WCF::getSession()->unregister('hasSubscriptions');
				
				HeaderUtil::redirect('index.php?page=SubscriptionsList'.SID_ARG_2ND_NOT_ENCODED);
				exit;
			
			case 'unsubscribeBoards':
				// remove subscriptions
				$sql = "DELETE FROM	wbb".WBB_N."_board_subscription
					WHERE		userID = ".WCF::getUser()->userID;
				WCF::getDB()->sendQuery($sql);
				
				// reset user data
				WCF::getSession()->resetUserData();
				WCF::getSession()->unregister('hasSubscriptions');
				
				HeaderUtil::redirect('index.php?page=SubscriptionsList'.SID_ARG_2ND_NOT_ENCODED);
				exit;
				
			default:
				throw new IllegalLinkException();
		}
	}
}
?>