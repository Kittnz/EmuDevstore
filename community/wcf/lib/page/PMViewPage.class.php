<?php
require_once(WCF_DIR.'lib/page/PMListPage.class.php');
require_once(WCF_DIR.'lib/data/message/pm/ViewablePM.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMFolderList.class.php');
require_once(WCF_DIR.'lib/data/message/sidebar/MessageSidebarFactory.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMList.class.php');

/**
 * Shows the private message with the given id.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	page
 * @category 	Community Framework (commercial)
 */
class PMViewPage extends PMListPage {
	// system
	public $templateName = 'pmView';
	
	/**
	 * The current page number.
	 * 
	 * @var integer
	 */
	public $pmPageNo = 0;
	
	/**
	 * The number of all pages.
	 * 
	 * @var integer
	 */
	public $pmPages = 0;
	
	/**
	 * The number of items shown per page.
	 * 
	 * @var integer
	 */
	public $pmItemsPerPage = 20;
	
	/**
	 * The number of all items.
	 * 
	 * @var integer
	 */
	public $pmItems = 0;
	
	/**
	 * Indicates the range of the listed items.
	 * 
	 * @var integer
	 */
	public $pmStartIndex, $pmEndIndex;
	
	/**
	 * pm object
	 * 
	 * @var	PM
	 */
	public $pm;
	
	/**
	 * list of private messages.
	 * 
	 * @var	PMList 
	 */
	public $pmList = null;
	
	/**
	 * sidebar factory object
	 * 
	 * @var	MessageSidebarFactory
	 */
	public $sidebarFactory = null;
	
	/**
	 * list of folders
	 * 
	 * @var	array
	 */
	public $moveToOptions = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get page number
		if (isset($_REQUEST['pmPageNo'])) $this->pmPageNo = intval($_REQUEST['pmPageNo']);
		// get pm id
		if (isset($_REQUEST['pmID'])) $this->pmID = intval($_REQUEST['pmID']);
		$this->pm = new PM($this->pmID);
		if (!$this->pm->pmID) {
			throw new IllegalLinkException();
		}
		if (!$this->pm->hasAccess()) {
			throw new PermissionDeniedException();
		}
		
		// init pm list
		$this->pmList = new PMList($this->pm);
		
		// go to message
		if ($this->pm->parentPmID && !isset($_REQUEST['pmPageNo'])) {
			$this->goToMessage();
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// calculates page number
		$this->calculateNumberOfPMPages();
		
		// get messages
		$this->pmList->sqlOffset = ($this->pmPageNo - 1) * $this->pmItemsPerPage;
		$this->pmList->sqlLimit = $this->pmItemsPerPage;
		$this->pmList->readObjects();
		
		// mark messages as read
		foreach ($this->pmList->getObjects() as $pm) {
			$pm->markAsRead();
		}
		
		// init sidebars
		$this->sidebarFactory = new MessageSidebarFactory($this);
		foreach ($this->pmList->getObjects() as $pm) {
			$this->sidebarFactory->create($pm);
		}
		$this->sidebarFactory->init();
		
		// get folders
		$this->loadMoveToOptions();
		
		// update folder id
		$this->updateFolderID();
	}
	
	/**
	 * Calculates the number of pages and
	 * handles the given page number parameter.
	 */
	public function calculateNumberOfPMPages() {
		// call calculateNumberOfPMPages event
		EventHandler::fireAction($this, 'calculateNumberOfPMPages');
		
		// calculate number of pages
		$this->pmItems = $this->countPMItems();
		$this->pmPages = intval(ceil($this->pmItems / $this->pmItemsPerPage));
		
		// correct active page number
		if ($this->pmPageNo > $this->pmPages) $this->pmPageNo = $this->pmPages;
		if ($this->pmPageNo < 1) $this->pmPageNo = 1;
		
		// calculate start and end index
		$this->pmStartIndex = ($this->pmPageNo - 1) * $this->pmItemsPerPage;
		$this->pmEndIndex = $this->pmStartIndex + $this->pmItemsPerPage;
		$this->pmStartIndex++;
		if ($this->pmEndIndex > $this->pmItems) $this->pmEndIndex = $this->pmItems;
	}
	
	/**
	 * Counts the displayed items.
	 * 
	 * @return	integer
	 */
	public function countPMItems() {
		// call countPMItems event
		EventHandler::fireAction($this, 'countPMItems');
		
		return $this->pmList->countObjects();;
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// assign page parameters
		WCF::getTPL()->assign(array(
			'pmPageNo' => $this->pmPageNo,
			'pmPages' => $this->pmPages,
			'pmItems' => $this->pmItems,
			'pmItemsPerPage' => $this->pmItemsPerPage,
			'pmStartIndex' => $this->pmStartIndex,
			'pmEndIndex' => $this->pmEndIndex,
			'privateMessages' => $this->pmList->getObjects(),
			'attachments' => $this->pmList->getAttachments(),
			'moveToOptions' => $this->moveToOptions,
			'sidebarFactory' => $this->sidebarFactory,
			'showAvatar' => WCF::getUser()->showAvatar,
			'parentPmID' => $this->pm->parentPmID,
			'pmID' => $this->pmID
		));
	}
	
	/**
	 * Validates the given folder id.
	 */
	protected function updateFolderID() {
		$validFolderIDs = array();
		
		// get valid folder id for active message id
		if ($this->pm->isSender()) {
			if ($this->pm->isDraft) $validFolderIDs[] = PMFolderList::FOLDER_DRAFTS;
			else if ($this->pm->saveInOutbox) $validFolderIDs[] = PMFolderList::FOLDER_OUTBOX;
		}
		if ($this->pm->recipientID) {
			if ($this->pm->isDeleted == 1) $validFolderIDs[] = PMFolderList::FOLDER_TRASH;
			else $validFolderIDs[] = $this->pm->folderID;
		}
		
		// check active folder id
		if (!in_array($this->folderID, $validFolderIDs)) {
			// update folder id
			$this->folderID = $validFolderIDs[0];
		}
	}
	
	/**
	 * Returns the move to folder options.
	 * 
	 * @return	array
	 */
	protected function loadMoveToOptions() {
		$folders = $this->folderList->folders;
		unset($folders[-3], $folders[-2], $folders[-1]);
		
		foreach ($folders as $folder) {
			$this->moveToOptions[$folder['folderID']] = $folder['folderName'];
		}
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
	 * Calculates the position of a specific message in this conversation.
	 */
	protected function goToMessage() {
		$sql = "SELECT		COUNT(*) AS items
			FROM		wcf".WCF_N."_pm pm
			WHERE		pm.parentPmID = ".$this->pm->parentPmID."
					AND (
						(pm.userID = ".WCF::getUser()->userID." AND pm.saveInOutbox = 1)
						OR pm.pmID IN (
							SELECT	pmID
							FROM	wcf".WCF_N."_pm_to_user
							WHERE	recipientID = ".WCF::getUser()->userID."
								AND isDeleted < 2
						)
					)
					AND pm.time >= ".$this->pm->time;
		$row = WCF::getDB()->getFirstRow($sql);
		$this->pmPageNo = intval(ceil($row['items'] / $this->pmItemsPerPage));
	}
}
?>