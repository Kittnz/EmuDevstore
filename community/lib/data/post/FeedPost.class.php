<?php
require_once(WBB_DIR.'lib/data/post/ViewablePost.class.php');

/**
 * Represents a viewable post in a rss or an atom feed.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class FeedPost extends ViewablePost {
	/**
	 * @see ViewablePost::getFormattedMessage()
	 */
	public function getFormattedMessage() {
		// replace relative urls
		$text = preg_replace('~(?<=href="|src=")(?![a-z0-9]+://)(?!mailto:)~i', PAGE_URL.'/', parent::getFormattedMessage());
		
		return StringUtil::escapeCDATA($text);
	}
}
?>