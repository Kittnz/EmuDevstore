<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionTypeText.class.php');

/**
 * OptionTypeTextarea is an implementation of OptionType for 'textarea' tags.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeTextarea extends OptionTypeText {
	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(&$optionData) {
		if (!isset($optionData['optionValue'])) {
			if (isset($optionData['defaultValue'])) $optionData['optionValue'] = $optionData['defaultValue'];
			else $optionData['optionValue'] = '';
		}
		
		WCF::getTPL()->assign('optionData', $optionData);
		return WCF::getTPL()->fetch('optionTypeTextarea');
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
}
?>