<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/HelpItemAddForm.class.php');

/**
 * Shows the help item edit form.
 *
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.help
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class HelpItemEditForm extends HelpItemAddForm {
	// system	
	public $activeMenuItem = 'wcf.acp.menu.link.helpItem';
	public $neededPermissions = 'admin.help.canEditHelpItem';
	
	// data
	public $languages = array();
	public $helpItemID = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// language
		if (isset($_REQUEST['languageID'])) $this->languageID = intval($_REQUEST['languageID']);
		else $this->languageID = WCF::getLanguage()->getLanguageID();
		
		// help item
		if (isset($_REQUEST['helpItemID'])) $this->helpItemID = intval($_REQUEST['helpItemID']);
		$this->helpItem = new HelpItemEditor($this->helpItemID);
		if (!$this->helpItem->helpItemID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get all available languages
		$this->languages = WCF::getLanguage()->getLanguageCodes();
		
		// rekursion fail
		if ($this->parentItem == $this->helpItem->helpItem) {
			$this->parentItem = $this->helpItem->parentHelpItem;
		}
		
		// default values
		if (!count($_POST)) { 
			$this->refererPattern = $this->helpItem->refererPattern;
			$this->showOrder = $this->helpItem->showOrder;
			$this->isDisabled = $this->helpItem->isDisabled;
			$this->parentItem = $this->helpItem->parentHelpItem;
			
			if (WCF::getLanguage()->getLanguageID() != $this->languageID) {
				$language = new Language($this->languageID);
			}
			else {
				$language = WCF::getLanguage();
			}
			
			$this->topic = $language->get('wcf.help.item.'.$this->helpItem->helpItem);
			if ($this->topic == 'wcf.help.item.'.$this->helpItem->helpItem) $this->topic = "";
			$this->text = $language->get('wcf.help.item.'.$this->helpItem->helpItem.'.description');
			if ($this->text == 'wcf.help.item.'.$this->helpItem->helpItem.'.description') $this->text = "";
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// update item
		$this->helpItem->update($this->topic, $this->text, $this->parentItem, $this->refererPattern, $this->showOrder, $this->isDisabled, $this->languageID);
		HelpItemEditor::clearCache();
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
			'action' => 'edit',
			'helpItemID' => $this->helpItemID,
			'languages' => $this->languages
		));
	}
}
?>