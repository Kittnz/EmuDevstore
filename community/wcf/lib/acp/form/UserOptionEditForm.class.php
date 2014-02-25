<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/UserOptionAddForm.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionEditor.class.php');

/**
 * Shows the user option edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.option
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class UserOptionEditForm extends UserOptionAddForm {
	public $activeMenuItem = 'wcf.acp.menu.link.user.option';
	public $neededPermissions = 'admin.user.option.canEditOption';
	
	public $userOption;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		AbstractForm::readParameters();
		
		if (isset($_REQUEST['optionID'])) $this->optionID = intval($_REQUEST['optionID']);
		$this->userOption = new UserOptionEditor($this->optionID);
		if (!$this->userOption->optionID || $this->userOption->editable > 3 || $this->userOption->visible > 3) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see UserOptionAddForm::getOutputClass()
	 */
	protected function getOutputClass() {
		if ($this->userOption->outputClass && !in_array($this->userOption->outputClass, self::$selectableOutputClasses)) {
			return $this->userOption->outputClass;
		}
		
		return parent::getOutputClass();
	}
	
	/**
	 * @see Form::validate()
	 */
	public function save() {
		AbstractForm::save();

		// update
		$this->userOption->update($this->userOption->optionName, $this->categoryName, $this->optionType, $this->defaultValue, $this->validationPattern, $this->selectOptions, '', $this->required, $this->askDuringRegistration, $this->editable, $this->visible, $this->getOutputClass(), $this->searchable, $this->showOrder);
		
		// update languages variables
		require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
		$language = new LanguageEditor(WCF::getLanguage()->getLanguageID());
		$language->updateItems(array('wcf.user.option.'.$this->userOption->optionName => $this->optionName, 'wcf.user.option.'.$this->userOption->optionName.'.description' => $this->optionDescription), 0, PACKAGE_ID, array('wcf.user.option.'.$this->userOption->optionName => 1, 'wcf.user.option.'.$this->userOption->optionName.'.description' => 1));
		
		// delete cache
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.user-option-*');
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
			'userOption' => $this->userOption,
			'optionID' => $this->optionID,
			'action' => 'edit',
			'outputClassSelectable' => (!$this->userOption->outputClass || in_array($this->userOption->outputClass, self::$selectableOutputClasses))
		));
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// default value
			$this->optionName = WCF::getLanguage()->get('wcf.user.option.'.$this->userOption->optionName);
			$this->optionDescription = WCF::getLanguage()->get('wcf.user.option.'.$this->userOption->optionName.'.description');
			$this->categoryName = $this->userOption->categoryName;
			$this->optionType = $this->userOption->optionType;
			$this->defaultValue = $this->userOption->defaultValue;
			$this->validationPattern = $this->userOption->validationPattern;
			$this->selectOptions = $this->userOption->selectOptions;
			$this->required = $this->userOption->required;
			$this->askDuringRegistration = $this->userOption->askDuringRegistration;
			$this->editable = $this->userOption->editable;
			$this->visible = $this->userOption->visible;
			$this->searchable = $this->userOption->searchable;
			$this->showOrder = $this->userOption->showOrder;
			
			if ($this->userOption->outputClass == 'UserOptionOutputNewlineToBreak') $this->showLineBreaks = 1;
			if ($this->userOption->outputClass == 'UserOptionOutputURL') $this->textFormat = 'link';
			if ($this->userOption->outputClass == 'UserOptionOutputImage') $this->textFormat = 'image';
		}
	}
}
?>