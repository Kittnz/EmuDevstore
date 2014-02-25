<?php
require_once(WBB_DIR.'lib/data/thread/ViewableThread.class.php');

/**
 * ThreadList is a default implementation for displaying a list of threads. 
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.thread
 * @category 	Burning Board
 */
class ThreadList {
	// parameters
	public $limit = 20, $offset = 0;
	
	// data
	public $threads = array();
	public $threadIDs = '';
	
	// sql plugin options
	public $sqlConditions = '';
	public $sqlOrderBy = 'thread.lastPostTime DESC';
	public $sqlSelects = '';
	public $sqlJoins = '';
	public $sqlSelectRating = '';
	public $enableRating = THREAD_ENABLE_RATING;
	
	/**
	 * Creates a new ThreadList object.
	 */
	public function __construct() {
		// default sql conditions
		$this->initDefaultSQL();
	}
	
	/**
	 * Fills the sql parameters with default values.
	 */
	protected function initDefaultSQL() {
		if (WCF::getUser()->userID != 0) {
			// own posts
			if (BOARD_THREADS_ENABLE_OWN_POSTS) {
				$this->sqlSelects = "DISTINCT post.userID AS ownPosts,";
				$this->sqlJoins = "	LEFT JOIN	wbb".WBB_N."_post post
							ON 		(post.threadID = thread.threadID
									AND post.userID = ".WCF::getUser()->userID.")";
			}
			
			// last visit time
			$this->sqlSelects .= 'thread_visit.lastVisitTime,';
			$this->sqlJoins .= "	LEFT JOIN 	wbb".WBB_N."_thread_visit thread_visit 
						ON 		(thread_visit.threadID = thread.threadID
								AND thread_visit.userID = ".WCF::getUser()->userID.")";
			
			// subscriptions
			$this->sqlSelects .= 'IF(thread_subscription.userID IS NOT NULL, 1, 0) AS subscribed,';
			$this->sqlJoins .= "	LEFT JOIN 	wbb".WBB_N."_thread_subscription thread_subscription 
						ON 		(thread_subscription.userID = ".WCF::getUser()->userID."
								AND thread_subscription.threadID = thread.threadID)";
		}
		
		// ratings
		if ($this->enableRating) {
			$this->sqlSelectRating = "if (thread.ratings>0 AND thread.ratings>=".THREAD_MIN_RATINGS.",thread.rating/thread.ratings,0) AS ratingResult,";
			$this->sqlSelects .= $this->sqlSelectRating;
		}
	}
	
	/**
	 * Counts threads.
	 * 
	 * @return	integer
	 */
	public function countThreads() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wbb".WBB_N."_thread thread
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : "");
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * Gets thread ids.
	 */
	protected function readThreadIDs() {
		$sql = "SELECT		".$this->sqlSelectRating."
					thread.threadID
			FROM		wbb".WBB_N."_thread thread
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : "")."
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
			$this->threads[] = new ViewableThread(null, $row);
		}
	}
	
	/**
	 * Builds the main sql query for selecting threads.
	 * 
	 * @return	string
	 */
	protected function buildQuery() {
		return "SELECT		".$this->sqlSelects."
					thread.*
			FROM 		wbb".WBB_N."_thread thread
			".$this->sqlJoins."
			WHERE		thread.threadID IN (".$this->threadIDs.")
			ORDER BY	".$this->sqlOrderBy;
	}
}
?>