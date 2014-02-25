<?php
require_once(WCF_DIR.'lib/action/AbstractPMRuleAction.class.php');

/**
 * Disables a rule. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class PMRuleDisableAction extends AbstractPMRuleAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// disable rule
		$this->rule->disable();
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?page=PMRuleList'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>