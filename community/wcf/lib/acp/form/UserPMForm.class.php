<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');

/**
 * Shows the user pm form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class UserPMForm extends ACPForm {
	public $templateName = 'userPM';
	public $neededPermissions = 'admin.user.canPMUser';
	
	public $userIDs = '';
	public $groupIDs = array();
	public $subject = '';
	public $text = '';
	public $users = array();
	public $groups = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->activeMenuItem = ($this->action == 'all' ? 'wcf.acp.menu.link.user.pm' : ($this->action == 'group' ? 'wcf.acp.menu.link.group.pm' : 'wcf.acp.menu.link.user.management'));
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['userIDs'])) $this->userIDs = implode(',', ArrayUtil::toIntegerArray(explode(',', $_POST['userIDs'])));
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
		if (isset($_POST['subject'])) $this->subject = StringUtil::trim($_POST['subject']);
		if (isset($_POST['text'])) $this->text = StringUtil::trim($_POST['text']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->action == 'group') {
			if (!count($this->groupIDs)) {
				throw new UserInputException('groupIDs');
			}
		}
		if ($this->action == '') {
			if (empty($this->userIDs)) throw new IllegalLinkException();
		}
		
		if (empty($this->subject)) {
			throw new UserInputException('subject');
		}
		
		if (empty($this->text)) {
			throw new UserInputException('text');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save pm
		$sql = "INSERT INTO	wcf".WCF_N."_pm
					(userID, username, subject, message, time)
			VALUES		(".WCF::getUser()->userID.", '".escapeString(WCF::getUser()->username)."', '".escapeString($this->subject)."', '".escapeString($this->text)."', ".TIME_NOW.")";
		WCF::getDB()->sendQuery($sql);
		$pmID = WCF::getDB()->getInsertID("wcf".WCF_N."_pm", 'pmID');
		
		// save recipients
		if ($this->action == 'group') {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_pm_to_user
							(pmID, recipientID, recipient, isBlindCopy)
				SELECT			".$pmID.", user_to_groups.userID, user_table.username, 1
				FROM			wcf".WCF_N."_user_to_groups user_to_groups
				LEFT JOIN		wcf".WCF_N."_user user_table
				ON			(user_table.userID = user_to_groups.userID)
				WHERE			user_to_groups.groupID IN (".implode(',', $this->groupIDs).")";
			WCF::getDB()->sendQuery($sql);
			
			// update counters
			$sql = "UPDATE	wcf".WCF_N."_user
				SET	pmUnreadCount = pmUnreadCount + 1,
					pmOutstandingNotifications = pmOutstandingNotifications + 1
				WHERE	userID IN (
						SELECT	userID
						FROM	wcf".WCF_N."_user_to_groups
						WHERE	groupID IN (".implode(',', $this->groupIDs).")
					)";
			WCF::getDB()->sendQuery($sql);
			
			// reset sessions
			Session::resetSessions(array(), true, false);
		}
		else if ($this->action == 'all') {
			$sql = "INSERT INTO	wcf".WCF_N."_pm_to_user
						(pmID, recipientID, recipient, isBlindCopy)
				SELECT		".$pmID.", userID, username, 1
				FROM		wcf".WCF_N."_user";
			WCF::getDB()->sendQuery($sql);
			
			// update counters
			$sql = "UPDATE	wcf".WCF_N."_user
				SET	pmUnreadCount = pmUnreadCount + 1,
					pmOutstandingNotifications = pmOutstandingNotifications + 1";
			WCF::getDB()->sendQuery($sql);
			
			// reset sessions
			Session::resetSessions(array(), true, false);
		}
		else {
			$sql = "INSERT INTO	wcf".WCF_N."_pm_to_user
						(pmID, recipientID, recipient, isBlindCopy)
				SELECT		".$pmID.", userID, username, 1
				FROM		wcf".WCF_N."_user
				WHERE		userID IN (".$this->userIDs.")";
			WCF::getDB()->sendQuery($sql);
			
			// update counters
			$sql = "UPDATE	wcf".WCF_N."_user
				SET	pmUnreadCount = pmUnreadCount + 1,
					pmOutstandingNotifications = pmOutstandingNotifications + 1
				WHERE	userID IN (".$this->userIDs.")";
			WCF::getDB()->sendQuery($sql);
			
			// reset sessions
			Session::resetSessions(explode(',', $this->userIDs), true, false);
		}
		$this->saved();
		
		// reset values
		$this->subject = $this->text = '';
		$this->groupIDs = array();
		$this->userIDs = '';
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// get marked user ids
			if (empty($this->action)) {
				$markedUsers = WCF::getSession()->getVar('markedUsers');
				if (is_array($markedUsers)) $this->userIDs = implode(',', $markedUsers);
				if (empty($this->userIDs)) throw new IllegalLinkException();
			}
		}
		
		if (!empty($this->userIDs)) $this->users = User::getUsers($this->userIDs);
		$this->groups = Group::getAccessibleGroups(array(), array(Group::GUESTS, Group::EVERYONE));
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'users' => $this->users,
			'groups' => $this->groups,
			'userIDs' => $this->userIDs,
			'groupIDs' => $this->groupIDs,
			'subject' => $this->subject,
			'text' => $this->text
		));
	}
}
?>