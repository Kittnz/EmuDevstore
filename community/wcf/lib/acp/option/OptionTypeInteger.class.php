<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionTypeText.class.php');

/**
 * OptionTypeText is an implementation of OptionType for integer fields.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeInteger extends OptionTypeText {
	/**
	 * @see OptionType::getData()
	 */
	public function getData($optionData, $newValue) {
		return intval($newValue);
	}
}
?>