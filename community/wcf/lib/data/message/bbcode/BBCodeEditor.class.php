<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Provides functions to create and edit the data of a bbcode.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	data.message.bbcode
 * @category 	Community Framework
 */
class BBCodeEditor extends DatabaseObject {
	protected $attributes = null;
	
	/**
	 * Creates a new BBCodeEditor object.
	 * 
	 * @param	array		$row
	 * @param	integer		$bbcodeID
	 */
	public function __construct($bbcodeID, $row = null) {
		if ($bbcodeID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_bbcode
				WHERE	bbcodeID = ".$bbcodeID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns the attributes of this bbcode.
	 * 
	 * @return	array
	 */
	public function getAttributes() {
		if ($this->attributes === null) {
			$this->attributes = array();
			
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_bbcode_attribute
				WHERE		bbcodeID = ".$this->bbcodeID."
				ORDER BY	attributeNo";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->attributes[] = $row;
			}
		}
		
		return $this->attributes;
	}
	
	/**
	 * Updates the data of this bbcode.
	 */
	public function update($bbcodeTag, $htmlOpen, $htmlClose, $textOpen, $textClose, $allowedChildren, $className, $sourceCode, $attributes, $wysiwyg = 0, $wysiwygIcon = '', $disabled = 0) {
		// update bbcode
		$sql = "UPDATE	wcf".WCF_N."_bbcode
			SET	bbcodeTag = '".escapeString($bbcodeTag)."', htmlOpen = '".escapeString($htmlOpen)."',
				htmlClose = '".escapeString($htmlClose)."', textOpen = '".escapeString($textOpen)."',
				textClose = '".escapeString($textClose)."', allowedChildren = '".escapeString($allowedChildren)."',
				className = '".escapeString($className)."', sourceCode = ".$sourceCode.", 
				wysiwyg = ".$wysiwyg.", wysiwygIcon = '".escapeString($wysiwygIcon)."',
				disabled = ".$disabled."
			WHERE	bbcodeID = ".$this->bbcodeID;
		WCF::getDB()->sendQuery($sql);
		
		// update attributes
		$this->setAttributes($attributes);
	}
	
	/**
	 * Sets the attributes of this bbcode.
	 */
	public function setAttributes($attributes) {
		// delete old attributes
		$this->deleteAttributes();
		
		// save new attributes
		$i = 0;
		foreach ($attributes as $attribute) {
			$sql = "INSERT INTO	wcf".WCF_N."_bbcode_attribute
						(bbcodeID, attributeNo, attributeHtml, attributeText, validationPattern, required, useText)
				VALUES		(".$this->bbcodeID.", ".$i.", '".escapeString($attribute['attributeHtml'])."',
						'".escapeString($attribute['attributeText'])."', '".escapeString($attribute['validationPattern'])."',
						".$attribute['required'].", ".$attribute['useText'].")";
			WCF::getDB()->sendQuery($sql);
			$i++;
		}
	}
	
	/**
	 * Deletes the attributes of this bbcode.
	 */
	public function deleteAttributes() {
		$sql = "DELETE FROM	wcf".WCF_N."_bbcode_attribute
			WHERE		bbcodeID = ".$this->bbcodeID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes this bbcode.
	 */
	public function delete() {
		// delete attributes
		$this->deleteAttributes();
		
		// delete bbcode
		$sql = "DELETE FROM	wcf".WCF_N."_bbcode
			WHERE		bbcodeID = ".$this->bbcodeID;
		WCF::getDB()->sendQuery($sql);
		
		// reset cache
		self::clearCache();
	}
	
	/**
	 * Enables this bbcode.
	 */
	public function enable() {
		$sql = "UPDATE	wcf".WCF_N."_bbcode
			SET	disabled = 0			
			WHERE	bbcodeID = ".$this->bbcodeID;
		WCF::getDB()->sendQuery($sql);
		
		// reset cache
		self::clearCache();
	}
	
	/**
	 * Disables this bbcode.
	 */
	public function disable() {
		$sql = "UPDATE	wcf".WCF_N."_bbcode
			SET	disabled = 1			
			WHERE	bbcodeID = ".$this->bbcodeID;
		WCF::getDB()->sendQuery($sql);
		
		// reset cache
		self::clearCache();
	}

	/**
	 * Creates a new bbcode.
	 * 
	 * @return	BBCodeEditor
	 */
	public static function create($bbcodeTag, $htmlOpen, $htmlClose, $textOpen, $textClose, $allowedChildren, $className, $sourceCode, $attributes, $wysiwyg = 0, $wysiwygIcon = '', $disabled = 0) {
		// create bbcode
		$sql = "INSERT INTO	wcf".WCF_N."_bbcode
					(packageID, bbcodeTag, htmlOpen, htmlClose, textOpen, textClose, allowedChildren, className, sourceCode, wysiwyg, wysiwygIcon, disabled)
			VALUES		(".PACKAGE_ID.", '".escapeString($bbcodeTag)."', '".escapeString($htmlOpen)."', '".escapeString($htmlClose)."',
					'".escapeString($textOpen)."', '".escapeString($textClose)."', '".escapeString($allowedChildren)."',
					'".escapeString($className)."', ".$sourceCode.", ".$wysiwyg.", '".escapeString($wysiwygIcon)."', ".$disabled.")";
		WCF::getDB()->sendQuery($sql);
		
		// get bbcode
		$bbcodeID = WCF::getDB()->getInsertID();
		$bbcode = new BBCodeEditor($bbcodeID);
		
		// save attributes
		$bbcode->setAttributes($attributes);
		
		return $bbcode;
	}
	
	/**
	 * Resets the bbcode cache.
	 */
	public static function clearCache() {
		// delete cache
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.bbcodes.php');
	}
}
?>