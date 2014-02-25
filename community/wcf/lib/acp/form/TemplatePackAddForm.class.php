<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/template/TemplatePackEditor.class.php');

/**
 * Shows the template pack add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.template
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class TemplatePackAddForm extends ACPForm {
	public $templateName = 'templatePackAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.templatepack.add';
	public $neededPermissions = 'admin.template.canAddTemplatePack';
		
	public $templatePackName = '';
	public $templatePackFolderName = '';
	public $templatePackID = 0;
	public $parentTemplatePackID = 0;
	public $availableTemplatePacks = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['templatePackName'])) $this->templatePackName = StringUtil::trim($_POST['templatePackName']);
		if (isset($_POST['templatePackFolderName'])) $this->templatePackFolderName = StringUtil::trim($_POST['templatePackFolderName']);
		if (!empty($this->templatePackFolderName)) $this->templatePackFolderName = FileUtil::addTrailingSlash($this->templatePackFolderName);
		if (isset($_POST['parentTemplatePackID'])) $this->parentTemplatePackID = intval($_POST['parentTemplatePackID']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->validateName();
		$this->validateFolderName();
	}
 	
	/**
	 * Validates the template pack name.
	 */
 	protected function validateName() {
 		if (empty($this->templatePackName) || str_replace('/', '', $this->templatePackName) == '') {
			throw new UserInputException('templatePackName');
		}
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_template_pack
			WHERE	templatePackName = '".escapeString($this->templatePackName)."'";
		$row = WCF::getDB()->getFirstRow($sql);
		if ($row['count']) {
			throw new UserInputException('templatePackName', 'notUnique');
		}
 	}
 	
 	/**
	 * Validates the template pack folder name.
	 */
 	protected function validateFolderName() {
 		if (empty($this->templatePackFolderName)) {
			throw new UserInputException('templatePackFolderName');
		}
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_template_pack
			WHERE	templatePackFolderName = '".escapeString($this->templatePackFolderName)."'
				AND parentTemplatePackID = ".$this->parentTemplatePackID;
		$row = WCF::getDB()->getFirstRow($sql);
		if ($row['count']) {
			throw new UserInputException('templatePackFolderName', 'notUnique');
		}
 	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();

		// save pack
		$this->templatePackID = TemplatePackEditor::create($this->templatePackName, $this->templatePackFolderName, $this->parentTemplatePackID);
		
		// reset cache
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.templatePacks.php');
		$this->saved();
		
		// reset values
		$this->templatePackName = $this->templatePackFolderName = '';
		$this->parentTemplatePackID = 0;
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->availableTemplatePacks = TemplatePack::getSelectList();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'templatePackName' => $this->templatePackName,	
			'templatePackFolderName' => $this->templatePackFolderName,
			'action' => 'add',
			'parentTemplatePackID' => $this->parentTemplatePackID,
			'availableTemplatePacks' => $this->availableTemplatePacks
		));
	}
}
?>