<?php
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

/**
 * Deletes user group applications.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class UserGroupApplicationDeleteAction extends AbstractSecureAction {
	public $applicationIDs = array();
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['applicationIDs'])) $this->applicationIDs = ArrayUtil::toIntegerArray($_POST['applicationIDs']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		if (MODULE_MODERATED_USER_GROUP != 1) {
			throw new IllegalLinkException();
		}
		
		// delete applications
		if (count($this->applicationIDs)) {
			$sql = "DELETE FROM	wcf".WCF_N."_group_application
				WHERE		applicationID IN (".implode(',', $this->applicationIDs).")
						AND groupID IN (
							SELECT	groupID
							FROM	wcf".WCF_N."_group_leader
							WHERE	leaderUserID = ".WCF::getUser()->userID."
								OR leaderGroupID IN (".implode(',', WCF::getUser()->getGroupIDs()).")
						)";
			WCF::getDB()->sendQuery($sql);
		}
		
		$this->executed();
		
		HeaderUtil::redirect('index.php?page=UserGroupLeader'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>