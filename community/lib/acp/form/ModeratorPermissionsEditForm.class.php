<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');
require_once(WBB_DIR.'lib/acp/form/UserPermissionsEditForm.class.php');

/**
 * Shows the moderator to boards permissions list.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.form
 * @category 	Burning Board
 */
class ModeratorPermissionsEditForm extends UserPermissionsEditForm {
	public $neededPermissions = 'admin.board.canEditModerators';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		AbstractForm::readParameters();
		
		// get user
		if (isset($_REQUEST['userID'])) {
			$this->userID = intval($_REQUEST['userID']);
			require_once(WBB_DIR.'lib/data/user/AbstractWBBUserSession.class.php');
			$this->user = new AbstractWBBUserSession($this->userID);
			if (!$this->user->userID) {
				throw new IllegalLinkException();
			}
			require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
			if (!Group::isAccessibleGroup($this->user->getGroupIDs())) {
				throw new PermissionDeniedException();
			}
			
			$this->activeMenuItem = 'wcf.acp.menu.link.user.management';
		}
		// get group
		else if (isset($_REQUEST['groupID'])) {
			$this->groupID = intval($_REQUEST['groupID']);
			require_once(WCF_DIR.'lib/data/user/group/GroupEditor.class.php');
			$this->group = new GroupEditor($this->groupID);
			if (!$this->group->groupID) {
				throw new IllegalLinkException();
			}
			if (!$this->group->isAccessible()) {
				throw new PermissionDeniedException();
			}
			
			$this->activeMenuItem = 'wcf.acp.menu.link.group';
		}
		
		// active permission
		if (isset($_REQUEST['permissionName'])) $this->permissionName = $_REQUEST['permissionName'];
		
		$this->readPermissionSettings();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		$inserts = $fields = '';
		foreach ($this->permissionSettings as $name) {
			$fields .= ', '.$name;
		}
		
		foreach ($this->boardPermissions as $boardID => $permissions) {
			$noDefaultValue = false;
			foreach ($permissions as $value) {
				if ($value != -1) $noDefaultValue = true;
			}
			
			if (!$noDefaultValue) continue;
			
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= '('.intval($boardID).', '.($this->userID != 0 ? $this->userID.', 0 ' : '0, '.$this->groupID);
			foreach ($this->permissionSettings as $name) {
				$inserts .= ', '.(isset($permissions[$name]) ? $permissions[$name] : -1);
			}
			$inserts .= ')';
		}
		
		// delete old entries
		$sql = "DELETE FROM	wbb".WBB_N."_board_moderator
			WHERE		".($this->userID != 0 ? "userID = ".$this->userID : "groupID = ".$this->groupID);
		WCF::getDB()->sendQuery($sql);
			
		if (!empty($inserts)) {
			$sql = "INSERT IGNORE INTO	wbb".WBB_N."_board_moderator
							(boardID, userID, groupID".$fields.")
				VALUES			".$inserts;
			WCF::getDB()->sendQuery($sql);
		}
		
		// reset sessions
		Session::resetSessions(array(), true, false);
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'type' => 'moderator'
		));
	}
	
	/**
	 * Gets a list of board permissions.
	 */
	protected function readBoardPermissions() {
		$sql = "SELECT		*
			FROM		wbb".WBB_N."_board_moderator
			WHERE		".($this->userID != 0 ? "userID = ".$this->userID : "groupID = ".$this->groupID)."
			ORDER BY	boardID";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$boardID = $row['boardID'];
			unset($row['boardID'], $row['userID'], $row['groupID']);
			$this->activeBoardPermissions[$boardID] = $row;
		}
	}
	
	/**
	 * Gets available permission settings.
	 */
	protected function readPermissionSettings() {
		$sql = "SHOW COLUMNS FROM wbb".WBB_N."_board_moderator";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['Field'] != 'boardID' && $row['Field'] != 'userID' && $row['Field'] != 'groupID') {
				// check modules
				switch ($row['Field']) {
					case 'canMarkAsDoneThread': 
						if (!MODULE_THREAD_MARKING_AS_DONE) continue 2;
						break;
				}
				
				$this->permissionSettings[] = $row['Field'];
			}
		}
	}
	
	/**
	 * @see GroupPermissionsEditForm::loadGlobalPermissions()
	 */
	protected function loadGlobalPermissions() {
		if ($this->userID != 0) {
			$boards = WCF::getCache()->get('board', 'boards');
			foreach ($boards as $board) {
				$permissions = array();
				foreach ($this->permissionSettings as $permissionSetting) {
					$permissions[$permissionSetting] = intval($this->user->getBoardModeratorPermission($permissionSetting, $board->boardID));
				}
				
				$this->globalPermissions[$board->boardID] = $permissions;
			}
		}
		else {
			$this->globalPermissions = array();
			foreach ($this->permissionSettings as $permissionSetting) {
				$this->globalPermissions[$permissionSetting] = intval($this->group->getGroupOption('mod.board.'.$permissionSetting));
			}
		}
	}
}
?>