<?php
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/data/user/group/GroupApplicationEditor.class.php');
require_once(WCF_DIR.'lib/page/UserGroupMembersListPage.class.php');

/**
 * Shows the user group application form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	form
 * @category 	Community Framework (commercial)
 */
class UserGroupAdministrateForm extends AbstractForm {
	public $groupID = 0;
	public $group;
	public $templateName = 'userGroupAdministrate';
	public $usernames = '';
	public $membersList;
	public $users;
	public $pmSuccess = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['groupID'])) $this->groupID = intval($_REQUEST['groupID']);
		$this->group = new Group($this->groupID);
		if (!$this->group->groupID) {
			throw new IllegalLinkException();
		}
		if (isset($_REQUEST['pmSuccess'])) $this->pmSuccess = intval($_REQUEST['pmSuccess']);
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['usernames'])) $this->usernames = StringUtil::trim($_POST['usernames']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->usernames)) {
			throw new UserInputException('usernames');
		}
		
		// explode multiple usernames to an array
		$usernameArray = explode(',', $this->usernames);
		$error = array();
		
		// loop through users
		foreach ($usernameArray as $username) {
			$username = StringUtil::trim($username);
			if (empty($username)) continue;
			
			try {
				// get user
				$user = new UserEditor(null, null, $username);
				if (!$user->userID) {
					throw new UserInputException('username', 'notFound');
				}
				
				// no error
				$this->users[] = $user;
			}
			catch (UserInputException $e) {
				$error[] = array('type' => $e->getType(), 'username' => $username);
			}
		}
		
		if (count($error)) {
			throw new UserInputException('usernames', $error);
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save
		$userIDArray = array();
		foreach ($this->users as $user) {
			$userIDArray[] = $user->userID;
			$user->addToGroup($this->groupID);
		}
		
		// reset sessions
		if (count($userIDArray)) {
			Session::resetSessions($userIDArray);
		}
		$this->saved();
		
		// reset value
		$this->usernames = '';
		
		// show success message
		WCF::getTPL()->assign(array(
			'success' => true,
			'users' => $this->users
		));
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function readData() {
		parent::readData();
		
		// get members list
		$this->membersList = new UserGroupMembersListPage($this->groupID);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'usernames' => $this->usernames,
			'groupID' => $this->groupID,
			'group' => $this->group,
			'pmSuccess' => $this->pmSuccess
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// check permission
		if (!WCF::getUser()->userID || !GroupApplicationEditor::isGroupLeader(WCF::getUser(), $this->groupID)) {
			throw new PermissionDeniedException();
		}
		
		if (MODULE_MODERATED_USER_GROUP != 1) {
			throw new IllegalLinkException();
		}
		
		// set active tab
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.userGroups');
		
		parent::show();
	}
}
?>