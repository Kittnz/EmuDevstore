<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/DynamicOptionListForm.class.php');
require_once(WCF_DIR.'lib/acp/option/Options.class.php');

/**
 * Shows the option edit form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class OptionForm extends DynamicOptionListForm {
	public $templateName = 'option';
	
	public $category;
	public $categoryID = 0;
	public $activeTabMenuItem = '';
	public $options;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['categoryID'])) $this->categoryID = intval($_REQUEST['categoryID']);
		
		// get option category
		$this->getOptionCategory();
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['activeTabMenuItem'])) $this->activeTabMenuItem = $_POST['activeTabMenuItem'];
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save options and affected package ids
		$saveOptions = $affectedPackageIDArray = array();
		foreach ($this->cachedOptions as $option) {
			$saveOptions[$option['optionID']] = $option['optionValue'];
			$affectedPackageIDArray[] = $option['packageID'];
		}
		Options::save($saveOptions);
		
		// clear cache
		Options::resetCache();
		
		// delete relevant options.inc.php's
		$affectedPackageIDArray = array_unique($affectedPackageIDArray);
		Options::resetFile($affectedPackageIDArray);
		$this->saved();
		
		// show succes message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->options = $this->getOptionTree($this->category['categoryName']);
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
			'category' => $this->category,
			'options' => $this->options,
			'activeTabMenuItem' => $this->activeTabMenuItem
		));
	}
	
	/**
	 * @see Form::show()
	 */
	public function show() {
		// set active menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.option.category.'.$this->category['categoryName']);
		
		// check permission
		WCF::getUser()->checkPermission('admin.system.canEditOption');

		if ($this->activeCategory == 'module') {
			// check master password
			WCFACP::checkMasterPassword();
		}
		
		// get options and categories from cache
		$this->readCache();
		
		// show form
		parent::show();
	}
	
	/**
	 * Gets the active option category.
	 */
	protected function getOptionCategory() {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_option_category
			WHERE	categoryID = ".$this->categoryID."
				AND parentCategoryName = ''";
		$this->category = WCF::getDB()->getFirstRow($sql);
		if (!isset($this->category['categoryID'])) {
			throw new IllegalLinkException();
		}
		
		$this->activeCategory = $this->category['categoryName'];
	}
	
	/**
	 * @see DynamicOptionListForm::checkOption()
	 */
	protected function checkOption($optionName) {
		if (!parent::checkOption($optionName)) return false;
		$option = $this->cachedOptions[$optionName];
		return ($option['hidden'] != 1);
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
	
}
?>