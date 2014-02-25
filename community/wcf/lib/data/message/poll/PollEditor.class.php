<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/poll/Poll.class.php');

/**
 * PollEditor provides functions to create and edit a poll.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.poll
 * @subpackage	data.message.poll
 * @category 	Community Framework
 */
class PollEditor extends Poll {
	protected $pollOptionsArray = array();
	protected $valid = false;
	protected $endTimeDay = '';
	protected $endTimeMonth = '';
	protected $endTimeYear = '';
	protected $endTimeHour = '';
	protected $endTimeMinutes = '';
	protected $canStartPublicPoll = true;
	
	/**
	 * Creates a new PollEditor object.
	 * 
	 * @param	integer		$pollID
	 * @param	integer		$messageID
	 * @param	string		$messageType
	 */
	public function __construct($pollID = 0, $messageID = 0, $messageType = 'post', $canStartPublicPoll = true) {
		$this->canStartPublicPoll = ($pollID == 0 ? $canStartPublicPoll : false);
		$this->data['pollID'] = $pollID;
		$this->data['messageID'] = $messageID;
		$this->data['messageType'] = $messageType;
		$this->data['timeout'] = 0;
		$this->data['choiceCount'] = 1;
		$this->data['votesNotChangeable'] = 0;
		$this->data['sortByResult'] = 0;
		$this->data['isPublic'] = 0;
		
		if ($messageID != 0 || $pollID != 0) {
			// get poll
			$sql = "SELECT	*
				FROM 	wcf".WCF_N."_poll
				WHERE	".($messageID != 0 ? "
					messageID = ".$messageID."
					AND messageType = '".escapeString($messageType)."'					
					AND packageID = ".PACKAGE_ID : 
					"pollID = ".$pollID."
					AND packageID = ".PACKAGE_ID);
			$row = WCF::getDB()->getFirstRow($sql);
			if (isset($row['pollID'])) {
				parent::__construct(null, $row);
			
				// get poll options
				$sql = "SELECT		*
					FROM 		wcf".WCF_N."_poll_option
					WHERE 		pollID = ".$this->pollID."
					ORDER BY 	showOrder";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$pollOption = new PollOption($row, $this);
					$this->pollOptionsArray[] = $pollOption->pollOption;
					$this->addOption($pollOption);
				}
			}
		}
		
		$this->assign();
	}
	
	/**
	 * Reads the given parameters.
	 */
	public function readParams() {
		$pollOptionsText = '';
		$this->data['votesNotChangeable'] = $this->data['sortByResult'] = 0;
		if (isset($_POST['pollQuestion'])) 	$this->data['question']			= StringUtil::trim($_POST['pollQuestion']);
		if (isset($_POST['pollOptions'])) 	$pollOptionsText 			= StringUtil::unifyNewlines(StringUtil::trim($_POST['pollOptions']));
		if (isset($_POST['choiceCount'])) 	$this->data['choiceCount'] 		= intval($_POST['choiceCount']);
		if (isset($_POST['votesNotChangeable'])) $this->data['votesNotChangeable'] 	= intval($_POST['votesNotChangeable']);
		if (isset($_POST['sortByResult'])) 	$this->data['sortByResult'] 		= intval($_POST['sortByResult']);
		if ($this->canStartPublicPoll) {
			$this->data['isPublic'] = 0;
			if (isset($_POST['isPublic'])) $this->data['isPublic'] = intval($_POST['isPublic']);
		}
		
		// end time
		if (isset($_POST['endTimeDay'])) $this->endTimeDay = intval($_POST['endTimeDay']);
		if (isset($_POST['endTimeMonth'])) $this->endTimeMonth = intval($_POST['endTimeMonth']);
		if (isset($_POST['endTimeYear'])) $this->endTimeYear = intval($_POST['endTimeYear']);
		if (isset($_POST['endTimeHour'])) $this->endTimeHour = intval($_POST['endTimeHour']);
		if (isset($_POST['endTimeMinutes'])) $this->endTimeMinutes = intval($_POST['endTimeMinutes']);
		
		$this->pollOptionsArray = array_unique(ArrayUtil::trim(explode("\n", $pollOptionsText)));
		
		$this->assign();
	}
	
	/**
	 * Checks the given parameters.
	 * 
	 * @return	boolean
	 */
	public function checkParams() {
		if (!$this->question) {
			return false;
		}
		
		if (count($this->pollOptionsArray) < 2) {
			throw new UserInputException('pollOptions', 'notEnoughOptions');
		}
		
		if (count($this->pollOptionsArray) > POLL_MAX_OPTIONS) {
			throw new UserInputException('pollOptions', 'tooMuch');
		}
		
		if ($this->choiceCount < 1) {
			throw new UserInputException('choiceCount', 'notValid');
		}
		
		if ($this->choiceCount > count($this->pollOptionsArray)) {
			throw new UserInputException('choiceCount', 'tooMuch');
		}
		
		if ($this->endTimeDay || $this->endTimeMonth || $this->endTimeYear || $this->endTimeHour || $this->endTimeMinutes) {
			$time = @gmmktime($this->endTimeHour, $this->endTimeMinutes, 0, $this->endTimeMonth, $this->endTimeDay, $this->endTimeYear);
			// since php5.1.0 mktime returns false on failure
			if ($time === false || $time === -1) {
				throw new UserInputException('endTime', 'invalid');
			}
			
			// get utc time
			$time = DateUtil::getUTC($time);
			if (!$this->pollID && $time <= TIME_NOW) {
				throw new UserInputException('endTime', 'invalid');
			}
			
			$this->data['endTime'] = $time;
		}
		else {
			$this->data['endTime'] = 0;
		}
		
		$this->valid = true;
	}
	
	/**
	 * Saves the poll in database.
	 */
	public function save() {
		if ($this->valid) {
			if ($this->pollID) {
				// update poll
				$this->update();
			}
			else {
				// create new poll
				$this->pollID = self::create($this->messageID, $this->messageType, $this->question, $this->pollOptionsArray, $this->choiceCount, $this->endTime, $this->votesNotChangeable, $this->sortByResult, $this->isPublic);
			}
		}
		else if ($this->pollID) {
			$this->delete();
		}
	}
	
	/**
	 * Updates the data of an existing poll.
	 */
	public function update() {
		$sql = "UPDATE 	wcf".WCF_N."_poll
			SET	question = '".escapeString($this->question)."',
				choiceCount = ".$this->choiceCount.",
				endTime = ".$this->endTime.",
				votesNotChangeable = ".$this->votesNotChangeable.",
				sortByResult = ".$this->sortByResult.",
				isPublic = ".$this->isPublic."
			WHERE 	pollID = " .$this->pollID;
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// search for unchanged or moved options
		$showOrder = 0;
		foreach ($this->pollOptionsArray as $outerKey => $newPollOption) {
			foreach ($this->pollOptions as $innerKey => $pollOption) {
				if ($pollOption->pollOption == $newPollOption) {
					if ($showOrder != $pollOption->showOrder) {
						// position of this option changed
						$sql = "UPDATE	wcf".WCF_N."_poll_option
							SET 	showOrder = ".$showOrder."
							WHERE 	pollOptionID = ".$pollOption->pollOptionID;
						WCF::getDB()->registerShutdownUpdate($sql);
					}
					
					unset($this->pollOptions[$innerKey]);
					$this->pollOptionsArray[$outerKey] = '';
					break;
				}
			}
			
			$showOrder++;
		}
		
		// search for renamed or added options
		$showOrder = 0;
		foreach ($this->pollOptionsArray as $outerKey => $newPollOption) {
			if (!empty($newPollOption)) {
				$renamed = false;
				foreach ($this->pollOptions as $innerKey => $pollOption) {
					if ($pollOption->showOrder == $showOrder) {
						// option probably renamed
						$sql = "UPDATE	wcf".WCF_N."_poll_option
							SET 	pollOption = '".escapeString($newPollOption)."'
							WHERE 	pollOptionID = ".$pollOption->pollOptionID;
						WCF::getDB()->registerShutdownUpdate($sql);
						
						unset($this->pollOptions[$innerKey]);
						$this->pollOptionsArray[$outerKey] = '';
						$renamed = true;
						break;
					}
				}
				
				// option probably added
				if (!$renamed) {
					$sql = "INSERT INTO 	wcf".WCF_N."_poll_option
								(pollID, pollOption, showOrder)
						VALUES		(".$this->pollID.",
								'".escapeString($newPollOption)."',
								".$showOrder.")";
					WCF::getDB()->registerShutdownUpdate($sql);
				}
			}
			
			$showOrder++;
		}
		
		// delete removed options
		foreach ($this->pollOptions as $pollOption) {
			$sql = "DELETE FROM	wcf".WCF_N."_poll_option
				WHERE		pollOptionID = ".$pollOption->pollOptionID;
			WCF::getDB()->registerShutdownUpdate($sql);
			
			$sql = "DELETE FROM	wcf".WCF_N."_poll_option_vote
				WHERE 		pollOptionID = ".$pollOption->pollOptionID;
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	
	}
	
	/**
	 * Updates the message id of this poll.
	 * 
	 * @param	integer		$messageID	new message id
	 */
	public function updateMessageID($messageID) {
		$this->data['messageID'] = $messageID;
		
		if ($this->pollID) {
			$sql = "UPDATE	wcf".WCF_N."_poll
				SET	messageID = ".$messageID."
				WHERE 	pollID = ".$this->pollID;
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	}
	
	/**
	 * Assigns the data of this poll to the template engine.
	 */
	protected function assign() {
		require_once(WCF_DIR.'lib/page/util/InlineCalendar.class.php');
		InlineCalendar::assignVariables();
		
		if (!count($_POST) && $this->endTime) {
			$this->endTimeDay = intval(DateUtil::formatDate('%e', $this->endTime, false, true));
			$this->endTimeMonth = intval(DateUtil::formatDate('%m', $this->endTime, false, true));
			$this->endTimeYear = DateUtil::formatDate('%Y', $this->endTime, false, true);
			$this->endTimeHour = DateUtil::formatDate('%H', $this->endTime, false, true);
			$this->endTimeMinutes = DateUtil::formatDate('%M', $this->endTime, false, true);
		}
		
		WCF::getTPL()->assign(array(
			'pollID' => $this->pollID,
			'pollQuestion' => $this->question,
			'pollOptions' => implode("\n", $this->pollOptionsArray),
			'choiceCount' => $this->choiceCount,
			'timeout' => $this->timeout,
			'votesNotChangeable' => $this->votesNotChangeable,
			'sortByResult' => $this->sortByResult,
			'isPublic' => $this->isPublic,
			'endTimeDay' => $this->endTimeDay,
			'endTimeMonth' => $this->endTimeMonth,
			'endTimeYear' => $this->endTimeYear,
			'endTimeHour' => $this->endTimeHour,
			'endTimeMinutes' => $this->endTimeMinutes,
			'canStartPublicPoll' => $this->canStartPublicPoll
		));
	}
	
	/**
	 * Deletes this poll.
	 */
	public function delete() {
		self::deleteData($this->pollID);
		$this->data['pollID'] = 0;
	}
	
	/**
	 * Deletes all polls with the given message ids.
	 * 
	 * @param	string		$messageIDs
	 * @param	string		$messageType
	 */
	public static function deleteAll($messageIDs, $messageType = 'post') {
		if (empty($messageIDs)) return;
		 
		$pollIDs = '';
		$sql = "SELECT	pollID
			FROM 	wcf".WCF_N."_poll
			WHERE 	messageID IN (".$messageIDs.")
				AND messageType = '".escapeString($messageType)."'
				AND packageID = ".PACKAGE_ID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($pollIDs)) $pollIDs .= ',';
			$pollIDs .= $row['pollID'];
		}
		
		self::deleteData($pollIDs);
	}
	
	/**
	 * Deletes all polls with the given poll ids.
	 * Deletes the sql data in tables poll, poll_option, poll_option_vote and poll_vote.
	 * 
	 * @param	string		$pollIDs
	 */
	protected static function deleteData($pollIDs) {
		if (empty($pollIDs)) return;
		
		$sql = "DELETE FROM	wcf".WCF_N."_poll
			WHERE 		pollID IN (".$pollIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		$sql = "DELETE FROM	wcf".WCF_N."_poll_option
			WHERE 		pollID IN (".$pollIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		$sql = "DELETE FROM	wcf".WCF_N."_poll_option_vote
			WHERE 		pollID IN (".$pollIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		$sql = "DELETE FROM	wcf".WCF_N."_poll_vote
			WHERE 		pollID IN (".$pollIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Copies all sql data of the polls with the given message ids.
	 * 
	 * @param	string		$messageIDs
	 * @param	array		$messageMapping
	 * @param	string		$messageType
	 * @return	array		$pollMapping
	 */
	public static function copyAll($messageIDs, &$messageMapping, $messageType = 'post') {
		if (empty($messageIDs)) return;
		
		// copy 'poll' data
		$pollMapping = array();
		$pollIDs = '';
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_poll
			WHERE 	messageID IN (".$messageIDs.")
				AND messageType = '".escapeString($messageType)."'
				AND packageID = ".PACKAGE_ID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($pollIDs)) $pollIDs .= ',';
			$pollIDs .= $row['pollID'];
			
			$newPollID = self::insert($row['question'], array(
				'packageID' => PACKAGE_ID,
				'messageID' => $messageMapping[$row['messageID']],
				'messageType' => $row['messageType'],
				'time' => $row['time'],
				'choiceCount' => $row['choiceCount'],
				'endTime' => $row['endTime'],
				'votes' => $row['votes'],
				'votesNotChangeable' => $row['votesNotChangeable'],
				'sortByResult' => $row['sortByResult'],
				'isPublic' => $row['isPublic']
			));
			
			$pollMapping[$row['pollID']] = $newPollID;
		}
		
		if (empty($pollIDs)) return;
		
		// copy 'poll_option' data
		$pollOptionMapping = array();
		$sql = "SELECT	*
			FROM 	wcf".WCF_N."_poll_option
			WHERE 	pollID IN (".$pollIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$sql = "INSERT INTO	wcf".WCF_N."_poll_option
						(pollID, pollOption, votes, showOrder)
				VALUES		(".$pollMapping[$row['pollID']].",
						'".escapeString($row['pollOption'])."',
						".$row['votes'].",
						".$row['showOrder'].")";
			WCF::getDB()->registerShutdownUpdate($sql);
				
			$pollOptionMapping[$row['pollOptionID']] = WCF::getDB()->getInsertID();
		}
		
		// copy 'poll_option_vote' data
		$sql = "SELECT	*
			FROM 	wcf".WCF_N."_poll_option_vote
			WHERE 	pollID IN (".$pollIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		$inserts = '';
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$pollMapping[$row['pollID']].", ".$pollOptionMapping[$row['pollOptionID']].", ".$row['userID'].", '".escapeString($row['ipAddress'])."')";
		}
		if (!empty($inserts)) {
			$sql = "INSERT INTO	wcf".WCF_N."_poll_option_vote
						(pollID, pollOptionID, userID, ipAddress)
				VALUES		".$inserts;
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		// copy 'poll_vote' data
		$sql = "SELECT	*
			FROM 	wcf".WCF_N."_poll_vote
			WHERE	pollID IN (".$pollIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		$inserts = '';
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$pollMapping[$row['pollID']].", ".$row['isChangeable'].", ".$row['userID'].", '".escapeString($row['ipAddress'])."')";
		}
		if (!empty($inserts)) {
			$sql = "INSERT INTO	wcf".WCF_N."_poll_vote
						(pollID, isChangeable, userID, ipAddress)
				VALUES		".$inserts;
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		return $pollMapping;
	}
	
	/**
	 * Creates a new poll.
	 * 
	 * @param	integer		$messageID
	 * @param	string		$messageType
	 * @param	string		$pollQuestion
	 * @param	array		$pollOptions
	 * @param	integer		$choiceCount
	 * @param	integer		$endTime
	 * @param	boolean		$voteNotChangeable
	 * @param	boolean		$sortByResult
	 * @param	boolean		$isPublic
	 * @return	integer		$pollID
	 */
	public static function create($messageID, $messageType, $pollQuestion, $pollOptions, $choiceCount, $endTime, $votesNotChangeable, $sortByResult, $isPublic) {
		// insert poll
		$pollID = self::insert($pollQuestion, array(
			'packageID' => PACKAGE_ID,
			'messageID' => $messageID,
			'messageType' => $messageType,
			'time' => TIME_NOW,
			'choiceCount' => $choiceCount,
			'endTime' => $endTime,
			'votesNotChangeable' => $votesNotChangeable,
			'sortByResult' => $sortByResult,
			'isPublic' => $isPublic
		));
		
		// insert poll options
		$showOrder = 0;
		$inserts = '';
		foreach ($pollOptions as $option) {
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$pollID.", '".escapeString($option)."', ".$showOrder.")";
			$showOrder++;
		}
		if (!empty($inserts)) {
			$sql = "INSERT INTO	wcf".WCF_N."_poll_option
						(pollID, pollOption, showOrder)
				VALUES		".$inserts;
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		
		// insert poll vote for author
		/*
		$sql = "INSERT INTO	wcf".WCF_N."_poll_vote
					(pollID, changeable, userID, ipAddress)
			VALUES		(".$pollID.",
					0,
					".WCF::getUser()->userID.",
					'".escapeString(WCF::getSession()->ipAddress)."')";
		WCF::getDB()->registerShutdownUpdate($sql);
		*/
		
		return $pollID;
	}
	
	/**
	 * Creates the poll row in database table.
	 *
	 * @param 	string 		$question
	 * @param 	array		$additionalFields
	 * @return	integer		new poll id
	 */
	public static function insert($question, $additionalFields = array()){ 
		$keys = $values = '';
		foreach ($additionalFields as $key => $value) {
			$keys .= ','.$key;
			$values .= ",'".escapeString($value)."'";
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_poll
					(question
					".$keys.")
			VALUES		('".escapeString($question)."'
					".$values.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}
}
?>