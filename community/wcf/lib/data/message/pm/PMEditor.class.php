<?php
require_once(WCF_DIR.'lib/data/message/pm/PM.class.php');
require_once(WCF_DIR.'lib/data/message/pm/ViewablePM.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/system/session/Session.class.php');

/**
 * This class extends PM class with functions to edit the private message.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm
 * @category 	Community Framework (commercial)
 */
class PMEditor extends PM {
	/**
	 * Updates the database entry of this private message with the given parameters.
	 */
	public function update($draft, $recipients, $blindCopies, $subject, $text, $options = array(), $attachments = null) {
		// get number of attachments
		$attachmentsAmount = $attachments != null ? count($attachments->getAttachments($this->getID())) : 0;
		
		// save message
		$sql = "UPDATE	wcf".WCF_N."_pm
			SET	subject = '".escapeString($subject)."',
				message = '".escapeString($text)."',
				time = ".TIME_NOW.",
				".(isset($options['enableSmilies']) ? "enableSmilies = ".$options['enableSmilies']."," : "")."
				".(isset($options['enableHtml']) ? "enableHtml = ".$options['enableHtml']."," : "")."
				".(isset($options['enableBBCodes']) ? "enableBBCodes = ".$options['enableBBCodes']."," : "")."
				".(isset($options['showSignature']) ? "showSignature = ".$options['showSignature']."," : "")."
				saveInOutbox = ".($draft ? 0 : 1).",
				isDraft = ".($draft ? 1 : 0).",
				attachments = ".$attachmentsAmount."
			WHERE 	pmID = ".$this->messageID;
		WCF::getDB()->sendQuery($sql);
		
		// save recipients
		$recipientIDs = '';
		$inserts = '';
		foreach ($recipients as $recipient) {
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$this->messageID.", ".$recipient['userID'].", '".escapeString($recipient['username'])."'".($draft ? ', 2' : ', 0').", 0)";
			if (!empty($recipientIDs)) $recipientIDs .= ',';
			$recipientIDs .= $recipient['userID'];
		}
		
		// save blind copy recipients
		foreach ($blindCopies as $recipient) {
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$this->messageID.", ".$recipient['userID'].", '".escapeString($recipient['username'])."'".($draft ? ', 2' : ', 0').", 1)";
			if (!empty($recipientIDs)) $recipientIDs .= ',';
			$recipientIDs .= $recipient['userID'];
		}
		
		if (!empty($inserts)) {
			// delete old recipients
			$sql = "DELETE FROM 	wcf".WCF_N."_pm_to_user
				WHERE		pmID = ".$this->messageID;
			WCF::getDB()->sendQuery($sql);
			
			// insert new recipients
			$sql = "INSERT IGNORE INTO 	wcf".WCF_N."_pm_to_user
							(pmID, recipientID, recipient, isDeleted, isBlindCopy)
				VALUES			".$inserts;
			WCF::getDB()->sendQuery($sql);
		}
		
		// update attachments
		if ($attachments != null) {
			$attachments->findEmbeddedAttachments($text);
		}
		
		// update message count
		if (!empty($recipientIDs)) {
			$this->updateUnreadMessageCount($recipientIDs);
			$this->updateTotalMessageCount($recipientIDs.','.$this->userID);
			
			// reset session data
			$userIDArray = explode(',', $recipientIDs);
			$userIDArray[] = $this->userID;
			Session::resetSessions($userIDArray, true, false);
		}
	}
	
	/**
	 * Tests whether a private message with the given parameters already exist.
	 */
	public static function test($recipients, $blindCopies, $subject, $text, $userID, $username) {
		$hash = StringUtil::getHash(serialize($recipients) . serialize($blindCopies) . $subject . $text . $userID . $username);
		$sql = "SELECT	pmID
			FROM 	wcf".WCF_N."_pm_hash
			WHERE 	messagehash = '".$hash."'";
		$result = WCF::getDB()->getFirstRow($sql);
		if (isset($result['pmID'])) return $result['pmID'];
		return false;
	}
	
	/**
	 * Creates a new private message with the given parameters.
	 */
	public static function create($draft, $recipients, $blindCopies, $subject, $text, $userID, $username, $options = array(), $attachments = null, $parentPmID = 0) {
		$hash = StringUtil::getHash(serialize($recipients) . serialize($blindCopies) . $subject . $text . $userID . $username);
		
		// get number of attachments
		$attachmentsAmount = $attachments != null ? count($attachments->getAttachments()) : 0;
		
		// save message
		$pmID = self::insert($subject, $text, array(
			'parentPmID' => $parentPmID,
			'userID' => $userID,
			'username' => $username,
			'time' => TIME_NOW,
			'enableSmilies' => (isset($options['enableSmilies']) ? $options['enableSmilies'] : 1),
			'enableHtml' => (isset($options['enableHtml']) ? $options['enableHtml'] : 0),
			'enableBBCodes' => (isset($options['enableBBCodes']) ? $options['enableBBCodes'] : 1),
			'showSignature' => (isset($options['showSignature']) ? $options['showSignature'] : 1),
			'saveInOutbox' => ($draft ? 0 : 1),
			'isDraft' => ($draft ? 1 : 0),
			'attachments' => $attachmentsAmount
		));
		
		// save hash
		$sql = "REPLACE INTO	wcf".WCF_N."_pm_hash
					(pmID, messageHash, time)
			VALUES		(".$pmID.", '".$hash."', ".TIME_NOW.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// update parent pm id
		if ($parentPmID == 0) {
			$sql = "UPDATE	wcf".WCF_N."_pm
				SET	parentPmID = pmID
				WHERE	pmID = ".$pmID;
			WCF::getDB()->sendQuery($sql);
		}
		
		// save recipients
		$recipientIDs = '';
		$inserts = '';
		foreach ($recipients as $recipient) {
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$pmID.", ".$recipient['userID'].", '".escapeString($recipient['username'])."'".($draft ? ', 2' : ', 0').", 0)";
			if (!empty($recipientIDs)) $recipientIDs .= ',';
			$recipientIDs .= $recipient['userID'];
		}
		
		// save blind copy recipients
		foreach ($blindCopies as $recipient) {
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$pmID.", ".$recipient['userID'].", '".escapeString($recipient['username'])."'".($draft ? ', 2' : ', 0').", 1)";
			if (!empty($recipientIDs)) $recipientIDs .= ',';
			$recipientIDs .= $recipient['userID'];
		}
		
		if (!empty($inserts)) {
			$sql = "INSERT IGNORE INTO 	wcf".WCF_N."_pm_to_user
							(pmID, recipientID, recipient, isDeleted, isBlindCopy)
				VALUES			".$inserts;
			WCF::getDB()->sendQuery($sql);
		}
				
		// update attachments
		if ($attachments != null) {
			$attachments->updateContainerID($pmID);
			$attachments->findEmbeddedAttachments($text);
		}
		
		// update message count
		if (!empty($recipientIDs)) self::updateUnreadMessageCount($recipientIDs);
		self::updateTotalMessageCount((!empty($recipientIDs) ? $recipientIDs.',' : '').$userID);
		// reset session data
		$userIDArray = (!empty($recipientIDs) ? explode(',', $recipientIDs) : array());
		$userIDArray[] = $userID;
		Session::resetSessions($userIDArray, true, false);
		
		return new PMEditor($pmID);
	}
	
	/**
	 * Creates the pm row in database table.
	 *
	 * @param 	string 		$subject
	 * @param 	string		$message
	 * @param 	array		$additionalFields
	 * @return	integer		new pm id
	 */
	public static function insert($subject, $message, $additionalFields = array()) { 
		$keys = $values = '';
		foreach ($additionalFields as $key => $value) {
			$keys .= ','.$key;
			$values .= ",'".escapeString($value)."'";
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_pm
					(subject, message
					".$keys.")
			VALUES		('".escapeString($subject)."', '".escapeString($message)."'
					".$values.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Creates a preview of a private message.
	 */
	public static function createPreview($subject, $text, $enableSmilies = 1, $enableHtml = 0, $enableBBCodes = 1) {
		$row = array(
			'pmID' => 0,
			'subject' => $subject,
			'message' => $text,
			'enableSmilies' => $enableSmilies,
			'enableHtml' => $enableHtml,
			'enableBBCodes' => $enableBBCodes,
			'messagePreview' => true,
			'username' => ''
		);

		$pm = new ViewablePM(null, $row);
		return $pm->getFormattedMessage();
	}
	
	/**
	 * Marks this private message as replied.
	 */
	public function markAsReplied() {
		$sql = "UPDATE	wcf".WCF_N."_pm_to_user
			SET 	isReplied = 1
			WHERE 	pmID = ".$this->pmID."
				AND recipientID = ".WCF::getUser()->userID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Marks this private message as forwarded.
	 */
	public function markAsForwarded() {
		$sql = "UPDATE	wcf".WCF_N."_pm_to_user
			SET 	isForwarded = 1
			WHERE 	pmID = ".$this->pmID."
				AND recipientID = ".WCF::getUser()->userID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Applies the rules of all recipients of this message.
	 */
	public function applyRules() {
		// get conditions
		require_once(WCF_DIR.'lib/data/message/pm/rule/PMRuleCondition.class.php');
		$ruleConditions = array();
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_pm_rule_condition
			WHERE	ruleID IN (
					SELECT	ruleID
					FROM	wcf".WCF_N."_pm_rule
					WHERE	userID IN (
							SELECT	recipientID
							FROM	wcf".WCF_N."_pm_to_user
							WHERE	pmID = ".$this->pmID."
						)
						AND disabled = 0
				)";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($ruleConditions[$row['ruleID']])) $ruleConditions[$row['ruleID']] = array();
			$ruleConditions[$row['ruleID']][] = new PMRuleCondition(null, $row);
		}

		// get rules
		require_once(WCF_DIR.'lib/data/message/pm/rule/PMRule.class.php');
		$rules = array();
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_pm_rule
			WHERE	userID IN (
					SELECT	recipientID
					FROM	wcf".WCF_N."_pm_to_user
					WHERE	pmID = ".$this->pmID."
				)
				AND disabled = 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['conditions'] = (isset($ruleConditions[$row['ruleID']]) ? $ruleConditions[$row['ruleID']] : array());
			$rules[$row['userID']][$row['ruleID']] = new PMRule(null, $row);
		}
		unset($ruleConditions);
		
		// get users
		require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');
		$userIDs = implode(',', array_keys($rules));
		$recipients = array();
		if (!empty($userIDs)) {
			$sql = "SELECT		pm_to_user.*, user_table.*,
						white_list.userID AS buddy
				FROM		wcf".WCF_N."_pm_to_user pm_to_user
				LEFT JOIN	wcf".WCF_N."_user user_table
				ON		(user_table.userID = pm_to_user.recipientID)
				LEFT JOIN 	wcf".WCF_N."_user_whitelist white_list
				ON 		(white_list.userID = user_table.userID AND white_list.whiteUserID = ".$this->userID.")
				WHERE		pm_to_user.recipientID IN (".$userIDs.")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$recipients[$row['userID']] = new UserProfile(null, $row);
			}
		}
		
		// apply rules
		foreach ($recipients as $recipient) {
			if (isset($rules[$recipient->userID])) {
				foreach ($rules[$recipient->userID] as $rule) {
					$rule->apply($this, $recipient);
				}
			}
		}
	}
	
	/**
	 * Sends e-mail notifications to all given recipients.
	 * 
	 * @param	array<User>
	 */
	public function sendNotifications($recipients) {
		require_once(WCF_DIR.'lib/data/mail/Mail.class.php');
		require_once(WCF_DIR.'lib/system/language/Language.class.php');
		
		// get attachments
		if ($this->attachments > 0) {
			require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentList.class.php');
			require_once(WCF_DIR.'lib/data/message/bbcode/AttachmentBBCode.class.php');
			$attachmentList = new MessageAttachmentList($this->pmID, 'pm');
			AttachmentBBCode::setAttachments($attachmentList->getSortedAttachments());
		}
		
		// get parsed text
		require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
		$parser = MessageParser::getInstance();
		$parser->setOutputType('text/plain');
		$parsedText = $parser->parse($this->message, $this->enableSmilies, $this->enableHtml, $this->enableBBCodes, false);
		
		// send notifications
		$languages = array();
		$languages[0] = $languages[WCF::getLanguage()->getLanguageID()] = WCF::getLanguage();
		foreach ($recipients as $recipient) {
			// no notification for the author
			if ($recipient->userID == $this->userID) {
				continue;
			}
			
			// get language
			if (!isset($languages[$recipient->languageID])) {
				$languages[$recipient->languageID] = Language::getLanguageObjectByID($recipient->languageID);
			}
		
			// enable language
			$languages[$recipient->languageID]->setLocale();

			// send mail
			$subjectData = array(
				'$author' => $this->username,
				'PAGE_TITLE' => $languages[$recipient->languageID]->get(PAGE_TITLE)
			);
			$messageData = array(
				'PAGE_TITLE' => $languages[$recipient->languageID]->get(PAGE_TITLE),
				'$recipient' => $recipient->username,
				'$author' => $this->username,
				'PAGE_URL' => PAGE_URL,
				'$subject' => $this->subject,
				'$text' => $parsedText);
			$mail = new Mail(array($recipient->username => $recipient->email), $languages[$recipient->languageID]->get('wcf.pm.notification.subject', $subjectData), $languages[$recipient->languageID]->get('wcf.pm.notification.mail', $messageData));
			$mail->send();
		}
		
		// enable user language
		WCF::getLanguage()->setLocale();
	}
}
?>