<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');

/**
 * Shows the user merge form.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.form
 * @category 	Burning Board
 */
class UserMergeForm extends ACPForm {
	// system
	public $templateName = 'userMerge';
	public $activeMenuItem = 'wcf.acp.menu.link.user.management';
	public $neededPermissions = 'admin.user.canEditUser';
	
	/**
	 * list of user ids
	 * 
	 * @var	array<integer>
	 */
	public $userIDs = array();
	
	/**
	 * user id
	 * 
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * list of users
	 * 
	 * @var	array<User>
	 */
	public $users = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['userIDs'])) $this->userIDs = ArrayUtil::toIntegerArray(explode(',', $_POST['userIDs']));
		if (isset($_POST['userID'])) $this->userID = intval($_POST['userID']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// user ids
		if (!count($this->userIDs)) {
			throw new IllegalLinkException();
		}
		
		if (count($this->userIDs) < 2) {
			throw new NamedUserException(WCF::getLanguage()->get('wbb.acp.user.merge.error.tooFew'));
		}
		
		// check permission
		$sql = "SELECT	DISTINCT groupID
			FROM	wcf".WCF_N."_user_to_groups
			WHERE	userID IN (".implode(',', $this->userIDs).")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!Group::isAccessibleGroup($row['groupID'])) {
				throw new PermissionDeniedException();
			}
		}
		
		// user id
		if (!$this->userID || !in_array($this->userID, $this->userIDs)) {
			throw new UserInputException('userID');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// merge
		$userIDs = $this->userIDs;
		$key = array_search($this->userID, $userIDs);
		unset($userIDs[$key]);
		$userIDsString = implode(',', $userIDs);
		
		// board
		$sql = "UPDATE IGNORE	wbb".WBB_N."_board_closed_category_to_user
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wbb".WBB_N."_board_closed_category_to_admin
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE	wbb".WBB_N."_board_moderator
			SET	userID = ".$this->userID."
			WHERE	userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wbb".WBB_N."_board_subscription
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wbb".WBB_N."_board_to_user
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wbb".WBB_N."_board_visit
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		
		// posts
		$sql = "UPDATE	wbb".WBB_N."_post
			SET	userID = ".$this->userID."
			WHERE	userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE	wbb".WBB_N."_post_report
			SET	userID = ".$this->userID."
			WHERE	userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		
		// thread
		$sql = "UPDATE	wbb".WBB_N."_thread
			SET	userID = ".$this->userID."
			WHERE	userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wbb".WBB_N."_thread_rating
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wbb".WBB_N."_thread_subscription
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wbb".WBB_N."_thread_visit
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		
		// attachment
		$sql = "UPDATE	wcf".WCF_N."_attachment
			SET	userID = ".$this->userID."
			WHERE	userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		
		// groups
		$sql = "UPDATE IGNORE	wcf".WCF_N."_group_application
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_group_leader
			SET		leaderUserID = ".$this->userID."
			WHERE		leaderUserID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_user_to_groups
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		
		// pm
		$sql = "UPDATE	wcf".WCF_N."_pm
			SET	userID = ".$this->userID."
			WHERE	userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE	wcf".WCF_N."_pm_folder
			SET	userID = ".$this->userID."
			WHERE	userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_pm_to_user
			SET		recipientID = ".$this->userID."
			WHERE		recipientID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		
		// poll
		$sql = "UPDATE IGNORE	wcf".WCF_N."_poll_option_vote
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_poll_vote
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		
		// blacklist / whitelist
		$sql = "UPDATE IGNORE	wcf".WCF_N."_user_blacklist
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_user_blacklist
			SET		blackUserID = ".$this->userID."
			WHERE		blackUserID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_user_whitelist
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE IGNORE	wcf".WCF_N."_user_whitelist
			SET		whiteUserID = ".$this->userID."
			WHERE		whiteUserID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		
		// languages
		$sql = "UPDATE IGNORE	wcf".WCF_N."_user_to_languages
			SET		userID = ".$this->userID."
			WHERE		userID IN (".$userIDsString.")";
		WCF::getDB()->sendQuery($sql);
		
		// delete users
		UserEditor::deleteUsers($userIDs);
		
		// unmark users
		UserEditor::unmarkAll();
		$this->saved();
		
		// show succes message
		WCF::getTPL()->assign('message', 'wbb.acp.user.merge.success');
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
			if (is_array($markedUsers)) $this->userIDs = $markedUsers;
			if (!count($this->userIDs)) throw new IllegalLinkException();
		}
		
		$this->users = User::getUsers(implode(',', $this->userIDs));
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'users' => $this->users,
			'userIDs' => implode(',', $this->userIDs),
			'userID' => $this->userID
		));
	}
}
?>