<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/TemplateAddForm.class.php');
require_once(WCF_DIR.'lib/data/template/TemplateEditor.class.php');

/**
 * Shows the template edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.template
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class TemplateEditForm extends TemplateAddForm {
	public $activeMenuItem = 'wcf.acp.menu.link.template';
	public $neededPermissions = 'admin.template.canEditTemplate';
		
	public $templateID = 0;
	public $copy = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['templateID'])) $this->templateID = intval($_REQUEST['templateID']);
		$this->template = new TemplateEditor($this->templateID);
		if (!$this->template->templateID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['copy'])) $this->copy = intval($_POST['copy']);
	}
	
	/**
	 * @see TemplateAddForm::validateTemplateName()
	 */
	protected function validateTemplateName() {
		if ($this->copy || StringUtil::toLowerCase($this->tplName) != StringUtil::toLowerCase($this->template->templateName)) {
			parent::validateTemplateName();
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// create template pack
		if (count($this->templatePacks) == 0) {
			$this->templatePackID = TemplatePackEditor::create($this->templatePackName, $this->templatePackFolderName);
			
			// get available template packs
			$this->templatePacks = TemplatePackEditor::getTemplatePacks();
		}
		
		// save template
		if ($this->copy) $this->template = TemplateEditor::create($this->tplName, $this->source, $this->templatePackID, $this->template->packageID);
		else $this->template->update($this->tplName, $this->source, $this->templatePackID);
		$this->templateID = $this->template->templateID;
		
		// reset cache
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.templates-*.php');
		$this->saved();
		
		$this->copy = 0;
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// default value
			$this->source = $this->template->getSource();
			$this->tplName = $this->template->templateName;
			$this->templatePackID = $this->template->templatePackID;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'templateID' => $this->templateID,
			'action' => 'edit',
			'copy' => $this->copy,
			'template' => $this->template
		));
	}
}
?>