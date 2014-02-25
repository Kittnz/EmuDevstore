<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/smiley/Smiley.class.php');

/**
 * Provides functions to create and edit the data of a smiley.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	data.message.smiley
 * @category 	Community Framework
 */
class SmileyEditor extends Smiley {
	/**
	 * Deletes this smiley.
	 */
	public function delete() {
		$this->removePositions();
		
		// delete database entry
		$sql = "DELETE FROM	wcf".WCF_N."_smiley
			WHERE		smileyID = ".$this->smileyID;
		WCF::getDB()->sendQuery($sql);
		
		// delete file
		@unlink(WCF_DIR . $this->smileyPath);
	}
	
	/**
	 * Updates the data of this smiley.
	 */
	public function update($path, $title, $code, $showOrder = 0, $smileyCategoryID = 0) {
		$sql = "UPDATE	wcf".WCF_N."_smiley
			SET	smileyPath = '".escapeString($path)."',
				smileyTitle = '".escapeString($title)."',
				smileyCode = '".escapeString($code)."',
				showOrder = ".$showOrder.",
				smileyCategoryID = ".$smileyCategoryID."
			WHERE	smileyID = ".$this->smileyID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Adds a smiley to a specific position in the smiley list
	 * 
	 * @param	integer		$smileyCategoryID
	 * @param	integer		$showOrder
	 */
	public function addPosition($smileyCategoryID = 0, $showOrder = null) {
		// shift boards
		if ($showOrder !== null) {
			$sql = "UPDATE	wcf".WCF_N."_smiley
				SET	showOrder = showOrder + 1
				WHERE 	smileyCategoryID = ".$smileyCategoryID."
					AND showOrder >= ".$showOrder;
			WCF::getDB()->sendQuery($sql);
		}
		
		// get final showOrder
		$sql = "SELECT 	IFNULL(MAX(showOrder), 0) + 1 AS showOrder
			FROM	wcf".WCF_N."_smiley
			WHERE	smileyCategoryID = ".$smileyCategoryID."
				".($showOrder ? "AND showOrder <= ".$showOrder : '');
		$row = WCF::getDB()->getFirstRow($sql);
		$showOrder = $row['showOrder'];
		
		// save showOrder
		$sql = "UPDATE	wcf".WCF_N."_smiley
			SET	showOrder = " . $showOrder . "
			WHERE	smileyID = " . $this->smileyID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Removes a smiley the smiley list
	 */
	public function removePositions() {
		$sql = "UPDATE	wcf".WCF_N."_smiley
			SET	showOrder = showOrder - 1
			WHERE 	smileyCategoryID = ".$this->smileyCategoryID."
				AND showOrder > ".$this->showOrder;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Creates a new smiley.
	 * 
	 * @return	Smiley		new smiley
	 */
	public static function create($filename, $destination, $field, $title = null, $code = null, $showOrder = 0, $smileyCategoryID = 0) {
		if (!file_exists($filename)) {
			throw new UserInputException($field, 'notFound');
		}
		
		if (!getImageSize($filename)) {
			throw new UserInputException($field, 'noValidImage');
		}
		
		// copy
		if ($filename != $destination && !copy($filename, $destination)) {
			throw new UserInputException($field, 'copyFailed');
		}
		// set permissions
		@chmod($destination, 0666);
		
		// generate title & code by filename
		$name = preg_replace('/\.[^\.]+$/', '', basename($destination));
		if ($title === null) $title = $name;
		if ($code === null) $code = ':'.$name.':';
		
		// save data
		$smileyID = self::insert(str_replace(WCF_DIR, '', $destination), $code, array(
			'smileyTitle' => $title,
			'showOrder' => $showOrder,
			'smileyCategoryID' => $smileyCategoryID
		));
		
		// get editor object
		$smiley = new SmileyEditor($smileyID);
		
		// save position
		$smiley->addPosition($smileyCategoryID, $showOrder);
		
		// save data
		return $smiley;
	}
	
	/**
	 * Creates the smiley row in database table.
	 *
	 * @param 	string 		$path
	 * @param	string		$code
	 * @param 	array		$additionalFields
	 * @return	integer		new smiley id
	 */
	public static function insert($path, $code, $additionalFields = array()){ 
		$keys = $values = '';
		if (!isset($additionalFields['packageID'])) $additionalFields['packageID'] = PACKAGE_ID;
		foreach ($additionalFields as $key => $value) {
			$keys .= ','.$key;
			$values .= ",'".escapeString($value)."'";
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_smiley
					(smileyPath, smileyCode
					".$keys.")
			VALUES		('".escapeString($path)."', '".escapeString($code)."'
					".$values.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Deletes the smiley chache.
	 */
	public static function resetCache() {
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.smileys.php');
	}
	
	/**
	 * Returns the number of marked smileys.
	 *
	 * @return	integer
	 */
	public static function getMarkedSmileys() {
		$markedSmileys = WCF::getSession()->getVar('markedSmileys');
		if ($markedSmileys !== null) return count($markedSmileys);
		return 0;
	}
	
	/**
	 * Marks smileys.
	 *
	 * @param	array<integer>		$smileyIDArray
	 */
	public static function mark($smileyIDArray) {
		if (!is_array($smileyIDArray)) $smileyIDArray = array($smileyIDArray);
		$markedSmileys = WCF::getSession()->getVar('markedSmileys');
		if ($markedSmileys !== null) {
			foreach ($smileyIDArray as $smileyID) {
				if (!in_array($smileyID, $markedSmileys)) {
					$markedSmileys[] = $smileyID;
				}
			}
		}
		else {
			$markedSmileys = $smileyIDArray;
		}
		
		WCF::getSession()->register('markedSmileys', $markedSmileys);
	}
	
	/**
	 * Unmarks smileys.
	 *
	 * @param	array<integer>		$smileyIDArray
	 */
	public static function unmark($smileyIDArray) {
		if (!is_array($smileyIDArray)) $smileyIDArray = array($smileyIDArray);
		$markedSmileys = WCF::getSession()->getVar('markedSmileys');
		if ($markedSmileys !== null) {
			foreach ($smileyIDArray as $smileyID) {
				if (($key = array_search($smileyID, $markedSmileys)) !== false) {
					unset($markedSmileys[$key]);
				}
			}
			
			if (count($markedSmileys)) WCF::getSession()->register('markedSmileys', $markedSmileys);
			else WCF::getSession()->unregister('markedSmileys');
		}
	}
}
?>