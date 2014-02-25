<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionTypeText.class.php');
require_once(WCF_DIR.'lib/acp/group/GroupOptionType.class.php');

/**
 * GroupOptionTypeText is an implementation of GroupOptionType for text values.
 * The merge of option values returns merge of all text values.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.group
 * @category 	Community Framework
 */
class GroupOptionTypeText extends OptionTypeText implements GroupOptionType {
	/**
	 * @see GroupOptionType::merge()
	 */
	public function merge($values) {
		$result = '';
		
		foreach ($values as $value) {
			if (!empty($result)) $result .= "\n";
			$result .= $value;
		}

		return $result;
	}
}
?>