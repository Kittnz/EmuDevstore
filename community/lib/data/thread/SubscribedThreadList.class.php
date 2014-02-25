<?php
require_once(WBB_DIR.'lib/data/thread/ThreadList.class.php');
require_once(WBB_DIR.'lib/data/thread/SubscribedThread.class.php');

/**
 * SubscribedThreadList displays a list of subscribed threads. 
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.thread
 * @category 	Burning Board
 */
class SubscribedThreadList extends ThreadList {
	public $daysPrune = 100;
	
	/**
	 * Creates a new SubscribedThreadList object.
	 */
	public function __construct($daysPrune = 100) {
		$this->daysPrune = $daysPrune;
		
		parent::__construct();
	}
	
	/**
	 * @see ThreadList::countThreads()
	 */
	public function countThreads() {
		$sql = "SELECT		COUNT(*) AS threads
			FROM 		wbb".WBB_N."_thread_subscription subscription
			LEFT JOIN	wbb".WBB_N."_thread thread
			ON		(thread.threadID = subscription.threadID)
			WHERE 		subscription.userID = ".WCF::getUser()->userID."
					AND thread.isDeleted = 0
					AND thread.isDisabled = 0
					".($this->daysPrune != 1000 ? "AND thread.lastPostTime >= ".(TIME_NOW - ($this->daysPrune * 86400)) : '');
		$result = WCF::getDB()->getFirstRow($sql);
		return $result['threads'];
	}
	
	/**
	 * @see ThreadList::countThreads()
	 */
	public function countAllThreads() {
		$sql = "SELECT	COUNT(*) AS threads
			FROM 	wbb".WBB_N."_thread_subscription
			WHERE 	userID = ".WCF::getUser()->userID;
		$result = WCF::getDB()->getFirstRow($sql);
		return $result['threads'];
	}
	
	/**
	 * Gets thread ids.
	 */
	protected function readThreadIDs() {
		$sql = "SELECT		".$this->sqlSelectRating."
					subscription.threadID
			FROM 		wbb".WBB_N."_thread_subscription subscription
			LEFT JOIN	wbb".WBB_N."_thread thread
			ON		(thread.threadID = subscription.threadID)
			WHERE 		subscription.userID = ".WCF::getUser()->userID."
					AND thread.isDeleted = 0
					AND thread.isDisabled = 0
					".($this->daysPrune != 1000 ? "AND thread.lastPostTime >= ".(TIME_NOW - ($this->daysPrune * 86400)) : '')."
			ORDER BY	".$this->sqlOrderBy;
		$result = WCF::getDB()->sendQuery($sql, $this->limit, $this->offset);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($this->threadIDs)) $this->threadIDs .= ',';
			$this->threadIDs .= $row['threadID'];
		}
	}
	
	/**
	 * Reads a list of threads.
	 */
	public function readThreads() {
		// get post ids
		$this->readThreadIDs();
		if (empty($this->threadIDs)) return false;

		// get threads
		$sql = $this->buildQuery();
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->threads[] = new SubscribedThread(null, $row);
		}
	}
}
?>