<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionTypeInteger.class.php');
require_once(WCF_DIR.'lib/acp/group/GroupOptionType.class.php');

/**
 * GroupOptionTypeInverseInteger is an implementation of GroupOptionType for integer values.
 * The merge of option values returns the lowest value.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.group
 * @category 	Community Framework
 */
class GroupOptionTypeInverseinteger extends OptionTypeInteger implements GroupOptionType {
	/**
	 * @see GroupOptionType::merge()
	 */
	public function merge($values) {
		return min($values);
	}
}
?>