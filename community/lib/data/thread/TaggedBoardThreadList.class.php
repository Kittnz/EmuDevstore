<?php
require_once(WBB_DIR.'lib/data/thread/BoardThreadList.class.php');

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
class TaggedBoardThreadList extends BoardThreadList {
	/**
	 * tag id
	 * 
	 * @var	integer
	 */
	public $tagID = 0;
	
	/**
	 * taggable object
	 * 
	 * @var	Taggable
	 */
	public $taggable = null;

	/**
	 * Creates a new TaggedBoardThreadList object.
	 */
	public function __construct($tagID, Board $board, $daysPrune = 100, $prefix = '', $status = '', $languageID = 0) {
		$this->tagID = $tagID;
		$this->taggable = TagEngine::getInstance()->getTaggable('com.woltlab.wbb.thread');
		parent::__construct($board, $daysPrune, $prefix, $status, $languageID);
	}
	
	/**
	 * Counts threads.
	 * 
	 * @return	integer
	 */
	public function countThreads() {
		if (!empty($this->sqlConditions)) {
			$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_tag_to_object tag_to_object,
					wbb".WBB_N."_thread thread
				WHERE	tag_to_object.tagID = ".$this->tagID."
					AND tag_to_object.taggableID = ".$this->taggable->getTaggableID()."
					AND thread.threadID = tag_to_object.objectID
					AND ".$this->sqlConditions;
		}
		else {
			$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_tag_to_object
				WHERE	tagID = ".$this->tagID."
					AND taggableID = ".$this->taggable->getTaggableID();
		}
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see DatabaseObjectList::readObjects()
	 */
	protected function readThreadIDs() {
		$sql = "SELECT	".$this->sqlSelectRating."
				thread.threadID
			FROM	wcf".WCF_N."_tag_to_object tag_to_object,
				wbb".WBB_N."_thread thread
			 WHERE	tag_to_object.tagID = ".$this->tagID."
				AND tag_to_object.taggableID = ".$this->taggable->getTaggableID()."
				AND thread.threadID = tag_to_object.objectID
				".(!empty($this->sqlConditions) ? "AND ".$this->sqlConditions : '')."
				".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$result = WCF::getDB()->sendQuery($sql, $this->limit, $this->offset);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($this->threadIDs)) $this->threadIDs .= ',';
			$this->threadIDs .= $row['threadID'];
		}
	}
}
?>