<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionEditor.class.php');

/**
 * Shows the user option add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.option
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class UserOptionAddForm extends ACPForm {
	public $templateName = 'userOptionAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.user.option.add';
	public $neededPermissions = 'admin.user.option.canAddOption';
	public $categories = array();
	public $optionID = 0;
	
	public static $optionTypes = array('birthday', 'boolean', 'date', 'integer', 'float', 'password', 'multiselect', 'radiobuttons', 'select', 'text', 'textarea', 'message');
	public static $optionTypesUsingSelectOptions = array('longselect', 'multiselect', 'radiobuttons', 'select');
	public static $selectableOutputClasses = array('UserOptionOutputDate', 'UserOptionOutputSelectOptions', 'UserOptionOutputURL', 'UserOptionOutputImage', 'UserOptionOutputNewlineToBreak');
	
	// data
	public $optionName = '';
	public $optionDescription = '';
	public $categoryName = '';
	public $optionType = '';
	public $defaultValue = '';
	public $validationPattern = '';
	public $selectOptions = '';
	public $required = 0;
	public $askDuringRegistration = 0;
	public $editable = 0;
	public $visible = 0;
	public $searchable = 0;
	public $showOrder = 0;
	public $showLineBreaks = 0;
	public $textFormat = '';
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['optionName'])) $this->optionName = StringUtil::trim($_POST['optionName']);
		if (isset($_POST['optionDescription'])) $this->optionDescription = $_POST['optionDescription'];
		if (isset($_POST['categoryName'])) $this->categoryName = StringUtil::trim($_POST['categoryName']);
		if (isset($_POST['optionType'])) $this->optionType = StringUtil::trim($_POST['optionType']);
		if (isset($_POST['defaultValue'])) $this->defaultValue = $_POST['defaultValue'];
		if (isset($_POST['validationPattern'])) $this->validationPattern = $_POST['validationPattern'];
		if (isset($_POST['selectOptions'])) $this->selectOptions = $_POST['selectOptions'];
		if (isset($_POST['required'])) $this->required = intval($_POST['required']);
		if (isset($_POST['askDuringRegistration'])) $this->askDuringRegistration = intval($_POST['askDuringRegistration']);
		if (isset($_POST['editable'])) $this->editable = intval($_POST['editable']);
		if (isset($_POST['visible'])) $this->visible = intval($_POST['visible']);
		if (isset($_POST['searchable'])) $this->searchable = intval($_POST['searchable']);
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		if (isset($_POST['showLineBreaks'])) $this->showLineBreaks = intval($_POST['showLineBreaks']);
		if (isset($_POST['textFormat'])) $this->textFormat = $_POST['textFormat'];
	}
	
	/**
	 * Validates the option name.
	 */
	protected function validateOptionName() {
		if (empty($this->optionName)) {
			throw new UserInputException('optionName');
		}
		/*
		$sql = "SELECT	optionID
			FROM	wcf".WCF_N."_user_option
			WHERE	optionName = '".escapeString($this->optionName)."'";
		$row = WCF::getDB()->getFirstRow($sql);
		if (isset($row['optionID'])) {
			throw new UserInputException('optionName', 'notUnique');
		}
		*/
	}
	
	/**
	 * Validates the category name.
	 */
	protected function validateCategoryName() {
		if (empty($this->categoryName)) {
			throw new UserInputException('categoryName');
		}
		
		$sql = "SELECT	categoryID
			FROM	wcf".WCF_N."_user_option_category
			WHERE	categoryName = '".escapeString($this->categoryName)."'";
		$row = WCF::getDB()->getFirstRow($sql);
		if (!isset($row['categoryID'])) {
			throw new UserInputException('categoryName');
		}
	}
	
	/**
	 * Validates the option type.
	 */
	protected function validateOptionType() {
		if (!in_array($this->optionType, self::$optionTypes)) {
			throw new UserInputException('optionType');
		}
	}
	
	/**
	 * Validates the select options.
	 */
	protected function validateSelectOptions() {
		if (in_array($this->optionType, self::$optionTypesUsingSelectOptions) && empty($this->selectOptions)) {
			throw new UserInputException('selectOptions');
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->validateOptionName();
		$this->validateCategoryName();
		$this->validateOptionType();
		$this->validateSelectOptions();
	}
	
	
	protected function getOutputClass() {
		switch ($this->optionType) {
			case 'birthday':
			case 'date':
				return 'UserOptionOutputDate';
			case 'radiobuttons':
			case 'select':
			case 'multiselect':
				return 'UserOptionOutputSelectOptions';
			case 'text':
				if ($this->textFormat == 'link') return 'UserOptionOutputURL';
				if ($this->textFormat == 'image') return 'UserOptionOutputImage';
				break;
			case 'textarea':
				if ($this->showLineBreaks) return 'UserOptionOutputNewlineToBreak';
				break;
			case 'message':
				return 'UserOptionOutputMessage';
				break;
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// create random name
		$name = "userOption".time();
		
		// save option
		$this->optionID = UserOptionEditor::create($name, $this->categoryName, $this->optionType, $this->defaultValue, $this->validationPattern, $this->selectOptions, '', $this->required, $this->askDuringRegistration, $this->editable, $this->visible, $this->getOutputClass(), $this->searchable, $this->showOrder);
		
		// change random name
		$name = "userOption".$this->optionID;
		WCF::getDB()->sendQuery("UPDATE		wcf".WCF_N."_user_option
					SET		optionName = '$name'
					WHERE 		optionID = ".$this->optionID);
		
		// save language variables
		require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
		$language = new LanguageEditor(WCF::getLanguage()->getLanguageID());
		$language->updateItems(array('wcf.user.option.'.$name => $this->optionName, 'wcf.user.option.'.$name.'.description' => $this->optionDescription));
		
		// delete cache
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.user-option-*');
		$this->saved();
		
		// reset values
		$this->optionName = $this->optionDescription = $this->categoryName = $this->optionType = $this->defaultValue = $this->validationPattern = '';
		$this->optionType = $this->selectOptions = '';
		$this->required = $this->editable = $this->visible = $this->searchable = $this->showOrder = $this->askDuringRegistration = 0;
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readCategories();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'optionName' => $this->optionName,
			'optionDescription' => $this->optionDescription,
			'categoryName' => $this->categoryName,
			'optionType' => $this->optionType,
			'defaultValue' => $this->defaultValue,
			'validationPattern' => $this->validationPattern,
			'optionType' => $this->optionType,
			'selectOptions' => $this->selectOptions,
			'required' => $this->required,
			'askDuringRegistration' => $this->askDuringRegistration,
			'editable' => $this->editable,
			'visible' => $this->visible,
			'searchable' => $this->searchable,
			'showOrder' => $this->showOrder,
			'action' => 'add',
			'categories' => $this->categories,
			'optionTypes' => self::$optionTypes,
			'optionTypesUsingSelectOptions' => self::$optionTypesUsingSelectOptions,
			'showLineBreaks' => $this->showLineBreaks,
			'textFormat' => $this->textFormat,
			'outputClassSelectable' => true
		));
	}
	
	/**
	 * Gets a list of available user option categories.
	 */
	protected function readCategories() {
		$sql = "SELECT		categoryName, categoryID 
			FROM		wcf".WCF_N."_user_option_category option_category,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		option_category.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".PACKAGE_ID."
					AND option_category.parentCategoryName = 'profile'
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$optionCategories = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$optionCategories[$row['categoryName']] = $row['categoryID'];
		}
		
		if (count($optionCategories) > 0) {
			// get needed option categories
			$sql = "SELECT		option_category.*, package.packageDir
				FROM		wcf".WCF_N."_user_option_category option_category
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = option_category.packageID)
				WHERE		categoryID IN (".implode(',', $optionCategories).")
				ORDER BY	showOrder";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->categories[$row['categoryName']] = WCF::getLanguage()->get('wcf.user.option.category.'.$row['categoryName']);
			}
		}
	}
}
?>