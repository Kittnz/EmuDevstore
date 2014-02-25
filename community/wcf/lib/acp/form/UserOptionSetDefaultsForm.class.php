<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/DynamicOptionListForm.class.php');

/**
 * Provides functions to set the default values of user options.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.option
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class UserOptionSetDefaultsForm extends DynamicOptionListForm {
	// system
	public $cacheName = 'user-option-';
	public $templateName = 'userOptionSetDefaults';
	public $menuItemName = 'wcf.acp.menu.link.user.option.setDefaults';
	public $permission = 'admin.user.option.canEditOption';
	public $activeCategory = 'settings';
	
	// parameters
	public $applyChangesToExistingUsers = 0;
	
	/**
	 * options tree
	 *
	 * @var array
	 */
	public $options = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['applyChangesToExistingUsers'])) $this->applyChangesToExistingUsers = intval($_POST['applyChangesToExistingUsers']);
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save
		$updateOptionValueUpdate = '';
		foreach ($this->activeOptions as $option) {
			if ($option['defaultValue'] != $option['optionValue']) {
				$sql = "UPDATE	wcf".WCF_N."_user_option
					SET	defaultValue = '".escapeString($option['optionValue'])."'
					WHERE	optionID = ".$option['optionID'];
				WCF::getDB()->sendQuery($sql);
				
				if (!empty($updateOptionValueUpdate)) $updateOptionValueUpdate .= ',';
				$updateOptionValueUpdate .= 'userOption'.$option['optionID']."='".escapeString($option['optionValue'])."'";
			}
		}
		
		// apply to existing users
		if ($this->applyChangesToExistingUsers == 1 && !empty($updateOptionValueUpdate)) {
			$sql = "UPDATE	wcf".WCF_N."_user_option_value
				SET	".$updateOptionValueUpdate;
			WCF::getDB()->sendQuery($sql);
			
			// reset sessions
			Session::resetSessions();
		}
		
		// reset cache
		WCF::getCache()->clearResource($this->cacheName.PACKAGE_ID);
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->options = $this->getOptionTree($this->activeCategory);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'options' => $this->options,
			'applyChangesToExistingUsers' => $this->applyChangesToExistingUsers
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
		
		// get user options and categories from cache
		$this->readCache();
		
		// show form
		parent::show();
	}
}
?>