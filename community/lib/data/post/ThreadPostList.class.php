<?php
require_once(WBB_DIR.'lib/data/post/DependentPostList.class.php');

/**
 * ThreadPostList provides extended functions for displaying a list of posts.
 * Including user profile information like avatar, number user posts, special profile fields etc.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class ThreadPostList extends DependentPostList {
	// data
	public $pollIDs = '';
	public $polls;
	public $maxPostTime = 0;
	public $userData = array();
	public $userOptions;
	
	/**
	 * @see PostList::initDefaultSQL();
	 */
	protected function initDefaultSQL() {
		parent::initDefaultSQL();

		// default selects / joins
		$this->sqlSelects = "user_option.*, wbb_user.*, user.*, rank.*, IFNULL(user.username, post.username) AS username,";
		$this->sqlJoins = "	LEFT JOIN 	wcf".WCF_N."_user user
					ON 		(user.userID = post.userID)
					LEFT JOIN 	wbb".WBB_N."_user wbb_user 
					ON 		(wbb_user.userID = post.userID)
					LEFT JOIN 	wcf".WCF_N."_user_option_value user_option
					ON		(user_option.userID = post.userID)
					LEFT JOIN 	wcf".WCF_N."_user_rank rank
					ON		(rank.rankID = user.rankID)";
		
		if (MESSAGE_SIDEBAR_ENABLE_AVATAR) {
			$this->sqlSelects .= 'avatar.avatarID, avatar.avatarExtension, avatar.width, avatar.height,';
			$this->sqlJoins .= ' LEFT JOIN wcf'.WCF_N.'_avatar avatar ON (avatar.avatarID = user.avatarID) ';
		}
	}
	
	/**
	 * @see PostList::readPostIDs()
	 */
	protected function readPostIDs() {
		$sql = "SELECT		postID, attachments, pollID
			FROM		wbb".WBB_N."_post post
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : "")."
			ORDER BY	".$this->sqlOrderBy;
		$result = WCF::getDB()->sendQuery($sql, $this->limit, $this->offset);
		while ($row = WCF::getDB()->fetchArray($result)) {
			// post id
			if (!empty($this->postIDs)) $this->postIDs .= ',';
			$this->postIDs .= $row['postID'];
			
			// attachments
			if ($row['attachments'] != 0) {
				$this->attachmentPostIDArray[] = $row['postID'];
			}
			
			// polls
			if ($row['pollID'] != 0) {
				if ($this->pollIDs != '') $this->pollIDs .= ',';
				$this->pollIDs .= $row['pollID'];
			}
		}
		
		$this->readAttachments();
		$this->readPolls();
	}
	
	/**
	 * @see PostList::readPosts()
	 */
	public function readPosts() {
		parent::readPosts();
		
		// calculate max post time
		foreach ($this->posts as $post) {
			if ($post->time > $this->maxPostTime) $this->maxPostTime = $post->time;
		}
	}
	
	/**
	 * Gets a list of polls.
	 */
	protected function readPolls() {
		if (MODULE_POLL == 1 && $this->pollIDs != '') {
			require_once(WCF_DIR.'lib/data/message/poll/Polls.class.php');
			$this->polls = new Polls($this->pollIDs, $this->board->getPermission('canVotePoll'), 'index.php?page=Thread');
		}
	}
}
?>