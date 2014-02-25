<?php
// wcf imports
require_once(WCF_DIR.'lib/form/AbstractUserListEditForm.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');

/**
 * Shows the white list edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	form
 * @category 	Community Framework
 */
class WhiteListEditForm extends AbstractUserListEditForm {
	public $templateName = 'whiteListEdit';
	
	public $invitingMembers = array();
	public $invitedMembers = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		AbstractSecureForm::readParameters();
		
		if (isset($_GET['remove'])) {
			$user = new User(intval($_GET['remove']));
			if (!$user->userID) {
				throw new IllegalLinkException();
			}
			
			// delete user
			$sql = "DELETE FROM	wcf".WCF_N."_user_whitelist
				WHERE		(userID = ".WCF::getUser()->userID."
						AND whiteUserID = ".$user->userID.") OR
						(userID = ".$user->userID."
						AND whiteUserID = ".WCF::getUser()->userID.")";
			WCF::getDB()->sendQuery($sql);
			
			// reset session
			Session::resetSessions(array(WCF::getUser()->userID, $user->userID), true, false);
			
			// show success message
			WCF::getTPL()->assign(array(
				'success' => 'remove',
				'user' => $user
			));
		}
		else if (isset($_GET['accept'])) {
			$user = new User(intval($_GET['accept']));
			if (!$user->userID) {
				throw new IllegalLinkException();
			}
			
			// validate id
			$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_user_whitelist
				WHERE	userID = ".$user->userID."
					AND whiteUserID = ".WCF::getUser()->userID."
					AND confirmed = 0";
			$row = WCF::getDB()->getFirstRow($sql);
			if (!$row['count']) {
				throw new IllegalLinkException();
			}
			
			// insert user
			$sql = "REPLACE INTO	wcf".WCF_N."_user_whitelist
						(userID, whiteUserID, confirmed, time)
				VALUES		(".WCF::getUser()->userID.", ".$user->userID.", 1, ".TIME_NOW."),
						(".$user->userID.", ".WCF::getUser()->userID.", 1, ".TIME_NOW.")";
			WCF::getDB()->sendQuery($sql);
			
			// delete blacklist entries if necessary
			$sql = "DELETE FROM	wcf".WCF_N."_user_blacklist
				WHERE		(userID = ".WCF::getUser()->userID."
						AND blackUserID = ".$user->userID.") OR
						(userID = ".$user->userID."
						AND blackUserID = ".WCF::getUser()->userID.")";
			WCF::getDB()->sendQuery($sql);
			
			// reset session
			Session::resetSessions(array(WCF::getUser()->userID, $user->userID), true, false);
			
			// show success message
			WCF::getTPL()->assign(array(
				'success' => 'accept',
				'user' => $user
			));
		}
		else if (isset($_GET['decline'])) {
			$user = new User(intval($_GET['decline']));
			if (!$user->userID) {
				throw new IllegalLinkException();
			}
			
			// delete user
			$sql = "DELETE FROM	wcf".WCF_N."_user_whitelist
				WHERE		(userID = ".$user->userID."
						AND whiteUserID = ".WCF::getUser()->userID.")";
			WCF::getDB()->sendQuery($sql);
			
			// reset session
			Session::resetSessions(array(WCF::getUser()->userID, $user->userID), true, false);
			
			// show success message
			WCF::getTPL()->assign(array(
				'success' => 'decline',
				'user' => $user
			));
		}
		else if (isset($_GET['cancel'])) {
			$user = new User(intval($_GET['cancel']));
			if (!$user->userID) {
				throw new IllegalLinkException();
			}
			
			// delete user
			$sql = "DELETE FROM	wcf".WCF_N."_user_whitelist
				WHERE		(userID = ".WCF::getUser()->userID."
						AND whiteUserID = ".$user->userID.")";
			WCF::getDB()->sendQuery($sql);
			
			// reset session
			Session::resetSessions(array(WCF::getUser()->userID, $user->userID), true, false);
			
			// show success message
			WCF::getTPL()->assign(array(
				'success' => 'cancel',
				'user' => $user
			));
		}
		else if (isset($_GET['add'])) {
			$user = new User(intval($_GET['add']));
			if (!$user->userID) {
				throw new IllegalLinkException();
			}
			
			$this->usernames = $user->username;
			$this->submit();
		}
	}
	
	/**
	 * @see WhiteListEditForm::validateUser()
	 */
	protected function validateUser(UserSession $user) {
		parent::validateUser($user);
		
		if (!$user->allowFriendshipOfferings) {
			throw new UserInputException('username', 'userDoesNotAllowFriendshipOfferings');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// get invitings
		$confirmedInvitings = $invitings = array();
		$sql = "SELECT	userID
			FROM	wcf".WCF_N."_user_whitelist
			WHERE	whiteUserID = ".WCF::getUser()->userID."
				AND confirmed = 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$invitings[$row['userID']] = $row['userID'];
		}
		
		// save user
		$inserts = '';
		$userIDArray = $addedUsers = $invitedUsers = array();
		foreach ($this->users as $user) {
			$userIDArray[] = $user->userID;
			$confirmed = 0;
			if (isset($invitings[$user->userID])) {
				$confirmed = 1;
				$confirmedInvitings[] = $user->userID;
				$addedUsers[] = $user;
			}
			else {
				$invitedUsers[] = $user;
			}
			
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".WCF::getUser()->userID.", ".$user->userID.", ".$confirmed.", ".($confirmed == 1 ? TIME_NOW : 0).")";
		}
		
		if (!empty($inserts)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_whitelist
							(userID, whiteUserID, confirmed, time)
				VALUES			".$inserts;
			WCF::getDB()->sendQuery($sql);
		}
		
		if (count($confirmedInvitings) > 0) {
			$sql = "UPDATE	wcf".WCF_N."_user_whitelist
				SET	confirmed = 1,
					time = ".TIME_NOW."
				WHERE	userID IN (".implode(',', $confirmedInvitings).")
					AND whiteUserID = ".WCF::getUser()->userID;
			WCF::getDB()->sendQuery($sql);
		}
		
		if (count($userIDArray)) {
			// delete blacklist entries if necessary
			$sql = "DELETE FROM	wcf".WCF_N."_user_blacklist
				WHERE		userID = ".WCF::getUser()->userID."
						AND blackUserID IN (".implode(',', $userIDArray).")";
			WCF::getDB()->sendQuery($sql);
		}
		
		// reset session
		$userIDArray[] = WCF::getUser()->userID;
		Session::resetSessions($userIDArray, true, false);
		$this->saved();
		
		// reset field
		$this->usernames = '';
		
		// show success message
		WCF::getTPL()->assign(array(
			'success' => 'add',
			'addedUsers' => $addedUsers,
			'invitedUsers' => $invitedUsers
		));
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readInvitedMembers();
		$this->readInvitingMembers();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'invitingMembers' => $this->invitingMembers,
			'invitedMembers' => $this->invitedMembers
		));
	}
	
	/**
	 * Gets a list of all members in this white list.
	 */
	protected function readMembers() {
		// get members
		$sql = "SELECT		user_option.userOption".User::getUserOptionID('invisible').", user.userID, user.username, user.lastActivityTime
			FROM		wcf".WCF_N."_user_whitelist whitelist
			LEFT JOIN	wcf".WCF_N."_user user
			ON		(user.userID = whitelist.whiteUserID)
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option
			ON 		(user_option.userID = user.userID) 
			WHERE 		whitelist.userID = ".WCF::getUser()->userID."
					AND whitelist.confirmed = 1
					AND user.userID IS NOT NULL
			ORDER BY 	user.username";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->members[] = new UserProfile(null, $row);
		}
	}
	
	/**
	 * Gets a list of all members in this white list.
	 */
	protected function readInvitedMembers() {
		// get members
		$sql = "SELECT		user_option.userOption".User::getUserOptionID('invisible').", user.userID, user.username, user.lastActivityTime
			FROM		wcf".WCF_N."_user_whitelist whitelist
			LEFT JOIN	wcf".WCF_N."_user user
			ON		(user.userID = whitelist.whiteUserID)
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option
			ON 		(user_option.userID = user.userID) 
			WHERE 		whitelist.userID = ".WCF::getUser()->userID."
					AND whitelist.confirmed = 0
					AND user.userID IS NOT NULL
			ORDER BY 	user.username";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->invitedMembers[] = new UserProfile(null, $row);
		}
	}
	
	/**
	 * Gets a list of all members in this white list.
	 */
	protected function readInvitingMembers() {
		// get members
		$sql = "SELECT		user_option.userOption".User::getUserOptionID('invisible').", user.userID, user.username, user.lastActivityTime
			FROM		wcf".WCF_N."_user_whitelist whitelist
			LEFT JOIN	wcf".WCF_N."_user user
			ON		(user.userID = whitelist.userID)
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option
			ON 		(user_option.userID = user.userID) 
			WHERE 		whitelist.whiteUserID = ".WCF::getUser()->userID."
					AND whitelist.confirmed = 0
					AND user.userID IS NOT NULL
			ORDER BY 	user.username";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->invitingMembers[] = new UserProfile(null, $row);
		}
	}
}
?>