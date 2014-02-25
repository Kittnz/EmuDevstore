<?php
require_once(WCF_DIR.'lib/action/UserGroupJoinAction.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/data/user/group/GroupApplicationEditor.class.php');

/**
 * Removes a user from a user group.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class UserGroupLeaveAction extends UserGroupJoinAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		AbstractAction::execute();
		
		// check permission
		if (!WCF::getUser()->userID || ($this->group->groupType != 5 && $this->group->groupType != 6) || !in_array($this->group->groupID, WCF::getUser()->getGroupIDs())) {
			throw new PermissionDeniedException();
		}
		
		if (MODULE_MODERATED_USER_GROUP != 1) {
			throw new IllegalLinkException();
		}
		
		// remove user
		$editor = WCF::getUser()->getEditor();
		$editor->removeFromGroup($this->group->groupID);
		// reset session
		WCF::getSession()->resetUserData();
		
		// delete application if existing
		if (($application = GroupApplicationEditor::getApplication(WCF::getUser()->userID, $this->group->groupID))) {
			$application->delete();
		}
		$this->executed();
		
		HeaderUtil::redirect('index.php?page=UserGroups'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>