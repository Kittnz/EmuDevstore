<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/template/TemplatePackEditor.class.php');
require_once(WCF_DIR.'lib/data/template/TemplateEditor.class.php');

/**
 * Shows the template add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.template
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class TemplateAddForm extends ACPForm {
	public $templateName = 'templateAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.template.add';
	public $neededPermissions = 'admin.template.canAddTemplate';
	
	public $templatePackID = 0;
	public $templatePackName = '';
	public $templatePackFolderName = '';
	public $tplName = '';
	public $source = '';
	public $templatePacks = array();
	public $template;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_GET['templatePackID'])) $this->templatePackID = intval($_GET['templatePackID']);
		
		// get available template packs
		$this->templatePacks = TemplatePackEditor::getTemplatePacks();
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['templatePackID'])) $this->templatePackID = intval($_POST['templatePackID']);
		if (isset($_POST['templatePackName'])) $this->templatePackName = StringUtil::trim($_POST['templatePackName']);
		if (isset($_POST['templatePackFolderName'])) $this->templatePackFolderName = StringUtil::trim($_POST['templatePackFolderName']);
		if (isset($_POST['templateName'])) $this->tplName = StringUtil::trim($_POST['templateName']);
		if (isset($_POST['source'])) $this->source = $_POST['source'];
	}
	
	/**
	 * Validates the given template name.
	 */
	protected function validateTemplateName() {
		if (empty($this->tplName)) {
			throw new UserInputException('templateName');
		}
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_template
			WHERE	packageID = ".PACKAGE_ID."
				AND templatePackID = ".$this->templatePackID."
				AND templateName = '".escapeString($this->tplName)."'";
		$row = WCF::getDB()->getFirstRow($sql);
		if ($row['count']) {
			throw new UserInputException('templateName', 'notUnique');
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (count($this->templatePacks) > 0) {
			if (!$this->templatePackID) {
				throw new UserInputException('templatePackID');
			}
			
			// template pack
			if ($this->templatePackID) {
				$templatePack = new TemplatePackEditor($this->templatePackID);
				if (!$templatePack->templatePackID) {
					throw new UserInputException('templatePackID');
				}
			}
		}
		else {
			if (empty($this->templatePackName)) {
				throw new UserInputException('templatePackName');
			}
			
			if (empty($this->templatePackFolderName)) {
				throw new UserInputException('templatePackFolderName');
			}
			
			// create template pack
			$this->templatePackID = TemplatePackEditor::create($this->templatePackName, FileUtil::addTrailingSlash($this->templatePackFolderName));
				
			// get available template packs
			$this->templatePacks = TemplatePackEditor::getTemplatePacks();
			
			// reset values
			$this->templatePackName = $this->templatePackFolderName = '';
		}
		
		// template name
		$this->validateTemplateName();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save template
		$this->template = TemplateEditor::create($this->tplName, $this->source, $this->templatePackID);
		
		// reset cache
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.templates-*.php');
		
		// reset values
		$this->tplName = $this->source = '';
		$this->templatePackID = 0;
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'templatePacks' => $this->templatePacks,
			'templatePackID' => $this->templatePackID,	
			'templateName' => $this->tplName,
			'source' => $this->source,
			'action' => 'add',
			'templatePackName' => $this->templatePackName,	
			'templatePackFolderName' => $this->templatePackFolderName
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