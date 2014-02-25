<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/AbstractBBCodeAction.class.php');

/**
 * Deletes a bbcode.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.bbcode
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class BBCodeDeleteAction extends AbstractBBCodeAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.bbcode.canDeleteBBCode');
		
		// delete bbcode
		$this->bbcode->delete();
		$this->executed();
	}
	
	/**
	 * @see AbstractAction::executed()
	 */
	protected function executed() {
		AbstractAction::executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=BBCodeList&deletedBBCodeID='.$this->bbcodeID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>