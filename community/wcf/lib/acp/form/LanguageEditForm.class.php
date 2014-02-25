<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');

/**
 * Shows the language edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.language
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class LanguageEditForm extends ACPForm {
	public $templateName = 'languageEdit';
	public $activeMenuItem = 'wcf.acp.menu.link.language';
	public $neededPermissions = 'admin.language.canEditLanguage';
	
	public $languageID = 0;
	public $language;
	public $languageCategoryID = 0;
	public $customVariables = 0; 
	public $languageCategory = '';
	public $languageItems = array();
	public $languageItemIDs = array();
	public $languageItemID = 0;
	public $languageCategories = array();
	public $languageUseCustom = array();
	public $languageCustomItems = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get language
		if (isset($_REQUEST['languageID'])) $this->languageID = intval($_REQUEST['languageID']);
		$this->language = new LanguageEditor($this->languageID);
		if (!$this->language->getLanguageID()) {
			throw new IllegalLinkException();
		}
		
		// get language category
		if (isset($_REQUEST['languageCategoryID'])) {
			$this->languageCategoryID = intval($_REQUEST['languageCategoryID']);
		}
		else if (isset($_REQUEST['languageItemID'])) {
			$this->languageItemID = intval($_REQUEST['languageItemID']);
			// get category by item
			$sql = "SELECT	languageCategoryID
				FROM	wcf".WCF_N."_language_item
				WHERE	languageItemID = ".$this->languageItemID;
			$row = WCF::getDB()->getFirstRow($sql);
			if (!empty($row['languageCategoryID'])) $this->languageCategoryID = $row['languageCategoryID'];
		}
		
		if ($this->languageCategoryID) {
			$sql = "SELECT	languageCategory
				FROM	wcf".WCF_N."_language_category
				WHERE	languageCategoryID = ". $this->languageCategoryID;
			$row = WCF::getDB()->getFirstRow($sql);
			if (empty($row['languageCategory'])) {
				throw new IllegalLinkException();
			}
			$this->languageCategory = $row['languageCategory'];
		}
		if (isset($_REQUEST['customVariables'])) {
			$this->customVariables = intval($_REQUEST['customVariables']);
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['languageItems']) && is_array($_POST['languageItems'])) {
			$this->languageCustomItems = $_POST['languageItems'];
			/*if (isset($this->languageCustomItems[0])) {
				$this->languageCustomItems[''] = $this->languageCustomItems[0];
				unset($this->languageCustomItems[0]);
			}*/	
		}
		
		if (isset($_POST['languageUseCustom']) && is_array($_POST['languageUseCustom'])) {
			$this->languageUseCustom = ArrayUtil::toIntegerArray($_POST['languageUseCustom']);
			/*if (isset($this->languageUseCustom[0])) {
				$this->languageUseCustom[''] = $this->languageUseCustom[0];
				unset($this->languageUseCustom[0]);
 			}*/
 		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save
		/*$saveItems = $useCustom = array();
		foreach ($this->languageCustomItems as $item => $value) {
 			$saveItems[$this->languageCategory.($item ? '.'.$item : '')] = $value;
 		}
		foreach ($this->languageUseCustom as $item => $value) {
			$useCustom[$this->languageCategory.($item ? '.'.$item : '')] = $value;
		}*/
		$this->language->updateItems($this->languageCustomItems, $this->languageCategoryID, PACKAGE_ID, $this->languageUseCustom);
 		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->languageCategories = Language::getLanguageCategories();
		$this->readLanguageItems();
		// group categories
		$languageCategories = array();
		foreach ($this->languageCategories as $id => $name) {
			$categorySplit = explode('.', $name);
			$group = array_shift($categorySplit);
			$name = implode('.', $categorySplit);
			if (!isset($languageCategories[$group])) $languageCategories[$group] = array();
			$languageCategories[$group][$id] = $name;
		}
		$this->languageCategories = $languageCategories;
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'languageID' => $this->languageID,
			'language' => $this->language,
			'languageCategoryID' => $this->languageCategoryID,
			'languageCategories' => $this->languageCategories,
			'languageItems' => $this->languageItems,
			'languageUseCustom' => $this->languageUseCustom,
			'languageCustomItems' => $this->languageCustomItems,
			'languageItemIDs' => $this->languageItemIDs,
			'languageCategory' => $this->languageCategory,
			'languageItemID' => $this->languageItemID,
			'customVariables' => $this->customVariables
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
	
	/**
	 * Gets a list of sorted language items.
	 * 
	 * @return	array
	 */
	protected function readLanguageItems() {
		$languageItems = $languageCustomItems = array();
		if ($this->languageCategoryID || $this->customVariables) {
			$sql = "SELECT		languageItemID, languageItem, languageItemValue, languageCustomItemValue, languageUseCustomValue, languageCategoryID
				FROM		wcf".WCF_N."_language_item language_item,
						wcf".WCF_N."_package_dependency package_dependency
				WHERE 		language_item.packageID = package_dependency.dependency
						AND languageID = ".$this->languageID."
						".($this->languageCategoryID ? "AND languageCategoryID = ".$this->languageCategoryID : '')."
						".($this->customVariables ? "AND languageHasCustomValue = 1" : '')."
						AND package_dependency.packageID = ".PACKAGE_ID."
				ORDER BY 	languageCategoryID, package_dependency.priority";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				//$row['languageItem'] = preg_replace('!^'.preg_quote($this->languageCategories[$row['languageCategoryID']]).'\.?!', '', $row['languageItem']);
				$languageItems[$row['languageItem']] = $row;
				$languageCustomItems[$row['languageItem']] = $row['languageCustomItemValue'];
				$this->languageUseCustom[$row['languageItem']] = $row['languageUseCustomValue'];
				$this->languageItemIDs[$row['languageItem']] = $row['languageItemID'];
			}
		}
		
		foreach ($languageItems as $name => $item) {
			if (!isset($this->languageItems[$item['languageCategoryID']])) {
				$this->languageItems[$item['languageCategoryID']] = array('category' => $this->languageCategories[$item['languageCategoryID']], 'items' => array());
			}
			
			if (!isset($this->languageItems[$item['languageCategoryID']]['items'][$name])) {
				$this->languageItems[$item['languageCategoryID']]['items'][$name] = $item['languageItemValue'];
			}
		}
		
		foreach ($languageCustomItems as $item => $value) {
			if (!isset($this->languageCustomItems[$item])) {
				$this->languageCustomItems[$item] = $value;
			}
		}
		
		// sort items
		ksort($this->languageItems);
		ksort($this->languageCustomItems);
	}
}
?>