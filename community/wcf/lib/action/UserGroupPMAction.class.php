<?php
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/user/group/GroupApplicationEditor.class.php');

/**
 * Sends a private message to group members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class UserGroupPMAction extends AbstractSecureAction {
	public $groupID = 0;
	public $group;
	public $subject = '';
	public $text = '';
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!MODULE_MODERATED_USER_GROUP || !MODULE_PM) {
			throw new IllegalLinkException();
		}
		
		if (isset($_POST['groupID'])) $this->groupID = intval($_POST['groupID']);
		$this->group = new Group($this->groupID);
		if (!$this->group->groupID) {
			throw new IllegalLinkException();
		}
		// check permission
		if (!GroupApplicationEditor::isGroupLeader(WCF::getUser(), $this->groupID)) {
			throw new PermissionDeniedException();
		}
		if (isset($_POST['subject'])) $this->subject = StringUtil::trim($_POST['subject']);
		if (isset($_POST['text'])) $this->text = StringUtil::trim($_POST['text']);
		if (empty($this->subject) || empty($this->text)) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// save pm
		$sql = "INSERT INTO	wcf".WCF_N."_pm
					(userID, username, subject, message, time)
			VALUES		(".WCF::getUser()->userID.", '".escapeString(WCF::getUser()->username)."', '".escapeString($this->subject)."', '".escapeString($this->text)."', ".TIME_NOW.")";
		WCF::getDB()->sendQuery($sql);
		$pmID = WCF::getDB()->getInsertID("wcf".WCF_N."_pm", 'pmID');
		
		// save recipients
		$sql = "INSERT INTO	wcf".WCF_N."_pm_to_user
					(pmID, recipientID, recipient, isBlindCopy)
			SELECT		".$pmID.", user_to_groups.userID, user_table.username, 1
			FROM		wcf".WCF_N."_user_to_groups user_to_groups
			LEFT JOIN	wcf".WCF_N."_user user_table
			ON		(user_table.userID = user_to_groups.userID)
			WHERE		user_to_groups.groupID = ".$this->groupID;
		WCF::getDB()->sendQuery($sql);
			
		// update counters
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	pmUnreadCount = pmUnreadCount + 1,
				pmOutstandingNotifications = pmOutstandingNotifications + 1
			WHERE	userID IN (
					SELECT	userID
					FROM	wcf".WCF_N."_user_to_groups
					WHERE	groupID = ".$this->groupID."
				)";
		WCF::getDB()->sendQuery($sql);
			
		// reset sessions
		Session::resetSessions(array(), true, false);
		$this->executed();
		
		HeaderUtil::redirect('index.php?form=UserGroupAdministrate&groupID='.$this->groupID.'&pmSuccess=1'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>