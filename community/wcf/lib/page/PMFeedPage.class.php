<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractFeedPage.class.php');
require_once(WCF_DIR.'lib/data/message/pm/FeedPM.class.php');

/**
 * Prints a list of private messages as a rss or an atom feed.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	page
 * @category 	Community Framework (commercial)
 */
class PMFeedPage extends AbstractFeedPage {
	/**
	 * list of messages
	 * 
	 * @var	array<FeedPM>
	 */
	public $messages = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Gets the messages for the feed.
	 */
	protected function readPMs() {
		$sql = "SELECT		pm.*,
					recipient.*, IF(recipient.isViewed > 0, 1, 0) isViewedSortField
			FROM		wcf".WCF_N."_pm_to_user recipient
			LEFT JOIN	wcf".WCF_N."_pm pm
			ON		(pm.pmID = recipient.pmID)
			WHERE		recipient.recipientID = ".WCF::getUser()->userID."
					AND recipient.isDeleted = 0
			ORDER BY 	pm.time DESC";
		$result = WCF::getDB()->sendQuery($sql, $this->limit);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->messages[] = new FeedPM(null, $row);
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readPMs();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'messages' => $this->messages
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!MODULE_PM) {
			throw new IllegalLinkException();
		}
		
		parent::show();
		
		// send content
		WCF::getTPL()->display(($this->format == 'atom' ? 'pmFeedAtom' : 'pmFeedRss2'), false);
	}
}
?>