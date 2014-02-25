<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionType.class.php');
require_once(WCF_DIR.'lib/acp/option/SearchableUserOption.class.php');

/**
 * OptionTypeBoolean is an implementation of OptionType for boolean values.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeBoolean implements OptionType, SearchableUserOption {
	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(&$optionData) {
		if (!isset($optionData['optionValue'])) {
			if (isset($optionData['defaultValue'])) $optionData['optionValue'] = $optionData['defaultValue'];
			else $optionData['optionValue'] = false;
		}
		$optionData['beforeLabel'] = true;
		$optionData['divClass'] = 'formCheckBox'; 
		
		if (!empty($optionData['enableOptions'])) {
			$options = explode(',', $optionData['enableOptions']);
			$enableOptions = $disableOptions = '';
			foreach ($options as $option) {			
				if ($option[0] == '!') {
					if (!empty($disableOptions)) $disableOptions .= ',';
					$disableOptions .= "'".substr($option, 1)."'";
				}
				else {
					if (!empty($enableOptions)) $enableOptions .= ',';
					$enableOptions .= "'".$option."'";
				}
			}
			
			$optionData['enableOptionsJS'] = 'enableOptions('.$enableOptions.') + disableOptions('.$disableOptions.'); else enableOptions('.$disableOptions.') + disableOptions('.$enableOptions.');';
			$optionData['enableOptions'] = 'if (this.checked) '.$optionData['enableOptionsJS'];
			$optionData['enableOptionsJS'] = 'if ('.($optionData['optionValue'] ? 'true' : 'false').') '.$optionData['enableOptionsJS'];
		}
		
		WCF::getTPL()->assign('optionData', $optionData);
		return WCF::getTPL()->fetch('optionTypeBoolean');
	}
	
	/**
	 * @see OptionType::validate()
	 */
	public function validate($optionData, $newValue) {}
	
	/**
	 * @see OptionType::getData()
	 */
	public function getData($optionData, $newValue) {
		if ($newValue !== null) return 1;
		return 0;
	}
	
	/**
	 * @see SearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(&$optionData) {
		if (!isset($optionData['optionValue'])) $optionData['optionValue'] = 0;
		return $this->getFormElement($optionData);
	}
	
	/**
	 * @see SearchableUserOption::getCondition()
	 */
	public function getCondition($optionData, $value, $matchesExactly = true) {
		$value = intval($value);
		if (!$value) return false;
		
		return "option_value.userOption".$optionData['optionID']." = 1";
	}
}
?>