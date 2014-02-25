<?php
require_once(WCF_DIR.'lib/action/AbstractPMRuleAction.class.php');

/**
 * Enables a rule. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class PMRuleEnableAction extends AbstractPMRuleAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// enable rule
		$this->rule->enable();
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?page=PMRuleList'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>