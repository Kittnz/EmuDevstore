<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/option/category/UserOptionCategoryEditor.class.php');

/**
 * Shows the form for adding new user option categories
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.option
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class UserOptionCategoryAddForm extends ACPForm {
	// system
	public $templateName = 'userOptionCategoryAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.user.option.category.add';
	public $neededPermissions = 'admin.user.option.canAddOptionCategory';
	
	// parameters
	public $categoryName = '';
	public $showOrder = 0;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['categoryName'])) $this->categoryName = StringUtil::trim($_POST['categoryName']);
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->categoryName)) {
			throw new UserInputException('categoryName');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// create random name
		$name = "userOptionCategory".time();
		
		// save
		$userOptionCategory = UserOptionCategoryEditor::create($name, 'profile', '', '', $this->showOrder);
		
		// change name
		$userOptionCategory->update('userOptionCategory'.$userOptionCategory->categoryID, 'profile', '', '', $this->showOrder);
		
		// save name as language variable
		require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
		$language = new LanguageEditor(WCF::getLanguage()->getLanguageID());
		$language->updateItems(array('wcf.user.option.category.'.'userOptionCategory'.$userOptionCategory->categoryID => $this->categoryName));
		
		// delete cache
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.user-option-*');
		$this->saved();
		
		// reset values
		$this->categoryName = '';
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
			'categoryName' => $this->categoryName,
			'showOrder' => $this->showOrder
		));
	}
}
?>