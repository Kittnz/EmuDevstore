<?php
// wbb imports
require_once(WBB_DIR.'lib/data/thread/ViewableThread.class.php');

// wcf imports
require_once(WCF_DIR.'lib/data/message/util/KeywordHighlighter.class.php');

/**
 * Represents a viewable thread in a search result.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.thread
 * @category 	Burning Board
 */
class ThreadSearchResult extends ViewableThread {
	/**
	 * @see Thread::getTopic()
	 */
	public function getHighlightedTopic() {
		return KeywordHighlighter::doHighlight(StringUtil::encodeHTML($this->topic));
	}
}
?>