<?php
require_once(WCF_DIR.'lib/data/message/pm/ViewablePM.class.php');

/**
 * Represents a viewable private message in a rss or an atom feed.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm
 * @category 	Community Framework (commercial)
 */
class FeedPM extends ViewablePM {
	/**
	 * @see ViewablePM::getFormattedMessage()
	 */
	public function getFormattedMessage() {
		// replace relative urls
		$text = preg_replace('~(?<=href="|src=")(?![a-z0-9]+://)~i', PAGE_URL.'/', parent::getFormattedMessage());
		
		return StringUtil::escapeCDATA($text);
	}
}
?>