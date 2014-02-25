<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionType.class.php');

/**
 * OptionTypeUseroptions is an implementation of OptionType for user option checkboxes.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeUseroptions implements OptionType {
	// cache
	public static $cachedCategories = array();
	public static $cachedOptions = array();
	public static $cachedCategoryStructure = array();
	public static $cachedOptionToCategories = array();
	public static $cacheLoaded = false;
	public $templateName = 'optionTypeUseroptions';

	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(&$optionData) {
		if (!isset($optionData['optionValue'])) {
			if (isset($optionData['defaultValue'])) $optionData['optionValue'] = $optionData['defaultValue'];
			else $optionData['optionValue'] = '';
		}
		$optionData['divClass'] = 'formCheckboxes'; 
		$optionData['isOptionGroup'] = true; 
		
		// get options
		$options = $this->getUserOptions($optionData);
		
		WCF::getTPL()->assign(array(
			'optionData' => $optionData,
			'options' => $options,
			'selectedOptions' => explode(',', $optionData['optionValue'])
		));
		return WCF::getTPL()->fetch($this->templateName);
	}
	
	/**
	 * @see OptionType::validate()
	 */
	public function validate($optionData, $newValue) {
		$options = $this->getUserOptions($optionData);
		if (!is_array($newValue)) $newValue = array();
		foreach ($newValue as $option) {
			if (!isset($options[$option])) return false;
		}
		
		return true;
	}
	
	/**
	 * @see OptionType::getData()
	 */
	public function getData($optionData, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		return implode(',', $newValue);
	}
	
	protected static function readCache() {
		if (!self::$cacheLoaded) {
			WCF::getCache()->addResource('user-option-'.PACKAGE_ID, WCF_DIR.'cache/cache.user-option-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderOption.class.php');
			self::$cachedCategories = WCF::getCache()->get('user-option-'.PACKAGE_ID, 'categories');
			self::$cachedOptions = WCF::getCache()->get('user-option-'.PACKAGE_ID, 'options');
			self::$cachedCategoryStructure = WCF::getCache()->get('user-option-'.PACKAGE_ID, 'categoryStructure');
			self::$cachedOptionToCategories = WCF::getCache()->get('user-option-'.PACKAGE_ID, 'optionToCategories');
			
			self::$cacheLoaded = true;
		}
	}
	
	protected function getUserOptions(&$optionData) {
		$this->readCache();
		return $this->getCategoryOptions('profile');
	}
	
	protected function getCategoryOptions($category) {
		$options = array();
		
		if (isset(self::$cachedOptionToCategories[$category])) {
			foreach (self::$cachedOptionToCategories[$category] as $optionName) {
				if (self::$cachedOptions[$optionName]['visible'] == 0 && self::$cachedOptions[$optionName]['disabled'] == 0) {
					$options[$optionName] = 'wcf.user.option.'.$optionName;
				}
			}
		}
		
		if (isset(self::$cachedCategoryStructure[$category])) {
			foreach (self::$cachedCategoryStructure[$category] as $subCategory) {
				$options = array_merge($options, $this->getCategoryOptions($subCategory));
			}
		}
		
		return $options;
	}
}
?>