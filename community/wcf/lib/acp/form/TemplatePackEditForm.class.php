<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/TemplatePackAddForm.class.php');
require_once(WCF_DIR.'lib/data/template/TemplatePackEditor.class.php');

/**
 * Shows the template pack edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.template
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class TemplatePackEditForm extends TemplatePackAddForm {
	public $activeMenuItem = 'wcf.acp.menu.link.template';
	public $neededPermissions = 'admin.template.canEditTemplatePack';

	/**
	 * template pack editor object.
	 * 
	 * @var	TemplatePackEditor
	 */
	public $templatePack = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['templatePackID'])) $this->templatePackID = intval($_REQUEST['templatePackID']);
		$this->templatePack = new TemplatePackEditor($this->templatePackID);
		if (!$this->templatePack->templatePackID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see TemplatePackAddForm::validateName()
	 */
 	protected function validateName() {
 		if ($this->templatePackName != $this->templatePack->templatePackName) {
 			parent::validateName();
 		}
 	}
 	
 	/**
	 * @see TemplatePackAddForm::validateFolderName()
	 */
 	protected function validateFolderName() {
 		if ($this->templatePackFolderName != $this->templatePack->templatePackFolderName) {
 			parent::validateFolderName();
 		}
 	}
	
	/**
	 * @see Form::validate()
	 */
	public function save() {
		AbstractForm::save();

		// update
		$this->templatePack->update($this->templatePackName, $this->templatePackFolderName, $this->parentTemplatePackID);
		
		// reset cache
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.templatePacks.php');
		
		// show success message
		WCF::getTPL()->assign('success', true);
		
		$this->saved();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'templatePack' => $this->templatePack,	
			'templatePackID' => $this->templatePackID,
			'action' => 'edit'
		));
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		ACPForm::readData();
		
		$this->availableTemplatePacks = TemplatePack::getSelectList(array($this->templatePackID));
		
		// default values
		if (!count($_POST)) {
			$this->templatePackName = $this->templatePack->templatePackName;
			$this->templatePackFolderName = $this->templatePack->templatePackFolderName;
			$this->parentTemplatePackID = $this->templatePack->parentTemplatePackID;
		}
	}
}
?>