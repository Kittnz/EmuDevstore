<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');

/**
 * Shows the language multilingualism form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.language
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class LanguageMultilingualismForm extends ACPForm {
	public $templateName = 'languageMultilingualism';
	public $activeMenuItem = 'wcf.acp.menu.link.language.multilingualism';
	public $neededPermissions = 'admin.language.canEditLanguage';
	
	public $enable = 0;
	public $languageIDs = array();
	public $languages = array();
	public $languageList = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->languages = WCF::getCache()->get('languages', 'languages');
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['enable'])) $this->enable = intval($_POST['enable']);
		if (isset($_POST['languageIDs']) && is_array($_POST['languageIDs'])) $this->languageIDs = ArrayUtil::toIntegerArray($_POST['languageIDs']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->enable == 1) {
			// add default language
			if (!in_array(Language::getDefaultLanguageID(), $this->languageIDs)) {
				$this->languageIDs[] = Language::getDefaultLanguageID();
			}

			// validate language ids
			$contentLanguages = 0;
			foreach ($this->languageIDs as $languageID) {
				if (isset($this->languages[$languageID])) {
					$contentLanguages++;
				}
			}
			
			if ($contentLanguages < 2) {
				throw new UserInputException('languageIDs');
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();

		// save
		LanguageEditor::enableMultilingualism(($this->enable == 1 ? $this->languageIDs : array()));
		
		// clear cache
		WCF::getCache()->clearResource('languages');
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// default values
			$contentLanguages = 0;
			foreach ($this->languages as $languageID => $language) {
				if ($language['hasContent']) {
					$contentLanguages++;
					$this->languageIDs[] = $languageID;
				}
			}
			
			// add default language
			if (!in_array(Language::getDefaultLanguageID(), $this->languageIDs)) {
				$this->languageIDs[] = Language::getDefaultLanguageID();
			}
			
			if ($contentLanguages > 1) {
				$this->enable = 1;
			}
		}
		
		$this->languageList = Language::getLanguages();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'enable' => $this->enable,
			'languageIDs' => $this->languageIDs,
			'languages' => $this->languageList
		));
	}
}
?>