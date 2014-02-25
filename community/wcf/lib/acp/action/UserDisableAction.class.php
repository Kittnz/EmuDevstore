<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/UserEnableAction.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/system/session/Session.class.php');

/**
 * Disables users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class UserDisableAction extends UserEnableAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		AbstractAction::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.user.canEnableUser');
		
		if (count($this->userIDs) > 0) {
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
			
			// update groups
			$sql = "DELETE FROM	wcf".WCF_N."_user_to_groups
				WHERE		userID IN (".implode(',', $this->userIDs).")
						AND groupID <> ".Group::getGroupIdByType(Group::EVERYONE);
			WCF::getDB()->sendQuery($sql);
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_groups
							(userID, groupID)
				VALUES			(".implode(', '.Group::getGroupIdByType(Group::GUESTS).'),(', $this->userIDs).", '".Group::getGroupIdByType(Group::GUESTS)."')";
			WCF::getDB()->sendQuery($sql);
			
			// update activation code
			foreach ($this->userIDs as $userID) {
				$sql = "UPDATE	wcf".WCF_N."_user
					SET	activationCode = ".UserRegistrationUtil::getActivationCode()."
					WHERE	userID = ".$userID;
				WCF::getDB()->sendQuery($sql);
			}
			
			// unmark users
			UserEditor::unmarkAll();
		
			// reset sessions
			Session::resetSessions($this->userIDs);
		}
		$this->executed();
		
		if (!empty($this->url)) HeaderUtil::redirect($this->url);
		else {
			// set active menu item
			WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.user.management');
			
			// show succes message
			WCF::getTPL()->assign('message', 'wcf.acp.user.disable.success');
			WCF::getTPL()->display('success');
		}
		exit;
	}
}
?>