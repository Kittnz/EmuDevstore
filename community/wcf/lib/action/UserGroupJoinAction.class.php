<?php
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');

/**
 * Adds a user to a user group.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class UserGroupJoinAction extends AbstractSecureAction {
	public $groupID = 0;
	public $group;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['groupID'])) $this->groupID = intval($_REQUEST['groupID']);
		$this->group = new Group($this->groupID);
		if (!$this->group->groupID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		if (!WCF::getUser()->userID || $this->group->groupType != 5 || in_array($this->group->groupID, WCF::getUser()->getGroupIDs())) {
			throw new PermissionDeniedException();
		}
		
		if (MODULE_MODERATED_USER_GROUP != 1) {
			throw new IllegalLinkException();
		}
		
		// add user
		$editor = WCF::getUser()->getEditor();
		$editor->addToGroup($this->group->groupID);
		// reset session
		WCF::getSession()->resetUserData();
		$this->executed();		
		
		HeaderUtil::redirect('index.php?page=UserGroups'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>