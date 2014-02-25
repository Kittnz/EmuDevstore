<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/SmileyEditor.class.php');

/**
 * Marks / unmarks smileys.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class SmileyMarkAction extends AbstractAction {
	public $action = '';
	public $smileyID = 0;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['action'])) $this->action = $_POST['action'];
		if (isset($_POST['smileyID'])) {
			if (is_array($_POST['smileyID'])) $this->smileyID = ArrayUtil::toIntegerArray($_POST['smileyID']);
			else $this->smileyID = intval($_POST['smileyID']); 
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.smiley.canEditSmiley', 'admin.smiley.canDeleteSmiley'));
		
		// mark / unmark
		if ($this->action == 'mark') SmileyEditor::mark($this->smileyID);
		else if ($this->action == 'unmark') SmileyEditor::unmark($this->smileyID);
		$this->executed();
	}
}
?>