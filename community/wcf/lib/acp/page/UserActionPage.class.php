<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
	
/**
 * Calls user actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class UserActionPage extends AbstractPage {
	public $userID = 0;
	public static $validFunctions = array('mark', 'unmark', 'unmarkAll', 'deleteMarked');
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['userID'])) $this->userID = ArrayUtil::toIntegerArray($_REQUEST['userID']);
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();
		
		if (in_array($this->action, self::$validFunctions)) {
			$this->{$this->action}();
		}
	}
	
	/**
	 * Marks a user.
	 */
	public function mark() {
		if (!is_array($this->userID)) $this->userID = array($this->userID);
		foreach ($this->userID as $userID) {
			$markedUsers = WCF::getSession()->getVar('markedUsers');
			if ($markedUsers == null || !is_array($markedUsers)) { 
				$markedUsers = array($userID);
				WCF::getSession()->register('markedUsers', $markedUsers);
			}
			else {
				if (!in_array($userID, $markedUsers)) {
					array_push($markedUsers, $userID);
					WCF::getSession()->register('markedUsers', $markedUsers);
				}
			}
		}
	}
	
	/**
	 * Unmarks a user.
	 */
	public function unmark() {
		if (!is_array($this->userID)) $this->userID = array($this->userID);
		foreach ($this->userID as $userID) {
			$markedUsers = WCF::getSession()->getVar('markedUsers');
			if (is_array($markedUsers) && in_array($userID, $markedUsers)) {
				$key = array_search($userID, $markedUsers);
				
				unset($markedUsers[$key]);
				if (count($markedUsers) == 0) {
					self::unmarkAll();
				} 
				else {
					WCF::getSession()->register('markedUsers', $markedUsers);
				}
			}
		}
	}
	
	/**
	 * Unmarks all marked users.
	 */
	public static function unmarkAll() {
		UserEditor::unmarkAll();
	}
	
	/**
	 * Deletes marked users.
	 */
	public function deleteMarked() {
		WCF::getUser()->checkPermission('admin.user.canDeleteUser');
		
		$userIDs = WCF::getSession()->getVar('markedUsers');	
		if (!is_array($userIDs)) $userIDs = array();
		$deletedUsers = 0;
		
		// active user can't delete himself   
		$activeUserID = WCF::getSession()->getUser()->userID;
		$userIDs = array_diff($userIDs, array($activeUserID));
		
		// check permission
		if (count($userIDs) > 0) {
			$sql = "SELECT	DISTINCT groupID
				FROM	wcf".WCF_N."_user_to_groups
				WHERE	userID IN (".implode(',', $userIDs).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!Group::isAccessibleGroup($row['groupID'])) {
					throw new PermissionDeniedException();
				}
			}
			
			$deletedUsers = UserEditor::deleteUsers($userIDs);
		}
		
		self::unmarkAll();
		HeaderUtil::redirect('index.php?form=UserSearch&deletedUsers='.$deletedUsers.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>