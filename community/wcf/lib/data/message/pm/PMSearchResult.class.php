<?php
require_once(WCF_DIR.'lib/data/message/pm/ViewablePM.class.php');
require_once(WCF_DIR.'lib/data/message/util/SearchResultTextParser.class.php');

/**
 * This class extends the viewable post by function for a search result output.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm
 * @category 	Community Framework (commercial)
 */
class PMSearchResult extends ViewablePM {
	/**
	 * @see ViewablePM::handleData();
	 */
	protected function handleData($data) {
		$data['messagePreview'] = true;
		parent::handleData($data);
	}
	
	/**
	 * @see ViewablePM::getFormattedMessage()
	 */
	public function getFormattedMessage() {
		return SearchResultTextParser::parse(parent::getFormattedMessage());
	}
}
?>