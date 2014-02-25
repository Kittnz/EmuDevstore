<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/smiley/category/SmileyCategory.class.php');

/**
 * Provides functions to create and edit the data of a smiley category.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	data.message.smiley.category
 * @category 	Community Framework
 */
class SmileyCategoryEditor extends SmileyCategory {
	/**
	 * Deletes this smiley category.
	 */
	public function delete() {
		// delete database entry
		$sql = "DELETE FROM	wcf".WCF_N."_smiley_category
			WHERE		smileyCategoryID = ".$this->smileyCategoryID;
		WCF::getDB()->sendQuery($sql);
		
		// update smileys
		$sql = "UPDATE	wcf".WCF_N."_smiley
			SET	smileyCategoryID = 0
			WHERE	smileyCategoryID = ".$this->smileyCategoryID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Creates a new smiley category.
	 * 
	 * @param	string		$title
	 * @param	string		$showOrder
	 * @return	SmileyCategoryEditor
	 */
	public static function create($title, $showOrder) {
		$sql = "INSERT INTO	wcf".WCF_N."_smiley_category
					(title, showOrder)
			VALUES		('".escapeString($title)."', ".$showOrder.")";
		WCF::getDB()->sendQuery($sql);
		$smileyCategoryID = WCF::getDB()->getInsertID("wcf".WCF_N."_smiley_category", 'smileyCategoryID');
		
		return new SmileyCategoryEditor($smileyCategoryID);
	}
	
	/**
	 * Updates this smiley category.
	 * 
	 * @param	string		$title
	 * @param	string		$showOrder
	 */
	public function update($title, $showOrder) {
		$sql = "UPDATE	wcf".WCF_N."_smiley_category
			SET	title = '".escapeString($title)."',
				showOrder = ".$showOrder."
			WHERE	smileyCategoryID = ".$this->smileyCategoryID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Enables this smiley category.
	 */
	public function enable() {
		$sql = "UPDATE	wcf".WCF_N."_smiley_category
			SET	disabled = 0
			WHERE	smileyCategoryID = ".$this->smileyCategoryID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Disables this smiley category.
	 */
	public function disable() {
		$sql = "UPDATE	wcf".WCF_N."_smiley_category
			SET	disabled = 1
			WHERE	smileyCategoryID = ".$this->smileyCategoryID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>