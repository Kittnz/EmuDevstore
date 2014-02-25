<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/AbstractSmileyCategoryAction.class.php');

/**
 * Deletes a smiley category.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class SmileyCategoryDeleteAction extends AbstractSmileyCategoryAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.smiley.canDeleteSmileyCategory');
		
		// delete category
		$this->smileyCategory->delete();
		SmileyEditor::resetCache();
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=SmileyCategoryList&deletedSmileyCategoryID='.$this->smileyCategoryID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>