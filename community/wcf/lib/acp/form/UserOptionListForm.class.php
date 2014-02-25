<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/DynamicOptionListForm.class.php');
require_once(WCF_DIR.'lib/system/language/Language.class.php');

/**
 * This class provides default implementations for a list of dynamic user options.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
abstract class UserOptionListForm extends DynamicOptionListForm {
	public $cacheName = 'user-option-';
	
	/**
	 * Returns a list of all available user groups.
	 * 
	 * @return	array
	 */
	protected function getAvailableGroups() {
		require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
		return Group::getAccessibleGroups(array(), array(Group::GUESTS, Group::EVERYONE, Group::USERS));
	}
	
	/**
	 * Returns a list of all available languages.
	 * 
	 * @return	array
	 */
	protected function getAvailableLanguages() {
		$availableLanguages = array();
		foreach (Language::getAvailableLanguages(PACKAGE_ID) as $language) {
			$availableLanguages[$language['languageID']] = WCF::getLanguage()->get('wcf.global.language.'.$language['languageCode']);	
		}
		
		// sort languages
		StringUtil::sort($availableLanguages);
		
		return $availableLanguages;
	}
	
	/**
	 * Returns a list of all available content languages.
	 * 
	 * @return	array
	 */
	public static function getAvailableContentLanguages() {
		$availableLanguages = array();
		foreach (Language::getAvailableContentLanguages(PACKAGE_ID) as $language) {
			$availableLanguages[$language['languageID']] = WCF::getLanguage()->get('wcf.global.language.'.$language['languageCode']);	
		}
		
		// sort languages
		StringUtil::sort($availableLanguages);
		
		return $availableLanguages;
	}
	
	/**
	 * Returns the default-form language id    
	 * 
	 * @return 	integer		$languageID
	 */
	protected function getDefaultFormLanguageID() {
		return Language::getDefaultLanguageID();
	}
	
	/**
	 * Validates an option.
	 * 
	 * @param	string		$key		option name
	 * @param	array		$option		option data
	 */
	protected function validateOption($key, $option) {
		parent::validateOption($key, $option);

		if ($option['required'] && empty($this->activeOptions[$key]['optionValue'])) {
			throw new UserInputException($option['optionName']);
		}
	}
}
?>