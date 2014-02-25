<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/group/GroupOptionTypeInteger.class.php');

/**
 * GroupOptionTypeInfiniteinteger is an implementation of GroupOptionType for integer values with the infinite option.
 * The merge of option values returns true, if at least one value is -1. Otherwise it returns the highest value.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.group
 * @category 	Community Framework
 */
class GroupOptionTypeInfiniteinteger extends GroupOptionTypeInteger {
	/**
	 * @see GroupOptionType::merge()
	 */
	public function merge($values) {
		if (in_array(-1, $values)) return -1;
		return parent::merge($values);
	}
}
?>