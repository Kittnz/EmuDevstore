<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Checks the permissions for viewing the poll overview page.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class PollOverviewPagePostsListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventObj->poll->messageType == 'post') {
			// check permissions
			require_once(WBB_DIR.'lib/data/post/Post.class.php');
			$post = new Post($eventObj->poll->messageID);
			if (!$post->postID) {
				throw new IllegalLinkException();
			}
			require_once(WBB_DIR.'lib/data/thread/Thread.class.php');
			$thread = new Thread($post->threadID);
			$thread->enter();
			require_once(WBB_DIR.'lib/data/board/Board.class.php');
			$board = new Board($thread->boardID);
			$eventObj->canVotePoll = $board->getPermission('canVotePoll');
			
			// plug in breadcrumbs
			WCF::getTPL()->assign(array(
				'board' => $board,
				'thread' => $thread,
				'showThread' => true
			));
			WCF::getTPL()->append('specialBreadCrumbs', WCF::getTPL()->fetch('navigation'));
			
			// get other polls from this thread
			if ($thread->polls > 1) {
				require_once(WCF_DIR.'lib/data/message/poll/Poll.class.php');
				$polls = array();
				$sql = "SELECT 		poll_vote.pollID AS voted,
							poll_vote.isChangeable,
							poll.*
					FROM 		wcf".WCF_N."_poll poll
					LEFT JOIN 	wcf".WCF_N."_poll_vote poll_vote
					ON 		(poll_vote.pollID = poll.pollID
							".(!WCF::getUser()->userID ? "AND poll_vote.ipAddress = '".escapeString(WCF::getSession()->ipAddress)."'" : '')."
							AND poll_vote.userID = ".WCF::getUser()->userID.")
					WHERE 		poll.pollID IN (
								SELECT	pollID
								FROM	wbb".WBB_N."_post
								WHERE	threadID = ".$thread->threadID."
									AND isDeleted = 0
									AND isDisabled = 0
									AND pollID <> 0
							)
					ORDER BY	poll.question";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$polls[] = new Poll(null, $row, $eventObj->canVotePoll);
				}
				
				if (count($polls) > 1) {
					WCF::getTPL()->assign(array(
						'polls' => $polls,
						'pollID' => $eventObj->pollID
					));
					WCF::getTPL()->append('additionalSidebarContent', WCF::getTPL()->fetch('pollOverviewSidebar'));
				}
			}
		}
	}
}
?>