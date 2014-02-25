<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/DynamicOptionListForm.class.php');

/**
 * Shows the group add form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class GroupAddForm extends DynamicOptionListForm {
	public $permission = 'admin.user.canAddGroup';
	public $templateName = 'groupAdd';
	public $menuItemName = 'wcf.acp.menu.link.group.add';
	
	public $cacheName = 'group-option-';
	public $additionalFields = array();
	public $group;
	public $options;
	
	public $groupName = '';
	public $activeTabMenuItem = '';
	public $activeSubTabMenuItem = '';
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['groupName'])) $this->groupName = StringUtil::trim($_POST['groupName']);
		if (isset($_POST['activeTabMenuItem'])) $this->activeTabMenuItem = $_POST['activeTabMenuItem'];
		if (isset($_POST['activeSubTabMenuItem'])) $this->activeSubTabMenuItem = $_POST['activeSubTabMenuItem'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		// validate dynamic options
		parent::validate();
		
		// validate group name
		try {
			if (empty($this->groupName)) {
				throw new UserInputException('groupName');
			}
		}
		catch (UserInputException $e) {
			$this->errorType[$e->getField()] = $e->getType();
		}
	
		if (count($this->errorType) > 0) {
			throw new UserInputException('groupName', $this->errorType);
		}		
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// create
		require_once(WCF_DIR.'lib/data/user/group/GroupEditor.class.php');
		$this->group = GroupEditor::create($this->groupName, $this->activeOptions, $this->additionalFields);
		$this->saved();
		
		// show empty add form
		WCF::getTPL()->assign(array(
			'success' => true,
			'newGroup' => $this->group
		));
		
		// reset values
		$this->groupName = '';
		
		foreach ($this->activeOptions as $key => $option) {
			unset($this->activeOptions[$key]['optionValue']);
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->options = $this->getOptionTree();
		if (!count($_POST)) {
			$this->activeTabMenuItem = $this->options[0]['categoryName'];
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'groupName' 		=> $this->groupName,
			'options' 		=> $this->options,
			'action'		=> 'add',
			'activeTabMenuItem' 	=> $this->activeTabMenuItem,
			'activeSubTabMenuItem' 	=> $this->activeSubTabMenuItem
		));
	}

	/**
	 * @see Form::show()
	 */
	public function show() {
		// set active menu item
		WCFACP::getMenu()->setActiveMenuItem($this->menuItemName);
		
		// check permission
		WCF::getUser()->checkPermission($this->permission);
		
		// check master password
		WCFACP::checkMasterPassword();
		
		// get user options and categories from cache
		$this->readCache();
		
		// show form
		parent::show();
	}
	
	/**
	 * Returns the tree of options.
	 * 
	 * @param	string		$parentCategoryName
	 * @param	integer		$level
	 * @return	array
	 */
	protected function getOptionTree($parentCategoryName = '', $level = 0) {
		$options = array();
		
		if (isset($this->cachedCategoryStructure[$parentCategoryName])) {
			// get super categories
			foreach ($this->cachedCategoryStructure[$parentCategoryName] as $superCategoryName) {
				$superCategory = $this->cachedCategories[$superCategoryName];
				
				if ($this->checkCategory($superCategory)) {
					if ($level <= 1) {
						$superCategory['categories'] = $this->getOptionTree($superCategoryName, $level + 1);
					}
					if ($level > 1 || count($superCategory['categories']) == 0) {
						$superCategory['options'] = $this->getCategoryOptions($superCategoryName);
					}
					else {
						$superCategory['options'] = $this->getCategoryOptions($superCategoryName, false);
					}
					
					if ((isset($superCategory['categories']) && count($superCategory['categories']) > 0) || (isset($superCategory['options']) && count($superCategory['options']) > 0)) {
						$options[] = $superCategory;
					}
				}
			}
		}
	
		return $options;
	}
	
	/**
	 * @see DynamicOptionListForm::getTypeObject()
	 */
	protected function getTypeObject($type) {
		if (!isset($this->typeObjects[$type])) {
			$className = 'GroupOptionType'.ucfirst(strtolower($type));
			$classPath = WCF_DIR.'lib/acp/group/'.$className.'.class.php';
			
			// include class file
			if (!file_exists($classPath)) {
				throw new SystemException("unable to find class file '".$classPath."'", 11000);
			}
			require_once($classPath);
			
			// create instance
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'", 11001);
			}
			$this->typeObjects[$type] = new $className();
		}
		
		return $this->typeObjects[$type];
	}
}
?>