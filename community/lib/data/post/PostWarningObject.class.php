<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/infraction/warning/object/WarningObject.class.php');

// wbb imports
require_once(WBB_DIR.'lib/data/post/Post.class.php');

/**
 * 
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class PostWarningObject extends Post implements WarningObject {
	/**
	 * Creates a new PostWarningObject object.
	 *
	 * @see Post::__construct()
	 */
	public function __construct($postID, $row = null) {
		if ($postID !== null) {
			$sql = "SELECT		post.*, thread.topic, thread.boardID
				FROM 		wbb".WBB_N."_post post
				LEFT JOIN	wbb".WBB_N."_thread thread
				ON		(thread.threadID = post.threadID)
				WHERE 		postID = ".$postID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		parent::__construct(null, $row);
	}
	
	/**
	 * @see WarningObject::getTitle()
	 */
	public function getTitle() {
		if ($this->subject) {
			return $this->subject; 
		}
		return $this->topic;
	}
	
	/**
	 * @see WarningObject::getURL()
	 */
	public function getURL() {
		return 'index.php?page=Thread&postID='.$this->postID.'#post'.$this->postID;
	}
}
?>