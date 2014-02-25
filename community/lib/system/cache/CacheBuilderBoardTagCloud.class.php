<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilderTagCloud.class.php');

/**
 * Caches the tag cloud of a board.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.cache
 * @category 	Burning Board
 */
class CacheBuilderBoardTagCloud extends CacheBuilderTagCloud {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $boardID, $languageIDs) = explode('-', $cacheResource['cache']);
		$data = array();

		// get taggable
		require_once(WCF_DIR.'lib/data/tag/TagEngine.class.php');
		$taggable = TagEngine::getInstance()->getTaggable('com.woltlab.wbb.thread');
		
		// get tag ids
		$tagIDArray = array();
		$sql = "SELECT		COUNT(*) AS counter, object.tagID
			FROM 		wbb".WBB_N."_thread thread,
					wcf".WCF_N."_tag_to_object object
			WHERE 		thread.boardID = ".$boardID."
					AND object.taggableID = ".$taggable->getTaggableID()."
					AND object.languageID IN (".$languageIDs.")
					AND object.objectID = thread.threadID
			GROUP BY 	object.tagID
			ORDER BY 	counter DESC";
		$result = WCF::getDB()->sendQuery($sql, 500);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$tagIDArray[$row['tagID']] = $row['counter'];
		}
			
		// get tags
		if (count($tagIDArray)) {
			$sql = "SELECT		name, tagID
				FROM		wcf".WCF_N."_tag
				WHERE		tagID IN (".implode(',', array_keys($tagIDArray)).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$row['counter'] = $tagIDArray[$row['tagID']];
				$this->tags[StringUtil::toLowerCase($row['name'])] = new Tag(null, $row);
			}

			// sort by counter
			uasort($this->tags, array('self', 'compareTags'));
						
			$data = $this->tags;
		}
		
		return $data;
	}
}
?>