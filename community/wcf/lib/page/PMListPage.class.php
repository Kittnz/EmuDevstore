<?php
require_once(WCF_DIR.'lib/data/message/pm/PMFolderList.class.php');
require_once(WCF_DIR.'lib/data/message/pm/ViewablePM.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMAction.class.php');
require_once(WCF_DIR.'lib/page/SortablePage.class.php');

/**
 * Lists all private messages in the folder with the given folder id.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	page
 * @category 	Community Framework (commercial)
 */
class PMListPage extends SortablePage {
	public $defaultSortField = PM_DEFAULT_SORT_FIELD;
	public $realSortField;
	public $defaultSortOrder = PM_DEFAULT_SORT_ORDER;
	public $folderID = 0;
	public $folderList;
	public $templateName = 'pmIndex';
	public $messages = array();
	public $pmID = 0;
	public $itemsPerPage = PM_MESSAGES_PER_PAGE;
	public $showRecipientColumn, $showIconColumn, $showViewedColumn;
	public $markedMessages = 0;
	public $filterBySender = 0;
	public $availableSenders = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		// get folders
		$this->folderList = new PMFolderList();
		
		parent::readParameters();
		
		if (WCF::getUser()->pmsPerPage) $this->itemsPerPage = WCF::getUser()->pmsPerPage;
		
		// get folder id
		if (isset($_REQUEST['folderID'])) $this->folderID = intval($_REQUEST['folderID']);
		if (!isset($this->folderList->folders[$this->folderID])) {
			throw new IllegalLinkException();
		}
		if (isset($_REQUEST['filterBySender'])) $this->filterBySender = intval($_REQUEST['filterBySender']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get message list
		$this->readMessages();
		$this->readAvailableSenders();
		
		// columns
		$this->showRecipientColumn = $this->folderID == PMFolderList::FOLDER_DRAFTS || 
			$this->folderID == PMFolderList::FOLDER_OUTBOX;
		$this->showIconColumn = !$this->showRecipientColumn;
		$this->showViewedColumn = $this->showRecipientColumn;
		
		// marked messages
		$this->markedMessages = PMAction::getMarkedCount();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'messages' => $this->messages,
			'folders' => $this->folderList->folders,
			'folderID' => $this->folderID,
			'usage' => $this->folderList->usage,
			'showRecipientColumn' => $this->showRecipientColumn,
			'showIconColumn' => $this->showIconColumn,
			'showViewedColumn' => $this->showViewedColumn,
			'markedMessages' => $this->markedMessages,
			'pmID' => $this->pmID,
			'availableSenders' => $this->availableSenders,
			'filterBySender' => $this->filterBySender
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!MODULE_PM) {
			throw new IllegalLinkException();
		}

		// check permission
		WCF::getUser()->checkPermission('user.pm.canUsePm');
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		parent::show();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		$this->realSortField = $this->sortField;
		switch ($this->sortField) {
			case 'subject':
			case 'time': break;
			case 'recipients': if ($this->folderID == PMFolderList::FOLDER_OUTBOX || $this->folderID == PMFolderList::FOLDER_DRAFTS) break;
			case 'isViewedByAll': if ($this->folderID == PMFolderList::FOLDER_OUTBOX) break;
			case 'username': if ($this->folderID != PMFolderList::FOLDER_OUTBOX && $this->folderID != PMFolderList::FOLDER_DRAFTS) break;
			case 'isViewed': 
				if ($this->folderID != PMFolderList::FOLDER_OUTBOX && $this->folderID != PMFolderList::FOLDER_DRAFTS) {
					$this->realSortField = 'isViewedSortField '.$this->sortOrder.', isReplied '.$this->sortOrder.', isForwarded';
					break;
				}
			default: 
				$this->sortField = $this->defaultSortField;
				$this->realSortField = $this->sortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		switch ($this->folderID) {
			// outbox
			case PMFolderList::FOLDER_OUTBOX: 
				$sql = "SELECT	COUNT(*) AS count
					FROM 	wcf".WCF_N."_pm
					WHERE 	userID = ".WCF::getUser()->userID."
						AND saveInOutbox = 1";
				break;
			
			// drafts
			case PMFolderList::FOLDER_DRAFTS: 
				$sql = "SELECT	COUNT(*) AS count
					FROM 	wcf".WCF_N."_pm
					WHERE 	userID = ".WCF::getUser()->userID."
						AND isDraft = 1";
				break;
			
			// trash
			case PMFolderList::FOLDER_TRASH: 
				$sql = "SELECT		COUNT(*) AS count
					FROM 		wcf".WCF_N."_pm_to_user pm_to_user
					".($this->filterBySender != 0 ? "LEFT JOIN wcf".WCF_N."_pm pm USING (pmID)" : '')."
					WHERE 		pm_to_user.recipientID = ".WCF::getUser()->userID."
							AND pm_to_user.isDeleted = 1
							".($this->filterBySender != 0 ? "AND pm.userID = ".$this->filterBySender : '');
				break;
			
			// inbox & own folders
			default:
				$sql = "SELECT		COUNT(*) AS count
					FROM 		wcf".WCF_N."_pm_to_user pm_to_user
					".($this->filterBySender != 0 ? "LEFT JOIN wcf".WCF_N."_pm pm USING (pmID)" : '')."
					WHERE 		pm_to_user.recipientID = ".WCF::getUser()->userID."
							AND pm_to_user.isDeleted = 0
							AND pm_to_user.folderID = ".$this->folderID."
							".($this->filterBySender != 0 ? "AND pm.userID = ".$this->filterBySender : '');
		}
		
		$result = WCF::getDB()->getFirstRow($sql);
		return $result['count'];
	}
	
	/**
	 * Reads a list of available senders.
	 */
	protected function readAvailableSenders() {
		switch ($this->folderID) {
			// trash
			case PMFolderList::FOLDER_TRASH: 
				$sql = "SELECT		DISTINCT pm.userID, user_table.username
					FROM 		wcf".WCF_N."_pm_to_user pm_to_user
					LEFT JOIN	wcf".WCF_N."_pm pm
					ON		(pm.pmID = pm_to_user.pmID)
					LEFT JOIN	wcf".WCF_N."_user user_table
					ON		(user_table.userID = pm.userID)
					WHERE 		pm_to_user.recipientID = ".WCF::getUser()->userID."
							AND pm_to_user.isDeleted = 1
							AND user_table.userID IS NOT NULL
					ORDER BY 	user_table.username";
				break;
			
			// inbox & own folders
			default:
				$sql = "SELECT		DISTINCT pm.userID, user_table.username
					FROM 		wcf".WCF_N."_pm_to_user pm_to_user
					LEFT JOIN	wcf".WCF_N."_pm pm
					ON		(pm.pmID = pm_to_user.pmID)
					LEFT JOIN	wcf".WCF_N."_user user_table
					ON		(user_table.userID = pm.userID)
					WHERE 		pm_to_user.recipientID = ".WCF::getUser()->userID."
							AND pm_to_user.isDeleted = 0
							AND folderID = ".$this->folderID."
							AND user_table.userID IS NOT NULL
					ORDER BY 	user_table.username";
		}
		
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->availableSenders[$row['userID']] = $row['username'];
		}
	}
	
	/**
	 * Reads the messages in the selected folder.
	 */
	protected function readMessages() {
		// call readMessages only if folder is NOT empty
		if ($this->items == 0) {
			return;
		}
		
		$defaultSort = ($this->sortField != $this->defaultSortField ? ', '.$this->defaultSortField.' '.$this->defaultSortOrder : '');
		
		switch ($this->folderID) {
			// outbox
			case PMFolderList::FOLDER_OUTBOX: 
				$sql = "SELECT		pm.*, 
							(SELECT	MAX(isViewed) FROM wcf".WCF_N."_pm_to_user WHERE pmID = pm.pmID) AS isViewedByOne
							".($this->sortField == 'recipients' ? ", (SELECT GROUP_CONCAT(recipient ORDER BY recipient SEPARATOR ', ') FROM wcf".WCF_N."_pm_to_user WHERE pmID = pm.pmID) AS recipients" : '')."
					FROM 		wcf".WCF_N."_pm pm
					WHERE 		userID = ".WCF::getUser()->userID."
							AND saveInOutbox = 1
					ORDER BY 	".$this->realSortField." ".$this->sortOrder.
							$defaultSort;
				break;
			
			// drafts
			case PMFolderList::FOLDER_DRAFTS: 
				$sql = "SELECT		pm.*, 
							1 AS isViewedByAll
							".($this->sortField == 'recipients' ? ", (SELECT GROUP_CONCAT(recipient ORDER BY recipient SEPARATOR ', ') FROM wcf".WCF_N."_pm_to_user WHERE pmID = pm.pmID) AS recipients" : '')."
					FROM 		wcf".WCF_N."_pm pm
					WHERE 		userID = ".WCF::getUser()->userID."
							AND isDraft = 1
					ORDER BY 	".$this->realSortField." ".$this->sortOrder.
							$defaultSort;
				break;
			
			// trash
			case PMFolderList::FOLDER_TRASH: 
				$sql = "SELECT		pm.*,
							recipient.*, IF(recipient.isViewed > 0, 1, 0) isViewedSortField
					FROM		wcf".WCF_N."_pm_to_user recipient
					LEFT JOIN	wcf".WCF_N."_pm pm
					ON		(pm.pmID = recipient.pmID)
					WHERE		recipient.recipientID = ".WCF::getUser()->userID."
							AND recipient.isDeleted = 1
							".($this->filterBySender != 0 ? "AND pm.userID = ".$this->filterBySender : '')."
					ORDER BY 	".$this->realSortField." ".$this->sortOrder.
							$defaultSort;
				break;
			
			// inbox & own folders
			default:
				$sql = "SELECT		pm.*,
							recipient.*, IF(recipient.isViewed > 0, 1, 0) isViewedSortField
					FROM		wcf".WCF_N."_pm_to_user recipient
					LEFT JOIN	wcf".WCF_N."_pm pm
					ON		(pm.pmID = recipient.pmID)
					WHERE		recipient.recipientID = ".WCF::getUser()->userID."
							AND recipient.isDeleted = 0
							AND recipient.folderID = ".$this->folderID."
							".($this->filterBySender != 0 ? "AND pm.userID = ".$this->filterBySender : '')."
					ORDER BY 	".$this->realSortField." ".$this->sortOrder.
							$defaultSort;
		}

		$pmIDArray = array();
		$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$pmIDArray[] = $row['pmID'];
			$this->messages[$row['pmID']] = new ViewablePM(null, $row);
		}
		
		// get recipients
		// outbox
		if (count($pmIDArray) && ($this->folderID == PMFolderList::FOLDER_OUTBOX || $this->folderID == PMFolderList::FOLDER_DRAFTS)) {
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_pm_to_user
				WHERE		pmID IN (".implode(',', $pmIDArray).")
						AND isBlindCopy = 0
				ORDER BY	recipient";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->messages[$row['pmID']]->setRecipient(new PMRecipient(null, null, $row));
			}
		}
	}
}
?>