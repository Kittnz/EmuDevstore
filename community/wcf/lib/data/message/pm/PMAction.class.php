<?php
require_once(WCF_DIR.'lib/data/message/pm/PM.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMEditor.class.php');
require_once(WCF_DIR.'lib/system/io/ZipWriter.class.php');
require_once(WCF_DIR.'lib/system/session/Session.class.php');

/**
 * Executes private message actions.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm
 * @category 	Community Framework (commercial)
 */
class PMAction {
	protected $pm = null;
	protected $activeFolderID = 0;
	
	/**
	 * Creates a new PMAction object.
	 * 
	 * @param	integer		$pmID
	 */
	public function __construct($pmID, $activeFolderID = 0) {
		$this->activeFolderID = $activeFolderID;
		$this->pm = new PMEditor($pmID);
		if (!$this->pm->hasAccess()) throw new PermissionDeniedException();
	}
	
	/**
	 * Starts the download of this message.
	 */
	public function download() {
		self::downloadAll($this->pm->pmID);
		throw new IllegalLinkException();
	}
	
	/**
	 * Starts the download of the marked messages.
	 */
	public static function downloadMarked() {
		$markedMessages = self::getMarkedMessages();
		if ($markedMessages != null && count($markedMessages) > 0) {
			self::downloadAll(implode(',', $markedMessages));
			self::unmark($markedMessages);
		}
		throw new IllegalLinkException();
	}
	
	/**
	 * Starts the download of the specified messages.
	 * 
	 * @param	string		$pmIDs
	 */
	public static function downloadAll($pmIDs) {
		// count messages
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_pm
			WHERE	pmID IN (".$pmIDs.")";
		$row = WCF::getDB()->getFirstRow($sql);
		$count = $row['count'];
		
		// get recipients
		$recpients = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_pm_to_user
			WHERE		pmID IN (".$pmIDs.")
					AND isBlindCopy = 0
			ORDER BY	recipient";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($recpients[$row['pmID']])) $recpients[$row['pmID']] = array();
			$recpients[$row['pmID']][] = new PMRecipient(null, null, $row);
		}
		
		// get messages
		if ($count > 1) $zip = new ZipWriter();
		$sql = "SELECT		recipient.*,
					pm.*
			FROM		wcf".WCF_N."_pm pm
			LEFT JOIN 	wcf".WCF_N."_pm_to_user recipient
			ON 		(recipient.pmID = pm.pmID
					AND recipient.recipientID = ".WCF::getUser()->userID."
					AND recipient.isDeleted < 2)
			WHERE		pm.pmID IN (".$pmIDs.")
			GROUP BY	pm.pmID
			ORDER BY	pm.time DESC";
		$result = WCF::getDB()->sendQuery($sql);
		$messageNo = 1;
		while ($row = WCF::getDB()->fetchArray($result)) {
			$pm = new PM(null, $row);
			$pm->setRecipients((isset($recpients[$row['pmID']]) ? $recpients[$row['pmID']] : array()));
			
			// get parsed text
			require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
			$parser = MessageParser::getInstance();
			$parser->setOutputType('text/plain');
			$parsedText = $parser->parse($pm->message, $pm->enableSmilies, $pm->enableHtml, $pm->enableBBCodes, false);
			
			$data = array(	'$author' => ($pm->username ? $pm->username : WCF::getLanguage()->get('wcf.pm.author.system')),
					'$date' => DateUtil::formatTime(null, $pm->time),
					'$recipient' => implode(', ', $pm->getRecipients()),
					'$subject' => $pm->subject,
					'$text' => $parsedText);
			
			if ($count == 1) {
				// send headers
				// file type
				@header('Content-Type: text/plain');
				
				// file name
				@header('Content-disposition: attachment; filename="'.$pm->pmID.'-'.(preg_replace('~[^a-z0-9_ -]+~i', '', $pm->subject)).'.txt"');
			
				// no cache headers
				@header('Pragma: no-cache');
				@header('Expires: 0');
			
				// output message
				echo (CHARSET == 'UTF-8' ? "\xEF\xBB\xBF" : '') . WCF::getLanguage()->get('wcf.pm.download.message', $data);
				exit;
			}
			else {
				$zip->addFile((CHARSET == 'UTF-8' ? "\xEF\xBB\xBF" : '') . WCF::getLanguage()->get('wcf.pm.download.message', $data), $pm->pmID.'-'.(preg_replace('~[^a-z0-9_ -]+~i', '', $pm->subject)).'.txt', $pm->time);
			}
			$messageNo++;
		}
		
		if ($messageNo > 1) {
			// send headers
			// file type
			@header('Content-Type: application/octet-stream');
			
			// file name
			@header('Content-disposition: attachment; filename="messages-'.($messageNo - 1).'.zip"');
		
			// no cache headers
			@header('Pragma: no-cache');
			@header('Expires: 0');
		
			// output file
			echo $zip->getFile();
			exit;
		}
	}
	
	/**
	 * Cancels this message.
	 */
	public function cancel() {
		if (!$this->pm->isSender()) {
			throw new PermissionDeniedException();
		}
		
		if (!$this->isUnread()) {
			throw new IllegalLinkException();
		}
		
		// get recipients
		$recipients = '';
		$sql = "SELECT		recipientID
			FROM		wcf".WCF_N."_pm_to_user
			WHERE		pmID = ".$this->pm->pmID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($recipients)) $recipients .= ',';
			$recipients .= $row['recipientID'];
		}
		
		// delete message
		$this->deleteData($this->pm->pmID);
		
		// update message count
		PM::updateTotalMessageCount($recipients.','.WCF::getUser()->userID);
		PM::updateUnreadMessageCount($recipients);
		
		// reset session data
		$userIDArray = explode(',', $recipients);
		$userIDArray[] = WCF::getUser()->userID;
		Session::resetSessions($userIDArray, true, false);
		
		HeaderUtil::redirect('index.php?page=PMList&folderID='.$this->activeFolderID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Cancels all marked messages.
	 */
	public static function cancelMarked($activeFolderID) {
		$markedMessages = self::getMarkedMessages();
		if ($markedMessages != null && count($markedMessages) > 0) {
			$pmIDs = '';
			$sql = "SELECT		pm.pmID, MAX(isViewed) AS isViewed
				FROM		wcf".WCF_N."_pm pm
				LEFT JOIN	wcf".WCF_N."_pm_to_user recipients
				ON		(recipients.pmID = pm.pmID)
				WHERE		pm.pmID IN (".implode(',', $markedMessages).")		
						AND pm.userID = ".WCF::getUser()->userID."
				GROUP BY	pm.pmID
				HAVING		isViewed = 0";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!empty($pmIDs)) $pmIDs .= ',';
				$pmIDs .= $row['pmID'];
			}
			
			if (!empty($pmIDs)) {
				$recipients = '';
				$sql = "SELECT		recipientID
					FROM		wcf".WCF_N."_pm_to_user
					WHERE		pmID IN (".$pmIDs.")";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					if (!empty($recipients)) $recipients .= ',';
					$recipients .= $row['recipientID'];
				}
				
				// delete message
				self::deleteData($pmIDs);
				
				// update message count
				PM::updateTotalMessageCount($recipients.','.WCF::getUser()->userID);
				PM::updateUnreadMessageCount($recipients);
				
				// reset session data
				$userIDArray = explode(',', $recipients);
				$userIDArray[] = WCF::getUser()->userID;
				Session::resetSessions($userIDArray, true, false);
			}
			
			self::unmark($markedMessages);
		}
		HeaderUtil::redirect('index.php?page=PMList&folderID='.$activeFolderID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Marks a message as read.
	 */
	public function markAsRead() {
		$this->pm->markAsRead();
	}
	
	/**
	 * Marks a message as read.
	 */
	public function markAsUnread() {
		$this->pm->markAsUnread();
	}
	
	/**
	 * Recovers this message.
	 */
	public function recover() {
		if (!$this->pm->recipientID || $this->pm->isDeleted != 1) {
			throw new PermissionDeniedException();
		}
		
		// update message
		$sql = "UPDATE	wcf".WCF_N."_pm_to_user
			SET	isDeleted = 0,
				folderID = 0
			WHERE	pmID = ".$this->pm->pmID."
				AND ".WCF::getUser()->userID;
		WCF::getDB()->sendQuery($sql);
		
		HeaderUtil::redirect('index.php?page=PMList&folderID='.$this->activeFolderID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Checks whether this message was already read by one of the recipients.
	 * 
	 * @param	boolean
	 */
	public function isUnread() {
		$sql = "SELECT	MAX(isViewed) as isViewed
			FROM	wcf".WCF_N."_pm_to_user
			WHERE	pmID = ".$this->pm->pmID;
		$row = WCF::getDB()->getFirstRow($sql);
		return ($row['isViewed'] == 0);
	}
	
	/**
	 * Offers the possibility to edit an unread message.
	 */
	public function edit() {
		if (!$this->pm->isSender()) {
			throw new PermissionDeniedException();
		}
		
		if (!$this->isUnread()) {
			throw new IllegalLinkException();
		}
		
		// alter message to draft
		$sql = "UPDATE	wcf".WCF_N."_pm_to_user
			SET	isDeleted = 2
			WHERE	pmID = ".$this->pm->pmID;
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE	wcf".WCF_N."_pm
			SET	isDraft = 1,
				saveInOutbox = 0
			WHERE	pmID = ".$this->pm->pmID;
		WCF::getDB()->sendQuery($sql);
		
		// get recipients
		$recipients = '';
		$sql = "SELECT		recipientID
			FROM		wcf".WCF_N."_pm_to_user
			WHERE		pmID = ".$this->pm->pmID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($recipients)) $recipients .= ',';
			$recipients .= $row['recipientID'];
		}
		
		// update message count
		PM::updateTotalMessageCount($recipients);
		PM::updateUnreadMessageCount($recipients);
		
		// reset session data
		Session::resetSessions(explode(',', $recipients), true, false);
		
		// forward to edit page
		HeaderUtil::redirect('index.php?form=PMNew&pmID='.$this->pm->pmID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Moves this message to the specified folder.
	 * 
	 * @param	integer		$folderID
	 */
	public function moveTo($folderID) {
		$this->moveAllTo($this->pm->pmID, $folderID);
		HeaderUtil::redirect('index.php?page=PMList&folderID='.$folderID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Moves all marked messages to the specified folder.
	 * 
	 * @param	integer		$folderID
	 */
	public static function moveMarkedTo($folderID) {
		$markedMessages = self::getMarkedMessages();
		if ($markedMessages != null && count($markedMessages) > 0) {
			self::moveAllTo(implode(',', $markedMessages), $folderID);
			self::unmark($markedMessages);
		}
		HeaderUtil::redirect('index.php?page=PMList&folderID='.$folderID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Moves the messages with the given message ids to the specified folder.
	 * 
	 * @param	string		$pmIDs
	 * @param	integer		$folderID
	 */
	public static function moveAllTo($pmIDs, $folderID) {
		if ($folderID < 0) return false;
		if ($folderID > 0) {
			// validate folderID
			$sql = "SELECT	folderID
				FROM	wcf".WCF_N."_pm_folder
				WHERE	folderID = ".$folderID."
					AND userID = ".WCF::getUser()->userID;
			$row = WCF::getDB()->getFirstRow($sql);
			if (!isset($row['folderID'])) {
				throw new PermissionDeniedException();
			}
		}
		
		// move messages
		$sql = "UPDATE	wcf".WCF_N."_pm_to_user
			SET	folderID = ".$folderID."
			WHERE	pmID IN (".$pmIDs.")
				AND recipientID = ".WCF::getUser()->userID;
		WCF::getDB()->sendQuery($sql);
	}
	
	
	/**
	 * Deletes this message.
	 */
	public function delete() {
		$this->deleteAll($this->pm->pmID);
		$this->unmark($this->pm->pmID);
		HeaderUtil::redirect('index.php?page=PMList&folderID='.$this->activeFolderID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Deletes all marked messages.
	 */
	public static function deleteMarked($activeFolderID) {
		$markedMessages = self::getMarkedMessages();
		if ($markedMessages != null && count($markedMessages) > 0) {
			self::deleteAll(implode(',', $markedMessages));
			self::unmark($markedMessages);
		}
		HeaderUtil::redirect('index.php?page=PMList&folderID='.$activeFolderID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Deletes the messages with the given message ids.
	 * 
	 * @param	string		$pmIDs
	 */
	public static function deleteAll($pmIDs) {
		// get message data
		$sql = "SELECT		recipients.recipientID, recipients.isDeleted,
					pm.pmID, pm.userID, pm.saveInOutbox
			FROM 		wcf".WCF_N."_pm pm
			LEFT JOIN 	wcf".WCF_N."_pm_to_user recipients
			ON 		(recipients.pmID = pm.pmID
					AND recipients.recipientID = ".WCF::getUser()->userID."
					AND recipients.isDeleted < 2)
			WHERE 		pm.pmID IN (".$pmIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['userID'] == WCF::getUser()->userID && $row['saveInOutbox'] == 1) {
				// remove from outbox
				$sql = "UPDATE	wcf".WCF_N."_pm
					SET	saveInOutbox = 0
					WHERE	pmID = ".$row['pmID'];
				WCF::getDB()->sendQuery($sql);
			}
			
			if (isset($row['recipientID'])) {
				// move to trash or mark as deleted completely
				$sql = "UPDATE	wcf".WCF_N."_pm_to_user
					SET	isDeleted = ".($row['isDeleted'] == 0 ? 1 : 2)."
					WHERE	pmID = ".$row['pmID']."
						AND recipientID = ".WCF::getUser()->userID;
				WCF::getDB()->sendQuery($sql);
			}
		}
		
		// update message count
		PM::updateTotalMessageCount(WCF::getUser()->userID);
		PM::updateUnreadMessageCount(WCF::getUser()->userID);
		Session::resetSessions(WCF::getUser()->userID);
		
		// delete messages completely
		$deletePmIDs = '';
		$sql = "SELECT		pm.pmID,
					COUNT(recipients.recipientID) AS count
			FROM		wcf".WCF_N."_pm pm
			LEFT JOIN 	wcf".WCF_N."_pm_to_user recipients
			ON 		(recipients.pmID = pm.pmID
					AND recipients.isDeleted < 2)
			WHERE 		pm.pmID IN (".$pmIDs.")
					AND saveInOutbox = 0
			GROUP BY	pm.pmID
			HAVING 		count = 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($deletePmIDs)) $deletePmIDs .= ',';
			$deletePmIDs .= $row['pmID'];
		}
		
		if (!empty($deletePmIDs)) {
			self::deleteData($deletePmIDs);
			self::unmark(explode(',', $deletePmIDs));
		}
	}
	
	/**
	 * Deletes the data of the specified messages completely.
	 * 
	 * @param	string		$pmIDs
	 */
	protected static function deleteData($pmIDs) {
		// delete recipients
		$sql = "DELETE FROM	wcf".WCF_N."_pm_to_user
			WHERE		pmID IN (".$pmIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		// delete messages
		$sql = "DELETE FROM	wcf".WCF_N."_pm
			WHERE		pmID IN (".$pmIDs.")";
		WCF::getDB()->sendQuery($sql);
		
		// delete pm hashes
		$sql = "DELETE FROM	wcf".WCF_N."_pm_hash
			WHERE		pmID IN (".$pmIDs.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// delete attachments
		require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentListEditor.class.php');
		$attachmentList = new MessageAttachmentListEditor(explode(',', $pmIDs), 'pm', WCF::getPackageID('com.woltlab.wcf.data.message.pm'));
		$attachmentList->deleteAll();
	}
	
	/**
	 * Returns the marked messages.
	 * 
	 * @return	array		marked messages
	 */
	public static function getMarkedMessages() {
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedPMs'])) {
			return $sessionVars['markedPMs'];
		}
		return null;
	}
	
	/**
	 * Returns the number of marked messages.
	 * 
	 * @return	integer
	 */
	public static function getMarkedCount() {
		$markedMessages = self::getMarkedMessages();
		if ($markedMessages != null) return count($markedMessages);
		return 0;
	}
	
	/**
	 * Marks messages.
	 * 
	 * @param	mixed		$pmIDs
	 */
	public static function mark($pmIDs) {
		if (!is_array($pmIDs)) $pmIDs = array($pmIDs);
		
		// check permission
		foreach ($pmIDs as $pmID) {
			$pm = new PMEditor($pmID);
			if (!$pm->hasAccess()) throw new PermissionDeniedException();
		}
		
		$markedMessages = self::getMarkedMessages();
		if ($markedMessages == null || !is_array($markedMessages)) { 
			WCF::getSession()->register('markedPMs', $pmIDs);
		}
		else {
			$update = false;
			foreach ($pmIDs as $pmID) {
				if (!in_array($pmID, $markedMessages)) {
					array_push($markedMessages, $pmID);
					$update = true;
				}
			}
			
			if ($update) {
				WCF::getSession()->register('markedPMs', $markedMessages);
			}
		}
	}
	
	/**
	 * Unmarks messages.
	 * 
	 * @param	mixed		$pmIDs
	 */
	public static function unmark($pmIDs) {
		if (!is_array($pmIDs)) $pmIDs = array($pmIDs);
		
		$markedMessages = self::getMarkedMessages();
		if (is_array($markedMessages)) {
			$update = false;
			
			foreach ($pmIDs as $pmID) {
				if (in_array($pmID, $markedMessages)) {
					$key = array_search($pmID, $markedMessages);
					unset($markedMessages[$key]);
					$update = true;
				}
			}
			
			if ($update) {
				WCF::getSession()->register('markedPMs', $markedMessages);
			}
		}
	}
	
	/**
	 * Unmarks all marked messages.
	 */
	public static function unmarkAll() {
		WCF::getSession()->unregister('markedPMs');
	}
	
	/**
	 * Empties the recycle bin.
	 */
	public static function emptyRecycleBin() {
		$pmIDs = '';
		$sql = "SELECT	pmID
			FROM	wcf".WCF_N."_pm_to_user
			WHERE	recipientID = ".WCF::getUser()->userID."
				AND isDeleted = 1";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($pmIDs)) $pmIDs .= ',';
			$pmIDs .= $row['pmID'];
		}
		
		if (!empty($pmIDs)) {
			self::deleteAll($pmIDs);
			self::unmark(explode(',', $pmIDs));
		}
		
		HeaderUtil::redirect('index.php?page=PMList&folderID=-3'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>