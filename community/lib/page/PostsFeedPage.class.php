<?php
// wbb imports
require_once(WBB_DIR.'lib/data/post/FeedPost.class.php');
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractFeedPage.class.php');

/**
 * Prints a list of posts as a rss or an atom feed.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class PostsFeedPage extends AbstractFeedPage {
	/**
	 * list of thread ids
	 * 
	 * @var	array<integer>
	 */
	public $threadIDArray = array();
	
	/**
	 * list of posts
	 * 
	 * @var	array<FeedPost>
	 */
	public $posts = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['threadID'])) $this->threadIDArray = ArrayUtil::toIntegerArray(explode(',', $_REQUEST['threadID']));
		if (!count($this->threadIDArray)) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Gets the posts for the feed.
	 */
	protected function readPosts() {
		// accessible boards
		$accessibleBoardIDArray = Board::getAccessibleBoardIDArray(array('canViewBoard', 'canEnterBoard', 'canReadThread'));
		if (!count($accessibleBoardIDArray)) {
			throw new PermissionDeniedException();
		}
		
		// get posts
		$attachmentPostIDArray = array();
		$sql = "SELECT		post.*
			FROM		wbb".WBB_N."_post post
			WHERE		post.threadID IN (".implode(',', $this->threadIDArray).")
					AND post.threadID IN (SELECT threadID FROM wbb".WBB_N."_thread WHERE boardID IN (".implode(',', $accessibleBoardIDArray)."))
					AND post.isDeleted = 0
					AND post.isDisabled = 0
					".($this->hours ? "AND post.time > ".(TIME_NOW - $this->hours * 3600) : '')."
			ORDER BY	post.time DESC";
		$result = WCF::getDB()->sendQuery($sql, $this->limit);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->posts[] = new FeedPost(null, $row);
			
			// attachments
			if ($row['attachments'] != 0) {
				$attachmentPostIDArray[] = $row['postID'];
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
		
		$this->readPosts();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'posts' => $this->posts
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();
		
		// send content
		WCF::getTPL()->display(($this->format == 'atom' ? 'feedAtomPosts' : 'feedRss2Posts'), false);
	}
}
?>