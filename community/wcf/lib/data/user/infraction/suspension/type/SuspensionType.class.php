<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/User.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/UserSuspension.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/Suspension.class.php');

/**
 * A suspension type should implement this interface.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.suspension.type
 * @category 	Community Framework (commercial)
 */
interface SuspensionType {
	// execution methods
	/**
	 * Applies this suspension.
	 * 
	 * @param 	User		$user
	 * @param 	UserSuspension 	$userSuspension
	 * @param 	Suspension 	$suspension
	 */
	public function apply(User $user, UserSuspension $userSuspension, Suspension $suspension);
	
	/**
	 * Revokes this suspension.
	 * 
	 * @param 	User		$user
	 * @param 	UserSuspension 	$userSuspension
	 * @param 	Suspension 	$suspension
	 */
	public function revoke(User $user, UserSuspension $userSuspension, Suspension $suspension);
	
	// form methods
	/**
	 * Reads the given form parameters.
	 */
	public function readFormParameters();
	
	/**
	 * Validates form inputs.
	 */
	public function validate();
	
	/**
	 * Returns the data of this suspension type.
	 * 
	 * @return	array
	 */
	public function getData();
	
	/**
	 * Sets the default data of this suspension type. 
	 *
	 * @param	array		$data
	 */
	public function setData($data);
	
	/**
	 * Assigns variables to the template engine.
	 */
	public function assignVariables();
	
	/**
	 * Returns the name of the template.
	 * 
	 * @return	string
	 */
	public function getTemplateName();
}
?>