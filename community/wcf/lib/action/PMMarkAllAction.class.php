<?php
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMFolderList.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMAction.class.php');

/**
 * Marks all messages of a folder.  
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class PMMarkAllAction extends AbstractSecureAction {
	public $folderID = 0;
	public $folderList = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}
		
		if (isset($_REQUEST['folderID'])) $this->folderID = intval($_REQUEST['folderID']);
		$this->folderList = new PMFolderList();
		if (!isset($this->folderList->folders[$this->folderID])) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// get pm ids
		switch ($this->folderID) {
			// outbox
			case PMFolderList::FOLDER_OUTBOX: 
				$sql = "SELECT		pmID 
					FROM 		wcf".WCF_N."_pm
					WHERE 		userID = ".WCF::getUser()->userID."
							AND saveInOutbox = 1";
				break;
			
			// drafts
			case PMFolderList::FOLDER_DRAFTS: 
				$sql = "SELECT		pmID 
					FROM 		wcf".WCF_N."_pm
					WHERE 		userID = ".WCF::getUser()->userID."
							AND isDraft = 1";
				break;
			
			// trash
			case PMFolderList::FOLDER_TRASH: 
				$sql = "SELECT		pmID
					FROM		wcf".WCF_N."_pm_to_user
					WHERE		recipientID = ".WCF::getUser()->userID."
							AND isDeleted = 1";
				break;
			
			// inbox & own folders
			default:
				$sql = "SELECT		pmID
					FROM		wcf".WCF_N."_pm_to_user
					WHERE		recipientID = ".WCF::getUser()->userID."
							AND isDeleted = 0
							AND folderID = ".$this->folderID;
		}
		
		$pmIDArray = array();
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$pmIDArray[] = $row['pmID'];
		}
		
		// update session
		$markedMessages = PMAction::getMarkedMessages();
		if ($markedMessages == null || !is_array($markedMessages)) { 
			WCF::getSession()->register('markedPMs', $pmIDArray);
		}
		else {
			$update = false;
			foreach ($pmIDArray as $pmID) {
				if (!in_array($pmID, $markedMessages)) {
					array_push($markedMessages, $pmID);
					$update = true;
				}
			}
			
			if ($update) {
				WCF::getSession()->register('markedPMs', $markedMessages);
			}
		}
		
		WCF::getSession()->update();
		WCF::getSession()->disableUpdate(true);
		$this->executed();

		HeaderUtil::redirect('index.php?page=PMList&folderID='.$this->folderID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>