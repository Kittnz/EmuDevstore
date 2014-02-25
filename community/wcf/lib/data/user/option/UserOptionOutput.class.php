<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * Any user option output class should implement this interface.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option
 * @category 	Community Framework
 */
interface UserOptionOutput {
	/**
	 * Returns a short version of the html code for the output of the given user option.
	 * 
	 * @param	User		$user
	 * @param	array		$optionData
	 * @param	string		$value
	 * @return	string
	 */
	public function getShortOutput(User $user, $optionData, $value);
	
	/**
	 * Returns a medium version of the html code for the output of the given user option.
	 * 
	 * @param	User		$user
	 * @param	array		$optionData
	 * @param	string		$value
	 * @return	string
	 */
	public function getMediumOutput(User $user, $optionData, $value);
	
	/**
	 * Returns the html code for the output of the given user option.
	 * 
	 * @param	User		$user
	 * @param	array		$optionData
	 * @param	string		$value
	 * @return	string
	 */
	public function getOutput(User $user, $optionData, $value);
}
?>