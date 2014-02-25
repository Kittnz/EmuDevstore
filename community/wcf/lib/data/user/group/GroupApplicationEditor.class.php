<?php
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * GroupApplicationEditor creates, edits or deletes group applications.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	data.user.group
 * @category 	Community Framework (commercial)
 */
class GroupApplicationEditor extends DatabaseObject {
	const STATUS_UNFINISHED = 0;
	const STATUS_IN_PROGRESS = 1;
	const STATUS_DECLINED = 2;
	const STATUS_ACCEPTED = 3;
	
	/**
	 * Creates a new GroupApplicationEditor object.
	 * 
	 * @param	integer		$applicationID
	 */
	public function __construct($applicationID) {
		$sql = "SELECT		usergroup.*, application.*, user.username, group_leader.username AS groupLeader
			FROM		wcf".WCF_N."_group_application application
			LEFT JOIN	wcf".WCF_N."_group usergroup
			ON		(usergroup.groupID = application.groupID)
			LEFT JOIN	wcf".WCF_N."_user user
			ON		(user.userID = application.userID)
			LEFT JOIN	wcf".WCF_N."_user group_leader
			ON		(group_leader.userID = application.groupLeaderID)
			WHERE		application.applicationID = ".$applicationID;
		$row = WCF::getDB()->getFirstRow($sql);
	
		parent::__construct($row);
	}
	
	/**
	 * Updates this application.
	 * 
	 * @param	string		$reason
	 * @param	integer		$enableNotification
	 */
	public function update($reason = null, $enableNotification = 0) {
		$sql = "UPDATE	wcf".WCF_N."_group_application
			SET	enableNotification = ".$enableNotification.
				($reason !== null ? (", reason = '".escapeString($reason)."'") : '')."
			WHERE	applicationID = ".$this->applicationID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Updates this application by leader.
	 * 
	 * @param	integer		$newStatus
	 * @param	string		$reply
	 */
	public function updateByLeader($newStatus = 0, $reply = '', $groupLeaderID = 0) {
		// update
		$sql = "UPDATE	wcf".WCF_N."_group_application
			SET	applicationStatus = ".$newStatus.",
				reply = '".escapeString($reply)."',
				groupLeaderID = ".$groupLeaderID."
			WHERE	applicationID = ".$this->applicationID;
		WCF::getDB()->sendQuery($sql);
		
		// set data
		$this->data['reply'] = $reply;
		
		// set status
		// declined
		if ($newStatus == 2 && $this->applicationStatus != 2) {
			// remove user from group
			require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
			$user = new UserEditor($this->userID);
			$user->removeFromGroup($this->groupID);
			$user->resetSession();
			
			// send e-mail notification
			if ($this->enableNotification) {
				$this->sendNotification($user, 'declined');
			}
		}
		
		// accepted
		if ($newStatus == 3 && $this->applicationStatus != 3) {
			// add user to group
			require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
			$user = new UserEditor($this->userID);
			$user->addToGroup($this->groupID);
			$user->resetSession();
			
			if ($this->enableNotification) {
				$this->sendNotification($user, 'accepted');
			}
		}
	}
	
	/**
	 * Sends the e-mail notifications.
	 * 
	 * @param	User		$user
	 * @param	string		$type		notification type
	 */
	protected function sendNotification(User $user, $type) {
		require_once(WCF_DIR.'lib/data/mail/Mail.class.php');
		
		// get user language
		if ($user->languageID == WCF::getLanguage()->getLanguageID()) {
			$userLanguage = WCF::getLanguage();
		}
		else {
			$userLanguage = new Language($user->languageID);
			// enable language
			$userLanguage->setLocale();
		}
		
		// get group name
		$group = new Group($this->groupID);
		
		// send mail
		$data = array(
			'PAGE_TITLE' => $userLanguage->get(PAGE_TITLE),
			'PAGE_URL' => PAGE_URL,
			'$username' => $user->username,
			'$reply' => $this->reply,
			'$groupName' => $userLanguage->get($group->groupName));
		$mail = new Mail(	array($user->username => $user->email),
					$userLanguage->get('wcf.user.userGroups.application.'.$type.'.notification.subject', array('$groupName' => $userLanguage->get($group->groupName))),
					$userLanguage->get('wcf.user.userGroups.application.'.$type.'.notification.mail', $data));
		$mail->send();

		// enable system language
		WCF::getLanguage()->setLocale();
	}
	
	/**
	 * Deletes this application.
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_group_application
			WHERE		applicationID = ".$this->applicationID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Creates a new application.
	 * 
	 * @param	integer		$userID
	 * @param	integer		$groupID
	 * @param	string		$reason
	 * @param	integer		$enableNotification
	 * @return	integer		new application id
	 */
	public static function create($userID, $groupID, $reason, $enableNotification = 0) {
		$sql = "INSERT INTO	wcf".WCF_N."_group_application
					(userID, groupID, applicationTime, reason,
					enableNotification)
			VALUES		(".$userID.", ".$groupID.", ".TIME_NOW.", '".escapeString($reason)."',
					".$enableNotification.")";
		WCF::getDB()->sendQuery($sql);
		$applicationID = WCF::getDB()->getInsertID();
		
		return $applicationID;
	}
	
	/**
	 * Returns an existing application for the given user id and group id.
	 * 
	 * @param	integer				$userID
	 * @param	integer				$groupID
	 * @return	GroupApplicationEditor
	 */
	public static function getApplication($userID, $groupID) {
		$sql = "SELECT	applicationID
			FROM	wcf".WCF_N."_group_application
			WHERE	userID = ".$userID."
				AND groupID = ".$groupID;
		$result = WCF::getDB()->sendQuery($sql);
		if (WCF::getDB()->countRows($result) > 0) {
			$row = WCF::getDB()->fetchArray($result);
			return new GroupApplicationEditor($row['applicationID']);
		}
		
		return null;
	}
	
	/**
	 * Checks the permissions of the given user to edit applications.
	 * 
	 * @return	boolean 
	 */
	public static function isGroupLeader(User $user, $groupID) {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_group_leader
			WHERE	groupID = ".$groupID."
				AND (
					leaderUserID = ".$user->userID."
					OR leaderGroupID IN (".implode(',', $user->getGroupIDs()).")
				)";
		$row = WCF::getDB()->getFirstRow($sql);
		return intval($row['count'] != 0);
	}
	
	/**
	 * Sends e-mail notifications to all group leaders.
	 */
	public function sendLeaderNotification() {
		// send notifications
		$languages = array();
		$languages[WCF::getLanguage()->getLanguageID()] = WCF::getLanguage();
		$languages[0] = WCF::getLanguage();
		require_once(WCF_DIR.'lib/data/mail/Mail.class.php');

		// get group leaders
		$sql = "SELECT		user_option.*, user_table.*
			FROM		wcf".WCF_N."_user user_table
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option
			ON		(user_option.userID = user_table.userID)
			WHERE 		(user_table.userID IN (
						SELECT	leaderUserID
						FROM	wcf".WCF_N."_group_leader
						WHERE	groupID = ".$this->groupID."
							AND leaderUserID <> 0
					) 
					OR user_table.userID IN (
						SELECT		user_to_groups.userID
						FROM		wcf".WCF_N."_group_leader group_leader
						LEFT JOIN	wcf".WCF_N."_user_to_groups user_to_groups
						ON		(user_to_groups.groupID = group_leader.leaderGroupID)
						WHERE		user_to_groups.groupID = ".$this->groupID."
								AND leaderGroupID <> 0
					))
					AND user_option.userOption".User::getUserOptionID('enableGroupApplicationEmailNotification')." = 1";
		$result = WCF::getDB()->sendQuery($sql, 100);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$recipient = new User(null, $row);
			
			// get language
			if (!isset($languages[$recipient->languageID])) {
				$languages[$recipient->languageID] = new Language($recipient->languageID);	
			}
			
			// enable language
			$languages[$recipient->languageID]->setLocale();
			
			// send mail
			$data = array(
				'PAGE_TITLE' => $languages[$recipient->languageID]->get(PAGE_TITLE),
				'PAGE_URL' => PAGE_URL,
				'$applicationID' => $this->applicationID,
				'$recipient' => $recipient->username,
				'$applicant' => $this->username,
				'$reason' => $this->reason,
				'$groupName' => $languages[$recipient->languageID]->get($this->groupName));
			$mail = new Mail(	array($recipient->username => $recipient->email),
						$languages[$recipient->languageID]->get('wcf.user.userGroups.application.leader.notification.subject', array('PAGE_TITLE' => $languages[$recipient->languageID]->get(PAGE_TITLE))),
						$languages[$recipient->languageID]->get('wcf.user.userGroups.application.leader.notification.mail', $data));
			$mail->send();
		}
		
		// enable user language
		WCF::getLanguage()->setLocale();
	}
}
?>