<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionType.class.php');
require_once(WCF_DIR.'lib/acp/option/SearchableUserOption.class.php');

/**
 * OptionTypeRadiobuttons is an implementation of OptionType for 'input type="radio"' tags.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeRadiobuttons implements OptionType, SearchableUserOption {
	public $templateName = 'optionTypeRadiobuttons';

	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(&$optionData) {
		if (!isset($optionData['optionValue'])) {
			if (isset($optionData['defaultValue'])) $optionData['optionValue'] = $optionData['defaultValue'];
			else $optionData['optionValue'] = false;
		}
		$optionData['divClass'] = 'formRadio'; 
		$optionData['isOptionGroup'] = true; 
		
		// get options
		$options = OptionUtil::parseSelectOptions($optionData['selectOptions']);
		if (isset($optionData['enableOptions'])) $enableOptions = OptionUtil::parseMultipleEnableOptions($optionData['enableOptions']);
		else $enableOptions = array();
		
		// build html
		$html = '';
		foreach ($options as $key => $value) {
			$enableJS = $disableJS = '';
			$options[$key] = array('value' => $value, 'enableOptions' => '');
			if (isset($enableOptions[$key])) {
				$enables = explode(',', $enableOptions[$key]);
				foreach ($enables as $enable) {			
					if ($enable[0] == '!') {
						if (!empty($disableJS)) $disableJS .= ',';
						$disableJS .= "'".substr($enable, 1)."'";
					}
					else {
						if (!empty($enableJS)) $enableJS .= ',';
						$enableJS .= "'".$enable."'";
					}
				}
			
				$options[$key]['enableOptions'] = 'enableOptions('.$enableJS.') + disableOptions('.$disableJS.');';
				if ($optionData['optionValue'] == $key) $optionData['enableOptionsJS'] = $options[$key]['enableOptions'];
			}
		}
		
		WCF::getTPL()->assign(array(
			'optionData' => $optionData,
			'options' => $options
		));
		return WCF::getTPL()->fetch($this->templateName);
	}
	
	/**
	 * @see OptionType::validate()
	 */
	public function validate($optionData, $newValue) {
		if (!empty($newValue)) {
			$options = OptionUtil::parseSelectOptions($optionData['selectOptions']);
			if (!isset($options[$newValue])) {
				throw new UserInputException($optionData['optionName'], 'validationFailed');
			}
		}
	}
	
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
		return $this->getFormElement($optionData);
	}
	
	/**
	 * @see SearchableUserOption::getCondition()
	 */
	public function getCondition($optionData, $value, $matchesExactly = true) {
		$value = StringUtil::trim($value);
		if (!$value) return false;
		
		return "option_value.userOption".$optionData['optionID']." = '".escapeString($value)."'";
	}
}
?>