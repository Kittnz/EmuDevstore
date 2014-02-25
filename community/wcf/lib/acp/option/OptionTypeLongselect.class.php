<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionTypeSelect.class.php');

/**
 * OptionTypeLongselect is an implementation of OptionType for huge 'select' tags.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeLongselect extends OptionTypeSelect {
	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(&$optionData) {
		$optionData['divClass'] = 'longSelect';
		return parent::getFormElement($optionData);
	}
}
?>