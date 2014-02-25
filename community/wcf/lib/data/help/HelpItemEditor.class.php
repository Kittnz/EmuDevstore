<?php
// wcf imports
require_once(WCF_DIR.'lib/data/help/HelpItem.class.php');
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');

/**
 * Provides functions to edit help items.
 *
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.help
 * @subpackage	data.help
 * @category 	Community Framework
 */
class HelpItemEditor extends HelpItem {
	/**
	 * Creates a new help item.
	 * 
	 * @param 	string		$name
	 * @param 	string		$text
	 * @param 	string		$parentItem
	 * @param 	string		$refererPattern
	 * @param 	integer		$showOrder
	 * @param 	boolean		$isDisabled
	 * @param 	integer		$languageID
	 * @param	integer		$packageID
	 *
	 * @return	HelpItemEditor
	 */	
	public static function create($name, $text = '', $parentItem = '', $refererPattern = '', $showOrder = 0, $isDisabled = 0, $languageID = 0, $packageID = PACKAGE_ID) {
		// get show order
		if ($showOrder == 0) {
			// get next number in row
			$sql = "SELECT	MAX(showOrder) AS showOrder
				FROM	wcf".WCF_N."_help_item
				WHERE	parentHelpItem = '".escapeString($parentItem)."'";
			$row = WCF::getDB()->getFirstRow($sql);
			if (!empty($row)) $showOrder = intval($row['showOrder']) + 1;
			else $showOrder = 1;
		}
		else {
			$sql = "UPDATE	wcf".WCF_N."_help_item
				SET 	showOrder = showOrder + 1
				WHERE 	showOrder >= ".$showOrder."
					AND parentHelpItem = '".escapeString($parentItem)."'";
			WCF::getDB()->sendQuery($sql);
		}
		
		// get menu item name
		$helpItem = '';
		if ($languageID == 0) $helpItem = $name;
		
		// save
		$sql = "INSERT INTO	wcf".WCF_N."_help_item
					(packageID, helpItem, parentHelpItem, refererPattern, showOrder, isDisabled)
			VALUES		(".$packageID.", '".escapeString($helpItem)."', '".escapeString($parentItem)."', '".escapeString($refererPattern)."', ".$showOrder.", ".$isDisabled.")";
		WCF::getDB()->sendQuery($sql);
		
		// get item id
		$helpItemID = WCF::getDB()->getInsertID("wcf".WCF_N."_help_item", 'helpItemID');
		
		if ($languageID != 0) {
			// set name
			$helpItem = "item".$helpItemID;
			$sql = "UPDATE	wcf".WCF_N."_help_item
				SET	helpItem = '".escapeString($helpItem)."'
				WHERE 	helpItemID = ".$helpItemID;
			WCF::getDB()->sendQuery($sql);
			
			// save language variables
			$language = new LanguageEditor($languageID);
			$language->updateItems(array('wcf.help.item.'.$helpItem => $name, 'wcf.help.item.'.$helpItem.'.description' => $text), 0, WCF::getPackageID('com.woltlab.wcf.data.help'));
			LanguageEditor::deleteLanguageFiles($languageID, 'wcf.help.item');
		}
		
		return new HelpItemEditor($helpItemID);
	}
	
	/**
	 * Updates this help item.
	 * 
	 * @param 	string		$name
	 * @param 	string		$text
	 * @param 	string		$parentItem
	 * @param 	string		$refererPattern
	 * @param 	integer		$showOrder
	 * @param 	boolean		$isDisabled
	 * @param	integer		$languageID
	 * @param	integer		$packageID
	 */
	public function update($name, $text = '', $parentItem = '', $refererPattern = '', $showOrder = 0, $isDisabled = 0, $languageID = 0, $packageID = PACKAGE_ID) {
		if ($parentItem == $this->helpItem) {
			$parentItem = $this->parentHelpItem;
		}
		
		// update show order
		if ($parentItem == $this->parentHelpItem) {
			if ($this->showOrder != $showOrder) {
				if ($showOrder < $this->showOrder) {
					$sql = "UPDATE	wcf".WCF_N."_help_item
						SET 	showOrder = showOrder + 1
						WHERE 	showOrder >= ".$showOrder."
							AND showOrder < ".$this->showOrder."
							AND parentHelpItem = '".escapeString($parentItem)."'";
					WCF::getDB()->sendQuery($sql);
				}
				else if ($showOrder > $this->showOrder) {
					$sql = "UPDATE	wcf".WCF_N."_help_item
						SET	showOrder = showOrder - 1
						WHERE	showOrder <= ".$showOrder."
							AND showOrder > ".$this->showOrder."
							AND parentHelpItem = '".escapeString($parentItem)."'";
					WCF::getDB()->sendQuery($sql);
				}
			}
		}
		else {
			$sql = "UPDATE	wcf".WCF_N."_help_item
				SET 	showOrder = showOrder - 1
				WHERE 	showOrder >= ".$this->showOrder."
					AND parentHelpItem = '".escapeString($this->parentHelpItem)."'";
			WCF::getDB()->sendQuery($sql);
				
			$sql = "UPDATE 	wcf".WCF_N."_help_item
				SET 	showOrder = showOrder + 1
				WHERE 	showOrder >= ".$showOrder."
					AND parentHelpItem = '".escapeString($parentItem)."'";
			WCF::getDB()->sendQuery($sql);
		}
		
		// Update
		$sql = "UPDATE	wcf".WCF_N."_help_item
			SET	".($languageID == 0 ? "helpItem = '".escapeString($name)."'," : '')."
				parentHelpItem = '".escapeString($parentItem)."',
				refererPattern = '".escapeString($refererPattern)."',
				showOrder = ".$showOrder.",
				isDisabled = ".$isDisabled."
			WHERE 	helpItemID = ".$this->helpItemID.";";
		WCF::getDB()->sendQuery($sql);
		
		if ($languageID != 0) {
			// save language variables
			$language = new LanguageEditor($languageID);
			$language->updateItems(array('wcf.help.item.'.$this->helpItem => $name, 'wcf.help.item.'.$this->helpItem.'.description' => $text), 0, $this->packageID, array('wcf.help.item.'.$this->helpItem => 1, 'wcf.help.item.'.$this->helpItem.'.description' => 1));
			LanguageEditor::deleteLanguageFiles($languageID, 'wcf.help.item');
			$language->deleteCompiledTemplates();
		}
	}
	
	/**
	 * Deletes this help item.
	 */
	public function delete() {
		// update show order
		$sql = "UPDATE	wcf".WCF_N."_help_item
			SET	showOrder = showOrder - 1
			WHERE	showOrder >= ".$this->showOrder."
				AND parentHelpItem = '".escapeString($this->parentHelpItem)."'";
		WCF::getDB()->sendQuery($sql);
		
		// update children
		$sql = "UPDATE	wcf".WCF_N."_help_item
			SET	parentHelpItem = '".escapeString($this->parentHelpItem)."'
			WHERE	parentHelpItem = '".escapeString($this->helpItem)."'";
		WCF::getDB()->sendQuery($sql);
		
		// delete item
		$sql = "DELETE FROM	wcf".WCF_N."_help_item
			WHERE		helpItemID = ".$this->helpItemID;
		WCF::getDB()->sendQuery($sql);
			
		// delete language variables
		LanguageEditor::deleteVariable('wcf.help.item.'.$this->helpItem);
		LanguageEditor::deleteVariable('wcf.help.item.'.$this->helpItem.'.description');
	}
	
	/**
	 * Clears the help cache.
	 */
	public static function clearCache() {
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.help-*.php');
	}
	
	/**
	 * Updates the positions of a help item directly.
	 *
	 * @param	integer		$helpItemID
	 * @param	string		$parentItem
	 * @param	integer		$position
	 */
	public static function updateShowOrder($helpItemID, $parentItem, $position) {
		$sql = "UPDATE	wcf".WCF_N."_help_item
			SET	parentHelpItem = '".escapeString($parentItem)."',
				showOrder = ".$position."
			WHERE 	helpItemID = ".$helpItemID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Enables / disables this item.
	 * 
	 * @param	boolean		$enable
	 */
	public function enable($enable = 1) {
		$sql = "UPDATE	wcf".WCF_N."_help_item
			SET	isDisabled = ".intval(!$enable)."
			WHERE	helpItemID = ".$this->helpItemID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>