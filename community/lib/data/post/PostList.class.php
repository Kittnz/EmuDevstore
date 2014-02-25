<?php
require_once(WBB_DIR.'lib/data/post/ViewablePost.class.php');

/**
 * PostList is a default implementation for displaying a list of posts. 
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class PostList {
	// parameters
	public $limit = 20, $offset = 0;

	// data
	public $posts = array();
	public $postIDs = '';
	public $attachmentPostIDArray = array();
	public $attachmentList = null;
	public $attachments = array();
	public $canViewAttachmentPreview = true;
	public $thread = null;
	
	// sql plugin options
	public $sqlConditions = '';
	public $sqlConditionJoins = '';
	public $sqlOrderBy = 'post.time';
	public $sqlSelects = '';
	public $sqlJoins = '';
	
	/**
	 * Creates a new PostList object.
	 */
	public function __construct() {
		// default sql conditions
		$this->initDefaultSQL();
	}
	
	/**
	 * Fills the sql parameters with default values.
	 */
	protected function initDefaultSQL() {}
	
	/**
	 * Counts posts.
	 * 
	 * @return	integer
	 */
	public function countPosts() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wbb".WBB_N."_post post
			".$this->sqlConditionJoins."
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : "");
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * Gets post ids.
	 */
	protected function readPostIDs() {
		$sql = "SELECT		post.postID, post.attachments
			FROM		wbb".WBB_N."_post post
			".$this->sqlConditionJoins."
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : "")."
			ORDER BY	".$this->sqlOrderBy;
		$result = WCF::getDB()->sendQuery($sql, $this->limit, $this->offset);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($this->postIDs)) $this->postIDs .= ',';
			$this->postIDs .= $row['postID'];
			
			// attachments
			if ($row['attachments'] != 0) {
				$this->attachmentPostIDArray[] = $row['postID'];
			}
		}
		
		$this->readAttachments();
	}
	
	/**
	 * Gets a list of attachments.
	 */
	protected function readAttachments() {
		// read attachments
		if (MODULE_ATTACHMENT == 1 && count($this->attachmentPostIDArray) > 0) {
			require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentList.class.php');
			$this->attachmentList = new MessageAttachmentList($this->attachmentPostIDArray, 'post');
			$this->attachmentList->readObjects();
			$this->attachments = $this->attachmentList->getSortedAttachments($this->canViewAttachmentPreview);
			
			// set embedded attachments
			if ($this->canViewAttachmentPreview) {
				require_once(WCF_DIR.'lib/data/message/bbcode/AttachmentBBCode.class.php');
				AttachmentBBCode::setAttachments($this->attachments);
			}
			
			// remove embedded attachments from list
			if (count($this->attachments) > 0) {
				MessageAttachmentList::removeEmbeddedAttachments($this->attachments);
			}
		}
	}
	
	/**
	 * Reads a list of posts.
	 */
	public function readPosts() {
		// get post ids
		$this->readPostIDs();
		if (empty($this->postIDs)) return false;

		// get posts
		$sql = $this->buildQuery();
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->posts[] = new ViewablePost(null, $row, $this->thread);
		}
	}
	
	/**
	 * Builds the main sql query for selecting posts.
	 * 
	 * @return	string
	 */
	protected function buildQuery() {
		return "SELECT		post.*,
					".$this->sqlSelects."
					post_cache.messageCache,
					post.username
			FROM		wbb".WBB_N."_post post
			LEFT JOIN 	wbb".WBB_N."_post_cache post_cache
			ON 		(post_cache.postID = post.postID)
			".$this->sqlJoins."
			WHERE		post.postID IN (".$this->postIDs.")
			ORDER BY	".$this->sqlOrderBy;
	}
}
?>