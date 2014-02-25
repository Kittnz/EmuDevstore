<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/SmileyEditor.class.php');

/**
 * Moves all marked smileys.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class SmileyMoveMarkedAction extends AbstractAction {
	public $smileyCategoryID = 0;
	public $smileyCategory = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['smileyCategoryID'])) $this->smileyCategoryID = intval($_REQUEST['smileyCategoryID']);
		if ($this->smileyCategoryID != 0) {
			require_once(WCF_DIR.'lib/data/message/smiley/category/SmileyCategoryEditor.class.php');
			$this->smileyCategory = new SmileyCategoryEditor($this->smileyCategoryID);
			if (!$this->smileyCategory->smileyCategoryID) {
				throw new IllegalLinkException();
			}
		}
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// check permission
		WCF::getUser()->checkPermission('admin.smiley.canEditSmiley');
		
		// delete makred smileys
		$markedSmileys = WCF::getSession()->getVar('markedSmileys');
		if ($markedSmileys !== null) {
			$sql = "UPDATE	wcf".WCF_N."_smiley
				SET	smileyCategoryID = ".$this->smileyCategoryID."
				WHERE	smileyID IN (".implode(',', $markedSmileys).")";
			WCF::getDB()->sendQuery($sql);
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