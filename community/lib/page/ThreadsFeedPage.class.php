<?php
// wbb imports
require_once(WBB_DIR.'lib/data/thread/FeedThread.class.php');
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractFeedPage.class.php');

/**
 * Prints a list of threads as a rss or an atom feed.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class ThreadsFeedPage extends AbstractFeedPage {
	/**
	 * list of board ids
	 * 
	 * @var	array<integer>
	 */
	public $boardIDArray = array();
	
	/**
	 * list of threads
	 * 
	 * @var	array<FeedThread>
	 */
	public $threads = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['boardID'])) $this->boardIDArray = ArrayUtil::toIntegerArray(explode(',', $_REQUEST['boardID']));
	}
	
	/**
	 * Gets the threads for the feed.
	 */
	protected function readThreads() {
		$boardIDArray = $this->boardIDArray;
		// include subboards
		if (count($boardIDArray)) {
			$boardIDArray = array_merge($boardIDArray, Board::getSubBoardIDArray($boardIDArray));
		}
		
		// accessible boards
		$accessibleBoardIDArray = Board::getAccessibleBoardIDArray(array('canViewBoard', 'canEnterBoard', 'canReadThread'));
		if (count($boardIDArray)) {
			$boardIDArray = array_intersect($boardIDArray, $accessibleBoardIDArray);
		}
		else {
			$boardIDArray = $accessibleBoardIDArray;
			foreach ($boardIDArray as $key => $boardID) {
				if (WCF::getUser()->isIgnoredBoard($boardID)) {
					unset($boardIDArray[$key]);
				}
			}
		}
		
		// get threads
		$attachmentPostIDArray = array();
		if (count($boardIDArray)) {
			$sql = "SELECT		post.*, thread.*, post.attachments
				FROM		wbb".WBB_N."_thread thread
				LEFT JOIN	wbb".WBB_N."_post post
				ON		(post.postID = thread.firstPostID)
				WHERE		thread.boardID IN (".implode(',', $boardIDArray).")
						AND thread.isDeleted = 0
						AND thread.isDisabled = 0
						AND thread.movedThreadID = 0
						AND thread.time > ".($this->hours ? (TIME_NOW - $this->hours * 3600) : (TIME_NOW - 30 * 86400))."
				ORDER BY	thread.time DESC";
			$result = WCF::getDB()->sendQuery($sql, $this->limit);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->threads[] = new FeedThread($row);
				
				// attachments
				if ($row['attachments'] != 0) {
					$attachmentPostIDArray[] = $row['postID'];
				}
			}
		}
		
		// read attachments
		if (MODULE_ATTACHMENT == 1 && count($attachmentPostIDArray) > 0 && (WCF::getUser()->getPermission('user.board.canViewAttachmentPreview') || WCF::getUser()->getPermission('user.board.canDownloadAttachment'))) {
			require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentList.class.php');
			$attachmentList = new MessageAttachmentList($attachmentPostIDArray, 'post');
			$attachmentList->readObjects();
			$attachments = $attachmentList->getSortedAttachments();
			
			// set embedded attachments
			require_once(WCF_DIR.'lib/data/message/bbcode/AttachmentBBCode.class.php');
			AttachmentBBCode::setAttachments($attachments);
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readThreads();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'threads' => $this->threads
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();
		
		// send content
		WCF::getTPL()->display(($this->format == 'atom' ? 'feedAtomThreads' : 'feedRss2Threads'), false);
	}
}
?>