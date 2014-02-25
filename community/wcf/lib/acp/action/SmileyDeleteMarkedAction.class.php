<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/SmileyEditor.class.php');

/**
 * Deletes all marked smileys.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class SmileyDeleteMarkedAction extends AbstractAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// check permission
		WCF::getUser()->checkPermission('admin.smiley.canDeleteSmiley');
		
		// delete makred smileys
		$markedSmileys = WCF::getSession()->getVar('markedSmileys');
		if ($markedSmileys !== null) {
			foreach ($markedSmileys as $smileyID) {
				$smiley = new SmileyEditor($smileyID);	
				if ($smiley->smileyID) $smiley->delete();
			}
		}
		
		// reset cache
		SmileyEditor::resetCache();
		// unmark smileys
		WCF::getSession()->unregister('markedSmileys');
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=SmileyList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>