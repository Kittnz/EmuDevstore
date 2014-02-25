<?php
// wcf imports
require_once(WCF_DIR.'lib/form/AbstractSecureForm.class.php');
require_once(WCF_DIR.'lib/system/session/UserSession.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');

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
abstract class AbstractUserListEditForm extends AbstractSecureForm {
	public $listType = 'white';
	public $usernames = '';
	public $users = array();
	public $members = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_GET['remove'])) {
			$user = new User(intval($_GET['remove']));
			if (!$user->userID) {
				throw new IllegalLinkException();
			}
			
			// delete user
			$sql = "DELETE FROM	wcf".WCF_N."_user_".$this->listType."list
				WHERE		userID = ".WCF::getUser()->userID."
						AND ".$this->listType."UserID = ".$user->userID;
			WCF::getDB()->sendQuery($sql);
			
			// reset session
			Session::resetSessions(WCF::getUser()->userID, true, false);
			
			// show success message
			WCF::getTPL()->assign(array(
				'success' => 'remove',
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
		
		if (count($this->users) == 0) {
			if (empty($this->usernames)) {
				throw new UserInputException('usernames');
			}
			
			$this->validateUsers();
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save user
		$inserts = '';
		foreach ($this->users as $user) {
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".WCF::getUser()->userID.", ".$user->userID.")";
		}
		
		if (!empty($inserts)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_".$this->listType."list
							(userID, ".$this->listType."UserID)
				VALUES			".$inserts;
			WCF::getDB()->sendQuery($sql);
		}
		
		// reset session
		Session::resetSessions(WCF::getUser()->userID, true, false);
		$this->saved();
		
		// reset field
		$this->usernames = '';
		
		// show success message
		WCF::getTPL()->assign(array(
			'success' => 'add',
			'users' => $this->users
		));
	}

	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readMembers();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'type' => $this->listType,
			'usernames' => $this->usernames,
			'members' => $this->members
		));
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		// set active tab
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.management.'.$this->listType.'list');
		
		// show form
		parent::show();
	}
	
	/**
	 * Gets a list of all members in this white list.
	 */
	protected function readMembers() {
		// get members
		require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');
		$sql = "SELECT		user_option.userOption".User::getUserOptionID('invisible').", user.userID, user.username, user.lastActivityTime
			FROM		wcf".WCF_N."_user_".$this->listType."list ".$this->listType."list
			LEFT JOIN	wcf".WCF_N."_user user
			ON		(user.userID = ".$this->listType."list.".$this->listType."UserID)
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option
			ON 		(user_option.userID = user.userID) 
			WHERE 		".$this->listType."list.userID = ".WCF::getUser()->userID."
					AND user.userID IS NOT NULL
			ORDER BY 	user.username";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->members[] = new UserProfile(null, $row);
		}
	}
	
	/**
	 * Checks the given usernames.
	 */
	protected function validateUsers() {
		// explode multiple usernames to an array
		$usernameArray = explode(',', $this->usernames);
		$error = array();
		
		// loop through recipients and check their settings
		foreach ($usernameArray as $username) {
			$username = StringUtil::trim($username);
			if (empty($username)) continue;
			
			try {
				// get recipient's profile
				$user = new UserSession(null, null, $username);
				if (!$user->userID) {
					throw new UserInputException('username', 'notFound');
				}
				
				$this->validateUser($user);
				
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
	 * Validates the given user.
	 * 
	 * @param	UserSession	$user
	 */
	protected function validateUser(UserSession $user) {
		if ($user->userID == WCF::getUser()->userID) {
			throw new UserInputException('username', 'canNotAddYourself');
		}
	}
	
	/**
	 * Validates the security token.
	 */
	protected function checkSecurityToken() {
		if (!isset($_REQUEST['t']) || !WCF::getSession()->checkSecurityToken($_REQUEST['t'])) {
			throw new IllegalLinkException();
		}
	}
}
?>