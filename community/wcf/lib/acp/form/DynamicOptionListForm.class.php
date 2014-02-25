<?php
// wcf imports
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');

/**
 * This class provides default implementations for a list of dynamic options.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
abstract class DynamicOptionListForm extends AbstractForm {
	// system
	public $errorField = array();
	public $errorType = array();

	// cache data
	public $cacheName = 'option-';
	public $cacheClass = 'CacheBuilderOption';

	// cache content
	public $cachedCategories = array();
	public $cachedOptions = array();
	public $cachedCategoryStructure = array();
	public $cachedOptionToCategories = array();
	
	// form parameters
	public $values = array();
	
	/**
	 * Name of the active option category.
	 * 
	 * @var string
	 */
	public $activeCategory = '';
	
	/**
	 * Options of the active category.
	 * 
	 * @var array
	 */
	public $activeOptions = array();
	
	/**
	 * Type object cache.
	 * 
	 * @var array
	 */
	public $typeObjects = array();
		
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['values']) && is_array($_POST['values'])) $this->values = $_POST['values'];
	}

	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		foreach ($this->activeOptions as $key => $option) {
			try {
				$this->validateOption($key, $option);
			}
			catch (UserInputException $e) {
				$this->errorType[$e->getField()] = $e->getType();
			}
		}
		
		if (count($this->errorType) > 0) {
			throw new UserInputException('options', $this->errorType);
		}
	}
	
	/**
	 * Validates an option.
	 * 
	 * @param	string		$key		name of option
	 * @param	array		$option		option data
	 */
	protected function validateOption($key, $option) {
		// get type object
		$typeObj = $this->getTypeObject($option['optionType']);
		
		// get new value
		$newValue = isset($this->values[$option['optionName']]) ? $this->values[$option['optionName']] : null;
				
		// get save value
		$this->activeOptions[$key]['optionValue'] = $typeObj->getData($option, $newValue);
				
		// validate with pattern
		if (!empty($option['validationPattern'])) {
			if (!preg_match('~'.$option['validationPattern'].'~', $this->activeOptions[$key]['optionValue'])) {
				throw new UserInputException($option['optionName'], 'validationFailed');
			}
		}
		
		// validate by type object
		$typeObj->validate($option, $newValue);
	}
	
	/**
	 * Gets all options and option categories from cache.
	 */
	protected function readCache() {
		// get cache contents
		$cacheName = $this->cacheName.PACKAGE_ID;
		WCF::getCache()->addResource($cacheName, WCF_DIR.'cache/cache.'.$cacheName.'.php', WCF_DIR.'lib/system/cache/'.$this->cacheClass.'.class.php');
		$this->cachedCategories = WCF::getCache()->get($cacheName, 'categories');
		$this->cachedOptions = WCF::getCache()->get($cacheName, 'options');
		$this->cachedCategoryStructure = WCF::getCache()->get($cacheName, 'categoryStructure');
		$this->cachedOptionToCategories = WCF::getCache()->get($cacheName, 'optionToCategories');
		
		// get active options
		$this->loadActiveOptions($this->activeCategory);
	}
	
	/**
	 * Creates a list of all active options.
	 * 
	 * @param	string		$parentCategoryName
	 */
	protected function loadActiveOptions($parentCategoryName) {
		if (!isset($this->cachedCategories[$parentCategoryName]) || $this->checkCategory($this->cachedCategories[$parentCategoryName])) {
			if (isset($this->cachedOptionToCategories[$parentCategoryName])) {
				foreach ($this->cachedOptionToCategories[$parentCategoryName] as $optionName) {
					if (!$this->checkOption($optionName)) continue;
					$this->activeOptions[$optionName] =& $this->cachedOptions[$optionName];
				}
			}
			if (isset($this->cachedCategoryStructure[$parentCategoryName])) {
				foreach ($this->cachedCategoryStructure[$parentCategoryName] as $categoryName) {
					$this->loadActiveOptions($categoryName);
				}
			}
		}
	}
	
	/**
	 * Returns an object of the requested option type.
	 * 
	 * @param	string			$type
	 * @return	OptionType
	 */
	protected function getTypeObject($type) {
		if (!isset($this->typeObjects[$type])) {
			$className = 'OptionType'.ucfirst(strtolower($type));
			$classPath = WCF_DIR.'lib/acp/option/'.$className.'.class.php';
			
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
	
	/**
	 * Returns the tree of options.
	 * 
	 * @param	string		$parentCategoryName
	 * @return	array
	 */
	protected function getOptionTree($parentCategoryName = '') {
		$options = array();
		
		if (isset($this->cachedCategoryStructure[$parentCategoryName])) {
			// get super categories
			foreach ($this->cachedCategoryStructure[$parentCategoryName] as $superCategoryName) {
				$superCategory = $this->cachedCategories[$superCategoryName];
				if ($this->checkCategory($superCategory)) {
					$superCategory['options'] = $this->getCategoryOptions($superCategoryName);
					
					if (count($superCategory['options']) > 0) {
						$options[] = $superCategory;
					}
				}
			}
		}
	
		return $options;
	}
	
	/**
	 * Checks the required permissions and options of a category.
	 * 
	 * @param	array		$category
	 * @return	boolean
	 */
	protected function checkCategory($category) {
		if (!empty($category['permissions'])) {
			$hasPermission = false;
			$permissions = explode(',', $category['permissions']);
			foreach ($permissions as $permission) {
				if (WCF::getUser()->getPermission($permission)) {
					$hasPermission = true;
					break;
				}
			}
			
			if (!$hasPermission) return false;
			
		}
		if (!empty($category['options'])) {
			$hasEnabledOption = false;
			$options = explode(',', strtoupper($category['options']));
			foreach ($options as $option) {
				if (defined($option) && constant($option)) {
					$hasEnabledOption = true;
					break;
				}
			}
			
			if (!$hasEnabledOption) return false;
		}
		
		return true;
	}
	
	/**
	 * Returns a list with the options of a specific option category.
	 * 
	 * @param	string		$categoryName
	 * @param	boolean		$inherit
	 * @return	array
	 */
	protected function getCategoryOptions($categoryName = '', $inherit = true) {
		$children = array();
		
		// get sub categories
		if ($inherit && isset($this->cachedCategoryStructure[$categoryName])) {
			foreach ($this->cachedCategoryStructure[$categoryName] as $subCategoryName) {
				$children = array_merge($children, $this->getCategoryOptions($subCategoryName));
			}
		}
		
		// get options
		if (isset($this->cachedOptionToCategories[$categoryName])) {
			$i = 0;
			$last = count($this->cachedOptionToCategories[$categoryName]) - 1;
			foreach ($this->cachedOptionToCategories[$categoryName] as $optionName) {
				if (!$this->checkOption($optionName) || !isset($this->activeOptions[$optionName])) continue;
				
				// get option data
				$option = $this->activeOptions[$optionName];
				
				// set default values
				$option['beforeLabel'] = false;
				
				// get form element htlm
				$option['html'] = $this->getFormElement($option['optionType'], $option);
				
				// add option to list
				$children[] = $option;
				
				$i++;
			}
		}
		
		return $children;
	}
	
	/**
	 * @see OptionType::getFormElement()
	 */
	protected function getFormElement($type, &$optionData) {
		return $this->getTypeObject($type)->getFormElement($optionData);
	}
	
	/**
	 * Filters displayed options by specific parameters.
	 * 
	 * @param	string		$optionName
	 * @return	boolean
	 */
	protected function checkOption($optionName) {
		$optionData = $this->cachedOptions[$optionName];
		
		if (!empty($optionData['permissions'])) {
			$hasPermission = false;
			$permissions = explode(',', $optionData['permissions']);
			foreach ($permissions as $permission) {
				if (WCF::getUser()->getPermission($permission)) {
					$hasPermission = true;
					break;
				}
			}
			
			if (!$hasPermission) return false;
			
		}
		if (!empty($optionData['options'])) {
			$hasEnabledOption = false;
			$options = explode(',', strtoupper($optionData['options']));
			foreach ($options as $option) {
				if (defined($option) && constant($option)) {
					$hasEnabledOption = true;
					break;
				}
			}
			
			if (!$hasEnabledOption) return false;
		}
		
		return true;
	}
}
?>