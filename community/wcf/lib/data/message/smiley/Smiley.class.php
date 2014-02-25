<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a smiley in a message.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	data.message.smiley
 * @category 	Community Framework
 */
class Smiley extends DatabaseObject {
	/**
	 * Creates a new Smiley object.
	 */
	public function __construct($smileyID, $row = null) {
		if ($row === null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_smiley
				WHERE	smileyID = ".$smileyID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns the url to this smiley.
	 * 
	 * @return	string
	 */
	public function getURL() {
		return RELATIVE_WCF_DIR.$this->smileyPath;
	}
	
	/**
	 * Returns the html code to display this smiley.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return '<img src="'.$this->getURL().'" alt="'.StringUtil::encodeHTML($this->smileyCode).'" />';
	}
	
	/**
	 * Returns true, if this smiley is marked in the active session.
	 */
	public function isMarked() {
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedSmileys'])) {
			if (in_array($this->smileyID, $sessionVars['markedSmileys'])) return 1;
		}
		
		return 0;
	}
}
?>