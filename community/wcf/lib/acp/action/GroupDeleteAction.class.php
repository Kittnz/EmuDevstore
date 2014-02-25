<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Deletes a user group.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class GroupDeleteAction extends AbstractAction {
	public $groupID = 0;
	public $groupIDs = array();
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['groupID'])) $this->groupID = intval($_REQUEST['groupID']);
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// check permission
		WCF::getUser()->checkPermission('admin.user.canDeleteGroup');
		
		require_once(WCF_DIR.'lib/data/user/group/GroupEditor.class.php');
		if ($this->groupID !== 0) {
			$this->groupIDs[] = $this->groupID;
		}
		
		// check permission
		if (!Group::isAccessibleGroup($this->groupIDs)) {
			throw new PermissionDeniedException();
		}
		
		// check master password
		WCFACP::checkMasterPassword();
			
		$deletedGroups = GroupEditor::deleteGroups($this->groupIDs);
		$this->executed();
		HeaderUtil::redirect('index.php?page=GroupList&deletedGroups='.$deletedGroups.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>