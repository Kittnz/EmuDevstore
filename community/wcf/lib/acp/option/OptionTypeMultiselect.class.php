<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionTypeSelect.class.php');

/**
 * OptionTypeSelect is an implementation of OptionType for multiple 'select' tags.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeMultiselect extends OptionTypeSelect {
	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(&$optionData) {
		if (!isset($optionData['optionValue'])) {
			if (isset($optionData['defaultValue'])) $optionData['optionValue'] = explode("\n", $optionData['defaultValue']);
			else $optionData['optionValue'] = array();
		}
		else if (!is_array($optionData['optionValue'])) {
			$optionData['optionValue'] = explode("\n", $optionData['optionValue']);
		}
		
		// get options
		$options = OptionUtil::parseSelectOptions($optionData['selectOptions']);
		
		WCF::getTPL()->assign(array(
			'optionData' => $optionData,
			'options' => $options
		));
		return WCF::getTPL()->fetch('optionTypeMultiselect');
	}
	
	/**
	 * @see OptionType::validate()
	 */
	public function validate($optionData, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		$options = OptionUtil::parseSelectOptions($optionData['selectOptions']);
		foreach ($newValue as $value) {
			if (!isset($options[$value])) throw new UserInputException($optionData['optionName'], 'validationFailed');
		}
	}
	
	/**
	 * @see OptionType::getData()
	 */
	public function getData($optionData, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		return implode("\n", $newValue);
	}
	
	/**
	 * @see SearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(&$optionData) {
		return $this->getFormElement($optionData);
	}
	
	/**
	 * @see SearchableUserOption::getCondition()
	 */
	public function getCondition($optionData, $value, $matchesExactly = true) {
		if (!is_array($value) || !count($value)) return false;
		$value = ArrayUtil::trim($value);
		if (!count($value)) return false;
		
		return "option_value.userOption".$optionData['optionID']." = '".implode("\n", array_map('escapeString', $value))."'";
	}
}
?>