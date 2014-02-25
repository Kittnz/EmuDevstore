<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionTypeText.class.php');

/**
 * OptionTypeFloat is an implementation of OptionType for float fields.
 *
 * @author	Tobias Friebel
 * @copyright	2001-2009 Tobias Friebel
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeFloat extends OptionTypeText {
	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(&$optionData) {
		if (isset($optionData['defaultValue'])) $optionData['defaultValue'] = str_replace('.', WCF::getLanguage()->get('wcf.global.decimalPoint'), $optionData['defaultValue']);
		if (isset($optionData['optionValue'])) $optionData['optionValue'] = str_replace('.', WCF::getLanguage()->get('wcf.global.decimalPoint'), $optionData['optionValue']);
		
		return parent::getFormElement($optionData);
	}
	
	/**
	 * @see OptionType::getData()
	 */
	public function getData($optionData, $newValue) {
		$newValue = str_replace(' ', '', $newValue);
		$newValue = str_replace(WCF::getLanguage()->get('wcf.global.thousandsSeparator'), '', $newValue);
		$newValue = str_replace(WCF::getLanguage()->get('wcf.global.decimalPoint'), '.', $newValue);
		return floatval($newValue);
	}
}
?>