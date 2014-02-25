<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/User.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutputContactInformation.class.php');

/**
 * Shows a list of user options.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.option
 * @category 	Community Framework
 */
class UserOptions {
	protected $categories = array();
	protected $options = array();
	protected $categoryStructure = array();
	protected $optionToCategories = array();
	protected $outputObjects = array();
	protected $outputType;
	protected $optionFilter;
	
	/**
	 * Creates a new UserOptions object.
	 */
	public function __construct($outputType = 'normal', $optionFilter = null) {
		$this->outputType = $outputType;
		$this->optionFilter = $optionFilter;
		$this->getCache();
	}
	
	/**
	 * Gets all user options and option categories from cache.
	 */
	protected function getCache() {
		$cacheName = 'user-option-'.PACKAGE_ID;
		WCF::getCache()->addResource($cacheName, WCF_DIR.'cache/cache.'.$cacheName.'.php', WCF_DIR.'lib/system/cache/CacheBuilderOption.class.php');
		$this->categories = WCF::getCache()->get($cacheName, 'categories');
		$this->options = WCF::getCache()->get($cacheName, 'options');
		$this->categoryStructure = WCF::getCache()->get($cacheName, 'categoryStructure');
		$this->optionToCategories = WCF::getCache()->get($cacheName, 'optionToCategories');
		
		// filter options
		if ($this->optionFilter != null) {
			foreach ($this->optionToCategories as $categoryName => $options) {
				foreach ($options as $key => $optionName) {
					if (!in_array($optionName, $this->optionFilter)) {
						unset($this->optionToCategories[$categoryName][$key]);
					}
				}
			}
		}
	}
	
	/**
	 * Returns the tree of options.
	 * 
	 * @return	array
	 */
	public function getOptionTree($parentCategoryName = '', User $user) {
		$options = array();
		
		if (isset($this->categoryStructure[$parentCategoryName])) {
			// get super categories
			foreach ($this->categoryStructure[$parentCategoryName] as $superCategoryName) {
				$superCategory = $this->categories[$superCategoryName];
				
				// add icon path
				if (!empty($superCategory['categoryIconM'])) {
					// get relative path
					$path = '';
					if (empty($superCategory['packageDir'])) {
						$path = RELATIVE_WCF_DIR;
					}
					else {						
						$path = FileUtil::getRealPath(RELATIVE_WCF_DIR.$superCategory['packageDir']);
					}
					
					$superCategory['categoryIconM'] = $path . $superCategory['categoryIconM'];
				}
				
				$superCategory['options'] = $this->getCategoryOptions($superCategoryName, $user);
				
				if (count($superCategory['options']) > 0) {
					$options[$superCategoryName] = $superCategory;
				}
			}
		}
	
		return $options;
	}
	
	/**
	 * Returns a list with the options of a specific option category.
	 * 
	 * @param	string		$categoryName
	 * @param	User		$user
	 * @return	array
	 */
	public function getCategoryOptions($categoryName = '', User $user) {
		$children = array();
		
		// get sub categories
		if (isset($this->categoryStructure[$categoryName])) {
			foreach ($this->categoryStructure[$categoryName] as $subCategoryName) {
				$children = array_merge($children, $this->getCategoryOptions($subCategoryName, $user));
			}
		}
		
		// get options
		if (isset($this->optionToCategories[$categoryName])) {
			foreach ($this->optionToCategories[$categoryName] as $optionName) {
				$option = $this->getOptionValue($optionName, $user);
				
				// add option to list
				if ($option) {
					$children[] = $option;
				}
			}
		}
		
		return $children;
	}
	
	/**
	 * Returns the data of a user option.
	 * 
	 * @return	array
	 */
	public function getOption($optionName) {
		if (isset($this->options[$optionName])) return $this->options[$optionName];
		return false;
	}
	
	
	/**
	 * Returns the formatted value of a user option.
	 */
	public function getOptionValue($optionName, User $user) {
		if (!isset($this->options[$optionName])) return false;
		
		$visible = ($this->options[$optionName]['visible'] == 0
			|| ($this->options[$optionName]['visible'] == 1 && ($user->userID == WCF::getUser()->userID || WCF::getUser()->getPermission('admin.general.canViewPrivateUserOptions')))
			|| ($this->options[$optionName]['visible'] == 2 && $user->userID == WCF::getUser()->userID)
			|| ($this->options[$optionName]['visible'] == 3 && WCF::getUser()->getPermission('admin.general.canViewPrivateUserOptions')));
		if (!isset($this->options[$optionName]) || !$visible || $this->options[$optionName]['disabled']) return false;

		// get option data
		$option = $this->options[$optionName];
		
		// get option value
		$option['optionValue'] = $user->{'userOption'.$option['optionID']};
		
		// use output class
		//if (!isset($option['optionValue'])) return false;
		if ($option['outputClass']) {
			$outputObj = $this->getOutputObject($option['outputClass']);
			
			if ($outputObj instanceof UserOptionOutputContactInformation) {
				$option['outputData'] = $outputObj->getOutputData($user, $option, $option['optionValue']);
			}
			
			if ($this->outputType == 'normal') $option['optionValue'] = $outputObj->getOutput($user, $option, $option['optionValue']);
			else if ($this->outputType == 'short') $option['optionValue'] = $outputObj->getShortOutput($user, $option, $option['optionValue']);
			else $option['optionValue'] = $outputObj->getMediumOutput($user, $option, $option['optionValue']);
		}
		else {
			$option['optionValue'] = StringUtil::encodeHTML($option['optionValue']);
		}
		
		if (empty($option['optionValue']) && empty($option['outputData'])) return false;
		return $option;
	}
		
	/**
	 * Returns an object of the requested option output type.
	 * 
	 * @param	string			$type
	 * @return	UserOptionOutput
	 */
	public function getOutputObject($className) {
		if (!isset($this->outputObjects[$className])) {
			// include class file
			$classPath = WCF_DIR.'lib/data/user/option/'.$className.'.class.php';
			if (!file_exists($classPath)) {
				throw new SystemException("unable to find class file '".$classPath."'", 11000);
			}
			require_once($classPath);
			
			// create instance
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'", 11001);
			}
			$this->outputObjects[$className] = new $className();
		}
		
		return $this->outputObjects[$className];
	}
}
?>