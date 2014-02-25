<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObjectList.class.php');
require_once(WCF_DIR.'lib/data/tag/Tag.class.php');
require_once(WCF_DIR.'lib/data/tag/TagEngine.class.php');

/**
 * Represents a list of tags.
 * 
 * @author 	Ronny Lau
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	data.tag
 * @category 	Community Framework
 */
class TagList extends DatabaseObjectList {
	/**
	 * list of Tags
	 *
	 * @var array<Tag>
	 */
	public $tags = array();

	/**
	 * max value of tag counter
	 *
	 * @var integer
	 */
	public $maxCounter = 1;

	/**
	 * min value of tag counter
	 *
	 * @var integer
	 */
	public $minCounter = 4294967295;

	/**
	 * sql limit
	 *
	 * @var integer
	 */
	public $sqlLimit = 50;
	
	/**
	 * sql order by statement
	 *
	 * @var	string
	 */
	public $sqlOrderBy = 'counter DESC';
	
	/**
	 * list of taggable ids
	 * 
	 * @var	array<integer>
	 */
	public $taggableIDArray = array();
	
	/**
	 * list of language ids
	 * 
	 * @var	array<integer>
	 */
	public $languageIDArray = array();
	
	/**
	 * Creates a new TagList object.
	 * 
	 * @param	array<string>		$taggables
	 */
	public function __construct($taggables = array(), $languageIDArray = array()) {
		// language ids
		$this->languageIDArray = $languageIDArray;
		if (!count($this->languageIDArray)) {
			$this->languageIDArray = array(0);
		}
		
		// taggable ids
		if (count($taggables)) {
			// get taggable ids
			foreach ($taggables as $taggable) {
				if (($taggableObj = TagEngine::getInstance()->getTaggable($taggable)) !== null) {
					$this->taggableIDArray[] = $taggableObj->getTaggableID();
				}
			}
		}
		else {
			// get ids of all taggables in this environment
			foreach (TagEngine::getInstance()->getTaggables() as $taggableObj) {
				$this->taggableIDArray[] = $taggableObj->getTaggableID();
			}
		}
	}
	
	/**
	 * @see DatabaseObjectList::countObjects()
	 */
	public function countObjects() {
		return null;
	}
	
	/**
	 * Gets the tag ids.
	 */
	public function getTagsIDArray() {
		$tagIDArray = array();
		$sql = "SELECT		COUNT(*) AS counter, object.tagID
			FROM 		wcf".WCF_N."_tag_to_object object
			WHERE 		object.taggableID IN (".implode(',', $this->taggableIDArray).")
					AND object.languageID IN (".implode(',', $this->languageIDArray).")
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
	
	/**
	 * @see DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		$tagIDArray = $this->getTagsIDArray();
		
		// get tags
		if (count($tagIDArray)) {
			$sql = "SELECT		name, tagID
				FROM		wcf".WCF_N."_tag
				WHERE		tagID IN (".implode(',', array_keys($tagIDArray)).")
				ORDER BY	name";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$row['counter'] = $tagIDArray[$row['tagID']];
				$this->tags[StringUtil::toLowerCase($row['name'])] = new Tag(null, $row);
			}

			// assign sizes
			foreach ($this->tags as $tag) {
				$tag->setSize($this->calculateSize($tag->getCounter()));
			}
		}
	}
	
	/**
	 * @see DatabaseObjectList::getObjects()
	 */
	public function getObjects() {
		return $this->tags;
	}
	
	/**
	 * Calculate the size of the tag in a weighted list
	 *
	 * @param	integer 	$counter 	the number of times a tag has been used
	 * @return	double 				the size to calculate
	 */
	private function calculateSize($counter) {
		$maxSize = 250;
		$minSize = 80;

		if ($this->maxCounter == $this->minCounter) {
			return 100;
		}
		else {
			return $minSize + floor(log($this->maxCounter - $this->minCounter - ($this->maxCounter - $counter) + 1) / log($this->maxCounter - $this->minCounter + 1) * ($maxSize - $minSize));
			//return ($maxSize - $minSize) / ($this->maxCounter - $this->minCounter) * $counter + $minSize - (($maxSize - $minSize) / ($this->maxCounter - $this->minCounter)) * $this->minCounter;
		}
	}
}
?>