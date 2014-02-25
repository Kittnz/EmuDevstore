<?php
require_once(WCF_DIR.'lib/data/message/search/AbstractSearchableMessageType.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMFolderList.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMSearchResult.class.php');

/**
 * An implementation of SearchableMessageType for searching private messages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm
 * @category 	Community Framework (commercial)
 */
class PMSearch extends AbstractSearchableMessageType {
	protected $messageCache = array();
	protected $folderIDs = array();
	
	/**
	 * Caches the data of the messages with the given ids.
	 */
	public function cacheMessageData($messageIDs, $additionalData = null) {
		$folders = PMFolderList::getDefaultFolders();
		
		// get recipients
		$recpients = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_pm_to_user
			WHERE		pmID IN (".$messageIDs.")
					AND isBlindCopy = 0
			ORDER BY	recipient";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($recpients[$row['pmID']])) $recpients[$row['pmID']] = array();
			$recpients[$row['pmID']][] = new PMRecipient(null, null, $row);
		}
	
		// get messages
		$sql = "SELECT		pm.*,
					pm_to_user.recipient, pm_to_user.isDeleted,
					pm_folder.folderID, pm_folder.folderName
			FROM		wcf".WCF_N."_pm pm
			LEFT JOIN 	wcf".WCF_N."_pm_to_user pm_to_user
			ON 		(pm_to_user.pmID = pm.pmID
					AND pm_to_user.recipientID = ".WCF::getUser()->userID.")
			LEFT JOIN 	wcf".WCF_N."_pm_folder pm_folder
			ON 		(pm_folder.folderID = pm_to_user.folderID)
			WHERE		pm.pmID IN (".$messageIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			// default folders
			if (!$row['folderID']) {
				// trash
				if ($row['isDeleted']) {
					$row['folderID'] = PMFolderList::FOLDER_TRASH;
					$row['folderName'] = $folders[PMFolderList::FOLDER_TRASH]['folderName'];
				}
				// draft
				else if ($row['isDraft']) {
					$row['folderID'] = PMFolderList::FOLDER_DRAFTS;
					$row['folderName'] = $folders[PMFolderList::FOLDER_DRAFTS]['folderName'];
				}
				// outbox
				else if ($row['userID'] == WCF::getUser()->userID && $row['saveInOutbox']) {
					$row['folderID'] = PMFolderList::FOLDER_OUTBOX;
					$row['folderName'] = $folders[PMFolderList::FOLDER_OUTBOX]['folderName'];
				}
				// inbox
				else {
					$row['folderID'] = PMFolderList::FOLDER_INBOX;
					$row['folderName'] = $folders[PMFolderList::FOLDER_INBOX]['folderName'];
				}
			}
			
			$pm = new PMSearchResult(null, $row);
			$pm->setRecipients((isset($recpients[$row['pmID']]) ? $recpients[$row['pmID']] : array()));
			$this->messageCache[$row['pmID']] = array('type' => 'pm', 'message' => $pm);
		}
	}
	
	/**
	 * @see SearchableMessageType::getMessageData()
	 */
	public function getMessageData($messageID, $additionalData = null) {
		if (isset($this->messageCache[$messageID])) return $this->messageCache[$messageID];
		return null;
	}
	
	/**
	 * Shows private message specific form elements in the global search form.
	 */
	public function show($form = null) {
		$folderOptions = array();
		foreach (PMFolderList::getFolders() as $folder) {
			$folderOptions[$folder['folderID']] = StringUtil::encodeHTML($folder['folderName']);
		}
		
		// get existing values
		if ($form !== null && isset($form->searchData['additionalData']['pm'])) {
			$this->folderIDs = $form->searchData['additionalData']['pm']['folderIDs'];
		}
		
		WCF::getTPL()->assign(array(
			'folderOptions' => $folderOptions,
			'folderIDs' => $this->folderIDs,
			'selectAllFolders' => count($this->folderIDs) == 0 || $this->folderIDs[0] == -10
		));
	}
	
	/**
	 * Returns the conditions for a search in the table of this search type.
	 */
	public function getConditions($form = null) {
		// get existing values
		if ($form !== null && isset($form->searchData['additionalData']['pm'])) {
			$this->folderIDs = $form->searchData['additionalData']['pm']['folderIDs'];
		}
		
		// get new values
		if (isset($_POST['folderIDs']) && is_array($_POST['folderIDs'])) {
			$folderIDs = ArrayUtil::toIntegerArray($_POST['folderIDs']);
			$this->folderIDs = $folderIDs;
		}
		else $folderIDs = array();
		
		if (count($folderIDs) && $folderIDs[0] == -10) $folderIDs = array();
		
		// remove empty elements
		foreach ($folderIDs as $key => $folderID) {
			if ($folderID == -11) unset($folderIDs[$key]);
		}
		
		$selectedFolderIDs = count($folderIDs);
		
		// inbox folders
		$inboxFolders = '';
		if ($selectedFolderIDs && !in_array(PMFolderList::FOLDER_TRASH, $folderIDs)) {
			$inboxFolders = '-1';
		}
		foreach ($folderIDs as $folderID) {
			if ($folderID >= 0) {
				if (!empty($inboxFolders)) $inboxFolders .= ',';
				$inboxFolders .= $folderID;
			}
		}
		
		$outbox = !$selectedFolderIDs || in_array(PMFolderList::FOLDER_OUTBOX, $folderIDs);
		$drafts = !$selectedFolderIDs || in_array(PMFolderList::FOLDER_DRAFTS, $folderIDs);
		$trash = !$selectedFolderIDs || in_array(PMFolderList::FOLDER_TRASH, $folderIDs);
		
		// return sql condition
		return '('.(($outbox || $drafts) ? 
			"(userID = ".WCF::getUser()->userID." AND (".($outbox ? "saveInOutbox = 1" : "")." ".($outbox && $drafts ? "OR" : "")." ".($drafts ? "isDraft = 1" : "").")) OR " : "").
			"pmID IN (SELECT pmID FROM wcf".WCF_N."_pm_to_user
				WHERE recipientID = ".WCF::getUser()->userID." 
				".(!empty($inboxFolders) ? "AND folderID IN (".$inboxFolders.")" : "")." 
				AND isDeleted < ".($trash ? "2" : "1")."))";
	}
	
	/**
	 * Returns the database table name for this search type.
	 */
	public function getTableName() {
		return 'wcf'.WCF_N.'_pm';
	}
	
	/**
	 * Returns the message id field name for this search type.
	 */
	public function getIDFieldName() {
		return 'pmID';
	}
	
	/**
	 * @see SearchableMessageType::getAdditionalData()
	 */
	public function getAdditionalData() {
		return array(
			'folderIDs' => $this->folderIDs
		);
	}
	
	/**
	 * @see SearchableMessageType::isAccessible()
	 */
	public function isAccessible() {
		return (MODULE_PM && WCF::getUser()->userID != 0);
	}
	
	/**
	 * @see SearchableMessageType::getFormTemplateName()
	 */
	public function getFormTemplateName() {
		return 'searchPm';
	}
	
	/**
	 * @see SearchableMessageType::getResultTemplateName()
	 */
	public function getResultTemplateName() {
		return 'searchResultPm';
	}
}
?>