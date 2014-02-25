<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/poll/Poll.class.php');
require_once(WCF_DIR.'lib/data/message/poll/PollOption.class.php');

/**
 * This class reads one or more polls from database and handles the request on a specific poll.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.poll
 * @subpackage	data.message.poll
 * @category 	Community Framework
 */
class Polls {
	/**
	 * list of polls
	 * 
	 * @var	array<Poll>
	 */
	protected $polls = array();
	
	/**
	 * name of the forward script
	 * 
	 * @var	string
	 */
	protected $forwardScript = null;
	
	/**
	 * message type
	 * 
	 * @var	string
	 */
	protected $messageType = '';
	
	/**
	 * Creates a new Polls object.
	 * 
	 * @param	string		$pollIDs
	 * @param	boolean		$canVotePoll	true, if the active user has permission to vote poll
	 * @param	string		$forwardScript		
	 */
	public function __construct($pollIDs, $canVotePoll = true, $forwardScript = null, $messageType = '') {
		// get polls
		$sql = "SELECT 		poll_vote.pollID AS voted,
					poll_vote.isChangeable,
					poll.*
			FROM 		wcf".WCF_N."_poll poll
			LEFT JOIN 	wcf".WCF_N."_poll_vote poll_vote
			ON 		(poll_vote.pollID = poll.pollID
					".(!WCF::getUser()->userID ? "AND poll_vote.ipAddress = '".escapeString(WCF::getSession()->ipAddress)."'" : '')."
					AND poll_vote.userID = ".WCF::getUser()->userID.")
			WHERE 		poll.pollID IN (".$pollIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
 			$this->polls[$row['pollID']] = new Poll(null, $row, $canVotePoll);
 			if (empty($messageType)) $messageType = $row['messageType'];
 		}
		
		// get poll options
		$sql = "SELECT 		poll_option_vote.*,
					poll_option.*
			FROM 		wcf".WCF_N."_poll_option poll_option
			LEFT JOIN 	wcf".WCF_N."_poll_option_vote poll_option_vote
			ON 		(poll_option_vote.pollOptionID = poll_option.pollOptionID
					".(!WCF::getUser()->userID ? "AND poll_option_vote.ipAddress = '".escapeString(WCF::getSession()->ipAddress)."'" : '')."
					AND poll_option_vote.userID = ".WCF::getUser()->userID.")
			WHERE 		poll_option.pollID IN (".$pollIDs.")
			ORDER BY 	poll_option.showOrder";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
 			$this->polls[$row['pollID']]->addOption(new PollOption($row, $this->polls[$row['pollID']]));
 		}
 		
 		$this->messageType = $messageType;
 		$this->forwardScript = $forwardScript; 		
 		$this->handleRequest();
	}
	
	/**
	 * Returns the poll with the given poll id.
	 * 
	 * @param	integer		$pollID
	 * @return	Poll
	 */
	public function getPoll($pollID) {
		if (isset($this->polls[$pollID])) return $this->polls[$pollID];
		return null;
	}
	
	/**
	 * Counts the polls of the active page.
	 * 
	 * @return	integer
	 */
	public function countPolls() {
		return count($this->polls);
	}
	
	/**
	 * Handles the request on a poll.
	 */
	protected function handleRequest() {
		if (isset($_REQUEST['pollID'])) $pollID = intval($_REQUEST['pollID']);
		else $pollID = 0;
		
		if (isset($_POST['votePoll'])) {
			if (isset($_POST['pollOptionID'])) {
				if (is_array($_POST['pollOptionID'])) $pollOptionID = ArrayUtil::toIntegerArray($_POST['pollOptionID']);
				else $pollOptionID = intval($_POST['pollOptionID']);
			}
			else $pollOptionID = 0;
		
			// get poll
			if (!isset($this->polls[$pollID])) {
				throw new IllegalLinkException();
			}
			$poll = $this->polls[$pollID];
			
			try {
				// error handling
				if (!$poll->canVote()) {
					throw new PermissionDeniedException();
				}
			
				if (is_array($pollOptionID)) {
					if (count($pollOptionID) < 1) {
						throw new UserInputException('pollOptionID', 'notValid');
					}
					
					if (count($pollOptionID) > $poll->choiceCount) {
						throw new UserInputException('pollOptionID', 'tooMuch');
					}
					
					foreach ($pollOptionID as $optionID) {
						$pollOption = $poll->getPollOption($optionID);
						if ($pollOption === null) {
							throw new UserInputException('pollOptionID', 'notValid');
						}
					}
				}
				else {
					$pollOption = $poll->getPollOption($pollOptionID);
					if ($pollOption === null) {
						throw new UserInputException('pollOptionID', 'notValid');
					}
				}
			
				// save poll, options & votes
				if ($poll->isChangeable) {
					$sql = "DELETE FROM	wcf".WCF_N."_poll_option_vote
						WHERE 		pollID = ".$pollID."
								".(!WCF::getUser()->userID ? "AND ipAddress = '".escapeString(WCF::getSession()->ipAddress)."'" : '')."
								AND userID = ".WCF::getUser()->userID;
					WCF::getDB()->sendQuery($sql);
					foreach ($poll->getPollOptions() as $pollOption) {
						if ($pollOption->isChecked()) {
							$pollOption->removeVote();
							$sql = "UPDATE	wcf".WCF_N."_poll_option
								SET 	votes = votes - 1
								WHERE 	pollOptionID = ".$pollOption->pollOptionID;
							WCF::getDB()->sendQuery($sql);	
						}
					}
				}
				else {
					$poll->addVote();
					$sql = "UPDATE	wcf".WCF_N."_poll
						SET 	votes = votes + 1
						WHERE 	pollID = ".$pollID;
					WCF::getDB()->sendQuery($sql);
					
					$sql = "INSERT INTO	wcf".WCF_N."_poll_vote
								(pollID, userID, ipAddress)
						VALUES		(".$pollID.",
								".WCF::getUser()->userID.",
								'".escapeString(WCF::getSession()->ipAddress)."')";
					WCF::getDB()->sendQuery($sql);
				}
				
				if (is_array($pollOptionID)) {
					foreach ($pollOptionID as $optionID) {
						$pollOption = $poll->getPollOption($optionID);
						$pollOption->addVote();
						
						$sql = "UPDATE	wcf".WCF_N."_poll_option
							SET 	votes = votes + 1
							WHERE 	pollOptionID = ".$optionID;
						WCF::getDB()->sendQuery($sql);
						
						$sql = "INSERT INTO	wcf".WCF_N."_poll_option_vote
									(pollID, pollOptionID, userID, ipAddress)
							VALUES 		(".$pollID.",
									".$optionID.",
									".WCF::getUser()->userID.",
									'".escapeString(WCF::getSession()->ipAddress)."')";
						WCF::getDB()->sendQuery($sql);
					}
				}
				else {
					$pollOption = $poll->getPollOption($pollOptionID);
					$pollOption->addVote();
					
					$sql = "UPDATE	wcf".WCF_N."_poll_option
						SET 	votes = votes + 1
						WHERE 	pollOptionID = ".$pollOptionID;
					WCF::getDB()->sendQuery($sql);
					
					$sql = "INSERT INTO	wcf".WCF_N."_poll_option_vote
								(pollID, pollOptionID, userID, ipAddress)
						VALUES 		(".$pollID.",
								".$pollOptionID.",
								".WCF::getUser()->userID.",
								'".escapeString(WCF::getSession()->ipAddress)."')";
					WCF::getDB()->sendQuery($sql);
				}
			
				if ($this->forwardScript != null) {
					// forward to message page
					HeaderUtil::redirect($this->forwardScript.(strstr($this->forwardScript, '?') ? '&' : '?').$this->messageType.'ID='.$poll->messageID.SID_ARG_2ND_NOT_ENCODED.'#'.$this->messageType.$poll->messageID);
					exit;
				}
			}
			catch (UserInputException $e) {
				WCF::getTPL()->assign(array(
					'errorField' => $e->getField(),
					'errorType' => $e->getType(),
					'activePollID' => $pollID
				));
			}
		}
	}
}
?>