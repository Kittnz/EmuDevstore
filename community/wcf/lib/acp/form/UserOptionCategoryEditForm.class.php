<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/UserOptionCategoryAddForm.class.php');

/**
 * Shows the form for editing user option categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.option
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class UserOptionCategoryEditForm extends UserOptionCategoryAddForm {
	// system
	public $activeMenuItem = 'wcf.acp.menu.link.user.option';
	public $neededPermissions = 'admin.user.option.canEditOptionCategory';
	
	// parameters
	public $categoryID = 0;
	
	/**
	 * category editor object
	 * 
	 * @var	UserOptionCategoryEditor
	 */
	public $category = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['categoryID'])) $this->categoryID = intval($_REQUEST['categoryID']);
		$this->category = new UserOptionCategoryEditor($this->categoryID);
		if (!$this->category->categoryID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		ACPForm::save();
		
		// save
		$this->category->update($this->category->categoryName, 'profile', $this->category->categoryIconS, $this->category->categoryIconM, $this->showOrder);
		
		// update language variable
		require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
		$language = new LanguageEditor(WCF::getLanguage()->getLanguageID());
		$language->updateItems(array('wcf.user.option.category.'.$this->category->categoryName => $this->categoryName), 0, PACKAGE_ID, array('wcf.user.option.category.'.$this->category->categoryName => 1));
		
		// delete cache
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.user-option-*');
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
			$this->categoryName = WCF::getLanguage()->get('wcf.user.option.category.'.$this->category->categoryName);
			$this->showOrder = $this->category->showOrder;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'categoryID' => $this->categoryID,
			'category' => $this->category
		));
	}
}
?>