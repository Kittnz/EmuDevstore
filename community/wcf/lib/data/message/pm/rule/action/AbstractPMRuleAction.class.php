<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/pm/rule/action/PMRuleAction.class.php');

/**
 * Provides default implementations for rule actions.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm.rule.action
 * @category 	Community Framework (commercial)
 */
abstract class AbstractPMRuleAction implements PMRuleAction {
	/**
	 * @see PMRuleAction::getDestinationType()
	 */
	public function getDestinationType() {
		return '';
	}
	
	/**
	 * @see PMRuleAction::getAvailableDestinations()
	 */
	public function getAvailableDestinations() {
		return array();
	}
}
?>