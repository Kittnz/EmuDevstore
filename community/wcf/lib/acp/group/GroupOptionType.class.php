<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionType.class.php');

/**
 * Any group permission type should implement this interface.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.group
 * @category 	Community Framework
 */
interface GroupOptionType extends OptionType {
	/**
	 * Merges the different values of an option to one single value.
	 * 
	 * @param	array		$values
	 * @return	mixed		$value
	 */
	public function merge($values);
}
?>