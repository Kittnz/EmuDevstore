<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/PageMenuItemAddForm.class.php');

/**
 * Shows the page menu item edit form.
 *
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.pageMenu
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class PageMenuItemEditForm extends PageMenuItemAddForm {
	// system	
	public $activeMenuItem = 'wcf.acp.menu.link.pageMenuItem';
	public $neededPermissions = 'admin.pageMenu.canEditPageMenuItem';
	
	// properties
	public $pageMenuItemID = 0;
	public $languageID = 0;
	
	// data
	public $languages = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// read language id
		if (isset($_REQUEST['languageID'])) $this->languageID = intval($_REQUEST['languageID']);
		else $this->languageID = WCF::getLanguage()->getLanguageID();
		
		// read page menu item id
		if (isset($_REQUEST['pageMenuItemID'])) $this->pageMenuItemID = intval($_REQUEST['pageMenuItemID']);
		$this->pageMenuItem = new PageMenuItemEditor($this->pageMenuItemID);
		if (!$this->pageMenuItem->menuItemID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get all available languages
		$this->languages = Language::getLanguageCodes();
		
		// default values
		if (!count($_POST)) { 
			$this->link = $this->pageMenuItem->menuItemLink;
			$this->iconS = $this->pageMenuItem->menuItemIconS;
			$this->iconM = $this->pageMenuItem->menuItemIconM;
			$this->showOrder = $this->pageMenuItem->showOrder;
			$this->position = $this->pageMenuItem->menuPosition;
			
			// get name
			if (WCF::getLanguage()->getLanguageID() != $this->languageID) $language = new Language($this->languageID);
			else $language = WCF::getLanguage();
			$this->name = $language->get($this->pageMenuItem->menuItem);
			if ($this->name == $this->pageMenuItem->menuItem) $this->name = "";
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// update
		$this->pageMenuItem->update($this->name, $this->link, $this->iconS, $this->iconM, $this->showOrder, $this->position, $this->languageID);
		
		// delete Cache
		PageMenuItemEditor::clearCache();
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
			'pageMenuItemID' => $this->pageMenuItemID,
			'languageID' => $this->languageID,
			'languages' => $this->languages,
			'action' => 'edit'
		));
	}
}
?>