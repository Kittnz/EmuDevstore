<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMAction.class.php');

/**
 * Downloads a private message dialogue.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class PMDialogueDownloadAction extends AbstractAction {
	/**
	 * parent pm id
	 * 
	 * @var	integer
	 */
	public $parentPmID = 0;
	
	/**
	 * list of pm ids
	 * 
	 * @var	array<integer>
	 */
	public $pmIDArray = array();
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['parentPmID'])) $this->parentPmID = intval($_REQUEST['parentPmID']);
		if (!$this->parentPmID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// get ids
		$sql = "SELECT	pm.pmID
			FROM	wcf".WCF_N."_pm pm
			WHERE	pm.parentPmID = ".$this->parentPmID."
				AND (
					(pm.userID = ".WCF::getUser()->userID." AND pm.saveInOutbox = 1)
					OR pm.pmID IN (
						SELECT	pmID
						FROM	wcf".WCF_N."_pm_to_user
						WHERE	recipientID = ".WCF::getUser()->userID."
							AND isDeleted < 2
					)
				)";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->pmIDArray[] = $row['pmID'];
		}
		if (!count($this->pmIDArray)) {
			throw new IllegalLinkException();
		}
		
		// download
		PMAction::downloadAll(implode(',', $this->pmIDArray));
	}
}
?>