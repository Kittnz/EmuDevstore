<?php
/**
 * Represents a Tag.
 * 
 * @author 	Arian Glander
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	data.tag
 * @category 	Community Framework
 */
class Tag extends DatabaseObject {
	/**
	 * Size of tag in a weighted list
	 *
	 * @var  double
	 */
	protected $size;

	/**
	 * Contructs a Tag.
	 *
	 * @param 	integer 	$tagID 		id of tag
	  * @param 	array 	$row			resultset with tag data form database
	 */
	public function __construct($tagID, $row = null) {
		if ($tagID !== null) {
			$sql = "SELECT	*
				FROM 	wcf".WCF_N."_tag
				WHERE 	tagID = ".$tagID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		parent::__construct($row);
	}
	
	/**
	 * Returns the name of this tag.
	 *
	 * @return	string
	 */
	public function __toString() {
		return $this->name;	
	}

	/**
	 * Gets the name of the Tag.
	 *
	 * @return 	string 		name of the tag
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Gets the id of the Tag.
	 *
	 * @return 	integer		id of the tag
	 */
	public function getID() {
		return $this->tagID;
	}

	/**
	 * Gets the counter of the Tag.
	 *
	 * @return 	integer		counter of the tag
	 */
	public function getCounter() {
		return $this->counter;
	}

	/**
	 * Gets the language id of the tag.
	 *
	 * @return 	integer		language id
	 */
	public function getLanguageID() {
		return $this->languageID;
	}

	/**
	 * Sets the size of the tag.
	 *
	 * @param 	double		$size	size of tag
	 */
	public function setSize($size) {
		$this->size = $size;
	}

	/**
	 * Gets the size of the tag.
	 *
	 * @return 	double 		the size to get
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * Checks whether a tag with the given name already exists in the database.
	 *
	 * @param	string		$name
	 * @param	integer		$languageID
	 * @return	mixed		false, if no tag with the given name exist or the id of the existing tag
	 */
	public static function test($name, $languageID) {
		$sql = "SELECT	tagID
			FROM 	wcf".WCF_N."_tag
			WHERE 	languageID = ".$languageID."
				AND name = '".escapeString($name)."'";
		$result = WCF::getDB()->getFirstRow($sql);
		if (isset($result['tagID'])) return $result['tagID'];
		return false;
	}

	/**
	 * Inserts the tag word in the database.
	 *
	 * @param	string 		$name		the name of the word to be inserted
	 * @param	integer		$languageID
	 * @return 	integer 			the new id of the word
	 */
	public static function insert($name, $languageID) {
		$sql = "INSERT INTO	wcf".WCF_N."_tag
					(name, languageID)
			VALUES		('".escapeString($name)."', ".$languageID.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID("wcf".WCF_N."_tag", "tagID");
	}
	
	/**
	 * Deletes tags by name.
	 *
	 * @param	string		$name
	 * @param	integer		$languageID
	 */
	public static function deleteByName($name, $languageID) {
		$sql = "DELETE FROM	wcf".WCF_N."_tag
			WHERE 		languageID = ".$languageID."
					AND name = '".escapeString($name)."'";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes tags by ids.
	 *
	 * @param	array		$tagIDArray
	 */
	public static function deleteByID($tagIDArray) {
		if (!is_array($tagIDArray)) $tagIDArray = array($tagIDArray);
		
		$sql = "DELETE FROM	wcf".WCF_N."_tag
			WHERE 		tagID IN (".implode(',', $tagIDArray).")";
		WCF::getDB()->sendQuery($sql);
	}
}
?>