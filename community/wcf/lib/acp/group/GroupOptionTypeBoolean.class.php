<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionTypeBoolean.class.php');
require_once(WCF_DIR.'lib/acp/group/GroupOptionType.class.php');

/**
 * GroupOptionTypeBoolean is an implementation of GroupOptionType for boolean values.
 * The merge of option values returns true, if at least one value is true. Otherwise false.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.group
 * @category 	Community Framework
 */
class GroupOptionTypeBoolean extends OptionTypeBoolean implements GroupOptionType {
	/**
	 * @see GroupOptionType::merge()
	 */
	public function merge($values) {
		foreach ($values as $value) {
			if ($value) return true;
		}

		return false;
	}
}
?>