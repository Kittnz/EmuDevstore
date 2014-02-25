<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/UserEditForm.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');

/**
 * Shows the user edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	form
 * @category 	Community Framework
 */
class UserProfileEditForm extends UserEditForm {
	/**
	 * @see AbstractPage::$templateName
	 */
	public $templateName = 'userProfileEdit';
	
	/**
	 * Holds the name of the active user option category
	 *
	 * @var string
	 */
	public $activeCategory = 'profile';
	
	/**
	 * Holds the id of the user's style
	 *
	 * @var integer
	 */
	public $styleID = 0;
	
	/**
	 * An array holding the cached category data of user options
	 *
	 * @var array
	 */
	public $categoryData = array();
	
	/**
	 * An array holding the cached user options of the active category
	 *
	 * @var array
	 */
	public $pageOptions = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		AbstractForm::readParameters();
		
		if (isset($_REQUEST['category'])) $this->activeCategory = $_REQUEST['category'];
		$this->user = WCF::getUser();
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['styleID'])) $this->styleID = intval($_POST['styleID']);
	}
	
	/**
	 * Sets the active usercp menu item.
	 */
	protected function setCategory() {
		$category = $this->activeCategory;
	
		// check category
		if (!isset($this->cachedCategories[$category])) {
			throw new IllegalLinkException();
		}
		
		// set active tab
		if ($category == 'profile') {
			UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.profile.personalDetails');
		}
		else {
			UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.option.category.'.$category);
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		UserOptionListForm::readData();
		
		if (!count($_POST)) {
			// get visible languages
			$this->readVisibleLanguages();
			
			// default values
			$this->readDefaultValues();
		}
		
		// add icon path
		$this->categoryData = $this->cachedCategories[$this->activeCategory];
		if (!empty($this->categoryData['categoryIconM'])) {
			// get relative path
			$path = '';
			if (empty($this->categoryData['packageDir'])) {
				$path = RELATIVE_WCF_DIR;
			}
			else {						
				$path = FileUtil::getRealPath(RELATIVE_WCF_DIR.$this->categoryData['packageDir']);
			}
			
			$this->categoryData['categoryIconM'] = $path . $this->categoryData['categoryIconM'];
		}
		
		// get path to category icons
		foreach ($this->cachedCategories as $key => $category) {
			// add icon path
			if (!empty($category['categoryIconM'])) {
				// get relative path
				$path = '';
				if (empty($category['packageDir'])) {
					$path = RELATIVE_WCF_DIR;
				}
				else {						
					$path = FileUtil::getRealPath(RELATIVE_WCF_DIR.$category['packageDir']);
				}
				
				$this->cachedCategories[$key]['categoryIconM'] = $path . $category['categoryIconM'];
			}
		}
		
		// get categories
		$this->options = $this->getOptionTree($this->activeCategory);
		if (count($this->options) == 0) $this->pageOptions = $this->getCategoryOptions($this->activeCategory);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		AbstractForm::assignVariables();
		
		WCF::getTPL()->assign(array(
			'options' => $this->pageOptions,
			'optionCategories' => $this->options,
			'category' => $this->activeCategory,
			'categoryData' => $this->categoryData,
			'languageID' => $this->languageID,
			'visibleLanguages' => $this->visibleLanguages,
			'availableLanguages' => ($this->activeCategory == 'settings.general' ? $this->getAvailableLanguages() : array()),
			'availableContentLanguages' => ($this->activeCategory == 'settings.general' ? $this->getAvailableContentLanguages() : array()),
			'availableStyles' => ($this->activeCategory == 'settings.display' ? StyleManager::getAvailableStyles() : array()),
			'styleID' => $this->styleID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		$this->styleID = $this->user->styleID;
				
		// get user options and categories from cache
		$this->readCache();
		
		// set active tab
		$this->setCategory();
		
		// show form
		AbstractForm::show();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save user
		if ($this->activeCategory == 'settings.general') {
			$this->additionalFields['languageID'] = $this->languageID;
		}
		if ($this->activeCategory == 'settings.display') {
			$this->additionalFields['styleID'] = $this->styleID;
			require_once(WCF_DIR.'lib/system/style/StyleManager.class.php');
			StyleManager::changeStyle($this->styleID);
		}
		
		$editor = WCF::getUser()->getEditor();
		$editor->update('', '', '', null, $this->activeOptions, $this->additionalFields, ($this->activeCategory == 'settings.general' ? $this->visibleLanguages : null));
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see DynamicOptionListForm::checkOption()
	 */
	protected function checkOption($optionName) {
		if (!DynamicOptionListForm::checkOption($optionName)) return false;
		$option = $this->cachedOptions[$optionName];
		// show options visible for and editable by user 
		return ($option['editable'] <= 1 && !$option['disabled']);
	}
	
	/**
	 * Does nothing.
	 * 
	 * @see UserAddForm::validateUsername()
	 */
	protected function validateUsername($username) {}
	
	/**
	 * Does nothing.
	 * 
	 * @see UserAddForm::validatePassword()
	 */
	protected function validatePassword($password, $confirmPassword) {}
	
	/**
	 * Does nothing.
	 * 
	 * @see UserAddForm::validateEmail()
	 */
	protected function validateEmail($email, $confirmEmail) {}
}
?>