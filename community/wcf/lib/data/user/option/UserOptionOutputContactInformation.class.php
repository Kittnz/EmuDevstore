<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * Any user option output class should implement this interface.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.option
 * @category 	Community Framework
 */
interface UserOptionOutputContactInformation {
	/**
	 * Returns the output data of this user option.
	 *
	 * @param	User		$user
	 * @param	array		$optionData
	 * @param	string		$value
	 * @return	array
	 */
	public function getOutputData(User $user, $optionData, $value);
} 
?>