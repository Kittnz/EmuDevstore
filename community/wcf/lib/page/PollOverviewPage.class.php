<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/data/message/poll/Polls.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');

/**
 * Shows a detailed overview of a poll.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.poll
 * @subpackage	page
 * @category 	Community Framework
 */
class PollOverviewPage extends AbstractPage {
	// system
	public $templateName = 'pollOverview';

	/**
	 * poll id
	 *
	 * @var	integer
	 */
	public $pollID = 0;
	
	/**
	 * polls object
	 *
	 * @var	Polls
	 */
	public $polls = null;
	
	/**
	 * poll object
	 *
	 * @var	Poll
	 */
	public $poll = null;
	
	/**
	 * list of poll votes
	 *
	 * @var	array
	 */
	public $votes = array();
	
	/**
	 * true, if the active user can vote this poll
	 * 
	 * @var	boolean
	 */
	public $canVotePoll = true;
	
	/**
	 * @see	Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['pollID'])) $this->pollID = intval($_REQUEST['pollID']);
		$this->poll = new Poll($this->pollID);
		if (!$this->poll->pollID || $this->poll->packageID != PACKAGE_ID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->polls = new Polls($this->pollID, $this->canVotePoll);
		$this->poll = $this->polls->getPoll($this->pollID);

		if ($this->poll->isPublic) {
	 		// get sorted options
	 		foreach ($this->poll->getSortedPollOptions() as $pollOption) {
	 			$this->votes[$pollOption->pollOptionID] = array('pollOption' => $pollOption, 'users' => array(), 'guests' => 0);
	 		}
	 		
	 		// get poll votes
	 		$sql = "SELECT 		poll_option_vote.*,
						option_value.*, user_table.*
				FROM 		wcf".WCF_N."_poll_option_vote poll_option_vote
				LEFT JOIN	wcf".WCF_N."_user user_table
				ON		(user_table.userID = poll_option_vote.userID)
				LEFT JOIN	wcf".WCF_N."_user_option_value option_value
				ON		(option_value.userID = user_table.userID)
				WHERE 		poll_option_vote.pollID = ".$this->pollID."
				ORDER BY 	user_table.username";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
	 			if (isset($this->votes[$row['pollOptionID']])) {
					if ($row['userID']) {
		 				$this->votes[$row['pollOptionID']]['users'][] = new UserProfile(null, $row);
		 			}
		 			else {
		 				$this->votes[$row['pollOptionID']]['guests']++;
		 			}
	 			}
			}
 		}
	}
	
	/**
	 * @see	Page::readData()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'pollID' => $this->pollID,
			'poll' => $this->poll,
			'votes' => $this->votes
		));
	}
}
?>