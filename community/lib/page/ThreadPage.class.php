<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');
require_once(WBB_DIR.'lib/data/post/ViewablePost.class.php');
require_once(WBB_DIR.'lib/data/thread/ViewableThread.class.php');
require_once(WBB_DIR.'lib/data/post/ThreadPostList.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/MultipleLinkPage.class.php');
require_once(WCF_DIR.'lib/data/message/sidebar/MessageSidebarFactory.class.php');

/**
 * This class provides the ability to render a thread page.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class ThreadPage extends MultipleLinkPage {
	// system
	public $templateName = 'thread';
	public $itemsPerPage = THREAD_POSTS_PER_PAGE;
	public $sortOrder = THREAD_DEFAULT_POST_SORT_ORDER; 
	public $enableRating = THREAD_ENABLE_RATING;
	
	// parameters
	public $threadID = 0, $postID = 0;
	public $highlight = '';
	public $markedPosts = 0, $markedThreads = 0, $quotes = 0;
	
	/**
	 * board of this thread.
	 * 
	 * @var	Board
	 */
	public $board = null;
	
	/**
	 * this thread
	 * 
	 * @var	ViewableThread
	 */
	public $thread = null;
	
	/**
	 * list of posts.
	 * 
	 * @var	ThreadPostList 
	 */
	public $postList = null;
	
	/**
	 * list of tags.
	 * 
	 * @var	array<Tag>
	 */
	public $tags = array();
	
	/**
	 * sidebar factory object
	 * 
	 * @var	MessageSidebarFactory
	 */
	public $sidebarFactory = null;
	
	/**
	 * list of similar threads.
	 * 
	 * @var	array<ViewableThread>
	 */
	public $similarThreads = array();
	
	/**
	 * Reads the given parameters.
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['threadID'])) $this->threadID = intval($_REQUEST['threadID']);
		else if (isset($_REQUEST['threadid'])) $this->threadID = intval($_REQUEST['threadid']); // wbb2 style
		if (isset($_REQUEST['messageID'])) $this->postID = intval($_REQUEST['messageID']);
		else if (isset($_REQUEST['postID'])) $this->postID = intval($_REQUEST['postID']);
		else if (isset($_REQUEST['postid'])) $this->postID = intval($_REQUEST['postid']); // wbb2 style
		if (isset($_REQUEST['action'])) $this->action = $_REQUEST['action'];
		if (isset($_REQUEST['highlight'])) $this->highlight = $_REQUEST['highlight'];
		
		// get thread
		$this->thread = new ViewableThread($this->threadID, null, $this->postID);
		$this->threadID = $this->thread->threadID;
		
		// get board
		$this->board = Board::getBoard($this->thread->boardID);
		if ($this->board->postSortOrder) $this->sortOrder = $this->board->postSortOrder;
		if ($this->board->enableRating != -1) $this->enableRating = $this->board->enableRating;
		
		// posts per page
		if ($this->board->postsPerPage) $this->itemsPerPage = $this->board->postsPerPage;
		if (WCF::getUser()->postsPerPage) $this->itemsPerPage = WCF::getUser()->postsPerPage;
		
		// enter thread
		$this->thread->enter($this->board);
		
		// init post list
		$this->postList = new ThreadPostList($this->thread, $this->board);
		$this->postList->sqlOrderBy = 'post.time '.$this->sortOrder;
		
		// handle jump to
		if ($this->action == 'lastPost') $this->goToLastPost();
		if ($this->action == 'firstNew') $this->goToFirstNewPost();
		if ($this->postID) $this->goToPost();
		
		// handle subscriptions
		if (WCF::getUser()->userID) {
			$this->thread->updateSubscription();
			if ($this->thread->subscribed) {
				WCF::getSession()->unregister('lastSubscriptionsStatusUpdateTime');
			}
		}
		
		// handle parameters and special actions
		$this->handleRating();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get users online
		if (MODULE_USERS_ONLINE && THREAD_ENABLE_ONLINE_LIST) {
			$this->renderOnlineList();
		}
		
		// get posts
		$this->postList->offset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->postList->limit = $this->itemsPerPage;
		$this->postList->readPosts();
		$this->readSimilarThreads();
		
		// update thread visit
		if ($this->thread->isNew() && $this->postList->maxPostTime > $this->thread->lastVisitTime) {
			WCF::getUser()->setThreadVisitTime($this->threadID, $this->postList->maxPostTime);
		}
		
		// get marked posts
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedPosts'])) {
			$this->markedPosts = count($sessionVars['markedPosts']);
		}
		if (isset($sessionVars['markedThreads'])) {
			$this->markedThreads = count($sessionVars['markedThreads']);
		}
		if (isset($sessionVars['quotes'][$this->threadID])) {
			foreach ($sessionVars['quotes'][$this->threadID] as $postID => $postQuotes) {
				$this->quotes += count($postQuotes);
			}
		}
		
		// get tags
		if (MODULE_TAGGING && THREAD_ENABLE_TAGS) {
			$this->readTags();
		}
		
		// init sidebars
		$this->sidebarFactory = new MessageSidebarFactory($this);
		foreach ($this->postList->posts as $post) {
			$this->sidebarFactory->create($post);
		}
		$this->sidebarFactory->init();
		
		// update views
		if (!WCF::getSession()->spiderID) {
			$this->updateViews();
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'permissions' => $this->board->getModeratorPermissions(),
			'markedPosts' => $this->markedPosts,
			'markedThreads' => $this->markedThreads,
			'board' => $this->board,
			'thread' => $this->thread,
			'threadID' => $this->threadID,
			'postID' => $this->postID,
			'boardQuickJumpOptions' => Board::getBoardSelect(),
			'similarThreads' => $this->similarThreads,
			'showAvatar' => (!WCF::getUser()->userID || WCF::getUser()->showAvatar),
			'highlight' => $this->highlight,
			'quotes' => $this->quotes,
			'posts' => $this->postList->posts,
			'polls' => $this->postList->polls,
			'sidebarFactory' => $this->sidebarFactory,
			'attachments' => $this->postList->attachments,
			'allowSpidersToIndexThisPage' => true,
			'enableRating' => $this->enableRating,
			'tags' => $this->tags,
			'sortOrder' => $this->sortOrder
		));
		
		if (WCF::getSession()->spiderID) {
			@header('Last-Modified: '.gmdate('D, d M Y H:i:s', $this->thread->lastPostTime).' GMT');
		}
	}
	
	/**
	 * Handles a rating request on this thread.
	 */
	public function handleRating() {
		if (isset($_POST['rating'])) {
			$rating = intval($_POST['rating']);
			
			// rating is disabled
			if (!$this->enableRating) {
				throw new IllegalLinkException();
			}
			
			// user has already rated this thread and the rating is NOT changeable
			if ($this->thread->userRating !== null && !$this->thread->userRating) {
				throw new IllegalLinkException();
			}
			
			// user has no permission to rate this thread
			if (!$this->board->getPermission('canRateThread')) {
				throw new IllegalLinkException();
			}
			
			// illegal rating
			if ($rating < 1 || $rating > 5) {
				throw new IllegalLinkException();
			}
			
			// user has already rated this thread and the rating is changeable
			// change rating
			if ($this->thread->userRating) {
				$sql = "UPDATE 	wbb".WBB_N."_thread_rating
					SET 	rating = ".$rating."
					WHERE 	threadID = ".$this->threadID."
						AND ".(WCF::getUser()->userID ? "userID = ".WCF::getUser()->userID : "ipAddress = '".escapeString(WCF::getSession()->ipAddress)."'");
				WCF::getDB()->registerShutdownUpdate($sql);
				
				$sql = "UPDATE 	wbb".WBB_N."_thread
					SET 	rating = (rating + ".$rating.") - ".$this->thread->userRating."
					WHERE 	threadID = ".$this->threadID;
				WCF::getDB()->registerShutdownUpdate($sql);	
			}
			// insert new rating
			else {
				$sql = "INSERT INTO	wbb".WBB_N."_thread_rating
							(threadID, rating, userID, ipAddress)
					VALUES		(".$this->threadID.",
							".$rating.",
							".WCF::getUser()->userID.",
							'".escapeString(WCF::getSession()->ipAddress)."')";
				WCF::getDB()->registerShutdownUpdate($sql);
				
				$sql = "UPDATE 	wbb".WBB_N."_thread
					SET 	ratings = ratings + 1,
						rating = rating + ".$rating."
					WHERE 	threadID = ".$this->threadID;
				WCF::getDB()->registerShutdownUpdate($sql);
			}
			
			HeaderUtil::redirect('index.php?page=Thread&threadID='.$this->threadID.'&pageNo='.$this->pageNo.SID_ARG_2ND_NOT_ENCODED);
			exit;
		}
	}
	
	/**
	 * Wrapper for UsersOnlineList->renderOnlineList()
	 * @see UsersOnlineList::renderOnlineList()
	 */
	protected function renderOnlineList() {
		require_once(WCF_DIR.'lib/data/user/usersOnline/UsersOnlineList.class.php');
		$usersOnlineList = new UsersOnlineList('session.threadID = ' . $this->threadID);
		$usersOnlineList->renderOnlineList();
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->postList->countPosts();
	}

	/**
	 * Calculates the position of a specific post in this thread.
	 */
	protected function goToPost() {
		$sql = "SELECT	COUNT(*) AS posts
			FROM 	wbb".WBB_N."_post
			WHERE 	threadID = ".$this->threadID."
				".$this->postList->sqlConditionVisible."
				AND time ".($this->sortOrder == 'ASC' ? '<=' : '>=')." ".$this->thread->getPost()->time;
		$result = WCF::getDB()->getFirstRow($sql);
		$this->pageNo = intval(ceil($result['posts'] / $this->itemsPerPage));
	}
	
	/**
	 * Gets the post id of the last post in this thread and forwards the user to this post.
	 */
	protected function goToLastPost() {
		$sql = "SELECT		postID
			FROM 		wbb".WBB_N."_post
			WHERE 		threadID = ".$this->threadID.
					$this->postList->sqlConditionVisible."
			ORDER BY 	time DESC";
		$result = WCF::getDB()->getFirstRow($sql);
		HeaderUtil::redirect('index.php?page=Thread&postID=' . $result['postID'] . SID_ARG_2ND_NOT_ENCODED . '#post' . $result['postID'], true, true);
		exit;
	}
	
	/**
	 * Forwards the user to the first new post in this thread.
	 */
	protected function goToFirstNewPost() {
		$lastVisitTime = intval($this->thread->lastVisitTime);
		$sql = "SELECT		postID
			FROM 		wbb".WBB_N."_post
			WHERE 		threadID = ".$this->threadID.
					$this->postList->sqlConditionVisible."
					AND time > ".$lastVisitTime."
			ORDER BY 	time ASC";
		$result = WCF::getDB()->getFirstRow($sql);
		if (isset($result['postID'])) {
			HeaderUtil::redirect('index.php?page=Thread&postID=' . $result['postID'] . SID_ARG_2ND_NOT_ENCODED . '#post' . $result['postID'], true, true);
			exit;
		}
		else $this->goToLastPost();
	}
	
	/**
	 * Updates the views of this thread.
	 */
	public function updateViews() {
		$sql = "UPDATE	wbb".WBB_N."_thread
			SET 	views = views + 1
			WHERE 	threadID = " . $this->threadID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Reads the similar threads of this thread.
	 */
	protected function readSimilarThreads() {
		if (!THREAD_ENABLE_SIMILAR_THREADS) {
			return;
		}
		
		// get accessible boards
		$boardIDs = Board::getAccessibleBoards(array('canViewBoard', 'canEnterBoard', 'canReadThread'));
		if (empty($boardIDs)) return;
		
		// get similar threads
		$sql = "SELECT 		thread.*, board.title
			FROM 		wbb".WBB_N."_thread_similar similar
			LEFT JOIN	wbb".WBB_N."_thread thread
			ON		(thread.threadID = similar.similarThreadID)
			LEFT JOIN 	wbb".WBB_N."_board board
			ON 		(board.boardID = thread.boardID)
			WHERE 		similar.threadID = ".$this->threadID."
					AND thread.isDeleted = 0
					AND thread.isDisabled = 0
					AND thread.boardID IN (".$boardIDs.")
			ORDER BY 	thread.lastPostTime DESC";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->similarThreads[] = new ViewableThread(null, $row);
		}
	}
	
	/**
	 * Reads the tags of this thread.
	 */
	protected function readTags() {
		$this->tags = $this->thread->getTags(WCF::getSession()->getVisibleLanguageIDArray());
	}
}
?>