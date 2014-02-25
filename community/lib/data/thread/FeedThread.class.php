<?php
// wbb imports
require_once(WBB_DIR.'lib/data/thread/ViewableThread.class.php');

/**
 * Represents a viewable thread in a rss or an atom feed.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.thread
 * @category 	Burning Board
 */
class FeedThread extends ViewableThread {
	protected $post;
	
	/**
	 * Creates a new FeedThread object.
	 */
	public function __construct($data) {
		parent::__construct(null, $data);
		
		if ($data['postID']) {
			require_once(WBB_DIR.'lib/data/post/ViewablePost.class.php');
			$this->post = new ViewablePost(null, $data);
		}
	}
	
	/**
	 * @see ViewablePost::getFormattedMessage()
	 */
	public function getFormattedMessage() {
		$text = '';
		if ($this->post) $text = $this->post->getFormattedMessage();
		
		// replace relative urls
		$text = preg_replace('~(?<=href="|src=")(?![a-z0-9]+://)(?!mailto:)~i', PAGE_URL.'/', $text);
		
		return StringUtil::escapeCDATA($text);
	}
}
?>