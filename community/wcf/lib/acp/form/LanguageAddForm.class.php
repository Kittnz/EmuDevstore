<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');

/**
 * Shows the language add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.language
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class LanguageAddForm extends ACPForm {
	public $templateName = 'languageAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.language.add';
	public $neededPermissions = 'admin.language.canAddLanguage';
	
	public $mode = 'import';
	public $languageFile = '';
	public $languageCode = '';
	public $sourceLanguageID = 0;
	public $filename = '';
	public $sourceLanguage, $language;
	public $importField = 'languageFile';
	public $languages = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		// mode
		if (isset($_POST['mode'])) $this->mode = $_POST['mode'];
		
		// copy
		if (isset($_POST['languageCode'])) $this->languageCode = $_POST['languageCode'];
		if (isset($_POST['sourceLanguageID'])) $this->sourceLanguageID = intval($_POST['sourceLanguageID']);
		
		// import
		if (isset($_POST['languageFile']) && !empty($_POST['languageFile'])) {
			$this->languageFile = $_POST['languageFile'];
			$this->filename = $_POST['languageFile'];
		}
		if (isset($_FILES['languageUpload']) && !empty($_FILES['languageUpload']['tmp_name'])) {
			$this->importField = 'languageUpload';
			$this->filename = $_FILES['languageUpload']['tmp_name'];
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->mode == 'copy') {
			// language code
			if (empty($this->languageCode)) {
				throw new UserInputException('languageCode');
			}
			
			// 
			if (LanguageEditor::getLanguageByCode($this->languageCode)) {
				throw new UserInputException('languageCode', 'notUnique');
			}
			
			// source language id
			if (empty($this->sourceLanguageID)) {
				throw new UserInputException('sourceLanguageID');
			}
			
			// get language
			$this->sourceLanguage = new LanguageEditor($this->sourceLanguageID);
			if (!$this->sourceLanguage->getLanguageID()) {
				throw new UserInputException('sourceLanguageID');
			}
		}
		else {
			// check file
			if (!file_exists($this->filename)) {
				throw new UserInputException('languageFile');
			}
			
			// try to import
			try {
				// open xml document
				$xml = new XML($this->filename);
				
				// import xml document
				$this->language = LanguageEditor::importFromXML($xml, PACKAGE_ID);
			}
			catch (SystemException $e) {
				throw new UserInputException($this->importField, $e->getMessage());
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		if ($this->mode == 'copy') {
			$this->language = LanguageEditor::create(StringUtil::toLowerCase($this->languageCode));
			$this->sourceLanguage->copy($this->language);
		}
		
		// add language to this package.
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_language_to_packages
						(languageID, packageID)
			VALUES			(".$this->language->getLanguageID().", 
						".PACKAGE_ID.")";
		WCF::getDB()->sendQuery($sql);
		Language::clearCache();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->languages = Language::getLanguages();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'mode' => $this->mode,
			'languageCode' => $this->languageCode,
			'sourceLanguageID' => $this->sourceLanguageID,
			'languages' => $this->languages,
			'languageFile' => $this->languageFile
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