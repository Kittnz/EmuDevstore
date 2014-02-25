<?php
// wcf imports
require_once(WCF_DIR.'lib/data/page/location/Location.class.php');
require_once(WCF_DIR.'lib/data/tag/Tag.class.php');

/**
 * TaggedObjectsLocation is an implementation of Location for the list of tagged objects.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.search.tagging
 * @subpackage	data.page.location
 * @category 	Community Framework
 */
class TaggedObjectsLocation implements Location {
	/**
	 * list of tag ids
	 *
	 * @var	array<integer>
	 */
	public $cachedTagIDArray = array();
	
	/**
	 * list of tags
	 * 
	 * @var	array<Tag>
	 */
	public $tags = null;
	
	/**
	 * @see Location::cache()
	 */
	public function cache($location, $requestURI, $requestMethod, $match) {
		$this->cachedTagIDArray[] = $match[1];
	}
	
	/**
	 * @see Location::get()
	 */
	public function get($location, $requestURI, $requestMethod, $match) {
		if ($this->tags == null) {
			$this->readTags();
		}
		
		$tagID = $match[1];
		if (!isset($this->tags[$tagID])) {
			return '';
		}
		
		return WCF::getLanguage()->get($location['locationName'], array('$tag' => '<a href="index.php?page=TaggedObjects&amp;tagID='.$tagID.SID_ARG_2ND.'">'.StringUtil::encodeHTML($this->tags[$tagID]->getName()).'</a>'));
	}
	
	/**
	 * Gets tags.
	 */
	protected function readTags() {
		$this->tags = array();
		
		if (!count($this->cachedTagIDArray)) {
			return;
		}
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_tag
			WHERE	tagID IN (".implode(',', $this->cachedTagIDArray).")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->tags[$row['tagID']] = new Tag(null, $row);
		}
	}
}
?>