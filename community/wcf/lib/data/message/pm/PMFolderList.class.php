<?php
/**
 * Lists all private message folders for the current user.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm
 * @category 	Community Framework (commercial)
 */
class PMFolderList {
	public $folders = array();
	public $usage = 0;
	
	// default folders
	const FOLDER_INBOX = 0;
	const FOLDER_OUTBOX = -1;
	const FOLDER_DRAFTS = -2;
	const FOLDER_TRASH = -3;
	
	/**
	 * Creates a new PMFolderList object.
	 */
	public function __construct() {
		$this->folders = $this->getFolders();
		$this->readMessageCounts();
		$this->calculateUsage();
	}
	
	/**
	 * Calculates the number of messages in the different message folders.
	 */
	protected function readMessageCounts() {
		// use 3 queries here because it's faster than an union
		$sql = "SELECT		folderID, isDeleted, 0 AS saveInOutbox, 0 AS isDraft, isViewed
			FROM 		wcf".WCF_N."_pm_to_user
			WHERE 		recipientID = ".WCF::getUser()->userID." 
					AND isDeleted < 2";
		$this->calculateMessageCounts($sql);
					
		$sql = "SELECT		0 AS folderID, 0 AS isDeleted, saveInOutbox, 0 AS isDraft, 1 AS isViewed
			FROM 		wcf".WCF_N."_pm
			WHERE 		userID = ".WCF::getUser()->userID."
					AND saveInOutbox = 1";
		$this->calculateMessageCounts($sql);
			
		$sql = "SELECT		0 AS folderID, 0 AS isDeleted, 0 AS saveInOutbox, isDraft, 1 AS isViewed
			FROM 		wcf".WCF_N."_pm
			WHERE 		userID = ".WCF::getUser()->userID."
					AND isDraft = 1";
		$this->calculateMessageCounts($sql);
		
		// update trash folder icon
		if ($this->folders[self::FOLDER_TRASH]['messages'] == 0) {
			$this->folders[self::FOLDER_TRASH]['icon'] = 'pmTrashEmptyM.png';
			$this->folders[self::FOLDER_TRASH]['iconLarge'] = 'pmTrashEmptyL.png';
		}
	}
	
	/**
	 * Calculates the number of messages in the different message folders.
	 */
	protected function calculateMessageCounts($sql) {
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['isDeleted'] == 1) {
				$this->folders[self::FOLDER_TRASH]['messages']++;
				if (!$row['isViewed']) {
					$this->folders[self::FOLDER_TRASH]['unreadMessages']++;
				}
			}
			else if ($row['saveInOutbox'] == 1) $this->folders[self::FOLDER_OUTBOX]['messages']++;
			else if ($row['isDraft'] == 1) $this->folders[self::FOLDER_DRAFTS]['messages']++;
			else if (isset($this->folders[$row['folderID']])) {
				$this->folders[$row['folderID']]['messages']++;
				if (!$row['isViewed']) {
					$this->folders[$row['folderID']]['unreadMessages']++;
				}
			}
		}
	}
	
	/**
	 * Calculates the total mailbox usage.
	 */
	protected function calculateUsage() {
		$maxPm = intval(WCF::getUser()->getPermission('user.pm.maxPm'));
		if (!$maxPm) $this->usage = 1.0; 
		else {
			$this->usage = (double) WCF::getUser()->pmTotalCount / (double) $maxPm;
			if ($this->usage > 1.0) $this->usage = 1.0;
		}
	}
	
	/**
	 * Returns the list of visible message folders.
	 * 
	 * @return	array
	 */
	public static function getFolders() {
		$folders = self::getDefaultFolders();
		$folders += self::getUserFolders();
		
		return $folders;
	}
	
	/**
	 * Returns the list of the default message folders.
	 * 
	 * @return	array
	 */
	public static function getDefaultFolders() {
		$folders = array();
		
		// default folders
		$folders[self::FOLDER_INBOX] = array('folderID' => self::FOLDER_INBOX, 'folderName' => WCF::getLanguage()->get('wcf.pm.inbox'), 'icon' => 'pmInboxM.png', 'iconLarge' => 'pmInboxL.png', 'messages' => 0, 'unreadMessages' => 0);
		$folders[self::FOLDER_OUTBOX] = array('folderID' => self::FOLDER_OUTBOX, 'folderName' => WCF::getLanguage()->get('wcf.pm.outbox'), 'icon' => 'pmOutboxM.png', 'iconLarge' => 'pmOutboxL.png', 'messages' => 0, 'unreadMessages' => 0);
		$folders[self::FOLDER_DRAFTS] = array('folderID' => self::FOLDER_DRAFTS, 'folderName' => WCF::getLanguage()->get('wcf.pm.drafts'), 'icon' => 'pmDraftsM.png', 'iconLarge' => 'pmDraftsL.png', 'messages' => 0, 'unreadMessages' => 0);
		$folders[self::FOLDER_TRASH] = array('folderID' => self::FOLDER_TRASH, 'folderName' => WCF::getLanguage()->get('wcf.pm.trash'), 'icon' => 'pmTrashM.png', 'iconLarge' => 'pmTrashL.png', 'messages' => 0, 'unreadMessages' => 0);

		return $folders;
	}
	
	/**
	 * Returns the list of all by the active user created message folders.
	 * 
	 * @return	array
	 */
	public static function getUserFolders() {
		$folders = array();
		
		// user folders
		$sql = "SELECT		*
			FROM 		wcf".WCF_N."_pm_folder
			WHERE 		userID = ".WCF::getUser()->userID."
			ORDER BY 	folderName";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['icon'] = 'pmFolder'.ucfirst($row['color']).'M.png';
			$row['iconLarge'] = 'pmFolder'.ucfirst($row['color']).'L.png';
			$row['messages'] = 0;
			$row['unreadMessages'] = 0;
			
			$folders[$row['folderID']] = $row;
		}
		
		return $folders;
	}
}
?>