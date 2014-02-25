<?php
// wcf imports
require_once(WCF_DIR.'lib/data/tag/TagCloud.class.php');

/**
 * his class holds a list of tags that can be used for creating a tag cloud.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.board
 * @category 	Burning Board
 */
class BoardTagCloud extends TagCloud {
	/**
	 * Contructs a new BoardTagCloud.
	 *
	 * @param	integer		$boardID
	 * @param	array<integer>	$languageIDArray
	 */
	public function __construct($boardID, $languageIDArray = array()) {
		$this->boardID = $boardID;
		$this->languageIDArray = $languageIDArray;
		if (!count($this->languageIDArray)) $this->languageIDArray = array(0);
		
		// init cache
		$this->cacheName = 'tagCloud-'.$this->boardID.'-'.implode(',', $this->languageIDArray);
		$this->loadCache();
	}
	
	/**
	 * Loads the tag cloud cache.
	 */
	public function loadCache() {
		if ($this->tags !== null) return;

		// get cache
		WCF::getCache()->addResource($this->cacheName, WBB_DIR.'cache/cache.tagCloud-'.$this->boardID.'-'.StringUtil::getHash(implode(',', $this->languageIDArray)).'.php', WBB_DIR.'lib/system/cache/CacheBuilderBoardTagCloud.class.php', 0, 86400);
		$this->tags = WCF::getCache()->get($this->cacheName);
	}
}
?>