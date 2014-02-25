<?php
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/user/group/GroupApplicationEditor.class.php');

/**
 * Removes users from a user group.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class UserGroupMemberRemoveAction extends AbstractSecureAction {
	public $userIDs = array();
	public $groupID = 0;
	public $group;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['userIDs'])) $this->userIDs = ArrayUtil::toIntegerArray($_POST['userIDs']);
		if (isset($_POST['groupID'])) $this->groupID = intval($_POST['groupID']);
		$this->group = new Group($this->groupID);
		if (!$this->group->groupID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		AbstractAction::execute();
		
		// check permission
		if (!GroupApplicationEditor::isGroupLeader(WCF::getUser(), $this->groupID)) {
			throw new PermissionDeniedException();
		}
		
		if (MODULE_MODERATED_USER_GROUP != 1) {
			throw new IllegalLinkException();
		}
		
		// remove users
		if (count($this->userIDs)) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_to_groups
				WHERE		userID IN (".implode(',', $this->userIDs).")
						AND groupID = ".$this->groupID;
			WCF::getDB()->sendQuery($sql);
			
			// reset sessions
			Session::resetSessions($this->userIDs);
		}
		$this->executed();
		
		HeaderUtil::redirect('index.php?form=UserGroupAdministrate&groupID='.$this->groupID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>