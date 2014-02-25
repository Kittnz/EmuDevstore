<?php
// wbb imports
require_once(WBB_DIR.'lib/data/post/Post.class.php');
require_once(WBB_DIR.'lib/data/user/WBBUser.class.php');

// wcf imports
require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/AttachmentBBCode.class.php');
require_once(WCF_DIR.'lib/data/message/util/KeywordHighlighter.class.php');
require_once(WCF_DIR.'lib/data/message/sidebar/MessageSidebarObject.class.php');

/**
 * Represents a viewable post in the forum.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class ViewablePost extends Post implements MessageSidebarObject {
	protected $user;
	protected $thread;
	protected $signature = null;
	
	/**
	 * Creates a new ViewablePost object.
	 *
	 * @param 	integer 	$postID
	 * @param 	array 		$row		resultset with post data form database
	 * @param 	Thread		$thread		thread of this post
	 */
	public function __construct($postID, $row = null, $thread = null) {
		parent::__construct($postID, $row);
		$this->thread = $thread;
	}
	
	/**
	 * @see DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		$this->user = new WBBUser(null, $data);
	}
	
	/**
	 * Returns the text of this post.
	 *
	 * @return 	string		the text of this post
	 */
	public function getFormattedMessage() {
		// return message cache
		if ($this->messageCache) {
			return KeywordHighlighter::doHighlight($this->messageCache);
		}
		
		// parse message
		$parser = MessageParser::getInstance();
		$parser->setOutputType('text/html');
		AttachmentBBCode::setMessageID($this->postID);
		return $parser->parse($this->message, $this->enableSmilies, $this->enableHtml, $this->enableBBCodes, !$this->messagePreview);
	}
	
	/**
	 * Returns true, if the active user doesn't have read this post.
	 * 
	 * @return	boolean		true, if the active user doesn't have read this post
	 */
	public function isNew() {
		if ($this->thread == null) return false;
		return ($this->time > $this->thread->lastVisitTime);
	}
	
	/**
	 * @see Post::canEditPost()
	 */
	public function canEditPost($board, $thread) {
		if ($this->thread != null) $thread = $this->thread;
		return parent::canEditPost($board, $thread);
	}
	
	/**
	 * Returns the filename of the post icon.
	 *
	 * @return	string		filename of the post icon
	 */
	public function getIconName() {
		// deleted
		if ($this->isDeleted) return 'postTrash';
		
		$icon = 'post';
		
		// new
		if ($this->isNew()) $icon .= 'New';
		
		// closed
		if ($this->isClosed) $icon .= 'Closed';
		
		return $icon;
	}
	
	/**
	 * Returns the signature of the message author.
	 * 
	 * @return	string
	 */
	public function getSignature() {
		if ($this->signature === null) {
			$this->signature = '';
			
			if ($this->showSignature && (!WCF::getUser()->userID || WCF::getUser()->showSignature) && !$this->user->disableSignature) {
				if ($this->user->signatureCache) $this->signature = $this->user->signatureCache;
				else if ($this->user->signature) {
					$parser = MessageParser::getInstance();
					$parser->setOutputType('text/html');
					$this->signature = $parser->parse($this->user->signature, $this->user->enableSignatureSmilies, $this->user->enableSignatureHtml, $this->user->enableSignatureBBCodes, false);
				}
			}
		}
		
		return $this->signature;
	}
	
	// MessageSidebarObject implementation
	/**
	 * @see MessageSidebarObject::getUser()
	 */
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * @see MessageSidebarObject::getMessageID()
	 */
	public function getMessageID() {
		return $this->postID;
	}
	
	/**
	 * @see MessageSidebarObject::getMessageType()
	 */
	public function getMessageType() {
		return 'post';
	}
}
?>