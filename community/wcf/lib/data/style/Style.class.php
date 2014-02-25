<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a style.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.system.style
 * @subpackage	data.style
 * @category 	Community Framework
 */
class Style extends DatabaseObject {
	/**
	 * Creates a new Style object.
	 * 
	 * @param	integer		$styleID
	 * @param	array		$row
	 */
	public function __construct($styleID, $row = null) {
		if ($styleID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_style
				WHERE	styleID = ".$styleID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns the name of this style.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->styleName;
	}
	
	/**
	 * Returns a list of styles.
	 *
	 * @return 	array<Style>
	 */
	public static function getStyles() {
		$styles = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_style
			ORDER BY	styleName";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$styles[$row['styleID']] = new Style(null, $row);
		}
		
		return $styles;
	}
	
	/**
	 * Returns a new ActiveStyle object.
	 * 
	 * @param	ActiveStyle
	 */
	public function getActiveStyle() {
		require_once(WCF_DIR.'lib/system/style/ActiveStyle.class.php');
		return new ActiveStyle(null, $this->data);
	}
}
?>