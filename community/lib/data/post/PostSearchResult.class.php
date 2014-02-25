<?php
// wbb imports
require_once(WBB_DIR.'lib/data/post/ViewablePost.class.php');

// wcf imports
require_once(WCF_DIR.'lib/data/message/util/SearchResultTextParser.class.php');

/**
 * This class extends the viewable post by function for a search result output.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.post
 * @category 	Burning Board
 */
class PostSearchResult extends ViewablePost {
	/**
	 * @see DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		if (!$this->subject) $this->data['subject'] = $this->data['topic'];
		$this->data['messagePreview'] = true;
	}
	
	/**
	 * @see ViewablePost::getFormattedMessage()
	 */
	public function getFormattedMessage() {
		return SearchResultTextParser::parse(parent::getFormattedMessage());
	}
}
?>