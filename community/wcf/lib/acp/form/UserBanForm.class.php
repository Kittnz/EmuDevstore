<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/system/session/Session.class.php');

/**
 * Shows the user ban form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UserBanForm extends ACPForm {
	// system
	public $templateName = 'userBan';
	public $activeMenuItem = 'wcf.acp.menu.link.user.management';
	public $neededPermissions = 'admin.user.canBanUser';

	// parameters
	/**
	 * list of user ids
	 * 
	 * @var	array<integer>
	 */
	public $userIDArray = array();
	
	/**
	 * list of users
	 * 
	 * @var	array<User>
	 */
	public $users = array();
	
	/**
	 * the forward url
	 * 
	 * @var	string
	 */
	public $url = '';
	
	/**
	 * the ban reason
	 * 
	 * @var	string
	 */
	public $reason = '';

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['userID'])) $this->userIDArray[] = intval($_REQUEST['userID']);
		else {
			$this->userIDArray = WCF::getSession()->getVar('markedUsers');	
			if (!is_array($this->userIDArray)) $this->userIDArray = array();
		}
		if (isset($_REQUEST['url'])) $this->url = $_REQUEST['url'];
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['userIDs'])) $this->userIDArray = ArrayUtil::toIntegerArray(explode(',', $_POST['userIDs']));
		if (isset($_POST['reason'])) $this->reason = StringUtil::trim($_POST['reason']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// active user can't ban himself   
		$this->userIDArray = array_diff($this->userIDArray, array(WCF::getUser()->userID));		
		
		if (count($this->userIDArray) > 0) {
			// check permission
			$sql = "SELECT	DISTINCT groupID
				FROM	wcf".WCF_N."_user_to_groups
				WHERE	userID IN (".implode(',', $this->userIDArray).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!Group::isAccessibleGroup($row['groupID'])) {
					throw new PermissionDeniedException();
				}
			}
			
			// get adminCanMail user option id
			$adminCanMailID = User::getUserOptionID('adminCanMail');
			
			// update user
			$sql = "UPDATE	wcf".WCF_N."_user
				SET	banned = 1,
					banReason = '".escapeString($this->reason)."'
				WHERE	userID IN (".implode(',', $this->userIDArray).")";
			WCF::getDB()->sendQuery($sql);
			
			// update user options
			if ($adminCanMailID !== null) {
				$sql = "UPDATE	wcf".WCF_N."_user_option_value
					SET	userOption".$adminCanMailID." = 0
					WHERE	userID IN (".implode(',', $this->userIDArray).")";
				WCF::getDB()->sendQuery($sql);
			}
			
			// unmark users
			UserEditor::unmarkAll();
		
			// reset sessions
			Session::resetSessions($this->userIDArray);
		}
		$this->saved();
		
		// forward
		if (empty($this->url)) {
			$this->url = 'index.php?form=UserSearch&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED;
		}
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->users = User::getUsers(implode(',', $this->userIDArray));
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'url' => $this->url,
			'userIDs' => implode(',', $this->userIDArray),
			'users' => $this->users,
			'reason' => $this->reason
		));
	}
}
?>