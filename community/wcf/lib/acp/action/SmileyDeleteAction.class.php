<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Deletes a smiley.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class SmileyDeleteAction extends AbstractAction {
	public $smileyID = 0;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['smileyID'])) $this->smileyID = intval($_REQUEST['smileyID']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// check permission
		WCF::getUser()->checkPermission('admin.smiley.canDeleteSmiley');
		
		// delete smiley
		require_once(WCF_DIR.'lib/data/message/smiley/SmileyEditor.class.php');
		$smiley = new SmileyEditor($this->smileyID);	
		if (!$smiley->smileyID) {
			throw new IllegalLinkException();
		}
		$smiley->delete();
		
		// reset cache
		SmileyEditor::resetCache();
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=SmileyList&deletedSmileyID='.$this->smileyID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>