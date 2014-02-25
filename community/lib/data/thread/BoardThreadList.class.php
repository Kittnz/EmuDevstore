<?php
require_once(WBB_DIR.'lib/data/thread/ThreadList.class.php');

/**
 * BoardThreadList provides extended functions for displaying a list of threads.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.thread
 * @category 	Burning Board
 */
class BoardThreadList extends ThreadList {
	// parameters
	public $board;
	public $daysPrune = 100;
	public $prefix = '';
	public $status = ''; 
	public $languageID = 0;
	
	// data
	public $topThreads = array();
	public $newTopThreads = 0;
	public $newThreads = 0;
	public $maxLastPostTime = 0;
	
	// sql
	public $sqlConditionVisible = '';
	public $sqlConditionLanguage = '';

	/**
	 * Creates a new BoardThreadList object.
	 */
	public function __construct(Board $board, $daysPrune = 100, $prefix = '', $status = '', $languageID = 0) {
		$this->board = $board;
		$this->daysPrune = $daysPrune;
		$this->prefix = $prefix;
		$this->status = $status;
		$this->languageID = $languageID;
		if ($this->board->enableRating != -1) $this->enableRating = $this->board->enableRating;
		
		parent::__construct();
	}
	
	/**
	 * @see ThreadList::initDefaultSQL()
	 */
	protected function initDefaultSQL() {
		parent::initDefaultSQL();
		
		$this->sqlConditions = "boardID = ".$this->board->boardID."
					AND isAnnouncement = 0";
		
		// days prune
		if ($this->daysPrune != 1000) {
			$this->sqlConditions .= " AND ((isSticky = 0 AND lastPostTime >= ".(TIME_NOW - ($this->daysPrune * 86400)).") OR (isSticky = 1 AND lastPostTime >= 0))";
		}
			
		// visible status
		if (!$this->board->getModeratorPermission('canReadDeletedThread') && !BOARD_ENABLE_DELETED_THREAD_NOTE) {
			$this->sqlConditionVisible .= ' AND isDeleted = 0';
		}
		if (!$this->board->getModeratorPermission('canEnableThread')) {
			$this->sqlConditionVisible .= ' AND isDisabled = 0';
		}
		$this->sqlConditions .= $this->sqlConditionVisible;
		
		// prefix filter
		if (!empty($this->prefix)) {
			$this->sqlConditions .= " AND prefix = '".($this->prefix != 'empty' ? escapeString($this->prefix) : '')."'";
		}
		
		// thread language
		if ($this->languageID != 0) {
			$this->sqlConditionLanguage = " AND thread.languageID = ".$this->languageID;
			$this->sqlConditions .= $this->sqlConditionLanguage;
		}
		else if (count(WCF::getSession()->getVisibleLanguageIDArray()) && (BOARD_THREADS_ENABLE_LANGUAGE_FILTER_FOR_GUESTS == 1 || WCF::getUser()->userID != 0)) {
			$this->sqlConditionLanguage = " AND thread.languageID IN (".implode(',', WCF::getSession()->getVisibleLanguageIDArray()).")";
			$this->sqlConditions .= $this->sqlConditionLanguage;
		}
		
		// status filter
		if (!empty($this->status)) {
			switch ($this->status) {
				case 'read':
					if (WCF::getUser()->userID) {
						$this->sqlConditions .= "	AND (thread.lastPostTime <= ".WCF::getUser()->getBoardVisitTime($this->board->boardID)."
										OR thread.lastPostTime <= IFNULL((
											SELECT	lastVisitTime
											FROM	wbb".WBB_N."_thread_visit visit
											WHERE	visit.threadID = thread.threadID
												AND visit.userID = ".WCF::getUser()->userID."
										), 0))";
					}
					break;
				case 'unread': 
					if (WCF::getUser()->userID) {
						$this->sqlConditions .= "	AND thread.lastPostTime > ".WCF::getUser()->getBoardVisitTime($this->board->boardID)."
										AND thread.lastPostTime > IFNULL((
											SELECT	lastVisitTime
											FROM	wbb".WBB_N."_thread_visit visit
											WHERE	visit.threadID = thread.threadID
												AND visit.userID = ".WCF::getUser()->userID."
										), 0)";
					}
					break;
				case 'open':
				case 'closed':
					$this->sqlConditions .= " AND thread.isClosed = ".($this->status == 'open' ? 0 : 1); 
					break;
				case 'deleted': 
					if ($this->board->getModeratorPermission('canReadDeletedThread')) $this->sqlConditions .= " AND thread.isDeleted = 1";
					break;
				case 'hidden':
					if ($this->board->getModeratorPermission('canEnableThread')) $this->sqlConditions .= " AND thread.isDisabled = 1";
					break;
				case 'done':
				case 'undone':
					if (MODULE_THREAD_MARKING_AS_DONE && $this->board->enableMarkingAsDone) {
						$this->sqlConditions .= " AND thread.isDone = ".($this->status == 'done' ? 1 : 0);
					}
					break;
			}
		}
	}
	
	/**
	 * @see ThreadList::readThreadIDs()
	 */
	protected function readThreadIDs() {
		// read announcements
		$sql = "SELECT 	announcement.threadID
			FROM 	wbb".WBB_N."_thread_announcement announcement
			".((!empty($this->sqlConditionVisible) || !empty($this->sqlConditionLanguage)) ? "LEFT JOIN wbb".WBB_N."_thread thread ON (thread.threadID = announcement.threadID)" : "")."
			WHERE 	announcement.boardID = ".$this->board->boardID.
			(!empty($this->sqlConditionVisible) ? $this->sqlConditionVisible : "")
			.(!empty($this->sqlConditionLanguage) ? $this->sqlConditionLanguage : "");
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($this->threadIDs)) $this->threadIDs .= ',';
			$this->threadIDs .= $row['threadID'];
		}
		
		parent::readThreadIDs();
	}
	
	/**
	 * @see ThreadList::readThreads()
	 */
	public function readThreads() {
		parent::readThreads();
		
		foreach ($this->threads as $key => $thread) {
			if ($thread->lastPostTime > $this->maxLastPostTime) {
				$this->maxLastPostTime = $thread->lastPostTime;
			}
			
			if ($thread->isSticky || $thread->isAnnouncement) {
				$this->topThreads[] = $thread;
				unset($this->threads[$key]);
				if ($thread->isNew()) $this->newTopThreads++;
			}
			else {
				if ($thread->isNew()) $this->newThreads++;
			}
		}
	}
}
?>