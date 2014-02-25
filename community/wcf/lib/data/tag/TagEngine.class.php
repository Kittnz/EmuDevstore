<?php
// wcf imports
require_once(WCF_DIR.'lib/data/tag/Tag.class.php');
require_once(WCF_DIR.'lib/data/tag/Taggable.class.php');
require_once(WCF_DIR.'lib/data/tag/Tagged.class.php');
require_once(WCF_DIR.'lib/data/tag/TagCloud.class.php');

/**
 * Manages the tagging of objects.
 * 
 * @author 	Arian Glander
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	data.tag
 * @category 	Community Framework
 */
class TagEngine {
	/**
	 * list of taggable data
	 * 
	 * @var	array
	 */
	protected $taggablesData = array();
	
	/**
	 * list of taggables
	 * 
	 * @var	array<Taggable>
	 */
	protected $taggables = null;
	
	/**
	 * instance of TagEngine
	 * 
	 * @var	TagEngine
	 */
	protected static $instance = null;
	
	/**
	 * Get an instance of the TagEngine
	 * 
	 * @return TagEngine to get
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new TagEngine();
		}
		return self::$instance;
	}
	
	/**
	 * Hidden constructor, please call TagEngine::getInstance() instead
	 */
	protected function __construct() {}
	
	/**
	 * Forbidden method, only one instance can exist
	 */
	private function __clone() {}
	
	/**
	 * Loads the taggable objects.
	 */
	protected function loadTaggables() {
		if ($this->taggables !== null) return;
		
		// get cache
		WCF::getCache()->addResource('taggables-'.PACKAGE_ID, WCF_DIR.'cache/cache.taggables-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderTaggable.class.php');
		$this->taggablesData = WCF::getCache()->get('taggables-'.PACKAGE_ID);
		
		// get objects
		$this->taggables = array();
		
		foreach ($this->taggablesData as $type) {
			// calculate class path
			$path = '';
			if (empty($type['packageDir'])) {
				$path = WCF_DIR;
			}
			else {						
				$path = FileUtil::getRealPath(WCF_DIR.$type['packageDir']);
			}
			
			// include class file
			if (!file_exists($path.$type['classPath'])) {
				throw new SystemException("unable to find class file '".$path.$type['classPath']."'", 11000);
			}
			require_once($path.$type['classPath']);
			
			// create instance
			$className = StringUtil::getClassName($type['classPath']);
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'", 11001);
			}
			$this->taggables[$type['taggableID']] = new $className($type['taggableID'], $type['name']);
		}
	}
	
	/**
	 * Gets a cached taggable type by name.
	 * 
	 * @param 	string		$name		the name of the taggable
	 * @return	Taggable 			the taggable to get
	 */
	public function getTaggable($name) {
		$this->loadTaggables();
		
		foreach ($this->taggables as $taggable) {
			if ($name == $taggable->getName())
			return $taggable;
		}
		
		return null;
	}
	
	/**
	 * Gets a cached taggable type by id.
	 * 
	 * @param 	string		$ID	the id of the taggable
	 * @return	Taggable		the taggable to get
	 */
	public function getTaggableByID($ID) {
		$this->loadTaggables();
		
		if (isset($this->taggables[$ID]))
			return $this->taggables[$ID];
		
		return null;
	}
	
	/**
	 * Gets a list of all taggables.
	 * 
	 * @return	array<Taggable>
	 */
	public function getTaggables() {
		$this->loadTaggables();
		
		return $this->taggables;
	}
	
	/**
	 * Gets a list of grouped tagged objects by tagID.
	 * 
	 * @param 	integer 	$tagID 	the tagID of the tag that is bound to several objects
	 * @param	integer		$limit
	 * @return 	array			array with objects of type Tagged
	 */
	public function getGroupedTaggedObjectsByTagID($tagID, $limit = 5) {
		// get taggables
		$this->loadTaggables();
		
		// get objects
		$taggedObjects = array();
		foreach ($this->taggables as $taggable) {
			$results = $taggable->getObjectsByTagID($tagID, $limit);
			if (count($results)) {
				$taggedObjects[$taggable->getTaggableID()] = $results;
			}
		}
		
		return $taggedObjects;
	}
	
	/**
	 * Gets the tags of a specific tagged object.
	 * 
	 * @param 	Tagged 		$object		object labelled with tags
	 * @return 	array 				array holding objects of type Tag
	 */
	public function getTagsByTaggedObject(Tagged $object, $languageIDArray = array()) {
		if (!count($languageIDArray)) $languageIDArray = array(0);
		$tags = array();
		$sql = "SELECT		tag.tagID, tag.name, tag.languageID
			FROM		wcf".WCF_N."_tag_to_object object
			LEFT JOIN 	wcf".WCF_N."_tag tag
			ON 		(tag.tagID = object.tagID)
			WHERE 	 	object.taggableID = ".$object->getTaggable()->getTaggableID()."
					AND object.languageID IN (".implode(',', $languageIDArray).")
					AND object.objectID = ".$object->getObjectID()."
			ORDER BY	name";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$tags[] = new Tag(null, $row);
		}
		return $tags;
	}
	
	/**
	 * Gets a tag by id.
	 * 
	 * @param 	integer		$id	id of tag
	 * @return 	Tag 			the tag to get
	 */
	public function getTagByID($id) {
		$tag = new Tag($id);
		if ($tag->tagID) {
			return $tag;
		}
		
		return null;
	}
	
	/**
	 * Gets a tag by name.
	 * 
	 * @param 	string	$name	name of tag
	 * @return 	Tag 		the tag to get
	 */
	public function getTagByName($name, $languageIDArray = array()) {
		if (!count($languageIDArray)) $languageIDArray = array(0);
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_tag
			WHERE 	languageID IN (".implode(',', $languageIDArray).")
				AND name = '".escapeString($name)."'";
		$row = WCF::getDB()->getFirstRow($sql);
		if (!empty($row['tagID'])) {
			return new Tag(null, $row);
		}
		
		return null;
	}
	
	/**
	 * Gets tags by name.
	 * 
	 * @param 	array<string>	$name
	 * @return 	array<Tag>
	 */
	public function getTagsByName($name, $languageIDArray = array()) {
		if (!count($languageIDArray)) $languageIDArray = array(0);
		$tags = array();
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_tag
			WHERE 	languageID IN (".implode(',', $languageIDArray).")
				AND name IN ('".implode("','", array_map('escapeString', $name))."')";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$tags[$row['tagID']] = new Tag(null, $row);
		}
		return $tags;
	}
	
	/**
	 * Gets tags by id.
	 * 
	 * @param 	array<integer>	$id
	 * @return 	array<Tag>
	 */
	public function getTagsByID($id) {
		$tags = array();
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_tag
			WHERE 	tagID IN (".implode(',', $id).")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$tags[$row['tagID']] = new Tag(null, $row);
		}
		return $tags;
	}
	
	/**
	 * Adds tags to a tagged object.
	 * 
	 * @param 	array 		$tags 		array holding tag names
	 * @param 	Tagged 		$object 	object that should be tagged
	 */
	public function addTags($tags, Tagged $object, $languageID = 0) {
		$tagIDs = array();
		foreach ($tags as $tag) {
			if (empty($tag)) continue;
			$tagID = Tag::test($tag, $languageID);
			if (!$tagID) $tagID = Tag::insert($tag, $languageID);
			$tagIDs[] = $tagID;
		}
		$tagIDs = array_unique($tagIDs);
		
		$sql = "INSERT INTO	wcf".WCF_N."_tag_to_object
					(objectID, tagID, taggableID, time, languageID)
			VALUES ";
		foreach ($tagIDs as $tagID) {
			$sql .= "(" . $object->getObjectID() . ", " . $tagID . ", " . $object->getTaggable()->getTaggableID() . ", " . TIME_NOW . ", ".$languageID."),";
		}
		$sql = StringUtil::substring($sql, 0, StringUtil::length($sql) - 1);
		$result = WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes all Tags assigned to these tagged object.
	 *
	 * @param 	Tagged		$object 	object whose assigned to tags should be deleted
	 */
	public function deleteObjectTags(Tagged $object, $languageIDArray = array(), $deleteUnassignedTags = true) {
		if (!count($languageIDArray)) $languageIDArray = array(0);
		$sql = "DELETE FROM 	wcf".WCF_N."_tag_to_object
			WHERE 		taggableID = ".$object->getTaggable()->getTaggableID()."
					AND languageID IN (".implode(',', $languageIDArray).")
					AND objectID = ".$object->getObjectID();
		WCF::getDB()->sendQuery($sql);
		if ($deleteUnassignedTags) $this->deleteUnassignedTags();
	}
	
	/**
	 * Deletes all Tags who are not assigned to an Object.
	 */
	public function deleteUnassignedTags() {
		//get unassigned TagIDs
		$sql = "SELECT		COUNT(object.tagID) AS counter, tag.tagID
			FROM 		wcf".WCF_N."_tag tag
			LEFT JOIN 	wcf".WCF_N."_tag_to_object object
			ON 		(object.tagID = tag.tagID)
			GROUP BY 	tag.tagID
			HAVING		counter = 0";
		$result = WCF::getDB()->sendQuery($sql);
		$tagIDs = array();
		//make TagID List
		while ($row = WCF::getDB()->fetchArray($result)) {
			$tagIDs[] = $row['tagID'];
		}
		//delete Tags
		if (count($tagIDs) > 0) {
			Tag::deleteByID($tagIDs);
		}
	}

	/**
	 * Gets a list of taggables by tagID.
	 * 
	 * @param 	integer 	$tagID 	the tagID of the tag that is bound to several objects
	 * @return 	array<Taggable>		array with objects of type Taggable
	 */
	public function getTaggablesByTagID($tagID) {
		// get taggables
		$this->loadTaggables();
		
		// get counts
		$taggables = array();
		foreach ($this->taggables as $taggable) {
			$count = $taggable->countObjectsByTagID($tagID);
			if ($count) {
				$taggables[] = $taggable;
			}
		}
		
		return $taggables;
	}
}
?>