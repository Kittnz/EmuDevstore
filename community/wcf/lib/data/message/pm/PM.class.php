<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/Message.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMRecipient.class.php');

/**
 * This class represents a private message.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm
 * @category 	Community Framework (commercial)
 */
class PM extends Message {
	protected $recipients = null;
	
	/**
	 * Creates a new PM object.
	 * 
	 * @param	integer		$pmID
	 * @param	array		$row
	 */
	public function __construct($pmID, $row = null) {
		if ($pmID !== null) {
			$sql = "SELECT		recipient.*,
						pm.*,
						user.username
				FROM 		wcf".WCF_N."_pm pm
				LEFT JOIN 	wcf".WCF_N."_pm_to_user recipient
				ON 		(recipient.pmID = pm.pmID
						AND recipient.recipientID = ".WCF::getUser()->userID."
						AND recipient.isDeleted < 2)
				LEFT JOIN 	wcf".WCF_N."_user user
				ON 		(user.userID = pm.userID)
				WHERE 		pm.pmID = ".$pmID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		$this->messageID = $row['pmID'];
		parent::__construct($row);
	}
	
	/**
	 * Returns true, if this message is viewed by recipient.
	 * 
	 * @return	boolean
	 */
	public function isViewed() {
		// current user is a recipient of this message
		if ($this->isViewed) {
			return $this->isViewed;
		}
		// current user is the author of this message
		if ($this->isViewedByAll) {
			return $this->isViewedByAll;
		}
	}
	
	/**
	 * Returns true, if the active user has access to this message.
	 * 
	 * @return	boolean
	 */
	public function hasAccess() {
		return (WCF::getUser()->userID == $this->recipientID && $this->isDeleted < 2) || (WCF::getUser()->userID == $this->userID && ($this->saveInOutbox || $this->isDraft));
	}
	
	/**
	 * Marks this private message as read.
	 */
	public function markAsRead() {
		// update only if current user is recipient and message is unread
		if (WCF::getUser()->userID == $this->recipientID && !$this->isViewed) {
			$sql = "UPDATE	wcf".WCF_N."_pm_to_user
				SET 	isViewed = ".TIME_NOW.",
					userWasNotified = 1
				WHERE 	pmID = ".$this->messageID."
					AND recipientID = ".$this->recipientID;
			WCF::getDB()->sendQuery($sql);
			
			$this->updateViewedByAll();
			$this->updateUnreadMessageCount(WCF::getUser()->userID);
			WCF::getSession()->resetUserData();
		}
	}
	
	/**
	 * Marks this private message as unread.
	 */
	public function markAsUnread() {
		// update only if current user is recipient and message is read
		if (WCF::getUser()->userID == $this->recipientID && $this->isViewed) {
			$sql = "UPDATE	wcf".WCF_N."_pm_to_user
				SET 	isViewed = 0
				WHERE 	pmID = ".$this->messageID."
					AND recipientID = ".$this->recipientID;
			WCF::getDB()->sendQuery($sql);
			
			$this->updateViewedByAll();
			$this->updateUnreadMessageCount(WCF::getUser()->userID);
			WCF::getSession()->resetUserData();
		}
	}
	
	/**
	 * Updates the unread message count for the given userIDs.
	 */
	public static function updateUnreadMessageCount($userIDs) {
		$sql = "UPDATE	wcf".WCF_N."_user user
			SET	pmUnreadCount = (
					SELECT	COUNT(*)
					FROM 	wcf".WCF_N."_pm_to_user
					WHERE 	recipientID = user.userID
						AND isDeleted < 2
						AND isViewed = 0
				),
				pmOutstandingNotifications = (
					SELECT	COUNT(*)
					FROM 	wcf".WCF_N."_pm_to_user
					WHERE 	recipientID = user.userID
						AND isDeleted = 0
						AND folderID = 0
						AND isViewed = 0						
						AND userWasNotified = 0
				)
			WHERE	user.userID IN (".$userIDs.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Updates the total message count for the given userIDs.
	 */
	public static function updateTotalMessageCount($userIDs) {
		$sql = "UPDATE	wcf".WCF_N."_user user
			SET	pmTotalCount = (
					SELECT	COUNT(*)
					FROM 	wcf".WCF_N."_pm_to_user
					WHERE 	recipientID = user.userID
						AND isDeleted < 2)
						+ (
					SELECT		COUNT(*)
					FROM 		wcf".WCF_N."_pm pm
					LEFT JOIN	wcf".WCF_N."_pm_to_user pm_to_user
					ON		(pm_to_user.pmID = pm.pmID
							AND pm_to_user.recipientID = pm.userID
							AND pm_to_user.isDeleted < 2)
					WHERE 		userID = user.userID
							AND (saveInOutBox = 1
							OR isDraft = 1)
							AND pm_to_user.pmID IS NULL)
					
				 
			WHERE	user.userID IN (".$userIDs.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Returns a list of new private messages for the active user.
	 * 
	 * @param	integer			$userID	
	 * 
	 * @return	array<ViewablePM>
	 */
	public static function getOutstandingNotifications($userID = null) {
		if ($userID === null) $userID = WCF::getUser()->userID;
		require_once(WCF_DIR.'lib/data/message/pm/ViewablePM.class.php');
		$pms = array();
		$sql = "SELECT		pm.*
			FROM		wcf".WCF_N."_pm_to_user pm_to_user
			LEFT JOIN	wcf".WCF_N."_pm	pm
			ON		(pm.pmID = pm_to_user.pmID)
			WHERE		pm_to_user.recipientID = ".$userID."
					AND pm_to_user.isDeleted = 0
					AND pm_to_user.isViewed = 0
					AND pm_to_user.userWasNotified = 0
			ORDER BY	pm.time DESC";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$pms[] = new ViewablePM(null, $row); 
		}
		
		return $pms;
	}
	
	/**
	 * Disables all outstanding notifications.
	 */
	public static function disableNotifications() {
		$sql = "UPDATE	wcf".WCF_N."_pm_to_user
			SET	userWasNotified = 1
			WHERE	recipientID = ".WCF::getUser()->userID;
		WCF::getDB()->sendQuery($sql);
		
		$editor = WCF::getUser()->getEditor();
		$editor->update('', '', '', null, null, array('pmOutstandingNotifications' => 0));
	}
	
	/**
	 * Updates the viewed by all status of this message.
	 */ 
	public function updateViewedByAll() {
		$sql = "UPDATE	wcf".WCF_N."_pm
			SET	isViewedByAll = if((
					SELECT	COUNT(*)
					FROM 	wcf".WCF_N."_pm_to_user
					WHERE 	pmID = ".$this->messageID."
						AND isDeleted < 2
						AND isViewed = 0) > 0
				, 0, 1) 
			WHERE	pmID = ".$this->messageID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Returns the list of recipients.
	 * 
	 * @return	array<PMRecipient>
	 */
	public function getRecipients() {
		if ($this->recipients === null) {
			$this->recipients = array();
			
			// get recipients
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_pm_to_user
				WHERE		pmID = ".$this->pmID."
						AND isBlindCopy = 0
				ORDER BY	recipient";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->recipients[] = new PMRecipient(null, null, $row);
			}
		}
		
		return $this->recipients;
	}
	
	/**
	 * Sets a recipient.
	 * 
	 * @param	PMRecipient	$recipient
	 */
	public function setRecipient(PMRecipient $recipient) {
		if ($this->recipients === null) {
			$this->recipients = array();
		}
		
		$this->recipients[] = $recipient;
	}
	
	/**
	 * Sets the recipients.
	 * 
	 * @param	array<PMRecipient>	$recipients
	 */
	public function setRecipients($recipients) {
		$this->recipients = $recipients;
	}
	
	/**
	 * Returns true, if the active user is the author of this message.
	 * 
	 * @return	boolean
	 */
	public function isSender() {
		return ($this->userID == WCF::getUser()->userID);
	}
	
	/**
	 * Returns the number of quotes of this message.
	 * 
	 * @return	integer
	 */
	public function isQuoted() {
		require_once(WCF_DIR.'lib/data/message/multiQuote/MultiQuoteManager.class.php');
		return MultiQuoteManager::getQuoteCount($this->pmID, 'pm');
	}
}
?>