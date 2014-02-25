<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/pm/PMEditor.class.php');
require_once(WCF_DIR.'lib/data/message/pm/rule/PMRule.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');

/**
 * Any rule action should implement this interface.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm.rule.action
 * @category 	Community Framework (commercial)
 */
interface PMRuleAction {
	/**
	 * Executes this action.
	 *
	 * @param	PMEditor	$pm
	 * @param	PMRule		$rule
	 * @param 	UserProfile	$recipient
	 * @return	boolean		false, to stop the execution of other actions	
	 */
	public function execute(PMEditor $pm, PMRule $rule, UserProfile $recipient);
	
	/**
	 * Returns the destination type.
	 * 
	 * @var	string
	 */
	public function getDestinationType();
	
	/**
	 * Returns a list of available destinations.
	 * 
	 * @var	array
	 */
	public function getAvailableDestinations();
}
?>