<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/AbstractBBCodeAction.class.php');

/**
 * Disables a bbcode.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.bbcode
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class BBCodeDisableAction extends AbstractBBCodeAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
				
		// check permission
		WCF::getUser()->checkPermission('admin.bbcode.canEditBBCode');
		
		// disable bbcode
		$this->bbcode->disable();
		$this->executed();
	}
}
?>