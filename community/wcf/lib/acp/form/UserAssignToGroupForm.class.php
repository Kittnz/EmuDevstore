<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');

/**
 * Shows the assign user to group form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UserAssignToGroupForm extends ACPForm {
	public $templateName = 'userAssignToGroup';
	public $activeMenuItem = 'wcf.acp.menu.link.user.management';
	public $neededPermissions = 'admin.user.canEditUser';
	
	public $userIDs = '';
	public $groupIDs = array();
	public $users = array();
	public $groups = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['userIDs'])) $this->userIDs = implode(',', ArrayUtil::toIntegerArray(explode(',', $_POST['userIDs'])));
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->userIDs)) throw new IllegalLinkException();
		
		// groups
		/*if (!count($this->groupIDs)) {
			throw new UserInputException('groupIDs');
		}*/
		
		foreach ($this->groupIDs as $groupID) {
			$group = new Group($groupID);
			if (!$group->groupID) throw new UserInputException('groupIDs');
			if (!$group->isAccessible()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		$sql = "SELECT		user.*,
					GROUP_CONCAT(groupID SEPARATOR ',') AS groupIDs
			FROM		wcf".WCF_N."_user user
			LEFT JOIN	wcf".WCF_N."_user_to_groups groups
			ON		(groups.userID = user.userID)
			WHERE		user.userID IN (".$this->userIDs.")
			GROUP BY	user.userID";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!Group::isAccessibleGroup(explode(',', $row['groupIDs']))) {
				throw new PermissionDeniedException();
			}
			
			$user = new UserEditor(null, $row);
			$user->addToGroups($this->groupIDs, false, false);
		}
		
		UserEditor::unmarkAll();
		Session::resetSessions(explode(',', $this->userIDs));
		$this->saved();
		WCF::getTPL()->assign('message', 'wcf.acp.user.assignToGroup.success');
		WCF::getTPL()->display('success');
		exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// get marked user ids
			$markedUsers = WCF::getSession()->getVar('markedUsers');
			if (is_array($markedUsers)) $this->userIDs = implode(',', $markedUsers);
			if (empty($this->userIDs)) throw new IllegalLinkException();
		}
		
		$this->users = User::getUsers($this->userIDs);
		$this->readGroups();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'users' => $this->users,
			'userIDs' => $this->userIDs,
			'groupIDs' => $this->groupIDs,
			'groups' => $this->groups
		));
	}
	
	/**
	 * Get a list of available groups.
	 */
	protected function readGroups() {
		$this->groups = Group::getAccessibleGroups(array(), array(Group::GUESTS, Group::EVERYONE, Group::USERS));
	}
}
?>