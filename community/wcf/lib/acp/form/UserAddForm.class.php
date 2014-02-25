<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/UserOptionListForm.class.php');

/**
 * Shows the user add form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UserAddForm extends UserOptionListForm {
	public $templateName = 'userAdd';
	public $menuItemName = 'wcf.acp.menu.link.user.add';
	public $permission = 'admin.user.canAddUser';
	
	public $username = '';
	public $email = '';
	public $confirmEmail = '';
	public $password = '';
	public $confirmPassword = '';
	public $groupIDs = array();
	public $languageID = 0;
	public $visibleLanguages = array();
	public $additionalFields = array();
	public $options = array();
	
	/**
	 * user to add
	 *
	 * @var UserEditor
	 */
	public $user;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']); 
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
		if (isset($_POST['confirmEmail'])) $this->confirmEmail = StringUtil::trim($_POST['confirmEmail']);
		if (isset($_POST['password'])) $this->password = $_POST['password'];
		if (isset($_POST['confirmPassword'])) $this->confirmPassword = $_POST['confirmPassword'];
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
		if (isset($_POST['visibleLanguages']) && is_array($_POST['visibleLanguages'])) $this->visibleLanguages = ArrayUtil::toIntegerArray($_POST['visibleLanguages']);
		if (isset($_POST['languageID'])) $this->languageID = intval($_POST['languageID']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		// validate static user options 
		try {
			$this->validateUsername($this->username); 
		}
		catch (UserInputException $e) {
			$this->errorType[$e->getField()] = $e->getType();
		}
		
		try {
			$this->validateEmail($this->email, $this->confirmEmail); 
		}
		catch (UserInputException $e) {
			$this->errorType[$e->getField()] = $e->getType();
		}
		
		try {
			$this->validatePassword($this->password, $this->confirmPassword);
		}
		catch (UserInputException $e) {
			$this->errorType[$e->getField()] = $e->getType();
		}
		
		// validate user groups
		if (count($this->groupIDs) > 0) {
			require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
			$sql = "SELECT	groupID
				FROM	wcf".WCF_N."_group
				WHERE	groupID IN (".implode(',', $this->groupIDs).")
					AND groupType NOT IN (".Group::GUESTS.", ".Group::EVERYONE.", ".Group::USERS.")";
			$result = WCF::getDB()->sendQuery($sql);
			$this->groupIDs = array();
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (Group::isAccessibleGroup($row['groupID'])) {
					$this->groupIDs[] = $row['groupID'];
				}
			}
		}
		
		// validate user language
		require_once(WCF_DIR.'lib/system/language/Language.class.php');
		if (!Language::getLanguage($this->languageID)) {
			// use default language
			$this->languageID = Language::getDefaultLanguageID();
		}
		
		// validate visible languages
		foreach ($this->visibleLanguages as $key => $visibleLanguage) {
			if (!($language = Language::getLanguage($visibleLanguage)) || !$language['hasContent']) {
				unset($this->visibleLanguages[$key]);
			}
		}
		if (!count($this->visibleLanguages) && ($language = Language::getLanguage($this->languageID)) && $language['hasContent']) {
			$this->visibleLanguages[] = $this->languageID;
		}
		
		// validate dynamic options
		parent::validate();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// create
		$this->additionalFields['languageID'] = $this->languageID;
		require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
		$this->user = UserEditor::create($this->username, $this->email, $this->password, $this->groupIDs, $this->activeOptions, $this->additionalFields, $this->visibleLanguages);
		$this->saved();
		
		// show empty add form
		WCF::getTPL()->assign(array(
			'success' => true,
			'newUser' => $this->user
		));
		
		// reset values
		$this->username = $this->email = $this->confirmEmail = $this->password = $this->confirmPassword = '';
		$this->groupIDs = array();
		$this->languageID = $this->getDefaultFormLanguageID();
		
		foreach ($this->activeOptions as $key => $option) {
			unset($this->activeOptions[$key]['optionValue']);
		}
	}
	
	/**
	 * Throws a UserInputException if the username is not unique or not valid.
	 * 
	 * @param	string		$username
	 */
	protected function validateUsername($username) {
		if (empty($username)) {
			throw new UserInputException('username');
		}
		
		// check for forbidden chars (e.g. the ",")
		if (!UserUtil::isValidUsername($username)) {
			throw new UserInputException('username', 'notValid');
		}
		
		// Check if username exists already.
		if (!UserUtil::isAvailableUsername($username)) {
			throw new UserInputException('username', 'notUnique');
		}
	}
	
	/**
	 * Throws a UserInputException if the email is not unique or not valid.
	 * 
	 * @param	string		$email
	 * @param	string		$confirmEmail
	 */
	protected function validateEmail($email, $confirmEmail) {
		if (empty($email)) {	
			throw new UserInputException('email');
		}
		
		// check for valid email (one @ etc.)
		if (!UserUtil::isValidEmail($email)) {
			throw new UserInputException('email', 'notValid');
		}
		
		// Check if email exists already.
		if (!UserUtil::isAvailableEmail($email)) {
			throw new UserInputException('email', 'notUnique');
		}
		
		// check confirm input
		if (StringUtil::toLowerCase($email) != StringUtil::toLowerCase($confirmEmail)) {
			throw new UserInputException('confirmEmail', 'notEqual');
		}
	}
	
	/**
	 * Throws a UserInputException if the password is not valid.
	 * 
	 * @param	string		$password
	 * @param	string		$confirmPassword
	 */
	protected function validatePassword($password, $confirmPassword) {
		if (empty($password)) {
			throw new UserInputException('password');
		}
		
		// check confirm input
		if ($password != $confirmPassword) {
			throw new UserInputException('confirmPassword', 'notEqual');
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->options = $this->getOptionTree();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' 			=> $this->username,
			'email'				=> $this->email,
			'confirmEmail'			=> $this->confirmEmail,
			'password'			=> $this->password,
			'confirmPassword'		=> $this->confirmPassword,
			'groupIDs'			=> $this->groupIDs,
			'options' 			=> $this->options,
			'availableGroups'		=> $this->getAvailableGroups(),
			'availableLanguages'		=> $this->getAvailableLanguages(),
			'languageID'			=> $this->languageID,
			'visibleLanguages'		=> $this->visibleLanguages,
			'availableContentLanguages' 	=> $this->getAvailableContentLanguages(),
			'action'			=> 'add',
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// set active menu item
		WCFACP::getMenu()->setActiveMenuItem($this->menuItemName);
		
		// check permission
		WCF::getUser()->checkPermission($this->permission);
		
		// get the default langauge id
		$this->languageID = $this->getDefaultFormLanguageID();
		
		// get user options and categories from cache
		$this->readCache();
		
		// show form
		parent::show();
	}
	
	/**
	 * @see DynamicOptionListForm::checkOption()
	 */
	protected function checkOption($optionName) {
		if (!parent::checkOption($optionName)) return false;
		$option = $this->cachedOptions[$optionName];
		return ($option['editable'] != 1 &&  $option['editable'] != 4 && !$option['disabled']);
	}
	
	/**
	 * @see DynamicOptionListForm::getOptionTree()
	 */
	protected function getOptionTree($parentCategoryName = '', $level = 0) {
		$options = array();
		
		if (isset($this->cachedCategoryStructure[$parentCategoryName])) {
			// get super categories
			foreach ($this->cachedCategoryStructure[$parentCategoryName] as $superCategoryName) {
				$superCategory = $this->cachedCategories[$superCategoryName];
				$superCategory['options'] = array();
				
				if ($this->checkCategory($superCategory)) {
					if ($level <= 0) {
						$superCategory['categories'] = $this->getOptionTree($superCategoryName, $level + 1);
					}
					if ($level > 0 || count($superCategory['categories']) == 0) {
						$superCategory['options'] = $this->getCategoryOptions($superCategoryName);
					}
					
					if ((isset($superCategory['categories']) && count($superCategory['categories']) > 0) || (isset($superCategory['options']) && count($superCategory['options']) > 0)) {
						$options[] = $superCategory;
					}
				}
			}
		}
	
		return $options;
	}
}
?>