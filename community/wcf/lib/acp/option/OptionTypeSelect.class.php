<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionTypeRadiobuttons.class.php');

/**
 * OptionTypeSelect is an implementation of OptionType for 'select' tags.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeSelect extends OptionTypeRadiobuttons {
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
			'options' => $options
		));
		return WCF::getTPL()->fetch('optionTypeSelect');
	}
	
	/**
	 * @see SearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(&$optionData) {
		$optionData['selectOptions'] = ":\n".$optionData['selectOptions'];
		return $this->getFormElement($optionData);
	}
}
?>