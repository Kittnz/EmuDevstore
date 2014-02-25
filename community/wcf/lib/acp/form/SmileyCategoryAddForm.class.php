<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/category/SmileyCategoryEditor.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/SmileyEditor.class.php');

/**
 * Shows the form for adding smiley categories.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class SmileyCategoryAddForm extends ACPForm {
	// system
	public $templateName = 'smileyCategoryAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.smiley.category.add';
	public $neededPermissions = 'admin.smiley.canAddSmileyCategory';
	
	// parameters
	public $title = '';
	public $showOrder = 0;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->title)) {
			throw new UserInputException('title');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save
		SmileyCategoryEditor::create($this->title, $this->showOrder);
		
		// reset cache
		SmileyEditor::resetCache();
		$this->saved();
		
		// reset values
		$this->title = '';
		$this->showOrder = 0;

		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'title' => $this->title,
			'showOrder' => $this->showOrder
		));
	}
}
?>