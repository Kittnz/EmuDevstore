<?php
// wcf imports
require_once(WCF_DIR.'lib/data/tag/TagList.class.php');

/**
 * Represents a list of tags.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.board
 * @category 	Burning Board
 */
class BoardTagList extends TagList {
	/**
	 * board id
	 *
	 * @var	integer
	 */
	public $boardID = 0;
	
	/**
	 * Creates a new BoardTagList object.
	 *
	 * @param	integer		$boardID
	 * @param	array<integer>	$languageIDArray
	 */
	public function __construct($boardID, $languageIDArray = array()) {
		parent::__construct(array('com.woltlab.wbb.thread'), $languageIDArray);
		$this->boardID = $boardID;
	}
	
	/**
	 * Gets the tag ids.
	 */
	public function getTagsIDArray() {
		$tagIDArray = array();
		$sql = "SELECT		COUNT(*) AS counter, object.tagID
			FROM 		wbb".WBB_N."_thread thread,
					wcf".WCF_N."_tag_to_object object
			WHERE 		thread.boardID = ".$this->boardID."
					AND object.taggableID IN (".implode(',', $this->taggableIDArray).")
					AND object.languageID IN (".implode(',', $this->languageIDArray).")
					AND object.objectID = thread.threadID
			GROUP BY 	object.tagID
			".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$result = WCF::getDB()->sendQuery($sql, $this->sqlLimit, $this->sqlOffset);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['counter'] > $this->maxCounter) $this->maxCounter = $row['counter'];
			if ($row['counter'] < $this->minCounter) $this->minCounter = $row['counter'];
			$tagIDArray[$row['tagID']] = $row['counter'];
		}
		
		return $tagIDArray;
	}
}
?>