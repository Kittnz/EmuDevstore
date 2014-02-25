<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionType.class.php');
require_once(WCF_DIR.'lib/acp/option/SearchableUserOption.class.php');

/**
 * OptionTypeText is an implementation of OptionType for 'input type="text"' tags.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeText implements OptionType, SearchableUserOption {
	protected $inputType = 'text';
	
	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(&$optionData) {
		if (!isset($optionData['optionValue'])) {
			if (isset($optionData['defaultValue'])) $optionData['optionValue'] = $optionData['defaultValue'];
			else $optionData['optionValue'] = '';
		}
		
		WCF::getTPL()->assign(array(
			'optionData' => $optionData,
			'inputType' => $this->inputType	
		));
		return WCF::getTPL()->fetch('optionTypeText');
	}
	
	/**
	 * @see OptionType::validate()
	 */
	public function validate($optionData, $newValue) {}
	
	/**
	 * @see OptionType::getData()
	 */
	public function getData($optionData, $newValue) {
		return $newValue;
	}
	
	/**
	 * @see SearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(&$optionData) {
		if (!isset($optionData['optionValue'])) $optionData['optionValue'] = '';
		$optionData['supportsExactMatch'] = true;
		return $this->getFormElement($optionData);
	}
	
	/**
	 * @see SearchableUserOption::getCondition()
	 */
	public function getCondition($optionData, $value, $matchesExactly = true) {
		$value = StringUtil::trim($value);
		if (empty($value)) return false;
		
		if ($matchesExactly) {
			return "option_value.userOption".$optionData['optionID']." = '".escapeString($value)."'";
		}
		else {
			return "option_value.userOption".$optionData['optionID']." LIKE '%".addcslashes(escapeString($value), '_%')."%'";
		}
	}
}
?>