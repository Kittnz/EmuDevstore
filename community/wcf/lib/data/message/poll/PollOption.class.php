<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/poll/Poll.class.php');
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * This class represents an option of a poll.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.poll
 * @subpackage	data.message.poll
 * @category 	Community Framework
 */
class PollOption extends DatabaseObject {
	/**
	 * poll object
	 *
	 * @var Poll
	 */
	protected $poll = null;
	
	/**
	 * Creates a new PollOption object.
	 * 
	 * @param	array		$row		resultset with option data form database
	 * @param	Poll		$poll		the poll of this option
	 */
	public function __construct($row, Poll $poll) {
		$this->poll = $poll;
		parent::__construct($row);
	}
	
	/**
	 * Returns the percent value of this option.
	 * 
	 * @return	double		percent value of this option
	 */
	public function getPercent() {
		if ($this->poll->votes > 0) {
			return $this->votes / $this->poll->votes * 100;
		}
			
		return 0;
	}
	
	/**
	 * Returns the bar width of this option.
	 * 
	 * @return	integer		bar width of this option
	 */
	public function getBarWidth() {
		if ($this->poll->votes > 0) {
			return round($this->votes / $this->poll->votes * 100, 0);
		}
		
		return 0;
	}
	
	/**
	 * Returns true, if this option is checked.
	 * 
	 * @return	boolean		true, if this option is checked
	 */
	public function isChecked() {
		return ($this->userID || $this->ipAddress);
	}
	
	/**
	 * Decreases the number of votes by one.
	 */
	public function removeVote() {
		$this->data['votes']--;
		$this->data['userID'] = 0;
		$this->data['ipAddress'] = '';
	}
	
	/**
	 * Increases the number of votes by one.
	 */
	public function addVote() {
		$this->data['votes']++;
		$this->data['userID'] = WCF::getUser()->userID;
		$this->data['ipAddress'] = WCF::getSession()->ipAddress;
	}
	
	/**
	 * Compares the votes of two poll options.
	 *
	 * @param	PollOption 	$optionA
	 * @param	PollOption 	$optionB
	 * @return	integer
	 */
	public static function compareResult(PollOption $optionA, PollOption $optionB) {
		if ($optionA->votes < $optionB->votes) return 1;
		else if ($optionA->votes > $optionB->votes) return -1;
		else return ($optionA->showOrder > $optionB->showOrder ? 1 : -1);
	}
}
?>