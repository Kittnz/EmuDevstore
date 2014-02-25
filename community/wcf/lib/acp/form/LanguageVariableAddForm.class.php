<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');

/**
 * Shows the variable add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.language
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class LanguageVariableAddForm extends ACPForm {
	public $templateName = 'languageVariableAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.language.variable.add';
	public $neededPermissions = 'admin.language.canEditLanguage';
	
	public $languageCategoryID = 0;
	public $newLanguageCategory = '';
	public $languageItemName = '';
	public $languageItemValues = array();
	public $languageCategories = array();
	public $languages = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->languageCategories = Language::getLanguageCategories();
		$this->languages = WCF::getCache()->get('languages', 'languages');
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['languageCategoryID'])) $this->languageCategoryID = intval($_POST['languageCategoryID']);
		if (isset($_POST['newLanguageCategory'])) $this->newLanguageCategory = StringUtil::trim($_POST['newLanguageCategory']);
		if (isset($_POST['languageItemName'])) $this->languageItemName = StringUtil::trim($_POST['languageItemName']);
		if (isset($_POST['languageItemValues']) && is_array($_POST['languageItemValues'])) $this->languageItemValues = $_POST['languageItemValues'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// language category
		$categoryName = '';
		if (empty($this->newLanguageCategory)) {
			if (!isset($this->languageCategories[$this->languageCategoryID])) {
				throw new UserInputException('languageCategoryID');
			}
			
			$categoryName = $this->languageCategories[$this->languageCategoryID];
		}
		else {
			// language category syntax
			if (!preg_match('/[a-z0-9_]+(?:\.[a-z0-9_]+)+/i', $this->newLanguageCategory)) {
				throw new UserInputException('newLanguageCategory', 'invalid');
			}
			
			$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_language_category
				WHERE	languageCategory = '".escapeString($this->newLanguageCategory)."'";
			$row = WCF::getDB()->getFirstRow($sql);
			if ($row['count']) {
				throw new UserInputException('newLanguageCategory', 'notUnique');
			}
			
			$categoryName = $this->newLanguageCategory;
		}
		
		if (StringUtil::indexOfIgnoreCase($this->languageItemName, $categoryName) === false) {
			$this->languageItemName = $categoryName . ($this->languageItemName ? '.'.$this->languageItemName : '');
		}
		
		// language item
		if (empty($this->languageItemName)) {
			throw new UserInputException('languageItemName');
		}
		
		// language item syntax
		if (!preg_match('/[a-z0-9_]+(?:\.[a-z0-9_]+){2,}/i', $this->languageItemName)) {
			throw new UserInputException('languageItemName', 'invalid');
		}
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_language_item
			WHERE	languageItem = '".escapeString($this->languageItemName)."'
				AND packageID IN (
					SELECT	dependency
					FROM	wcf".WCF_N."_package_dependency
					WHERE	packageID = ".PACKAGE_ID."
				)";
		$row = WCF::getDB()->getFirstRow($sql);
		if ($row['count']) {
			throw new UserInputException('languageItemName', 'notUnique');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();

		// create new language category
		$languageCategoryID = $this->languageCategoryID;
		if (!empty($this->newLanguageCategory)) {
			$languageCategoryID = LanguageEditor::createCategory($this->newLanguageCategory);
			
			// clear language cache
			WCF::getCache()->clearResource('languages');
		}
		
		// save item values
		foreach (array_keys($this->languages) as $languageID) {
			$language = new LanguageEditor($languageID);
			$language->updateItems(array($this->languageItemName => (isset($this->languageItemValues[$languageID]) ? $this->languageItemValues[$languageID] : '')), $languageCategoryID);
		}
		
		// reset values
		//$this->languageCategoryID = 0;
		$this->newLanguageCategory = $this->languageItemName = '';
		$this->languageItemValues = array();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'languageCategoryID' => $this->languageCategoryID,
			'newLanguageCategory' => $this->newLanguageCategory,
			'languageItemName' => $this->languageItemName,
			'languageItemValues' => $this->languageItemValues,
			'languages' => $this->languages,
			'languageCategories' => $this->languageCategories
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
?>