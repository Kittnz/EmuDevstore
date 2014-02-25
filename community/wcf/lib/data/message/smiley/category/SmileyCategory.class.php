<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a smiley category.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	data.message.smiley.category
 * @category 	Community Framework
 */
class SmileyCategory extends DatabaseObject {
	/**
	 * Creates a new SmileyCategory object.
	 * 
	 * @param	integer		$smileyCategoryID
	 * @param	array<mixed>	$row
	 */
	public function __construct($smileyCategoryID, $row = null) {
		if ($smileyCategoryID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_smiley_category
				WHERE	smileyCategoryID = ".$smileyCategoryID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns the title of this category.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->title;
	}
	
	/**
	 * Returns a list of smiley categories.
	 * 
	 * @return	array<SmileyCategory>
	 */
	public static function getSmileyCategories() {
		$categories = array();
		$sql = "SELECT		smiley_category.*,
					(SELECT COUNT(*) AS count FROM wcf".WCF_N."_smiley WHERE smileyCategoryID = smiley_category.smileyCategoryID) AS smileys
			FROM		wcf".WCF_N."_smiley_category smiley_category
			ORDER BY	smiley_category.showOrder, smiley_category.title";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['title'] = WCF::getLanguage()->get($row['title']);
			$categories[$row['smileyCategoryID']] = new SmileyCategory(null, $row);
		}

		return $categories;
	}
}
?>