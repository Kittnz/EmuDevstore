<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/AbstractSmileyCategoryAction.class.php');

/**
 * Disables a smiley category.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class SmileyCategoryDisableAction extends AbstractSmileyCategoryAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.smiley.canEditSmileyCategory');
		
		// delete category
		$this->smileyCategory->disable();
		SmileyEditor::resetCache();
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=SmileyCategoryList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>