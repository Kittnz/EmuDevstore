<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');

/**
 * Shows the language sync form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.language
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class LanguageSyncForm extends ACPForm {
	public $templateName = 'languageSync';
	public $activeMenuItem = 'wcf.acp.menu.link.language.sync';
	public $neededPermissions = 'admin.language.canEditLanguage';
	
	public $sourceLanguageID = 0;
	public $languages = array();
	public $languageItemIDs = array();
	public $addedLanguageItems = 0;
	public $deletedLanguageItems = 0;
	public $languageDiff = array();
	
	public $preview = false;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['sourceLanguageID'])) $this->sourceLanguageID = intval($_POST['sourceLanguageID']);
		if (isset($_POST['preview'])) $this->preview = (boolean) $_POST['preview'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (!$this->sourceLanguageID) {
			throw new UserInputException('sourceLanguageID');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		if (!$this->preview) {
			parent::save();
			// get languages
			$languages = array();
			$sql = "SELECT	languageID
				FROM	wcf".WCF_N."_language
				WHERE	languageID <> ".$this->sourceLanguageID;
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) $languages[] = $row['languageID'];
			
			if (count($languages) > 0) {
				// add missing language items
				foreach ($languages as $languageID) {
					$sql = "INSERT IGNORE INTO	wcf".WCF_N."_language_item
									(languageID, languageItem, languageItemValue, languageCategoryID, packageID)
						SELECT			".$languageID.", languageItem, languageItemValue, languageCategoryID, packageID
						FROM			wcf".WCF_N."_language_item
						WHERE			languageID = ".$this->sourceLanguageID."
									AND languageItem NOT IN (
										SELECT	languageItem
										FROM	wcf".WCF_N."_language_item
										WHERE	languageID = ".$languageID."
									)";
					WCF::getDB()->sendQuery($sql);
					$this->addedLanguageItems += WCF::getDB()->getAffectedRows();
				}
				
				// remove obsolete language items
				$languageItemIDs = '';
				$sql = "SELECT	languageItemID 
					FROM	wcf".WCF_N."_language_item
					WHERE	languageID IN (".implode(',', $languages).")
						AND languageItem NOT IN (
							SELECT	languageItem
							FROM	wcf".WCF_N."_language_item
							WHERE	languageID = ".$this->sourceLanguageID."
						)";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) $languageItemIDs .= ',' . $row['languageItemID'];
				
				$sql = "DELETE FROM	wcf".WCF_N."_language_item
					WHERE		languageItemID IN (0".$languageItemIDs.")";
				WCF::getDB()->sendQuery($sql);
				$this->deletedLanguageItems = WCF::getDB()->getAffectedRows();
			}
			
			// delete language files
			LanguageEditor::deleteLanguageFiles();
			$this->saved();
			
			// show success message
			WCF::getTPL()->assign(array(
				'success' => true,
				'addedLanguageItems' => $this->addedLanguageItems,
				'deletedLanguageItems' => $this->deletedLanguageItems
			));
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->languages = Language::getLanguages();
		
		if ($this->preview) {
			
			// missing variables
			foreach ($this->languages as $key => $language) {
				if ($key == $this->sourceLanguageID) continue;
				
				$sql = "SELECT	" . $key . " as languageID, languageItem, languageItemID
					FROM	wcf".WCF_N."_language_item
					WHERE	languageID = " . $this->sourceLanguageID . "
						AND languageItem NOT IN (
							SELECT	languageItem
							FROM	wcf".WCF_N."_language_item
							WHERE	languageID = " . $key . "
						)";
				WCF::getDB()->sendQuery($sql);
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$this->languageDiff[$row['languageItem']][$row['languageID']] = true;
					$this->languageItemIDs[$row['languageItem']] = $row['languageItemID'];
				}
			}
			
			$languages = $this->languages;
			unset($languages[$this->sourceLanguageID]);
			
			//obsolete variables
			$sql = "SELECT	languageItem, languageID 
				FROM	wcf" . WCF_N . "_language_item
				WHERE	languageID IN (" . implode(',', array_keys($languages)) . ")
					AND languageItem NOT IN (
						SELECT	languageItem
						FROM	wcf" . WCF_N . "_language_item
						WHERE	languageID = " . $this->sourceLanguageID . "
					)";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->languageDiff[$row['languageItem']][$row['languageID']] = false;
			}
			
			ksort($this->languageDiff);
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'sourceLanguageID' => $this->sourceLanguageID,
			'languages' => $this->languages,
			'languageDiff' => $this->languageDiff,
			'languageCodes' => Language::getLanguageCodes(),
			'preview' => $this->preview,
			'languageItemIDs' => $this->languageItemIDs
		));
	}
}
?>