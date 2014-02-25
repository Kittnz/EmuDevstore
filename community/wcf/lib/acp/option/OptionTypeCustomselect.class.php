<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionTypeSelect.class.php');

/**
 * OptionTypeSelect is an implementation of OptionType for 'select' tags with a text field for custom inputs.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeCustomselect extends OptionTypeSelect {
	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(&$optionData) {
		if (!isset($optionData['optionValue'])) {
			if (isset($optionData['defaultValue'])) $optionData['optionValue'] = $optionData['defaultValue'];
			else $optionData['optionValue'] = false;
		}
		 
		// get options
		$options = OptionUtil::parseSelectOptions($optionData['selectOptions']);
		
		WCF::getTPL()->assign(array(
			'optionData' => $optionData,
			'options' => $options,
			'customValue' => (!isset($options[$optionData['optionValue']]) ? $optionData['optionValue'] : '')
		));
		return WCF::getTPL()->fetch('optionTypeCustomselect');
	}
	
	/**
	 * @see OptionType::validate()
	 */
	public function validate($optionData, $newValue) {}
	
	/**
	 * @see OptionType::getData()
	 */
	public function getData($optionData, $newValue) {
		if (empty($newValue) && isset($_POST['values'][$optionData['optionName'].'_custom'])) {
			return $_POST['values'][$optionData['optionName'].'_custom'];
		}
		return $newValue;
	}
}
?>