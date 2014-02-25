<?php
// wcf imports
require_once(WCF_DIR.'lib/data/page/menu/PageMenuItemEditor.class.php');
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');

/**
 * Shows the page menu item add form.
 *
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.pageMenu
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class PageMenuItemAddForm extends ACPForm {
	// system	
	public $templateName = 'pageMenuItemAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.pageMenuItem.add';
	public $neededPermissions = 'admin.pageMenu.canAddPageMenuItem';
	
	// properties
	public $name = '';
	public $link = '';
	public $iconS = '';
	public $iconM = '';
	public $position = 'header';
	public $showOrder = 0;

	// item
	public $pageMenuItem = null;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		// data
		if (isset($_POST['name'])) $this->name = StringUtil::trim($_POST['name']);
		if (isset($_POST['link'])) $this->link = StringUtil::trim($_POST['link']);
		if (isset($_POST['iconS'])) $this->iconS = StringUtil::trim($_POST['iconS']);
		if (isset($_POST['iconM'])) $this->iconM = StringUtil::trim($_POST['iconM']);
		if (isset($_POST['position'])) $this->position = StringUtil::trim($_POST['position']);		
		if ($this->position != 'header' && $this->position != 'footer') $this->position = "header";
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);	
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		//  validate name
		$this->validateName();
		// validate link
		$this->validateLink();
		// validate icon
		$this->validateIcon();
	}
	
	/**
	 * Validates the given name.
	 */
	public function validateName() {
		// check if empty	
		if (empty($this->name)) {
			throw new UserInputException('name', 'empty');
		}
	}
	
	/**
	 * Validates the given link.
	 */
	public function validateLink() {
		// check if empty	
		if (empty($this->link)) {
			throw new UserInputException('link', 'empty');
		}
	}
	
	/**
	 * Validates the given icon.
	 */
	public function validateIcon() {
		// check if empty	
		/*if (empty($this->icon)) {
			throw new UserInputException('icon', 'empty');
		}*/
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		$this->pageMenuItem = PageMenuItemEditor::create($this->name, $this->link, $this->iconS, $this->iconM, $this->showOrder, $this->position, WCF::getLanguage()->getLanguageID());
		
		// reset values
		$this->name = $this->link = $this->iconS = $this->iconM = '';
		$this->position = 'header';
		$this->showOrder = 0;
		
		// delete cache
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
			'name' => $this->name,
			'link' => $this->link,
			'iconS' => $this->iconS,
			'iconM' => $this->iconM,
			'position' => $this->position,
			'showOrder' => $this->showOrder,
			'action' => 'add'
		));
	}
}
?>