<?php
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/message/pm/rule/PMRuleEditor.class.php');

/**
 * Provides default implementations for rule actions. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class AbstractPMRuleAction extends AbstractSecureAction {
	/**
	 * rule id
	 *
	 * @var integer
	 */
	public $ruleID = 0;
	
	/**
	 * rule editor object
	 *
	 * @var PMRuleEditor
	 */
	public $rule = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['ruleID'])) $this->ruleID = intval($_REQUEST['ruleID']);
		$this->rule = new PMRuleEditor($this->ruleID);
		if (!$this->rule->ruleID || !WCF::getUser()->userID || $this->rule->userID != WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}
	}
}
?>