<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/group/GroupOptionTypeInverseinteger.class.php');

/**
 * GroupOptionTypeInfiniteinverseinteger is an implementation of GroupOptionType for integer values.
 * The merge of option values returns -1 if all values are -1 otherwise the lowest value.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.group
 * @category 	Community Framework
 */
class GroupOptionTypeInfiniteinverseinteger extends GroupOptionTypeInverseinteger {
	/**
	 * @see GroupOptionType::merge()
	 */
	public function merge($values) {
		foreach ($values as $key => $value) {
			if ($value == -1) unset($values[$key]);
		}
		
		if (count($values) == 0) return -1;
		return min($values);
	}
}
?>