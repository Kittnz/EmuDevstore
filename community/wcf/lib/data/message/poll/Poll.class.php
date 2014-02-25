<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/poll/PollOption.class.php');
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * This class represents a poll in a message.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.poll
 * @subpackage	data.message.poll
 * @category 	Community Framework
 */
class Poll extends DatabaseObject {
	/**
	 * list of poll options
	 *
	 * @var	array<PollOption>
	 */
	protected $pollOptions = array();
	
	/**
	 * true, if the results of a poll should be shown 
	 *
	 * @var boolean
	 */
	protected $showResult = false;
	
	/**
	 * true, if the active user has permission to vote a poll 
	 *
	 * @var boolean
	 */
	protected $canVotePoll = true;
	
	/**
	 * Creates a new Poll object.
	 * 
	 * @param	integer		$pollID
	 * @param	array<mixed>	$row
	 * @param	boolean		$canVotePoll	true, if the active user has permission to vote a poll
	 */
	public function __construct($pollID, $row = null, $canVotePoll = true) {
		$this->canVotePoll = $canVotePoll;
		
		if ($pollID !== null) {
			$sql = "SELECT 		poll_vote.pollID AS voted,
						poll_vote.isChangeable,
						poll.*
				FROM 		wcf".WCF_N."_poll poll
				LEFT JOIN 	wcf".WCF_N."_poll_vote poll_vote
				ON 		(poll_vote.pollID = poll.pollID
						".(!WCF::getUser()->userID ? "AND poll_vote.ipAddress = '".escapeString(WCF::getSession()->ipAddress)."'" : '')."
						AND poll_vote.userID = ".WCF::getUser()->userID.")
				WHERE 		poll.pollID = ".$pollID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns the options of this poll.
	 * 
	 * @return	array		options of this poll
	 */
	public function getPollOptions() {
		return $this->pollOptions;
	}
	
	/**
	 * Returns the sorted options of this poll.
	 *
	 * @return	array
	 */
	public function getSortedPollOptions() {
		$pollOptions = $this->getPollOptions();
		if ($this->sortByResult) {
			// sort by result
			uasort($pollOptions, array('PollOption', 'compareResult'));
		}
		
		return $pollOptions;
	}
	
	/**
	 * Returns the option with the given option id.
	 * 
	 * @param	integer			$optionID
	 * @return	PollOption	option with the given option id
	 */
	public function getPollOption($optionID) {
		if (isset($this->pollOptions[$optionID])) return $this->pollOptions[$optionID];
		return null;
	}
	
	/**
	 * Returns true, if the active user can vote this poll.
	 * 
	 * @return	boolean		true, if the active user can vote this poll	
	 */
	public function canVote() {
		return $this->canVotePoll && ($this->endTime == 0 || $this->endTime >= TIME_NOW) && (!$this->voted || ($this->isChangeable && !$this->votesNotChangeable));
	}
	
	/**
	 * Adds a new option to this poll.
	 * 
	 * @param	PollOption	$option		new option 
	 */
	public function addOption(PollOption $option) {
		$this->pollOptions[$option->pollOptionID] = $option;
	}
	
	/**
	 * Increases the number of votes by one.
	 */
	public function addVote() {
		$this->data['votes']++;
	}
}
?>