<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/SmileyEditor.class.php');

/**
 * Changes the sort order of a given smiley
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class SmileySortAction extends AbstractAction {
	public $showOrder = 0;
	public $smiley = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['smileyID'])) $this->smileyID = intval($_REQUEST['smileyID']);
		$this->smiley = new SmileyEditor($this->smileyID);
		if (!$this->smiley->smileyID) {
			throw new IllegalLinkException();
		}
		
		if (isset($_REQUEST['showOrder'])) $this->showOrder = intval($_REQUEST['showOrder']);
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// check permission
		WCF::getUser()->checkPermission('admin.smiley.canEditSmiley');
		
		if ($this->showOrder) {
			$this->smiley->update($this->smiley->smileyPath, $this->smiley->smileyTitle, $this->smiley->smileyCode, $this->showOrder, $this->smiley->smileyCategoryID);
			//$this->smiley->removePositions();
			//$this->smiley->addPosition($this->smiley->smileyCategoryID, ($this->showOrder ? $this->showOrder : null));
		}
		SmileyEditor::resetCache();
		
		$this->executed();
	}
}
?>